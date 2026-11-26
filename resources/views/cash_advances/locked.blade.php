@extends('layouts.app')

@section('title', 'Pengajuan Belum Tersedia')
@section('page_title', 'Pengajuan Baru Belum Tersedia')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="card-commerce text-center p-7 sm:p-9">
        <!-- Lock Icon -->
        <div class="w-16 h-16 mx-auto rounded-full bg-orange-50 border border-[#FF3C00]/30 flex items-center justify-center text-[#FF3C00] mb-5 shadow-sm">
            <i data-lucide="lock" class="w-8 h-8"></i>
        </div>

        <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight">Belum Bisa Mengajukan Cash Advance Baru</h1>
        <p class="text-bodygrey text-xs sm:text-sm mt-2 max-w-lg mx-auto leading-relaxed">
            Anda belum bisa mengajukan Cash Advance baru. Selesaikan Cash Advance sebelumnya sampai berstatus <strong>Selesai</strong>, lalu ajukan kembali.
        </p>

        @if($lockingCA)
            <div class="mt-6 p-4 sm:p-5 bg-[#F4F4F4] border border-cardborder rounded-[15px] text-left space-y-3">
                <div class="flex items-center justify-between border-b border-cardborder pb-2.5 gap-2">
                    <span class="text-xs font-head font-bold text-bodygrey uppercase tracking-wider">CA Pengunci:</span>
                    <span class="badge-retail text-xs
                        {{ $lockingCA->status === 'ACTIVE' ? 'badge-active' : '' }}
                        {{ $lockingCA->status === 'PENDING_APPROVAL' ? 'badge-pending-approval' : '' }}
                        {{ $lockingCA->status === 'NEED_SETTLEMENT' ? 'badge-need-settlement' : '' }}
                        {{ $lockingCA->status === 'PENDING_VERIFICATION' ? 'badge-pending-verification' : '' }}
                        {{ $lockingCA->status === 'CLOSED' ? 'badge-closed' : '' }}
                        {{ $lockingCA->status === 'REJECTED' ? 'badge-rejected' : '' }}
                    ">
                        @if($lockingCA->status === 'ACTIVE') Aktif (Berjalan) @endif
                        @if($lockingCA->status === 'PENDING_APPROVAL') Menunggu Persetujuan @endif
                        @if($lockingCA->status === 'NEED_SETTLEMENT') Penyelesaian Kas @endif
                        @if($lockingCA->status === 'PENDING_VERIFICATION') Verifikasi Akhir @endif
                        @if($lockingCA->status === 'CLOSED') Selesai @endif
                        @if($lockingCA->status === 'REJECTED') Ditolak @endif
                    </span>
                </div>

                <div>
                    <h3 class="text-sm font-head font-bold text-[#050A1E]">{{ $lockingCA->ca_number }} &bull; <span class="font-sans font-bold text-slate-800">{{ $lockingCA->project_name }}</span></h3>
                    <p class="text-xs text-bodygrey mt-1 leading-relaxed">{{ $lockingCA->purpose }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs pt-2.5 border-t border-cardborder">
                    <div>
                        <span class="text-bodygrey block text-xs font-head font-bold uppercase">Sisa Saldo Kas:</span>
                        <span class="font-head font-bold text-[#050A1E] text-sm">Rp {{ number_format($lockingCA->current_balance, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-bodygrey block text-xs font-head font-bold uppercase">Sisa Waktu:</span>
                        <span class="font-head font-bold {{ $lockingCA->is_overdue ? 'text-[#FF3C00]' : 'text-[#050A1E]' }} text-sm">
                            {{ $lockingCA->is_overdue ? 'Lewat Jatuh Tempo' : ($lockingCA->days_remaining . ' Hari Tersisa') }}
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

            <a href="{{ route('dashboard') }}" class="btn-secondary btn-sm px-4 py-2 text-xs font-head font-semibold rounded-full w-full sm:w-auto inline-flex items-center justify-center gap-1.5 shadow-xs transition active:scale-95">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- Direktur override notice -->
        <div class="mt-6 pt-5 border-t border-slate-100 text-xs text-slate-400 max-w-md mx-auto leading-relaxed">
            <span class="font-bold text-slate-600">Catatan Pengecualian:</span> Izin pengajuan baru sebelum proyek lama selesai hanya dapat diberikan oleh <strong>Direktur</strong> untuk kondisi darurat dengan alasan tertulis.
        </div>
    </div>
</div>
@endsection