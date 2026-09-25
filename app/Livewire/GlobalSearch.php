<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Asset;
use App\Models\Schedule;

class GlobalSearch extends Component
{
    public bool $isOpen = false;
    public string $query = '';

    protected $listeners = ['open-global-search' => 'open'];

    public function open()
    {
        $this->isOpen = true;
        $this->query = '';
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        $projects = collect();
        $tasks = collect();
        $users = collect();
        $assets = collect();
        $schedules = collect();

        if (strlen(trim($this->query)) >= 2) {
            $q = '%' . $this->query . '%';

            $projects = Project::where('name', 'like', $q)
                ->orWhere('code', 'like', $q)
                ->orWhere('client', 'like', $q)
                ->take(5)->get();

            $tasks = Task::where('title', 'like', $q)
                ->orWhere('description', 'like', $q)
                ->take(5)->get();

            if (auth()->user()->isSuperAdmin()) {
                $users = User::where('name', 'like', $q)
                    ->orWhere('email', 'like', $q)
                    ->take(5)->get();

                $assets = Asset::where('name', 'like', $q)
                    ->orWhere('asset_code', 'like', $q)
                    ->take(5)->get();
            }

            $schedules = Schedule::where('title', 'like', $q)->take(5)->get();
        }

        return view('livewire.global-search', [
            'projects' => $projects,
            'tasks' => $tasks,
            'users' => $users,
            'assets' => $assets,
            'schedules' => $schedules,
        ]);
    }
}
