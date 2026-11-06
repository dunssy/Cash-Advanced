@extends('layouts.app')

@section('title', 'Ajukan Cash Advance Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('cash-advances.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 inline-flex items-center space-x-1 mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar CA</span>
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Form Pengajuan Cash Advance</h1>
        <p class="text-xs text-slate-500 mt-1">Prinsip: Satu CA = Satu Proyek = Satu Buku Saldo Terpisah.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('cash-advances.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Nama Proyek -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Proyek / Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="project_name" value="{{ old('project_name') }}" required
                    class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none transition"
                    placeholder="Contoh: Instalasi & Komisioning Inverter PT ABC Gresik">
                <p class="text-[11px] text-slate-400 mt-1">Satu CA dikhususkan untuk satu proyek tertentu.</p>
            </div>

            <!-- Kode Proyek -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Kode Proyek (Opsional)
                </label>
                <input type="text" name="project_code" value="{{ old('project_code') }}"
                    class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none transition"
                    placeholder="Contoh: PRJ-2026-INV01">
            </div>

            <!-- Tujuan & Rincian Penggunaan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tujuan Pengajuan & Rincian Keperluan <span class="text-rose-500">*</span>
                </label>
                <textarea name="purpose" rows="4" required
                    class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none transition leading-relaxed"
                    placeholder="Jelaskan kebutuhan dana operasional (misal: transport, sewa alat crane, penginapan 4 teknisi, perintilan material lokal)...">{{ old('purpose') }}</textarea>
            </div>

            <!-- Nominal Pengajuan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nominal Pengajuan (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-bold text-slate-400">Rp</span>
                    <input type="number" name="requested_amount" value="{{ old('requested_amount') }}" required min="10000" step="1000"
                        class="w-full pl-11 pr-3.5 py-2.5 text-sm font-bold text-slate-900 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none transition"
                        placeholder="10000000">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Nominal yang diajukan akan dievaluasi oleh Atasan / Direktur sebelum dicairkan Finance.</p>
            </div>

            <!-- Information Card -->
            <div class="p-4 bg-sky-50/70 border border-sky-100 rounded-xl text-xs text-sky-800 space-y-1">
                <span class="font-bold flex items-center space-x-1 text-sky-900">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>Aturan Pengajuan:</span>
                </span>
                <ul class="list-disc list-inside space-y-0.5 text-sky-700 text-[11px]">
                    <li>Masa aktif CA maksimal <strong>2 bulan</strong> sejak dana dicairkan.</li>
                    <li>Struk wajib dicatat maksimal <strong>1 hari</strong> setelah transaksi terjadi.</li>
                    <li>Pengajuan baru digembok sampai CA ini berstatus <strong>CLOSED</strong>.</li>
                </ul>
            </div>

            <div class="pt-3 flex items-center justify-end space-x-3">
                <a href="{{ route('cash-advances.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition flex items-center space-x-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Pengajuan CA</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
