@extends('layouts.app')

@section('title', 'Pengajuan Digembok')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden text-center p-8 sm:p-10">
        <!-- Lock Icon Animation -->
        <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 shadow-inner mb-6">
            <i data-lucide="lock" class="w-10 h-10"></i>
        </div>

        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengajuan Baru Digembok Sistem</h1>
        <p class="text-slate-600 text-sm mt-2 max-w-lg mx-auto leading-relaxed">
            Sesuai <strong>Prinsip Desain Sistem PT Technodrives</strong>: setiap karyawan tidak dapat membuat pengajuan Cash Advance baru sebelum CA sebelumnya tuntas dan berstatus <strong>CLOSED</strong>.
        </p>

        @if($lockingCA)
            <div class="mt-6 p-5 bg-slate-50 border border-slate-200 rounded-2xl text-left space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="text-xs font-bold text-slate-500 uppercase">Cash Advance Yang Mengunci:</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold uppercase
                        {{ $lockingCA->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $lockingCA->status === 'PENDING_APPROVAL' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $lockingCA->status === 'NEED_SETTLEMENT' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $lockingCA->status === 'PENDING_VERIFICATION' ? 'bg-purple-100 text-purple-800' : '' }}
                    ">
                        Status: {{ $lockingCA->status }}
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ $lockingCA->ca_number }} - {{ $lockingCA->project_name }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $lockingCA->purpose }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-200">
                    <div>
                        <span class="text-slate-400 block">Sisa Saldo Belum Lunas:</span>
                        <span class="font-bold text-slate-900 text-sm">Rp {{ number_format($lockingCA->current_balance, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Batas Waktu / Sisa Hari:</span>
                        <span class="font-bold {{ $lockingCA->is_overdue ? 'text-rose-600' : 'text-slate-800' }} text-sm">
                            {{ $lockingCA->is_overdue ? 'OVERDUE' : ($lockingCA->days_remaining . ' Hari') }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            @if($lockingCA)
                <a href="{{ route('cash-advances.show', $lockingCA) }}" class="w-full sm:w-auto px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow transition inline-flex items-center justify-center space-x-2">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    <span>Buka & Tuntaskan CA Ini</span>
                </a>
            @endif

            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Direktur override notice -->
        <div class="mt-8 pt-6 border-t border-slate-100 text-[11px] text-slate-400 max-w-md mx-auto">
            <span class="font-semibold text-slate-600">Catatan Pengecualian:</span> Gembok hanya dapat dibuka oleh Direktur dalam kondisi mendesak dengan alasan tertulis yang dicatat permanen dalam Audit Log.
        </div>
    </div>
</div>
@endsection
