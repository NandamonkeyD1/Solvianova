<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public string $email = 'admin@solvia.nova';
    public string $password = 'password';
    public bool $remember = true;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password, 'status' => 'ACTIVE'], $this->remember)) {
            session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        $this->addError('email', 'Kredensial login tidak valid atau akun dinonaktifkan.');
    }

    public function selectDemoUser($email)
    {
        $this->email = $email;
        $this->password = 'password';
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
