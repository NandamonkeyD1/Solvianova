<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Assets & Infrastructure Management</h1>
            <p class="text-xs text-slate-400">Inventaris hardware, IoT equipment, server VPS, domain, SSL, dan lisensi software.</p>
        </div>
        @if(auth()->user()->hasPermission('asset.create'))
        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/25 transition-all flex items-center justify-center gap-2">
            + Tambah Aset / Infra
        </button>
        @endif
    </div>

    <!-- Expiry Alert Banner (Requirements 29) -->
    @if($expiringSoonAssets->isNotEmpty())
    <div class="bg-amber-500/10 border border-amber-500/30 rounded-3xl p-5 space-y-3">
        <div class="flex items-center gap-2 text-amber-400 font-extrabold text-sm">
            <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Peringatan Expiry Aset / Infrastruktur (&le; 30 Hari)
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($expiringSoonAssets as $exp)
            <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-2xl flex items-center justify-between text-xs">
                <div>
                    <span class="font-bold text-white block">{{ $exp->name }}</span>
                    <span class="text-[10px] text-slate-400">PIC: {{ $exp->responsibleUser?->name ?? 'Unassigned' }}</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold text-amber-400 block">{{ $exp->expiry_date?->format('d M Y') }}</span>
                    <span class="text-[9px] text-slate-500">{{ $exp->expiry_date?->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filter Tabs -->
    <div class="flex items-center gap-1 overflow-x-auto pb-2 border-b border-slate-800 text-xs font-semibold">
        @foreach(['ALL', 'HARDWARE', 'IOT', 'INFRASTRUCTURE', 'SOFTWARE'] as $tp)
            <button wire:click="$set('filterType', '{{ $tp }}')" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $filterType === $tp ? 'bg-brand-600 text-white font-bold shadow-md' : 'text-slate-400 hover:bg-slate-800' }}">
                {{ $tp }}
            </button>
        @endforeach
    </div>

    <!-- Assets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($assets as $ast)
        <div class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-3xl p-6 shadow-xl space-y-4 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono text-brand-400 font-bold">{{ $ast->asset_code }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-800 text-slate-300">
                    {{ $ast->type }}
                </span>
            </div>

            <div>
                <h3 class="font-extrabold text-base text-white">{{ $ast->name }}</h3>
                <p class="text-xs text-slate-400 line-clamp-2 mt-1">{{ $ast->serial_spec ?? 'Tanpa rincian spesifikasi.' }}</p>
            </div>

            <div class="text-xs text-slate-400 space-y-1.5 pt-3 border-t border-slate-800">
                <div class="flex justify-between">
                    <span>Penanggung Jawab (PIC):</span>
                    <span class="font-semibold text-slate-200">{{ $ast->responsibleUser?->name ?? 'Solvia Office' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Project Terkait:</span>
                    <span class="font-semibold text-slate-200">{{ $ast->project?->code ?? '-' }}</span>
                </div>
                @if($ast->expiry_date)
                <div class="flex justify-between">
                    <span>Expired Date:</span>
                    <span class="font-bold text-amber-400">{{ $ast->expiry_date->format('d M Y') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span>Biaya Perolehan / Ops:</span>
                    <span class="font-bold text-emerald-400">Rp {{ number_format($ast->cost, 0, ',', '.') }}</span>
                </div>
            </div>

            @if(auth()->user()->isSuperAdmin())
            <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
                <button wire:click="editAsset({{ $ast->id }})" class="flex-1 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs transition-all text-center">
                    Edit Aset
                </button>
                <button wire:confirm="Hapus inventaris aset ini?" wire:click="deleteAsset({{ $ast->id }})" class="py-1.5 px-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold rounded-xl text-xs transition-all">
                    Hapus
                </button>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-xs text-slate-500">
            Belum ada inventaris aset terdaftar.
        </div>
        @endforelse
    </div>

    <!-- CREATE ASSET MODAL -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-white">Tambah Aset / Infrastruktur</h3>
                <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveAsset" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Aset / Resource *</label>
                        <input type="text" wire:model="name" placeholder="MacBook Pro / Domain / VPS" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                        @error('name') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode Aset *</label>
                        <input type="text" wire:model="asset_code" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 font-mono focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tipe Resource *</label>
                        <select wire:model="type" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            <option value="HARDWARE">HARDWARE</option>
                            <option value="IOT">IOT</option>
                            <option value="INFRASTRUCTURE">INFRASTRUCTURE (VPS/Domain/SSL)</option>
                            <option value="SOFTWARE">SOFTWARE / SUBSCRIPTION</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">PIC / Penanggung Jawab</label>
                        <select wire:model="responsible_user_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            <option value="">- Solvia Office -</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ str_replace('_', ' ', $u->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Biaya (Cost Rp)</label>
                        <input type="number" wire:model="cost" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tanggal Expiry (Jika Ada)</label>
                        <input type="date" wire:model="expiry_date" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Spesifikasi / Detail Serial Number</label>
                    <textarea wire:model="serial_spec" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 text-white font-bold rounded-xl">Simpan Aset</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
