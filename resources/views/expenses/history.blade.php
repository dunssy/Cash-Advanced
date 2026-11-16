@extends('layouts.app')

@php
    $isAtasan = auth()->user()->isAtasan();
    $isKaryawan = auth()->user()->isKaryawan();
    $pageTitle = $isAtasan ? 'Tracking Penggunaan Dana Divisi' : ($isKaryawan ? 'History Penggunaan Dana' : 'Monitoring Penggunaan Dana');
@endphp

@section('title', $pageTitle)

@section('content')
<div class="space-y-6" x-data="{ receiptModal: false, receiptSrc: '', receiptTitle: '' }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center space-x-2 text-xs font-head text-bodygrey mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#FF3C00] transition">Dashboard</a>
                <span>&bull;</span>
                <span class="text-[#050A1E] font-bold">{{ $pageTitle }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight flex items-center gap-2">
                <i data-lucide="{{ $isAtasan ? 'binoculars' : 'receipt-text' }}" class="w-6 h-6 text-[#FF3C00]"></i>
                <span>{{ $pageTitle }}</span>
            </h1>
            <p class="text-xs text-bodygrey mt-1">
                @if($isAtasan)
                    Pantau secara real-time rincian barang apa saja yang telah dibeli oleh akun karyawan di divisi Anda beserta bukti struk fisik.
                @elseif($isKaryawan)
                    Riwayat seluruh pengeluaran, daftar barang/jasa yang telah Anda beli, dan status validasi struk oleh tim Finance.
                @else
                    Monitoring komprehensif seluruh transaksi pengeluaran dan riwayat pembelian barang operasional perusahaan.
                @endif
            </p>
        </div>

        @if(auth()->user()->canSubmitCashAdvance() && !auth()->user()->isFinance() && !auth()->user()->isDirektur())
            <div class="flex items-center gap-2">
                <a href="{{ route('cash-advances.create') }}" class="btn-brand py-2 px-3 text-xs justify-center shadow-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Ajukan CA Baru</span>
                </a>
            </div>
        @endif
    </div>

    <!-- KPI Metric Summary Tiles -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Nominal Dana Terpakai -->
        <div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-head font-bold text-slate-500 uppercase tracking-wider">Total Dana Terpakai</span>
                <div class="w-8 h-8 rounded-lg bg-orange-50 text-[#FF3C00] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-lg sm:text-2xl font-head font-extrabold text-[#050A1E]">
                    Rp {{ number_format($totalSpent, 0, ',', '.') }}
                </div>
                <span class="text-[10px] text-bodygrey font-body">Akumulasi pengeluaran tercatat</span>
            </div>
        </div>

        <!-- Total Item / Transaksi Pembelian Barang -->
        <div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-head font-bold text-slate-500 uppercase tracking-wider">Item Barang Dibeli</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#2E3087] flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-lg sm:text-2xl font-head font-extrabold text-[#050A1E]">
                    {{ $totalItems }} <span class="text-xs font-normal text-slate-500">Transaksi</span>
                </div>
                <span class="text-[10px] text-bodygrey font-body">Total struk belanja diunggah</span>
            </div>
        </div>

        <!-- Total Valid Terverifikasi -->
        <div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-head font-bold text-slate-500 uppercase tracking-wider">Struk Terverifikasi</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-lg sm:text-2xl font-head font-extrabold text-emerald-600">
                    Rp {{ number_format($totalValid, 0, ',', '.') }}
                </div>
                <span class="text-[10px] text-emerald-700/80 font-body">Telah disetujui tim Finance</span>
            </div>
        </div>

        <!-- Menunggu Verifikasi Struk -->
        <div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-head font-bold text-slate-500 uppercase tracking-wider">Menunggu Validasi</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-lg sm:text-2xl font-head font-extrabold text-amber-600">
                    {{ $pendingCount }} <span class="text-xs font-normal text-slate-500">Struk</span>
                </div>
                <span class="text-[10px] text-amber-700/80 font-body">Dalam antrean verifikasi</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls Card -->
    <div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
        <form method="GET" action="{{ route('expenses.history') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Keyword -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Pencarian Barang / Toko
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama barang, vendor, nomor CA..."
                            class="w-full pl-9 input-commerce text-xs">
                    </div>
                </div>

                <!-- Dropdown Karyawan (If Atasan, Finance, Direktur) -->
                @if(!$isKaryawan && $karyawanList->isNotEmpty())
                    <div>
                        <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                            {{ $isAtasan ? 'Karyawan Divisi' : 'Karyawan Pemohon' }}
                        </label>
                        <select name="user_id" onchange="this.form.submit()" class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                            <option value="">Semua Karyawan</option>
                            @foreach($karyawanList as $karyawan)
                                <option value="{{ $karyawan->id }}" {{ request('user_id') == $karyawan->id ? 'selected' : '' }}>
                                    {{ $karyawan->name }} ({{ $karyawan->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Dropdown Cash Advance / Proyek -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Pilih Cash Advance
                    </label>
                    <select name="cash_advance_id" onchange="this.form.submit()" class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                        <option value="">Semua Dokumen CA</option>
                        @foreach($availableCAs as $ca)
                            <option value="{{ $ca->id }}" {{ request('cash_advance_id') == $ca->id ? 'selected' : '' }}>
                                {{ $ca->ca_number }} - {{ Str::limit($ca->project_name, 25) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropdown Kategori Pengeluaran -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Kategori Barang
                    </label>
                    <select name="category" onchange="this.form.submit()" class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                        <option value="">Semua Kategori</option>
                        <option value="material" {{ request('category') === 'material' ? 'selected' : '' }}>Material & Perlengkapan</option>
                        <option value="transport" {{ request('category') === 'transport' ? 'selected' : '' }}>Transportasi & Bensin</option>
                        <option value="sewa_alat" {{ request('category') === 'sewa_alat' ? 'selected' : '' }}>Sewa Alat & Kendaraan</option>
                        <option value="konsumsi" {{ request('category') === 'konsumsi' ? 'selected' : '' }}>Konsumsi & Makan</option>
                        <option value="lainnya" {{ request('category') === 'lainnya' ? 'selected' : '' }}>Lain-lain</option>
                    </select>
                </div>

                <!-- Dropdown Status Verifikasi -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Status Verifikasi
                    </label>
                    <select name="verification_status" onchange="this.form.submit()" class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                        <option value="">Semua Status</option>
                        <option value="PENDING" {{ request('verification_status') === 'PENDING' ? 'selected' : '' }}>Menunggu Validasi</option>
                        <option value="VALID" {{ request('verification_status') === 'VALID' ? 'selected' : '' }}>Disetujui (VALID)</option>
                        <option value="REJECTED" {{ request('verification_status') === 'REJECTED' ? 'selected' : '' }}>Ditolak</option>
                        <option value="VOID" {{ request('verification_status') === 'VOID' ? 'selected' : '' }}>Dibatalkan (VOID)</option>
                    </select>
                </div>

                <!-- Tanggal Dari -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Dari Tanggal
                    </label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full input-commerce text-xs">
                </div>

                <!-- Tanggal Sampai -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Sampai Tanggal
                    </label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full input-commerce text-xs">
                </div>

                <!-- Action Filter Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-dark py-2.5 px-4 text-xs font-bold font-head w-full justify-center">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan</span>
                    </button>
                    @if(request()->hasAny(['search', 'user_id', 'cash_advance_id', 'category', 'verification_status', 'date_from', 'date_to']))
                        <a href="{{ route('expenses.history') }}" class="btn-secondary py-2.5 px-3 text-xs justify-center" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Tracking Table & Purchases List -->
    <div class="card-commerce overflow-hidden border border-cardborder rounded-2xl bg-white shadow-sm">
        <div class="bg-slate-50 px-4 sm:px-6 py-3.5 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center space-x-2">
                <i data-lucide="list-ordered" class="w-4 h-4 text-[#FF3C00]"></i>
                <h3 class="text-xs font-head font-bold uppercase tracking-wider text-[#050A1E]">
                    {{ $isAtasan ? 'Rincian Penggunaan Dana & Barang Dibeli Divisi' : 'Rincian Pengeluaran Dana' }}
                </h3>
            </div>
            <span class="text-xs font-mono text-slate-500">
                Menampilkan {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }} dari {{ $expenses->total() }} transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-body border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[#050A1E] font-head font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Tgl & Pemohon</th>
                        <th class="py-3 px-4">Nama Proyek / No. CA</th>
                        <th class="py-3 px-4">Barang / Deskripsi Pengeluaran</th>
                        <th class="py-3 px-4">Kategori & Toko</th>
                        <th class="py-3 px-4 text-right">Nominal (Rp)</th>
                        <th class="py-3 px-4 text-center">Bukti Struk</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Tanggal & Pemohon -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-800 text-xs">
                                    {{ $exp->transaction_date ? $exp->transaction_date->format('d/m/Y') : '-' }}
                                </div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <div class="w-5 h-5 rounded-full bg-[#161A2B] text-white flex items-center justify-center text-[9px] font-head font-bold">
                                        {{ $exp->user ? $exp->user->initials : 'U' }}
                                    </div>
                                    <span class="text-[11px] font-head font-semibold text-slate-700 truncate max-w-[120px]">
                                        {{ $exp->user ? $exp->user->name : '-' }}
                                    </span>
                                </div>
                                @if($exp->user && !empty($exp->user->departments))
                                    <div class="text-[9px] font-mono text-slate-400 mt-0.5 truncate max-w-[130px]">
                                        # {{ $exp->user->departments[0] }}
                                    </div>
                                @endif
                            </td>

                            <!-- Proyek & No. CA -->
                            <td class="py-3.5 px-4">
                                @if($exp->cashAdvance)
                                    <a href="{{ route('cash-advances.show', $exp->cashAdvance) }}" class="font-mono text-[11px] font-bold text-[#FF3C00] hover:underline block">
                                        {{ $exp->cashAdvance->ca_number }}
                                    </a>
                                    <span class="text-xs font-semibold text-slate-800 block line-clamp-1" title="{{ $exp->cashAdvance->project_name }}">
                                        {{ $exp->cashAdvance->project_name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Barang / Rincian Belanja -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-bold text-slate-900 text-xs leading-snug">
                                    {{ $exp->description }}
                                </div>
                                @if($exp->notes)
                                    <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1 italic font-body">
                                        "{{ $exp->notes }}"
                                    </p>
                                @endif
                                @if($exp->is_talangan)
                                    <span class="inline-block mt-1 px-1.5 py-0.5 bg-purple-50 text-purple-700 text-[9px] font-mono font-bold rounded border border-purple-200">
                                        TALANGAN PRIBADI
                                    </span>
                                @endif
                                @if($exp->is_late)
                                    <span class="inline-block mt-1 px-1.5 py-0.5 bg-rose-50 text-rose-700 text-[9px] font-mono font-bold rounded border border-rose-200">
                                        CATAT TERLAMBAT
                                    </span>
                                @endif
                            </td>

                            <!-- Kategori & Toko / Vendor -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="badge-retail text-[10px] font-head font-bold uppercase
                                    {{ $exp->category === 'material' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                    {{ $exp->category === 'transport' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                    {{ $exp->category === 'sewa_alat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                    {{ $exp->category === 'konsumsi' ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}
                                    {{ $exp->category === 'lainnya' ? 'bg-slate-100 text-slate-700 border-slate-200' : '' }}
                                ">
                                    {{ $exp->category_label }}
                                </span>
                                <div class="text-[11px] text-slate-600 font-medium mt-1 truncate max-w-[130px]" title="{{ $exp->vendor_name ?: 'Vendor tidak tercatat' }}">
                                    <i data-lucide="store" class="w-3 h-3 inline text-slate-400 mr-0.5"></i>
                                    {{ $exp->vendor_name ?: '-' }}
                                </div>
                            </td>

                            <!-- Nominal -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <span class="font-head font-bold text-sm text-[#050A1E]">
                                    Rp {{ number_format($exp->amount, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Bukti Struk / Foto -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($exp->attachments->isNotEmpty())
                                    @php
                                        $att = $exp->attachments->first();
                                    @endphp
                                    <button type="button" 
                                        @click="receiptModal = true; receiptSrc = '{{ $att->file_url }}'; receiptTitle = '{{ addslashes($exp->description) }} - Rp {{ number_format($exp->amount, 0, ',', '.') }}'"
                                        class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-head font-semibold bg-slate-100 hover:bg-[#FF3C00] hover:text-white transition group border border-slate-200">
                                        <i data-lucide="image" class="w-3.5 h-3.5 text-[#FF3C00] group-hover:text-white transition"></i>
                                        <span>Lihat ({{ $exp->attachments->count() }})</span>
                                    </button>
                                @elseif($exp->is_lost_receipt)
                                    <span class="inline-flex items-center text-[10px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 font-mono">
                                        Surat Hilang
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400 font-mono">Tanpa Struk</span>
                                @endif
                            </td>

                            <!-- Status Verifikasi -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($exp->verification_status === 'VALID')
                                    <span class="badge-retail bg-emerald-50 text-emerald-700 border-emerald-300 font-bold text-[10px]">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        <span>VALID</span>
                                    </span>
                                    @if($exp->verifier)
                                        <div class="text-[9px] text-slate-400 font-mono mt-0.5">Oleh: {{ $exp->verifier->name }}</div>
                                    @endif
                                @elseif($exp->verification_status === 'PENDING')
                                    <span class="badge-retail bg-amber-50 text-amber-800 border-amber-300 font-bold text-[10px]">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span>MENUNGGU</span>
                                    </span>
                                @elseif($exp->verification_status === 'REJECTED')
                                    <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 font-bold text-[10px]">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                        <span>DITOLAK</span>
                                    </span>
                                @elseif($exp->verification_status === 'VOID')
                                    <span class="badge-retail bg-slate-100 text-slate-600 border-slate-300 font-bold text-[10px]">
                                        <span>BATAL (VOID)</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-4 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                                    <i data-lucide="receipt-text" class="w-6 h-6"></i>
                                </div>
                                <h4 class="text-sm font-bold font-head text-[#050A1E]">Belum Ada Riwayat Penggunaan Dana</h4>
                                <p class="text-xs text-slate-500 font-body max-w-sm mx-auto mt-1">
                                    Tidak ditemukan transaksi pengeluaran belanja yang sesuai dengan kriteria filter yang Anda pilih.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Preview Bukti Struk -->
    <div x-show="receiptModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="receiptModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="receiptModal = false"
                class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"></div>

            <div x-show="receiptModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-xl w-full border border-cardborder">
                
                <div class="p-4 bg-[#050A1E] text-white flex items-center justify-between border-b border-[#161A2B]">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-[#FF3C00]"></i>
                        <span class="text-xs font-head font-bold truncate max-w-sm" x-text="receiptTitle"></span>
                    </div>
                    <button type="button" @click="receiptModal = false" class="text-slate-300 hover:text-white transition p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-4 bg-slate-50 flex items-center justify-center max-h-[70vh] overflow-auto">
                    <img :src="receiptSrc" alt="Bukti Struk Pembelian" class="max-w-full h-auto rounded-xl shadow-sm border border-slate-200 object-contain">
                </div>

                <div class="p-3 bg-white border-t border-slate-200 flex items-center justify-between">
                    <a :href="receiptSrc" target="_blank" class="text-xs font-bold font-head text-[#FF3C00] hover:underline inline-flex items-center gap-1">
                        <span>Buka Ukuran Asli</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                    <button type="button" @click="receiptModal = false" class="btn-secondary py-1.5 px-4 text-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
