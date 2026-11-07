@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Welcome & Role Banner (Design.md: Dark #232F3E, Rounded 6px, Primary #FF9900 CTA) -->
    <div class="bg-retail-ink rounded-[6px] p-6 text-white border border-retail-darkInk shadow-card flex flex-col md:flex-row md:items-center md:justify-between gap-5">
        <div>
            <div class="inline-flex items-center space-x-2 px-2.5 py-0.5 rounded-[3px] bg-retail-darkInk border border-retail-surface/20 text-retail-cyan text-xs font-mono font-semibold uppercase tracking-wider mb-2">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Akses: {{ auth()->user()->role }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Halo, {{ auth()->user()->name }}</h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-0.5">Sistem Manajemen Cash Advance & Settlement &bull; PT Technodrives Indonesia</p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            @if(auth()->user()->canSubmitCashAdvance())
                @if($stats['has_lock'])
                    <a href="{{ route('cash-advances.create') }}" class="btn-primary" style="background-color: #D97706; border-color: #B45309; color: #FFFFFF;">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span>Status Gembok CA</span>
                    </a>
                @else
                    <a href="{{ route('cash-advances.create') }}" class="btn-primary">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Ajukan CA Baru</span>
                    </a>
                @endif
            @endif

            @if($stats['my_active_ca'])
                <a href="{{ route('expenses.create', $stats['my_active_ca']) }}" class="btn-cyan">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                    <span>Catat Struk (HP)</span>
                </a>
            @endif
        </div>
    </div>

    <!-- GEMBOK NOTICE (Prinsip 6: Pengajuan baru digembok sampai CA sebelumnya berstatus CLOSED) -->
    @if($stats['has_lock'] && $stats['my_locking_ca'])
        <div class="bg-amber-50/90 border border-amber-300 rounded-[6px] p-4 sm:p-5 flex items-start space-x-3.5 shadow-card">
            <div class="w-9 h-9 rounded-[3px] bg-amber-200 border border-amber-300 flex items-center justify-center flex-shrink-0 text-amber-900">
                <i data-lucide="lock" class="w-5 h-5"></i>
            </div>
            <div class="flex-grow">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h3 class="text-xs sm:text-sm font-bold text-amber-950 uppercase tracking-wide">Pengajuan Baru Digembok Sistem</h3>
                    <span class="badge-retail bg-amber-200 text-amber-900 border-amber-300 font-mono">
                        CA: {{ $stats['my_locking_ca']->status }}
                    </span>
                </div>
                <p class="text-xs text-amber-900 mt-1 leading-relaxed">
                    Sistem mendeteksi Anda memiliki Cash Advance aktif <strong class="font-mono">{{ $stats['my_locking_ca']->ca_number }}</strong> (<em>{{ $stats['my_locking_ca']->project_name }}</em>). Sesuai aturan tata kelola, pengajuan baru dikunci hingga CA ini resmi <strong>CLOSED</strong>.
                </p>
                <div class="mt-2.5">
                    <a href="{{ route('cash-advances.show', $stats['my_locking_ca']) }}" class="btn-primary text-xs py-1.5 px-3">
                        <span>Buka CA Aktif</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- ACTIVE CA COUNTDOWN & SALDO WIDGET (Split Hero Layout per Design.md) -->
    @if($stats['my_active_ca'])
        @php
            $myCa = $stats['my_active_ca'];
            $daysLeft = $myCa->days_remaining;
            $isOverdue = $myCa->is_overdue;
        @endphp
        <div class="card-commerce overflow-hidden">
            <div class="bg-slate-50 px-5 py-3 border-b border-retail-surface flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-retail-green"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-retail-ink">Cash Advance Berjalan (Proyek Aktif)</h3>
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
                    <h2 class="text-base font-bold text-retail-ink leading-snug">{{ $myCa->project_name }}</h2>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">{{ $myCa->purpose }}</p>
                    <div class="pt-2">
                        <a href="{{ route('cash-advances.show', $myCa) }}" class="text-xs font-bold text-retail-cyan hover:underline inline-flex items-center space-x-1">
                            <span>Lihat Rincian Buku Saldo</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>

                <!-- Live Saldo Display (Right Column - JetBrains Mono per Design.md) -->
                <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-[6px] border border-retail-surface">
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-500">CA Disetujui</span>
                        <span class="block text-xs sm:text-sm font-mono font-bold text-slate-800 mt-1">
                            Rp {{ number_format($myCa->approved_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-500">Top-Up Cair (+)</span>
                        <span class="block text-xs sm:text-sm font-mono font-bold text-emerald-700 mt-1">
                            +Rp {{ number_format($myCa->disbursed_topups_total, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase text-slate-500">Pengeluaran (-)</span>
                        <span class="block text-xs sm:text-sm font-mono font-bold text-rose-700 mt-1">
                            -Rp {{ number_format($myCa->recorded_expenses_total, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="bg-white p-2.5 rounded-[3px] border border-retail-orange shadow-sm">
                        <span class="block text-[10px] font-bold uppercase text-retail-orange">Sisa Saldo</span>
                        <span class="block text-sm sm:text-base font-mono font-extrabold text-retail-ink mt-0.5">
                            Rp {{ number_format($myCa->current_balance, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            @if($myCa->is_low_balance)
                <div class="bg-rose-50 px-5 py-2.5 border-t border-rose-200 text-xs text-rose-800 flex items-center justify-between">
                    <span class="font-medium flex items-center space-x-1.5">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                        <span>Peringatan: Saldo CA Anda tersisa kurang dari 20%. Segera ajukan top-up jika butuh tambahan operasional.</span>
                    </span>
                    <a href="{{ route('cash-advances.show', $myCa) }}#topup" class="font-bold underline text-rose-900 ml-2">Ajukan Top-Up</a>
                </div>
            @endif
        </div>
    @endif

    <!-- STAT METRIC TILES (Design.md: Card rounded 6px, border 1px #DDDDDD, Mono numbers) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @if(auth()->user()->isAtasan() || auth()->user()->isDirektur())
            <div class="card-commerce p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Approval CA</span>
                    <div class="w-8 h-8 rounded-[3px] bg-sky-50 text-retail-cyan flex items-center justify-center border border-sky-100">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-mono font-bold text-retail-ink mt-2">{{ $stats['pending_approval_count'] }}</div>
                <span class="text-[11px] text-slate-500 mt-1 block">Menunggu persetujuan Anda</span>
            </div>

            <div class="card-commerce p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Top-Up Menunggu</span>
                    <div class="w-8 h-8 rounded-[3px] bg-amber-50 text-retail-orange flex items-center justify-center border border-amber-100">
                        <i data-lucide="arrow-up-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-mono font-bold text-retail-ink mt-2">{{ $stats['pending_topup_count'] }}</div>
                <span class="text-[11px] text-slate-500 mt-1 block">Permintaan dana tambahan</span>
            </div>
        @endif

        @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
            <div class="card-commerce p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Verifikasi Struk</span>
                    <div class="w-8 h-8 rounded-[3px] bg-purple-50 text-purple-700 flex items-center justify-center border border-purple-100">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-mono font-bold text-retail-ink mt-2">{{ $stats['pending_verification_count'] ?? 0 }}</div>
                <span class="text-[11px] text-slate-500 mt-1 block">Struk menunggu validasi</span>
            </div>

            <div class="card-commerce p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">CA OVERDUE</span>
                    <div class="w-8 h-8 rounded-[3px] bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                        <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-mono font-bold text-rose-600 mt-2">{{ $stats['overdue_count'] }}</div>
                <span class="text-[11px] text-slate-500 mt-1 block">Lewat batas 2 bulan</span>
            </div>

            <div class="card-commerce p-4 sm:p-5 col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Dana CA Beredar (Float)</span>
                    <div class="w-8 h-8 rounded-[3px] bg-emerald-50 text-retail-green flex items-center justify-center border border-emerald-100">
                        <i data-lucide="banknote" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-mono font-bold text-emerald-800 mt-2">Rp {{ number_format($stats['total_active_float'], 0, ',', '.') }}</div>
                <span class="text-[11px] text-slate-500 mt-1 block">Dana operasional aktif di lapangan</span>
            </div>
        @endif

        @if(auth()->user()->isSuperAdmin())
            <div class="card-commerce p-5 col-span-2 sm:col-span-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-retail-ink uppercase tracking-wide">Konfigurasi & Pengguna Sistem</h3>
                    <p class="text-xs text-slate-500 mt-1">Super Admin bertugas mengelola user master, relasi departemen, dan parameter global sistem.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.users.index') }}" class="btn-primary text-xs py-2 px-3.5">Kelola User</a>
                    <a href="{{ route('admin.settings.index') }}" class="btn-secondary text-xs py-2 px-3.5">Pengaturan Parameter</a>
                </div>
            </div>
        @endif
    </div>

    <!-- RECENT CASH ADVANCES TABLE (Design.md: Card commerce, 1px border #DDDDDD) -->
    <div class="card-commerce overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-retail-surface flex items-center justify-between">
            <h3 class="text-xs font-bold text-retail-ink uppercase tracking-wider flex items-center space-x-2">
                <i data-lucide="history" class="w-4 h-4 text-slate-500"></i>
                <span>Cash Advance Terbaru</span>
            </h3>
            @if(!auth()->user()->isSuperAdmin())
                <a href="{{ route('cash-advances.index') }}" class="text-xs font-bold text-retail-cyan hover:underline">
                    Lihat Semua &rarr;
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-retail-surface">
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
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 font-mono font-bold text-retail-ink">
                                {{ $ca->ca_number }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900">{{ $ca->user->name }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $ca->user->department }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-800">{{ $ca->project_name }}</span>
                                <span class="block text-[10px] font-mono text-slate-400">{{ $ca->project_code ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-800">
                                Rp {{ number_format($ca->approved_amount ?: $ca->requested_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge-retail
                                    {{ $ca->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : '' }}
                                    {{ $ca->status === 'PENDING_APPROVAL' ? 'bg-sky-50 text-sky-800 border-sky-300' : '' }}
                                    {{ $ca->status === 'NEED_SETTLEMENT' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                                    {{ $ca->status === 'PENDING_VERIFICATION' ? 'bg-purple-50 text-purple-800 border-purple-300' : '' }}
                                    {{ $ca->status === 'CLOSED' ? 'bg-slate-100 text-slate-700 border-slate-300' : '' }}
                                    {{ $ca->status === 'REJECTED' ? 'bg-rose-50 text-rose-800 border-rose-300' : '' }}
                                ">
                                    {{ $ca->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-mono">
                                @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                    @if($ca->is_overdue)
                                        <span class="badge-retail bg-rose-100 text-rose-700 border-rose-300 font-bold">OVERDUE</span>
                                    @else
                                        <span class="text-slate-700 font-semibold">{{ $ca->days_remaining }} hr</span>
                                    @endif
                                @elseif($ca->status === 'CLOSED')
                                    <span class="text-slate-400">Lunas</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('cash-advances.show', $ca) }}" class="btn-secondary py-1 px-2.5 text-xs">
                                    <span>Buka</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
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