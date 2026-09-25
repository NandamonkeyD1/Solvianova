<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'date',
        'type',
        'category_id',
        'source_destination',
        'description',
        'amount',
        'financial_account_id',
        'target_account_id',
        'project_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    public function targetAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'target_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'category_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::saved(function (FinancialTransaction $transaction) {
            $transaction->account?->recalculateBalance();
            $transaction->targetAccount?->recalculateBalance();
        });

        static::deleted(function (FinancialTransaction $transaction) {
            $transaction->account?->recalculateBalance();
            $transaction->targetAccount?->recalculateBalance();
        });
    }
}
