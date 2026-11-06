@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Welcome & Role Banner -->
    <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-sky-900 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="inline-flex items-center space-x-2 px-2.5 py-1 rounded-full bg-white/10 text-sky-200 text-xs font-semibold uppercase tracking-wider mb-2">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Role: {{ auth()->user()->role }}</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight">Halo, {{ auth()->user()->name }}!</h1>
            <p class="text-sky-200 text-xs sm:text-sm mt-0.5">Sistem Manajemen Cash Advance & Settlement PT Technodrives</p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->canSubmitCashAdvance())
                @if($stats['has_lock'])
                    <a href="{{ route('cash-advances.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow transition">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span>Status Gembok CA</span>
                    </a>
                @else
                    <a href="{{ route('cash-advances.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-sky-500 hover:bg-sky-400 text-white text-xs font-bold rounded-xl shadow-lg hover:shadow-xl transition">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Ajukan CA Baru</span>
                    </a>
                @endif
            @endif

            @if($stats['my_active_ca'])
                <a href="{{ route('expenses.create', $stats['my_active_ca']) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow transition">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                    <span>Catat Struk (HP)</span>
                </a>
            @endif
        </div>
    </div>

    <!-- GEMBOK NOTICE (Prinsip 6: Pengajuan baru digembok sampai CA sebelumnya berstatus CLOSED) -->
    @if($stats['has_lock'] && $stats['my_locking_ca'])
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5 flex items-start space-x-4 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-600">
                <i data-lucide="lock" class="w-5 h-5"></i>
            </div>
            <div class="flex-grow">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h3 class="text-sm font-bold text-amber-900">Gembok Pengajuan Aktif</h3>
                    <span class="text-[11px] font-semibold bg-amber-200 text-amber-900 px-2 py-0.5 rounded-full">
                        Status CA: {{ $stats['my_locking_ca']->status }}
                    </span>
                </div>
                <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                    Sistem mendeteksi Anda memiliki Cash Advance <strong>{{ $stats['my_locking_ca']->ca_number }}</strong> (<em>{{ $stats['my_locking_ca']->project_name }}</em>) yang belum berstatus <strong>CLOSED</strong>. Pengajuan CA baru dikunci otomatis untuk memastikan akuntabilitas saldo.
                </p>
                <div class="mt-3 flex items-center space-x-3">
                    <a href="{{ route('cash-advances.show', $stats['my_locking_ca']) }}" class="text-xs font-bold text-amber-900 bg-amber-200 hover:bg-amber-300 px-3 py-1.5 rounded-lg transition inline-flex items-center space-x-1">
                        <span>Buka CA Aktif</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- ACTIVE CA COUNTDOWN & SALDO WIDGET (For employee with active CA) -->
    @if($stats['my_active_ca'])
        @php
            $myCa = $stats['my_active_ca'];
            $daysLeft = $myCa->days_remaining;
            $isOverdue = $myCa->is_overdue;
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Cash Advance Berjalan (Proyek Aktif)</h3>
                </div>
                <div class="flex items-center space-x-2">
                    @if($isOverdue)
                        <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 animate-pulse flex items-center space-x-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            <span>OVERDUE</span>
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $daysLeft <= 7 ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }} flex items-center space-x-1">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <span>{{ $daysLeft }} Hari Tersisa (Jatuh Tempo: {{ $myCa->due_date ? $myCa->due_date->format('d M Y') : '-' }})</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                <!-- Info Proyek -->
                <div class="lg:col-span-1 space-y-2">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ $myCa->ca_number }}</div>
                    <h2 class="text-lg font-bold text-slate-900 leading-snug">{{ $myCa->project_name }}</h2>
                    <p class="text-xs text-slate-500 line-clamp-2">{{ $myCa->purpose }}</p>
                    <div class="pt-2 flex items-center space-x-2">
                        <a href="{{ route('cash-advances.show', $myCa) }}" class="text-xs font-bold text-sky-600 hover:text-sky-800 inline-flex items-center space-x-1">
                            <span>Lihat Rincian Buku Saldo</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <!-- Live Saldo Display (Prinsip 3: Saldo dihitung sistem, tidak pernah diketik manusia) -->
                <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div>
                        <span class="block text-[11px] font-medium text-slate-500">CA Disetujui</span>
                        <span class="block text-sm font-bold text-slate-800 mt-1">Rp {{ number_format($myCa->approved_amount, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-medium text-slate-500">Top-Up Cair (+)</span>
                        <span class="block text-sm font-bold text-emerald-600 mt-1">+Rp {{ number_format($myCa->disbursed_topups_total, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-medium text-slate-500">Pengeluaran (-)</span>
                        <span class="block text-sm font-bold text-rose-600 mt-1">-Rp {{ number_format($myCa->recorded_expenses_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-white p-2 rounded-lg border border-slate-200">
                        <span class="block text-[11px] font-bold text-sky-700">Sisa Saldo</span>
                        <span class="block text-base font-extrabold text-sky-600 mt-0.5">Rp {{ number_format($myCa->current_balance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if($myCa->is_low_balance)
                <div class="bg-rose-50 px-5 py-2.5 border-t border-rose-100 text-xs text-rose-700 flex items-center justify-between">
                    <span class="font-medium flex items-center space-x-1.5">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        <span>Peringatan: Saldo CA Anda tersisa kurang dari 20%. Segera ajukan top-up jika butuh tambahan biaya.</span>
                    </span>
                    <a href="{{ route('cash-advances.show', $myCa) }}#topup" class="font-bold underline ml-2">Ajukan Top-Up</a>
                </div>
            @endif
        </div>
    @endif

    <!-- STAT METRIC TILES -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @if(auth()->user()->isAtasan() || auth()->user()->isDirektur())
            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Approval CA</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $stats['pending_approval_count'] }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Menunggu persetujuan Anda</span>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Top-Up Menunggu</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="arrow-up-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $stats['pending_topup_count'] }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Permintaan top-up dana</span>
            </div>
        @endif

        @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Struk Menunggu Verifikasi</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $stats['pending_verification_count'] ?? 0 }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Perlu dicek keabsahannya</span>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">CA OVERDUE</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-rose-600 mt-2">{{ $stats['overdue_count'] }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Lewat batas 2 bulan</span>
            </div>

            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200 col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Total Saldo CA Beredar (Float)</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="banknote" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-600 mt-2">Rp {{ number_format($stats['total_active_float'], 0, ',', '.') }}</div>
                <span class="text-[11px] text-slate-400 mt-1 block">Dana aktif di tangan tim proyek</span>
            </div>
        @endif

        @if(auth()->user()->isSuperAdmin())
            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200 col-span-2 sm:col-span-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Panel Super Admin</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola data master pengguna, hierarki relasi atasan, dan konfigurasi batasan sistem.</p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg transition">Kelola User</a>
                        <a href="{{ route('admin.settings.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition">Pengaturan Batasan</a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- RECENT CASH ADVANCES TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                <i data-lucide="history" class="w-4 h-4 text-slate-500"></i>
                <span>Cash Advance Terbaru</span>
            </h3>
            @if(!auth()->user()->isSuperAdmin())
                <a href="{{ route('cash-advances.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">
                    Lihat Semua
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">No. CA</th>
                        <th class="px-4 py-3">Pemohon</th>
                        <th class="px-4 py-3">Proyek</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Sisa Waktu</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentCAs as $ca)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 font-bold text-slate-900">
                                {{ $ca->ca_number }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-800">{{ $ca->user->name }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $ca->user->department }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-800">{{ $ca->project_name }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $ca->project_code ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                Rp {{ number_format($ca->approved_amount ?: $ca->requested_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $ca->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $ca->status === 'PENDING_APPROVAL' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $ca->status === 'NEED_SETTLEMENT' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $ca->status === 'PENDING_VERIFICATION' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $ca->status === 'CLOSED' ? 'bg-slate-200 text-slate-700' : '' }}
                                    {{ $ca->status === 'REJECTED' ? 'bg-rose-100 text-rose-800' : '' }}
                                ">
                                    {{ $ca->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                    @if($ca->is_overdue)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-700">OVERDUE</span>
                                    @else
                                        <span class="text-slate-600 font-medium">{{ $ca->days_remaining }} hr</span>
                                    @endif
                                @elseif($ca->status === 'CLOSED')
                                    <span class="text-slate-400">Lunas</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('cash-advances.show', $ca) }}" class="inline-flex items-center space-x-1 text-sky-600 hover:text-sky-800 font-bold">
                                    <span>Buka</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada Cash Advance tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
