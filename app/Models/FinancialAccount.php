<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'account_number',
        'opening_balance',
        'current_balance',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function recalculateBalance(): void
    {
        $moneyIn = FinancialTransaction::where('financial_account_id', $this->id)
            ->where('type', 'INCOME')
            ->sum('amount');

        $moneyOut = FinancialTransaction::where('financial_account_id', $this->id)
            ->where('type', 'EXPENSE')
            ->sum('amount');

        $transfersOut = FinancialTransaction::where('financial_account_id', $this->id)
            ->where('type', 'TRANSFER')
            ->sum('amount');

        $transfersIn = FinancialTransaction::where('target_account_id', $this->id)
            ->where('type', 'TRANSFER')
            ->sum('amount');

        $newBalance = $this->opening_balance + $moneyIn + $transfersIn - $moneyOut - $transfersOut;
        $this->update(['current_balance' => $newBalance]);
    }
}
