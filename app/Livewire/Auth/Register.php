<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'CONTENT_CREATOR';
    public string $phone = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
        'role' => 'required',
    ];

    public function register()
    {
        $this->validate();

        // Check if this is the very first user in the system (if so, make Super Admin automatically)
        $isFirstUser = User::count() === 0;
        $userRole = $isFirstUser ? 'SUPER_ADMIN' : $this->role;

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $userRole,
            'status' => 'ACTIVE',
            'phone' => $this->phone,
        ]);

        ActivityLog::log('USER_REGISTERED', 'User baru ' . $user->name . ' (' . $user->role . ') telah mendaftar.', $user);

        Auth::login($user);
        session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('components.layouts.guest');
    }
}
