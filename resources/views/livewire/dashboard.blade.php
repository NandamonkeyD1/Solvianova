<div class="space-y-6">

    @if($isSuperAdmin)
        <!-- ================= SUPER ADMIN COMMAND CENTER ================= -->

        <!-- Top Greeting Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 border border-slate-800 p-6 rounded-3xl shadow-xl">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-extrabold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-brand-400 animate-ping"></span>
                    Company Command Center
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Solvia.Nova Operational Overview</h1>
                <p class="text-xs md:text-sm text-slate-400">Pantau seluruh project, tim, progress, jadwal, dan keuangan perusahaan secara realtime.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects') }}" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/25 transition-all">
                    + Buat Project
                </a>
                <a href="{{ route('tasks') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl border border-slate-700 transition-all">
                    + Assign Task
                </a>
            </div>
        </div>

        <!-- 🔴 ACTION REQUIRED PANEL (Requirements Section 4) -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></span>
                    <h3 class="font-extrabold text-base text-white tracking-tight">Action Required</h3>
                </div>
                <span class="text-xs text-slate-400 font-medium">Item yang membutuhkan perhatian & tindakan segera</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Overdue Tasks -->
                <a href="{{ route('tasks') }}" class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 hover:border-red-500/50 transition-all flex flex-col justify-between space-y-2 group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-red-400 uppercase tracking-wider">Overdue Tasks</span>
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    </div>
                    <div class="text-2xl font-black text-red-400 group-hover:scale-105 transition-transform">{{ $overdueTasksCount }}</div>
                    <span class="text-[10px] text-red-300 font-semibold">🔴 Melewati Deadline</span>
                </a>

                <!-- Waiting Review -->
                <a href="{{ route('tasks') }}" class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 hover:border-amber-500/50 transition-all flex flex-col justify-between space-y-2 group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-amber-400 uppercase tracking-wider">Waiting Review</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="text-2xl font-black text-amber-400 group-hover:scale-105 transition-transform">{{ $waitingReviewTasksCount }}</div>
                    <span class="text-[10px] text-amber-300 font-semibold">🟠 Perlu Dicheck</span>
                </a>

                <!-- Blocked Tasks -->
                <a href="{{ route('tasks') }}" class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 hover:border-rose-500/50 transition-all flex flex-col justify-between space-y-2 group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-rose-400 uppercase tracking-wider">Blocked Tasks</span>
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    </div>
                    <div class="text-2xl font-black text-rose-400 group-hover:scale-105 transition-transform">{{ $blockedTasksCount }}</div>
                    <span class="text-[10px] text-rose-300 font-semibold">🚨 Ada Kendala</span>
                </a>

                <!-- Behind Schedule -->
                <a href="{{ route('projects') }}" class="p-4 rounded-2xl bg-orange-500/10 border border-orange-500/20 hover:border-orange-500/50 transition-all flex flex-col justify-between space-y-2 group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-orange-400 uppercase tracking-wider">Behind Schedule</span>
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    </div>
                    <div class="text-2xl font-black text-orange-400 group-hover:scale-105 transition-transform">{{ $projectsBehindSchedule }}</div>
                    <span class="text-[10px] text-orange-300 font-semibold">🟠 Project Lambat</span>
                </a>

                <!-- Missing Updates -->
                <a href="{{ route('daily-progress') }}" class="p-4 rounded-2xl bg-yellow-500/10 border border-yellow-500/20 hover:border-yellow-500/50 transition-all flex flex-col justify-between space-y-2 group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-yellow-400 uppercase tracking-wider">Missing Updates</span>
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                    </div>
                    <div class="text-2xl font-black text-yellow-400 group-hover:scale-105 transition-transform">{{ $membersMissingUpdateCount }}</div>
                    <span class="text-[10px] text-yellow-300 font-semibold">🟡 Belum Lapor Hari Ini</span>
                </a>

                <!-- Today Schedule -->
                <a href="{{ route('schedule') }}" class="p-4 rounded-2xl bg-sky-500/10 border border-sky-500/20 hover:border-sky-500/50 transition-all flex flex-col justify-between space-y-2 group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-sky-400 uppercase tracking-wider">Schedule Today</span>
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    </div>
                    <div class="text-2xl font-black text-sky-400 group-hover:scale-105 transition-transform">{{ count($todaySchedules) }}</div>
                    <span class="text-[10px] text-sky-300 font-semibold">📅 Agenda Hari Ini</span>
                </a>
            </div>
        </div>

        <!-- 📊 OVERVIEW KPI METRICS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Active Projects -->
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl shadow-xl flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400">Active Projects</span>
                    <div class="text-2xl font-black text-white">{{ $activeProjectsCount }} <span class="text-xs font-normal text-slate-500">/ {{ $totalProjectsCount }} total</span></div>
                    <span class="text-[10px] text-emerald-400 font-medium">Progress Tim Rata-rata: {{ $overallProgress }}%</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
            </div>

            <!-- Current Balance -->
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl shadow-xl flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400">Current Balance</span>
                    <div class="text-2xl font-black text-emerald-400">Rp {{ number_format($currentBalance, 0, ',', '.') }}</div>
                    <span class="text-[10px] text-slate-400">Total Kas & Rekening</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Money In & Money Out -->
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl shadow-xl flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400">Financial Cashflow</span>
                    <div class="text-sm font-bold text-emerald-400">+ Rp {{ number_format($moneyIn, 0, ',', '.') }}</div>
                    <div class="text-sm font-bold text-red-400">- Rp {{ number_format($moneyOut, 0, ',', '.') }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>

            <!-- Profit -->
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl shadow-xl flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400">Net Profit</span>
                    <div class="text-2xl font-black {{ $netProfit >= 0 ? 'text-indigo-400' : 'text-red-400' }}">
                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                    </div>
                    <span class="text-[10px] text-slate-400">Money In - Money Out</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>

        <!-- TWO COLUMN LAYOUT: Today's Team Activity & Recent Activity Timeline -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Today's Team Activity Tracker (Requirements 16 & 39) -->
            <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-extrabold text-base text-white tracking-tight">Today's Team Activity</h3>
                    <a href="{{ route('daily-progress') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300">Lihat Laporan &rarr;</a>
                </div>

                <div class="space-y-3">
                    @foreach($operationalUsers as $u)
                        @php $hasUpdated = in_array($u->id, $updatedTodayUserIds); @endphp
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-800 font-bold text-xs flex items-center justify-center text-slate-300">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-white">{{ $u->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ str_replace('_', ' ', $u->role) }}</span>
                                </div>
                            </div>
                            @if($hasUpdated)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                                    ✓ Lapor
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-full border border-amber-500/20">
                                    ⚠ Belum Lapor
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Global Activity Stream & Active Projects List -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Active Projects Overview -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="font-extrabold text-base text-white tracking-tight">Active Projects Monitoring</h3>
                        <a href="{{ route('projects') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300">Lihat Semua &rarr;</a>
                    </div>

                    <div class="space-y-4">
                        @foreach($activeProjects as $p)
                        <div class="p-4 bg-slate-950/70 border border-slate-800/80 rounded-2xl space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <h4 class="font-bold text-sm text-white flex items-center gap-2">
                                        {{ $p->name }}
                                        <span class="text-xs font-mono text-slate-400">({{ $p->code }})</span>
                                    </h4>
                                    <p class="text-xs text-slate-400">Client: {{ $p->client ?? 'Internal' }} &bull; Deadline: {{ $p->deadline?->format('d M Y') ?? 'TBA' }}</p>
                                </div>
                                <span class="text-xs font-black text-brand-400 px-3 py-1 rounded-full bg-brand-500/10 border border-brand-500/20 self-start sm:self-auto">
                                    {{ $p->progress }}% Progress
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-brand-600 to-indigo-400 rounded-full transition-all duration-500" style="width: {{ $p->progress }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Activity Timeline (Requirements 32 & 63) -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <h3 class="font-extrabold text-base text-white tracking-tight border-b border-slate-800 pb-3">Recent Activity Log</h3>
                    
                    <div class="space-y-3">
                        @foreach($recentActivities as $act)
                        <div class="flex items-start gap-3 text-xs text-slate-300 pb-3 border-b border-slate-800/50 last:border-0 last:pb-0">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                            <div class="flex-1">
                                <span class="font-semibold text-slate-400">{{ $act->created_at->format('H:i') }}</span> &bull; 
                                <span class="font-bold text-white">{{ $act->user?->name ?? 'System' }}</span>
                                <p class="text-slate-300 mt-0.5">{{ $act->description }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

    @else
        <!-- ================= MEMBER / JOKI DASHBOARD ================= -->

        <div class="bg-gradient-to-r from-slate-900 via-brand-950/30 to-slate-900 border border-slate-800 p-6 rounded-3xl shadow-xl space-y-2">
            <h1 class="text-2xl font-black text-white">Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p class="text-xs text-slate-400">Role: <span class="font-bold text-brand-400">{{ str_replace('_', ' ', auth()->user()->role) }}</span> &bull; Akses operasional langsung ke task & progress harian.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- My Active Tasks (Requirements 9 & 40) -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-extrabold text-base text-white">Task Saya (My Tasks)</h3>
                    <a href="{{ route('tasks') }}" class="text-xs font-semibold text-brand-400">Buka Task Board &rarr;</a>
                </div>

                <div class="space-y-3">
                    @forelse($myTasks as $t)
                    <div class="p-4 bg-slate-950/80 border border-slate-800 rounded-2xl space-y-3 hover:border-slate-700 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono text-brand-400">{{ $t->project?->code }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                {{ $t->status === 'DONE' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                                {{ $t->status === 'BLOCKED' ? 'bg-red-500/10 text-red-400' : '' }}
                                {{ $t->status === 'WAITING_REVIEW' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                {{ $t->status === 'REVISION' ? 'bg-rose-500/10 text-rose-400' : '' }}
                                {{ $t->status === 'IN_PROGRESS' ? 'bg-indigo-500/10 text-indigo-400' : '' }}
                                {{ $t->status === 'TODO' ? 'bg-slate-800 text-slate-400' : '' }}">
                                {{ str_replace('_', ' ', $t->status) }}
                            </span>
                        </div>

                        <div>
                            <h4 class="font-bold text-sm text-white">{{ $t->title }}</h4>
                            <p class="text-xs text-slate-400 line-clamp-2 mt-1">{{ $t->description }}</p>
                        </div>

                        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-800/60">
                            <span class="text-slate-400">Deadline: {{ $t->deadline?->format('d M Y H:i') ?? '-' }}</span>
                            <a href="{{ route('tasks') }}" class="px-3 py-1 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-lg text-xs">
                                Update Progress
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-xs text-slate-500">
                        Tidak ada task yang ditugaskan saat ini.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Sidebar: Today's Schedule & Notifications -->
            <div class="space-y-6">
                <!-- Today's Schedule -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    <h3 class="font-extrabold text-base text-white border-b border-slate-800 pb-3">Jadwal Hari Ini</h3>
                    <div class="space-y-3">
                        @forelse($todaySchedules as $sch)
                        <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800/80 space-y-1">
                            <span class="text-[10px] font-bold text-brand-400">{{ $sch->time ? substr($sch->time, 0, 5) : 'All Day' }}</span>
                            <h5 class="text-xs font-bold text-white">{{ $sch->title }}</h5>
                        </div>
                        @empty
                        <p class="text-xs text-slate-500 text-center py-4">Tidak ada jadwal hari ini.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Action Daily Progress -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-3">
                    <h3 class="font-extrabold text-base text-white">Daily Progress</h3>
                    <p class="text-xs text-slate-400">Jangan lupa mengirimkan laporan aktivitas harian Anda hari ini.</p>
                    <a href="{{ route('daily-progress') }}" class="block text-center py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition-all">
                        Isi Daily Progress Hari Ini &rarr;
                    </a>
                </div>
            </div>

        </div>

    @endif

</div>
