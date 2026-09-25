<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Schedule & Company Calendar</h1>
            <p class="text-xs text-slate-400">Jadwal meeting, event, serta deadline project & task yang tersinkronisasi otomatis.</p>
        </div>
        <button wire:click="openCreateModal" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/25 transition-all flex items-center justify-center gap-2">
            + Tambah Schedule
        </button>
    </div>

    <!-- Agenda Timeline View (Requirements 17 & 44) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
        <h3 class="font-extrabold text-base text-white border-b border-slate-800 pb-3">Agenda & Synced Deadlines</h3>

        <div class="space-y-6">
            @forelse($sortedAgenda as $dateStr => $events)
            @php $cDate = \Carbon\Carbon::parse($dateStr); @endphp
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-xs font-black text-brand-400 border-b border-slate-800/80 pb-1">
                    <span>📅 {{ $cDate->isToday() ? 'TODAY — ' : '' }}{{ strtoupper($cDate->format('d M Y')) }}</span>
                </div>

                <div class="space-y-2">
                    @foreach($events as $ev)
                    <div class="p-4 bg-slate-950 border border-slate-800/80 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="flex items-start gap-3">
                            <div class="px-2.5 py-1 bg-slate-900 rounded-xl font-mono font-bold text-white text-center shrink-0">
                                {{ $ev['time'] }}
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">{{ $ev['title'] }}</h4>
                                <p class="text-slate-400 text-xs mt-0.5">{{ $ev['description'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-[10px] font-extrabold border shrink-0 {{ $ev['color'] }}">
                                {{ $ev['badge'] }}
                            </span>
                            @if(auth()->user()->isSuperAdmin() && $ev['is_custom'])
                            <button wire:click="editSchedule({{ $ev['id'] }})" title="Edit Agenda" class="p-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:confirm="Hapus agenda ini?" wire:click="deleteSchedule({{ $ev['id'] }})" title="Hapus Agenda" class="p-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-xs text-slate-500">
                Belum ada agenda terdaftar.
            </div>
            @endforelse
        </div>
    </div>

    <!-- CREATE SCHEDULE MODAL -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-white">Tambah Schedule Custom</h3>
                <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveSchedule" class="space-y-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Judul Agenda *</label>
                    <input type="text" wire:model="title" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    @error('title') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tanggal *</label>
                        <input type="date" wire:model="date" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Jam</label>
                        <input type="time" wire:model="time" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tipe Agenda</label>
                        <select wire:model="type" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            <option value="MEETING">MEETING</option>
                            <option value="EVENT">COMPANY EVENT</option>
                            <option value="MAINTENANCE">MAINTENANCE</option>
                            <option value="PERSONAL">PERSONAL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Project Terkait</label>
                        <select wire:model="project_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            <option value="">- Tanpa Project -</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi & Catatan</label>
                    <textarea wire:model="description" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 text-white font-bold rounded-xl">Simpan Agenda</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
