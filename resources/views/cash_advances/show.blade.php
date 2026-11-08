@extends('layouts.app')

@section('title', $cashAdvance->ca_number . ' - ' . $cashAdvance->project_name)

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
    showVoidModal: false,
    voidExpenseId: null,
    voidExpenseDesc: '',
    showVerifyModal: false,
    verifyExpenseId: null,
    verifyExpenseDesc: '',
    verifyStatus: 'VALID',
    activeReceiptUrl: null
}">

    <!-- Top Navigation Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('cash-advances.index') }}" class="text-xs font-semibold text-slate-500 hover:text-retail-ink inline-flex items-center space-x-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar CA</span>
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.rincian-ca', ['ca_id' => $cashAdvance->id]) }}" class="btn-secondary py-1.5 px-3 text-xs">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Cetak / Laporan Rincian</span>
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
                        <span class="badge-retail bg-slate-100 text-retail-ink border-retail-surface font-mono font-bold text-xs">
                            {{ $cashAdvance->ca_number }}
                        </span>
                        
                        <!-- Status Badge -->
                        <span class="badge-retail text-xs font-bold uppercase tracking-wide
                            {{ $cashAdvance->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : '' }}
                            {{ $cashAdvance->status === 'PENDING_APPROVAL' ? 'bg-sky-50 text-sky-800 border-sky-300' : '' }}
                            {{ $cashAdvance->status === 'NEED_SETTLEMENT' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                            {{ $cashAdvance->status === 'PENDING_VERIFICATION' ? 'bg-purple-50 text-purple-800 border-purple-300' : '' }}
                            {{ $cashAdvance->status === 'CLOSED' ? 'bg-slate-100 text-slate-700 border-slate-300' : '' }}
                            {{ $cashAdvance->status === 'REJECTED' ? 'bg-rose-50 text-rose-800 border-rose-300' : '' }}
                        ">
                            Status: {{ $cashAdvance->status }}
                        </span>

                        @if($cashAdvance->is_unlocked)
                            <span class="badge-retail bg-amber-50 text-amber-900 border-amber-300 font-bold">
                                <i data-lucide="unlock" class="w-3 h-3 text-retail-orange"></i>
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
                <div class="bg-slate-50 border border-retail-surface rounded-[6px] p-4 text-center min-w-[240px]">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Masa Berlaku CA (Max 2 Bulan)</span>
                    
                    @if($cashAdvance->status === 'ACTIVE' || $cashAdvance->status === 'NEED_SETTLEMENT')
                        @if($cashAdvance->is_overdue)
                            <div class="badge-retail bg-rose-100 text-rose-800 border-rose-300 font-bold mb-2">
                                <i data-lucide="alert-octagon" class="w-3.5 h-3.5 text-rose-600"></i>
                                <span>OVERDUE</span>
                            </div>
                            <div class="text-xs text-rose-600 font-bold">Telah Melewati Batas Waktu</div>
                        @else
                            <div class="text-2xl sm:text-3xl font-mono font-bold text-retail-ink tracking-tight">
                                {{ $cashAdvance->days_remaining }} <span class="text-xs font-sans font-semibold text-slate-500">Hari</span>
                            </div>
                            <div class="text-[11px] font-mono text-slate-500 mt-1">
                                Due Date: {{ $cashAdvance->due_date ? $cashAdvance->due_date->format('d/m/Y') : '-' }}
                            </div>
                        @endif
                    @elseif($cashAdvance->status === 'CLOSED')
                        <div class="text-emerald-700 font-bold text-sm flex items-center justify-center space-x-1.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            <span>CLOSED & LUNAS</span>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 mt-1 block">Ditutup: {{ $cashAdvance->closed_at ? $cashAdvance->closed_at->format('d/m/Y') : '-' }}</span>
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
                    <!-- 1. CA Awal -->
                    <div class="bg-slate-50 p-3.5 rounded-[6px] border border-retail-surface">
                        <span class="block text-[10px] font-bold uppercase text-slate-500">CA Disetujui</span>
                        <span class="block text-sm sm:text-base font-mono font-bold text-slate-900 mt-1">
                            Rp {{ number_format($cashAdvance->approved_amount ?: $cashAdvance->requested_amount, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] font-mono text-slate-400 block mt-0.5">Ajuan: Rp {{ number_format($cashAdvance->requested_amount, 0, ',', '.') }}</span>
                    </div>

                    <!-- 2. Top-Up Cair -->
                    <div class="bg-slate-50 p-3.5 rounded-[6px] border border-retail-surface">
                        <span class="block text-[10px] font-bold uppercase text-slate-500">Top-Up Cair (+)</span>
                        <span class="block text-sm sm:text-base font-mono font-bold text-emerald-700 mt-1">
                            +Rp {{ number_format($cashAdvance->disbursed_topups_total, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] font-mono text-slate-400 block mt-0.5">{{ $cashAdvance->topUps()->where('status', 'DISBURSED')->count() }}x top-up cair</span>
                    </div>

                    <!-- 3. Pengeluaran Tercatat -->
                    <div class="bg-slate-50 p-3.5 rounded-[6px] border border-retail-surface">
                        <span class="block text-[10px] font-bold uppercase text-slate-500">Pengeluaran Tercatat (-)</span>
                        <span class="block text-sm sm:text-base font-mono font-bold text-rose-700 mt-1">
                            -Rp {{ number_format($cashAdvance->recorded_expenses_total, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] font-mono text-slate-400 block mt-0.5">Valid: Rp {{ number_format($cashAdvance->valid_expenses_total, 0, ',', '.') }}</span>
                    </div>

                    <!-- 4. Sisa Saldo Tersedia -->
                    <div class="bg-white p-3.5 rounded-[6px] border-2 border-retail-orange shadow-sm">
                        <span class="block text-[10px] font-bold uppercase text-retail-orange">Sisa Saldo Kas Aktif</span>
                        <span class="block text-base sm:text-lg font-mono font-black text-retail-ink mt-0.5">
                            Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-slate-500 block mt-0.5">Wajib Rp 0 untuk CLOSED</span>
                    </div>

                    <!-- 5. Talangan & Hasil Settlement -->
                    <div class="bg-slate-50 p-3.5 rounded-[6px] border border-retail-surface col-span-2 sm:col-span-1">
                        <span class="block text-[10px] font-bold uppercase text-slate-500">Talangan Pribadi</span>
                        <span class="block text-sm sm:text-base font-mono font-bold text-amber-800 mt-1">
                            Rp {{ number_format($cashAdvance->talangan_total, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] font-mono text-slate-600 block mt-0.5">
                            Settlement: <strong>Rp {{ number_format($cashAdvance->settlement_result, 0, ',', '.') }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Settlement Interpretation (Design.md: Clear message) -->
                <div class="mt-3 text-xs bg-slate-50 p-3 rounded-[6px] border border-retail-surface flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center space-x-2 text-slate-700">
                        <i data-lucide="info" class="w-4 h-4 text-retail-cyan flex-shrink-0"></i>
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

        <!-- WORKFLOW ACTIONS TOOLBAR -->
        <div class="bg-retail-ink px-6 py-3.5 border-t border-retail-darkInk flex flex-wrap items-center justify-between gap-3 text-white">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Aksi Tersedia:</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- 1. PENDING_APPROVAL: Atasan / Direktur -->
                @if($cashAdvance->status === 'PENDING_APPROVAL')
                    @if(auth()->user()->canApproveCA($cashAdvance))
                        <button @click="showApproveModal = true" class="btn-primary text-xs py-1.5 px-3">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Setujui Pengajuan CA</span>
                        </button>
                        <button @click="showRejectModal = true" class="btn-danger text-xs py-1.5 px-3">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>Tolak CA</span>
                        </button>
                    @endif

                    @if($cashAdvance->approved_by && auth()->user()->canDisburseCA($cashAdvance))
                        <button @click="showDisburseModal = true" class="btn-cyan text-xs py-1.5 px-3">
                            <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                            <span>Catat Pencairan Dana (Finance)</span>
                        </button>
                    @endif
                @endif

                <!-- 2. ACTIVE: Karyawan Action & Top-up -->
                @if($cashAdvance->status === 'ACTIVE')
                    @if($cashAdvance->user_id === auth()->id())
                        <a href="{{ route('expenses.create', $cashAdvance) }}" class="btn-primary text-xs py-1.5 px-3">
                            <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                            <span>Catat Pengeluaran (Foto Struk)</span>
                        </a>

                        <button @click="showTopupModal = true" class="btn-secondary text-xs py-1.5 px-3 bg-white text-retail-ink">
                            <i data-lucide="arrow-up-circle" class="w-3.5 h-3.5 text-retail-orange"></i>
                            <span>Ajukan Top-Up Dana</span>
                        </button>

                        <button @click="showCompleteModal = true" class="btn-dark text-xs py-1.5 px-3 bg-retail-darkInk border-slate-600">
                            <i data-lucide="flag" class="w-3.5 h-3.5"></i>
                            <span>Tandai Proyek Selesai</span>
                        </button>
                    @endif

                    <!-- Direktur Exception: Unlock Gembok -->
                    @if(auth()->user()->isDirektur())
                        <button @click="showUnlockModal = true" class="btn-primary text-xs py-1.5 px-3" style="background-color: #D97706; border-color: #B45309; color: #FFFFFF;">
                            <i data-lucide="unlock" class="w-3.5 h-3.5"></i>
                            <span>Buka Gembok Pengecualian</span>
                        </button>
                    @endif
                @endif

                <!-- 3. NEED_SETTLEMENT: Karyawan Upload Refund Proof -->
                @if($cashAdvance->status === 'NEED_SETTLEMENT')
                    @if($cashAdvance->user_id === auth()->id())
                        <button @click="showRefundModal = true" class="btn-primary text-xs py-1.5 px-3.5 shadow-lift">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            <span>Upload Bukti Transfer Refund Sisa Dana</span>
                        </button>
                    @endif
                @endif

                <!-- 4. PENDING_VERIFICATION: Finance / Direktur Closure -->
                @if($cashAdvance->status === 'PENDING_VERIFICATION')
                    @if(auth()->user()->canVerifyAndCloseCA($cashAdvance))
                        <button @click="showCloseModal = true" class="btn-primary text-xs py-1.5 px-4 shadow-lift" style="background-color: #7E22CE; border-color: #6B21A8; color: #FFFFFF;">
                            <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                            <span>Tutup Cash Advance (CLOSED)</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- SETTLEMENT NOTICE BANNER IF NEED_SETTLEMENT -->
    @if($cashAdvance->status === 'NEED_SETTLEMENT')
        <div class="bg-amber-50 border border-amber-300 rounded-[6px] p-5 shadow-card">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-[3px] bg-amber-200 border border-amber-300 text-amber-900 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="receipt-refund" class="w-5 h-5"></i>
                </div>
                <div class="space-y-2 flex-grow">
                    <h3 class="text-sm font-bold text-amber-950 uppercase tracking-wide">Kewajiban Pengembalian Sisa Dana (Refund)</h3>
                    <p class="text-xs text-amber-900 leading-relaxed">
                        Proyek telah selesai atau masa aktif telah habis dengan sisa saldo kas sebesar <strong class="font-mono">Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}</strong>. Sesuai aturan tata kelola, Anda wajib mentransfer sisa kas ke rekening kantor dan mengunggah bukti transfer dalam waktu <strong>3 hari kerja</strong>.
                    </p>
                    <div class="p-3 bg-white rounded-[3px] border border-amber-200 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px] font-bold uppercase">Bank Kantor:</span>
                            <span class="font-bold text-slate-800">{{ \App\Models\SystemSetting::get('company_bank_name', 'BCA') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] font-bold uppercase">No. Rekening:</span>
                            <span class="font-mono font-bold text-slate-900 text-sm">{{ \App\Models\SystemSetting::get('company_bank_account', '712-888-9900') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] font-bold uppercase">Atas Nama:</span>
                            <span class="font-bold text-slate-800">{{ \App\Models\SystemSetting::get('company_bank_holder', 'PT Technodrives Indonesia') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- REFUND PROOF INFO IF UPLOADED -->
    @if($cashAdvance->settlement_refund_proof)
        <div class="bg-purple-50 border border-purple-200 rounded-[6px] p-4 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-[3px] bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0 border border-purple-200">
                    <i data-lucide="file-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-purple-900 uppercase">Bukti Transfer Refund Sisa Dana Telah Diunggah</h4>
                    <p class="text-xs text-purple-800">
                        Nominal Transfer: <strong class="font-mono">Rp {{ number_format($cashAdvance->settlement_refund_amount, 0, ',', '.') }}</strong> | Tanggal: {{ $cashAdvance->settlement_transferred_at ? $cashAdvance->settlement_transferred_at->format('d/m/Y') : '-' }}
                    </p>
                    @if($cashAdvance->settlement_notes)
                        <p class="text-[11px] text-purple-700 italic">"{{ $cashAdvance->settlement_notes }}"</p>
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
                @if($cashAdvance->status === 'ACTIVE' && $cashAdvance->user_id === auth()->id())
                    <button @click="showTopupModal = true" class="text-xs font-bold text-retail-cyan hover:underline inline-flex items-center space-x-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Ajukan Top-Up</span>
                    </button>
                @endif
            </div>

            @if($cashAdvance->topUps->isEmpty())
                <p class="text-xs text-slate-400 italic">Belum ada permintaan top-up dana untuk CA ini.</p>
            @else
                <div class="divide-y divide-slate-100 border border-retail-surface rounded-[6px] overflow-hidden">
                    @foreach($cashAdvance->topUps as $tu)
                        <div class="p-3.5 bg-slate-50/50 hover:bg-slate-50 transition flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-slate-900 text-sm">Rp {{ number_format($tu->amount, 0, ',', '.') }}</span>
                                    <span class="badge-retail text-[10px] uppercase
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
                                        <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 text-[9px]">Wajib Direktur</span>
                                    @endif
                                </div>
                                <p class="text-slate-600 leading-relaxed">{{ $tu->reason }}</p>
                                <span class="text-[10px] font-mono text-slate-400 block">Diajukan: {{ $tu->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            <!-- Approval Buttons for Top-Up -->
                            <div class="flex items-center space-x-2 flex-wrap">
                                <!-- Stage 1: Atasan -->
                                @if($tu->status === 'PENDING_ATASAN' && (auth()->user()->isAtasan() || auth()->user()->isDirektur()) && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-atasan', $tu) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-primary text-xs py-1 px-3">
                                            Setujui (Atasan)
                                        </button>
                                    </form>
                                @endif

                                <!-- Stage 2: Finance -->
                                @if($tu->status === 'PENDING_FINANCE' && (auth()->user()->isFinance() || auth()->user()->isDirektur()) && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-finance', $tu) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-cyan text-xs py-1 px-3">
                                            Setujui (Finance)
                                        </button>
                                    </form>
                                @endif

                                <!-- Stage 3: Direktur -->
                                @if($tu->status === 'PENDING_DIREKTUR' && auth()->user()->isDirektur() && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-direktur', $tu) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-primary text-xs py-1 px-3" style="background-color: #D97706; border-color: #B45309; color: #FFFFFF;">
                                            Setujui (Direktur)
                                        </button>
                                    </form>
                                @endif

                                <!-- Disburse Top-Up: Finance -->
                                @if($tu->status === 'APPROVED' && auth()->user()->isFinance())
                                    <form action="{{ route('top-ups.disburse', $tu) }}" method="POST" class="flex items-center space-x-1.5">
                                        @csrf
                                        <input type="text" name="disbursement_ref" required placeholder="No. Ref Transfer" class="input-commerce py-1 font-mono text-xs">
                                        <button type="submit" class="btn-primary text-xs py-1 px-3 whitespace-nowrap">
                                            Cairkan Dana
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- EXPENSES LIST SECTION (Design.md: Card commerce, 1px border #DDDDDD) -->
    <div class="card-commerce overflow-hidden">
        <div class="p-5 border-b border-retail-surface flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-50">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-retail-ink flex items-center space-x-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-retail-cyan"></i>
                    <span>Daftar Pengeluaran & Verifikasi Struk</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Saldo kas langsung terpotong saat transaksi dicatat, diverifikasi oleh Finance.</p>
            </div>

            @if($cashAdvance->status === 'ACTIVE' && $cashAdvance->user_id === auth()->id())
                <a href="{{ route('expenses.create', $cashAdvance) }}" class="btn-primary text-xs py-1.5 px-3">
                    <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                    <span>+ Catat Pengeluaran Baru</span>
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-retail-surface">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Keterangan & Vendor</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-center">Struk / Bukti</th>
                        <th class="px-4 py-3 text-center">Status Verifikasi</th>
                        <th class="px-4 py-3 text-center">Aksi / Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashAdvance->expenses as $exp)
                        <tr class="hover:bg-slate-50/80 transition {{ $exp->verification_status === 'VOID' ? 'opacity-40 bg-slate-50 line-through' : '' }}">
                            <!-- Tanggal -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-900 block">{{ $exp->transaction_date->format('d/m/Y') }}</span>
                                @if($exp->is_late)
                                    <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 text-[9px]">
                                        TERLAMBAT
                                    </span>
                                @else
                                    <span class="text-[10px] text-emerald-700 font-semibold">Tepat Waktu</span>
                                @endif
                            </td>

                            <!-- Keterangan & Vendor -->
                            <td class="px-4 py-3 max-w-xs">
                                <div class="font-bold text-slate-800">{{ $exp->description }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center space-x-1 mt-0.5">
                                    <i data-lucide="store" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $exp->vendor_name }}</span>
                                    @if($exp->vendor_tax_number)
                                        <span class="font-mono text-slate-400">({{ $exp->vendor_tax_number }})</span>
                                    @endif
                                </div>
                                @if($exp->notes)
                                    <div class="text-[10px] text-slate-400 italic mt-0.5">"{{ $exp->notes }}"</div>
                                @endif
                                @if($exp->is_talangan)
                                    <span class="badge-retail bg-amber-50 text-amber-900 border-amber-300 text-[9px] mt-1">
                                        TALANGAN: {{ $exp->talangan_emergency_reason }}
                                    </span>
                                @endif
                            </td>

                            <!-- Kategori -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="badge-retail bg-slate-100 text-slate-700 border-slate-300">
                                    {{ $exp->category_label }}
                                </span>
                            </td>

                            <!-- Nominal -->
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <span class="font-mono font-bold text-sm {{ $exp->is_talangan ? 'text-amber-800' : 'text-retail-ink' }}">
                                    Rp {{ number_format($exp->amount, 0, ',', '.') }}
                                </span>
                                @if($exp->is_talangan)
                                    <span class="block text-[9px] text-amber-700 font-semibold font-mono">(Talangan Pribadi)</span>
                                @endif
                            </td>

                            <!-- Foto Struk -->
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($exp->is_lost_receipt)
                                    <div class="space-y-1">
                                        <span class="badge-retail bg-amber-50 text-amber-900 border-amber-300 text-[10px]">
                                            Tanpa Struk Fisik
                                        </span>
                                        @if($exp->lost_receipt_statement_file)
                                            <a href="{{ asset('storage/' . $exp->lost_receipt_statement_file) }}" target="_blank" class="block text-[10px] text-retail-cyan hover:underline font-semibold">
                                                Lihat Pernyataan
                                            </a>
                                        @endif
                                        @if(!$exp->lost_receipt_approved_by && (auth()->user()->isAtasan() || auth()->user()->isDirektur()))
                                            <form action="{{ route('expenses.approve-lost-receipt', $exp) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-[10px] text-sky-700 hover:underline font-bold">
                                                    Acc Pernyataan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex items-center justify-center space-x-1.5">
                                        @forelse($exp->attachments as $att)
                                            <a href="{{ $att->url }}" target="_blank" class="w-8 h-8 rounded-[3px] border border-retail-surface overflow-hidden hover:border-retail-orange transition flex items-center justify-center bg-slate-50" title="{{ $att->file_name }}">
                                                @if(in_array(strtolower($att->file_type), ['jpg', 'jpeg', 'png', 'webp']))
                                                    <img src="{{ $att->url }}" alt="Struk" class="w-full h-full object-cover">
                                                @else
                                                    <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                                                @endif
                                            </a>
                                        @empty
                                            <span class="text-slate-400 text-[10px] font-mono">-</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>

                            <!-- Status Verifikasi -->
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="badge-retail text-[10px] uppercase font-bold
                                    {{ $exp->verification_status === 'VALID' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : '' }}
                                    {{ $exp->verification_status === 'PENDING' ? 'bg-sky-50 text-sky-800 border-sky-300' : '' }}
                                    {{ $exp->verification_status === 'NEEDS_REVISION' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                                    {{ $exp->verification_status === 'REJECTED' ? 'bg-rose-50 text-rose-800 border-rose-300' : '' }}
                                    {{ $exp->verification_status === 'VOID' ? 'bg-slate-100 text-slate-600 border-slate-300 line-through' : '' }}
                                ">
                                    {{ $exp->verification_status === 'NEEDS_REVISION' ? 'PERLU REVISI' : $exp->verification_status }}
                                </span>
                                @if($exp->verification_notes)
                                    <span class="block text-[10px] text-slate-500 italic mt-0.5 max-w-[150px] truncate" title="{{ $exp->verification_notes }}">
                                        "{{ $exp->verification_notes }}"
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Edit for Karyawan before Valid/Void -->
                                    @if($exp->user_id === auth()->id() && in_array($exp->verification_status, ['PENDING', 'NEEDS_REVISION']))
                                        <a href="{{ route('expenses.edit', $exp) }}" class="p-1.5 text-retail-ink hover:text-retail-orange transition" title="Edit Transaksi">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    <!-- Verifikasi Finance / Direktur -->
                                    @if(auth()->user()->canVerifyAndCloseCA($cashAdvance) && $exp->verification_status !== 'VOID')
                                        <button @click="showVerifyModal = true; verifyExpenseId = {{ $exp->id }}; verifyExpenseDesc = '{{ addslashes($exp->description) }}'" class="btn-cyan py-1 px-2 text-[10px]">
                                            Verifikasi
                                        </button>
                                    @endif

                                    <!-- Void by Finance -->
                                    @if((auth()->user()->isFinance() || auth()->user()->isDirektur()) && $exp->verification_status !== 'VOID')
                                        <button @click="showVoidModal = true; voidExpenseId = {{ $exp->id }}; voidExpenseDesc = '{{ addslashes($exp->description) }}'" class="p-1.5 text-rose-600 hover:text-rose-800 transition" title="VOID Transaksi">
                                            <i data-lucide="ban" class="w-4 h-4"></i>
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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
                    <input type="number" name="approved_amount" value="{{ old('approved_amount', (int)$cashAdvance->requested_amount) }}" required min="1000" step="any"
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
        <div x-show="showDisburseModal"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="card-commerce max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-retail-ink mb-1">Catat Pencairan Dana (Finance)</h3>
            <p class="text-xs text-slate-500 mb-4">Mencairkan dana sebesar <strong class="font-mono text-retail-ink">Rp {{ number_format($cashAdvance->approved_amount, 0, ',', '.') }}</strong> ke rekening pemohon.</p>

            <form action="{{ route('cash-advances.disburse', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Tanggal Pencairan <span class="text-rose-500">*</span></label>
                    <input type="date" name="disbursed_at" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                        class="w-full input-commerce font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">No. Ref Transfer / Voucher Bank <span class="text-rose-500">*</span></label>
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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
                    <button type="submit" class="btn-primary text-xs py-1.5 px-3.5">Ya, Selesaikan Proyek</button>
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Foto / File Bukti Transfer (JPG, PNG, PDF) <span class="text-rose-500">*</span></label>
                    <input type="file" name="settlement_refund_proof" required accept="image/*,application/pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-[3px] file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-retail-ink hover:file:bg-slate-300">
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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
                    <input type="number" name="amount" required min="1000" step="any" class="w-full input-commerce font-mono font-bold text-slate-900" placeholder="2000000">
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">Catatan / Alasan Evaluasi</label>
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
        class="fixed inset-0 bg-retail-darkInk/75 z-50 flex items-center justify-center p-4">
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

</div>
@endsection