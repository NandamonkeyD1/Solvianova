<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\Project;
use App\Models\ActivityLog;
use Carbon\Carbon;

class FinanceManager extends Component
{
    public bool $showMoneyInModal = false;
    public bool $showMoneyOutModal = false;
    public bool $showTransferModal = false;
    public bool $showAccountModal = false;

    // Account Form fields
    public ?int $editingAccountId = null;
    public string $account_name = '';
    public string $account_type = 'BANK';
    public string $account_number = '';
    public float $account_opening_balance = 0;

    // Transaction Form fields
    public ?int $editingTransactionId = null;
    public string $date = '';
    public string $source_destination = '';
    public string $description = '';
    public float $amount = 0;
    public ?int $financial_account_id = null;
    public ?int $target_account_id = null;
    public string $account_name_input = ''; // Manual source account input field
    public string $target_account_name = ''; // Manual target account input field
    public ?int $category_id = null;
    public string $category_name = ''; // Manual category input field
    public ?int $project_id = null;

    protected $rules = [
        'amount' => 'required|numeric|gt:0',
        'financial_account_id' => 'required|exists:financial_accounts,id',
    ];

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->financial_account_id = FinancialAccount::first()?->id;
    }

    public function openAccountModal()
    {
        $this->editingAccountId = null;
        $this->account_name = '';
        $this->account_type = 'BANK';
        $this->account_number = '';
        $this->account_opening_balance = 0;
        $this->showAccountModal = true;
    }

    public function editAccount(int $id)
    {
        $acc = FinancialAccount::findOrFail($id);
        $this->editingAccountId = $acc->id;
        $this->account_name = $acc->name;
        $this->account_type = $acc->type;
        $this->account_number = $acc->account_number ?? '';
        $this->account_opening_balance = (float) $acc->opening_balance;
        $this->showAccountModal = true;
    }

    public function deleteAccount(int $id)
    {
        if (!auth()->user()->isSuperAdmin()) return;
        $acc = FinancialAccount::findOrFail($id);
        $name = $acc->name;
        $acc->delete();
        ActivityLog::log('FINANCE_ACCOUNT_DELETED', 'Rekening ' . $name . ' telah dihapus.', null);
        $this->dispatch('toast', message: 'Rekening berhasil dihapus!', type: 'error');
        $this->financial_account_id = FinancialAccount::first()?->id;
    }

    public function saveAccount()
    {
        $this->validate([
            'account_name' => 'required|min:2',
            'account_type' => 'required',
            'account_opening_balance' => 'required|numeric|gte:0',
        ]);

        if ($this->editingAccountId) {
            $acc = FinancialAccount::findOrFail($this->editingAccountId);
            $acc->update([
                'name' => $this->account_name,
                'type' => $this->account_type,
                'account_number' => $this->account_number,
                'opening_balance' => $this->account_opening_balance,
            ]);
            $acc->recalculateBalance();
            ActivityLog::log('FINANCE_ACCOUNT_UPDATED', 'Rekening ' . $acc->name . ' diperbarui.', null);
            $this->dispatch('toast', message: 'Rekening berhasil diperbarui!');
        } else {
            $acc = FinancialAccount::create([
                'name' => $this->account_name,
                'type' => $this->account_type,
                'account_number' => $this->account_number,
                'opening_balance' => $this->account_opening_balance,
                'current_balance' => $this->account_opening_balance,
            ]);
            ActivityLog::log('FINANCE_ACCOUNT_CREATED', 'Rekening baru ' . $acc->name . ' ditambahkan.', null);
            $this->dispatch('toast', message: 'Rekening baru berhasil ditambahkan!');
        }

        $this->showAccountModal = false;
        $this->financial_account_id = $acc->id;
    }

    public function openMoneyIn()
    {
        $this->resetForm();
        $firstCategory = FinancialCategory::where('type', 'INCOME')->first();
        $this->category_id = $firstCategory?->id;
        $this->category_name = $firstCategory?->name ?? 'Project Payment';
        $this->showMoneyInModal = true;
    }

    public function editTransaction(int $id)
    {
        $trx = FinancialTransaction::with('category')->findOrFail($id);
        $this->editingTransactionId = $trx->id;
        $this->date = $trx->date?->format('Y-m-d') ?? Carbon::today()->format('Y-m-d');
        $this->source_destination = $trx->source_destination ?? '';
        $this->description = $trx->description ?? '';
        $this->amount = (float) $trx->amount;
        $this->financial_account_id = $trx->financial_account_id;
        $this->target_account_id = $trx->target_account_id;
        $this->account_name_input = $trx->account?->name ?? '';
        $this->target_account_name = $trx->targetAccount?->name ?? '';
        $this->category_id = $trx->category_id;
        $this->category_name = $trx->category?->name ?? '';
        $this->project_id = $trx->project_id;

        if ($trx->type === 'INCOME') {
            $this->showMoneyInModal = true;
        } elseif ($trx->type === 'EXPENSE') {
            $this->showMoneyOutModal = true;
        } elseif ($trx->type === 'TRANSFER') {
            $this->showTransferModal = true;
        }
    }

    public function deleteTransaction(int $id)
    {
        if (!auth()->user()->isSuperAdmin()) return;

        $trx = FinancialTransaction::findOrFail($id);
        $code = $trx->transaction_code;
        $oldAccount = $trx->account;
        $oldTargetAccount = $trx->targetAccount;

        $trx->delete();

        // Recalculate balances
        $oldAccount?->recalculateBalance();
        $oldTargetAccount?->recalculateBalance();

        ActivityLog::log('FINANCE_DELETED', 'Super Admin menghapus transaksi ' . $code . '.', null);
        $this->dispatch('toast', message: 'Transaksi berhasil dihapus!', type: 'error');
    }

    public function saveMoneyIn()
    {
        // Process manual source account entry if typed
        if (!empty(trim($this->account_name_input))) {
            $acc = FinancialAccount::firstOrCreate(
                ['name' => trim($this->account_name_input)],
                ['type' => 'BANK', 'opening_balance' => 0, 'current_balance' => 0]
            );
            $this->financial_account_id = $acc->id;
        }

        $this->validate();

        // Process manual category entry
        if (!empty(trim($this->category_name))) {
            $cat = FinancialCategory::firstOrCreate([
                'name' => trim($this->category_name),
                'type' => 'INCOME',
            ]);
            $this->category_id = $cat->id;
        }

        if ($this->editingTransactionId) {
            $trx = FinancialTransaction::findOrFail($this->editingTransactionId);
            $oldAcc = $trx->account;

            $trx->update([
                'date' => $this->date,
                'category_id' => $this->category_id,
                'source_destination' => $this->source_destination,
                'description' => $this->description,
                'amount' => $this->amount,
                'financial_account_id' => $this->financial_account_id,
                'project_id' => $this->project_id,
            ]);

            $oldAcc?->recalculateBalance();
            $trx->account?->recalculateBalance();

            ActivityLog::log('FINANCE_UPDATED', 'Super Admin memperbarui transaksi pemasukan ' . $trx->transaction_code . '.', null);
            $this->dispatch('toast', message: 'Transaksi berhasil diperbarui!');
        } else {
            $code = 'INC-' . Carbon::now()->format('Ym') . '-' . sprintf('%03d', rand(1, 999));

            FinancialTransaction::create([
                'transaction_code' => $code,
                'date' => $this->date,
                'type' => 'INCOME',
                'category_id' => $this->category_id,
                'source_destination' => $this->source_destination,
                'description' => $this->description,
                'amount' => $this->amount,
                'financial_account_id' => $this->financial_account_id,
                'project_id' => $this->project_id,
                'created_by' => auth()->id(),
            ]);

            ActivityLog::log('FINANCE_INCOME', 'Super Admin mencatat pemasukan Rp ' . number_format($this->amount, 0, ',', '.') . ' (' . $this->source_destination . ').', null);
            $this->dispatch('toast', message: 'Transaksi Money In berhasil dicatat!');
        }

        $this->showMoneyInModal = false;
        $this->resetForm();
    }

    public function openMoneyOut()
    {
        $this->resetForm();
        $firstCategory = FinancialCategory::where('type', 'EXPENSE')->first();
        $this->category_id = $firstCategory?->id;
        $this->category_name = $firstCategory?->name ?? 'Operational Expenses';
        $this->showMoneyOutModal = true;
    }

    public function saveMoneyOut()
    {
        // Process manual source account entry if typed
        if (!empty(trim($this->account_name_input))) {
            $acc = FinancialAccount::firstOrCreate(
                ['name' => trim($this->account_name_input)],
                ['type' => 'BANK', 'opening_balance' => 0, 'current_balance' => 0]
            );
            $this->financial_account_id = $acc->id;
        }

        $this->validate();

        // Process manual category entry
        if (!empty(trim($this->category_name))) {
            $cat = FinancialCategory::firstOrCreate([
                'name' => trim($this->category_name),
                'type' => 'EXPENSE',
            ]);
            $this->category_id = $cat->id;
        }

        if ($this->editingTransactionId) {
            $trx = FinancialTransaction::findOrFail($this->editingTransactionId);
            $oldAcc = $trx->account;

            $trx->update([
                'date' => $this->date,
                'category_id' => $this->category_id,
                'source_destination' => $this->source_destination,
                'description' => $this->description,
                'amount' => $this->amount,
                'financial_account_id' => $this->financial_account_id,
                'project_id' => $this->project_id,
            ]);

            $oldAcc?->recalculateBalance();
            $trx->account?->recalculateBalance();

            ActivityLog::log('FINANCE_UPDATED', 'Super Admin memperbarui transaksi pengeluaran ' . $trx->transaction_code . '.', null);
            $this->dispatch('toast', message: 'Transaksi berhasil diperbarui!');
        } else {
            $code = 'EXP-' . Carbon::now()->format('Ym') . '-' . sprintf('%03d', rand(1, 999));

            FinancialTransaction::create([
                'transaction_code' => $code,
                'date' => $this->date,
                'type' => 'EXPENSE',
                'category_id' => $this->category_id,
                'source_destination' => $this->source_destination,
                'description' => $this->description,
                'amount' => $this->amount,
                'financial_account_id' => $this->financial_account_id,
                'project_id' => $this->project_id,
                'created_by' => auth()->id(),
            ]);

            ActivityLog::log('FINANCE_EXPENSE', 'Super Admin mencatat pengeluaran Rp ' . number_format($this->amount, 0, ',', '.') . ' (' . $this->description . ').', null);
            $this->dispatch('toast', message: 'Transaksi Money Out berhasil dicatat!');
        }

        $this->showMoneyOutModal = false;
        $this->resetForm();
    }

    public function openTransfer()
    {
        $this->resetForm();
        $this->showTransferModal = true;
    }

    public function saveTransfer()
    {
        // Process manual source account entry if typed
        if (!empty(trim($this->account_name_input))) {
            $acc = FinancialAccount::firstOrCreate(
                ['name' => trim($this->account_name_input)],
                ['type' => 'BANK', 'opening_balance' => 0, 'current_balance' => 0]
            );
            $this->financial_account_id = $acc->id;
        }

        // Process manual target account entry if typed
        if (!empty(trim($this->target_account_name))) {
            $targetAcc = FinancialAccount::firstOrCreate(
                ['name' => trim($this->target_account_name)],
                ['type' => 'BANK', 'opening_balance' => 0, 'current_balance' => 0]
            );
            $this->target_account_id = $targetAcc->id;
        }

        $this->validate([
            'amount' => 'required|numeric|gt:0',
            'financial_account_id' => 'required|exists:financial_accounts,id',
            'target_account_id' => 'required|different:financial_account_id|exists:financial_accounts,id',
        ]);

        if ($this->editingTransactionId) {
            $trx = FinancialTransaction::findOrFail($this->editingTransactionId);
            $oldAcc = $trx->account;
            $oldTarget = $trx->targetAccount;

            $trx->update([
                'date' => $this->date,
                'description' => $this->description,
                'amount' => $this->amount,
                'financial_account_id' => $this->financial_account_id,
                'target_account_id' => $this->target_account_id,
            ]);

            $oldAcc?->recalculateBalance();
            $oldTarget?->recalculateBalance();
            $trx->account?->recalculateBalance();
            $trx->targetAccount?->recalculateBalance();

            ActivityLog::log('FINANCE_UPDATED', 'Super Admin memperbarui transaksi transfer ' . $trx->transaction_code . '.', null);
            $this->dispatch('toast', message: 'Transfer berhasil diperbarui!');
        } else {
            $code = 'TRF-' . Carbon::now()->format('Ym') . '-' . sprintf('%03d', rand(1, 999));

            FinancialTransaction::create([
                'transaction_code' => $code,
                'date' => $this->date,
                'type' => 'TRANSFER',
                'category_id' => null,
                'source_destination' => 'Internal Transfer Rekening',
                'description' => $this->description ?: 'Transfer antar rekening perusahaan.',
                'amount' => $this->amount,
                'financial_account_id' => $this->financial_account_id,
                'target_account_id' => $this->target_account_id,
                'created_by' => auth()->id(),
            ]);

            ActivityLog::log('FINANCE_TRANSFER', 'Transfer antar rekening sebesar Rp ' . number_format($this->amount, 0, ',', '.') . '.', null);
            $this->dispatch('toast', message: 'Transfer antar rekening berhasil!');
        }

        $this->showTransferModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingTransactionId = null;
        $this->date = Carbon::today()->format('Y-m-d');
        $this->source_destination = '';
        $this->description = '';
        $this->amount = 0;
        $this->financial_account_id = FinancialAccount::first()?->id;
        $this->target_account_id = null;
        $this->account_name_input = '';
        $this->target_account_name = '';
        $this->category_id = null;
        $this->category_name = '';
        $this->project_id = null;
    }

    public function render()
    {
        $accounts = FinancialAccount::all();
        $incomeCategories = FinancialCategory::where('type', 'INCOME')->get();
        $expenseCategories = FinancialCategory::where('type', 'EXPENSE')->get();
        $projects = Project::where('status', 'ACTIVE')->get();

        $transactions = FinancialTransaction::with(['account', 'targetAccount', 'category', 'project'])->latest()->take(30)->get();

        $totalMoneyIn = FinancialTransaction::where('type', 'INCOME')->sum('amount');
        $totalMoneyOut = FinancialTransaction::where('type', 'EXPENSE')->sum('amount');
        $currentTotalBalance = FinancialAccount::sum('current_balance');
        $netProfit = $totalMoneyIn - $totalMoneyOut;

        return view('livewire.finance-manager', [
            'accounts' => $accounts,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'projects' => $projects,
            'transactions' => $transactions,
            'totalMoneyIn' => $totalMoneyIn,
            'totalMoneyOut' => $totalMoneyOut,
            'currentTotalBalance' => $currentTotalBalance,
            'netProfit' => $netProfit,
        ]);
    }
}
