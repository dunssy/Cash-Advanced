@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="container-fluid px-0">

    <!-- Executive Welcome Card -->
    <div class="card border-0 text-white mb-4 shadow-sm" style="background-color: #050A1E; border-radius: 15px;">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <h2 class="h4 fw-bold font-head mb-0 text-white">
                        Halo, {{ auth()->user()->name }}
                    </h2>
                    <span class="badge {{ auth()->user()->isDirektur() ? 'text-bg-warning text-dark' : (auth()->user()->isFinance() ? 'text-bg-light' : (auth()->user()->isSuperAdmin() ? 'text-bg-danger' : 'text-bg-secondary')) }} text-uppercase px-2.5 py-1" style="font-size: 12px; letter-spacing: 0.5px;">
                        <i class="bi bi-shield-check me-1"></i>Akses: {{ auth()->user()->role }}
                    </span>
                </div>
                <p class="text-secondary small mb-0" style="color: #94A3B8 !important;">
                    Sistem Manajemen Cash Advance & Settlement Operasional Proyek &bull; <strong class="text-white">PT. Technodrives Indonesia</strong>
                </p>
            </div>
        </div>
    </div>

    <!-- ACTIVE CA COUNTDOWN & SALDO WIDGET (For Field Engineers / Karyawan with running CA) -->
    @if($stats['my_active_ca'])
        @php
            $myCa = $stats['my_active_ca'];
            $daysLeft = $myCa->days_remaining;
            $isOverdue = $myCa->is_overdue;
        @endphp
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-light border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-success d-inline-block" style="width: 10px; height: 10px;"></span>
                    <strong class="small text-uppercase font-head text-dark">CA Berjalan (Proyek Aktif)</strong>
                </div>
                <div>
                    @if($isOverdue)
                        <span class="badge text-bg-danger px-2.5 py-1">
                            <i class="bi bi-exclamation-octagon me-1"></i>OVERDUE
                        </span>
                    @else
                        <span class="badge {{ $daysLeft <= 7 ? 'text-bg-warning' : 'text-bg-info' }} px-2.5 py-1 font-mono">
                            <i class="bi bi-clock me-1"></i>{{ $daysLeft }} Hari Tersisa
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge text-bg-dark font-mono">{{ $myCa->ca_number }}</span>
                            <small class="text-muted font-mono">Due: {{ $myCa->due_date ? $myCa->due_date->format('d/m/Y') : '-' }}</small>
                        </div>
                        <h4 class="h5 fw-bold text-dark font-head mb-1">{{ $myCa->project_name }}</h4>
                        <p class="text-muted small mb-2">{{ $myCa->purpose }}</p>
                        <a href="{{ route('cash-advances.show', $myCa) }}" class="small fw-bold text-decoration-none d-inline-flex align-items-center gap-1" style="color: #FF3C00;">
                            <span>Lihat Rincian Buku Saldo</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="col-lg-7">
                        <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between gap-3 shadow-xs" style="border-color: #FF3C00 !important; border-width: 2px !important;">
                            <div>
                                <small class="text-uppercase fw-bold text-danger font-head d-block" style="font-size: 12px;">Sisa Saldo Kas</small>
                                <span class="h3 fw-bold text-dark font-mono mb-0 d-block mt-0.5">
                                    Rp {{ number_format($myCa->current_balance, 0, ',', '.') }}
                                </span>
                            </div>
                            <a href="{{ route('expenses.create', $myCa) }}" class="btn-brand btn-sm px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm rounded-full">
                                <i class="bi bi-camera"></i>
                                <span>Catat Struk</span>
                            </a>
                        </div>
                    </div>

                    @php
                        $totalBudget = $myCa->approved_amount + $myCa->disbursed_topups_total;
                        $percentUsed = ($totalBudget > 0) ? min(100, round(($myCa->recorded_expenses_total / $totalBudget) * 100)) : 0;
                    @endphp
                    <div class="col-12 pt-2 border-top">
                        <div class="d-flex justify-content-between small font-mono text-muted mb-1.5">
                            <span>Realisasi Pengeluaran:</span>
                            <strong class="text-dark">{{ $percentUsed }}% (Rp {{ number_format($myCa->recorded_expenses_total, 0, ',', '.') }} / Rp {{ number_format($totalBudget, 0, ',', '.') }})</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $percentUsed }}%; background-color: #FF3C00;" aria-valuenow="{{ $percentUsed }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            @if($myCa->is_low_balance)
                <div class="card-footer bg-danger-subtle text-danger-emphasis py-2 px-4 d-flex align-items-center justify-content-between small">
                    <span class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Peringatan: Saldo CA Anda tersisa kurang dari 20%.</span>
                    </span>
                    <a href="{{ route('cash-advances.show', $myCa) }}#topup" class="fw-bold text-danger text-decoration-none">Ajukan Top-Up</a>
                </div>
            @endif
        </div>
    @elseif(auth()->user()->isKaryawan())
        <!-- Clean Empty State for Karyawan when no active CA -->
        <div class="card border-0 shadow-sm mb-4 text-center py-5 px-4" style="border-radius: 15px;">
            <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px;">
                <i class="bi bi-receipt fs-3"></i>
            </div>
            <h5 class="fw-bold text-dark font-head mb-1">Tidak Ada Cash Advance Berjalan</h5>
            <p class="text-muted small mx-auto mb-3" style="max-width: 420px;">
                Anda tidak memiliki kasbon proyek aktif saat ini. Ajukan permohonan baru untuk memulai operasional lapangan.
            </p>
            @if(auth()->user()->canSubmitCashAdvance())
                <div>
                    <a href="{{ route('cash-advances.create') }}" class="btn-brand btn-sm px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm rounded-full">
                        <i class="bi bi-plus-circle"></i>
                        <span>Ajukan CA Baru Sekarang</span>
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- EXECUTIVE FINANCIAL SUMMARY: TOTAL DANA CA BEREDAR (FLOAT) -->
    @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge text-bg-success rounded-pill px-2.5 py-1" style="font-size: 12px;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> Real-Time Float
                        </span>
                        <span class="text-uppercase fw-bold text-secondary small" style="letter-spacing: 0.5px;">
                            Total Dana CA Beredar (Float Perusahaan)
                        </span>
                    </div>
                    <div class="h2 fw-bold text-dark font-head mb-1">
                        Rp {{ number_format($stats['total_active_float'], 0, ',', '.') }}
                    </div>
                    <p class="text-muted small mb-0">
                        Total saldo kas operasional aktif yang sedang dipegang oleh PIC lapangan proyek di seluruh unit perusahaan.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- OPERATIONAL ACTION QUEUE (ADMINLTE SMALL BOXES) -->
    @php
        $isAtasanOnly = auth()->user()->isAtasan() && !auth()->user()->isDirektur();
    @endphp

    @if(auth()->user()->isAtasan() || auth()->user()->isFinance() || auth()->user()->isDirektur())
        <div class="mb-4">
            <h6 class="text-uppercase fw-bold text-secondary mb-3 small" style="letter-spacing: 0.5px;">
                Antrean Operasional & Validasi
            </h6>

            <div class="row g-3">
                @if(auth()->user()->isAtasan() || auth()->user()->isDirektur())
                    <!-- Box 1: Approval CA -->
                    <div class="{{ $isAtasanOnly ? 'col-sm-6' : 'col-xl-3 col-sm-6' }}">
                        <div class="small-box shadow-xs" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                            <div class="inner p-3">
                                <p class="text-secondary small fw-bold text-uppercase mb-1">Approval CA</p>
                                <h3 class="fw-bold text-dark font-head mb-1">{{ $stats['pending_approval_count'] }}</h3>
                                <span class="text-muted small">Menunggu persetujuan Anda</span>
                            </div>
                            <i class="small-box-icon bi bi-check2-square text-warning"></i>
                            <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="small-box-footer bg-light text-dark text-decoration-none py-2 px-3 border-top d-flex align-items-center justify-content-between small fw-semibold">
                                <span>Lihat Antrean</span>
                                <i class="bi bi-arrow-right-circle-fill text-danger"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Box 2: Top-Up Menunggu -->
                    <div class="{{ $isAtasanOnly ? 'col-sm-6' : 'col-xl-3 col-sm-6' }}">
                        <div class="small-box shadow-xs" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                            <div class="inner p-3">
                                <p class="text-secondary small fw-bold text-uppercase mb-1">Top-Up Menunggu</p>
                                <h3 class="fw-bold text-dark font-head mb-1">{{ $stats['pending_topup_count'] }}</h3>
                                <span class="text-muted small">Permintaan dana tambahan</span>
                            </div>
                            <i class="small-box-icon bi bi-arrow-up-circle text-danger"></i>
                            <a href="{{ route('cash-advances.index') }}" class="small-box-footer bg-light text-dark text-decoration-none py-2 px-3 border-top d-flex align-items-center justify-content-between small fw-semibold">
                                <span>Lihat Pengajuan</span>
                                <i class="bi bi-arrow-right-circle-fill text-danger"></i>
                            </a>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->isFinance())
                    <!-- Box 1 (Finance): Pencairan CA -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="small-box shadow-xs" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                            <div class="inner p-3">
                                <p class="text-secondary small fw-bold text-uppercase mb-1">Pencairan CA</p>
                                <h3 class="fw-bold text-dark font-head mb-1">{{ $stats['pending_disbursement_count'] }}</h3>
                                <span class="text-muted small">CA disetujui siap dicairkan</span>
                            </div>
                            <i class="small-box-icon bi bi-wallet2 text-success"></i>
                            <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="small-box-footer bg-light text-dark text-decoration-none py-2 px-3 border-top d-flex align-items-center justify-content-between small fw-semibold">
                                <span>Cairkan Dana</span>
                                <i class="bi bi-arrow-right-circle-fill text-danger"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Box 2 (Finance): Top-Up Menunggu -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="small-box shadow-xs" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                            <div class="inner p-3">
                                <p class="text-secondary small fw-bold text-uppercase mb-1">Top-Up Menunggu</p>
                                <h3 class="fw-bold text-dark font-head mb-1">{{ $stats['pending_topup_count'] }}</h3>
                                <span class="text-muted small">Persetujuan top-up</span>
                            </div>
                            <i class="small-box-icon bi bi-arrow-up-circle text-danger"></i>
                            <a href="{{ route('cash-advances.index') }}" class="small-box-footer bg-light text-dark text-decoration-none py-2 px-3 border-top d-flex align-items-center justify-content-between small fw-semibold">
                                <span>Lihat Pengajuan</span>
                                <i class="bi bi-arrow-right-circle-fill text-danger"></i>
                            </a>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
                    <!-- Box 3: Verifikasi Struk -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="small-box shadow-xs" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                            <div class="inner p-3">
                                <p class="text-secondary small fw-bold text-uppercase mb-1">Verifikasi Struk</p>
                                <h3 class="fw-bold text-dark font-head mb-1">{{ $stats['pending_verification_count'] ?? 0 }}</h3>
                                <span class="text-muted small">Struk menunggu validasi</span>
                            </div>
                            <i class="small-box-icon bi bi-receipt text-primary"></i>
                            <a href="{{ route('cash-advances.index', ['status' => 'ACTIVE']) }}" class="small-box-footer bg-light text-dark text-decoration-none py-2 px-3 border-top d-flex align-items-center justify-content-between small fw-semibold">
                                <span>Periksa Struk</span>
                                <i class="bi bi-arrow-right-circle-fill text-danger"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Box 4: CA Overdue -->
                    <div class="col-xl-3 col-sm-6">
                        <div class="small-box shadow-xs" style="background-color: #FFFFFF; border: 1px solid #E2E8F0;">
                            <div class="inner p-3">
                                <p class="text-secondary small fw-bold text-uppercase mb-1">CA Overdue</p>
                                <h3 class="fw-bold text-danger font-head mb-1">{{ $stats['overdue_count'] }}</h3>
                                <span class="text-danger fw-semibold small">Lewat batas 2 bulan</span>
                            </div>
                            <i class="small-box-icon bi bi-exclamation-octagon text-danger"></i>
                            <a href="{{ route('cash-advances.index', ['status' => 'OVERDUE']) }}" class="small-box-footer bg-light text-dark text-decoration-none py-2 px-3 border-top d-flex align-items-center justify-content-between small fw-semibold">
                                <span>Lihat Detail</span>
                                <i class="bi bi-arrow-right-circle-fill text-danger"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ADMIN PANEL FOR SUPER ADMIN -->
    @if(auth()->user()->isSuperAdmin())
        <div class="card border-0 shadow-sm mb-4 mx-3" style="border-radius: 15px;">
            <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                <div>
                    <h5 class="fw-bold text-dark font-head mb-1">Konfigurasi & Pengguna Sistem</h5>
                    <p class="text-muted small mb-0">Super Admin bertugas mengelola user master, relasi departemen, dan parameter global sistem.</p>
                </div>
                <div class="ms-auto d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn-brand btn-sm px-3.5 py-2 fw-semibold shadow-xs rounded-full">Kelola User</a>
                    <a href="{{ route('admin.settings.index') }}" class="btn-secondary btn-sm px-3.5 py-2 fw-semibold shadow-xs rounded-full">Pengaturan Pengajuan CA</a>
                </div>
            </div>
        </div>
    @endif

    <!-- RECENT TRANSACTIONS TABLE (ADMINLTE CARD) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
            <div>
                <h5 class="h6 fw-bold text-dark font-head mb-0">
                </h5>
                {{ auth()->user()->isKaryawan() ? 'Pengajuan Cash Advance Terkini Anda' : 'Aktivitas Cash Advance Terbaru Perusahaan' }}
                <small class="text-muted">Memantau perkembangan status kasbon operasional yang sedang berjalan.</small>
            </div>
            <div class="ms-auto">
                <a href="{{ route('cash-advances.index') }}" class="btn-secondary btn-sm px-3 py-1.5 d-inline-flex align-items-center gap-1 fw-semibold rounded-full">
                    <span>Lihat Semua</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-technodrives" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th class="ps-4">No. CA</th>
                            @if(!auth()->user()->isKaryawan())
                                <th>Pemohon</th>
                            @endif
                            <th>Proyek & Keperluan</th>
                            <th class="text-end">Sisa Saldo</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCAs as $ca)
                            <tr>
                                <td class="ps-4 fw-bold font-mono text-dark">
                                    {{ $ca->ca_number }}
                                </td>
                                @if(!auth()->user()->isKaryawan())
                                    <td>
                                        <strong class="d-block text-dark font-head">{{ $ca->user->name }}</strong>
                                        <small class="text-muted">{{ $ca->user->department ?: 'Staff' }}</small>
                                    </td>
                                @endif
                                <td>
                                    <span class="d-block fw-semibold text-dark text-truncate" style="max-width: 280px;">{{ $ca->project_name }}</span>
                                    <small class="text-muted text-truncate d-block" style="max-width: 280px;">{{ $ca->purpose }}</small>
                                </td>
                                <td class="text-end fw-bold font-mono {{ $ca->status === 'CLOSED' || $ca->current_balance == 0 ? 'text-success' : 'text-danger' }}">
                                    @if($ca->status === 'CLOSED')
                                        <span class="text-success"><i class="bi bi-check me-1"></i>Rp 0 (Lunas)</span>
                                    @else
                                        Rp {{ number_format($ca->current_balance, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge-retail text-xs
                                        {{ $ca->status === 'PENDING_APPROVAL' ? ($ca->approved_by ? 'bg-sky-50 text-sky-800 border-sky-300' : 'badge-pending-approval') : '' }}
                                        {{ $ca->status === 'REJECTED' ? 'badge-rejected' : '' }}
                                        {{ $ca->status === 'ACTIVE' ? 'badge-active' : '' }}
                                        {{ $ca->status === 'NEED_SETTLEMENT' ? 'badge-need-settlement' : '' }}
                                        {{ $ca->status === 'PENDING_VERIFICATION' ? 'badge-pending-verification' : '' }}
                                        {{ $ca->status === 'CLOSED' ? 'badge-closed' : '' }}
                                    ">
                                        @if($ca->status === 'PENDING_APPROVAL')
                                            {{ $ca->approved_by ? 'Disetujui • Pencairan' : 'Menunggu Approval' }}
                                        @elseif($ca->status === 'REJECTED')
                                            Ditolak
                                        @elseif($ca->status === 'ACTIVE')
                                            Aktif
                                        @elseif($ca->status === 'NEED_SETTLEMENT')
                                            Wajib Settlement
                                        @elseif($ca->status === 'PENDING_VERIFICATION')
                                            Verifikasi Akhir
                                        @elseif($ca->status === 'CLOSED')
                                            Selesai
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('cash-advances.show', $ca) }}" class="btn-secondary btn-sm px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 rounded-full">
                                        <span>Detail</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isKaryawan() ? 5 : 6 }}" class="text-center text-muted py-5 small">
                                    Belum ada aktivitas Cash Advance terbaru saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
