<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Notification Center</h1>
            <p class="text-xs text-slate-400">Pusat pemberitahuan otomatis aktivitas task, review, blocker, dan jadwal.</p>
        </div>
        <button wire:click="markAllAsRead" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition-all">
            ✓ Tandai Semua Dibaca
        </button>
    </div>

    <!-- Filter Tabs (Requirements 20) -->
    <div class="flex items-center gap-1 overflow-x-auto pb-2 border-b border-slate-800 text-xs font-semibold">
        @foreach(['ALL', 'UNREAD', 'TASK', 'REVIEW', 'BLOCKER', 'PROJECT', 'SCHEDULE', 'EXPIRY'] as $ft)
            <button wire:click="$set('filterType', '{{ $ft }}')" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $filterType === $ft ? 'bg-brand-600 text-white font-bold shadow-md' : 'text-slate-400 hover:bg-slate-800' }}">
                {{ $ft }}
            </button>
        @endforeach
    </div>

    <!-- Notifications List -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-xl space-y-3">
        @forelse($notifications as $n)
        <div wire:click="markAsRead({{ $n->id }})" class="p-4 rounded-2xl border transition-all cursor-pointer flex items-start justify-between gap-4 {{ $n->is_read ? 'bg-slate-950/50 border-slate-800/60 opacity-75' : 'bg-slate-950 border-brand-500/30 ring-1 ring-brand-500/20' }}">
            <div class="flex items-start gap-3">
                <div class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0 {{ $n->is_read ? 'bg-slate-600' : 'bg-brand-400 animate-pulse' }}"></div>
                <div class="space-y-1 text-xs">
                    <h4 class="font-bold text-white text-sm flex items-center gap-2">
                        {{ $n->title }}
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-800 text-slate-300">{{ $n->type }}</span>
                    </h4>
                    <p class="text-slate-300 leading-relaxed">{{ $n->message }}</p>
                    <span class="text-[10px] text-slate-500 font-medium block">{{ $n->created_at->diffForHumans() }}</span>
                </div>
            </div>

            @if($n->link)
                <span class="text-xs font-bold text-brand-400 shrink-0">Buka &rarr;</span>
            @endif
        </div>
        @empty
        <div class="text-center py-12 text-xs text-slate-500">
            Tidak ada notifikasi ditemukan.
        </div>
        @endforelse
    </div>
</div>
