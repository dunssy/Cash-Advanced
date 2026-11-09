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

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#FF3C00',          // Official Brand Orange (action, hover, active, focus)
                        brandalt: '#FF3B00',       // Primary button background
                        brandhover: '#FF3D00',     // Hover & focus glow
                        navy: '#050A1E',           // Primary Navy (headers, dark summary band, table headers)
                        navy2: '#161A2B',          // Secondary Navy
                        bodygrey: '#7A7A7A',       // Body text grey
                        surface: '#F4F4F4',        // Light surface background
                        cardborder: '#E1E1E1',     // Clean card border
                        accent: '#FFC704',         // Yellow accent (PENDING_APPROVAL only)
                        retail: {
                            orange: '#FF3C00',
                            navy: '#050A1E',
                            navy2: '#161A2B',
                            ink: '#050A1E',
                            darkInk: '#030612',
                            surface: '#E1E1E1',
                            body: '#7A7A7A',
                            bg: '#F4F4F4',
                        }
                    },
                    fontFamily: {
                        sans: ['"Fira Sans"', 'sans-serif'],
                        head: ['"Poppins"', 'sans-serif'],
                        body: ['"Fira Sans"', 'sans-serif'],
                        mono: ['"Poppins"', 'monospace'], // Currency & technical amounts use bold Poppins
                    },
                    borderRadius: {
                        'pill': '30px',
                        'card': '15px',
                        'input': '15px',
                        'label': '20px',
                        'sm': '6px',
                        'DEFAULT': '10px',
                        'md': '12px',
                        'lg': '15px',
                        'xl': '20px',
                        '2xl': '30px',
                    },
                    boxShadow: {
                        'header': '0 2px 5px -2px rgba(0, 0, 0, 0.1)',
                        'submenu': '0px 1px 3px rgba(0, 0, 0, 0.3)',
                        'card': '0 1px 3px 0 rgba(0, 0, 0, 0.05)',
                        'focus': '0 0 4px rgba(255, 61, 0, 0.5)',
                    },
                    transitionDuration: {
                        'brand': '500ms',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        :root {
            --td-orange: #FF3C00;
            --td-orange-alt: #FF3B00;
            --td-orange-hover-text: #FF3D00;
            --td-navy: #050A1E;
            --td-navy-2: #161A2B;
            --td-body: #7A7A7A;
            --td-surface: #F4F4F4;
            --td-card-border: #E1E1E1;
            --td-input-border: rgba(122, 122, 122, 0.5);
            --td-yellow: #FFC704;
            --td-font-head: "Poppins", sans-serif;
            --td-font-body: "Fira Sans", sans-serif;
            --td-radius-pill: 30px;
            --td-radius-card: 15px;
            --td-radius-input: 15px;
            --td-radius-label: 20px;
            --td-transition: 0.5s;
        }

        body {
            font-family: var(--td-font-body);
            font-size: 16px;
            line-height: 26px;
            background-color: #F4F4F4;
            color: #7A7A7A;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--td-font-head);
            font-weight: 700;
            color: #050A1E;
        }

        /* Tombol Utama (Solutek Brand Button: Latar #FF3B00, teks putih, uppercase 15px, radius 30px, hover Navy #050A1E, transisi 0.5s) */
        .btn-brand, .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #FF3B00;
            color: #FFFFFF !important;
            font-family: var(--td-font-head);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 26px;
            border-radius: 30px;
            border: 1px solid #FF3B00;
            min-height: 46px;
            position: relative;
            z-index: 1;
            transition: all 0.5s ease;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(255, 59, 0, 0.25);
        }
        .btn-brand:hover, .btn-primary:hover {
            background-color: #050A1E;
            border-color: #050A1E;
            color: #FFFFFF !important;
            box-shadow: 0 4px 14px rgba(5, 10, 30, 0.3);
        }

        /* Tombol Gelap (Aksi Sekunder Kuat: Navy #050A1E) */
        .btn-dark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #050A1E;
            color: #FFFFFF !important;
            font-family: var(--td-font-head);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 24px;
            border-radius: 30px;
            border: 1px solid #050A1E;
            min-height: 46px;
            transition: all 0.5s ease;
            cursor: pointer;
        }
        .btn-dark:hover {
            background-color: #FF3C00;
            border-color: #FF3C00;
            color: #FFFFFF !important;
            box-shadow: 0 4px 14px rgba(255, 60, 0, 0.25);
        }

        /* Tombol Pucat / Sekunder */
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #FFFFFF;
            color: #050A1E;
            font-family: var(--td-font-head);
            font-size: 14px;
            font-weight: 600;
            padding: 11px 22px;
            border-radius: 30px;
            border: 1px solid #E1E1E1;
            min-height: 46px;
            transition: all 0.5s ease;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background-color: #F5EBE8;
            border-color: #FF3C00;
            color: #FF3C00;
        }

        /* Tombol Verifikasi / Biru Logo Technodrives (#2E3087) */
        .btn-cyan, .btn-verify, .btn-blue {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            background-color: #2E3087;
            color: #FFFFFF !important;
            font-family: var(--td-font-head);
            font-size: 12px;
            font-weight: 700;
            padding: 7px 16px;
            border-radius: 8px;
            border: 1px solid #2E3087;
            transition: all 0.25s ease;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(46, 48, 135, 0.25);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-cyan:hover, .btn-verify:hover, .btn-blue:hover {
            background-color: #23256C;
            border-color: #23256C;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(46, 48, 135, 0.4);
            transform: translateY(-1px);
        }

        /* Tombol Sukses / Tandai Selesai (Hijau Emerald #059669) */
        .btn-success, .btn-green {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #059669;
            color: #FFFFFF !important;
            font-family: var(--td-font-head);
            font-size: 13px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 30px;
            border: 1px solid #047857;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(5, 150, 105, 0.28);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .btn-success:hover, .btn-green:hover {
            background-color: #047857;
            border-color: #065F46;
            color: #FFFFFF !important;
            box-shadow: 0 6px 18px rgba(4, 120, 87, 0.4);
            transform: translateY(-1px);
        }
        .btn-success:active, .btn-green:active {
            transform: translateY(0);
        }

        /* Pill Label Seksi (Gaya h5.section-sub-title website resmi) */
        .pill-label {
            font-family: var(--td-font-head);
            font-size: 13px;
            font-weight: 600;
            color: #FF3C00;
            background-color: rgba(255, 60, 0, 0.04);
            border: 1px solid rgba(122, 122, 122, 0.18);
            border-radius: 20px;
            padding: 5px 22px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            line-height: 22px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Kartu Layanan (Putih, radius 15px, border 1px #E1E1E1) */
        .card-commerce {
            background-color: #FFFFFF;
            border: 1px solid #E1E1E1;
            border-radius: 15px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
            transition: border-color 0.5s ease, box-shadow 0.5s ease;
        }
        .card-commerce-hoverable:hover {
            border-color: #FF3C00;
            box-shadow: 0 8px 24px -4px rgba(255, 60, 0, 0.12);
        }

        /* Kartu Fitur (Latar #F4F4F4, radius 15px, border 1px #E1E1E1) */
        .card-feature {
            background-color: #F4F4F4;
            border: 1px solid #E1E1E1;
            border-radius: 15px;
            transition: border-color 0.5s ease;
        }
        .card-feature:hover {
            border-color: #FF3C00;
        }

        /* Form Input Resmi (.form_box input: height 52-60px, radius 15px, border rgba(122,122,122,0.5), focus #FF3D00) */
        .input-brand, .input-commerce {
            border: 1px solid rgba(122, 122, 122, 0.45);
            min-height: 50px;
            border-radius: 15px;
            padding: 0 20px;
            width: 100%;
            font-family: var(--td-font-body);
            font-size: 15px;
            color: #050A1E;
            background-color: #FFFFFF;
            transition: border-color 0.5s ease, box-shadow 0.5s ease;
            outline: 0;
        }
        .input-brand:focus, .input-commerce:focus {
            border-color: #FF3D00;
            box-shadow: 0 0 4px rgba(255, 61, 0, 0.5);
            outline: 0;
        }
        textarea.input-brand, textarea.input-commerce {
            border-radius: 25px;
            padding: 16px 20px;
            min-height: 120px;
        }

        /* 6 Badge Status Resmi PT Technodrives (Tidak ada hijau / ungu / biru muda) */
        .badge-retail {
            font-family: var(--td-font-head), 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 600;
            border-radius: 30px;
            padding: 4px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            line-height: 1.2;
            vertical-align: middle;
            transition: all 0.25s ease;
        }
        .badge-pending-approval {
            background-color: #FFC704 !important;
            color: #050A1E !important;
            border: 1px solid #E5B300 !important;
            box-shadow: 0 1px 3px rgba(255, 199, 4, 0.25) !important;
        }
        .badge-rejected {
            background-color: #FF3C00 !important;
            color: #FFFFFF !important;
            border: 1px solid #E03500 !important;
            box-shadow: 0 1px 4px rgba(255, 60, 0, 0.3) !important;
        }
        .badge-active {
            background: linear-gradient(135deg, #0E1638 0%, #050A1E 100%) !important;
            color: #FFFFFF !important;
            border: 1px solid rgba(255, 60, 0, 0.45) !important;
            box-shadow: 0 2px 6px rgba(5, 10, 30, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.15) !important;
        }
        .badge-active::before {
            content: '';
            width: 6.5px;
            height: 6.5px;
            border-radius: 50%;
            background-color: #FF3C00;
            box-shadow: 0 0 6px rgba(255, 60, 0, 0.9), 0 0 2px #FF3C00;
            display: inline-block;
            flex-shrink: 0;
            animation: activeDotPulse 1.8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes activeDotPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
                box-shadow: 0 0 6px rgba(255, 60, 0, 0.9), 0 0 2px #FF3C00;
            }
            50% {
                transform: scale(1.35);
                opacity: 0.7;
                box-shadow: 0 0 10px rgba(255, 60, 0, 1), 0 0 4px #FF3C00;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .badge-active::before {
                animation: none !important;
            }
        }
        .badge-need-settlement {
            background-color: #FFFFFF !important;
            border: 1.5px solid #FF3C00 !important;
            color: #FF3C00 !important;
            box-shadow: 0 1px 3px rgba(255, 60, 0, 0.15) !important;
        }
        .badge-pending-verification {
            background-color: #161A2B !important;
            color: #FFFFFF !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 1px 3px rgba(22, 26, 43, 0.3) !important;
        }
        .badge-closed {
            background-color: #F4F4F4 !important;
            border: 1px solid #D5D5D5 !important;
            color: #7A7A7A !important;
        }

        /* Tabel Data Resmi: Kepala Tabel Navy #050A1E Teks Putih Poppins 600 */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
            overflow-x: auto;
        }
        .table-technodrives thead {
            background-color: #050A1E;
            color: #FFFFFF;
            font-family: var(--td-font-head);
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-technodrives thead th {
            padding: 14px 16px;
            color: #FFFFFF;
        }
        .table-technodrives tbody tr {
            border-bottom: 1px solid #E9E9E9;
            transition: background-color 0.3s ease;
        }
        .table-technodrives tbody tr:nth-child(even) {
            background-color: #F4F4F4;
        }
        .table-technodrives tbody tr:hover {
            background-color: rgba(255, 60, 0, 0.03);
        }
        .table-technodrives tbody td {
            padding: 14px 16px;
            font-family: var(--td-font-body);
            font-size: 15px;
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
<body class="min-h-[100dvh] antialiased bg-[#F4F4F4] text-[#7A7A7A]" x-data="{ mobileNavOpen: false, sidebarCollapsed: true, laporanOpen: true }">
    @php
        $unreadCount = auth()->user() ? auth()->user()->unreadNotifications()->count() : 0;
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
            <div class="p-3 mx-3 my-2.5 bg-[#161A2B] rounded-xl border border-white/5 flex items-center space-x-3" x-show="!sidebarCollapsed" x-transition>
                <div class="w-9 h-9 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xs uppercase shadow-sm flex-shrink-0">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="font-head font-bold text-xs text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="flex items-center space-x-1 mt-0.5">
                        <span class="text-[9px] font-head uppercase font-bold px-2 py-0.5 rounded-full
                            {{ auth()->user()->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                            {{ auth()->user()->role === 'finance' ? 'bg-white/20 text-white' : '' }}
                            {{ auth()->user()->role === 'atasan' ? 'bg-[#050A1E] text-white border border-white/20' : '' }}
                            {{ auth()->user()->role === 'karyawan' ? 'bg-white/10 text-slate-300' : '' }}
                            {{ auth()->user()->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                        ">{{ auth()->user()->role }}</span>
                        <span class="text-[9px] text-slate-400 truncate">{{ auth()->user()->department ?: 'Staff' }}</span>
                    </div>
                </div>
            </div>

            <!-- Collapsed User Avatar -->
            <div class="py-2.5 flex justify-center" x-show="sidebarCollapsed" x-transition>
                <div class="w-9 h-9 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xs uppercase shadow-sm" title="{{ auth()->user()->name }} ({{ auth()->user()->role }})">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
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

            <!-- Sidebar Footer: 1-Click Demo Switcher + Logout -->
            <div class="p-3 border-t border-[#161A2B] bg-[#050A1E]/80 space-y-2">
                <div x-data="{ openDemoSide: false }" x-show="!sidebarCollapsed">
                    <button @click="openDemoSide = !openDemoSide" type="button" 
                        class="w-full py-1.5 px-2.5 rounded-lg bg-[#161A2B] hover:bg-white/10 text-[#FF3C00] border border-[#FF3C00]/30 text-xs font-semibold flex items-center justify-between transition cursor-pointer">
                        <span class="flex items-center space-x-1.5">
                            <i data-lucide="user-cog" class="w-3.5 h-3.5"></i>
                            <span class="font-head text-[10px] uppercase font-bold">Ganti Akun Demo</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="{ 'rotate-180': openDemoSide }"></i>
                    </button>
                    <div x-show="openDemoSide" x-cloak class="mt-2 space-y-1 max-h-40 overflow-y-auto pr-0.5">
                        @foreach(\App\Models\User::orderBy('role')->get() as $u)
                            <form action="{{ route('quick-login') }}" method="POST" class="m-0">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $u->id }}">
                                <button type="submit" 
                                    class="w-full text-left p-1.5 rounded-lg border transition flex items-center justify-between cursor-pointer {{ $u->id === auth()->id() ? 'border-[#FF3C00] bg-[#161A2B] font-bold text-white' : 'border-white/5 bg-white/5 text-slate-300 hover:bg-white/10' }}">
                                    <span class="text-[11px] truncate">{{ $u->name }}</span>
                                    <span class="text-[9px] uppercase font-bold px-1.5 py-0.2 rounded font-head
                                        {{ $u->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                        {{ $u->role === 'finance' ? 'bg-white text-[#050A1E]' : '' }}
                                        {{ $u->role === 'atasan' ? 'bg-[#050A1E] text-white border border-white/20' : '' }}
                                        {{ $u->role === 'karyawan' ? 'bg-slate-700 text-white' : '' }}
                                        {{ $u->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                    ">{{ $u->role }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

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
                    <!-- Quick Demo Switcher Navbar Button (Always Accessible in 1 Click) -->
                    <div class="relative hidden sm:block" x-data="{ openDemo: false }" @click.outside="openDemo = false" @keydown.escape.window="openDemo = false">
                        <button @click="openDemo = !openDemo" type="button" class="flex items-center space-x-1.5 px-3 py-1.5 rounded-full bg-[#161A2B] hover:bg-white/10 text-[#FF3C00] border border-[#FF3C00]/40 hover:border-[#FF3C00] text-xs font-semibold transition" title="Ganti Akun Demo (5 Role)">
                            <i data-lucide="user-cog" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                            <span class="font-head text-[11px] uppercase tracking-wider font-bold">Demo Switch</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform duration-150" :class="{ 'rotate-180': openDemo }"></i>
                        </button>

                        <!-- Dropdown Menu for Quick Switcher -->
                        <div x-show="openDemo" x-cloak @click.stop
                            x-transition:enter="transition ease-out duration-150 transform"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100 transform"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-2 w-72 rounded-xl shadow-2xl bg-white text-[#050A1E] border-y-[3px] border-[#FF3C00] py-2 z-50">
                            <div class="px-3.5 py-2 border-b border-slate-100 bg-[#F4F4F4] flex items-center justify-between">
                                <span class="text-[10px] font-bold text-[#050A1E] uppercase tracking-wider flex items-center space-x-1 font-head">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                                    <span>Pilih Akun Demo:</span>
                                </span>
                                <span class="text-[10px] font-head text-[#FF3C00] font-bold uppercase">{{ auth()->user()->role }}</span>
                            </div>
                            <div class="p-2 space-y-1.5 max-h-72 overflow-y-auto">
                                @foreach(\App\Models\User::orderBy('role')->get() as $u)
                                    <form action="{{ route('quick-login') }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $u->id }}">
                                        <button type="submit" 
                                            class="w-full text-left p-2 rounded-xl border transition flex items-center justify-between group
                                            {{ $u->id === auth()->id() ? 'border-[#FF3C00] bg-orange-50/70 ring-1 ring-[#FF3C00]/30' : 'border-slate-200 bg-white hover:border-[#FF3C00] hover:bg-[#F4F4F4]' }}">
                                            <div class="flex items-center space-x-2.5 min-w-0">
                                                <span class="w-7 h-7 rounded-lg text-xs font-head font-bold flex items-center justify-center flex-shrink-0
                                                    {{ $u->role === 'direktur' ? 'bg-[#050A1E] text-[#FFC704]' : '' }}
                                                    {{ $u->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                                    {{ $u->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                                    {{ $u->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#050A1E] border border-slate-300' : '' }}
                                                    {{ $u->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                                ">
                                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="flex items-center space-x-1">
                                                        <p class="text-xs font-bold text-[#050A1E] truncate group-hover:text-[#FF3C00] transition leading-tight font-head">{{ $u->name }}</p>
                                                        @if($u->id === auth()->id())
                                                            <span class="text-[9px] text-[#FF3C00] font-bold font-head">(Aktif)</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-[10px] text-[#7A7A7A] block truncate font-body">{{ $u->department ?: $u->email }}</span>
                                                </div>
                                            </div>
                                            <span class="text-[9px] font-head uppercase font-bold tracking-wider px-2 py-0.5 rounded-full flex-shrink-0 ml-2
                                                {{ $u->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                                {{ $u->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                                {{ $u->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                                {{ $u->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1]' : '' }}
                                                {{ $u->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                            ">
                                                {{ $u->role }}
                                            </span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>

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
                        <button @click="open = !open" type="button" class="flex items-center space-x-2 p-1.5 rounded-xl hover:bg-white/10 transition focus:outline-none border border-transparent hover:border-slate-700">
                            <div class="w-8 h-8 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xs uppercase shadow-sm">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
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
                            <div class="px-4 py-2.5 border-b border-slate-100 bg-[#F4F4F4]">
                                <p class="text-xs font-bold text-[#050A1E] font-head">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] font-mono text-[#7A7A7A] truncate">{{ auth()->user()->email }}</p>
                                <p class="text-[10px] text-bodygrey mt-0.5">Departemen: {{ auth()->user()->department ?: '-' }}</p>
                            </div>

                            <!-- Role Switcher Quick Actions for Demo (1-Click Buttons) -->
                            <div class="px-3 py-2.5 bg-[#F4F4F4]/50 border-b border-slate-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold text-[#050A1E] uppercase tracking-wider flex items-center space-x-1 font-head">
                                        <i data-lucide="user-cog" class="w-3 h-3 text-[#FF3C00]"></i>
                                        <span>Ganti Akun Cepat (Demo):</span>
                                    </span>
                                    <span class="text-[9px] font-head text-[#FF3C00] font-bold bg-white px-2 py-0.5 rounded-full border border-[#FF3C00]/30">1-Klik</span>
                                </div>
                                <div class="space-y-1.5 max-h-56 overflow-y-auto pr-0.5">
                                    @foreach(\App\Models\User::orderBy('role')->get() as $u)
                                        @if($u->id !== auth()->id())
                                            <form action="{{ route('quick-login') }}" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $u->id }}">
                                                <button type="submit" class="w-full text-left p-2 rounded-xl border border-cardborder bg-white hover:border-[#FF3C00] hover:bg-[#F4F4F4] transition flex items-center justify-between group cursor-pointer">
                                                    <div class="flex items-center space-x-2 min-w-0">
                                                        <span class="w-6 h-6 rounded-lg text-[10px] font-head font-bold flex items-center justify-center flex-shrink-0
                                                            {{ $u->role === 'direktur' ? 'bg-[#050A1E] text-[#FFC704]' : '' }}
                                                            {{ $u->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                                            {{ $u->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                                            {{ $u->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#050A1E] border border-cardborder' : '' }}
                                                            {{ $u->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                                        ">
                                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                                        </span>
                                                        <div class="min-w-0">
                                                            <p class="text-xs font-bold text-[#050A1E] truncate group-hover:text-[#FF3C00] transition leading-tight font-head">{{ $u->name }}</p>
                                                            <span class="text-[10px] text-bodygrey block truncate font-body">{{ $u->department ?: $u->email }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="text-[9px] font-head uppercase font-bold tracking-wider px-2 py-0.5 rounded-full flex-shrink-0 ml-2
                                                        {{ $u->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                                        {{ $u->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                                        {{ $u->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                                        {{ $u->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1]' : '' }}
                                                        {{ $u->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                                    ">
                                                        {{ $u->role }}
                                                    </span>
                                                </button>
                                            </form>
                                        @endif
                                    @endforeach
                                </div>
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
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xs uppercase shadow-sm flex-shrink-0">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-head font-bold text-sm text-[#050A1E] truncate">{{ auth()->user()->name }}</div>
                            <div class="flex items-center space-x-1.5 mt-0.5">
                                <span class="badge-retail text-[10px] uppercase font-bold
                                    {{ auth()->user()->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                    {{ auth()->user()->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                    {{ auth()->user()->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                    {{ auth()->user()->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1]' : '' }}
                                    {{ auth()->user()->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                ">
                                    {{ auth()->user()->role }}
                                </span>
                                <span class="text-[10px] text-[#7A7A7A] truncate font-body">{{ auth()->user()->department ?: 'Staff' }}</span>
                            </div>
                        </div>
                    </div>

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

                    <!-- 1-Click Role Switcher in Drawer (Mobile) -->
                    <div class="p-3 mt-2 border-t border-cardborder bg-[#F4F4F4]">
                        <p class="text-[10px] font-head font-bold uppercase tracking-wider text-[#050A1E] mb-2 flex items-center justify-between">
                            <span>Ganti Akun Demo (1-Klik):</span>
                            <span class="text-[9px] font-head text-[#FF3C00] font-bold">5 Role</span>
                        </p>
                        <div class="space-y-1.5 max-h-48 overflow-y-auto pr-0.5">
                            @foreach(\App\Models\User::orderBy('role')->get() as $u)
                                <form action="{{ route('quick-login') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                    <button type="submit" class="w-full text-left p-2 rounded-xl border transition flex items-center justify-between cursor-pointer {{ $u->id === auth()->id() ? 'border-[#FF3C00] bg-white font-bold ring-1 ring-[#FF3C00]/30' : 'border-cardborder bg-white hover:border-[#FF3C00]' }}">
                                        <div class="flex items-center space-x-2 min-w-0">
                                            <span class="w-6 h-6 rounded-lg text-[10px] font-head font-bold flex items-center justify-center flex-shrink-0
                                                {{ $u->role === 'direktur' ? 'bg-[#050A1E] text-[#FFC704]' : '' }}
                                                {{ $u->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                                {{ $u->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                                {{ $u->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#050A1E] border border-cardborder' : '' }}
                                                {{ $u->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                            ">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-head font-semibold text-[#050A1E] truncate leading-tight">{{ $u->name }}</p>
                                            </div>
                                        </div>
                                        <span class="text-[9px] font-head uppercase font-bold px-2 py-0.5 rounded-full
                                            {{ $u->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                            {{ $u->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                            {{ $u->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                            {{ $u->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-cardborder' : '' }}
                                            {{ $u->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                        ">{{ $u->role }}</span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
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
            <!-- Role Atasan (3-Tab Bar: Beranda, CA Tim, Laporan) -->
            <div class="flex items-center justify-around max-w-sm mx-auto">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-4 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Beranda</span>
                </a>
                <a href="{{ route('cash-advances.index') }}" class="flex flex-col items-center py-1 px-4 rounded-xl transition {{ request()->routeIs('cash-advances.*') && !request()->routeIs('cash-advances.create') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">CA Tim</span>
                </a>
                <a href="{{ route('reports.rincian-ca') }}" class="flex flex-col items-center py-1 px-4 rounded-xl transition {{ request()->routeIs('reports.*') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    <span class="text-[10px] mt-0.5 font-head font-medium">Laporan</span>
                </a>
            </div>
        @else
            <!-- Role Karyawan (3-Tab Bar: Beranda, + Ajukan, CA Saya) -->
            <div class="flex items-center justify-around max-w-sm mx-auto">
                <!-- Tab 1: Beranda -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-4 rounded-xl transition {{ request()->routeIs('dashboard') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
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
                <a href="{{ route('cash-advances.index') }}" class="flex flex-col items-center py-1 px-4 rounded-xl transition {{ request()->routeIs('cash-advances.*') && !request()->routeIs('cash-advances.create') ? 'text-[#FF3C00] font-bold' : 'text-slate-300 hover:text-[#FF3C00]' }}">
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

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
