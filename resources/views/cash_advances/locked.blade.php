@extends('layouts.app')

@section('title', 'Pengajuan Digembok')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="card-commerce text-center p-7 sm:p-9">
        <!-- Lock Icon -->
        <div class="w-16 h-16 mx-auto rounded-[6px] bg-amber-50 border border-amber-300 flex items-center justify-center text-retail-orange mb-5">
            <i data-lucide="lock" class="w-8 h-8"></i>
        </div>

        <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Pengajuan Baru Digembok Sistem</h1>
        <p class="text-slate-600 text-xs sm:text-sm mt-2 max-w-lg mx-auto leading-relaxed">
            Sesuai <strong>Prinsip Tata Kelola PT Technodrives</strong>: setiap karyawan tidak dapat membuat pengajuan Cash Advance baru sebelum CA sebelumnya tuntas dan berstatus <strong>CLOSED</strong>.
        </p>

        @if($lockingCA)
            <div class="mt-6 p-4 sm:p-5 bg-slate-50 border border-retail-surface rounded-[6px] text-left space-y-3">
                <div class="flex items-center justify-between border-b border-retail-surface pb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Cash Advance Yang Mengunci:</span>
                    <span class="badge-retail
                        {{ $lockingCA->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : '' }}
                        {{ $lockingCA->status === 'PENDING_APPROVAL' ? 'bg-sky-50 text-sky-800 border-sky-300' : '' }}
                        {{ $lockingCA->status === 'NEED_SETTLEMENT' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                        {{ $lockingCA->status === 'PENDING_VERIFICATION' ? 'bg-purple-50 text-purple-800 border-purple-300' : '' }}
                    ">
                        Status: {{ $lockingCA->status }}
                    </span>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-retail-ink font-mono">{{ $lockingCA->ca_number }} &bull; <span class="font-sans font-bold text-slate-800">{{ $lockingCA->project_name }}</span></h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $lockingCA->purpose }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs pt-2.5 border-t border-retail-surface">
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Sisa Saldo Kas:</span>
                        <span class="font-mono font-bold text-retail-ink text-sm">Rp {{ number_format($lockingCA->current_balance, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Masa Berlaku / Sisa:</span>
                        <span class="font-mono font-bold {{ $lockingCA->is_overdue ? 'text-rose-600' : 'text-slate-800' }} text-sm">
                            {{ $lockingCA->is_overdue ? 'OVERDUE' : ($lockingCA->days_remaining . ' Hari Tersisa') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-2.5">
            @if($lockingCA)
                <a href="{{ route('cash-advances.show', $lockingCA) }}" class="btn-primary w-full sm:w-auto">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    <span>Buka & Tuntaskan CA Ini</span>
                </a>
            @endif

            <a href="{{ route('dashboard') }}" class="btn-secondary w-full sm:w-auto">
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- Direktur override notice -->
        <div class="mt-6 pt-5 border-t border-slate-100 text-[11px] text-slate-400 max-w-md mx-auto leading-relaxed">
            <span class="font-bold text-slate-600">Catatan Pengecualian:</span> Gembok pengajuan hanya dapat dibuka oleh <strong>Direktur</strong> bila terdapat kondisi darurat proyek baru yang telah disetujui, dengan alasan tertulis yang tercatat permanen di Audit Log.
        </div>
    </div>
</div>
@endsection