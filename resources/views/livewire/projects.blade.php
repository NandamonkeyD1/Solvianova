<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Project Control Center</h1>
            <p class="text-xs text-slate-400">Kelola dan pantau seluruh project aktif, tim, deadline, serta keuntungan finansial.</p>
        </div>
        @if(auth()->user()->hasPermission('project.create'))
        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/25 transition-all flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Project Baru
        </button>
        @endif
    </div>

    <!-- Filters & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 p-4 rounded-2xl border border-slate-800">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto pb-2 sm:pb-0 text-xs font-semibold">
            @foreach(['ALL', 'PLANNING', 'ACTIVE', 'ON_HOLD', 'COMPLETED', 'CANCELLED'] as $st)
                <button wire:click="$set('filterStatus', '{{ $st }}')"
                        class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap {{ $filterStatus === $st ? 'bg-brand-600 text-white font-bold' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    {{ str_replace('_', ' ', $st) }}
                </button>
            @endforeach
        </div>

        <!-- Search Input -->
        <div class="w-full sm:w-64">
            <input type="text" wire:model.live.debounce.250ms="search" placeholder="Cari nama / kode project..." class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-3.5 py-2 text-xs text-slate-100 placeholder-slate-500 focus:outline-none">
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $p)
        <div class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-3xl p-6 shadow-xl flex flex-col justify-between space-y-4 transition-all group">
            
            <div class="space-y-3">
                <!-- Status Badge & Code -->
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono text-brand-400 font-bold">{{ $p->code }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold 
                        {{ $p->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                        {{ $p->status === 'COMPLETED' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : '' }}
                        {{ $p->status === 'PLANNING' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
                        {{ $p->status === 'ON_HOLD' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : '' }}">
                        {{ $p->status }}
                    </span>
                </div>

                <div>
                    <h3 class="font-extrabold text-base text-white group-hover:text-brand-300 transition-colors">{{ $p->name }}</h3>
                    <p class="text-xs text-slate-400 line-clamp-2 mt-1">{{ $p->description ?? 'Tidak ada deskripsi.' }}</p>
                </div>

                <div class="text-xs text-slate-400 space-y-1 pt-2 border-t border-slate-800">
                    <div class="flex justify-between">
                        <span>Client:</span>
                        <span class="font-semibold text-slate-200">{{ $p->client ?? 'Internal' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Deadline:</span>
                        <span class="font-semibold text-slate-200">{{ $p->deadline?->format('d M Y') ?? '-' }}</span>
                    </div>
                </div>

                <!-- Team Avatar Stack -->
                <div class="flex items-center justify-between pt-2">
                    <span class="text-[11px] text-slate-400 font-medium">Tim Project:</span>
                    <div class="flex -space-x-2">
                        @foreach($p->members->take(4) as $m)
                            <div title="{{ $m->name }} ({{ str_replace('_', ' ', $m->role) }})" class="w-7 h-7 rounded-full bg-slate-800 border-2 border-slate-900 font-bold text-[10px] text-slate-200 flex items-center justify-center">
                                {{ strtoupper(substr($m->name, 0, 2)) }}
                            </div>
                        @endforeach
                        @if($p->members->count() > 4)
                            <div class="w-7 h-7 rounded-full bg-slate-800 border-2 border-slate-900 text-[10px] text-slate-400 font-bold flex items-center justify-center">
                                +{{ $p->members->count() - 4 }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Progress Bar & Actions -->
            <div class="space-y-3 pt-4 border-t border-slate-800">
                <div class="space-y-1">
                    <div class="flex justify-between text-xs font-bold">
                        <span class="text-slate-400">Calculated Progress</span>
                        <span class="text-brand-400">{{ $p->progress }}%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full transition-all duration-300" style="width: {{ $p->progress }}%"></div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button wire:click="openDetail({{ $p->id }})" class="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs text-center transition-all">
                        Detail & Finance
                    </button>

                    @if(auth()->user()->isSuperAdmin())
                        <button wire:click="editProject({{ $p->id }})" title="Edit Project" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button wire:confirm="Hapus project ini beserta seluruh task-nya?" wire:click="deleteProject({{ $p->id }})" title="Hapus Project" class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @if($p->status !== 'COMPLETED')
                        <button wire:click="openClosure({{ $p->id }})" title="Selesaikan Project" class="p-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 rounded-xl text-xs">
                            ✓ Close
                        </button>
                        @endif
                    @endif
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-full bg-slate-900 border border-slate-800 rounded-3xl p-12 text-center space-y-3">
            <svg class="w-12 h-12 text-slate-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <h3 class="font-bold text-slate-300">Belum Ada Project</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Buat project pertama untuk mulai mengatur pekerjaan, tugas tim, dan arus keuangan.</p>
            @if(auth()->user()->hasPermission('project.create'))
            <button wire:click="openCreateModal" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl inline-block mt-2">
                + Buat Project Pertama
            </button>
            @endif
        </div>
        @endforelse
    </div>

    <!-- CREATE / EDIT PROJECT MODAL -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-xl p-6 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <h3 class="font-extrabold text-lg text-white">{{ $editingProjectId ? 'Edit Project' : 'Buat Project Baru' }}</h3>
                <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveProject" class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Project *</label>
                        <input type="text" wire:model="name" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-slate-100 focus:border-indigo-500 focus:outline-none">
                        @error('name') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode Project *</label>
                        <input type="text" wire:model="code" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-slate-100 font-mono focus:border-indigo-500 focus:outline-none">
                        @error('code') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Client</label>
                        <input type="text" wire:model="client" placeholder="Contoh: PT Agro Tech" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-slate-100 focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Status</label>
                        <select wire:model="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-slate-100 focus:border-indigo-500 focus:outline-none">
                            <option value="PLANNING">PLANNING</option>
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="ON_HOLD">ON HOLD</option>
                            <option value="COMPLETED">COMPLETED</option>
                            <option value="CANCELLED">CANCELLED</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tanggal Mulai</label>
                        <input type="date" wire:model="start_date" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-slate-100 focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Deadline Project</label>
                        <input type="date" wire:model="deadline" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-slate-100 focus:border-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi Project</label>
                    <textarea wire:model="description" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:border-indigo-500 focus:outline-none"></textarea>
                </div>

                <!-- Team Assignment Selection (Requirements 6) -->
                <div>
                    <label class="block font-semibold text-slate-300 mb-2">Pilih Anggota Tim Project</label>
                    <div class="grid grid-cols-2 gap-2 max-h-36 overflow-y-auto p-2 bg-slate-950 border border-slate-800 rounded-xl">
                        @foreach($users as $u)
                        <label class="flex items-center gap-2 p-1.5 hover:bg-slate-900 rounded-lg cursor-pointer">
                            <input type="checkbox" wire:model="selectedTeamMembers" value="{{ $u->id }}" class="rounded border-slate-700 bg-slate-900 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="font-bold text-white block">{{ $u->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ str_replace('_', ' ', $u->role) }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl shadow-lg shadow-brand-600/25">Simpan Project</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- PROJECT DETAIL & FINANCIAL P&L MODAL -->
    @if($showDetailModal && $selectedProject)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-3xl p-6 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <span class="text-xs font-mono text-brand-400 font-bold">{{ $selectedProject->code }}</span>
                    <h3 class="font-black text-xl text-white">{{ $selectedProject->name }}</h3>
                </div>
                <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-white font-bold text-xl">&times;</button>
            </div>

            <!-- Financial P&L Breakdown (Requirements 24) -->
            <div class="grid grid-cols-3 gap-3 p-4 bg-slate-950 border border-slate-800 rounded-2xl text-center">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Project Revenue</span>
                    <div class="text-base font-black text-emerald-400">Rp {{ number_format($selectedProject->revenue, 0, ',', '.') }}</div>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Project Cost</span>
                    <div class="text-base font-black text-red-400">Rp {{ number_format($selectedProject->cost, 0, ',', '.') }}</div>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Project Net Profit</span>
                    <div class="text-base font-black {{ $selectedProject->profit >= 0 ? 'text-indigo-400' : 'text-red-400' }}">
                        Rp {{ number_format($selectedProject->profit, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Task Breakdown -->
            <div class="space-y-3">
                <h4 class="font-extrabold text-sm text-white border-b border-slate-800 pb-2">Tasks in Project ({{ $selectedProject->tasks->count() }})</h4>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @forelse($selectedProject->tasks as $tk)
                    <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-white block">{{ $tk->title }}</span>
                            <span class="text-[10px] text-slate-400">Assignee: {{ $tk->assignees->pluck('name')->join(', ') ?: 'Unassigned' }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-slate-800 text-slate-300">
                            {{ str_replace('_', ' ', $tk->status) }} ({{ $tk->progress }}%)
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 py-2">Belum ada task pada project ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- Team Members -->
            <div class="space-y-3">
                <h4 class="font-extrabold text-sm text-white border-b border-slate-800 pb-2">Tim Komposisi Project</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                    @foreach($selectedProject->members as $mb)
                    <div class="p-2.5 bg-slate-950 border border-slate-800 rounded-xl flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-brand-600 font-bold text-[10px] text-white flex items-center justify-center">
                            {{ strtoupper(substr($mb->name, 0, 2)) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-white text-xs">{{ $mb->name }}</span>
                            <span class="text-[10px] text-slate-400">{{ str_replace('_', ' ', $mb->role) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- PROJECT CLOSURE SUMMARY MODAL (Requirements 55) -->
    @if($showClosureModal && $selectedProject)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg p-6 shadow-2xl space-y-6">
            <div class="text-center space-y-2 border-b border-slate-800 pb-4">
                <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-400 mx-auto flex items-center justify-center text-xl font-bold">
                    ✓
                </div>
                <h3 class="font-black text-xl text-white">Konfirmasi Project Closure</h3>
                <p class="text-xs text-slate-400">Selesaikan project "{{ $selectedProject->name }}" dan kunci statistik akhir.</p>
            </div>

            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Total Tasks:</span>
                    <span class="font-bold text-white">{{ $selectedProject->tasks->count() }} Tasks</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Tasks Done:</span>
                    <span class="font-bold text-emerald-400">{{ $selectedProject->tasks->where('status', 'DONE')->count() }} Tasks</span>
                </div>
                <div class="flex justify-between border-t border-slate-800 pt-2">
                    <span class="text-slate-400">Total Revenue:</span>
                    <span class="font-bold text-emerald-400">Rp {{ number_format($selectedProject->revenue, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Total Cost:</span>
                    <span class="font-bold text-red-400">Rp {{ number_format($selectedProject->cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-800 pt-2 font-bold text-sm">
                    <span class="text-white">Net Profit:</span>
                    <span class="{{ $selectedProject->profit >= 0 ? 'text-indigo-400' : 'text-red-400' }}">
                        Rp {{ number_format($selectedProject->profit, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button wire:click="$set('showClosureModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold text-xs rounded-xl">Batal</button>
                <button wire:click="confirmCloseProject" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20">
                    Konfirmasi Completed Project
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
