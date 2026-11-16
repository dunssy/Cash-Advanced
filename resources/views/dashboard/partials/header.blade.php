{{-- 
    Partial: Dashboard Header & Greeting
    Menampilkan salam pembuka, role pengguna, tanggal real-time, dan tombol aksi pengajuan/pencatatan.
--}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-200">
    <div>
        <div class="flex items-center space-x-2 text-xs font-head text-bodygrey mb-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-mono text-[11px] font-bold border border-slate-200">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                <span>{{ strtoupper(auth()->user()->role) }}</span>
            </span>
            <span>&bull;</span>
            <span class="text-slate-500 font-mono text-xs">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
        <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight">
            Halo, {{ auth()->user()->name }}
        </h1>
        <p class="text-xs text-bodygrey mt-0.5">
            Sistem Manajemen Cash Advance & Settlement operasional proyek &bull; <strong class="text-[#050A1E] font-head">PT. Technodrives</strong>
        </p>
    </div>

    <!-- Quick Access Action Buttons -->
    <div class="flex items-center gap-2 flex-wrap">
        @if(auth()->user()->canSubmitCashAdvance() && !auth()->user()->isFinance() && !auth()->user()->isDirektur())
            @if($stats['has_lock'])
                <a href="{{ route('cash-advances.create') }}" class="btn-dark py-2 px-3.5 text-xs justify-center shadow-sm">
                    <i data-lucide="lock" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                    <span>Status Gembok CA</span>
                </a>
            @else
                <a href="{{ route('cash-advances.create') }}" class="btn-brand py-2 px-4 text-xs justify-center shadow-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Ajukan CA Baru</span>
                </a>
            @endif
        @endif

        @if($stats['my_active_ca'])
            <a href="{{ route('expenses.create', $stats['my_active_ca']) }}" class="btn-secondary py-2 px-3.5 text-xs justify-center shadow-sm">
                <i data-lucide="camera" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                <span>Catat Struk</span>
            </a>
        @endif
    </div>
</div>
