<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Task & Operational Workflow</h1>
            <p class="text-xs text-slate-400">Plan → Assign → Work → Update → Review → Approve → Track</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Toggle View Mode -->
            <div class="bg-slate-900 border border-slate-800 p-1 rounded-xl flex items-center text-xs font-bold text-slate-400">
                <button wire:click="$set('viewMode', 'kanban')" class="px-3 py-1.5 rounded-lg transition-all {{ $viewMode === 'kanban' ? 'bg-brand-600 text-white shadow-md' : 'hover:text-slate-200' }}">Kanban</button>
                <button wire:click="$set('viewMode', 'list')" class="px-3 py-1.5 rounded-lg transition-all {{ $viewMode === 'list' ? 'bg-brand-600 text-white shadow-md' : 'hover:text-slate-200' }}">List</button>
            </div>

            @if(auth()->user()->hasPermission('task.create'))
            <button wire:click="openCreateModal" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/25 transition-all flex items-center gap-2">
                + Task Baru
            </button>
            @endif
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 p-4 rounded-2xl border border-slate-800 text-xs">
        <div class="flex items-center gap-3 flex-wrap">
            <select wire:model.live="filterProject" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-200 font-semibold focus:outline-none">
                <option value="ALL">Semua Project</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                @endforeach
            </select>

            <select wire:model.live="filterPriority" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-200 font-semibold focus:outline-none">
                <option value="ALL">Semua Priority</option>
                <option value="URGENT">🔴 URGENT</option>
                <option value="HIGH">🟠 HIGH</option>
                <option value="MEDIUM">🟡 MEDIUM</option>
                <option value="LOW">🔵 LOW</option>
            </select>
        </div>

        <div class="w-full sm:w-64">
            <input type="text" wire:model.live.debounce.250ms="search" placeholder="Cari task..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-slate-100 placeholder-slate-500 focus:outline-none">
        </div>
    </div>

    <!-- KANBAN BOARD VIEW -->
    @if($viewMode === 'kanban')
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 overflow-x-auto pb-4">
        @foreach(['TODO', 'IN_PROGRESS', 'WAITING_REVIEW', 'REVISION', 'BLOCKED', 'DONE'] as $colStatus)
        @php
            $colTasks = $tasks->where('status', $colStatus);
            $badgeColor = match($colStatus) {
                'TODO' => 'bg-slate-800 text-slate-400',
                'IN_PROGRESS' => 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20',
                'WAITING_REVIEW' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
                'REVISION' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                'BLOCKED' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                'DONE' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
            };
        @endphp

        <div class="bg-slate-900/90 border border-slate-800/90 rounded-3xl p-3.5 space-y-3 flex flex-col min-w-[240px]">
            <!-- Column Header -->
            <div class="flex items-center justify-between px-1 pb-2 border-b border-slate-800">
                <span class="text-xs font-black text-white tracking-tight flex items-center gap-1.5">
                    {{ str_replace('_', ' ', $colStatus) }}
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $badgeColor }}">
                    {{ $colTasks->count() }}
                </span>
            </div>

            <!-- Task Cards -->
            <div class="space-y-3 flex-1 overflow-y-auto max-h-[calc(100vh-280px)]">
                @forelse($colTasks as $t)
                <div wire:click="openTaskDetail({{ $t->id }})" class="p-4 bg-slate-950 border border-slate-800/80 hover:border-slate-600 rounded-2xl space-y-3 cursor-pointer transition-all shadow-md group">
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="font-mono text-brand-400 font-bold">{{ $t->project?->code }}</span>
                        <span class="px-2 py-0.5 rounded font-extrabold 
                            {{ $t->priority === 'URGENT' ? 'bg-red-500/20 text-red-400' : '' }}
                            {{ $t->priority === 'HIGH' ? 'bg-orange-500/20 text-orange-400' : '' }}
                            {{ $t->priority === 'MEDIUM' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                            {{ $t->priority === 'LOW' ? 'bg-blue-500/20 text-blue-400' : '' }}">
                            {{ $t->priority }}
                        </span>
                    </div>

                    <div>
                        <h4 class="font-bold text-xs text-white group-hover:text-brand-300 transition-colors">{{ $t->title }}</h4>
                        <p class="text-[11px] text-slate-400 line-clamp-2 mt-1">{{ $t->description }}</p>
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-[10px] font-semibold text-slate-400">
                            <span>Progress</span>
                            <span>{{ $t->progress }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-900 rounded-full overflow-hidden">
                            <div class="h-full bg-brand-500 rounded-full" style="width: {{ $t->progress }}%"></div>
                        </div>
                    </div>

                    <!-- Assignees & Deadline -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-900 text-[10px] text-slate-400">
                        <span>{{ $t->deadline?->format('d M') ?? '-' }}</span>
                        <div class="flex -space-x-1.5">
                            @foreach($t->assignees as $asg)
                                <div title="{{ $asg->name }}" class="w-5 h-5 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-[9px] ring-1 ring-slate-950">
                                    {{ strtoupper(substr($asg->name, 0, 1)) }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-[11px] text-slate-600 font-medium">
                    Kosong
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- LIST VIEW -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-xl overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-300">
            <thead class="text-[10px] font-extrabold uppercase text-slate-400 border-b border-slate-800">
                <tr>
                    <th class="p-3">Task & Project</th>
                    <th class="p-3">Assignees</th>
                    <th class="p-3">Priority</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Progress</th>
                    <th class="p-3">Deadline</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                @foreach($tasks as $t)
                <tr class="hover:bg-slate-950/60 transition-all">
                    <td class="p-3">
                        <span class="font-bold text-white block">{{ $t->title }}</span>
                        <span class="text-[10px] text-brand-400 font-mono">{{ $t->project?->name }} ({{ $t->project?->code }})</span>
                    </td>
                    <td class="p-3">
                        <span class="font-semibold text-slate-200">{{ $t->assignees->pluck('name')->join(', ') ?: 'Unassigned' }}</span>
                    </td>
                    <td class="p-3">
                        <span class="font-extrabold text-[10px]">{{ $t->priority }}</span>
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-800 text-slate-200">
                            {{ str_replace('_', ' ', $t->status) }}
                        </span>
                    </td>
                    <td class="p-3 font-bold text-brand-400">
                        {{ $t->progress }}%
                    </td>
                    <td class="p-3 text-slate-400">
                        {{ $t->deadline?->format('d M Y H:i') ?? '-' }}
                    </td>
                    <td class="p-3 text-right space-x-1">
                        <button wire:click="openTaskDetail({{ $t->id }})" class="px-3 py-1 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-lg text-xs">
                            Detail
                        </button>
                        @if(auth()->user()->isSuperAdmin())
                        <button wire:click="editTask({{ $t->id }})" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-lg text-xs">
                            Edit
                        </button>
                        <button wire:confirm="Hapus task ini?" wire:click="deleteTask({{ $t->id }})" class="px-2.5 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold rounded-lg text-xs">
                            Hapus
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- TASK DETAIL & MEMBER WORKFLOW MODAL -->
    @if($showDetailModal && $selectedTask)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-4xl p-6 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-slate-800 pb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="font-mono text-brand-400 font-bold">{{ $selectedTask->project?->code }}</span>
                        <span class="text-slate-500">&bull;</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300">{{ str_replace('_', ' ', $selectedTask->status) }}</span>
                    </div>
                    <h3 class="font-black text-xl text-white">{{ $selectedTask->title }}</h3>
                </div>
                <div class="flex items-center gap-2">
                    @if(auth()->user()->isSuperAdmin())
                    <button wire:click="editTask({{ $selectedTask->id }})" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl">
                        Edit Task
                    </button>
                    <button wire:confirm="Hapus task ini?" wire:click="deleteTask({{ $selectedTask->id }})" class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold text-xs rounded-xl border border-red-500/20">
                        Hapus Task
                    </button>
                    @endif
                    <button wire:click="$set('showDetailModal', false)" class="text-slate-400 hover:text-white font-bold text-xl">&times;</button>
                </div>
            </div>

            <!-- Task Description & Metadata -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column: Description & Update Form -->
                <div class="md:col-span-2 space-y-6 text-xs">
                    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <h4 class="font-bold text-slate-300">Deskripsi Task</h4>
                        <p class="text-slate-400 leading-relaxed">{{ $selectedTask->description ?? 'Tidak ada deskripsi rinci.' }}</p>
                    </div>

                    @if($selectedTask->revision_notes)
                    <div class="bg-rose-500/10 border border-rose-500/30 p-4 rounded-2xl space-y-1 text-rose-300">
                        <h4 class="font-extrabold flex items-center gap-2">⚠️ Catatan Revisi Super Admin</h4>
                        <p class="text-xs">{{ $selectedTask->revision_notes }}</p>
                    </div>
                    @endif

                    @if($selectedTask->blocker_reason)
                    <div class="bg-red-500/10 border border-red-500/30 p-4 rounded-2xl space-y-1 text-red-300">
                        <h4 class="font-extrabold flex items-center gap-2">🚨 Kendala (Blocker) Terdaftar</h4>
                        <p class="text-xs">{{ $selectedTask->blocker_reason }}</p>
                    </div>
                    @endif

                    <!-- UPDATE PROGRESS FORM FOR MEMBERS (Requirements 10, 11, 12, 41) -->
                    <div class="bg-slate-950 p-5 rounded-2xl border border-slate-800 space-y-4">
                        <h4 class="font-extrabold text-sm text-white border-b border-slate-800 pb-2">Update Progress & Submit Result</h4>
                        
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                    <span>Set Progress (%)</span>
                                    <span class="text-brand-400">{{ $progressPercent }}%</span>
                                </div>
                                <input type="range" wire:model.live="progressPercent" min="0" max="100" class="w-full accent-brand-600">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Catatan Pengerjaan (Work Done)</label>
                                <textarea wire:model="updateDescription" placeholder="Apa yang sudah diselesaikan?" rows="2" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Rencana Selanjutnya (Next Plan)</label>
                                    <input type="text" wire:model="nextPlan" placeholder="Langkah berikutnya..." class="w-full bg-slate-900 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-300 mb-1">Link Bukti / File Deliverables</label>
                                    <input type="text" wire:model="attachmentUrl" placeholder="https://... atau Figma / GitHub link" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                                </div>
                            </div>

                            <!-- Blocker Input -->
                            <div>
                                <label class="block font-semibold text-red-400 mb-1">Laporkan Kendala / Blocker (Jika Ada)</label>
                                <input type="text" wire:model="blockerInput" placeholder="Jelaskan kendala teknis atau kebutuhan penunjang..." class="w-full bg-slate-900 border border-red-500/30 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                <button type="button" wire:click="saveProgressUpdate" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl">
                                    Simpan History Progress
                                </button>

                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="reportBlocker" class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold rounded-xl border border-red-500/20">
                                        🚨 Laporkan Blocker
                                    </button>
                                    <button type="button" wire:click="submitForReview" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-xl shadow-lg shadow-amber-600/20">
                                        Submit for Review &rarr;
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress History Logs (Requirements 11) -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-300 border-b border-slate-800 pb-2">History Log Progress (Preserved)</h4>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @forelse($selectedTask->progressUpdates as $pu)
                            <div class="p-3 bg-slate-950 border border-slate-800/80 rounded-xl space-y-1">
                                <div class="flex justify-between font-bold text-slate-300">
                                    <span>{{ $pu->user?->name }} &bull; {{ $pu->progress }}%</span>
                                    <span class="text-[10px] text-slate-500">{{ $pu->created_at->format('d M H:i') }}</span>
                                </div>
                                <p class="text-slate-400">{{ $pu->description }}</p>
                                @if($pu->attachment_url)
                                    <a href="{{ $pu->attachment_url }}" target="_blank" class="text-brand-400 underline font-semibold block text-[10px]">Lihat Bukti Attachment &rarr;</a>
                                @endif
                            </div>
                            @empty
                            <p class="text-slate-500 text-center py-2">Belum ada history update.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Right Column: Super Admin Review Controls & Comments -->
                <div class="space-y-6 text-xs">
                    
                    <!-- SUPER ADMIN REVIEW CONTROLS (Requirements 13) -->
                    @if(auth()->user()->isSuperAdmin())
                    <div class="bg-indigo-950/40 border border-indigo-500/30 p-5 rounded-2xl space-y-3">
                        <h4 class="font-extrabold text-sm text-indigo-300 flex items-center gap-2">
                            👑 Super Admin Review Panel
                        </h4>
                        <p class="text-slate-400">Tentukan persetujuan atau berikan catatan revisi hasil pengerjaan member.</p>
                        
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Catatan Revisi (Jika Minta Revisi)</label>
                            <textarea wire:model="revisionFeedback" rows="2" placeholder="Contoh: Perbaiki spacing mobile dan kontras warna..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button wire:click="approveTaskDirect" class="py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-center shadow-lg shadow-emerald-600/20">
                                ✓ Approve Task
                            </button>
                            <button wire:click="submitRevisionNotes" class="py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl text-center shadow-lg shadow-rose-600/20">
                                ↺ Kirim Revisi
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Task Assignees list -->
                    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <h4 class="font-bold text-slate-300">Assignees</h4>
                        <div class="space-y-1">
                            @foreach($selectedTask->assignees as $asg)
                            <div class="flex items-center gap-2 p-1.5 bg-slate-900 rounded-lg">
                                <div class="w-6 h-6 rounded-full bg-brand-600 font-bold text-white text-[10px] flex items-center justify-center">
                                    {{ strtoupper(substr($asg->name, 0, 1)) }}
                                </div>
                                <span class="font-semibold text-slate-200">{{ $asg->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-3">
                        <h4 class="font-bold text-slate-300">Diskusi / Comments</h4>
                        <div class="space-y-2 max-h-36 overflow-y-auto">
                            @foreach($selectedTask->comments as $cm)
                            <div class="p-2 bg-slate-900 rounded-xl space-y-0.5">
                                <div class="flex justify-between font-bold text-slate-200 text-[10px]">
                                    <span>{{ $cm->user?->name }}</span>
                                    <span class="text-slate-500">{{ $cm->created_at->format('H:i') }}</span>
                                </div>
                                <p class="text-slate-300 text-[11px]">{{ $cm->comment }}</p>
                            </div>
                            @endforeach
                        </div>

                        <div class="flex gap-2 pt-1">
                            <input type="text" wire:model="newComment" wire:keydown.enter="postComment" placeholder="Tulis komentar..." class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-slate-100 focus:outline-none">
                            <button wire:click="postComment" class="px-3 py-1.5 bg-brand-600 text-white font-bold rounded-xl">Kirim</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endif

    <!-- CREATE TASK MODAL -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg p-6 shadow-2xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <h3 class="font-extrabold text-lg text-white">Buat Task Baru</h3>
                <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-white font-bold text-xl">&times;</button>
            </div>

            <form wire:submit.prevent="saveTask" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Judul Task *</label>
                    <input type="text" wire:model="title" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    @error('title') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Project *</label>
                        <select wire:model="project_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Priority *</label>
                        <select wire:model="priority" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            <option value="LOW">LOW</option>
                            <option value="MEDIUM">MEDIUM</option>
                            <option value="HIGH">HIGH</option>
                            <option value="URGENT">URGENT</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deadline Task</label>
                    <input type="datetime-local" wire:model="deadline" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi & Instruksi Pengerjaan</label>
                    <textarea wire:model="description" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-2">Assignee (Pilih Anggota Tim)</label>
                    <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2 bg-slate-950 border border-slate-800 rounded-xl">
                        @foreach($users as $u)
                        <label class="flex items-center gap-2 p-1 hover:bg-slate-900 rounded cursor-pointer">
                            <input type="checkbox" wire:model="selectedAssignees" value="{{ $u->id }}" class="rounded bg-slate-900 border-slate-700 text-brand-600">
                            <span class="font-medium text-white">{{ $u->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 text-white font-bold rounded-xl shadow-lg shadow-brand-600/20">Buat & Assign Task</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- SUPER ADMIN REVIEW ACTION MODAL -->
    @if($showReviewModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4">
            <h3 class="font-extrabold text-lg text-white">
                {{ $reviewAction === 'APPROVE' ? 'Konfirmasi Approve Task' : 'Catatan Revisi Task' }}
            </h3>

            @if($reviewAction === 'APPROVE')
                <p class="text-xs text-slate-400">Apakah Anda yakin menyetujui hasil pekerjaan ini? Task akan ditandai DONE dengan progress 100%.</p>
            @else
                <div class="space-y-2 text-xs">
                    <label class="block font-semibold text-slate-300">Jelaskan poin yang perlu diperbaiki (Wajib):</label>
                    <textarea wire:model="revisionFeedback" rows="3" placeholder="Contoh: Perbaiki spacing mobile pada section pricing..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none"></textarea>
                    @error('revisionFeedback') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                <button wire:click="$set('showReviewModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold text-xs rounded-xl">Batal</button>
                <button wire:click="processSuperAdminReview" class="px-5 py-2 {{ $reviewAction === 'APPROVE' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-rose-600 hover:bg-rose-500' }} text-white font-bold text-xs rounded-xl">
                    {{ $reviewAction === 'APPROVE' ? 'Approve Task' : 'Kirim Revisi' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
