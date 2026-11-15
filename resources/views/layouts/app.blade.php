<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Cash Advance & Settlement') - PT Technodrives</title>
    
    <!-- Google Fonts: Poppins (700 for headings, 600 for numbers/buttons) & Fira Sans (400/500 for body, labels, tables) per PT Technodrives Official UI Guide -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons & Lucide Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind CSS CDN & Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('assets/js/tailwind-config.js') }}"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Custom Application CSS (Separated from Blade) -->
    <link rel="stylesheet" href="{{ asset('assets/css/app-custom.css') }}">

    <!-- Dedicated Load Loader Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/css/loader.css') }}">
</head>
<body class="min-h-[100dvh] antialiased bg-[#F4F4F4] text-[#7A7A7A]" x-data="{ mobileNavOpen: false, sidebarCollapsed: true, laporanOpen: true }">
    @php
        $unreadCount = auth()->user() ? auth()->user()->unreadNotifications()->count() : 0;
        $isJustLoggedIn = session('login_loader') || session('just_logged_in');
    @endphp

    <div class="min-h-screen flex">
        <!-- Desktop Persistent Sidebar (Visible on md:flex) -->
        <aside class="hidden md:flex flex-col bg-[#050A1E] text-white border-r border-[#161A2B] sticky top-0 h-screen z-40 transition-all duration-300 flex-shrink-0 no-print"
            :class="sidebarCollapsed ? 'w-20' : 'w-64'">
            
            <!-- Brand Logo Header in Sidebar -->
            <div class="border-b border-[#161A2B] flex items-center justify-center h-20 transition-all duration-300"
                :class="sidebarCollapsed ? 'px-2' : 'px-4'">
                <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center group" title="PT Technodrives - Dashboard">
                    <!-- Collapsed View: Dedicated Circular Emblem (TD Icon) -->
                    <div x-show="sidebarCollapsed" class="w-11 h-11 bg-white rounded-xl p-1.5 flex items-center justify-center shadow-md border border-slate-200 mx-auto transition-transform duration-200 group-hover:scale-105">
                        <img src="{{ asset('assets/images/logo-icon.png') }}" alt="PT Technodrives" class="w-full h-full object-contain">
                    </div>

                    <!-- Expanded View: Full White Card displaying the complete official logo -->
                    <div x-show="!sidebarCollapsed" class="w-full bg-white rounded-xl py-2 px-3 flex items-center justify-center shadow-sm border border-slate-200 transition-all duration-200 group-hover:shadow-md">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="PT Technodrives" class="h-7 w-auto object-contain">
                    </div>
                </a>
            </div>

            <!-- Current User Profile in Sidebar (Expanded & Collapsed) -->
            <a href="{{ route('profile.edit') }}" class="p-3 mx-3 my-2.5 bg-[#161A2B] hover:bg-[#1f243b] rounded-xl border border-white/5 flex items-center space-x-3 transition group cursor-pointer" x-show="!sidebarCollapsed" x-transition title="Klik untuk edit profil & biodata">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover shadow-sm flex-shrink-0 border border-white/20">
                @else
                    <div class="w-9 h-9 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xs uppercase shadow-sm flex-shrink-0">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="font-head font-bold text-xs text-white truncate group-hover:text-[#FF3C00] transition flex items-center justify-between">
                        <span class="truncate">{{ auth()->user()->name }}</span>
                        <i data-lucide="user-cog" class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#FF3C00] flex-shrink-0 ml-1"></i>
                    </div>
                    <div class="flex items-center flex-wrap gap-1 mt-1">
                        <span class="text-[9px] font-head uppercase font-bold px-2 py-0.5 rounded-full
                            {{ auth()->user()->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                            {{ auth()->user()->role === 'finance' ? 'bg-white/20 text-white' : '' }}
                            {{ auth()->user()->role === 'atasan' ? 'bg-[#050A1E] text-white border border-white/20' : '' }}
                            {{ auth()->user()->role === 'karyawan' ? 'bg-white/10 text-slate-300' : '' }}
                            {{ auth()->user()->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                        ">{{ auth()->user()->role }}</span>
                        @if(!empty(auth()->user()->departments))
                            @foreach(array_slice(auth()->user()->departments, 0, 2) as $dept)
                                <span class="text-[9px] text-slate-300 bg-white/10 px-1.5 py-0.5 rounded font-mono truncate max-w-[110px]" title="# {{ $dept }}"># {{ $dept }}</span>
                            @endforeach
                            @if(count(auth()->user()->departments) > 2)
                                <span class="text-[8px] text-slate-400 font-mono font-bold">+{{ count(auth()->user()->departments) - 2 }}</span>
                            @endif
                        @else
                            <span class="text-[9px] font-mono text-slate-400 bg-white/5 px-1.5 py-0.5 rounded" title="Tidak ada tag">#</span>
                        @endif
                    </div>
                </div>
            </a>

            <!-- Collapsed User Avatar -->
            <div class="py-2.5 flex justify-center" x-show="sidebarCollapsed" x-transition>
                <a href="{{ route('profile.edit') }}" title="Pengaturan Profil: {{ auth()->user()->name }} ({{ auth()->user()->role }})" class="cursor-pointer">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full object-cover shadow-sm border border-white/20 hover:scale-105 transition">
                    @else
                        <div class="w-9 h-9 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xs uppercase shadow-sm hover:scale-105 transition">
                            {{ auth()->user()->initials }}
                        </div>
                    @endif
                </a>
            </div>

            <!-- Scrollable Navigation Items -->
            <div class="flex-1 overflow-y-auto px-3 py-2 space-y-4 text-xs font-head scrollbar-thin">
                <!-- Group: Menu Utama -->
                <div>
                    <p class="px-3 text-[10px] font-head font-bold uppercase tracking-wider text-slate-400 mb-1" x-show="!sidebarCollapsed">Menu Utama</p>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-[#FF3C00] text-white font-bold shadow-md shadow-[#FF3C00]/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}" 
                            :class="sidebarCollapsed ? 'justify-center' : ''"
                            title="Dashboard">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0"></i>
                            <span x-show="!sidebarCollapsed">Dashboard</span>
                        </a>

                        <a href="{{ route('cash-advances.index') }}" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cash-advances.*') && !request()->routeIs('cash-advances.create') ? 'bg-[#FF3C00] text-white font-bold shadow-md shadow-[#FF3C00]/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}" 
                            :class="sidebarCollapsed ? 'justify-center' : ''"
                            title="{{ auth()->user()->isKaryawan() ? 'CA Saya' : 'Daftar CA' }}">
                            <i data-lucide="receipt" class="w-4 h-4 flex-shrink-0"></i>
                            <span x-show="!sidebarCollapsed">{{ auth()->user()->isKaryawan() ? 'CA Saya' : 'Daftar CA' }}</span>
                        </a>

                        @if(auth()->user()->canSubmitCashAdvance() && !auth()->user()->isFinance() && !auth()->user()->isDirektur())
                            <a href="{{ route('cash-advances.create') }}" 
                                class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('cash-advances.create') ? 'bg-[#FF3C00] text-white font-bold' : 'text-emerald-400 hover:text-emerald-300 hover:bg-white/5' }}" 
                                :class="sidebarCollapsed ? 'justify-center' : ''"
                                title="Ajukan CA Baru">
                                <i data-lucide="plus-circle" class="w-4 h-4 flex-shrink-0"></i>
                                <span x-show="!sidebarCollapsed">Ajukan CA Baru</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Group: Laporan Manajemen (Direktur, Finance, Atasan) -->
                @if(auth()->user()->isFinance() || auth()->user()->isDirektur() || auth()->user()->isAtasan())
                    <div class="pt-2 border-t border-white/5">
                        <div class="flex items-center justify-between px-3 text-[10px] font-head font-bold uppercase tracking-wider text-slate-400 mb-1 cursor-pointer select-none"
                            @click="laporanOpen = !laporanOpen" x-show="!sidebarCollapsed">
                            <span>Laporan Manajemen</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': laporanOpen }"></i>
                        </div>

                        <!-- Sub-links when expanded -->
                        <div class="space-y-0.5" x-show="laporanOpen || sidebarCollapsed">
                            <a href="{{ route('reports.rincian-ca') }}" 
                                class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('reports.rincian-ca') ? 'bg-[#161A2B] text-[#FF3C00] font-bold border-l-2 border-[#FF3C00]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                                :class="sidebarCollapsed ? 'justify-center' : ''"
                                title="1. Rincian per CA">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#2E3087] flex-shrink-0"></i>
                                <span x-show="!sidebarCollapsed">1. Rincian per CA</span>
                            </a>

                            @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
                                <a href="{{ route('reports.rekap-bulanan') }}" 
                                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('reports.rekap-bulanan') ? 'bg-[#161A2B] text-[#FF3C00] font-bold border-l-2 border-[#FF3C00]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                                    :class="sidebarCollapsed ? 'justify-center' : ''"
                                    title="2. Rekap Bulanan Pajak">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#FF3C00] flex-shrink-0"></i>
                                    <span x-show="!sidebarCollapsed">2. Rekap Bulanan Pajak</span>
                                </a>

                                <a href="{{ route('reports.outstanding') }}" 
                                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('reports.outstanding') ? 'bg-[#161A2B] text-[#FF3C00] font-bold border-l-2 border-[#FF3C00]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                                    :class="sidebarCollapsed ? 'justify-center' : ''"
                                    title="3. CA Menggantung">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-[#DC2626] flex-shrink-0"></i>
                                    <span x-show="!sidebarCollapsed">3. CA Menggantung</span>
                                </a>

                                <a href="{{ route('reports.rekap-karyawan') }}" 
                                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('reports.rekap-karyawan') ? 'bg-[#161A2B] text-[#FF3C00] font-bold border-l-2 border-[#FF3C00]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                                    :class="sidebarCollapsed ? 'justify-center' : ''"
                                    title="4. Rekap per Karyawan">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-[#16A34A] flex-shrink-0"></i>
                                    <span x-show="!sidebarCollapsed">4. Rekap per Karyawan</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Group: Audit Trail (Direktur, Finance, SuperAdmin) -->
                @if(auth()->user()->isDirektur() || auth()->user()->isFinance() || auth()->user()->isSuperAdmin())
                    <div class="pt-2 border-t border-white/5">
                        <p class="px-3 text-[10px] font-head font-bold uppercase tracking-wider text-slate-400 mb-1" x-show="!sidebarCollapsed">Audit & Kontrol</p>
                        <a href="{{ route('audit-logs.index') }}" 
                            class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('audit-logs.*') ? 'bg-[#FF3C00] text-white font-bold shadow-md shadow-[#FF3C00]/20' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                            :class="sidebarCollapsed ? 'justify-center' : ''"
                            title="Audit Trail">
                            <i data-lucide="history" class="w-4 h-4 flex-shrink-0"></i>
                            <span x-show="!sidebarCollapsed">Audit Trail</span>
                        </a>
                    </div>
                @endif

                <!-- Group: Administrasi IT (SuperAdmin) -->
                @if(auth()->user()->isSuperAdmin())
                    <div class="pt-2 border-t border-white/5">
                        <p class="px-3 text-[10px] font-head font-bold uppercase tracking-wider text-slate-400 mb-1" x-show="!sidebarCollapsed">Administrasi IT</p>
                        <div class="space-y-1">
                            <a href="{{ route('admin.users.index') }}" 
                                class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-[#FF3C00] text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                                :class="sidebarCollapsed ? 'justify-center' : ''"
                                title="Kelola User">
                                <i data-lucide="users" class="w-4 h-4 flex-shrink-0"></i>
                                <span x-show="!sidebarCollapsed">Kelola User</span>
                            </a>
                            <a href="{{ route('admin.settings.index') }}" 
                                class="flex items-center space-x-3 px-3 py-2 rounded-xl transition {{ request()->routeIs('admin.settings.*') ? 'bg-[#FF3C00] text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                                :class="sidebarCollapsed ? 'justify-center' : ''"
                                title="Pengaturan Global">
                                <i data-lucide="settings" class="w-4 h-4 flex-shrink-0"></i>
                                <span x-show="!sidebarCollapsed">Pengaturan Global</span>
                            </a>
                        </div>
                    </div>
                @endif


            </div>

            <!-- Sidebar Footer: Logout Button -->
            <div class="p-3 border-t border-[#161A2B] bg-[#050A1E]/80">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" 
                        class="w-full py-2 px-3 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 text-xs font-bold flex items-center justify-center space-x-2 transition cursor-pointer"
                        :class="sidebarCollapsed ? 'p-2' : ''"
                        title="Logout">
                        <i data-lucide="log-out" class="w-3.5 h-3.5 flex-shrink-0"></i>
                        <span x-show="!sidebarCollapsed">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace (Topbar Header + Content + Footer) -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <!-- Top Primary Header (Sticky, Shadow, Mobile Friendly & Desktop Toggle Bar) -->
            <header class="bg-[#050A1E] text-white sticky top-0 z-30 border-b border-[#161A2B] no-print shadow-[0_2px_5px_-2px_rgba(0,0,0,0.1)]">
                <div class="w-full px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Left: Hamburger Button (Mobile) + Official Brand Logo on Crisp Light Container -->
                <div class="flex items-center space-x-3 md:hidden">
                    <!-- Mobile Hamburger Button -->
                    <button @click="mobileNavOpen = true" type="button" 
                        class="p-2 rounded-xl text-slate-200 hover:bg-white/10 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-[#FF3C00]"
                        aria-label="Buka Menu Navigasi">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>

                    <!-- Official Logo PT Technodrives (Mobile) -->
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                        <div class="bg-white rounded-xl px-2.5 py-1 flex items-center justify-center shadow-sm border border-slate-200">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="PT Technodrives" class="h-7 w-auto object-contain">
                        </div>
                    </a>
                </div>

                <!-- Left: Desktop Sidebar Toggle & Breadcrumb Title -->
                <div class="hidden md:flex items-center space-x-3">
                    <button @click="sidebarCollapsed = !sidebarCollapsed" type="button" 
                        class="p-2 rounded-xl text-slate-300 hover:bg-white/10 hover:text-white transition focus:outline-none focus:ring-1 focus:ring-[#FF3C00]"
                        :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'">
                        <i data-lucide="panel-left" class="w-5 h-5"></i>
                    </button>
                    <div class="flex items-center space-x-2 text-xs font-head font-medium text-slate-300">
                        <span class="font-bold text-white tracking-wide">Cash Advance & Settlement</span>
                        <span class="text-slate-500">&bull;</span>
                        <span class="text-[#FF3C00] font-semibold uppercase tracking-wider">PT. Technodrives</span>
                    </div>
                </div>

                <!-- User & Notifications Controls -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <!-- Notifications Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button @click="open = !open" type="button" class="p-2 rounded-lg hover:bg-white/10 relative text-slate-200 focus:outline-none transition">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-mono font-bold leading-none text-slate-900 bg-retail-orange rounded-full">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="open" x-cloak @click.stop
                            x-transition:enter="transition ease-out duration-150 transform"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100 transform"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-2 w-80 sm:w-96 rounded-xl shadow-2xl bg-white text-slate-800 border border-slate-200 py-2 z-50 overflow-hidden">
                            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                                <span class="text-xs font-bold uppercase text-slate-600 tracking-wider">Notifikasi ({{ $unreadCount }})</span>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-retail-cyan hover:underline font-semibold">Tandai semua dibaca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                                @forelse(auth()->user()->unreadNotifications()->take(6)->get() as $notif)
                                    <div class="p-3 hover:bg-slate-50 transition">
                                        <div class="flex items-start justify-between">
                                            <p class="text-xs font-bold text-slate-900">{{ $notif->title }}</p>
                                            <span class="text-[10px] font-mono text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $notif->message }}</p>
                                        @if($notif->link)
                                            <form action="{{ route('notifications.read', $notif) }}" method="POST" class="mt-2">
                                                @csrf
                                                <button type="submit" class="text-[11px] font-semibold text-retail-cyan hover:underline inline-flex items-center space-x-1">
                                                    <span>Lihat detail</span>
                                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-xs text-slate-400">
                                        Tidak ada notifikasi baru
                                    </div>
                                @endforelse
                            </div>
                            <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-slate-700 hover:text-retail-orange font-semibold">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile & Role Pill -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button @click="open = !open" type="button" class="flex items-center space-x-2 p-1.5 rounded-xl hover:bg-white/10 transition focus:outline-none border border-transparent hover:border-slate-700 cursor-pointer">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover shadow-sm border border-white/20 flex-shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xs uppercase shadow-sm flex-shrink-0">
                                    {{ auth()->user()->initials }}
                                </div>
                            @endif
                            <div class="hidden sm:block text-left text-xs leading-tight">
                                <span class="font-bold text-white block truncate max-w-[120px] font-head">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] font-head uppercase font-bold tracking-wider px-2 py-0.5 rounded-full inline-block
                                    {{ auth()->user()->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                    {{ auth()->user()->role === 'finance' ? 'bg-[#FF3C00] text-white' : '' }}
                                    {{ auth()->user()->role === 'atasan' ? 'bg-[#161A2B] text-white border border-slate-600' : '' }}
                                    {{ auth()->user()->role === 'karyawan' ? 'bg-white/20 text-white' : '' }}
                                    {{ auth()->user()->role === 'super_admin' ? 'bg-[#FF3B00] text-white' : '' }}
                                ">
                                    {{ auth()->user()->role }}
                                </span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-300 hidden sm:block transition-transform duration-150" :class="{ 'rotate-180': open }"></i>
                        </button>

                        <!-- User Dropdown Menu -->
                        <div x-show="open" x-cloak @click.stop
                            x-transition:enter="transition ease-out duration-150 transform"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100 transform"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-2 w-72 sm:w-80 rounded-[15px] shadow-2xl bg-white text-[#050A1E] border border-cardborder border-t-[3px] border-t-[#FF3C00] py-2 z-50">
                            
                            <!-- User Info Header -->
                            <div class="px-4 py-3 border-b border-slate-100 bg-[#F4F4F4] flex items-center space-x-3">
                                @if(auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover border border-cardborder shadow-sm flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-sm uppercase shadow-sm flex-shrink-0">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-[#050A1E] font-head truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[11px] font-mono text-[#7A7A7A] truncate">{{ auth()->user()->email }}</p>
                                    <div class="flex flex-wrap items-center gap-1 mt-1">
                                        <span class="text-[9px] font-head uppercase font-bold tracking-wider px-2 py-0.5 rounded-full inline-block
                                            {{ auth()->user()->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                            {{ auth()->user()->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                            {{ auth()->user()->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                            {{ auth()->user()->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#050A1E] border border-cardborder' : '' }}
                                            {{ auth()->user()->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                        ">{{ auth()->user()->role }}</span>
                                        @forelse(auth()->user()->departments as $dept)
                                            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-white text-[#050A1E] border border-cardborder"># {{ $dept }}</span>
                                        @empty
                                            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-white text-slate-400 border border-cardborder" title="Tidak ada tag">#</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- User Profile Setting Link (Edit Biodata & Foto) -->
                            <div class="px-2 py-1.5 border-b border-slate-100">
                                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold font-head text-[#050A1E] hover:bg-[#FF3C00] hover:text-white transition group">
                                    <i data-lucide="user-cog" class="w-4 h-4 text-[#FF3C00] group-hover:text-white transition"></i>
                                    <span>Pengaturan Profil (Biodata & Foto)</span>
                                </a>
                            </div>

                            <form action="{{ route('logout') }}" method="POST" class="mt-1">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-[#FF3C00] hover:bg-orange-50 font-bold flex items-center space-x-2 font-head cursor-pointer">
                                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Off-Canvas Mobile Drawer Navigation -->
    <div x-show="mobileNavOpen" x-cloak class="relative z-50 md:hidden" role="dialog" aria-modal="true">
        <!-- Backdrop Overlay -->
        <div x-show="mobileNavOpen" 
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileNavOpen = false"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

        <!-- Slide-Out Drawer Panel -->
        <div class="fixed inset-y-0 left-0 flex max-w-full pr-10">
            <div x-show="mobileNavOpen"
                x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="w-screen max-w-xs bg-white text-slate-800 shadow-2xl flex flex-col justify-between overflow-y-auto">
                
                <!-- Drawer Top Header -->
                <div>
                    <div class="p-4 bg-[#050A1E] text-white flex items-center justify-between border-b border-[#161A2B]">
                        <div class="flex items-center space-x-2.5">
                            <div class="bg-white rounded-lg px-2.5 py-1 shadow-sm border border-slate-200">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="PT Technodrives" class="h-6 w-auto object-contain">
                            </div>
                            <div>
                                <div class="font-head font-bold text-sm leading-tight text-white">Technodrives CA</div>
                                <span class="text-[10px] text-[#FF3C00] font-head font-semibold uppercase tracking-wider">PT. Technodrives</span>
                            </div>
                        </div>
                        <button @click="mobileNavOpen = false" class="p-2 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition" aria-label="Tutup Menu">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Current User Profile in Drawer -->
                    <a href="{{ route('profile.edit') }}" @click="mobileNavOpen = false" class="p-4 bg-slate-50 hover:bg-slate-100 border-b border-slate-200 flex items-center space-x-3 transition group cursor-pointer" title="Edit Profil & Biodata">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xs uppercase shadow-sm flex-shrink-0">
                                {{ auth()->user()->initials }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="font-head font-bold text-sm text-[#050A1E] group-hover:text-[#FF3C00] transition flex items-center justify-between">
                                <span class="truncate">{{ auth()->user()->name }}</span>
                                <i data-lucide="user-cog" class="w-3.5 h-3.5 text-slate-400 group-hover:text-[#FF3C00] flex-shrink-0 ml-1"></i>
                            </div>
                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                <span class="badge-retail text-[10px] uppercase font-bold
                                    {{ auth()->user()->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                    {{ auth()->user()->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                    {{ auth()->user()->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                    {{ auth()->user()->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1]' : '' }}
                                    {{ auth()->user()->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                ">
                                    {{ auth()->user()->role }}
                                </span>
                                @forelse(auth()->user()->departments as $dept)
                                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-[#050A1E] border border-slate-200"># {{ $dept }}</span>
                                @empty
                                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-400 border border-slate-200" title="Tidak ada tag">#</span>
                                @endforelse
                            </div>
                        </div>
                    </a>

                    <!-- Drawer Navigation Links -->
                    <nav class="p-3 space-y-1 text-sm font-semibold font-body">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-[#161A2B] text-[#FF3C00] font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-[#050A1E]' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 text-[#FF3C00]"></i>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('cash-advances.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('cash-advances.index') ? 'bg-[#161A2B] text-[#FF3C00] font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-[#050A1E]' }}">
                            <i data-lucide="receipt" class="w-4 h-4 text-[#FF3C00]"></i>
                            <span>{{ auth()->user()->isKaryawan() ? 'CA Saya' : 'Daftar Cash Advance' }}</span>
                        </a>
                        @if(auth()->user()->canSubmitCashAdvance() && !auth()->user()->isFinance() && !auth()->user()->isDirektur())
                            <a href="{{ route('cash-advances.create') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition text-slate-700 hover:bg-slate-100 hover:text-[#050A1E]">
                                <i data-lucide="plus-circle" class="w-4 h-4 text-[#16A34A]"></i>
                                <span>Ajukan CA Baru</span>
                            </a>
                        @endif

                        @if(auth()->user()->isFinance() || auth()->user()->isDirektur() || auth()->user()->isAtasan())
                            <div class="pt-2">
                                <p class="px-3 text-[10px] font-head font-bold uppercase tracking-wider text-slate-400 mb-1">Laporan Manajemen</p>
                                <div class="space-y-0.5 pl-2">
                                    <a href="{{ route('reports.rincian-ca') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs text-slate-700 hover:bg-slate-100 hover:text-[#FF3C00]">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#2E3087]"></i>
                                        <span>1. Rincian per CA</span>
                                    </a>
                                    @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
                                        <a href="{{ route('reports.rekap-bulanan') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs text-slate-700 hover:bg-slate-100 hover:text-[#FF3C00]">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                                            <span>2. Rekap Bulanan Pajak</span>
                                        </a>
                                        <a href="{{ route('reports.outstanding') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs text-slate-700 hover:bg-slate-100 hover:text-[#FF3C00]">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-[#DC2626]"></i>
                                            <span>3. CA Menggantung</span>
                                        </a>
                                        <a href="{{ route('reports.rekap-karyawan') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs text-slate-700 hover:bg-slate-100 hover:text-[#FF3C00]">
                                            <i data-lucide="users" class="w-3.5 h-3.5 text-[#16A34A]"></i>
                                            <span>4. Rekap per Karyawan</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if(auth()->user()->isDirektur() || auth()->user()->isFinance() || auth()->user()->isSuperAdmin())
                            <a href="{{ route('audit-logs.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('audit-logs.*') ? 'bg-[#161A2B] text-[#FF3C00] font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-100 hover:text-[#050A1E]' }}">
                                <i data-lucide="history" class="w-4 h-4 text-slate-500"></i>
                                <span>Audit Trail</span>
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin())
                            <div class="pt-2">
                                <p class="px-3 text-[10px] font-head font-bold uppercase tracking-wider text-slate-400 mb-1">Administrasi IT</p>
                                <div class="space-y-0.5 pl-2">
                                    <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs text-slate-700 hover:bg-slate-100">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-slate-500"></i>
                                        <span>Kelola User</span>
                                    </a>
                                    <a href="{{ route('admin.settings.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs text-slate-700 hover:bg-slate-100">
                                        <i data-lucide="settings" class="w-3.5 h-3.5 text-slate-500"></i>
                                        <span>Pengaturan Global</span>
                                    </a>
                                </div>
                            </div>
                        @endif


                    </nav>
                </div>

                <!-- Drawer Footer Logout -->
                <div class="p-3 border-t border-slate-200 bg-white">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-3 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold flex items-center justify-center space-x-2 transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 pb-8 md:pb-10">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-lg shadow-sm flex items-start space-x-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-emerald-950 font-medium leading-relaxed">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg shadow-sm flex items-start space-x-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-amber-950 font-medium leading-relaxed">{{ session('warning') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-rose-50 border-l-4 border-rose-600 p-4 rounded-lg shadow-sm flex items-start space-x-3">
                <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-rose-950 font-medium leading-relaxed">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 bg-sky-50 border-l-4 border-sky-500 p-4 rounded-lg shadow-sm flex items-start space-x-3">
                <i data-lucide="info" class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-sky-950 font-medium leading-relaxed">{{ session('info') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- App-Like Mobile Bottom Navigation Bar (Visible only on mobile md:hidden) -->
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-[#050A1E]/95 backdrop-blur-md border-t border-[#161A2B] pt-2 px-3 sm:px-6 md:hidden shadow-[0_-4px_16px_rgba(0,0,0,0.3)] no-print" style="padding-bottom: max(0.6rem, env(safe-area-inset-bottom));">
        @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
            <!-- Role Finance & Direktur (4-Tab Bar: Beranda, Daftar CA, Laporan, Audit) -->
            <div class="grid grid-cols-4 items-center max-w-sm mx-auto text-center">
                <!-- Tab 1: Beranda -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Beranda</span>
                </a>

                <!-- Tab 2: Daftar CA -->
                <a href="{{ route('cash-advances.index') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('cash-advances.*') && !request()->routeIs('cash-advances.create') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Daftar CA</span>
                </a>

                <!-- Tab 3: Laporan -->
                <a href="{{ route('reports.rincian-ca') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('reports.*') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Laporan</span>
                </a>

                <!-- Tab 4: Audit Trail -->
                <a href="{{ route('audit-logs.index') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('audit-logs.*') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="history" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Audit</span>
                </a>
            </div>
        @elseif(auth()->user()->isSuperAdmin())
            <!-- Role Super Admin (4-Tab Bar: Beranda, Users, Audit, Setting) -->
            <div class="grid grid-cols-4 items-center max-w-sm mx-auto text-center">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Beranda</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Users</span>
                </a>
                <a href="{{ route('audit-logs.index') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('audit-logs.*') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="history" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Audit</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('admin.settings.*') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Setting</span>
                </a>
            </div>
        @elseif(auth()->user()->isAtasan())
            <!-- Role Atasan (4-Tab Bar: Beranda, CA Tim, Laporan, Profil) -->
            <div class="grid grid-cols-4 items-center max-w-sm mx-auto text-center">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Beranda</span>
                </a>
                <a href="{{ route('cash-advances.index') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('cash-advances.*') && !request()->routeIs('cash-advances.create') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">CA Tim</span>
                </a>
                <a href="{{ route('reports.rincian-ca') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('reports.*') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Laporan</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('profile.*') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="user-cog" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Profil</span>
                </a>
            </div>
        @else
            <!-- Role Karyawan (3-Tab Bar: Beranda, + Ajukan, CA Saya) -->
            <div class="grid grid-cols-3 items-center max-w-xs mx-auto text-center">
                <!-- Tab 1: Beranda -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Beranda</span>
                </a>

                <!-- Tab 2: Center Elevated Action (+ Ajukan) -->
                <a href="{{ route('cash-advances.create') }}" class="flex flex-col items-center -mt-6 group" title="Ajukan CA Baru">
                    <div class="w-13 h-13 rounded-full bg-[#FF3B00] text-white flex items-center justify-center shadow-[0_4px_14px_rgba(255,59,0,0.5)] border-2 border-[#050A1E] group-hover:scale-105 active:scale-95 transition-transform duration-150 p-3">
                        <i data-lucide="plus" class="w-6 h-6 stroke-[2.5]"></i>
                    </div>
                    <span class="text-[10px] mt-0.5 font-head font-bold text-slate-200">Ajukan</span>
                </a>

                <!-- Tab 3: CA Saya -->
                <a href="{{ route('cash-advances.index') }}" class="flex flex-col items-center py-1 px-1 rounded-xl transition {{ request()->routeIs('cash-advances.*') && !request()->routeIs('cash-advances.create') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">CA Saya</span>
                </a>
            </div>
        @endif
    </nav>

    <!-- Bottom Functional Footer (PT. Technodrives Official Info - Diberi pb-24 di mobile agar tidak tertutup nav) -->
    <footer class="bg-[#050A1E] text-[#7A7A7A] border-t border-[#161A2B] text-xs pt-7 pb-24 md:pb-7 mt-auto no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-white/5 pb-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-white rounded-lg p-1.5 flex items-center justify-center shadow-sm">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="PT Technodrives" class="h-6 w-auto">
                    </div>
                    <div>
                        <span class="font-head font-bold text-sm text-white block">PT. Technodrives</span>
                        <span class="text-[11px] text-[#7A7A7A] block font-body">Expert Electrical Solutions & Testing for Industrial and Data Center Infrastructure</span>
                    </div>
                </div>
                <div class="flex items-center space-x-3 text-[11px] font-head">
                    <span class="px-2.5 py-1 rounded-full bg-[#161A2B] text-[#FF3C00] border border-[#FF3C00]/30 font-semibold">Cash Advance & Settlement</span>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center justify-between text-[11px] text-[#7A7A7A] font-body gap-2">
                <div>
                    Kantor Operasional: Jl. Pisok XIX Blok EB 15A No. 31, Bintaro Jaya Sektor 5, Tangerang Selatan | Telp: (021) 7364033
                </div>
                <div>
                    &copy; 2026 PT. Technodrives &bull; <a href="https://www.technodrives.co.id/" target="_blank" class="text-slate-400 hover:text-[#FF3C00] underline">technodrives.co.id</a>
                </div>
            </div>
        </div>
    </footer>
        </div>
    </div>

    <!-- Application Scripts -->
    <script src="{{ asset('assets/js/app-scripts.js') }}"></script>

    <!-- Dedicated Load Loader Script -->
    <script src="{{ asset('assets/js/loader.js') }}"
        data-logo="{{ asset('assets/images/logo.png') }}"
        data-just-logged-in="{{ $isJustLoggedIn ? 'true' : 'false' }}"
        data-user-name="{{ auth()->user()?->name }}"
        data-user-role="{{ auth()->user()?->role }}"></script>
    @stack('scripts')
</body>
</html>
