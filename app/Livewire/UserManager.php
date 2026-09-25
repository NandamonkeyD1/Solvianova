<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;

class UserManager extends Component
{
    public bool $showCreateModal = false;

    public ?int $editingUserId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'CONTENT_CREATOR';
    public string $custom_role = '';
    public string $status = 'ACTIVE';
    public string $phone = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
    ];

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function editUser(int $id)
    {
        $u = User::findOrFail($id);
        $this->editingUserId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->role = $u->role;
        $this->custom_role = $u->role;
        $this->status = $u->status;
        $this->phone = $u->phone ?? '';
        $this->password = '';
        $this->showCreateModal = true;
    }

    public function toggleStatus(int $id)
    {
        $u = User::findOrFail($id);
        $newStatus = $u->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $u->update(['status' => $newStatus]);
        ActivityLog::log('USER_STATUS_TOGGLED', 'Status user ' . $u->name . ' diubah ke ' . $newStatus . '.', $u);
        $this->dispatch('toast', message: 'Status user ' . $u->name . ' diperbarui ke ' . $newStatus . '!');
    }

    public function deleteUser(int $id)
    {
        if (!auth()->user()->isSuperAdmin() || $id === auth()->id()) return;

        $u = User::findOrFail($id);
        $name = $u->name;
        $u->delete();

        ActivityLog::log('USER_DELETED', 'Super Admin menghapus user ' . $name . '.', null);
        $this->dispatch('toast', message: 'User berhasil dihapus!', type: 'error');
    }

    public function saveUser()
    {
        $this->validate();

        $effectiveRole = !empty(trim($this->custom_role)) 
            ? strtoupper(str_replace(' ', '_', trim($this->custom_role))) 
            : $this->role;

        if ($this->editingUserId) {
            $u = User::findOrFail($this->editingUserId);
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'role' => $effectiveRole,
                'status' => $this->status,
                'phone' => $this->phone,
            ];
            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }
            $u->update($data);
            ActivityLog::log('USER_UPDATED', 'Profil user ' . $u->name . ' diperbarui.', $u);
            $this->dispatch('toast', message: 'Data user berhasil diperbarui!');
        } else {
            $this->validate(['password' => 'required|min:6']);
            $u = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => $effectiveRole,
                'status' => $this->status,
                'phone' => $this->phone,
            ]);
            ActivityLog::log('USER_CREATED', 'Super Admin menambah user baru ' . $u->name . ' (' . $u->role . ').', $u);
            $this->dispatch('toast', message: 'User baru berhasil didaftarkan!');
        }

        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'CONTENT_CREATOR';
        $this->custom_role = '';
        $this->status = 'ACTIVE';
        $this->phone = '';
    }

    public function render()
    {
        $users = User::latest()->get();
        $defaultRoles = ['SUPER_ADMIN', 'CONTENT_CREATOR', 'DESIGNER', 'FRONTEND_DEV', 'BACKEND_DEV', 'IOT_ENGINEER', 'JOKI'];
        $existingRoles = User::select('role')->distinct()->pluck('role')->toArray();
        $allRoles = array_unique(array_merge($defaultRoles, $existingRoles));

        return view('livewire.user-manager', [
            'users' => $users,
            'allRoles' => $allRoles,
        ]);
    }
}
