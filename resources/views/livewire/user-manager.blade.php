<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Team & User Management</h1>
            <p class="text-xs text-slate-400">Kelola akun anggota tim, joki freelance, role, dan hak akses sistem.</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="openRoleModal" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/25 transition-all flex items-center justify-center gap-2">
                🛡️ + Buat / Kelola Role Baru
            </button>
            <button wire:click="openCreateModal" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/25 transition-all flex items-center justify-center gap-2">
                + Tambah Member
            </button>
        </div>
    </div>

    <!-- Active Roles Overview -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-white uppercase tracking-wider">Role & Peran Sistem Aktif</span>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($allRoles as $rTag)
            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-950 border border-slate-800 text-slate-300 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                {{ str_replace('_', ' ', $rTag) }}
            </span>
            @endforeach
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left text-slate-300">
                <thead class="text-[10px] font-extrabold uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="p-3">User & Contact</th>
                        <th class="p-3">Role Sistem</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Tanggal Join</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-950/60 transition-all">
                        <td class="p-3 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 font-bold text-xs text-slate-200 flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            <div>
                                <span class="font-bold text-white block">{{ $u->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $u->email }} &bull; {{ $u->phone ?? 'No HP -' }}</span>
                            </div>
                        </td>
                        <td class="p-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold 
                                {{ $u->role === 'SUPER_ADMIN' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : '' }}
                                {{ $u->role === 'JOKI' ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : '' }}
                                {{ in_array($u->role, ['FRONTEND_DEV', 'BACKEND_DEV', 'IOT_ENGINEER']) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                                {{ in_array($u->role, ['DESIGNER', 'CONTENT_CREATOR']) ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}">
                                {{ str_replace('_', ' ', $u->role) }}
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded font-extrabold text-[10px] {{ $u->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                {{ $u->status }}
                            </span>
                        </td>
                        <td class="p-3 text-slate-400">
                            {{ $u->created_at->format('d M Y') }}
                        </td>
                        <td class="p-3 text-right space-x-1">
                            <button wire:click="editUser({{ $u->id }})" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-lg">
                                Edit
                            </button>
                            @if($u->id !== auth()->id())
                            <button wire:click="toggleStatus({{ $u->id }})" class="px-3 py-1 {{ $u->status === 'ACTIVE' ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-400' }} font-bold rounded-lg">
                                {{ $u->status === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                            <button wire:confirm="Hapus user ini dari sistem secara permanen?" wire:click="deleteUser({{ $u->id }})" class="px-3 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold rounded-lg">
                                Hapus
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- CREATE / EDIT USER MODAL -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-white">{{ $editingUserId ? 'Edit Member' : 'Tambah Member / Joki Baru' }}</h3>
                <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveUser" class="space-y-3">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Lengkap *</label>
                    <input type="text" wire:model="name" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    @error('name') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Email *</label>
                    <input type="email" wire:model="email" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    @error('email') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Password {{ $editingUserId ? '(Isi jika ingin ubah)' : '*' }}</label>
                    <input type="password" wire:model="password" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                    @error('password') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Role / Peran Member *</label>
                        <select wire:model="role" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-slate-100 focus:outline-none mb-1.5">
                            @foreach($allRoles as $rOpt)
                                <option value="{{ $rOpt }}">{{ str_replace('_', ' ', $rOpt) }}</option>
                            @endforeach
                        </select>
                        <input type="text" wire:model="custom_role" list="roles-datalist" placeholder="Atau ketik Role Baru (Custom)..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2 text-xs text-slate-100 focus:outline-none placeholder-slate-600">
                        <datalist id="roles-datalist">
                            @foreach($allRoles as $rOpt)
                                <option value="{{ $rOpt }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Status Member</label>
                        <select wire:model="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                            <option value="ACTIVE">ACTIVE</option>
                            <option value="INACTIVE">INACTIVE</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">No. WhatsApp / HP</label>
                    <input type="text" wire:model="phone" placeholder="+628..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 text-white font-bold rounded-xl">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- CREATE / EDIT MASTER ROLE MODAL -->
    @if($showRoleModal)
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-lg p-6 shadow-2xl space-y-4 text-xs">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-indigo-400">
                    {{ $editingRoleId ? 'Edit Master Role & Hak Akses' : '🛡️ Buat Role Baru (Master Role)' }}
                </h3>
                <button wire:click="$set('showRoleModal', false)" class="text-slate-400 hover:text-white font-bold text-lg">&times;</button>
            </div>

            <form wire:submit.prevent="saveRole" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Role (Display Name) *</label>
                        <input type="text" wire:model="role_display_name" placeholder="Contoh: QA Engineer, Scrum Master" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none">
                        @error('role_display_name') <span class="text-red-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode Role (Key)</label>
                        <input type="text" wire:model="role_name" placeholder="QA_ENGINEER" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 font-mono focus:outline-none uppercase">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi Role</label>
                    <textarea wire:model="role_description" rows="2" placeholder="Tugas dan tanggung jawab role ini..." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-slate-100 focus:outline-none"></textarea>
                </div>

                <!-- Master Roles List Table in Modal -->
                @if($masterRoles->count() > 0)
                <div class="space-y-2 border-t border-slate-800 pt-3">
                    <label class="block font-extrabold text-slate-200">Daftar Master Role Tersimpan dalam Sistem:</label>
                    <div class="max-h-44 overflow-y-auto space-y-1.5 pr-1">
                        @foreach($masterRoles as $mRole)
                        <div class="p-2.5 bg-slate-950 border border-slate-800 rounded-xl flex items-center justify-between">
                            <div>
                                <span class="font-bold text-white block">{{ $mRole->display_name }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $mRole->name }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="editRole({{ $mRole->id }})" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-lg text-[10px]">Edit</button>
                                @if(!in_array($mRole->name, ['SUPER_ADMIN', 'JOKI']))
                                <button type="button" wire:confirm="Hapus role master {{ $mRole->display_name }}?" wire:click="deleteRole({{ $mRole->id }})" class="px-2 py-1 bg-red-500/10 text-red-400 hover:bg-red-500/20 font-bold rounded-lg text-[10px]">Hapus</button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" wire:click="$set('showRoleModal', false)" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/25">Simpan Role Master</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
