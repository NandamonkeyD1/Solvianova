<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Financial Control & Cashflow</h1>
            <p class="text-xs text-slate-400">Pusat kendali arus uang masuk, keluar, rekening perusahaan, dan profitabilitas project.</p>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="openAccountModal" class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 flex items-center gap-1.5">
                + Tambah Rekening
            </button>
            <button wire:click="openMoneyIn" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 flex items-center gap-1.5">
                + Money In (Pemasukan)
            </button>
            <button wire:click="openMoneyOut" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-rose-600/20 flex items-center gap-1.5">
                - Money Out (Pengeluaran)
            </button>
            <button wire:click="openTransfer" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl border border-slate-700">
                ⇄ Transfer Rekening
            </button>
        </div>
    </div>

    <!-- Overall Finance Cards (Requirements 23 & 46) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl shadow-xl space-y-1">
            <span class="text-xs font-semibold text-slate-400">Current Balance</span>
            <div class="text-2xl font-black text-emerald-400">Rp {{ number_format($currentTotalBalance, 0, ',', '.') }}</div>
            <span class="text-[10px] text-slate-500">Saldo Akhir Seluruh Account</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl shadow-xl space-y-1">
            <span class="text-xs font-semibold text-slate-400">Total Money In</span>
            <div class="text-2xl font-black text-emerald-400">+ Rp {{ number_format($totalMoneyIn, 0, ',', '.') }}</div>
            <span class="text-[10px] text-slate-500">Pemasukan Operasional & Project</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl shadow-xl space-y-1">
            <span class="text-xs font-semibold text-slate-400">Total Money Out</span>
            <div class="text-2xl font-black text-red-400">- Rp {{ number_format($totalMoneyOut, 0, ',', '.') }}</div>
            <span class="text-[10px] text-slate-500">Gaji, Infrastructure, Operasional</span>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl shadow-xl space-y-1">
            <span class="text-xs font-semibold text-slate-400">Company Net Profit</span>
            <div class="text-2xl font-black {{ $netProfit >= 0 ? 'text-indigo-400' : 'text-red-400' }}">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-slate-500">Money In - Money Out</span>
        </div>
    </div>

    <!-- Financial Accounts Overview (Requirements 22) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="font-extrabold text-base text-white">Financial Accounts & Balances</h3>
            <button wire:click="openAccountModal" class="px-3 py-1.5 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 font-bold text-xs rounded-xl border border-indigo-500/30 flex items-center gap-1">
                + Tambah Rekening Baru
            </button>
        </div>

        @if($accounts->isEmpty())
        <div class="p-6 bg-slate-950 border border-dashed border-slate-800 rounded-2xl text-center space-y-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 mx-auto flex items-center justify-center font-bold">
                💳
            </div>
            <div class="space-y-1">
                <p class="text-sm font-bold text-white">Belum Ada Rekening Terdaftar</p>
                <p class="text-xs text-slate-400">Buat rekening bank, e-wallet, atau kas fisik secara manual untuk mulai mencatat keuangan.</p>
            </div>
            <button wire:click="openAccountModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl inline-flex items-center gap-1.5 shadow-lg shadow-indigo-600/20">
                + Buat Rekening Pertama Sekarang
            </button>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($accounts as $acc)
            <div class="p-4 bg-slate-950 border border-slate-800 rounded-2xl space-y-2 relative group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white">{{ $acc->name }}</span>
                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">{{ $acc->type }}</span>
                </div>
                <div class="text-base font-black text-emerald-400">
                    Rp {{ number_format($acc->current_balance, 0, ',', '.') }}
                </div>
                <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono">
                    <span>No: {{ $acc->account_number ?? '-' }}</span>
                    @if(auth()->user()->isSuperAdmin())
                    <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                        <button wire:click="editAccount({{ $acc->id }})" title="Edit Rekening" class="p-1 hover:text-indigo-400"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                        <button wire:confirm="Hapus rekening {{ $acc->name }}?" wire:click="deleteAccount({{ $acc->id }})" title="Hapus Rekening" class="p-1 hover:text-red-400"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Financial Transactions Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <h3 class="font-extrabold text-base text-white border-b border-slate-800 pb-3">History Transaksi Keuangan</h3>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left text-slate-300">
                <thead class="text-[10px] font-extrabold uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="p-3">Kode & Tanggal</th>
                        <th class="p-3">Tipe & Kategori</th>
                        <th class="p-3">Deskripsi / Sumber</th>
                        <th class="p-3">Account</th>
                        <th class="p-3">Project</th>
                        <th class="p-3 text-right">Nominal (Amount)</th>
                        @if(auth()->user()->isSuperAdmin())
                        <th class="p-3 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($transactions as $trx)
                    <tr class="hover:bg-slate-950/60 transition-all">
                        <td class="p-3">
                            <span class="font-mono font-bold text-white block">{{ $trx->transaction_code }}</span>
                            <span class="text-[10px] text-slate-400">{{ $trx->date?->format('d M Y') }}</span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded font-extrabold text-[10px] 
                                {{ $trx->type === 'INCOME' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                                {{ $trx->type === 'EXPENSE' ? 'bg-rose-500/10 text-rose-400' : '' }}
                                {{ $trx->type === 'TRANSFER' ? 'bg-sky-500/10 text-sky-400' : '' }}">
                                {{ $trx->type }}
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $trx->category?->name ?? 'Internal Transfer' }}</span>
                        </td>
                        <td class="p-3">
                            <span class="font-semibold text-slate-200 block">{{ $trx->description }}</span>
                            <span class="text-[10px] text-slate-400">{{ $trx->source_destination }}</span>
                        </td>
                        <td class="p-3">
                            <span class="font-bold text-slate-300">{{ $trx->account?->name }}</span>
                            @if($trx->targetAccount)
                                <span class="text-[10px] text-slate-400 block">&rarr; {{ $trx->targetAccount->name }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-slate-400">
                            {{ $trx->project?->name ?? '-' }}
                        </td>
                        <td class="p-3 text-right font-black {{ $trx->type === 'INCOME' ? 'text-emerald-400' : ($trx->type === 'EXPENSE' ? 'text-red-400' : 'text-slate-300') }}">
                            {{ $trx->type === 'INCOME' ? '+' : ($trx->type === 'EXPENSE' ? '-' : '') }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button wire:click="editTransaction({{ $trx->id }})" title="Edit Transaksi" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:confirm="Hapus transaksi keuangan ini?" wire:click="deleteTransaction({{ $trx->id }})" title="Hapus Transaksi" class="p-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MONEY IN MODAL (WITH MANUAL CATEGORY INPUT) -->
    @if($showMoneyInModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-emerald-400">+ Catat Money In (Pemasukan)</h3>
                <button wire:click="$set('showMoneyInModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveMoneyIn" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tanggal *</label>
                        <input type="date" wire:model="date" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nominal (Rp) *</label>
                        <input type="number" wire:model="amount" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none font-bold text-emerald-400">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Sumber Pemasukan (Source)</label>
                    <input type="text" wire:model="source_destination" placeholder="Contoh: PT Agro Tech Nusantara" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Masuk Rekening *</label>
                        @if($accounts->isEmpty())
                            <div class="p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 text-[11px] flex flex-col gap-1">
                                <span>Belum ada rekening!</span>
                                <button type="button" wire:click="openAccountModal" class="px-2 py-1 bg-indigo-600 text-white font-bold rounded-lg text-[10px]">+ Buat Rekening</button>
                            </div>
                        @else
                            <select wire:model="financial_account_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kategori (Isi Manual) *</label>
                        <input type="text" wire:model="category_name" list="income-categories-list" placeholder="Isi manual kategori..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                        <datalist id="income-categories-list">
                            @foreach($incomeCategories as $ic)
                                <option value="{{ $ic->name }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Project Terkait (Opsional)</label>
                    <select wire:model="project_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                        <option value="">- Tidak Dikaitkan Project -</option>
                        @foreach($projects as $pj)
                            <option value="{{ $pj->id }}">{{ $pj->name }} ({{ $pj->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi Pemasukan</label>
                    <textarea wire:model="description" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showMoneyInModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl">Simpan Pemasukan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MONEY OUT MODAL (WITH MANUAL CATEGORY INPUT) -->
    @if($showMoneyOutModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-rose-400">- Catat Money Out (Pengeluaran)</h3>
                <button wire:click="$set('showMoneyOutModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveMoneyOut" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tanggal *</label>
                        <input type="date" wire:model="date" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nominal (Rp) *</label>
                        <input type="number" wire:model="amount" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none font-bold text-rose-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Sumber Rekening *</label>
                        @if($accounts->isEmpty())
                            <div class="p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 text-[11px] flex flex-col gap-1">
                                <span>Belum ada rekening!</span>
                                <button type="button" wire:click="openAccountModal" class="px-2 py-1 bg-indigo-600 text-white font-bold rounded-lg text-[10px]">+ Buat Rekening</button>
                            </div>
                        @else
                            <select wire:model="financial_account_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kategori (Isi Manual) *</label>
                        <input type="text" wire:model="category_name" list="expense-categories-list" placeholder="Isi manual kategori..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                        <datalist id="expense-categories-list">
                            @foreach($expenseCategories as $ec)
                                <option value="{{ $ec->name }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Project Terkait (Opsional)</label>
                    <select wire:model="project_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                        <option value="">- Operasional Perusahaan -</option>
                        @foreach($projects as $pj)
                            <option value="{{ $pj->id }}">{{ $pj->name }} ({{ $pj->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Penerima / Keterangan</label>
                    <input type="text" wire:model="source_destination" placeholder="Penerima atau Vendor..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi Pengeluaran</label>
                    <textarea wire:model="description" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showMoneyOutModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- TRANSFER MODAL -->
    @if($showTransferModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-sky-400">⇄ Transfer Antar Rekening Perusahaan</h3>
                <button wire:click="$set('showTransferModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveTransfer" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Dari Rekening (Source) *</label>
                        @if($accounts->count() > 0)
                            <select wire:model="financial_account_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none mb-1.5">
                                <option value="">-- Pilih Rekening --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (Rp {{ number_format($acc->current_balance, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        @endif
                        <input type="text" wire:model="account_name_input" list="all-accounts-list" placeholder="Atau ketik nama rekening..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Ke Rekening (Target Manual) *</label>
                        @if($accounts->count() > 0)
                            <select wire:model="target_account_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none mb-1.5">
                                <option value="">-- Pilih Rekening --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <input type="text" wire:model="target_account_name" list="all-accounts-list" placeholder="Atau ketik nama target manual..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none text-xs">
                        <datalist id="all-accounts-list">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->name }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nominal Transfer (Rp) *</label>
                    <input type="number" wire:model="amount" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none font-bold text-sky-400">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Catatan Transfer</label>
                    <input type="text" wire:model="description" placeholder="Alokasi kas operasional..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showTransferModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-xl">Eksekusi Transfer</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- CREATE / EDIT ACCOUNT MODAL (REKENING BUAT MANUAL) -->
    @if($showAccountModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-indigo-400">
                    {{ $editingAccountId ? 'Edit Rekening Perusahaan' : '+ Buat Rekening Baru (Manual)' }}
                </h3>
                <button wire:click="$set('showAccountModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveAccount" class="space-y-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Rekening / Akun *</label>
                    <input type="text" wire:model="account_name" placeholder="Contoh: BCA Operasional, Kas Kecil, GoPay Admin" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                    @error('account_name') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tipe Rekening *</label>
                        <select wire:model="account_type" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            <option value="BANK">Bank Transfer</option>
                            <option value="E_WALLET">E-Wallet (GoPay, OVO, QRIS)</option>
                            <option value="CASH">Kas Tunai (Cash)</option>
                        </select>
                        @error('account_type') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">No. Rekening / Akun</label>
                        <input type="text" wire:model="account_number" placeholder="1234567890" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Saldo Awal (Opening Balance) *</label>
                    <input type="number" wire:model="account_opening_balance" placeholder="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 font-bold text-emerald-400 focus:outline-none">
                    @error('account_opening_balance') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showAccountModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/20">
                        {{ $editingAccountId ? 'Simpan Perubahan' : 'Buat Rekening' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
