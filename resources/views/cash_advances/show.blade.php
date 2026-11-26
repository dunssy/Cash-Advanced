@extends('layouts.app')

@section('title', $cashAdvance->ca_number . ' - ' . $cashAdvance->project_name)
@section('page_title', 'Rincian ' . $cashAdvance->ca_number)

@section('content')
<div class="space-y-6" x-data="{ 
    showApproveModal: false, 
    showRejectModal: false, 
    showDisburseModal: false,
    showCompleteModal: false,
    showRefundModal: false,
    showCloseModal: false,
    showUnlockModal: false,
    showTopupModal: false,
    showTopupRejectModal: false,
    topupRejectId: null,
    topupRejectAmount: '',
    showTopupDisburseModal: false,
    topupDisburseId: null,
    topupDisburseAmount: '',
    showVoidModal: false,
    voidExpenseId: null,
    voidExpenseDesc: '',
    showVerifyModal: false,
    verifyExpenseId: null,
    verifyExpenseDesc: '',
    verifyStatus: 'VALID',
    activeReceiptUrl: null,
    showDeleteCaModal: false,
    showDeleteExpenseModal: false,
    deleteExpenseId: null,
    deleteExpenseDesc: '',
    showDeleteTopupModal: false,
    deleteTopupId: null,
    deleteTopupAmount: ''
}">

    <!-- Top Navigation Breadcrumb & Actions (Responsive & Anti-Overlap) -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 pb-2 border-b border-slate-200">
        <a href="{{ route('cash-advances.index') }}" class="btn-secondary btn-sm px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-xs rounded-full text-xs font-head transition active:scale-95">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Kembali ke Daftar CA</span>
        </a>
        <div class="flex items-center gap-2">
            @if($cashAdvance->canBeSoftDeletedBy(auth()->user()))
                <button type="button" @click="showDeleteCaModal = true" class="btn-secondary btn-sm py-1.5 px-3.5 text-xs inline-flex items-center gap-1.5 font-head font-semibold text-rose-700 border-rose-200 hover:bg-rose-50 shadow-xs rounded-full transition active:scale-95" title="Hapus Cash Advance">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-600"></i>
                    <span>Hapus CA</span>
                </button>
            @endif
            <a href="{{ route('reports.rincian-ca', ['ca_id' => $cashAdvance->id]) }}" class="btn-secondary btn-sm py-1.5 px-3.5 text-xs inline-flex items-center gap-1.5 font-head font-semibold shadow-xs rounded-full transition active:scale-95">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    <!-- MAIN HEADER CARD (Design.md: Card commerce rounded 6px, border 1px #DDDDDD) -->
    <div class="card-commerce overflow-hidden">
        <div class="p-6 sm:p-7">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <!-- CA Information -->
                <div class="space-y-3 max-w-2xl">
                    <div class="flex items-center space-x-2 flex-wrap gap-2">
                        <span class="badge-retail bg-[#161A2B] text-white border border-slate-700 font-head font-bold text-xs">
                            {{ $cashAdvance->ca_number }}
                        </span>
                        
                        <!-- Status Badge (6 Status Resmi PT Technodrives) -->
                        <span class="badge-retail text-xs font-bold uppercase tracking-wide
                            {{ $cashAdvance->status === 'PENDING_APPROVAL' ? ($cashAdvance->approved_by ? 'bg-sky-50 text-sky-800 border-sky-300' : 'badge-pending-approval') : '' }}
                            {{ $cashAdvance->status === 'REJECTED' ? 'badge-rejected' : '' }}
                            {{ $cashAdvance->status === 'ACTIVE' ? 'badge-active' : '' }}
                            {{ $cashAdvance->status === 'NEED_SETTLEMENT' ? 'badge-need-settlement' : '' }}
                            {{ $cashAdvance->status === 'PENDING_VERIFICATION' ? 'badge-pending-verification' : '' }}
                            {{ $cashAdvance->status === 'CLOSED' ? 'badge-closed' : '' }}
                        ">
                            @if($cashAdvance->status === 'PENDING_APPROVAL')
                                {{ $cashAdvance->approved_by ? 'Disetujui • Menunggu Pencairan' : 'Menunggu Persetujuan' }}
                            @endif
                            @if($cashAdvance->status === 'REJECTED') Ditolak @endif
                            @if($cashAdvance->status === 'ACTIVE') Aktif (Berjalan) @endif
                            @if($cashAdvance->status === 'NEED_SETTLEMENT') Penyelesaian Kas @endif
                            @if($cashAdvance->status === 'PENDING_VERIFICATION') Verifikasi Akhir @endif
                            @if($cashAdvance->status === 'CLOSED') Selesai @endif
                        </span>

                        @if($cashAdvance->is_unlocked)
                            <span class="badge-retail bg-orange-50 text-[#FF3C00] border border-[#FF3C00]/40 font-bold">
                                <i data-lucide="unlock" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                                <span>Gembok Di-override Direktur</span>
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight leading-snug">
                        {{ $cashAdvance->project_name }}
                    </h1>
                    @if($cashAdvance->project_code)
                        <span class="text-xs font-mono font-semibold text-slate-500 uppercase tracking-wider block">
                            Kode Proyek: {{ $cashAdvance->project_code }}
                        </span>
                    @endif
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pt-1">
                        {{ $cashAdvance->purpose }}
                    </p>

                    <!-- User metadata -->
                    <div class="pt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500 border-t border-slate-100 font-mono">
                        <div class="flex items-center space-x-1.5 font-sans">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span class="font-bold text-slate-800">{{ $cashAdvance->user->name }}</span>
                            <span class="text-slate-500">({{ $cashAdvance->user->department ?: 'Staff' }})</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Diajukan: {{ $cashAdvance->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($cashAdvance->disbursed_at)
                            <div class="flex items-center space-x-1.5">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>Cair: {{ $cashAdvance->disbursed_at->format('d/m/Y') }} (Ref: {{ $cashAdvance->disbursement_ref }})</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Countdown & Overdue Widget -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center min-w-[240px]">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-1">Masa Berlaku CA (Max 2 Bulan)</span>
                    
                    @if($cashAdvance->status === 'ACTIVE' || $cashAdvance->status === 'NEED_SETTLEMENT')
                        @if($cashAdvance->is_overdue)
                            <div class="badge-retail bg-rose-100 text-rose-800 border-rose-300 font-bold mb-2">
                                <i data-lucide="alert-octagon" class="w-3.5 h-3.5 text-rose-600"></i>
                                <span>Lewat Jatuh Tempo</span>
                            </div>
                            <div class="text-xs text-rose-600 font-bold">Telah Melewati Batas Waktu</div>
                        @else
                            <div class="text-2xl sm:text-3xl font-mono font-bold text-retail-ink tracking-tight">
                                {{ $cashAdvance->days_remaining }} <span class="text-xs font-sans font-semibold text-slate-500">Hari</span>
                            </div>
                            <div class="text-xs font-mono text-slate-500 mt-1">
                                Due Date: {{ $cashAdvance->due_date ? $cashAdvance->due_date->format('d/m/Y') : '-' }}
                            </div>
                        @endif
                    @elseif($cashAdvance->status === 'CLOSED')
                        <div class="text-emerald-700 font-bold text-sm flex items-center justify-center space-x-1.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            <span>CLOSED & LUNAS</span>
                        </div>
                        <span class="text-xs font-mono text-slate-400 mt-1 block">Ditutup: {{ $cashAdvance->closed_at ? $cashAdvance->closed_at->format('d/m/Y') : '-' }}</span>
                    @elseif($cashAdvance->status === 'REJECTED')
                        <div class="text-rose-600 font-bold text-sm">DITOLAK</div>
                    @else
                        <div class="text-xs text-slate-500 italic">Hitung mundur aktif setelah dicairkan Finance</div>
                    @endif
                </div>
            </div>

            <!-- FINANCIAL LEDGER / BUKU SALDO (Prinsip 2 & 3: Saldo dihitung sistem, JetBrains Mono per Design.md) -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center space-x-1.5">
                        <i data-lucide="calculator" class="w-4 h-4 text-slate-400"></i>
                        <span>Buku Saldo Proyek (Dihitung Otomatis Oleh Sistem)</span>
                    </h3>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <!-- 4. Sisa Saldo Tersedia (Hero Stat di Mobile) -->
                    <div class="bg-white p-3.5 sm:p-4 rounded-xl border-2 border-[#FF3C00] shadow-sm col-span-2 sm:col-span-1 sm:order-4">
                        <span class="block text-xs font-bold uppercase text-[#FF3C00] font-head">Sisa Saldo Kas Aktif</span>
                        <span class="block text-xl sm:text-lg font-mono font-black text-[#050A1E] mt-0.5">
                            Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}
                        </span>
                        <span class="text-xs text-slate-500 block mt-0.5 font-head">Wajib Rp 0 untuk CLOSED</span>
                    </div>

                    <!-- 1. CA Awal -->
                    <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-200 sm:order-1">
                        <span class="block text-xs font-bold uppercase text-slate-500 font-head">CA Disetujui</span>
                        <span class="block text-sm sm:text-base font-mono font-bold text-slate-900 mt-1">
                            Rp {{ number_format($cashAdvance->approved_amount ?: $cashAdvance->requested_amount, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-mono text-slate-400 block mt-0.5">Ajuan: Rp {{ number_format($cashAdvance->requested_amount, 0, ',', '.') }}</span>
                    </div>

                    <!-- 2. Top-Up Cair -->
                    <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-200 sm:order-2">
                        <span class="block text-xs font-bold uppercase text-slate-500 font-head">Top-Up Cair (+)</span>
                        <span class="block text-sm sm:text-base font-mono font-bold text-emerald-700 mt-1">
                            +Rp {{ number_format($cashAdvance->disbursed_topups_total, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-mono text-slate-400 block mt-0.5">{{ $cashAdvance->topUps()->where('status', 'DISBURSED')->count() }}x top-up cair</span>
                    </div>

                    <!-- 3. Pengeluaran Tercatat -->
                    <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-200 sm:order-3">
                        <span class="block text-xs font-bold uppercase text-slate-500 font-head">Pengeluaran Tercatat (-)</span>
                        <span class="block text-sm sm:text-base font-mono font-bold text-rose-700 mt-1">
                            -Rp {{ number_format($cashAdvance->recorded_expenses_total, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-mono text-slate-400 block mt-0.5">Valid: Rp {{ number_format($cashAdvance->valid_expenses_total, 0, ',', '.') }}</span>
                    </div>

                    <!-- 5. Talangan & Hasil Settlement -->
                    <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-200 sm:order-5">
                        <span class="block text-xs font-bold uppercase text-slate-500 font-head">Talangan Pribadi</span>
                        <span class="block text-sm sm:text-base font-mono font-bold text-amber-800 mt-1">
                            Rp {{ number_format($cashAdvance->talangan_total, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-mono text-slate-600 block mt-0.5">
                            Settlement: <strong>Rp {{ number_format($cashAdvance->settlement_result, 0, ',', '.') }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Settlement Interpretation (Design.md: Clear message) -->
                <div class="mt-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center space-x-2 text-slate-700">
                        <i data-lucide="info" class="w-4 h-4 text-[#2E3087] flex-shrink-0"></i>
                        <span>
                            <strong>Kondisi Settlement:</strong>
                            @if($cashAdvance->settlement_result > 0)
                                Saldo kas tersisa <strong class="font-mono">Rp {{ number_format($cashAdvance->settlement_result, 0, ',', '.') }}</strong>. Karyawan <strong>mentransfer sisa ke rekening kantor</strong> saat proyek selesai.
                            @elseif($cashAdvance->settlement_result < 0)
                                Talangan darurat melebihi kas. Perusahaan <strong>mereimburse kekurangan <span class="font-mono">Rp {{ number_format(abs($cashAdvance->settlement_result), 0, ',', '.') }}</span> ke rekening karyawan</strong>.
                            @else
                                Saldo impas pas (<span class="font-mono">Rp 0</span>). Langsung ke verifikasi akhir Finance.
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->isKaryawan())
            {{-- Karyawan: TIDAK MENAMPILKAN BAR "AKSI WORKFLOW" --}}
            {{-- Bila status ACTIVE, tampilkan tombol operasional kas pemohon secara bersih dan nyaman --}}
            @if($cashAdvance->status === 'ACTIVE' && $cashAdvance->user_id === auth()->id())
                <div class="bg-slate-50 px-4 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center space-x-2 flex-wrap gap-2">
                        <span class="text-xs font-head font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="wallet" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                            <span>Aksi Kas Operasional Karyawan:</span>
                        </span>
                    </div>
                    <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <a href="{{ route('expenses.create', $cashAdvance) }}" class="btn-brand text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition rounded-full">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <span>Catat Pengeluaran (Upload Struk)</span>
                        </a>

                        <button type="button" @click="showTopupModal = true" class="btn-secondary text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center justify-center gap-1.5 active:scale-95 transition rounded-full">
                            <i data-lucide="arrow-up-circle" class="w-4 h-4 text-[#FF3C00]"></i>
                            <span>Ajukan Top-Up Dana</span>
                        </button>

                        <button type="button" @click="showCompleteModal = true" class="btn-dark text-xs py-2 px-4 border border-emerald-500/40 text-emerald-400 hover:bg-emerald-500 hover:text-white transition w-full sm:w-auto inline-flex items-center justify-center gap-1.5 active:scale-95 transition rounded-full">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                            <span>Tandai Proyek Selesai</span>
                        </button>
                    </div>
                </div>
            @endif
        @else
            <!-- WORKFLOW ACTIONS TOOLBAR (Untuk Atasan / Finance / Direktur / Super Admin) -->
            <div class="bg-[#050A1E] px-4 sm:px-6 py-4 border-t border-[#161A2B] flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-white">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-head font-bold text-slate-300 uppercase tracking-wider">Aksi Workflow:</span>
                </div>

                <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 w-full md:w-auto">
                    <!-- 1. PENDING_APPROVAL -->
                    @if($cashAdvance->status === 'PENDING_APPROVAL')
                        @if(!$cashAdvance->approved_by)
                            @if(auth()->user()->canApproveCA($cashAdvance))
                                <button @click="showApproveModal = true" class="btn-brand text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <span>Setujui Pengajuan CA</span>
                                </button>
                                <button @click="showRejectModal = true" class="btn-dark text-xs py-2 px-4 border border-[#FF3C00]/40 w-full sm:w-auto inline-flex items-center gap-1.5">
                                    <i data-lucide="x" class="w-4 h-4 text-[#FF3C00]"></i>
                                    <span>Tolak CA</span>
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-head font-medium">
                                    <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
                                    <span>Menunggu Persetujuan {{ $cashAdvance->user->isAtasan() || $cashAdvance->user->isFinance() ? 'Direktur' : 'Atasan' }}</span>
                                </span>
                            @endif
                        @else
                            @if(auth()->user()->canDisburseCA($cashAdvance))
                                <button @click="showDisburseModal = true" class="btn-brand text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center gap-1.5 shadow-md">
                                    <i data-lucide="wallet" class="w-4 h-4"></i>
                                    <span>Catat Pencairan Dana {{ auth()->user()->isFinance() ? '(Finance)' : '(Direktur)' }}</span>
                                </button>
                            @endif
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-head font-medium">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                                <span>Disetujui {{ $cashAdvance->approver->name ?? 'Direktur' }} &bull; Menunggu Pencairan {{ auth()->user()->canDisburseCA($cashAdvance) ? '(Siap Dicairkan)' : 'Finance' }}</span>
                            </span>
                        @endif
                    @endif

                    <!-- 2. ACTIVE -->
                    @if($cashAdvance->status === 'ACTIVE')
                        <!-- Pemohon Actions (Bila pemohon adalah atasan/finance/direktur itu sendiri) -->
                        @if($cashAdvance->user_id === auth()->id())
                            <a href="{{ route('expenses.create', $cashAdvance) }}" class="btn-brand text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center gap-1.5">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                                <span>Catat Pengeluaran</span>
                            </a>

                            <button @click="showTopupModal = true" class="btn-secondary text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center gap-1.5">
                                <i data-lucide="arrow-up-circle" class="w-4 h-4 text-[#FF3C00]"></i>
                                <span>Ajukan Top-Up Dana</span>
                            </button>

                            <button @click="showCompleteModal = true" class="btn-dark text-xs py-2 px-4 border border-emerald-500/40 text-emerald-400 hover:bg-emerald-500 hover:text-white transition w-full sm:w-auto inline-flex items-center gap-1.5">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                                <span>Tandai Proyek Selesai</span>
                            </button>
                        @endif

                        <!-- TOP-UP WORKFLOW ACTIONS -->
                        @php
                            $pendingTopup = $cashAdvance->topUps->whereIn('status', ['PENDING_ATASAN', 'PENDING_FINANCE', 'PENDING_DIREKTUR', 'APPROVED'])->first();
                        @endphp
                        @if($pendingTopup)
                            @if($pendingTopup->status === 'PENDING_ATASAN')
                                @if((auth()->user()->isAtasan() || auth()->user()->isDirektur()) && auth()->id() !== $pendingTopup->user_id)
                                    <form action="{{ route('top-ups.approve-atasan', $pendingTopup) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-brand text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center gap-1.5">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>Setujui Top-Up (Rp {{ number_format($pendingTopup->amount, 0, ',', '.') }})</span>
                                        </button>
                                    </form>
                                    <button type="button" @click="showTopupRejectModal = true; topupRejectId = {{ $pendingTopup->id }}; topupRejectAmount = '{{ number_format($pendingTopup->amount, 0, ',', '.') }}'" class="btn-dark text-xs py-2 px-4 border border-[#FF3C00]/40 w-full sm:w-auto inline-flex items-center gap-1.5">
                                        <i data-lucide="x" class="w-4 h-4 text-[#FF3C00]"></i>
                                        <span>Tolak Top-Up</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-300 text-xs font-head font-medium">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-blue-400"></i>
                                        <span>Top-Up Rp {{ number_format($pendingTopup->amount, 0, ',', '.') }} Menunggu Approval Atasan</span>
                                    </span>
                                @endif
                            @elseif($pendingTopup->status === 'PENDING_FINANCE')
                                @if((auth()->user()->isFinance() || auth()->user()->isDirektur()) && auth()->id() !== $pendingTopup->user_id)
                                    <form action="{{ route('top-ups.approve-finance', $pendingTopup) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-brand text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center gap-1.5">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>Setujui Top-Up Finance (Rp {{ number_format($pendingTopup->amount, 0, ',', '.') }})</span>
                                        </button>
                                    </form>
                                    <button type="button" @click="showTopupRejectModal = true; topupRejectId = {{ $pendingTopup->id }}; topupRejectAmount = '{{ number_format($pendingTopup->amount, 0, ',', '.') }}'" class="btn-dark text-xs py-2 px-4 border border-[#FF3C00]/40 w-full sm:w-auto inline-flex items-center gap-1.5">
                                        <i data-lucide="x" class="w-4 h-4 text-[#FF3C00]"></i>
                                        <span>Tolak Top-Up</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-purple-500/10 border border-purple-500/30 text-purple-300 text-xs font-head font-medium">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-purple-400"></i>
                                        <span>Top-Up Rp {{ number_format($pendingTopup->amount, 0, ',', '.') }} Menunggu Evaluasi Finance</span>
                                    </span>
                                @endif
                            @elseif($pendingTopup->status === 'PENDING_DIREKTUR')
                                @if(auth()->user()->isDirektur() && auth()->id() !== $pendingTopup->user_id)
                                    <form action="{{ route('top-ups.approve-direktur', $pendingTopup) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-brand text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center gap-1.5">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>Setujui Top-Up Direktur (Rp {{ number_format($pendingTopup->amount, 0, ',', '.') }})</span>
                                        </button>
                                    </form>
                                    <button type="button" @click="showTopupRejectModal = true; topupRejectId = {{ $pendingTopup->id }}; topupRejectAmount = '{{ number_format($pendingTopup->amount, 0, ',', '.') }}'" class="btn-dark text-xs py-2 px-4 border border-[#FF3C00]/40 w-full sm:w-auto inline-flex items-center gap-1.5">
                                        <i data-lucide="x" class="w-4 h-4 text-[#FF3C00]"></i>
                                        <span>Tolak Top-Up</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-head font-medium">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-400"></i>
                                        <span>Top-Up Rp {{ number_format($pendingTopup->amount, 0, ',', '.') }} Menunggu Approval Direktur</span>
                                    </span>
                                @endif
                            @elseif($pendingTopup->status === 'APPROVED')
                                @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
                                    <button type="button" @click="showTopupDisburseModal = true; topupDisburseId = {{ $pendingTopup->id }}; topupDisburseAmount = '{{ number_format($pendingTopup->amount, 0, ',', '.') }}'" class="btn-brand text-xs py-2 px-4 w-full sm:w-auto inline-flex items-center gap-1.5">
                                        <i data-lucide="wallet" class="w-4 h-4"></i>
                                        <span>Cairkan Dana Top-Up (Rp {{ number_format($pendingTopup->amount, 0, ',', '.') }})</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/30 text-sky-300 text-xs font-head font-medium">
                                        <i data-lucide="wallet" class="w-3.5 h-3.5 text-sky-400"></i>
                                        <span>Top-Up Disetujui &bull; Menunggu Pencairan Finance</span>
                                    </span>
                                @endif
                            @endif
                        @endif

                        <!-- Direktur Exception: Unlock Gembok -->
                        @if(auth()->user()->isDirektur())
                            @if(!$cashAdvance->is_unlocked)
                                <button @click="showUnlockModal = true" class="btn-dark text-xs py-2 px-4 border border-[#FF3C00]/40 w-full sm:w-auto inline-flex items-center gap-1.5">
                                    <i data-lucide="unlock" class="w-4 h-4 text-[#FF3C00]"></i>
                                    <span>Buka Gembok Pengecualian</span>
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-head font-medium">
                                    <i data-lucide="unlock" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>Gembok Dibuka (Pengecualian Direktur)</span>
                                </span>
                            @endif
                        @endif

                        <!-- Struk Pending Info for Finance / Direktur -->
                        @if(auth()->user()->canVerifyAndCloseCA($cashAdvance))
                            @php
                                $unverifiedCount = $cashAdvance->expenses->whereIn('verification_status', ['PENDING', 'NEEDS_REVISION'])->count();
                            @endphp
                            @if($unverifiedCount > 0)
                                <a href="#daftar-pengeluaran" class="btn-dark text-xs py-2 px-3.5 border border-amber-500/40 text-amber-300 hover:bg-amber-500 hover:text-slate-900 transition inline-flex items-center gap-1.5">
                                    <i data-lucide="file-check" class="w-4 h-4 text-amber-400"></i>
                                    <span>{{ $unverifiedCount }} Struk Perlu Verifikasi</span>
                                </a>
                            @endif
                        @endif
                    @endif

                    <!-- 3. NEED_SETTLEMENT -->
                    @if($cashAdvance->status === 'NEED_SETTLEMENT')
                        @if($cashAdvance->user_id === auth()->id())
                            <button @click="showRefundModal = true" class="btn-brand text-xs py-2.5 px-5 w-full sm:w-auto inline-flex items-center gap-1.5">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                <span>Upload Bukti Transfer Refund Sisa Dana</span>
                            </button>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-head font-medium">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-amber-400"></i>
                                <span>Menunggu Karyawan Mentransfer Refund Sisa Kas (Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }})</span>
                            </span>
                        @endif
                    @endif

                    <!-- 4. PENDING_VERIFICATION -->
                    @if($cashAdvance->status === 'PENDING_VERIFICATION')
                        @php
                            $unverifiedCount = $cashAdvance->expenses->whereIn('verification_status', ['PENDING', 'NEEDS_REVISION'])->count();
                        @endphp
                        @if(auth()->user()->canVerifyAndCloseCA($cashAdvance))
                            @if($unverifiedCount > 0)
                                <a href="#daftar-pengeluaran" class="btn-dark text-xs py-2.5 px-4 border border-amber-500/50 text-amber-300 hover:bg-amber-500 hover:text-slate-900 transition inline-flex items-center gap-1.5">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-400"></i>
                                    <span>Verifikasi {{ $unverifiedCount }} Struk Sebelum Tutup CA</span>
                                </a>
                            @else
                                <button @click="showCloseModal = true" class="btn-brand text-xs py-2.5 px-5 w-full sm:w-auto inline-flex items-center gap-1.5">
                                    <i data-lucide="check-check" class="w-4 h-4"></i>
                                    <span>Tutup Cash Advance (CLOSED)</span>
                                </button>
                            @endif
                        @endif
                        @if($cashAdvance->user_id === auth()->id())
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-300 text-xs font-head font-medium">
                                <i data-lucide="clock" class="w-4 h-4 text-blue-400"></i>
                                <span>Menunggu Verifikasi Akhir & Penutupan oleh Finance</span>
                            </span>
                        @elseif(!auth()->user()->canVerifyAndCloseCA($cashAdvance))
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-slate-500/10 border border-slate-500/30 text-slate-300 text-xs font-head font-medium">
                                <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                                <span>Dalam Proses Verifikasi Akhir Finance</span>
                            </span>
                        @endif
                    @endif

                    <!-- 5. CLOSED -->
                    @if($cashAdvance->status === 'CLOSED')
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs font-head font-bold">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                            <span>Cash Advance Resmi Ditutup (CLOSED) &bull; Lunas</span>
                        </span>
                    @endif

                    <!-- 6. REJECTED -->
                    @if($cashAdvance->status === 'REJECTED')
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs font-head font-bold">
                            <i data-lucide="x-circle" class="w-4 h-4 text-rose-400"></i>
                            <span>Pengajuan Ditolak: {{ $cashAdvance->rejection_reason ?: 'Tidak disetujui' }}</span>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- SETTLEMENT NOTICE BANNER IF NEED_SETTLEMENT -->
    @if($cashAdvance->status === 'NEED_SETTLEMENT')
        <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-5 sm:p-6 shadow-sm space-y-4" x-data="{ copied: false }">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-400/40 text-amber-900 flex items-center justify-center flex-shrink-0 shadow-2xs">
                    <i data-lucide="receipt-refund" class="w-6 h-6 text-amber-800"></i>
                </div>
                <div class="space-y-1.5 flex-grow">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm sm:text-base font-bold text-amber-950 uppercase tracking-wide">
                            Kewajiban Pengembalian Sisa Dana (Refund)
                        </h3>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-200 border border-amber-300 text-amber-900 text-xs font-bold uppercase tracking-wider">
                            Wajib Settlement Segera
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-amber-900 leading-relaxed">
                        Proyek telah selesai atau masa aktif telah habis dengan sisa saldo kas sebesar <strong class="font-mono text-amber-950 font-bold bg-amber-200/60 px-1.5 py-0.5 rounded">Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}</strong>. Sesuai aturan tata kelola, Anda wajib mentransfer sisa kas ke rekening kantor dan mengunggah bukti transfer dalam waktu <strong>3 hari kerja</strong>.
                    </p>
                </div>
            </div>

            <!-- Rekening Bank Kantor Card -->
            <div class="p-4 bg-white rounded-xl border border-amber-200 shadow-2xs grid grid-cols-1 sm:grid-cols-3 gap-3.5 text-xs">
                <div class="space-y-0.5">
                    <span class="text-slate-400 block text-xs font-bold uppercase tracking-wider">Bank Kantor:</span>
                    <span class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                        <i data-lucide="building-2" class="w-4 h-4 text-blue-600"></i>
                        <span>{{ \App\Models\SystemSetting::get('company_bank_name', 'Bank Central Asia (BCA)') }}</span>
                    </span>
                </div>
                <div class="space-y-0.5">
                    <span class="text-slate-400 block text-xs font-bold uppercase tracking-wider">No. Rekening:</span>
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-slate-950 text-base sm:text-lg tracking-tight select-all">
                            {{ \App\Models\SystemSetting::get('company_bank_account', '712-888-9900') }}
                        </span>
                        <button type="button" 
                            @click="navigator.clipboard.writeText('{{ preg_replace('/[^0-9]/', '', \App\Models\SystemSetting::get('company_bank_account', '7128889900')) }}'); copied = true; setTimeout(() => copied = false, 2500)"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition active:scale-95"
                            title="Salin Nomor Rekening">
                            <i data-lucide="copy" class="w-3 h-3" x-show="!copied"></i>
                            <i data-lucide="check" class="w-3 h-3 text-emerald-600" x-show="copied" x-cloak></i>
                            <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                        </button>
                    </div>
                </div>
                <div class="space-y-0.5">
                    <span class="text-slate-400 block text-xs font-bold uppercase tracking-wider">Atas Nama:</span>
                    <span class="font-bold text-slate-900 text-sm block">
                        {{ \App\Models\SystemSetting::get('company_bank_holder', 'PT TECHNODRIVES INDONESIA') }}
                    </span>
                </div>
            </div>

            <!-- Direct Upload Refund Action Button for CA Owner -->
            @if($cashAdvance->user_id === auth()->id())
                <div class="pt-3 border-t border-amber-200/80 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs text-amber-900">
                        <i data-lucide="info" class="w-4 h-4 text-amber-700 flex-shrink-0"></i>
                        <span>Sudah melakukan transfer? Klik tombol di samping untuk mengunggah bukti transfer.</span>
                    </div>
                    <button type="button" @click="showRefundModal = true" class="btn-brand text-xs sm:text-sm py-2.5 px-5 w-full sm:w-auto inline-flex items-center justify-center gap-2 shadow-md active:scale-95 transition flex-shrink-0 font-bold">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Upload Bukti Transfer Refund Sisa Dana</span>
                    </button>
                </div>
            @else
                <div class="pt-3 border-t border-amber-200/80 flex items-center gap-2 text-xs text-amber-900">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-700 flex-shrink-0"></i>
                    <span>Menunggu karyawan ({{ $cashAdvance->user->name }}) mentransfer sisa kas dan mengunggah bukti refund.</span>
                </div>
            @endif
        </div>
    @endif

    <!-- REFUND PROOF INFO IF UPLOADED -->
    @if($cashAdvance->settlement_refund_proof)
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0 border border-purple-200">
                    <i data-lucide="file-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-purple-900 uppercase">Bukti Transfer Refund Sisa Dana Telah Diunggah</h4>
                    <p class="text-xs text-purple-800">
                        Nominal Transfer: <strong class="font-mono">Rp {{ number_format($cashAdvance->settlement_refund_amount, 0, ',', '.') }}</strong> | Tanggal: {{ $cashAdvance->settlement_transferred_at ? $cashAdvance->settlement_transferred_at->format('d/m/Y') : '-' }}
                    </p>
                    @if($cashAdvance->settlement_notes)
                        <p class="text-xs text-purple-700 italic">"{{ $cashAdvance->settlement_notes }}"</p>
                    @endif
                </div>
            </div>
            <a href="{{ asset('storage/' . $cashAdvance->settlement_refund_proof) }}" target="_blank" class="btn-secondary py-1.5 px-3 text-xs">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>Lihat Bukti Refund</span>
            </a>
        </div>
    @endif

    <!-- TOP-UP HISTORY / REQUESTS SECTION -->
    @if($cashAdvance->topUps->isNotEmpty() || $cashAdvance->status === 'ACTIVE')
        <div class="card-commerce p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-retail-ink flex items-center space-x-2">
                    <i data-lucide="arrow-up-circle" class="w-4 h-4 text-retail-orange"></i>
                    <span>Riwayat Pengajuan Top-Up (Tambah Dana)</span>
                </h3>
            </div>

            @if($cashAdvance->topUps->isEmpty())
                <p class="text-xs text-slate-400 italic">Belum ada permintaan top-up dana untuk CA ini.</p>
            @else
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                    @foreach($cashAdvance->topUps as $tu)
                        <div class="p-3.5 bg-slate-50/50 hover:bg-slate-50 transition flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-slate-900 text-sm">Rp {{ number_format($tu->amount, 0, ',', '.') }}</span>
                                    <span class="badge-retail text-xs uppercase
                                        {{ $tu->status === 'DISBURSED' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : '' }}
                                        {{ $tu->status === 'APPROVED' ? 'bg-sky-50 text-sky-800 border-sky-300' : '' }}
                                        {{ $tu->status === 'PENDING_ATASAN' ? 'bg-blue-50 text-blue-800 border-blue-300' : '' }}
                                        {{ $tu->status === 'PENDING_FINANCE' ? 'bg-purple-50 text-purple-800 border-purple-300' : '' }}
                                        {{ $tu->status === 'PENDING_DIREKTUR' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                                        {{ $tu->status === 'REJECTED' ? 'bg-rose-50 text-rose-800 border-rose-300' : '' }}
                                    ">
                                        {{ $tu->status }}
                                    </span>
                                    @if($tu->requires_direktur_approval)
                                        <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 text-xs">Wajib Direktur</span>
                                    @endif
                                </div>
                                <p class="text-slate-600 leading-relaxed">{{ $tu->reason }}</p>
                                <span class="text-xs font-mono text-slate-400 block">Diajukan: {{ $tu->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            <!-- Approval & Action Buttons for Top-Up -->
                            <div class="flex items-center space-x-2 flex-wrap gap-1.5">
                                <!-- Stage 1: Atasan -->
                                @if($tu->status === 'PENDING_ATASAN' && (auth()->user()->isAtasan() || auth()->user()->isDirektur()) && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-atasan', $tu) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-brand text-xs py-1 px-3">
                                            Setujui (Atasan)
                                        </button>
                                    </form>
                                    <button type="button" @click="showTopupRejectModal = true; topupRejectId = {{ $tu->id }}; topupRejectAmount = '{{ number_format($tu->amount, 0, ',', '.') }}'" class="btn-dark text-xs py-1 px-2.5 border border-[#FF3C00]/40">
                                        Tolak
                                    </button>
                                @endif

                                <!-- Stage 2: Finance -->
                                @if($tu->status === 'PENDING_FINANCE' && (auth()->user()->isFinance() || auth()->user()->isDirektur()) && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-finance', $tu) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-brand text-xs py-1 px-3">
                                            Setujui (Finance)
                                        </button>
                                    </form>
                                    <button type="button" @click="showTopupRejectModal = true; topupRejectId = {{ $tu->id }}; topupRejectAmount = '{{ number_format($tu->amount, 0, ',', '.') }}'" class="btn-dark text-xs py-1 px-2.5 border border-[#FF3C00]/40">
                                        Tolak
                                    </button>
                                @endif

                                <!-- Stage 3: Direktur -->
                                @if($tu->status === 'PENDING_DIREKTUR' && auth()->user()->isDirektur() && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-direktur', $tu) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-brand text-xs py-1 px-3">
                                            Setujui (Direktur)
                                        </button>
                                    </form>
                                    <button type="button" @click="showTopupRejectModal = true; topupRejectId = {{ $tu->id }}; topupRejectAmount = '{{ number_format($tu->amount, 0, ',', '.') }}'" class="btn-dark text-xs py-1 px-2.5 border border-[#FF3C00]/40">
                                        Tolak
                                    </button>
                                @endif

                                <!-- Disburse Top-Up: Finance -->
                                @if($tu->status === 'APPROVED' && (auth()->user()->isFinance() || auth()->user()->isDirektur()))
                                    <button type="button" @click="showTopupDisburseModal = true; topupDisburseId = {{ $tu->id }}; topupDisburseAmount = '{{ number_format($tu->amount, 0, ',', '.') }}'" class="btn-brand text-xs py-1 px-3 whitespace-nowrap">
                                        Cairkan Dana Top-Up
                                    </button>
                                @endif

                                <!-- Soft Delete Top-Up -->
                                @if($tu->canBeSoftDeletedBy(auth()->user()))
                                    <button type="button" @click="showDeleteTopupModal = true; deleteTopupId = {{ $tu->id }}; deleteTopupAmount = '{{ number_format($tu->amount, 0, ',', '.') }}'" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg border border-rose-200 transition" title="Hapus Pengajuan Top-Up">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- EXPENSES LIST SECTION (Design.md: Card commerce, 1px border #DDDDDD) -->
    <div class="card-commerce p-5 sm:p-6 space-y-4" id="daftar-pengeluaran-struk">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-retail-ink flex items-center space-x-2 font-head">
                    <i data-lucide="receipt" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>Daftar Pengeluaran & Verifikasi Struk</span>
                </h3>
            </div>
        </div>

        <!-- Mobile Card List View (md:hidden - Tidak perlu geser horizontal) -->
        <div class="md:hidden divide-y divide-slate-100 rounded-xl border border-slate-200 overflow-hidden">
            @forelse($cashAdvance->expenses as $exp)
                <div class="p-4 space-y-3 {{ $exp->verification_status === 'VOID' ? 'opacity-40 bg-slate-50 line-through' : 'bg-white' }}">
                    <!-- Baris 1: Tanggal, Kategori & Status Verifikasi -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center space-x-1.5">
                                <span class="font-mono font-bold text-xs text-slate-900">{{ $exp->transaction_date->format('d/m/Y') }}</span>
                                @if($exp->is_late)
                                    <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 text-xs py-0.5 px-2 font-bold">
                                        TERLAMBAT
                                    </span>
                                @else
                                    <span class="text-xs text-emerald-700 font-semibold font-head">&bull; Tepat Waktu</span>
                                @endif
                            </div>
                            <span class="inline-block mt-1 text-xs font-head font-medium text-slate-700 bg-slate-100 rounded-md px-2 py-0.5 border border-slate-200">
                                {{ $exp->category_label }}
                            </span>
                        </div>

                        <!-- Status Badge -->
                        <div class="text-right flex-shrink-0">
                            <span class="badge-retail text-xs uppercase font-bold
                                {{ $exp->verification_status === 'VALID' ? 'bg-[#16A34A] text-white shadow-sm' : '' }}
                                {{ $exp->verification_status === 'PENDING' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                {{ $exp->verification_status === 'NEEDS_REVISION' ? 'bg-white border-2 border-[#FF3C00] text-[#FF3C00]' : '' }}
                                {{ $exp->verification_status === 'REJECTED' ? 'bg-[#DC2626] text-white shadow-sm' : '' }}
                                {{ $exp->verification_status === 'VOID' ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1] line-through' : '' }}
                            ">
                                {{ $exp->verification_status === 'NEEDS_REVISION' ? 'PERLU REVISI' : $exp->verification_status }}
                            </span>
                            @if($exp->verification_notes)
                                <span class="block text-xs text-slate-500 italic mt-0.5 max-w-[140px] truncate" title="{{ $exp->verification_notes }}">
                                    "{{ $exp->verification_notes }}"
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Baris 2: Keterangan & Nominal -->
                    <div class="flex items-baseline justify-between gap-3 pt-1 border-t border-slate-50">
                        <div class="min-w-0 flex-1">
                            <h4 class="font-head font-bold text-sm text-slate-900 leading-snug">{{ $exp->description }}</h4>
                            <p class="text-xs text-slate-500 flex items-center space-x-1 mt-0.5 font-body">
                                <i data-lucide="store" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span class="truncate">{{ $exp->vendor_name }}</span>
                                @if($exp->vendor_tax_number)
                                    <span class="font-mono text-slate-400">({{ $exp->vendor_tax_number }})</span>
                                @endif
                            </p>
                            @if($exp->notes)
                                <p class="text-xs text-slate-400 italic mt-0.5">"{{ $exp->notes }}"</p>
                            @endif
                            @if($exp->is_talangan)
                                <span class="inline-block text-xs font-head font-semibold text-amber-900 bg-amber-50 border border-amber-300 rounded px-2 py-0.5 mt-1">
                                    TALANGAN: {{ $exp->talangan_emergency_reason }}
                                </span>
                            @endif
                        </div>

                        <!-- Nominal -->
                        <div class="text-right flex-shrink-0">
                            <span class="font-mono font-bold text-base {{ $exp->is_talangan ? 'text-amber-800' : 'text-slate-900' }}">
                                Rp {{ number_format($exp->amount, 0, ',', '.') }}
                            </span>
                            @if($exp->is_talangan)
                                <span class="block text-xs text-amber-700 font-mono">(Talangan Pribadi)</span>
                            @endif
                        </div>
                    </div>

                    <!-- Baris 3: Struk Bukti & Tombol Aksi -->
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 flex-wrap gap-2">
                        <!-- Struk Thumbnail / Link -->
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-head font-bold uppercase text-slate-400">Bukti:</span>
                            @if($exp->is_lost_receipt)
                                <span class="badge-retail bg-amber-50 text-amber-900 border-amber-300 text-xs py-0.5 px-2">
                                    Tanpa Struk Fisik
                                </span>
                                @if($exp->lost_receipt_statement_file)
                                    <a href="{{ asset('storage/' . $exp->lost_receipt_statement_file) }}" target="_blank" class="text-xs text-[#FF3C00] hover:text-[#e03500] font-bold font-head">
                                        Pernyataan
                                    </a>
                                @endif
                                @if(!$exp->lost_receipt_approved_by && (auth()->user()->isAtasan() || auth()->user()->isDirektur()) && auth()->id() !== $exp->user_id)
                                    <form action="{{ route('expenses.approve-lost-receipt', $exp) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-sky-700 hover:text-sky-900 font-bold">
                                            Acc Pernyataan
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="flex items-center space-x-1.5">
                                    @forelse($exp->attachments as $att)
                                        <a href="{{ $att->url }}" target="_blank" class="h-9 w-9 rounded-lg border border-slate-200 overflow-hidden hover:border-[#FF3C00] transition flex items-center justify-center bg-slate-50 flex-shrink-0" title="{{ $att->file_name }}">
                                            @if(in_array(strtolower($att->file_type), ['jpg', 'jpeg', 'png', 'webp']))
                                                <img src="{{ $att->url }}" alt="Struk" class="w-full h-full object-cover">
                                            @else
                                                <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                                            @endif
                                        </a>
                                    @empty
                                        <span class="text-slate-400 text-xs font-mono">-</span>
                                    @endforelse
                                </div>
                            @endif
                        </div>

                        <!-- Aksi (Mobile Touch Targets >= 40px) -->
                        <div class="flex items-center space-x-2">
                            @if($exp->user_id === auth()->id() && in_array($exp->verification_status, ['PENDING', 'NEEDS_REVISION']) && $cashAdvance->status === 'ACTIVE')
                                <a href="{{ route('expenses.edit', $exp) }}" class="btn-secondary py-1.5 px-3 text-xs" title="Edit Transaksi">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    <span>Edit</span>
                                </a>
                            @endif

                            @if(auth()->user()->canVerifyAndCloseCA($cashAdvance) && in_array($exp->verification_status, ['PENDING', 'NEEDS_REVISION']))
                                <button @click="showVerifyModal = true; verifyExpenseId = {{ $exp->id }}; verifyExpenseDesc = {{ \Illuminate\Support\Js::from($exp->description) }}" 
                                        class="btn-cyan p-2 rounded-lg shadow-sm inline-flex items-center justify-center"
                                        title="Verifikasi Transaksi">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </button>
                            @endif

                            @if((auth()->user()->isFinance() || auth()->user()->isDirektur()) && $exp->verification_status !== 'VOID')
                                <button @click="showVoidModal = true; voidExpenseId = {{ $exp->id }}; voidExpenseDesc = {{ \Illuminate\Support\Js::from($exp->description) }}" 
                                        class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg border border-rose-200 transition" 
                                        title="VOID Transaksi">
                                    <i data-lucide="ban" class="w-4 h-4"></i>
                                </button>
                            @endif

                            @if($exp->canBeSoftDeletedBy(auth()->user()))
                                <button type="button" @click="showDeleteExpenseModal = true; deleteExpenseId = {{ $exp->id }}; deleteExpenseDesc = {{ \Illuminate\Support\Js::from($exp->description) }}"
                                        class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg border border-rose-200 transition"
                                        title="Hapus Transaksi">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-slate-400 text-xs font-body">
                    <i data-lucide="receipt" class="w-7 h-7 mx-auto text-slate-300 mb-2"></i>
                    Belum ada pengeluaran tercatat untuk Cash Advance ini.
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View (hidden md:block) -->
        <div class="hidden md:block overflow-x-auto rounded-[15px] border border-slate-200">
            <table class="w-full text-xs text-left table-technodrives">
                <thead>
                    <tr>
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3">Keterangan, Vendor & Kategori</th>
                        <th class="py-2.5 px-3 text-right">Nominal</th>
                        <th class="py-2.5 px-3 text-center">Struk</th>
                        <th class="py-2.5 px-3 text-center">Status Verifikasi</th>
                        <th class="py-2.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashAdvance->expenses as $exp)
                        <tr class="hover:bg-slate-50/70 transition {{ $exp->verification_status === 'VOID' ? 'opacity-40 bg-slate-50 line-through' : '' }}">
                            <!-- Tanggal -->
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-900 block">{{ $exp->transaction_date->format('d/m/Y') }}</span>
                                @if($exp->is_late)
                                    <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 text-xs">
                                        TERLAMBAT
                                    </span>
                                @else
                                    <span class="text-xs text-emerald-700 font-semibold">Tepat Waktu</span>
                                @endif
                            </td>

                            <!-- Keterangan, Vendor & Kategori -->
                            <td class="py-2.5 px-3 max-w-xs">
                                <div class="font-bold text-slate-800">{{ $exp->description }}</div>
                                <div class="text-xs text-slate-500 flex items-center space-x-1 mt-0.5">
                                    <i data-lucide="store" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $exp->vendor_name }}</span>
                                    @if($exp->vendor_tax_number)
                                        <span class="font-mono text-slate-400">({{ $exp->vendor_tax_number }})</span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    <span class="badge-retail bg-slate-100 text-slate-700 border-slate-300 text-xs">
                                        {{ $exp->category_label }}
                                    </span>
                                </div>
                                @if($exp->notes)
                                    <div class="text-xs text-slate-400 italic mt-0.5">"{{ $exp->notes }}"</div>
                                @endif
                                @if($exp->is_talangan)
                                    <span class="badge-retail bg-amber-50 text-amber-900 border-amber-300 text-xs mt-1">
                                        TALANGAN: {{ $exp->talangan_emergency_reason }}
                                    </span>
                                @endif
                            </td>

                            <!-- Nominal -->
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <span class="font-mono font-bold text-xs {{ $exp->is_talangan ? 'text-amber-800' : 'text-retail-ink' }}">
                                    Rp {{ number_format($exp->amount, 0, ',', '.') }}
                                </span>
                                @if($exp->is_talangan)
                                    <span class="block text-xs text-amber-700 font-semibold font-mono">(Talangan Pribadi)</span>
                                @endif
                            </td>

                            <!-- Foto Struk -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                @if($exp->is_lost_receipt)
                                    <div class="space-y-1">
                                        <span class="badge-retail bg-amber-50 text-amber-900 border-amber-300 text-xs">
                                            Tanpa Struk Fisik
                                        </span>
                                        @if($exp->lost_receipt_statement_file)
                                            <a href="{{ asset('storage/' . $exp->lost_receipt_statement_file) }}" target="_blank" class="block text-xs text-retail-cyan hover:text-cyan-800 font-semibold">
                                                Lihat Pernyataan
                                            </a>
                                        @endif
                                        @if(!$exp->lost_receipt_approved_by && (auth()->user()->isAtasan() || auth()->user()->isDirektur()))
                                            <form action="{{ route('expenses.approve-lost-receipt', $exp) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-xs text-sky-700 hover:text-sky-900 font-bold">
                                                    Acc Pernyataan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex items-center justify-center space-x-1.5">
                                        @forelse($exp->attachments as $att)
                                            <a href="{{ $att->url }}" target="_blank" class="w-8 h-8 rounded-lg border border-slate-200 overflow-hidden hover:border-retail-orange transition flex items-center justify-center bg-slate-50 flex-shrink-0" title="{{ $att->file_name }}">
                                                @if(in_array(strtolower($att->file_type), ['jpg', 'jpeg', 'png', 'webp']))
                                                    <img src="{{ $att->url }}" alt="Struk" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')">
                                                    <div class="hidden w-full h-full bg-slate-100 flex items-center justify-center text-slate-400">
                                                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                @else
                                                    <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                                                @endif
                                            </a>
                                        @empty
                                            <span class="text-slate-400 text-xs font-mono">-</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>

                            <!-- Status Verifikasi -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <span class="badge-retail text-xs uppercase font-bold
                                    {{ $exp->verification_status === 'VALID' ? 'bg-[#16A34A] text-white shadow-sm' : '' }}
                                    {{ $exp->verification_status === 'PENDING' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                    {{ $exp->verification_status === 'NEEDS_REVISION' ? 'bg-white border-2 border-[#FF3C00] text-[#FF3C00]' : '' }}
                                    {{ $exp->verification_status === 'REJECTED' ? 'bg-[#DC2626] text-white shadow-sm' : '' }}
                                    {{ $exp->verification_status === 'VOID' ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1] line-through' : '' }}
                                ">
                                    {{ $exp->verification_status === 'NEEDS_REVISION' ? 'PERLU REVISI' : $exp->verification_status }}
                                </span>
                                @if($exp->verification_notes)
                                    <span class="block text-xs text-slate-500 italic mt-0.5 max-w-[150px] truncate" title="{{ $exp->verification_notes }}">
                                        "{{ $exp->verification_notes }}"
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Edit for Karyawan before Valid/Void -->
                                    @if($exp->user_id === auth()->id() && in_array($exp->verification_status, ['PENDING', 'NEEDS_REVISION']) && $cashAdvance->status === 'ACTIVE')
                                        <a href="{{ route('expenses.edit', $exp) }}" class="p-1.5 text-retail-ink hover:text-retail-orange transition" title="Edit Transaksi">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    <!-- Verifikasi Finance / Direktur -->
                                    @if(auth()->user()->canVerifyAndCloseCA($cashAdvance) && in_array($exp->verification_status, ['PENDING', 'NEEDS_REVISION']))
                                        <button @click="showVerifyModal = true; verifyExpenseId = {{ $exp->id }}; verifyExpenseDesc = {{ \Illuminate\Support\Js::from($exp->description) }}" 
                                                class="btn-cyan p-1.5 rounded-lg shadow-sm inline-flex items-center justify-center"
                                                title="Verifikasi Transaksi">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </button>
                                    @endif

                                    <!-- Void by Finance -->
                                    @if((auth()->user()->isFinance() || auth()->user()->isDirektur()) && $exp->verification_status !== 'VOID')
                                        <button @click="showVoidModal = true; voidExpenseId = {{ $exp->id }}; voidExpenseDesc = {{ \Illuminate\Support\Js::from($exp->description) }}" class="p-1.5 text-rose-600 hover:text-rose-800 transition" title="VOID Transaksi">
                                            <i data-lucide="ban" class="w-4 h-4"></i>
                                        </button>
                                    @endif

                                    <!-- Soft Delete Expense -->
                                    @if($exp->canBeSoftDeletedBy(auth()->user()))
                                        <button type="button" @click="showDeleteExpenseModal = true; deleteExpenseId = {{ $exp->id }}; deleteExpenseDesc = {{ \Illuminate\Support\Js::from($exp->description) }}" class="p-1.5 text-rose-600 hover:text-rose-800 transition" title="Hapus Transaksi">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
                                <i data-lucide="receipt" class="w-6 h-6 mx-auto text-slate-300 mb-1.5"></i>
                                Belum ada pengeluaran tercatat untuk Cash Advance ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-head font-bold border-t border-slate-200">
                    <tr>
                        <td colspan="3" class="py-2.5 px-3 text-right uppercase text-xs text-slate-600">Total Pengeluaran Struk (Non-Void):</td>
                        <td class="py-2.5 px-3 text-right font-mono text-xs text-[#FF3C00]">
                            Rp {{ number_format($cashAdvance->expenses->where('verification_status', '!=', 'VOID')->sum('amount'), 0, ',', '.') }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- ==================== MODALS SECTION (Design.md: Rounded 6px, Border 1px #DDDDDD, Primary #FF9900) ==================== -->

    <!-- Modal 1: Approve CA (Atasan / Direktur) -->
    <div x-show="showApproveModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showApproveModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Setujui Cash Advance</h3>
            <p class="text-xs font-mono text-slate-500 mb-4">{{ $cashAdvance->ca_number }} &bull; {{ $cashAdvance->project_name }}</p>

            <form action="{{ route('cash-advances.approve', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Nominal Disetujui (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="approved_amount" value="{{ old('approved_amount', (int)$cashAdvance->requested_amount) }}" required min="1000" step="1"
                        class="w-full input-commerce font-mono font-bold text-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Catatan Persetujuan (Opsional)</label>
                    <textarea name="approval_notes" rows="3" class="w-full input-commerce" placeholder="Contoh: Disetujui sesuai rencana kerja lapangan."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showApproveModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-1.5 px-3.5">Konfirmasi Persetujuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Reject CA -->
    <div x-show="showRejectModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showRejectModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-rose-700 mb-1">Tolak Cash Advance</h3>
            <p class="text-xs text-slate-500 mb-4">Pengajuan akan dibatalkan dan gembok pemohon dibuka kembali.</p>

            <form action="{{ route('cash-advances.reject', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="rejection_reason" rows="3" required class="w-full input-commerce" placeholder="Jelaskan alasan penolakan..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showRejectModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-danger text-xs py-1.5 px-3.5">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Disburse CA (Finance) -->
    <div x-show="showDisburseModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showDisburseModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Catat Pencairan Dana</h3>
            <p class="text-xs text-slate-500 mb-4">Mencairkan dana sebesar <strong class="font-mono text-retail-ink">Rp {{ number_format($cashAdvance->approved_amount, 0, ',', '.') }}</strong> ke rekening pemohon.</p>

            <form action="{{ route('cash-advances.disburse', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Tanggal Pencairan <span class="text-rose-500">*</span></label>
                    <input type="date" name="disbursed_at" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                        class="w-full input-commerce font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">No. Referensi Transfer <span class="text-rose-500">*</span></label>
                    <input type="text" name="disbursement_ref" required placeholder="Contoh: TRF-BCA-992102"
                        class="w-full input-commerce font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Catatan Tambahan</label>
                    <textarea name="disbursement_notes" rows="2" class="w-full input-commerce"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showDisburseModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-1.5 px-3.5">Konfirmasi Pencairan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Mark Project Completed -->
    <div x-show="showCompleteModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showCompleteModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Tandai Proyek Selesai</h3>
            <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                Apakah seluruh pekerjaan lapangan telah tuntas? Sisa saldo saat ini: <strong class="font-mono">Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}</strong>.
                @if($cashAdvance->current_balance > 0)
                    Status akan beralih ke <strong>NEED_SETTLEMENT</strong> dan Anda wajib mentransfer sisa uang ke rekening kantor.
                @else
                    Status akan beralih ke <strong>PENDING_VERIFICATION</strong> untuk verifikasi akhir Finance.
                @endif
            </p>

            <form action="{{ route('cash-advances.complete-project', $cashAdvance) }}" method="POST">
                @csrf
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showCompleteModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-success text-xs py-1.5 px-3.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Ya, Selesaikan Proyek</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Submit Refund Proof (Upload Transfer Sisa Dana) -->
    <div x-show="showRefundModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showRefundModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Upload Bukti Transfer Refund</h3>
            <p class="text-xs text-slate-500 mb-4">Kembalikan sisa saldo ke rekening PT Technodrives Indonesia.</p>

            <form action="{{ route('cash-advances.submit-refund', $cashAdvance) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Nominal Yang Ditransfer (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="settlement_refund_amount" value="{{ (int)$cashAdvance->current_balance }}" required min="1" step="1"
                        class="w-full input-commerce font-mono font-bold text-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Tanggal Transfer <span class="text-rose-500">*</span></label>
                    <input type="date" name="settlement_transferred_at" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                        class="w-full input-commerce font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Bukti Transfer (JPG, PNG, PDF) <span class="text-rose-500">*</span></label>
                    <input type="file" name="settlement_refund_proof" required accept="image/*,application/pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-retail-ink hover:file:bg-slate-300 cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Catatan</label>
                    <textarea name="settlement_notes" rows="2" class="w-full input-commerce"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showRefundModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-1.5 px-3.5">Kirim Bukti Refund</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 6: Close CA (CLOSED) -->
    <div x-show="showCloseModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showCloseModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Tutup Cash Advance (CLOSED)</h3>
            <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                Tindakan ini akan mengunci seluruh data transaksi secara permanen, menyelesaikan buku saldo proyek, dan <strong>membuka gembok pengajuan</strong> bagi karyawan. Pastikan seluruh struk telah diperiksa dan disetujui.
            </p>

            <form action="{{ route('cash-advances.close', $cashAdvance) }}" method="POST">
                @csrf
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showCloseModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-1.5 px-3.5" style="background-color: #7E22CE; border-color: #6B21A8; color: #FFFFFF;">Konfirmasi Penutupan CA (CLOSED)</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 7: Direktur Override Gembok -->
    <div x-show="showUnlockModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showUnlockModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Buka Gembok Pengajuan (Pengecualian Direktur)</h3>
            <p class="text-xs text-slate-500 mb-4">Hanya Direktur yang berhak memberikan izin pengecualian. Alasan akan dicatat permanen di audit log.</p>

            <form action="{{ route('cash-advances.unlock-gembok', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Alasan Pengecualian Tertulis <span class="text-rose-500">*</span></label>
                    <textarea name="unlock_reason" rows="3" required minlength="10" class="w-full input-commerce" placeholder="Contoh: Kebutuhan darurat proyek baru PLN sebelum audit proyek sebelumnya selesai."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showUnlockModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-1.5 px-3.5" style="background-color: #D97706; border-color: #B45309; color: #FFFFFF;">Buka Gembok</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 8: Request Top-Up -->
    <div x-show="showTopupModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showTopupModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Pengajuan Top-Up Dana CA</h3>
            <p class="text-xs text-slate-500 mb-4">Tambahan dana akan dievaluasi oleh Atasan, Finance (dan Direktur bila nominal besar).</p>

            <form action="{{ route('top-ups.store', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Nominal Tambahan (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" required min="1000" step="1" class="w-full input-commerce font-mono font-bold text-slate-900" placeholder="2000000">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Alasan Kebutuhan Dana <span class="text-rose-500">*</span></label>
                    <textarea name="reason" rows="3" required minlength="10" class="w-full input-commerce" placeholder="Jelaskan rincian kebutuhan mendesak di lapangan..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showTopupModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-1.5 px-3.5">Kirim Pengajuan Top-Up</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 9: Verify Expense (Finance / Direktur) -->
    <div x-show="showVerifyModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showVerifyModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Verifikasi Struk Pengeluaran</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="verifyExpenseDesc"></p>

            <form :action="'/expenses/' + verifyExpenseId + '/verify'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Hasil Verifikasi <span class="text-rose-500">*</span></label>
                    <select name="status" x-model="verifyStatus" required class="w-full input-commerce font-bold text-xs">
                        <option value="VALID">VALID (Struk Sah & Diakui)</option>
                        <option value="NEEDS_REVISION">PERLU PERBAIKAN (Kembalikan ke Karyawan)</option>
                        <option value="REJECTED">DITOLAK (Kembalikan Saldo & Jadi Hutang Karyawan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Catatan Evaluasi</label>
                    <textarea name="verification_notes" rows="3" class="w-full input-commerce text-xs" placeholder="Catatan pemeriksa struk..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showVerifyModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-cyan text-xs py-1.5 px-3.5">Simpan Hasil Verifikasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 10: Void Expense (Finance) -->
    <div x-show="showVoidModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-retail-darkInk/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showVoidModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-rose-700 mb-1">VOID Transaksi Pengeluaran</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="voidExpenseDesc"></p>

            <form :action="'/expenses/' + voidExpenseId + '/void'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Alasan VOID Transaksi <span class="text-rose-500">*</span></label>
                    <textarea name="void_reason" rows="3" required class="w-full input-commerce" placeholder="Jelaskan alasan pembatalan transaksi..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showVoidModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-danger text-xs py-1.5 px-3.5">Konfirmasi VOID</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 11: Tolak Pengajuan Top-Up (Atasan / Finance / Direktur) -->
    <div x-show="showTopupRejectModal" x-cloak 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#050A1E]/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showTopupRejectModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-rose-700 mb-1">Tolak Pengajuan Top-Up</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="'Top-Up Sebesar ' + topupRejectAmount"></p>

            <form :action="'/top-ups/' + topupRejectId + '/reject'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-800 uppercase mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="rejection_reason" rows="3" required class="w-full input-commerce text-xs" placeholder="Tuliskan alasan penolakan top-up ini..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showTopupRejectModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-danger text-xs py-1.5 px-3.5">Konfirmasi Tolak Top-Up</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 12: Soft Delete Cash Advance -->
    <div x-show="showDeleteCaModal" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#050A1E]/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showDeleteCaModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-rose-700 mb-1">Hapus Cash Advance</h3>
            <p class="text-xs font-mono text-slate-600 mb-2">{{ $cashAdvance->ca_number }} &mdash; {{ $cashAdvance->project_name }}</p>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Data ini akan dipindahkan ke Arsip, bukan dihapus permanen. Jika diperlukan, data dapat dipulihkan dari menu Arsip.
            </p>

            <form action="{{ route('cash-advances.destroy', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div>
                    <label class="block text-xs font-bold text-slate-800 uppercase mb-1">Alasan Penghapusan <span class="text-rose-500">*</span></label>
                    <textarea name="delete_reason" rows="3" required minlength="5" class="w-full input-commerce text-xs" placeholder="Jelaskan alasan penghapusan dokumen CA ini (minimal 5 karakter)..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showDeleteCaModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-danger text-xs py-1.5 px-3.5">Konfirmasi Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 13: Soft Delete Expense -->
    <div x-show="showDeleteExpenseModal" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#050A1E]/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showDeleteExpenseModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-rose-700 mb-1">Hapus Transaksi Pengeluaran</h3>
            <p class="text-xs text-slate-600 font-semibold mb-2" x-text="deleteExpenseDesc"></p>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Data ini akan dipindahkan ke Arsip, bukan dihapus permanen, dan saldo kas akan disesuaikan otomatis. Jika diperlukan, data dapat dipulihkan dari menu Arsip.
            </p>

            <form :action="'/expenses/' + deleteExpenseId" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div>
                    <label class="block text-xs font-bold text-slate-800 uppercase mb-1">Alasan Penghapusan <span class="text-rose-500">*</span></label>
                    <textarea name="delete_reason" rows="3" required minlength="5" class="w-full input-commerce text-xs" placeholder="Jelaskan alasan penghapusan transaksi (minimal 5 karakter)..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showDeleteExpenseModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-danger text-xs py-1.5 px-3.5">Konfirmasi Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 14: Soft Delete Top-Up -->
    <div x-show="showDeleteTopupModal" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#050A1E]/75 z-[1060] flex items-center justify-center p-4">
        <div x-show="showDeleteTopupModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-rose-700 mb-1">Hapus Pengajuan Top-Up</h3>
            <p class="text-xs font-mono text-slate-600 mb-2" x-text="'Top-Up Sebesar Rp ' + deleteTopupAmount"></p>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Data ini akan dipindahkan ke Arsip, bukan dihapus permanen. Jika diperlukan, data dapat dipulihkan dari menu Arsip.
            </p>

            <form :action="'/top-ups/' + deleteTopupId" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div>
                    <label class="block text-xs font-bold text-slate-800 uppercase mb-1">Alasan Penghapusan <span class="text-rose-500">*</span></label>
                    <textarea name="delete_reason" rows="3" required minlength="5" class="w-full input-commerce text-xs" placeholder="Jelaskan alasan penghapusan pengajuan Top-Up (minimal 5 karakter)..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showDeleteTopupModal = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                    <button type="submit" class="btn-danger text-xs py-1.5 px-3.5">Konfirmasi Hapus</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection