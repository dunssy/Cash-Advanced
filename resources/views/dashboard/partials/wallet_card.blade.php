{{-- 
    Partial: Dompet Kas Operasional (Universal Financial Wallet Card)
    Menampilkan Saldo Kasbon aktif pemegang akun.
    - Belum ada pengajuan/cair: Saldo Nol (Rp 0)
    - Ada kasbon berjalan: Saldo Terisi real-time beserta rincian Plafon, Dana Terpakai, dan sisa hari.
--}}
@php
    $myCa = $stats['my_active_ca'] ?? null;
    $pendingMyCa = $stats['pending_my_ca'] ?? null;
    $userBal = $stats['user_balance'] ?? 0;
    $userPlafon = $stats['user_plafon'] ?? 0;
    $userSpent = $stats['user_spent'] ?? 0;
    $percentUsed = ($userPlafon > 0) ? min(100, round(($userSpent / $userPlafon) * 100)) : 0;
@endphp

<div class="card-commerce p-5 sm:p-7 border border-cardborder rounded-2xl bg-white shadow-sm overflow-hidden relative">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        
        <!-- Kolom Kiri: Sisa Saldo Kas & Informasi Status -->
        <div class="space-y-3 flex-1">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 text-[#FF3C00] flex items-center justify-center">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-head font-bold uppercase tracking-wider text-slate-500">
                        Sisa Saldo Kasbon Saya
                    </span>
                </div>

                @if($myCa)
                    @if($myCa->is_overdue)
                        <span class="badge-retail bg-rose-100 text-rose-800 border-rose-300 font-bold text-[11px]">
                            <i data-lucide="alert-octagon" class="w-3 h-3 text-rose-600"></i>
                            <span>OVERDUE (>60 HARI)</span>
                        </span>
                    @else
                        <span class="badge-retail bg-emerald-50 text-emerald-800 border-emerald-300 font-bold text-[11px] flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>CA Berjalan ({{ $myCa->days_remaining }} Hari Tersisa)</span>
                        </span>
                    @endif
                @elseif($pendingMyCa)
                    <span class="badge-retail bg-amber-50 text-amber-800 border-amber-300 font-bold text-[11px] flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i>
                        <span>Menunggu Persetujuan</span>
                    </span>
                @else
                    <span class="badge-retail bg-slate-100 text-slate-600 border-slate-300 font-bold text-[11px]">
                        <span>Saldo Nol (Belum Ada Kasbon Aktif)</span>
                    </span>
                @endif
            </div>

            <!-- Nominal Saldo Besar -->
            <div>
                <div class="text-3xl sm:text-5xl font-mono font-black text-[#050A1E] tracking-tight">
                    Rp {{ number_format($userBal, 0, ',', '.') }}
                </div>

                <!-- Keterangan Status Saldo -->
                @if($myCa)
                    <div class="text-xs text-slate-600 mt-2 flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-slate-800">{{ $myCa->project_name }}</span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="font-mono text-[#FF3C00] font-semibold">{{ $myCa->ca_number }}</span>
                        @if($myCa->project_code)
                            <span class="text-slate-300">&bull;</span>
                            <span class="font-mono text-slate-500">Kode: {{ $myCa->project_code }}</span>
                        @endif
                    </div>
                @elseif($pendingMyCa)
                    <p class="text-xs text-amber-800 bg-amber-50/70 p-2.5 rounded-xl border border-amber-200 mt-2">
                        Pengajuan <strong>{{ $pendingMyCa->ca_number }}</strong> ({{ $pendingMyCa->project_name }}) sebesar <strong>Rp {{ number_format($pendingMyCa->requested_amount, 0, ',', '.') }}</strong> sedang diproses. Saldo akan otomatis terisi dan siap digunakan setelah disetujui & dicairkan.
                    </p>
                @else
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed font-body">
                        Saat ini Anda tidak memiliki kasbon berjalan sehingga saldo bernilai <strong>Rp 0</strong>. Saat Anda mengajukan dan dana dicairkan, saldo kasbon akan otomatis terisi di sini.
                    </p>
                @endif
            </div>

            <!-- Progress Bar Realisasi jika ada CA Aktif -->
            @if($myCa && $userPlafon > 0)
                <div class="pt-2">
                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-500 mb-1">
                        <span>Realisasi Pengeluaran Dana:</span>
                        <span class="font-bold text-slate-800">{{ $percentUsed }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden border border-slate-200">
                        <div class="bg-[#FF3C00] h-full rounded-full transition-all duration-300" style="width: {{ $percentUsed }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Rincian Keuangan & Tombol Aksi Cepat -->
        <div class="lg:w-80 bg-slate-50 p-4 sm:p-5 rounded-xl border border-slate-200 space-y-4 flex flex-col justify-between">
            <div class="space-y-2.5">
                <span class="text-[10px] font-head font-bold uppercase tracking-wider text-slate-400 block border-b border-slate-200 pb-1">
                    Ringkasan Kas Operasional
                </span>
                
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Plafon Kas Masuk:</span>
                    <span class="font-mono font-bold text-slate-800">Rp {{ number_format($userPlafon, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Dana Terpakai (Nota):</span>
                    <span class="font-mono font-bold text-rose-600">Rp {{ number_format($userSpent, 0, ',', '.') }}</span>
                </div>

                <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-200">
                    <span class="font-head font-bold text-slate-800">Sisa Saldo:</span>
                    <span class="font-mono font-extrabold text-[#050A1E]">Rp {{ number_format($userBal, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Tombol Aksi Langsung -->
            <div class="pt-2 space-y-2">
                @if($myCa)
                    <a href="{{ route('expenses.create', $myCa) }}" class="btn-brand py-2.5 px-4 text-xs w-full justify-center shadow-sm">
                        <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                        <span>Catat Struk Belanja</span>
                    </a>
                    <a href="{{ route('cash-advances.show', $myCa) }}" class="text-xs font-head font-bold text-[#FF3C00] hover:underline flex items-center justify-center gap-1 py-1">
                        <span>Lihat Rincian Buku Saldo</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                @else
                    @if(auth()->user()->canSubmitCashAdvance() && !auth()->user()->isFinance() && !auth()->user()->isDirektur())
                        <a href="{{ route('cash-advances.create') }}" class="btn-brand py-2.5 px-4 text-xs w-full justify-center shadow-sm">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                            <span>Ajukan CA Baru</span>
                        </a>
                    @endif
                    <a href="{{ route('cash-advances.index') }}" class="text-xs font-head font-semibold text-slate-600 hover:text-[#050A1E] flex items-center justify-center gap-1 py-1">
                        <span>Lihat Riwayat CA Saya</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </div>

    </div>
</div>
