<div>
    <div x-data="{ open: @entangle('isOpen') }"
         x-show="open"
         x-cloak
         @keydown.escape.window="open = false"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-start justify-center pt-16 p-4">
        
        <div @click.outside="open = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl space-y-4 p-4">
            
            <!-- Search Bar Input -->
            <div class="relative flex items-center">
                <svg class="w-5 h-5 text-slate-400 absolute left-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text"
                       wire:model.live.debounce.250ms="query"
                       placeholder="Cari Project, Task, User, Asset, Schedule... (Esc untuk batal)"
                       class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl pl-12 pr-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                       autofocus>
                <button wire:click="close" class="absolute right-4 text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 px-2 py-1 rounded">ESC</button>
            </div>

            <!-- Search Results Container -->
            <div class="max-h-96 overflow-y-auto space-y-4 divide-y divide-slate-800/60">
                @if(strlen(trim($query)) < 2)
                    <div class="text-center py-8 text-xs text-slate-500">
                        Ketik minimal 2 karakter untuk mencari seluruh data sistem.
                    </div>
                @else
                    @if($projects->isEmpty() && $tasks->isEmpty() && $users->isEmpty() && $assets->isEmpty() && $schedules->isEmpty())
                        <div class="text-center py-8 text-xs text-slate-400">
                            Tidak ada hasil ditemukan untuk "<span class="font-bold text-white">{{ $query }}</span>".
                        </div>
                    @endif

                    @if($projects->isNotEmpty())
                    <div class="pt-3 first:pt-0">
                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider block mb-2">Projects</span>
                        <div class="space-y-1">
                            @foreach($projects as $p)
                            <a href="{{ route('projects') }}" wire:click="close" class="flex items-center justify-between p-2.5 hover:bg-slate-800/80 rounded-xl text-xs text-slate-200">
                                <span class="font-bold text-white">{{ $p->name }} <span class="text-slate-500 font-normal">({{ $p->code }})</span></span>
                                <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 text-[10px] font-bold">{{ $p->status }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($tasks->isNotEmpty())
                    <div class="pt-3">
                        <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block mb-2">Tasks</span>
                        <div class="space-y-1">
                            @foreach($tasks as $t)
                            <a href="{{ route('tasks') }}" wire:click="close" class="flex items-center justify-between p-2.5 hover:bg-slate-800/80 rounded-xl text-xs text-slate-200">
                                <div>
                                    <span class="font-bold text-white block">{{ $t->title }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $t->project?->name }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[10px] font-bold">{{ str_replace('_', ' ', $t->status) }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($users->isNotEmpty())
                    <div class="pt-3">
                        <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wider block mb-2">Users</span>
                        <div class="space-y-1">
                            @foreach($users as $u)
                            <a href="{{ route('users') }}" wire:click="close" class="flex items-center justify-between p-2.5 hover:bg-slate-800/80 rounded-xl text-xs text-slate-200">
                                <div>
                                    <span class="font-bold text-white block">{{ $u->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $u->email }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 text-[10px] font-bold">{{ str_replace('_', ' ', $u->role) }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($assets->isNotEmpty())
                    <div class="pt-3">
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider block mb-2">Assets</span>
                        <div class="space-y-1">
                            @foreach($assets as $a)
                            <a href="{{ route('assets') }}" wire:click="close" class="flex items-center justify-between p-2.5 hover:bg-slate-800/80 rounded-xl text-xs text-slate-200">
                                <div>
                                    <span class="font-bold text-white block">{{ $a->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $a->asset_code }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 text-[10px] font-bold">{{ $a->type }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($schedules->isNotEmpty())
                    <div class="pt-3">
                        <span class="text-[10px] font-bold text-sky-400 uppercase tracking-wider block mb-2">Schedule</span>
                        <div class="space-y-1">
                            @foreach($schedules as $s)
                            <a href="{{ route('schedule') }}" wire:click="close" class="flex items-center justify-between p-2.5 hover:bg-slate-800/80 rounded-xl text-xs text-slate-200">
                                <span class="font-bold text-white">{{ $s->title }}</span>
                                <span class="text-[10px] text-slate-400">{{ $s->date->format('d M Y') }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</div>
