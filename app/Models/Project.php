<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'client',
        'description',
        'start_date',
        'deadline',
        'status',
        'progress',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'progress' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('role_in_project');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function recalculateProgress(): void
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            $this->update(['progress' => 0]);
            return;
        }

        $sumProgress = $this->tasks()->sum('progress');
        $overallProgress = (int) round($sumProgress / $totalTasks);
        $this->update(['progress' => min(100, max(0, $overallProgress))]);
    }

    public function getRevenueAttribute(): float
    {
        return (float) $this->financialTransactions()
            ->where('type', 'INCOME')
            ->sum('amount');
    }

    public function getCostAttribute(): float
    {
        return (float) $this->financialTransactions()
            ->where('type', 'EXPENSE')
            ->sum('amount');
    }

    public function getProfitAttribute(): float
    {
        return $this->revenue - $this->cost;
    }
}
