<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Schedule;
use App\Models\Project;
use App\Models\Task;
use App\Models\Asset;
use App\Models\ActivityLog;
use Carbon\Carbon;

class ScheduleCalendar extends Component
{
    public string $viewMode = 'agenda'; // agenda, month
    public bool $showCreateModal = false;

    public ?int $editingScheduleId = null;
    public string $title = '';
    public string $date = '';
    public string $time = '10:00';
    public string $type = 'MEETING';
    public ?int $project_id = null;
    public string $description = '';
    public string $reminder = 'H-1';

    protected $rules = [
        'title' => 'required|min:3',
        'date' => 'required|date',
    ];

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function editSchedule(int $id)
    {
        $s = Schedule::findOrFail($id);
        $this->editingScheduleId = $s->id;
        $this->title = $s->title;
        $this->date = $s->date?->format('Y-m-d') ?? Carbon::today()->format('Y-m-d');
        $this->time = $s->time ? substr($s->time, 0, 5) : '10:00';
        $this->type = $s->type;
        $this->project_id = $s->project_id;
        $this->description = $s->description ?? '';
        $this->reminder = $s->reminder ?? 'H-1';
        $this->showCreateModal = true;
    }

    public function deleteSchedule(int $id)
    {
        if (!auth()->user()->isSuperAdmin()) return;

        $s = Schedule::findOrFail($id);
        $title = $s->title;
        $s->delete();

        ActivityLog::log('SCHEDULE_DELETED', 'Super Admin menghapus agenda "' . $title . '".', null);
        $this->dispatch('toast', message: 'Agenda berhasil dihapus!', type: 'error');
    }

    public function saveSchedule()
    {
        $this->validate();

        if ($this->editingScheduleId) {
            $s = Schedule::findOrFail($this->editingScheduleId);
            $s->update([
                'title' => $this->title,
                'date' => $this->date,
                'time' => $this->time ?: null,
                'type' => $this->type,
                'project_id' => $this->project_id,
                'description' => $this->description,
                'reminder' => $this->reminder,
            ]);
            ActivityLog::log('SCHEDULE_UPDATED', 'Jadwal "' . $this->title . '" diperbarui.', null);
            $this->dispatch('toast', message: 'Jadwal berhasil diperbarui!');
        } else {
            Schedule::create([
                'title' => $this->title,
                'date' => $this->date,
                'time' => $this->time ?: null,
                'type' => $this->type,
                'project_id' => $this->project_id,
                'description' => $this->description,
                'reminder' => $this->reminder,
                'created_by' => auth()->id(),
            ]);
            ActivityLog::log('SCHEDULE_CREATED', 'Jadwal baru "' . $this->title . '" ditambahkan ke kalender.', null);
            $this->dispatch('toast', message: 'Jadwal berhasil ditambahkan!');
        }

        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingScheduleId = null;
        $this->title = '';
        $this->date = Carbon::today()->format('Y-m-d');
        $this->time = '10:00';
        $this->type = 'MEETING';
        $this->project_id = null;
        $this->description = '';
        $this->reminder = 'H-1';
    }

    public function render()
    {
        // 1. Explicit Schedules
        $schedules = Schedule::with(['project', 'task', 'asset'])->orderBy('date')->orderBy('time')->get();

        // 2. Project Deadlines (Auto Synced)
        $projectDeadlines = Project::whereNotNull('deadline')->get();

        // 3. Task Deadlines (Auto Synced)
        $taskDeadlines = Task::whereNotNull('deadline')->get();

        // Combine into unified agenda
        $unifiedAgenda = collect();

        foreach ($schedules as $s) {
            $unifiedAgenda->push([
                'id' => $s->id,
                'is_custom' => true,
                'title' => $s->title,
                'date' => $s->date,
                'time' => $s->time ? substr($s->time, 0, 5) : 'All Day',
                'type' => $s->type,
                'description' => $s->description,
                'badge' => 'MEETING / EVENT',
                'color' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
            ]);
        }

        foreach ($projectDeadlines as $pd) {
            $unifiedAgenda->push([
                'id' => 'prj-' . $pd->id,
                'is_custom' => false,
                'title' => 'Deadline Project: ' . $pd->name,
                'date' => $pd->deadline,
                'time' => '23:59',
                'type' => 'PROJECT_DEADLINE',
                'description' => 'Target penyelesaian project ' . $pd->code,
                'badge' => 'PROJECT DEADLINE',
                'color' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            ]);
        }

        foreach ($taskDeadlines as $td) {
            $unifiedAgenda->push([
                'id' => 'tsk-' . $td->id,
                'is_custom' => false,
                'title' => 'Task Deadline: ' . $td->title,
                'date' => $td->deadline->toDateString(),
                'time' => $td->deadline->format('H:i'),
                'type' => 'TASK_DEADLINE',
                'description' => 'Project: ' . $td->project?->name,
                'badge' => 'TASK DEADLINE',
                'color' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            ]);
        }

        $sortedAgenda = $unifiedAgenda->sortBy('date')->groupBy(function($item) {
            return Carbon::parse($item['date'])->format('Y-m-d');
        });

        $projects = Project::where('status', 'ACTIVE')->get();

        return view('livewire.schedule-calendar', [
            'sortedAgenda' => $sortedAgenda,
            'projects' => $projects,
        ]);
    }
}
