<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\FinancialAccount;
use App\Models\FinancialTransaction;
use App\Models\Schedule;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\DailyProgressLog;
use Carbon\Carbon;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            // Super Admin Command Center Data
            $activeProjects = Project::where('status', 'ACTIVE')->get();
            $totalProjectsCount = Project::count();
            
            $tasks = Task::all();
            $totalTasksCount = $tasks->count();
            $overallProgress = $tasks->avg('progress') ? (int) round($tasks->avg('progress')) : 0;

            // Finance Summary
            $moneyIn = (float) FinancialTransaction::where('type', 'INCOME')->sum('amount');
            $moneyOut = (float) FinancialTransaction::where('type', 'EXPENSE')->sum('amount');
            $currentBalance = (float) FinancialAccount::sum('current_balance');
            $netProfit = $moneyIn - $moneyOut;

            // Action Required Counts
            $overdueTasksCount = Task::where('deadline', '<', Carbon::now())
                ->where('status', '!=', 'DONE')
                ->count();
            
            $waitingReviewTasksCount = Task::where('status', 'WAITING_REVIEW')->count();
            
            $blockedTasksCount = Task::where('status', 'BLOCKED')->count();

            // Projects Behind Schedule (Active projects with deadline < 7 days and progress < 50%)
            $projectsBehindSchedule = Project::where('status', 'ACTIVE')
                ->where('deadline', '<=', Carbon::now()->addDays(7))
                ->where('progress', '<', 50)
                ->count();

            // Members daily updates check
            $operationalUsers = User::where('role', '!=', 'SUPER_ADMIN')->where('status', 'ACTIVE')->get();
            $updatedTodayUserIds = DailyProgressLog::whereDate('date', Carbon::today())
                ->pluck('user_id')->toArray();
            
            $membersMissingUpdateCount = count($operationalUsers) - count(array_intersect($operationalUsers->pluck('id')->toArray(), $updatedTodayUserIds));

            // Today's Schedule & Notifications
            $todaySchedules = Schedule::whereDate('date', Carbon::today())->orderBy('time')->get();
            $unreadNotificationsCount = Notification::where('user_id', $user->id)->where('is_read', false)->count();

            // Activity Logs
            $recentActivities = ActivityLog::latest()->take(10)->get();

            return view('livewire.dashboard', [
                'isSuperAdmin' => true,
                'activeProjectsCount' => $activeProjects->count(),
                'totalProjectsCount' => $totalProjectsCount,
                'totalTasksCount' => $totalTasksCount,
                'overallProgress' => $overallProgress,
                'moneyIn' => $moneyIn,
                'moneyOut' => $moneyOut,
                'currentBalance' => $currentBalance,
                'netProfit' => $netProfit,
                'overdueTasksCount' => $overdueTasksCount,
                'waitingReviewTasksCount' => $waitingReviewTasksCount,
                'blockedTasksCount' => $blockedTasksCount,
                'projectsBehindSchedule' => $projectsBehindSchedule,
                'membersMissingUpdateCount' => max(0, $membersMissingUpdateCount),
                'operationalUsers' => $operationalUsers,
                'updatedTodayUserIds' => $updatedTodayUserIds,
                'todaySchedules' => $todaySchedules,
                'unreadNotificationsCount' => $unreadNotificationsCount,
                'recentActivities' => $recentActivities,
                'activeProjects' => $activeProjects,
            ]);
        } else {
            // Member / Joki Dashboard Data
            $myTasks = Task::whereHas('assignees', function($q) use ($user) {
                $q->where('users.id', $user->id);
            })->latest()->get();

            $todaySchedules = Schedule::whereDate('date', Carbon::today())->orderBy('time')->get();
            $myNotifications = Notification::where('user_id', $user->id)->latest()->take(5)->get();

            return view('livewire.dashboard', [
                'isSuperAdmin' => false,
                'myTasks' => $myTasks,
                'todaySchedules' => $todaySchedules,
                'myNotifications' => $myNotifications,
            ]);
        }
    }
}
