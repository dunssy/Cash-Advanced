@extends('layouts.app')

@section('title', 'Ajukan Cash Advance Baru')
@section('page_title', 'Form Pengajuan Cash Advance')

@section('content')
<div class="w-full space-y-6">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-2 border-bottom">
        <div>
            <a href="{{ route('cash-advances.index') }}" class="btn-secondary btn-sm px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-xs rounded-full text-xs font-head transition active:scale-95">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                <span>Kembali ke Daftar CA</span>
            </a>
        </div>
        @if(isset($previewCaNumber))
        <div class="px-3.5 py-1.5 bg-white rounded-full border shadow-xs d-flex align-items-center gap-2">
            <span class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Nomor Dokumen:</span>
            <span class="font-mono fw-bold text-dark text-xs">{{ $previewCaNumber }}</span>
        </div>
        @endif
    </div>

    <div class="card-commerce p-6 sm:p-7 w-full">
        @if(isset($errors) && $errors->any())
            <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-medium space-y-1">
                <div class="font-bold font-head flex items-center space-x-1.5 text-rose-700">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Mohon periksa kembali isian formulir:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-2 text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('cash-advances.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Nama Proyek -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                    Nama Proyek <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="project_name" value="{{ old('project_name') }}" required
                    class="w-full input-commerce"
                    placeholder="Contoh: Instalasi & Komisioning Inverter PT ABC Gresik">
                <p class="text-[11px] text-bodygrey mt-1">Kode proyek akan dibuat otomatis oleh sistem setelah pengajuan dikirim.</p>
            </div>

            <!-- Tujuan Proyek -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                    Tujuan Proyek <span class="text-rose-500">*</span>
                </label>
                <textarea name="purpose" rows="4" required
                    class="w-full input-commerce leading-relaxed"
                    placeholder="Jelaskan tujuan proyek dan kebutuhan dana operasional...">{{ old('purpose') }}</textarea>
            </div>

            <!-- Nominal Pengajuan -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                    Nominal Pengajuan (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-head font-bold text-slate-400">Rp</span>
                    <input type="number" name="requested_amount" value="{{ old('requested_amount') }}" required min="10000" step="1"
                        class="w-full pl-11 input-commerce font-head font-bold text-[#050A1E]"
                        placeholder="10000000">
                </div>
                <p class="text-[11px] text-bodygrey mt-1">Nominal yang diajukan akan dievaluasi oleh pimpinan sebelum dicairkan Finance.</p>
            </div>

            <div class="pt-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.index') }}" class="btn-secondary py-2.5 px-4 text-xs text-center justify-center rounded-full">
                    Batal
                </a>
                <button type="submit" class="btn-primary py-2.5 px-5 justify-center rounded-full">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Pengajuan CA</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
