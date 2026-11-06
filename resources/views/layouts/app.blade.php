<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Cash Advance & Settlement') - PT Technodrives</title>
    
    <!-- Tailwind CSS CDN for instant robust styling & zero config failure -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#082f49',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js for lightweight reactivity and modals -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; font-size: 11pt; }
            .print-full { width: 100% !important; margin: 0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans antialiased">
    
    <!-- Top Header -->
    <header class="bg-brand-900 text-white shadow-md sticky top-0 z-40 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center border border-white/20">
                            <i data-lucide="wallet-cards" class="w-5 h-5 text-sky-400"></i>
                        </div>
                        <div>
                            <span class="font-bold text-lg tracking-tight block leading-tight text-white">Technodrives CA</span>
                            <span class="text-[10px] text-sky-300 uppercase tracking-wider block">PT Technodrives</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-3 text-sm">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md hover:bg-white/10 transition {{ request()->routeIs('dashboard') ? 'bg-white/15 font-semibold text-white' : 'text-slate-200' }}">
                        Dashboard
                    </a>
                    
                    @if(!auth()->user()->isSuperAdmin())
                        <a href="{{ route('cash-advances.index') }}" class="px-3 py-2 rounded-md hover:bg-white/10 transition {{ request()->routeIs('cash-advances.*') ? 'bg-white/15 font-semibold text-white' : 'text-slate-200' }}">
                            Cash Advance
                        </a>
                    @endif

                    @if(auth()->user()->isFinance() || auth()->user()->isDirektur() || auth()->user()->isAtasan())
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" class="px-3 py-2 rounded-md hover:bg-white/10 transition flex items-center space-x-1 text-slate-200">
                                <span>Laporan</span>
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white text-slate-800 ring-1 ring-black ring-opacity-5 py-1 z-50">
                                <a href="{{ route('reports.rincian-ca') }}" class="block px-4 py-2 text-xs hover:bg-slate-100 font-medium">1. Rincian per CA</a>
                                @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
                                    <a href="{{ route('reports.rekap-bulanan') }}" class="block px-4 py-2 text-xs hover:bg-slate-100 font-medium">2. Rekap Bulanan Pajak</a>
                                    <a href="{{ route('reports.outstanding') }}" class="block px-4 py-2 text-xs hover:bg-slate-100 font-medium">3. CA Menggantung</a>
                                    <a href="{{ route('reports.rekap-karyawan') }}" class="block px-4 py-2 text-xs hover:bg-slate-100 font-medium">4. Rekap per Karyawan</a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isDirektur() || auth()->user()->isFinance() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 rounded-md hover:bg-white/10 transition {{ request()->routeIs('audit-logs.*') ? 'bg-white/15 font-semibold text-white' : 'text-slate-200' }}">
                            Audit Trail
                        </a>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-md hover:bg-white/10 transition {{ request()->routeIs('admin.users.*') ? 'bg-white/15 font-semibold text-white' : 'text-slate-200' }}">
                            Kelola User
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="px-3 py-2 rounded-md hover:bg-white/10 transition {{ request()->routeIs('admin.settings.*') ? 'bg-white/15 font-semibold text-white' : 'text-slate-200' }}">
                            Pengaturan
                        </a>
                    @endif
                </nav>

                <!-- User & Notifications -->
                <div class="flex items-center space-x-3">
                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="p-2 rounded-full hover:bg-white/10 relative text-slate-200 focus:outline-none">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-rose-600 rounded-full">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-80 sm:w-96 rounded-xl shadow-2xl bg-white text-slate-800 ring-1 ring-black/10 py-2 z-50 overflow-hidden">
                            <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase text-slate-500 tracking-wider">Notifikasi ({{ $unreadCount }})</span>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Tandai semua dibaca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                                @forelse(auth()->user()->unreadNotifications()->take(6)->get() as $notif)
                                    <div class="p-3 hover:bg-slate-50 transition">
                                        <div class="flex items-start justify-between">
                                            <p class="text-xs font-semibold text-slate-900">{{ $notif->title }}</p>
                                            <span class="text-[10px] text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $notif->message }}</p>
                                        @if($notif->link)
                                            <form action="{{ route('notifications.read', $notif) }}" method="POST" class="mt-2">
                                                @csrf
                                                <button type="submit" class="text-[11px] font-semibold text-sky-600 hover:underline inline-flex items-center space-x-1">
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
                                <a href="{{ route('notifications.index') }}" class="text-xs text-slate-600 hover:text-slate-900 font-medium">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile & Role Pill -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-white/10 transition focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-sky-500/30 border border-sky-400/40 flex items-center justify-center font-bold text-xs text-sky-200 uppercase">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
                            <div class="hidden sm:block text-left text-xs leading-tight">
                                <span class="font-medium text-white block">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] uppercase font-semibold tracking-wider px-1.5 py-0.5 rounded
                                    {{ auth()->user()->role === 'direktur' ? 'bg-amber-400/20 text-amber-300' : '' }}
                                    {{ auth()->user()->role === 'finance' ? 'bg-purple-400/20 text-purple-300' : '' }}
                                    {{ auth()->user()->role === 'atasan' ? 'bg-blue-400/20 text-blue-300' : '' }}
                                    {{ auth()->user()->role === 'karyawan' ? 'bg-emerald-400/20 text-emerald-300' : '' }}
                                    {{ auth()->user()->role === 'super_admin' ? 'bg-rose-400/20 text-rose-300' : '' }}
                                ">
                                    {{ auth()->user()->role }}
                                </span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-300 hidden sm:block"></i>
                        </button>

                        <!-- User Dropdown Menu -->
                        <div x-show="open" x-cloak class="absolute right-0 mt-2 w-60 rounded-xl shadow-2xl bg-white text-slate-800 ring-1 ring-black/10 py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">Departemen: {{ auth()->user()->department ?: '-' }}</p>
                            </div>

                            <!-- Role Switcher Quick Actions for Pair Review / Demo -->
                            <div class="px-4 py-2 bg-slate-50 border-b border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Ganti Akun Cepat (Demo):</p>
                                <form action="{{ route('quick-login') }}" method="POST" class="space-y-1">
                                    @csrf
                                    <select name="user_id" onchange="this.form.submit()" class="w-full text-xs bg-white border border-slate-200 rounded px-2 py-1 text-slate-700">
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
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-semibold flex items-center space-x-2">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
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
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm flex items-start space-x-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-emerald-800 font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm flex items-start space-x-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-amber-800 font-medium">{{ session('warning') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm flex items-start space-x-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-rose-800 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 bg-sky-50 border-l-4 border-sky-500 p-4 rounded-r-lg shadow-sm flex items-start space-x-3">
                <i data-lucide="info" class="w-5 h-5 text-sky-600 flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-sky-800 font-medium">{{ session('info') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-center space-x-2 text-rose-800 font-bold text-sm mb-1">
                    <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600"></i>
                    <span>Terdapat beberapa kesalahan:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5 pl-2">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation (Akses Utama dari HP Karyawan) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-40 px-2 py-1 shadow-lg no-print">
        <div class="grid grid-cols-4 text-center">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('dashboard') ? 'text-sky-600 font-bold' : 'text-slate-500' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1">Beranda</span>
            </a>

            @if(!auth()->user()->isSuperAdmin())
                <a href="{{ route('cash-advances.index') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('cash-advances.index') ? 'text-sky-600 font-bold' : 'text-slate-500' }}">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-1">CA</span>
                </a>

                <a href="{{ route('cash-advances.create') }}" class="flex flex-col items-center py-1 text-sky-600">
                    <div class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center -mt-3 shadow-md">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[10px] mt-0.5 font-semibold">Ajukan</span>
                </a>
            @else
                <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center py-1 text-slate-500">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-1">User</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex flex-col items-center py-1 text-slate-500">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-1">Pengaturan</span>
                </a>
            @endif

            <a href="{{ route('reports.rincian-ca') }}" class="flex flex-col items-center py-1 {{ request()->routeIs('reports.*') ? 'text-sky-600 font-bold' : 'text-slate-500' }}">
                <i data-lucide="pie-chart" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1">Laporan</span>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 no-print">
        <div class="max-w-7xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} PT Technodrives. Sistem Cash Advance & Settlement. All rights reserved.</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
