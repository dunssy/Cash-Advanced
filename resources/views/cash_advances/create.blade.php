@extends('layouts.app')

@section('title', 'Ajukan Cash Advance Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="{{ route('cash-advances.index') }}" class="text-xs font-semibold text-slate-500 hover:text-retail-ink inline-flex items-center space-x-1 mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar CA</span>
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Form Pengajuan Cash Advance</h1>
        <p class="text-xs text-slate-500 mt-1">Prinsip Tata Kelola: Satu CA = Satu Proyek = Satu Buku Saldo Terpisah.</p>
    </div>

    <div class="card-commerce p-6 sm:p-7">
        <form action="{{ route('cash-advances.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Nama Proyek -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Nama Proyek / Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="project_name" value="{{ old('project_name') }}" required
                    class="w-full input-commerce"
                    placeholder="Contoh: Instalasi & Komisioning Inverter PT ABC Gresik">
                <p class="text-[11px] text-slate-400 mt-1">Satu CA dikhususkan untuk satu proyek tertentu.</p>
            </div>

            <!-- Kode Proyek -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Kode Proyek (Opsional)
                </label>
                <input type="text" name="project_code" value="{{ old('project_code') }}"
                    class="w-full input-commerce font-mono"
                    placeholder="Contoh: PRJ-2026-INV01">
            </div>

            <!-- Tujuan & Rincian Penggunaan -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Tujuan Pengajuan & Rincian Keperluan <span class="text-rose-500">*</span>
                </label>
                <textarea name="purpose" rows="4" required
                    class="w-full input-commerce leading-relaxed"
                    placeholder="Jelaskan kebutuhan dana operasional (misal: transport teknisi, sewa alat crane, penginapan 4 teknisi, perintilan material lokal)...">{{ old('purpose') }}</textarea>
            </div>

            <!-- Nominal Pengajuan -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Nominal Pengajuan (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-mono font-bold text-slate-400">Rp</span>
                    <input type="number" name="requested_amount" value="{{ old('requested_amount') }}" required min="1000" step="any"
                        class="w-full pl-11 input-commerce font-mono font-bold text-slate-900"
                        placeholder="10000000">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Nominal yang diajukan akan dievaluasi oleh Atasan / Direktur sebelum dicairkan Finance.</p>
            </div>

            <!-- Information Card (Design.md: Surface card with crisp 6px border) -->
            <div class="p-4 bg-slate-50 border border-retail-surface rounded-[6px] text-xs text-slate-700 space-y-1.5">
                <span class="font-bold flex items-center space-x-1.5 text-retail-ink">
                    <i data-lucide="info" class="w-4 h-4 text-retail-cyan"></i>
                    <span class="uppercase tracking-wide text-[11px]">Ketentuan Kepatuhan CA:</span>
                </span>
                <ul class="list-disc list-inside space-y-1 text-slate-600 text-[11px] pl-1">
                    <li>Masa aktif CA maksimal <strong>2 bulan (60 hari)</strong> sejak dana dicairkan bank.</li>
                    <li>Struk pengeluaran wajib diunggah maksimal <strong>1 hari kalender</strong> setelah transaksi.</li>
                    <li>Pengajuan CA baru akan <strong>digembok otomatis</strong> sampai CA ini berstatus <strong>CLOSED</strong>.</li>
                </ul>
            </div>

            <div class="pt-3 flex items-center justify-end space-x-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.index') }}" class="btn-secondary py-2 px-4 text-xs">
                    Batal
                </a>
                <button type="submit" class="btn-primary">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Pengajuan CA</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection