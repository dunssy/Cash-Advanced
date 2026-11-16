@extends('layouts.app')

@section('title', 'Dashboard Keuangan & Operasional')

@section('content')
<div class="space-y-6">

    {{-- 1. Header Ringkas Keuangan & Greeting --}}
    @include('dashboard.partials.header')

    {{-- 2. Dompet Kas Operasional (Universal Financial Wallet Card) --}}
    @include('dashboard.partials.wallet_card')

    {{-- 3. Komponen & Fitur Khusus Berdasarkan Role Pengguna --}}
    @if(auth()->user()->isAtasan())
        {{-- Khusus Role Atasan: Antrean Approval, Top-Up, Tracking Dana Divisi, Master Kode Proyek & Tabel Belanja Divisi --}}
        @include('dashboard.partials.role_atasan')
    @elseif(auth()->user()->isFinance())
        {{-- Khusus Role Finance: Pencairan CA, Verifikasi Struk, Likuiditas Float Dana, Laporan Pajak & Audit --}}
        @include('dashboard.partials.role_finance')
    @elseif(auth()->user()->isDirektur())
        {{-- Khusus Role Direktur: Approval Khusus Eksekutif, Pengawasan Float Organisasi, Audit Trail & Laporan --}}
        @include('dashboard.partials.role_direktur')
    @elseif(auth()->user()->isSuperAdmin())
        {{-- Khusus Role Super Admin: Konfigurasi Parameter & Manajemen Pengguna --}}
        @include('dashboard.partials.role_superadmin')
    @else
        {{-- Khusus Role Karyawan: Akses Cepat Ajukan CA, Catat Struk, Ringkasan Belanja Pribadi --}}
        @include('dashboard.partials.role_karyawan')
    @endif

    {{-- 4. Riwayat Cash Advance Terbaru (Shared untuk semua role) --}}
    @include('dashboard.partials.recent_cash_advances')

</div>
@endsection
