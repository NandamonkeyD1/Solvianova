<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\User;
use App\Models\ActivityLog;
use Carbon\Carbon;

class Projects extends Component
{
    public string $filterStatus = 'ALL';
    public string $search = '';

    // Modal state
    public bool $showCreateModal = false;
    public bool $showDetailModal = false;
    public bool $showClosureModal = false;

    // Form fields
    public ?int $editingProjectId = null;
    public string $name = '';
    public string $code = '';
    public string $client = '';
    public string $description = '';
    public string $start_date = '';
    public string $deadline = '';
    public string $status = 'ACTIVE';
    public array $selectedTeamMembers = [];

    // Selected detail project
    public ?Project $selectedProject = null;

    protected $rules = [
        'name' => 'required|min:3',
        'code' => 'required|min:2',
        'client' => 'nullable',
        'description' => 'nullable',
        'start_date' => 'nullable|date',
        'deadline' => 'nullable|date',
        'status' => 'required',
    ];

    public function openCreateModal()
    {
        $this->resetForm();
        $this->code = 'PRJ-' . strtoupper(substr(md5(microtime()), 0, 5));
        $this->showCreateModal = true;
    }

    public function editProject(int $id)
    {
        $p = Project::findOrFail($id);
        $this->editingProjectId = $p->id;
        $this->name = $p->name;
        $this->code = $p->code;
        $this->client = $p->client ?? '';
        $this->description = $p->description ?? '';
        $this->start_date = $p->start_date?->format('Y-m-d') ?? '';
        $this->deadline = $p->deadline?->format('Y-m-d') ?? '';
        $this->status = $p->status;
        $this->selectedTeamMembers = $p->members->pluck('id')->toArray();
        $this->showCreateModal = true;
    }

    public function saveProject()
    {
        $this->validate();

        if ($this->editingProjectId) {
            $p = Project::findOrFail($this->editingProjectId);
            $p->update([
                'name' => $this->name,
                'code' => $this->code,
                'client' => $this->client,
                'description' => $this->description,
                'start_date' => $this->start_date ?: null,
                'deadline' => $this->deadline ?: null,
                'status' => $this->status,
            ]);
            $p->members()->sync($this->selectedTeamMembers);
            ActivityLog::log('PROJECT_UPDATED', 'Project "' . $p->name . '" diperbarui.', $p);
            $this->dispatch('toast', message: 'Project berhasil diperbarui!');
        } else {
            $p = Project::create([
                'name' => $this->name,
                'code' => $this->code,
                'client' => $this->client,
                'description' => $this->description,
                'start_date' => $this->start_date ?: null,
                'deadline' => $this->deadline ?: null,
                'status' => $this->status,
                'progress' => 0,
                'created_by' => auth()->id(),
            ]);
            $p->members()->sync($this->selectedTeamMembers);
            ActivityLog::log('PROJECT_CREATED', 'Super Admin membuat project "' . $p->name . '".', $p);
            $this->dispatch('toast', message: 'Project baru berhasil dibuat!');
        }

        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openDetail(int $id)
    {
        $this->selectedProject = Project::with(['members', 'tasks.assignees', 'financialTransactions'])->findOrFail($id);
        $this->selectedProject->recalculateProgress();
        $this->showDetailModal = true;
    }

    public function deleteProject(int $id)
    {
        if (!auth()->user()->isSuperAdmin()) return;

        $p = Project::findOrFail($id);
        $name = $p->name;
        $p->delete();

        ActivityLog::log('PROJECT_DELETED', 'Super Admin menghapus project "' . $name . '".', null);
        $this->dispatch('toast', message: 'Project berhasil dihapus!', type: 'error');
        $this->showDetailModal = false;
    }

    public function openClosure(int $id)
    {
        $this->selectedProject = Project::with(['members', 'tasks', 'financialTransactions'])->findOrFail($id);
        $this->selectedProject->recalculateProgress();
        $this->showClosureModal = true;
    }

    public function confirmCloseProject()
    {
        if ($this->selectedProject) {
            $this->selectedProject->update(['status' => 'COMPLETED', 'progress' => 100]);
            ActivityLog::log('PROJECT_COMPLETED', 'Project "' . $this->selectedProject->name . '" telah diselesaikan.', $this->selectedProject);
            $this->dispatch('toast', message: 'Project telah diselesaikan dengan sukses!');
            $this->showClosureModal = false;
        }
    }

    public function resetForm()
    {
        $this->editingProjectId = null;
        $this->name = '';
        $this->code = '';
        $this->client = '';
        $this->description = '';
        $this->start_date = '';
        $this->deadline = '';
        $this->status = 'ACTIVE';
        $this->selectedTeamMembers = [];
    }

    public function render()
    {
        $query = Project::with(['members', 'tasks']);

        if ($this->filterStatus !== 'ALL') {
            $query->where('status', $this->filterStatus);
        }

        if (!empty($this->search)) {
            $q = '%' . $this->search . '%';
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', $q)
                    ->orWhere('code', 'like', $q)
                    ->orWhere('client', 'like', $q);
            });
        }

        // Restrict view if member
        if (!auth()->user()->isSuperAdmin()) {
            $query->whereHas('members', function($sub) {
                $sub->where('users.id', auth()->id());
            });
        }

        $projects = $query->latest()->get();
        $users = User::where('status', 'ACTIVE')->get();

        return view('livewire.projects', [
            'projects' => $projects,
            'users' => $users,
        ]);
    }
}
