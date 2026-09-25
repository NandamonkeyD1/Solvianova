<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DailyProgressLog;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DailyProgress extends Component
{
    public string $date = '';
    public ?int $project_id = null;
    public ?int $task_id = null;
    public int $progress = 50;
    public string $work_done = '';
    public string $next_plan = '';
    public string $blocker = '';
    public string $attachment_url = '';

    protected $rules = [
        'work_done' => 'required|min:5',
    ];

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->project_id = Project::first()?->id;
    }

    public function saveDailyProgress()
    {
        $this->validate();

        DailyProgressLog::create([
            'user_id' => auth()->id(),
            'date' => $this->date,
            'project_id' => $this->project_id,
            'task_id' => $this->task_id,
            'progress' => $this->progress,
            'work_done' => $this->work_done,
            'next_plan' => $this->next_plan,
            'blocker' => $this->blocker,
            'attachment_url' => $this->attachment_url,
        ]);

        ActivityLog::log('DAILY_LOG_SUBMITTED', auth()->user()->name . ' mengirimkan laporan harian aktivitas.', null);
        $this->dispatch('toast', message: 'Laporan harian berhasil dikirim!');

        $this->work_done = '';
        $this->next_plan = '';
        $this->blocker = '';
        $this->attachment_url = '';
    }

    public function render()
    {
        $user = auth()->user();

        // All operational team members
        $operationalUsers = User::where('role', '!=', 'SUPER_ADMIN')->where('status', 'ACTIVE')->get();
        $updatedTodayUserIds = DailyProgressLog::whereDate('date', Carbon::today())
            ->pluck('user_id')->toArray();

        // Recent Daily Logs
        $query = DailyProgressLog::with(['user', 'project', 'task'])->latest();
        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }
        $dailyLogs = $query->take(20)->get();

        $projects = Project::where('status', 'ACTIVE')->get();
        $tasks = Task::where('status', '!=', 'DONE')->get();

        return view('livewire.daily-progress', [
            'operationalUsers' => $operationalUsers,
            'updatedTodayUserIds' => $updatedTodayUserIds,
            'dailyLogs' => $dailyLogs,
            'projects' => $projects,
            'tasks' => $tasks,
        ]);
    }
}
