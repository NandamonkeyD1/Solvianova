<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Models\ProgressUpdate;
use App\Models\DailyProgressLog;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\Notification;
use App\Models\ActivityLog;
use Carbon\Carbon;

class Tasks extends Component
{
    use WithFileUploads;

    public string $viewMode = 'kanban'; // kanban, list
    public string $filterProject = 'ALL';
    public string $filterPriority = 'ALL';
    public string $filterStatus = 'ALL';
    public string $search = '';

    // Create / Edit Modal State
    public bool $showCreateModal = false;
    public ?int $editingTaskId = null;
    public string $title = '';
    public string $description = '';
    public ?int $project_id = null;
    public array $selectedAssignees = [];
    public string $priority = 'MEDIUM';
    public string $deadline = '';

    // Task Detail / Member Workflow Modal State
    public bool $showDetailModal = false;
    public ?int $selectedTaskId = null;

    // Progress Update Form
    public int $progressPercent = 50;
    public string $updateDescription = '';
    public string $completedNotes = '';
    public string $nextPlan = '';
    public string $blockerInput = '';
    public string $attachmentUrl = '';
    public $uploadedFile = null;

    // Comment Form
    public string $newComment = '';

    // Super Admin Review Action Modal State
    public bool $showReviewModal = false;
    public string $reviewAction = 'APPROVE'; // APPROVE, REVISION
    public string $revisionFeedback = '';

    protected $rules = [
        'title' => 'required|min:3',
        'project_id' => 'required|exists:projects,id',
        'priority' => 'required',
    ];

    public function mount()
    {
        if (request()->has('task_id')) {
            $this->openTaskDetail((int) request()->get('task_id'));
        }
    }

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function editTask(int $id)
    {
        $t = Task::with('assignees')->findOrFail($id);
        $this->editingTaskId = $t->id;
        $this->title = $t->title;
        $this->description = $t->description ?? '';
        $this->project_id = $t->project_id;
        $this->priority = $t->priority;
        $this->deadline = $t->deadline?->format('Y-m-d\TH:i') ?? '';
        $this->selectedAssignees = $t->assignees->pluck('id')->toArray();
        $this->showCreateModal = true;
    }

    public function deleteTask(int $id)
    {
        if (!auth()->user()->isSuperAdmin()) return;

        $t = Task::findOrFail($id);
        $project = $t->project;
        $title = $t->title;

        $t->delete();

        $project?->recalculateProgress();
        ActivityLog::log('TASK_DELETED', 'Super Admin menghapus task "' . $title . '".', null);
        $this->dispatch('toast', message: 'Task berhasil dihapus!', type: 'error');
        $this->showDetailModal = false;
        $this->selectedTaskId = null;
    }

    public function saveTask()
    {
        $this->validate();

        if ($this->editingTaskId) {
            $t = Task::findOrFail($this->editingTaskId);
            $t->update([
                'title' => $this->title,
                'description' => $this->description,
                'project_id' => $this->project_id,
                'priority' => $this->priority,
                'deadline' => $this->deadline ?: null,
            ]);
            $t->assignees()->sync($this->selectedAssignees);
            ActivityLog::log('TASK_UPDATED', 'Task "' . $t->title . '" diperbarui.', $t);
            $this->dispatch('toast', message: 'Task berhasil diperbarui!');
        } else {
            $t = Task::create([
                'project_id' => $this->project_id,
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'status' => 'TODO',
                'progress' => 0,
                'deadline' => $this->deadline ?: null,
                'created_by' => auth()->id(),
            ]);
            $t->assignees()->sync($this->selectedAssignees);

            // Notify Assignees
            foreach ($this->selectedAssignees as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'type' => 'TASK',
                    'title' => 'Task Baru Ditugaskan',
                    'message' => 'Anda ditugaskan pada task "' . $t->title . '" di project ' . $t->project?->name . '.',
                    'link' => '/tasks?task_id=' . $t->id,
                ]);
            }

            ActivityLog::log('TASK_CREATED', 'Task "' . $t->title . '" dibuat & ditugaskan.', $t);
            $this->dispatch('toast', message: 'Task baru berhasil dibuat!');
        }

        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function openTaskDetail(int $taskId)
    {
        $this->selectedTaskId = $taskId;
        $task = Task::find($taskId);
        if ($task) {
            $this->progressPercent = $task->progress;
            $this->updateDescription = '';
            $this->completedNotes = '';
            $this->nextPlan = '';
            $this->blockerInput = $task->blocker_reason ?? '';
            $this->attachmentUrl = '';
            $this->uploadedFile = null;
            $this->showDetailModal = true;
        }
    }

    public function saveProgressUpdate()
    {
        if (!$this->selectedTaskId) return;

        $task = Task::find($this->selectedTaskId);
        if (!$task) return;

        $desc = trim($this->updateDescription) ?: 'Memperbarui progress pekerjaan.';

        // 1. Save progress update history record (never overwrites!)
        ProgressUpdate::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'progress' => $this->progressPercent,
            'description' => $desc,
            'completed_notes' => $this->completedNotes,
            'next_plan' => $this->nextPlan,
            'blocker' => $this->blockerInput,
            'attachment_url' => $this->attachmentUrl,
        ]);

        // 2. Also log to DailyProgressLog for team activity tracker
        DailyProgressLog::create([
            'user_id' => auth()->id(),
            'date' => Carbon::today(),
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'progress' => $this->progressPercent,
            'work_done' => $desc,
            'next_plan' => $this->nextPlan,
            'blocker' => $this->blockerInput,
            'attachment_url' => $this->attachmentUrl,
        ]);

        $newStatus = $task->status;
        if ($this->progressPercent > 0 && $task->status === 'TODO') {
            $newStatus = 'IN_PROGRESS';
        }

        $task->update([
            'progress' => $this->progressPercent,
            'status' => $newStatus,
        ]);

        $task->project?->recalculateProgress();
        ActivityLog::log('PROGRESS_UPDATED', auth()->user()->name . ' memperbarui progress task "' . $task->title . '" ke ' . $this->progressPercent . '%.', $task);

        $this->dispatch('toast', message: 'Progress berhasil diperbarui & disimpan ke History!');
        
        // Reset form inputs for next entry
        $this->updateDescription = '';
        $this->nextPlan = '';
        $this->attachmentUrl = '';
    }

    public function reportBlocker()
    {
        if (!$this->selectedTaskId) return;

        $task = Task::find($this->selectedTaskId);
        if (!$task) return;

        $task->update([
            'status' => 'BLOCKED',
            'blocker_reason' => $this->blockerInput ?: 'Kendala operasional dilaporkan oleh member.',
            'blocker_priority' => 'HIGH',
        ]);

        // Notify Super Admin
        $admins = User::where('role', 'SUPER_ADMIN')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'BLOCKER',
                'title' => '🚨 Task Terhambat (Blocked)',
                'message' => auth()->user()->name . ' melaporkan kendala pada task "' . $task->title . '": ' . $this->blockerInput,
                'link' => '/tasks?task_id=' . $task->id,
            ]);
        }

        ActivityLog::log('TASK_BLOCKED', auth()->user()->name . ' melaporkan kendala pada task "' . $task->title . '".', $task);
        $this->dispatch('toast', message: 'Kendala berhasil dilaporkan ke Super Admin!', type: 'error');
    }

    public function submitForReview()
    {
        if (!$this->selectedTaskId) return;

        $task = Task::find($this->selectedTaskId);
        if (!$task) return;

        $task->update([
            'status' => 'WAITING_REVIEW',
        ]);

        // Notify Super Admin
        $admins = User::where('role', 'SUPER_ADMIN')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'REVIEW',
                'title' => '🟠 Pekerjaan Menunggu Review',
                'message' => auth()->user()->name . ' mengirimkan hasil kerja task "' . $task->title . '" untuk direview.',
                'link' => '/tasks?task_id=' . $task->id,
            ]);
        }

        ActivityLog::log('TASK_SUBMITTED', auth()->user()->name . ' mengajukan review untuk task "' . $task->title . '".', $task);
        $this->dispatch('toast', message: 'Task berhasil diajukan untuk review Super Admin!');
    }

    public function openReviewModal(string $action)
    {
        $this->reviewAction = $action;
        $this->revisionFeedback = '';
        $this->resetErrorBag();
        $this->showReviewModal = true;
    }

    public function approveTaskDirect()
    {
        if (!$this->selectedTaskId || !auth()->user()->isSuperAdmin()) return;

        $task = Task::find($this->selectedTaskId);
        if (!$task) return;

        $task->update([
            'status' => 'DONE',
            'progress' => 100,
            'revision_notes' => null,
        ]);

        // Notify Assignees
        foreach ($task->assignees as $member) {
            Notification::create([
                'user_id' => $member->id,
                'type' => 'APPROVED',
                'title' => '✓ Task Disetujui (Approved)',
                'message' => 'Super Admin telah menyetujui hasil kerja Anda untuk task "' . $task->title . '".',
                'link' => '/tasks?task_id=' . $task->id,
            ]);
        }

        $task->project?->recalculateProgress();
        ActivityLog::log('TASK_APPROVED', 'Super Admin menyetujui task "' . $task->title . '".', $task);
        $this->dispatch('toast', message: 'Task berhasil disetujui (Approved)!');
        $this->showReviewModal = false;
    }

    public function submitRevisionNotes()
    {
        if (!$this->selectedTaskId || !auth()->user()->isSuperAdmin()) return;

        $task = Task::find($this->selectedTaskId);
        if (!$task) return;

        $notes = trim($this->revisionFeedback);
        if (empty($notes)) {
            $notes = 'Mohon perbaiki pekerjaan sesuai petunjuk Super Admin.';
        }

        $task->update([
            'status' => 'REVISION',
            'revision_notes' => $notes,
        ]);

        // Notify Assignees
        foreach ($task->assignees as $member) {
            Notification::create([
                'user_id' => $member->id,
                'type' => 'REVISION',
                'title' => '⚠️ Task Membutuhkan Revisi',
                'message' => 'Catatan revisi Super Admin: ' . $notes,
                'link' => '/tasks?task_id=' . $task->id,
            ]);
        }

        ActivityLog::log('TASK_REVISED', 'Super Admin meminta revisi task "' . $task->title . '".', $task);
        $this->dispatch('toast', message: 'Catatan revisi telah dikirim ke member!', type: 'error');
        $this->showReviewModal = false;
        $this->revisionFeedback = '';
    }

    public function processSuperAdminReview()
    {
        if ($this->reviewAction === 'APPROVE') {
            $this->approveTaskDirect();
        } else {
            $this->submitRevisionNotes();
        }
    }

    public function postComment()
    {
        if (!$this->selectedTaskId || empty(trim($this->newComment))) return;

        TaskComment::create([
            'task_id' => $this->selectedTaskId,
            'user_id' => auth()->id(),
            'comment' => $this->newComment,
        ]);

        $this->newComment = '';
    }

    public function resetCreateForm()
    {
        $this->editingTaskId = null;
        $this->title = '';
        $this->description = '';
        $this->project_id = Project::first()?->id;
        $this->selectedAssignees = [];
        $this->priority = 'MEDIUM';
        $this->deadline = '';
    }

    public function render()
    {
        $query = Task::with(['project', 'assignees']);

        if (!auth()->user()->isSuperAdmin()) {
            $query->whereHas('assignees', function($sub) {
                $sub->where('users.id', auth()->id());
            });
        }

        if ($this->filterProject !== 'ALL') {
            $query->where('project_id', $this->filterProject);
        }

        if ($this->filterPriority !== 'ALL') {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterStatus !== 'ALL') {
            $query->where('status', $this->filterStatus);
        }

        if (!empty($this->search)) {
            $q = '%' . $this->search . '%';
            $query->where(function($sub) use ($q) {
                $sub->where('title', 'like', $q)
                    ->orWhere('description', 'like', $q);
            });
        }

        $tasks = $query->latest()->get();
        $projects = Project::where('status', 'ACTIVE')->get();
        $users = User::where('status', 'ACTIVE')->get();

        // Freshly query selected task for detail modal
        $selectedTask = null;
        if ($this->showDetailModal && $this->selectedTaskId) {
            $selectedTask = Task::with(['project', 'assignees', 'progressUpdates.user', 'attachments', 'comments.user'])
                ->find($this->selectedTaskId);
        }

        return view('livewire.tasks', [
            'tasks' => $tasks,
            'projects' => $projects,
            'users' => $users,
            'selectedTask' => $selectedTask,
        ]);
    }
}
