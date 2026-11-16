@extends('layouts.app')

@section('title', 'Kelola Master Kode Proyek')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center space-x-2 text-xs font-head text-bodygrey mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#FF3C00] transition">Dashboard</a>
                <span>&bull;</span>
                <span class="text-[#050A1E] font-bold">Master Kode Proyek</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight flex items-center gap-2">
                <i data-lucide="folder-kanban" class="w-6 h-6 text-[#FF3C00]"></i>
                <span>Master Kode Proyek Resmi</span>
            </h1>
            <p class="text-xs text-bodygrey mt-1">
                Daftar kode proyek resmi yang dibuat oleh Atasan/Manajemen. Karyawan akan memilih kode proyek ini dari dropdown saat mengajukan CA baru.
            </p>
        </div>
    </div>

    <!-- Form Tambah Kode Proyek Baru (Card) -->
    <div class="card-commerce p-5 sm:p-6 border border-cardborder rounded-2xl bg-white shadow-sm">
        <div class="flex items-center space-x-2 pb-3 mb-4 border-b border-slate-100">
            <div class="w-7 h-7 rounded-lg bg-orange-50 text-[#FF3C00] flex items-center justify-center">
                <i data-lucide="plus" class="w-4 h-4"></i>
            </div>
            <h2 class="text-xs sm:text-sm font-head font-bold uppercase tracking-wider text-[#050A1E]">
                Tambah Kode Proyek Baru
            </h2>
        </div>

        <form action="{{ route('project-codes.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Kode Proyek -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Kode Proyek <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="code" value="{{ old('code') }}" required
                        placeholder="Contoh: PRJ-2026-PKG01"
                        class="w-full input-commerce font-mono font-bold uppercase text-xs">
                    <p class="text-[10px] text-bodygrey mt-1">Format standar: PRJ-YYYY-KODE (unik)</p>
                </div>

                <!-- Divisi -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Divisi Penanggung Jawab
                    </label>
                    <select name="department" class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div }}" {{ old('department') === $div ? 'selected' : '' }}>
                                {{ $div }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Deskripsi / Keterangan Singkat -->
                <div>
                    <label class="block text-[11px] font-head font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Keterangan Proyek (Opsional)
                    </label>
                    <input type="text" name="description" value="{{ old('description') }}"
                        placeholder="Contoh: Instalasi Inverter 250kW Petrokimia"
                        class="w-full input-commerce text-xs">
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="btn-brand py-2 px-5 text-xs shadow-sm justify-center">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan & Rilis Kode Proyek</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Table Daftar Kode Proyek -->
    <div class="card-commerce overflow-hidden border border-cardborder rounded-2xl bg-white shadow-sm">
        <div class="bg-slate-50 px-4 sm:px-6 py-3.5 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center space-x-2">
                <i data-lucide="list" class="w-4 h-4 text-[#FF3C00]"></i>
                <h3 class="text-xs font-head font-bold uppercase tracking-wider text-[#050A1E]">
                    Daftar Kode Proyek Tersedia
                </h3>
            </div>
            <form method="GET" action="{{ route('project-codes.index') }}" class="flex items-center gap-2">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari kode / keterangan..."
                        class="pl-8 pr-3 py-1.5 input-commerce text-xs w-48 sm:w-64">
                </div>
                <button type="submit" class="btn-dark py-1.5 px-3 text-xs">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-body border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 border-b border-slate-200 text-[#050A1E] font-head font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Kode Proyek</th>
                        <th class="py-3 px-4">Divisi</th>
                        <th class="py-3 px-4">Keterangan Proyek</th>
                        <th class="py-3 px-4">Dibuat Oleh</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projectCodes as $pCode)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Kode Proyek -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="badge-retail bg-[#161A2B] text-white font-mono font-bold text-xs px-2.5 py-1 rounded-lg shadow-sm border border-[#161A2B]">
                                    {{ $pCode->code }}
                                </span>
                            </td>

                            <!-- Divisi -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-800">
                                    {{ $pCode->department ?: 'Semua Divisi' }}
                                </span>
                            </td>

                            <!-- Keterangan -->
                            <td class="py-3.5 px-4">
                                <span class="text-xs text-slate-700">
                                    {{ $pCode->description ?: '-' }}
                                </span>
                            </td>

                            <!-- Dibuat Oleh -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-head font-semibold text-slate-800 text-xs">
                                    {{ $pCode->creator ? $pCode->creator->name : 'Sistem' }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    {{ $pCode->created_at ? $pCode->created_at->format('d/m/Y') : '-' }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($pCode->is_active)
                                    <span class="badge-retail bg-emerald-50 text-emerald-700 border-emerald-300 font-bold text-[10px]">
                                        AKTIF
                                    </span>
                                @else
                                    <span class="badge-retail bg-slate-100 text-slate-500 border-slate-300 font-bold text-[10px]">
                                        NONAKTIF
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Toggle Status Button -->
                                    <form action="{{ route('project-codes.toggle-status', $pCode) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded text-[11px] font-head font-semibold transition {{ $pCode->is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }}" title="{{ $pCode->is_active ? 'Nonaktifkan kode proyek' : 'Aktifkan kode proyek' }}">
                                            {{ $pCode->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    <!-- Delete Button -->
                                    <form action="{{ route('project-codes.destroy', $pCode) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kode proyek {{ $pCode->code }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 transition" title="Hapus kode proyek">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                                Belum ada kode proyek resmi yang didaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($projectCodes->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $projectCodes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
