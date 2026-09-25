<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900 text-slate-100 dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Solvia.Nova OS' }} - Central Command Center</title>
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
    @livewireStyles
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 flex flex-col"
      x-data="{ 
          sidebarOpen: true, 
          mobileMenuOpen: false, 
          searchOpen: false,
          online: navigator.onLine,
          toast: { show: false, message: '', type: 'success' },
          triggerToast(msg, type = 'success') {
              this.toast.message = msg;
              this.toast.type = type;
              this.toast.show = true;
              setTimeout(() => this.toast.show = false, 4000);
          }
      }"
      x-init="
          window.addEventListener('online', () => online = true);
          window.addEventListener('offline', () => online = false);
          window.addEventListener('toast', (e) => triggerToast(e.detail.message, e.detail.type || 'success'));
      ">

    <!-- Offline Alert Banner -->
    <div x-show="!online" x-cloak class="bg-amber-500/90 text-slate-950 text-xs font-bold text-center py-1.5 px-4 sticky top-0 z-50 flex items-center justify-center gap-2 shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span>Anda sedang offline. Beberapa fitur atau data mungkin belum tersinkronisasi.</span>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-cloak x-transition
         class="fixed bottom-20 right-4 md:bottom-6 md:right-6 z-50 max-w-sm w-full bg-slate-900 border text-slate-100 p-4 rounded-xl shadow-2xl flex items-center justify-between gap-3"
         :class="toast.type === 'error' ? 'border-red-500/50 text-red-300' : 'border-emerald-500/50 text-emerald-300'">
        <div class="flex items-center gap-3">
            <template x-if="toast.type === 'error'">
                <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </template>
            <template x-if="toast.type !== 'error'">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </template>
            <span class="text-sm font-medium text-slate-200" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-slate-400 hover:text-slate-200">&times;</button>
    </div>

    <div class="flex-1 flex overflow-hidden">

        <!-- Sidebar for Desktop -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="hidden md:flex flex-col bg-slate-900 border-r border-slate-800 transition-all duration-300 z-30 relative shrink-0">
            <!-- Brand Logo -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800">
                <a href="/" class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center font-black text-white text-lg shadow-lg shadow-brand-500/20 shrink-0">
                        S
                    </div>
                    <div x-show="sidebarOpen" class="flex flex-col">
                        <span class="font-extrabold text-base tracking-tight text-white">Solvia<span class="text-brand-400">.Nova</span></span>
                        <span class="text-[10px] font-semibold text-slate-400 tracking-wider uppercase">Operating System</span>
                    </div>
                </a>
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>

            <!-- Main Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                @php
                    $user = auth()->user();
                    $route = request()->route()?->getName();
                @endphp

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Command Center</span>
                </a>

                @if($user?->hasPermission('project.view'))
                <a href="{{ route('projects') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('projects*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Projects</span>
                </a>
                @endif

                @if($user?->hasPermission('task.view'))
                <a href="{{ route('tasks') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('tasks*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span x-show="sidebarOpen" class="truncate">{{ $user->isSuperAdmin() ? 'All Tasks' : 'My Tasks' }}</span>
                </a>
                @endif

                <a href="{{ route('daily-progress') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('daily-progress*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Daily Activity</span>
                </a>

                @if($user?->hasPermission('schedule.view'))
                <a href="{{ route('schedule') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('schedule*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Schedule</span>
                </a>
                @endif

                @if($user?->hasPermission('notification.view'))
                <a href="{{ route('notifications') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('notifications*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-show="sidebarOpen" class="truncate">Notifications</span>
                    </div>
                    @php $unreadCount = \App\Models\Notification::where('user_id', $user?->id)->where('is_read', false)->count(); @endphp
                    @if($unreadCount > 0)
                        <span x-show="sidebarOpen" class="bg-red-500 text-white text-xs font-extrabold px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                    @endif
                </a>
                @endif

                @if($user?->hasPermission('finance.view'))
                <a href="{{ route('finance') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('finance*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Finance</span>
                </a>
                @endif

                @if($user?->hasPermission('asset.view'))
                <a href="{{ route('assets') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('assets*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Assets & Infra</span>
                </a>
                @endif

                @if($user?->hasPermission('user.view'))
                <a href="{{ route('users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('users*') ? 'bg-brand-600 text-white shadow-md shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Team Users</span>
                </a>
                @endif
            </nav>

            <!-- Bottom User Profile Card -->
            <div class="p-3 border-t border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-slate-700 font-bold text-xs flex items-center justify-center text-slate-200 shrink-0">
                        {{ strtoupper(substr($user?->name ?? 'U', 0, 2)) }}
                    </div>
                    <div x-show="sidebarOpen" class="flex flex-col min-w-0">
                        <span class="text-xs font-semibold text-slate-200 truncate">{{ $user?->name }}</span>
                        <span class="text-[10px] text-brand-400 font-medium truncate">{{ str_replace('_', ' ', $user?->role) }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen">
                    @csrf
                    <button type="submit" title="Logout" class="text-slate-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-950">
            
            <!-- Top App Header -->
            <header class="h-16 bg-slate-900/90 backdrop-blur border-b border-slate-800 flex items-center justify-between px-4 md:px-6 z-20 shrink-0">
                <!-- Left: Mobile Toggle & Page Search Trigger -->
                <div class="flex items-center gap-3">
                    <button @click="mobileMenuOpen = true" class="md:hidden text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <!-- Global Search Button -->
                    <button @click="$dispatch('open-global-search')" class="hidden sm:flex items-center gap-3 px-3 py-1.5 rounded-xl bg-slate-800 text-slate-400 hover:text-slate-200 border border-slate-700/60 text-xs w-64 hover:border-slate-600 transition-all">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Global Search... (Ctrl+K)</span>
                    </button>
                </div>

                <!-- Right: Quick Actions & Notifications -->
                <div class="flex items-center gap-3">
                    <button @click="$dispatch('open-global-search')" class="sm:hidden text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    <!-- Notification Bell -->
                    @php $unreadHeader = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                    <a href="{{ route('notifications') }}" class="relative text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if($unreadHeader > 0)
                            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full ring-2 ring-slate-900 animate-pulse"></span>
                        @endif
                    </a>

                    <!-- User Pill -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-800">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 font-bold text-xs flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="hidden lg:inline text-xs font-semibold text-slate-300">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Body Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 pb-24 md:pb-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (Requirements 38) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-slate-900/95 backdrop-blur border-t border-slate-800 flex items-center justify-around z-40 px-2 text-[10px] font-semibold text-slate-400">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 p-1 {{ request()->routeIs('dashboard') ? 'text-brand-400 font-bold' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Home</span>
        </a>
        <a href="{{ route('projects') }}" class="flex flex-col items-center gap-1 p-1 {{ request()->routeIs('projects*') ? 'text-brand-400 font-bold' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <span>Projects</span>
        </a>
        <a href="{{ route('tasks') }}" class="flex flex-col items-center gap-1 p-1 {{ request()->routeIs('tasks*') ? 'text-brand-400 font-bold' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Tasks</span>
        </a>
        <a href="{{ route('notifications') }}" class="flex flex-col items-center gap-1 p-1 relative {{ request()->routeIs('notifications*') ? 'text-brand-400 font-bold' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span>Alerts</span>
        </a>
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center gap-1 p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>More</span>
        </button>
    </nav>

    <!-- Mobile Drawer Overlay Menu -->
    <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex flex-col justify-end">
        <div @click.outside="mobileMenuOpen = false" class="bg-slate-900 border-t border-slate-800 rounded-t-3xl p-6 space-y-4 max-h-[85vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center font-bold text-white">S</div>
                    <div>
                        <h4 class="font-bold text-white text-base">Solvia.Nova OS</h4>
                        <p class="text-xs text-slate-400">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <button @click="mobileMenuOpen = false" class="text-slate-400 text-2xl font-bold">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2 text-sm font-semibold">
                <a href="{{ route('dashboard') }}" class="p-3 bg-slate-800/80 rounded-xl flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('daily-progress') }}" class="p-3 bg-slate-800/80 rounded-xl flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Daily Progress
                </a>
                @if(auth()->user()->hasPermission('schedule.view'))
                <a href="{{ route('schedule') }}" class="p-3 bg-slate-800/80 rounded-xl flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Schedule
                </a>
                @endif
                @if(auth()->user()->hasPermission('finance.view'))
                <a href="{{ route('finance') }}" class="p-3 bg-slate-800/80 rounded-xl flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Finance
                </a>
                @endif
                @if(auth()->user()->hasPermission('asset.view'))
                <a href="{{ route('assets') }}" class="p-3 bg-slate-800/80 rounded-xl flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    Assets & Infra
                </a>
                @endif
                @if(auth()->user()->hasPermission('user.view'))
                <a href="{{ route('users') }}" class="p-3 bg-slate-800/80 rounded-xl flex items-center gap-3 text-slate-200">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Team Users
                </a>
                @endif
            </div>

            <form method="POST" action="{{ route('logout') }}" class="pt-4">
                @csrf
                <button type="submit" class="w-full py-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold rounded-xl text-center flex items-center justify-center gap-2 border border-red-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar / Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Livewire Global Search Component -->
    @livewire('global-search')

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW registration failed:', err));
            });
        }
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                Livewire.dispatch('open-global-search');
            }
        });
    </script>
</body>
</html>
