<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'avatar',
        'phone',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SUPER_ADMIN';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (empty($this->permissions)) {
            // Default role-based permissions fallback
            $defaultRolePermissions = [
                'JOKI' => [
                    'task.view', 'progress.view', 'progress.update', 'schedule.view', 'notification.view'
                ],
                'CONTENT_CREATOR' => [
                    'project.view', 'task.view', 'progress.view', 'progress.update', 'schedule.view', 'notification.view'
                ],
                'DESIGNER' => [
                    'project.view', 'task.view', 'progress.view', 'progress.update', 'schedule.view', 'notification.view'
                ],
                'FRONTEND_DEV' => [
                    'project.view', 'task.view', 'progress.view', 'progress.update', 'schedule.view', 'notification.view'
                ],
                'BACKEND_DEV' => [
                    'project.view', 'task.view', 'progress.view', 'progress.update', 'schedule.view', 'notification.view'
                ],
                'IOT_ENGINEER' => [
                    'project.view', 'task.view', 'progress.view', 'progress.update', 'schedule.view', 'notification.view'
                ],
            ];

            return in_array($permission, $defaultRolePermissions[$this->role] ?? [
                'project.view', 'task.view', 'progress.view', 'progress.update', 'schedule.view', 'notification.view'
            ]);
        }

        return in_array($permission, $this->permissions);
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignments');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')->withPivot('role_in_project');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyProgressLog::class);
    }
}
