<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Daily Team Activity Tracker</h1>
            <p class="text-xs text-slate-400">Pantau dan laporkan aktivitas kerja harian setiap anggota tim Solvia.Nova.</p>
        </div>
    </div>

    <!-- Today's Team Activity Overview Grid (Requirements 16) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <h3 class="font-extrabold text-base text-white border-b border-slate-800 pb-3 flex items-center justify-between">
            <span>Status Update Hari Ini ({{ \Carbon\Carbon::today()->format('d M Y') }})</span>
            <span class="text-xs font-semibold text-slate-400">{{ count($updatedTodayUserIds) }} / {{ count($operationalUsers) }} Tim Sudah Lapor</span>
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($operationalUsers as $u)
                @php $hasUpdated = in_array($u->id, $updatedTodayUserIds); @endphp
                <div class="p-3.5 rounded-2xl border text-xs flex flex-col justify-between space-y-2 {{ $hasUpdated ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-amber-500/10 border-amber-500/30 text-amber-300' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-bold truncate">{{ $u->name }}</span>
                        <span class="text-[10px] font-black px-1.5 py-0.5 rounded {{ $hasUpdated ? 'bg-emerald-500 text-slate-950' : 'bg-amber-500 text-slate-950' }}">
                            {{ $hasUpdated ? '✓' : '!' }}
                        </span>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400">{{ str_replace('_', ' ', $u->role) }}</span>
                    <span class="text-[10px] font-bold">{{ $hasUpdated ? '✓ Sudah Lapor' : '⚠ Belum Lapor' }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Input Daily Progress Form -->
        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4 text-xs">
            <h3 class="font-extrabold text-base text-white border-b border-slate-800 pb-3">Isi Progress Hari Ini</h3>

            <form wire:submit.prevent="saveDailyProgress" class="space-y-4">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Tanggal</label>
                    <input type="date" wire:model="date" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Project Terkait</label>
                    <select wire:model="project_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Task Terkait (Opsional)</label>
                    <select wire:model="task_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                        <option value="">- Tidak terkait spesifik task -</option>
                        @foreach($tasks as $tk)
                            <option value="{{ $tk->id }}">{{ $tk->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="flex justify-between font-bold text-slate-300 mb-1">
                        <span>Progress (%)</span>
                        <span class="text-brand-400">{{ $progress }}%</span>
                    </div>
                    <input type="range" wire:model.live="progress" min="0" max="100" class="w-full accent-brand-600">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Apa yang dikerjakan hari ini? *</label>
                    <textarea wire:model="work_done" placeholder="Rincian pekerjaan selesai..." rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none"></textarea>
                    @error('work_done') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Apa rencana selanjutnya (Next plan)?</label>
                    <input type="text" wire:model="next_plan" placeholder="Rencana esok hari..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-red-400 mb-1">Apakah ada kendala (Blocker)?</label>
                    <input type="text" wire:model="blocker" placeholder="Tulis kendala jika ada..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Link Bukti Hasil Pekerjaan</label>
                    <input type="text" wire:model="attachment_url" placeholder="https://..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl shadow-lg shadow-brand-600/20">
                    Kirim Laporan Harian &rarr;
                </button>
            </form>
        </div>

        <!-- Recent Daily Progress Logs -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <h3 class="font-extrabold text-base text-white border-b border-slate-800 pb-3">History Daily Progress Team</h3>
            
            <div class="space-y-3">
                @forelse($dailyLogs as $log)
                <div class="p-4 bg-slate-950 border border-slate-800/80 rounded-2xl space-y-2 text-xs">
                    <div class="flex items-center justify-between border-b border-slate-800/60 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white">{{ $log->user?->name }}</span>
                            <span class="text-[10px] text-slate-400">({{ str_replace('_', ' ', $log->user?->role) }})</span>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400">{{ $log->date?->format('d M Y') }}</span>
                    </div>

                    <p class="text-slate-200 font-medium">{{ $log->work_done }}</p>

                    @if($log->next_plan)
                    <p class="text-slate-400 text-[11px]"><span class="font-bold text-indigo-400">Next:</span> {{ $log->next_plan }}</p>
                    @endif

                    @if($log->blocker)
                    <div class="p-2 bg-red-500/10 border border-red-500/20 rounded-xl text-red-300 text-[11px]">
                        <span class="font-bold">🚨 Blocker:</span> {{ $log->blocker }}
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-8 text-xs text-slate-500">Belum ada history laporan harian.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
