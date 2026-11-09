@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Welcome & Role Hero Band (Gaya Hero Website: Latar Navy #050A1E, Pill Label Oranye, Judul Poppins Putih) -->
    <div class="rounded-[15px] p-6 sm:p-8 text-white border border-[#161A2B] shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative overflow-hidden"
        style="background: linear-gradient(135deg, #050A1E 0%, #161A2B 70%, rgba(255, 60, 0, 0.25) 100%);">
        <div class="space-y-2 max-w-2xl">
            <div class="pill-label border-[#40465D] bg-[#161A2B] text-[#FF3C00] shadow-sm">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                <span class="font-head text-xs tracking-wider uppercase font-bold">Akses Role: {{ auth()->user()->role }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-head font-bold tracking-tight text-white">
                Halo, {{ auth()->user()->name }}
            </h1>
            <p class="text-slate-300 font-body text-sm leading-relaxed">
                Sistem Manajemen Cash Advance & Settlement operasional proyek &bull; <strong class="text-white font-head font-semibold">PT. Technodrives</strong>
            </p>
        </div>

        <!-- Quick Action Buttons (Solutek Brand Buttons: Pill 30px, #FF3B00, hover Navy #050A1E, Rata Kanan) -->
        <div class="w-full sm:w-auto flex flex-col items-stretch sm:items-end justify-center gap-2.5 md:ml-auto shrink-0">
            @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
                <a href="{{ route('reports.rincian-ca') }}" class="btn-brand w-full sm:w-auto sm:min-w-[195px] justify-center">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>Menu Laporan</span>
                </a>
                <a href="{{ route('audit-logs.index') }}" class="btn-dark w-full sm:w-auto sm:min-w-[195px] justify-center">
                    <i data-lucide="history" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>Audit Trail</span>
                </a>
            @endif

            @if(auth()->user()->canSubmitCashAdvance() && !auth()->user()->isFinance() && !auth()->user()->isDirektur())
                @if($stats['has_lock'])
                    <a href="{{ route('cash-advances.create') }}" class="btn-dark w-full sm:w-auto sm:min-w-[195px] justify-center">
                        <i data-lucide="lock" class="w-4 h-4 text-[#FF3C00]"></i>
                        <span>Status Gembok CA</span>
                    </a>
                @else
                    <a href="{{ route('cash-advances.create') }}" class="btn-brand w-full sm:w-auto sm:min-w-[195px] justify-center">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Ajukan CA Baru</span>
                    </a>
                @endif
            @endif

            @if($stats['my_active_ca'])
                <a href="{{ route('expenses.create', $stats['my_active_ca']) }}" class="btn-secondary w-full sm:w-auto sm:min-w-[195px] justify-center">
                    <i data-lucide="camera" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>Catat Struk (HP)</span>
                </a>
            @endif
        </div>
    </div>



    <!-- ACTIVE CA COUNTDOWN & SALDO WIDGET -->
    @if($stats['my_active_ca'])
        @php
            $myCa = $stats['my_active_ca'];
            $daysLeft = $myCa->days_remaining;
            $isOverdue = $myCa->is_overdue;
        @endphp
        <div class="card-commerce overflow-hidden">
            <div class="bg-slate-50 px-5 py-3.5 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Cash Advance Berjalan (Proyek Aktif)</h3>
                </div>
                <div>
                    @if($isOverdue)
                        <span class="badge-retail bg-rose-100 text-rose-800 border-rose-300 font-bold">
                            <i data-lucide="alert-octagon" class="w-3 h-3 text-rose-600"></i>
                            <span>OVERDUE</span>
                        </span>
                    @else
                        <span class="badge-retail {{ $daysLeft <= 7 ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-sky-50 text-sky-900 border-sky-300' }}">
                            <i data-lucide="clock" class="w-3 h-3 text-retail-cyan"></i>
                            <span>{{ $daysLeft }} Hari Tersisa (Due: {{ $myCa->due_date ? $myCa->due_date->format('d/m/Y') : '-' }})</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                <!-- Info Proyek (Left Column) -->
                <div class="lg:col-span-1 space-y-2">
                    <span class="badge-retail bg-slate-100 text-slate-700 border-slate-300 font-mono">{{ $myCa->ca_number }}</span>
                    <h2 class="text-base font-bold text-slate-900 leading-snug">{{ $myCa->project_name }}</h2>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $myCa->purpose }}</p>
                    <div class="pt-2">
                        <a href="{{ route('cash-advances.show', $myCa) }}" class="text-xs font-bold text-retail-cyan hover:underline inline-flex items-center space-x-1">
                            <span>Lihat Rincian Buku Saldo</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <!-- Live Saldo Display (Right Column) -->
                <div class="lg:col-span-2">
                    <div class="bg-white p-5 rounded-xl border-2 border-[#FF3C00] shadow-sm">
                        <span class="block text-xs font-bold uppercase text-[#FF3C00] font-head tracking-wider">Sisa Saldo Kas</span>
                        <span class="block text-2xl sm:text-3xl font-mono font-black text-[#050A1E] mt-1">
                            Rp {{ number_format($myCa->current_balance, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                @php
                    $totalBudget = $myCa->approved_amount + $myCa->disbursed_topups_total;
                    $percentUsed = ($totalBudget > 0) ? min(100, round(($myCa->recorded_expenses_total / $totalBudget) * 100)) : 0;
                @endphp
                <div class="lg:col-span-3 pt-3 border-t border-slate-200">
                    <div class="flex items-center justify-between text-[11px] font-mono mb-1.5">
                        <span class="text-slate-500 font-medium">Realisasi Pengeluaran Terhadap Anggaran:</span>
                        <span class="font-bold text-slate-900">{{ $percentUsed }}% (Rp {{ number_format($myCa->recorded_expenses_total, 0, ',', '.') }} / Rp {{ number_format($totalBudget, 0, ',', '.') }})</span>
                    </div>
                    <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-retail-orange h-full rounded-full transform origin-left transition-transform duration-300 ease-out" style="width: {{ $percentUsed }}%"></div>
                    </div>
                </div>
            </div>

            @if($myCa->is_low_balance)
                <div class="bg-rose-50 px-5 py-3 border-t border-rose-200 text-xs text-rose-800 flex items-center justify-between flex-wrap gap-2">
                    <span class="font-medium flex items-center space-x-1.5">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                        <span>Peringatan: Saldo CA Anda tersisa kurang dari 20%. Segera ajukan top-up jika butuh tambahan operasional.</span>
                    </span>
                    <a href="{{ route('cash-advances.show', $myCa) }}#topup" class="font-bold underline text-rose-900 ml-2">Ajukan Top-Up</a>
                </div>
            @endif
        </div>
    @endif

    <!-- STAT METRIC TILES (Kartu Fitur: Latar #F4F4F4 / Putih, Radius 15px, Border #E1E1E1) -->
    @php
        $isAtasanOnly = auth()->user()->isAtasan() && !auth()->user()->isDirektur();
        $isFinanceOnly = auth()->user()->isFinance() && !auth()->user()->isDirektur();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $isAtasanOnly ? 'lg:grid-cols-2' : ($isFinanceOnly ? 'lg:grid-cols-2' : 'lg:grid-cols-4') }} gap-4 sm:gap-5">
        @if(auth()->user()->isAtasan() || auth()->user()->isDirektur())
            <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="card-commerce card-commerce-hoverable p-6 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Approval CA</span>
                        <span class="text-xs text-[#7A7A7A] mt-0.5 block font-body">Menunggu persetujuan Anda</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-[#161A2B] text-[#FFC704] flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm">
                        <i data-lucide="check-square" class="w-6 h-6"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between mt-4">
                    <div class="text-3xl sm:text-4xl font-head font-extrabold text-[#050A1E] group-hover:text-[#FF3C00] transition">
                        {{ $stats['pending_approval_count'] }}
                    </div>
                    <span class="inline-flex items-center text-xs font-head font-bold text-[#FF3C00] group-hover:underline">
                        Lihat Antrean <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
                    </span>
                </div>
            </a>

            <a href="{{ route('cash-advances.index') }}" class="card-commerce card-commerce-hoverable p-6 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Top-Up Menunggu</span>
                        <span class="text-xs text-[#7A7A7A] mt-0.5 block font-body">Permintaan dana tambahan</span>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#FF3C00] border border-[#FF3C00]/20 flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm">
                        <i data-lucide="arrow-up-circle" class="w-6 h-6"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between mt-4">
                    <div class="text-3xl sm:text-4xl font-head font-extrabold text-[#050A1E] group-hover:text-[#FF3C00] transition">
                        {{ $stats['pending_topup_count'] }}
                    </div>
                    <span class="inline-flex items-center text-xs font-head font-bold text-[#FF3C00] group-hover:underline">
                        Lihat Pengajuan <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
                    </span>
                </div>
            </a>
        @endif

        @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
            <div class="card-commerce card-commerce-hoverable p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider">Verifikasi Struk</span>
                    <div class="w-10 h-10 rounded-full bg-[#161A2B] text-white flex items-center justify-center">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-head font-extrabold text-[#050A1E] mt-3">{{ $stats['pending_verification_count'] ?? 0 }}</div>
                <span class="text-xs text-[#7A7A7A] mt-1 block font-body">Struk menunggu validasi</span>
            </div>

            <div class="card-commerce card-commerce-hoverable p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider">CA OVERDUE</span>
                    <div class="w-10 h-10 rounded-full bg-orange-50 text-[#FF3C00] flex items-center justify-center">
                        <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="text-3xl font-head font-extrabold text-[#FF3C00] mt-3">{{ $stats['overdue_count'] }}</div>
                <span class="text-xs text-[#7A7A7A] mt-1 block font-body">Lewat batas 2 bulan</span>
            </div>

            <div class="card-commerce card-commerce-hoverable p-5 col-span-1 sm:col-span-2 {{ auth()->user()->isDirektur() ? 'lg:col-span-4' : 'lg:col-span-2' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Total Dana CA Beredar (Float)</span>
                        <div class="text-2xl sm:text-3xl font-head font-extrabold text-[#050A1E] mt-2">Rp {{ number_format($stats['total_active_float'], 0, ',', '.') }}</div>
                        <span class="text-xs text-[#7A7A7A] mt-1 block font-body">Dana operasional aktif di lapangan proyek</span>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-[#050A1E] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <i data-lucide="banknote" class="w-6 h-6 text-[#FFC704]"></i>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->isSuperAdmin())
            <div class="card-commerce p-6 col-span-1 sm:col-span-2 lg:col-span-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-head font-bold text-[#050A1E] uppercase tracking-wide">Konfigurasi & Pengguna Sistem</h3>
                    <p class="text-xs text-[#7A7A7A] mt-1 font-body">Super Admin bertugas mengelola user master, relasi departemen, dan parameter global sistem.</p>
                </div>
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <a href="{{ route('admin.users.index') }}" class="btn-brand text-xs py-2.5 px-5 flex-1 sm:flex-initial">Kelola User</a>
                    <a href="{{ route('admin.settings.index') }}" class="btn-secondary text-xs py-2.5 px-5 flex-1 sm:flex-initial">Pengaturan Parameter</a>
                </div>
            </div>
        @endif
    </div>



</div>
@endsection