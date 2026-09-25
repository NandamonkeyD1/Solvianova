<div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
    <!-- Header -->
    <div class="text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-500 mx-auto flex items-center justify-center text-white font-extrabold text-xl shadow-lg shadow-indigo-500/30">
            S
        </div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Solvia<span class="text-indigo-400">.Nova</span> OS</h1>
        <p class="text-xs text-slate-400">Central Internal Operating System</p>
    </div>

    <!-- Login Form -->
    <form wire:submit.prevent="login" class="space-y-4">
        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
            <input type="email" wire:model="email" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all">
            @error('email') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
            <input type="password" wire:model="password" class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all">
            @error('password') <span class="text-xs text-red-400 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition-all text-sm">
            Masuk ke System
        </button>
    </form>

    <div class="pt-4 border-t border-slate-800 text-center">
        <p class="text-xs text-slate-400">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold transition-colors">Daftar sekarang</a>
        </p>
    </div>
</div>
