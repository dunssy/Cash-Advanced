<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Cash Advance & Settlement') - PT Technodrives</title>
    
    <!-- Google Fonts: JetBrains Mono for code, currency & technical metadata -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Verdana', 'Geneva', 'Tahoma', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        retail: {
                            orange: '#FF9900',       // primary
                            orangeHover: '#E68A00',  // primary hover
                            ink: '#232F3E',          // neutral / dark surface
                            darkInk: '#131921',      // secondary / off-black
                            cyan: '#00A8E1',         // accent
                            green: '#008000',        // success
                            gold: '#FFD700',         // premium
                            surface: '#DDDDDD',      // border & surface
                            card: '#FFFFFF',         // tertiary
                            muted: '#555555',
                            bg: '#F7F8FA',
                        }
                    },
                    borderRadius: {
                        'sm': '3px',
                        'DEFAULT': '3px',
                        'md': '6px',
                        'lg': '9px',
                        'xl': '9px',
                        '2xl': '9px',
                        '3xl': '9px',
                        'full': '9999px',
                    },
                    boxShadow: {
                        'card': '0 2px 12px rgba(0, 0, 0, 0.06)',
                        'lift': '0 4px 14px rgba(0, 0, 0, 0.12)',
                        'active': '0 1px 3px rgba(0, 0, 0, 0.15)',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            font-size: 1rem;
            line-height: 1.6;
            background-color: #F7F8FA;
            color: #131921;
        }

        /* 60fps Compositor-Only Transitions & Design.md Architecture */
        .btn-primary {
            position: relative;
            isolation: isolate;
            background-color: #FF9900;
            color: #131921;
            font-weight: 600;
            border-radius: 3px;
            padding: 9px 16px;
            font-size: 0.875rem;
            border: 1px solid #D68100;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transform: translateZ(0);
            transition: background-color 160ms ease, transform 100ms cubic-bezier(0.2, 0, 0, 1);
            will-change: transform;
        }
        .btn-primary::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            box-shadow: 0 4px 14px rgba(255, 153, 0, 0.40);
            opacity: 0;
            transition: opacity 160ms ease;
            z-index: -1;
            pointer-events: none;
        }
        .btn-primary:hover {
            background-color: #E68A00;
        }
        .btn-primary:hover::after {
            opacity: 1;
        }
        .btn-primary:active {
            transform: translateY(1px) scale(0.99);
        }

        .btn-secondary {
            position: relative;
            background-color: #FFFFFF;
            color: #232F3E;
            font-weight: 600;
            border-radius: 3px;
            padding: 9px 16px;
            font-size: 0.875rem;
            border: 1.5px solid #DDDDDD;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transform: translateZ(0);
            transition: background-color 160ms ease, border-color 160ms ease, transform 100ms cubic-bezier(0.2, 0, 0, 1);
            will-change: transform;
        }
        .btn-secondary:hover {
            background-color: #F3F4F6;
            border-color: #232F3E;
        }
        .btn-secondary:active {
            transform: translateY(1px) scale(0.99);
        }

        .btn-dark {
            position: relative;
            background-color: #232F3E;
            color: #FFFFFF;
            font-weight: 600;
            border-radius: 3px;
            padding: 9px 16px;
            font-size: 0.875rem;
            border: 1px solid #131921;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transform: translateZ(0);
            transition: background-color 160ms ease, transform 100ms cubic-bezier(0.2, 0, 0, 1);
            will-change: transform;
        }
        .btn-dark:hover {
            background-color: #131921;
            color: #FF9900;
        }
        .btn-dark:active {
            transform: translateY(1px) scale(0.99);
        }

        .btn-cyan {
            position: relative;
            background-color: #00A8E1;
            color: #FFFFFF;
            font-weight: 600;
            border-radius: 3px;
            padding: 9px 16px;
            font-size: 0.875rem;
            border: 1px solid #0088B8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transform: translateZ(0);
            transition: background-color 160ms ease, transform 100ms cubic-bezier(0.2, 0, 0, 1);
            will-change: transform;
        }
        .btn-cyan:hover {
            background-color: #0088B8;
        }
        .btn-cyan:active {
            transform: translateY(1px) scale(0.99);
        }

        .btn-danger {
            position: relative;
            background-color: #DC2626;
            color: #FFFFFF;
            font-weight: 600;
            border-radius: 3px;
            padding: 9px 16px;
            font-size: 0.875rem;
            border: 1px solid #B91C1C;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transform: translateZ(0);
            transition: background-color 160ms ease, transform 100ms cubic-bezier(0.2, 0, 0, 1);
            will-change: transform;
        }
        .btn-danger:hover {
            background-color: #B91C1C;
        }
        .btn-danger:active {
            transform: translateY(1px) scale(0.99);
        }

        /* 60fps Card with Pseudo-Element Shadow Lift */
        .card-commerce {
            position: relative;
            background-color: #FFFFFF;
            border: 1px solid #DDDDDD;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transform: translateZ(0);
            transition: border-color 200ms ease, transform 200ms cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-commerce-hoverable {
            transition: border-color 200ms ease, transform 200ms cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-commerce-hoverable::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            box-shadow: 0 10px 24px rgba(35, 47, 62, 0.09);
            opacity: 0;
            transition: opacity 200ms ease;
            pointer-events: none;
        }
        .card-commerce-hoverable:hover {
            border-color: #CBD5E1;
            transform: translateY(-2px);
        }
        .card-commerce-hoverable:hover::after {
            opacity: 1;
        }

        /* Accessible Commerce Inputs */
        .input-commerce {
            border: 1px solid #DDDDDD;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 0.875rem;
            background-color: #FFFFFF;
            color: #131921;
            outline: none;
            transition: border-color 160ms ease, box-shadow 160ms ease;
        }
        .input-commerce:focus {
            border-color: #FF9900;
            box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.35);
        }

        /* Technical JetBrains Mono Badges */
        .badge-retail {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 3px;
            padding: 2px 8px;
            border-width: 1px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Reduced Motion Respect (WCAG 2.3.3) */
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; font-size: 10pt; }
            .print-full { width: 100% !important; margin: 0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="min-h-[100dvh] flex flex-col antialiased">
    
    <!-- Top Primary Header (Neutral #232F3E) -->
    <header class="bg-retail-ink text-white shadow-md sticky top-0 z-40 border-b border-retail-darkInk no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
                        <div class="w-9 h-9 rounded-[3px] bg-retail-orange flex items-center justify-center text-retail-darkInk shadow group-hover:bg-retail-orangeHover transition">
                            <i data-lucide="wallet-cards" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center space-x-1.5">
                                <span class="font-bold text-base tracking-tight text-white group-hover:text-retail-orange transition">Technodrives CA</span>
                                <span class="text-[10px] font-mono font-bold bg-retail-darkInk text-retail-orange border border-retail-orange/30 px-1.5 py-0.2 rounded-[3px]">PRO</span>
                            </div>
                            <span class="text-[10px] text-slate-300 uppercase tracking-widest block font-medium">PT Technodrives</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-2 text-xs font-semibold">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-[3px] transition flex items-center space-x-1.5 {{ request()->routeIs('dashboard') ? 'bg-retail-darkInk text-retail-orange border-b-2 border-retail-orange font-bold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    @if(!auth()->user()->isSuperAdmin())
                        <a href="{{ route('cash-advances.index') }}" class="px-3 py-2 rounded-[3px] transition flex items-center space-x-1.5 {{ request()->routeIs('cash-advances.*') ? 'bg-retail-darkInk text-retail-orange border-b-2 border-retail-orange font-bold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                            <span>Cash Advance</span>
                        </a>
                    @endif

                    @if(auth()->user()->isFinance() || auth()->user()->isDirektur() || auth()->user()->isAtasan())
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" class="px-3 py-2 rounded-[3px] transition flex items-center space-x-1 {{ request()->routeIs('reports.*') ? 'bg-retail-darkInk text-retail-orange font-bold' : 'text-slate-200 hover:bg-white/10' }}">
                                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                                <span>Laporan</span>
                                <i data-lucide="chevron-down" class="w-3 h-3 ml-0.5"></i>
                            </button>
                            <div x-show="open" x-cloak 
                                x-transition:enter="transition ease-out duration-150 transform"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100 transform"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                class="absolute left-0 mt-2 w-56 rounded-[6px] shadow-2xl bg-white text-retail-ink border border-retail-surface py-1 z-50">
                                <a href="{{ route('reports.rincian-ca') }}" class="block px-4 py-2.5 text-xs hover:bg-slate-50 font-medium border-b border-slate-100 flex items-center space-x-2">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-retail-cyan"></i>
                                    <span>1. Rincian per CA</span>
                                </a>
                                @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
                                    <a href="{{ route('reports.rekap-bulanan') }}" class="block px-4 py-2.5 text-xs hover:bg-slate-50 font-medium border-b border-slate-100 flex items-center space-x-2">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-retail-orange"></i>
                                        <span>2. Rekap Bulanan Pajak</span>
                                    </a>
                                    <a href="{{ route('reports.outstanding') }}" class="block px-4 py-2.5 text-xs hover:bg-slate-50 font-medium border-b border-slate-100 flex items-center space-x-2">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-rose-600"></i>
                                        <span>3. CA Menggantung</span>
                                    </a>
                                    <a href="{{ route('reports.rekap-karyawan') }}" class="block px-4 py-2.5 text-xs hover:bg-slate-50 font-medium flex items-center space-x-2">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>4. Rekap per Karyawan</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isDirektur() || auth()->user()->isFinance() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 rounded-[3px] transition flex items-center space-x-1.5 {{ request()->routeIs('audit-logs.*') ? 'bg-retail-darkInk text-retail-orange border-b-2 border-retail-orange font-bold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="history" class="w-3.5 h-3.5"></i>
                            <span>Audit Trail</span>
                        </a>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-[3px] transition flex items-center space-x-1.5 {{ request()->routeIs('admin.users.*') ? 'bg-retail-darkInk text-retail-orange border-b-2 border-retail-orange font-bold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span>Kelola User</span>
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="px-3 py-2 rounded-[3px] transition flex items-center space-x-1.5 {{ request()->routeIs('admin.settings.*') ? 'bg-retail-darkInk text-retail-orange border-b-2 border-retail-orange font-bold' : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                            <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                            <span>Pengaturan</span>
                        </a>
                    @endif
                </nav>

                <!-- User & Notifications Controls -->
                <div class="flex items-center space-x-3">
                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="p-2 rounded-[3px] hover:bg-white/10 relative text-slate-200 focus:outline-none transition">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-mono font-bold leading-none text-retail-darkInk bg-retail-orange rounded-full">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="open" x-cloak 
                            x-transition:enter="transition ease-out duration-150 transform"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100 transform"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-2 w-80 sm:w-96 rounded-[6px] shadow-2xl bg-white text-retail-ink border border-retail-surface py-2 z-50 overflow-hidden">
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
                                            <p class="text-xs font-bold text-retail-ink">{{ $notif->title }}</p>
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
                                <a href="{{ route('notifications.index') }}" class="text-xs text-retail-ink hover:text-retail-orange font-semibold">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile & Role Pill -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center space-x-2 p-1.5 rounded-[3px] hover:bg-white/10 transition focus:outline-none border border-transparent hover:border-retail-surface/40">
                            <div class="w-7 h-7 rounded-[3px] bg-retail-orange text-retail-darkInk flex items-center justify-center font-mono font-bold text-xs uppercase shadow-sm">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
                            <div class="hidden sm:block text-left text-xs leading-tight">
                                <span class="font-bold text-white block truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] font-mono uppercase font-bold tracking-wider px-1.5 py-0.2 rounded-[3px] inline-block
                                    {{ auth()->user()->role === 'direktur' ? 'bg-amber-400/20 text-amber-300 border border-amber-400/30' : '' }}
                                    {{ auth()->user()->role === 'finance' ? 'bg-purple-400/20 text-purple-300 border border-purple-400/30' : '' }}
                                    {{ auth()->user()->role === 'atasan' ? 'bg-blue-400/20 text-blue-300 border border-blue-400/30' : '' }}
                                    {{ auth()->user()->role === 'karyawan' ? 'bg-emerald-400/20 text-emerald-300 border border-emerald-400/30' : '' }}
                                    {{ auth()->user()->role === 'super_admin' ? 'bg-rose-400/20 text-rose-300 border border-rose-400/30' : '' }}
                                ">
                                    {{ auth()->user()->role }}
                                </span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-300 hidden sm:block"></i>
                        </button>

                        <!-- User Dropdown Menu -->
                        <div x-show="open" x-cloak 
                            x-transition:enter="transition ease-out duration-150 transform"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100 transform"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-2 w-64 rounded-[6px] shadow-2xl bg-white text-retail-ink border border-retail-surface py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100 bg-slate-50">
                                <p class="text-xs font-bold text-retail-ink">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] font-mono text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">Departemen: {{ auth()->user()->department ?: '-' }}</p>
                            </div>

                            <!-- Role Switcher Quick Actions for Demo -->
                            <div class="px-4 py-2 bg-slate-50 border-b border-slate-100">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Ganti Akun Cepat (Demo):</p>
                                <form action="{{ route('quick-login') }}" method="POST" class="space-y-1">
                                    @csrf
                                    <select name="user_id" onchange="this.form.submit()" class="w-full text-xs input-commerce py-1 font-sans">
                                        <option value="" disabled selected>-- Pilih Pengguna Lain --</option>
                                        @foreach(\App\Models\User::orderBy('role')->get() as $u)
                                            @if($u->id !== auth()->id())
                                                <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </form>
                            </div>

                            <form action="{{ route('logout') }}" method="POST" class="mt-1">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-bold flex items-center space-x-2">
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

    <!-- Main Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-20 md:pb-8">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border-l-4 border-retail-green p-4 rounded-[3px] shadow-sm flex items-start space-x-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-retail-green flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-emerald-900 font-medium leading-relaxed">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 bg-amber-50 border-l-4 border-retail-orange p-4 rounded-[3px] shadow-sm flex items-start space-x-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-retail-orange flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-amber-900 font-medium leading-relaxed">{{ session('warning') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-rose-50 border-l-4 border-rose-600 p-4 rounded-[3px] shadow-sm flex items-start space-x-3">
                <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-rose-900 font-medium leading-relaxed">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 bg-sky-50 border-l-4 border-retail-cyan p-4 rounded-[3px] shadow-sm flex items-start space-x-3">
                <i data-lucide="info" class="w-5 h-5 text-retail-cyan flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-sky-900 font-medium leading-relaxed">{{ session('info') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Functional Footer (Design.md: Trustworthy, Dark #131921) -->
    <footer class="bg-retail-darkInk text-slate-400 border-t border-retail-ink text-xs py-5 mt-auto no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <span class="w-2 h-2 rounded-[2px] bg-retail-orange inline-block"></span>
                <span class="font-bold text-slate-200">PT Technodrives Indonesia</span>
                <span>&bull;</span>
                <span>Sistem Cash Advance & Settlement</span>
            </div>
            <div class="flex items-center space-x-4 text-[11px] font-mono">
                <span class="text-slate-400">Ver. 2.0 (Design.md)</span>
                <span>&bull;</span>
                <span class="text-retail-orange font-semibold">Strict Governance</span>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>