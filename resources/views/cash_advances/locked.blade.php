@extends('layouts.app')

@section('title', 'Pengajuan Digembok')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="card-commerce text-center p-7 sm:p-9">
        <!-- Lock Icon -->
        <div class="w-16 h-16 mx-auto rounded-full bg-orange-50 border border-[#FF3C00]/30 flex items-center justify-center text-[#FF3C00] mb-5 shadow-sm">
            <i data-lucide="lock" class="w-8 h-8"></i>
        </div>

        <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight">Pengajuan Baru Digembok Sistem</h1>
        <p class="text-bodygrey text-xs sm:text-sm mt-2 max-w-lg mx-auto leading-relaxed">
            Sesuai <strong>Prinsip Tata Kelola PT Technodrives</strong>: setiap karyawan tidak dapat membuat pengajuan Cash Advance baru sebelum CA sebelumnya tuntas dan berstatus <strong>CLOSED</strong>.
        </p>

        @if($lockingCA)
            <div class="mt-6 p-4 sm:p-5 bg-[#F4F4F4] border border-cardborder rounded-[15px] text-left space-y-3">
                <div class="flex items-center justify-between border-b border-cardborder pb-2.5 gap-2">
                    <span class="text-[11px] font-head font-bold text-bodygrey uppercase tracking-wider">CA Pengunci:</span>
                    <span class="badge-retail text-[11px]
                        {{ $lockingCA->status === 'ACTIVE' ? 'badge-active' : '' }}
                        {{ $lockingCA->status === 'PENDING_APPROVAL' ? 'badge-pending-approval' : '' }}
                        {{ $lockingCA->status === 'NEED_SETTLEMENT' ? 'badge-need-settlement' : '' }}
                        {{ $lockingCA->status === 'PENDING_VERIFICATION' ? 'badge-pending-verification' : '' }}
                        {{ $lockingCA->status === 'CLOSED' ? 'badge-closed' : '' }}
                        {{ $lockingCA->status === 'REJECTED' ? 'badge-rejected' : '' }}
                    ">
                        @if($lockingCA->status === 'ACTIVE') Aktif (Berjalan) @endif
                        @if($lockingCA->status === 'PENDING_APPROVAL') Menunggu Approval @endif
                        @if($lockingCA->status === 'NEED_SETTLEMENT') Wajib Settlement @endif
                        @if($lockingCA->status === 'PENDING_VERIFICATION') Verifikasi Akhir @endif
                        @if($lockingCA->status === 'CLOSED') Closed / Selesai @endif
                        @if($lockingCA->status === 'REJECTED') Ditolak @endif
                    </span>
                </div>

                <div>
                    <h3 class="text-sm font-head font-bold text-[#050A1E]">{{ $lockingCA->ca_number }} &bull; <span class="font-sans font-bold text-slate-800">{{ $lockingCA->project_name }}</span></h3>
                    <p class="text-xs text-bodygrey mt-1 leading-relaxed">{{ $lockingCA->purpose }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs pt-2.5 border-t border-cardborder">
                    <div>
                        <span class="text-bodygrey block text-[10px] font-head font-bold uppercase">Sisa Saldo Kas:</span>
                        <span class="font-head font-bold text-[#050A1E] text-sm">Rp {{ number_format($lockingCA->current_balance, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-bodygrey block text-[10px] font-head font-bold uppercase">Masa Berlaku / Sisa:</span>
                        <span class="font-head font-bold {{ $lockingCA->is_overdue ? 'text-[#FF3C00]' : 'text-[#050A1E]' }} text-sm">
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