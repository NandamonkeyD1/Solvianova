<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Dashboard;
use App\Livewire\Projects;
use App\Livewire\Tasks;
use App\Livewire\DailyProgress;
use App\Livewire\ScheduleCalendar;
use App\Livewire\NotificationsCenter;
use App\Livewire\FinanceManager;
use App\Livewire\AssetManager;
use App\Livewire\UserManager;
use App\Http\Middleware\EnsurePermission;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/', function() {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/daily-progress', DailyProgress::class)->name('daily-progress');

    // Permission Scoped Routes
    Route::middleware(EnsurePermission::class . ':project.view')->group(function () {
        Route::get('/projects', Projects::class)->name('projects');
    });

    Route::middleware(EnsurePermission::class . ':task.view')->group(function () {
        Route::get('/tasks', Tasks::class)->name('tasks');
    });

    Route::middleware(EnsurePermission::class . ':schedule.view')->group(function () {
        Route::get('/schedule', ScheduleCalendar::class)->name('schedule');
    });

    Route::middleware(EnsurePermission::class . ':notification.view')->group(function () {
        Route::get('/notifications', NotificationsCenter::class)->name('notifications');
    });

    Route::middleware(EnsurePermission::class . ':finance.view')->group(function () {
        Route::get('/finance', FinanceManager::class)->name('finance');
    });

    Route::middleware(EnsurePermission::class . ':asset.view')->group(function () {
        Route::get('/assets', AssetManager::class)->name('assets');
    });

    Route::middleware(EnsurePermission::class . ':user.view')->group(function () {
        Route::get('/users', UserManager::class)->name('users');
    });

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
