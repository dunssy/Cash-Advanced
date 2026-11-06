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
        <a href="{{ route('cash-advances.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 inline-flex items-center space-x-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar CA</span>
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.rincian-ca', ['ca_id' => $cashAdvance->id]) }}" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition inline-flex items-center space-x-1.5">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Cetak / Laporan Rincian</span>
            </a>
        </div>
    </div>

    <!-- MAIN HEADER CARD -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <!-- CA Information -->
                <div class="space-y-3 max-w-2xl">
                    <div class="flex items-center space-x-3 flex-wrap gap-2">
                        <span class="text-xs font-extrabold text-sky-700 bg-sky-50 border border-sky-200 px-3 py-1 rounded-full">
                            {{ $cashAdvance->ca_number }}
                        </span>
                        
                        <!-- Status Badge -->
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wide
                            {{ $cashAdvance->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : '' }}
                            {{ $cashAdvance->status === 'PENDING_APPROVAL' ? 'bg-blue-100 text-blue-800 border border-blue-300' : '' }}
                            {{ $cashAdvance->status === 'NEED_SETTLEMENT' ? 'bg-amber-100 text-amber-900 border border-amber-300' : '' }}
                            {{ $cashAdvance->status === 'PENDING_VERIFICATION' ? 'bg-purple-100 text-purple-900 border border-purple-300' : '' }}
                            {{ $cashAdvance->status === 'CLOSED' ? 'bg-slate-200 text-slate-800 border border-slate-300' : '' }}
                            {{ $cashAdvance->status === 'REJECTED' ? 'bg-rose-100 text-rose-800 border border-rose-300' : '' }}
                        ">
                            Status: {{ $cashAdvance->status }}
                        </span>

                        @if($cashAdvance->is_unlocked)
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center space-x-1">
                                <i data-lucide="unlock" class="w-3.5 h-3.5"></i>
                                <span>Gembok Di-override Direktur</span>
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        {{ $cashAdvance->project_name }}
                    </h1>
                    @if($cashAdvance->project_code)
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                            Kode Proyek: {{ $cashAdvance->project_code }}
                        </span>
                    @endif
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pt-1">
                        {{ $cashAdvance->purpose }}
                    </p>

                    <!-- User metadata -->
                    <div class="pt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500 border-t border-slate-100">
                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span class="font-semibold text-slate-800">{{ $cashAdvance->user->name }}</span>
                            <span>({{ $cashAdvance->user->department ?: 'Staff' }})</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Diajukan: {{ $cashAdvance->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        @if($cashAdvance->disbursed_at)
                            <div class="flex items-center space-x-1.5">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>Cair: {{ $cashAdvance->disbursed_at->format('d M Y') }} (Ref: {{ $cashAdvance->disbursement_ref }})</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Countdown & Overdue Widget -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-center min-w-[240px]">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Masa Berlaku CA (Max 2 Bulan)</span>
                    
                    @if($cashAdvance->status === 'ACTIVE' || $cashAdvance->status === 'NEED_SETTLEMENT')
                        @if($cashAdvance->is_overdue)
                            <div class="inline-flex items-center space-x-1.5 px-3 py-1 bg-rose-100 text-rose-700 rounded-full font-black text-xs animate-bounce mb-2">
                                <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                                <span>OVERDUE</span>
                            </div>
                            <div class="text-xs text-rose-600 font-bold">Telah Melewati Batas Waktu</div>
                        @else
                            <div class="text-3xl font-black text-slate-900 tracking-tight">
                                {{ $cashAdvance->days_remaining }} <span class="text-sm font-semibold text-slate-500">Hari</span>
                            </div>
                            <div class="text-[11px] font-medium text-slate-500 mt-1">
                                Jatuh Tempo: {{ $cashAdvance->due_date ? $cashAdvance->due_date->format('d M Y') : '-' }}
                            </div>
                        @endif
                    @elseif($cashAdvance->status === 'CLOSED')
                        <div class="text-emerald-600 font-bold text-base flex items-center justify-center space-x-1.5">
                            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            <span>CLOSED & LUNAS</span>
                        </div>
                        <span class="text-[11px] text-slate-400 mt-1 block">Ditutup pada: {{ $cashAdvance->closed_at ? $cashAdvance->closed_at->format('d M Y') : '-' }}</span>
                    @elseif($cashAdvance->status === 'REJECTED')
                        <div class="text-rose-600 font-bold text-base">DITOLAK</div>
                    @else
                        <div class="text-xs text-slate-500 italic">Hitung mundur berjalan setelah dana dicairkan Finance</div>
                    @endif
                </div>
            </div>

            <!-- FINANCIAL LEDGER / BUKU SALDO (Prinsip 2 & 3: Saldo dihitung sistem) -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center space-x-1.5">
                        <i data-lucide="calculator" class="w-4 h-4 text-slate-400"></i>
                        <span>Buku Saldo Proyek (Dihitung Otomatis Oleh Sistem)</span>
                    </h3>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <!-- 1. CA Awal -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="block text-[11px] font-medium text-slate-500">CA Disetujui</span>
                        <span class="block text-base font-extrabold text-slate-900 mt-1">
                            Rp {{ number_format($cashAdvance->approved_amount ?: $cashAdvance->requested_amount, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Pengajuan: Rp {{ number_format($cashAdvance->requested_amount, 0, ',', '.') }}</span>
                    </div>

                    <!-- 2. Top-Up Cair -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="block text-[11px] font-medium text-slate-500">Top-Up Cair (+)</span>
                        <span class="block text-base font-extrabold text-emerald-600 mt-1">
                            +Rp {{ number_format($cashAdvance->disbursed_topups_total, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $cashAdvance->topUps()->where('status', 'DISBURSED')->count() }}x top-up disetujui</span>
                    </div>

                    <!-- 3. Pengeluaran Tercatat -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="block text-[11px] font-medium text-slate-500">Pengeluaran Tercatat (-)</span>
                        <span class="block text-base font-extrabold text-rose-600 mt-1">
                            -Rp {{ number_format($cashAdvance->recorded_expenses_total, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-slate-400 block mt-0.5">Valid: Rp {{ number_format($cashAdvance->valid_expenses_total, 0, ',', '.') }}</span>
                    </div>

                    <!-- 4. Sisa Saldo Tersedia -->
                    <div class="bg-sky-50 p-4 rounded-xl border-2 border-sky-300">
                        <span class="block text-[11px] font-bold text-sky-800">Sisa Saldo Kas Aktif</span>
                        <span class="block text-lg font-black text-sky-700 mt-0.5">
                            Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] font-semibold text-sky-600 block mt-0.5">Wajib Rp 0 untuk CLOSED</span>
                    </div>

                    <!-- 5. Talangan & Hasil Settlement -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 col-span-2 sm:col-span-1">
                        <span class="block text-[11px] font-medium text-slate-500">Talangan Pribadi</span>
                        <span class="block text-base font-extrabold text-amber-700 mt-1">
                            Rp {{ number_format($cashAdvance->talangan_total, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-slate-500 block mt-0.5">
                            Settlement: <strong>Rp {{ number_format($cashAdvance->settlement_result, 0, ',', '.') }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Settlement Interpretation -->
                <div class="mt-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center space-x-2 text-slate-600">
                        <i data-lucide="info" class="w-4 h-4 text-sky-500 flex-shrink-0"></i>
                        <span>
                            <strong>Kondisi Settlement:</strong>
                            @if($cashAdvance->settlement_result > 0)
                                Saldo tersisa Rp {{ number_format($cashAdvance->settlement_result, 0, ',', '.') }}. Karyawan <strong>mentransfer sisa ke rekening kantor</strong> saat proyek selesai.
                            @elseif($cashAdvance->settlement_result < 0)
                                Talangan darurat melebihi sisa kas. Perusahaan <strong>mereimburse kekurangan Rp {{ number_format(abs($cashAdvance->settlement_result), 0, ',', '.') }} ke karyawan</strong>.
                            @else
                                Saldo lunas pas (Rp 0). Langsung ke verifikasi akhir Finance.
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- WORKFLOW ACTIONS TOOLBAR -->
        <div class="bg-slate-100 px-6 py-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi Tersedia:</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- 1. PENDING_APPROVAL: Atasan / Direktur -->
                @if($cashAdvance->status === 'PENDING_APPROVAL')
                    @if(auth()->user()->canApproveCA($cashAdvance))
                        <button @click="showApproveModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center space-x-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Setujui Pengajuan CA</span>
                        </button>
                        <button @click="showRejectModal = true" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center space-x-1.5">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span>Tolak CA</span>
                        </button>
                    @endif

                    @if($cashAdvance->approved_by && auth()->user()->canDisburseCA($cashAdvance))
                        <button @click="showDisburseModal = true" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center space-x-1.5">
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                            <span>Catat Pencairan Dana (Finance)</span>
                        </button>
                    @endif
                @endif

                <!-- 2. ACTIVE: Karyawan Action & Top-up -->
                @if($cashAdvance->status === 'ACTIVE')
                    @if($cashAdvance->user_id === auth()->id())
                        <a href="{{ route('expenses.create', $cashAdvance) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center space-x-1.5">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Catat Pengeluaran (Foto Struk)</span>
                        </a>

                        <button @click="showTopupModal = true" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow transition flex items-center space-x-1.5">
                            <i data-lucide="arrow-up-circle" class="w-4 h-4"></i>
                            <span>Ajukan Top-Up Dana</span>
                        </button>

                        <button @click="showCompleteModal = true" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow transition flex items-center space-x-1.5">
                            <i data-lucide="flag" class="w-4 h-4"></i>
                            <span>Tandai Proyek Selesai</span>
                        </button>
                    @endif

                    <!-- Direktur Exception: Unlock Gembok -->
                    @if(auth()->user()->isDirektur())
                        <button @click="showUnlockModal = true" class="px-3.5 py-2 bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-bold rounded-xl border border-amber-300 transition flex items-center space-x-1.5">
                            <i data-lucide="unlock" class="w-4 h-4"></i>
                            <span>Buka Gembok Pengecualian</span>
                        </button>
                    @endif
                @endif

                <!-- 3. NEED_SETTLEMENT: Karyawan Upload Refund Proof -->
                @if($cashAdvance->status === 'NEED_SETTLEMENT')
                    @if($cashAdvance->user_id === auth()->id())
                        <button @click="showRefundModal = true" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center space-x-1.5 animate-pulse">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            <span>Upload Bukti Transfer Refund Sisa Dana</span>
                        </button>
                    @endif
                @endif

                <!-- 4. PENDING_VERIFICATION: Finance / Direktur Closure -->
                @if($cashAdvance->status === 'PENDING_VERIFICATION')
                    @if(auth()->user()->canVerifyAndCloseCA($cashAdvance))
                        <button @click="showCloseModal = true" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold rounded-xl shadow-lg transition flex items-center space-x-1.5">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                            <span>Tutup Cash Advance (CLOSED)</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- SETTLEMENT NOTICE BANNER IF NEED_SETTLEMENT -->
    @if($cashAdvance->status === 'NEED_SETTLEMENT')
        <div class="bg-amber-50 border-2 border-amber-300 rounded-3xl p-6 shadow-sm">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="receipt-refund" class="w-6 h-6"></i>
                </div>
                <div class="space-y-2 flex-grow">
                    <h3 class="text-base font-extrabold text-amber-900">Kewajiban Pengembalian Sisa Dana (Refund)</h3>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        Proyek telah selesai atau durasi telah berakhir dengan sisa saldo sebesar <strong>Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}</strong>. Sesuai aturan perusahaan, Anda wajib mentransfer sisa dana ke rekening kantor dan mengunggah bukti transfer dalam waktu <strong>3 hari kerja</strong>.
                    </p>
                    <div class="p-4 bg-white rounded-xl border border-amber-200 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block">Bank Rekening Kantor:</span>
                            <span class="font-bold text-slate-800">{{ \App\Models\SystemSetting::get('company_bank_name', 'BCA') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Nomor Rekening:</span>
                            <span class="font-mono font-bold text-slate-900 text-sm">{{ \App\Models\SystemSetting::get('company_bank_account', '712-888-9900') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Atas Nama:</span>
                            <span class="font-bold text-slate-800">{{ \App\Models\SystemSetting::get('company_bank_holder', 'PT Technodrives Indonesia') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- REFUND PROOF INFO IF UPLOADED -->
    @if($cashAdvance->settlement_refund_proof)
        <div class="bg-purple-50 border border-purple-200 rounded-2xl p-5 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="file-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-purple-900 uppercase">Bukti Transfer Refund Sisa Dana Telah Diunggah</h4>
                    <p class="text-xs text-purple-800">
                        Nominal Transfer: <strong>Rp {{ number_format($cashAdvance->settlement_refund_amount, 0, ',', '.') }}</strong> | Tanggal: {{ $cashAdvance->settlement_transferred_at ? $cashAdvance->settlement_transferred_at->format('d M Y') : '-' }}
                    </p>
                    @if($cashAdvance->settlement_notes)
                        <p class="text-[11px] text-purple-700 italic">"{{ $cashAdvance->settlement_notes }}"</p>
                    @endif
                </div>
            </div>
            <a href="{{ asset('storage/' . $cashAdvance->settlement_refund_proof) }}" target="_blank" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center space-x-1.5">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>Lihat Bukti Transfer Refund</span>
            </a>
        </div>
    @endif

    <!-- TOP-UP HISTORY / REQUESTS SECTION -->
    @if($cashAdvance->topUps->isNotEmpty() || $cashAdvance->status === 'ACTIVE')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                    <i data-lucide="arrow-up-circle" class="w-4 h-4 text-amber-500"></i>
                    <span>Riwayat Pengajuan Top-Up (Tambah Dana)</span>
                </h3>
                @if($cashAdvance->status === 'ACTIVE' && $cashAdvance->user_id === auth()->id())
                    <button @click="showTopupModal = true" class="text-xs font-bold text-amber-600 hover:text-amber-800 inline-flex items-center space-x-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Ajukan Top-Up</span>
                    </button>
                @endif
            </div>

            @if($cashAdvance->topUps->isEmpty())
                <p class="text-xs text-slate-400 italic">Belum ada permintaan top-up dana untuk CA ini.</p>
            @else
                <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                    @foreach($cashAdvance->topUps as $tu)
                        <div class="p-4 bg-slate-50/50 hover:bg-slate-50 transition flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-extrabold text-slate-900 text-sm">Rp {{ number_format($tu->amount, 0, ',', '.') }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $tu->status === 'DISBURSED' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ $tu->status === 'APPROVED' ? 'bg-sky-100 text-sky-800' : '' }}
                                        {{ $tu->status === 'PENDING_ATASAN' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $tu->status === 'PENDING_FINANCE' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $tu->status === 'PENDING_DIREKTUR' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $tu->status === 'REJECTED' ? 'bg-rose-100 text-rose-800' : '' }}
                                    ">
                                        {{ $tu->status }}
                                    </span>
                                    @if($tu->requires_direktur_approval)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700">Wajib Direktur</span>
                                    @endif
                                </div>
                                <p class="text-slate-600 leading-relaxed">{{ $tu->reason }}</p>
                                <span class="text-[10px] text-slate-400 block">Diajukan: {{ $tu->created_at->format('d M Y, H:i') }}</span>
                            </div>

                            <!-- Approval Buttons for Top-Up -->
                            <div class="flex items-center space-x-2 flex-wrap">
                                <!-- Stage 1: Atasan -->
                                @if($tu->status === 'PENDING_ATASAN' && (auth()->user()->isAtasan() || auth()->user()->isDirektur()) && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-atasan', $tu) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs">
                                            Setujui (Atasan)
                                        </button>
                                    </form>
                                @endif

                                <!-- Stage 2: Finance -->
                                @if($tu->status === 'PENDING_FINANCE' && (auth()->user()->isFinance() || auth()->user()->isDirektur()) && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-finance', $tu) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg text-xs">
                                            Setujui (Finance)
                                        </button>
                                    </form>
                                @endif

                                <!-- Stage 3: Direktur -->
                                @if($tu->status === 'PENDING_DIREKTUR' && auth()->user()->isDirektur() && auth()->id() !== $tu->user_id)
                                    <form action="{{ route('top-ups.approve-direktur', $tu) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs">
                                            Setujui (Direktur)
                                        </button>
                                    </form>
                                @endif

                                <!-- Disburse Top-Up: Finance -->
                                @if($tu->status === 'APPROVED' && auth()->user()->isFinance())
                                    <form action="{{ route('top-ups.disburse', $tu) }}" method="POST" class="flex items-center space-x-1">
                                        @csrf
                                        <input type="text" name="disbursement_ref" required placeholder="No. Ref Transfer Bank" class="px-2 py-1 text-xs border border-slate-300 rounded-lg">
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs whitespace-nowrap">
                                            Cairkan Dana Top-Up
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

    <!-- EXPENSES LIST SECTION (CATATAN PENGELUARAN DENGAN FOTO STRUK DARI HP) -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                    <i data-lucide="receipt" class="w-5 h-5 text-sky-600"></i>
                    <span>Daftar Pengeluaran & Verifikasi Struk</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Saldo langsung berkurang saat transaksi dicatat, diverifikasi oleh Finance.</p>
            </div>

            @if($cashAdvance->status === 'ACTIVE' && $cashAdvance->user_id === auth()->id())
                <a href="{{ route('expenses.create', $cashAdvance) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center space-x-1.5">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                    <span>+ Catat Pengeluaran Baru</span>
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Keterangan & Vendor</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5 text-right">Nominal</th>
                        <th class="px-4 py-3.5 text-center">Struk / Bukti</th>
                        <th class="px-4 py-3.5 text-center">Status Verifikasi</th>
                        <th class="px-4 py-3.5 text-center">Aksi / Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashAdvance->expenses as $exp)
                        <tr class="hover:bg-slate-50/70 transition {{ $exp->verification_status === 'VOID' ? 'opacity-50 bg-slate-50' : '' }}">
                            <!-- Tanggal -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $exp->transaction_date->format('d/m/Y') }}</span>
                                @if($exp->is_late)
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-100 text-rose-700 tracking-wider">
                                        TERLAMBAT
                                    </span>
                                @else
                                    <span class="text-[10px] text-emerald-600 font-medium">Tepat Waktu</span>
                                @endif
                            </td>

                            <!-- Keterangan & Vendor -->
                            <td class="px-4 py-3.5 max-w-xs">
                                <div class="font-bold text-slate-800">{{ $exp->description }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center space-x-1 mt-0.5">
                                    <i data-lucide="store" class="w-3 h-3 text-slate-400"></i>
                                    <span>{{ $exp->vendor_name }}</span>
                                    @if($exp->vendor_tax_number)
                                        <span class="text-slate-400">({{ $exp->vendor_tax_number }})</span>
                                    @endif
                                </div>
                                @if($exp->notes)
                                    <div class="text-[10px] text-slate-400 italic mt-0.5">"{{ $exp->notes }}"</div>
                                @endif
                                @if($exp->is_talangan)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 mt-1">
                                        TALANGAN DARURAT: {{ $exp->talangan_emergency_reason }}
                                    </span>
                                @endif
                            </td>

                            <!-- Kategori -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-1 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $exp->category_label }}
                                </span>
                            </td>

                            <!-- Nominal -->
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <span class="font-extrabold text-sm {{ $exp->is_talangan ? 'text-amber-700' : 'text-slate-900' }}">
                                    Rp {{ number_format($exp->amount, 0, ',', '.') }}
                                </span>
                                @if($exp->is_talangan)
                                    <span class="block text-[9px] text-amber-600 font-semibold">(Talangan Pribadi)</span>
                                @endif
                            </td>

                            <!-- Foto Struk -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($exp->is_lost_receipt)
                                    <div class="space-y-1">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Tanpa Struk Fisik
                                        </span>
                                        @if($exp->lost_receipt_statement_file)
                                            <a href="{{ asset('storage/' . $exp->lost_receipt_statement_file) }}" target="_blank" class="block text-[10px] text-sky-600 hover:underline">
                                                Lihat Pernyataan
                                            </a>
                                        @endif
                                        @if(!$exp->lost_receipt_approved_by && (auth()->user()->isAtasan() || auth()->user()->isDirektur()))
                                            <form action="{{ route('expenses.approve-lost-receipt', $exp) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-[10px] text-blue-600 hover:underline font-bold">
                                                    Acc Pernyataan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex items-center justify-center space-x-1">
                                        @forelse($exp->attachments as $att)
                                            <a href="{{ $att->url }}" target="_blank" class="w-8 h-8 rounded-lg border border-slate-200 overflow-hidden hover:scale-105 transition flex items-center justify-center bg-slate-50" title="{{ $att->file_name }}">
                                                @if(in_array(strtolower($att->file_type), ['jpg', 'jpeg', 'png', 'webp']))
                                                    <img src="{{ $att->url }}" alt="Struk" class="w-full h-full object-cover">
                                                @else
                                                    <i data-lucide="file-text" class="w-4 h-4 text-slate-500"></i>
                                                @endif
                                            </a>
                                        @empty
                                            <span class="text-slate-400 text-[10px]">-</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>

                            <!-- Status Verifikasi -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                    {{ $exp->verification_status === 'VALID' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $exp->verification_status === 'PENDING' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $exp->verification_status === 'NEEDS_REVISION' ? 'bg-amber-100 text-amber-800 animate-pulse' : '' }}
                                    {{ $exp->verification_status === 'REJECTED' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ $exp->verification_status === 'VOID' ? 'bg-slate-200 text-slate-700 line-through' : '' }}
                                ">
                                    {{ $exp->verification_status === 'NEEDS_REVISION' ? 'PERLU PERBAIKAN' : $exp->verification_status }}
                                </span>
                                @if($exp->verification_notes)
                                    <span class="block text-[10px] text-slate-500 italic mt-0.5 max-w-[150px] truncate" title="{{ $exp->verification_notes }}">
                                        "{{ $exp->verification_notes }}"
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Edit for Karyawan before Valid/Void -->
                                    @if($exp->user_id === auth()->id() && in_array($exp->verification_status, ['PENDING', 'NEEDS_REVISION']))
                                        <a href="{{ route('expenses.edit', $exp) }}" class="p-1.5 text-sky-600 hover:bg-sky-50 rounded-lg transition" title="Edit Transaksi">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                    @endif

                                    <!-- Verifikasi Finance / Direktur -->
                                    @if(auth()->user()->canVerifyAndCloseCA($cashAdvance) && $exp->verification_status !== 'VOID')
                                        <button @click="showVerifyModal = true; verifyExpenseId = {{ $exp->id }}; verifyExpenseDesc = '{{ addslashes($exp->description) }}'" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg text-[11px] shadow-sm transition">
                                            Verifikasi
                                        </button>
                                    @endif

                                    <!-- Void by Finance -->
                                    @if((auth()->user()->isFinance() || auth()->user()->isDirektur()) && $exp->verification_status !== 'VOID')
                                        <button @click="showVoidModal = true; voidExpenseId = {{ $exp->id }}; voidExpenseDesc = '{{ addslashes($exp->description) }}'" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="VOID Transaksi">
                                            <i data-lucide="ban" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="receipt" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Belum ada pengeluaran tercatat untuk Cash Advance ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== MODALS SECTION ==================== -->

    <!-- Modal 1: Approve CA (Atasan / Direktur) -->
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-1">Setujui Cash Advance</h3>
            <p class="text-xs text-slate-500 mb-4">{{ $cashAdvance->ca_number }} - {{ $cashAdvance->project_name }}</p>

            <form action="{{ route('cash-advances.approve', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Disetujui (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="approved_amount" value="{{ old('approved_amount', (int)$cashAdvance->requested_amount) }}" required min="1000" step="1000"
                        class="w-full px-3 py-2 text-sm font-bold bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Persetujuan (Opsional)</label>
                    <textarea name="approval_notes" rows="3" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500" placeholder="Contoh: Disetujui sesuai rencana kerja lapangan."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showApproveModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow">Konfirmasi Persetujuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Reject CA -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-rose-700 mb-1">Tolak Cash Advance</h3>
            <p class="text-xs text-slate-500 mb-4">Pengajuan akan dibatalkan dan gembok pemohon akan terbuka kembali.</p>

            <form action="{{ route('cash-advances.reject', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="rejection_reason" rows="3" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500" placeholder="Jelaskan alasan penolakan..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Disburse CA (Finance) -->
    <div x-show="showDisburseModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-1">Catat Pencairan Dana (Finance)</h3>
            <p class="text-xs text-slate-500 mb-4">Mencairkan dana sebesar <strong>Rp {{ number_format($cashAdvance->approved_amount, 0, ',', '.') }}</strong> ke rekening pemohon.</p>

            <form action="{{ route('cash-advances.disburse', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Pencairan <span class="text-rose-500">*</span></label>
                    <input type="date" name="disbursed_at" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Ref Transfer / Voucher Bank <span class="text-rose-500">*</span></label>
                    <input type="text" name="disbursement_ref" required placeholder="Contoh: TRF-BCA-992102"
                        class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                    <textarea name="disbursement_notes" rows="2" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showDisburseModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow">Konfirmasi Pencairan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Mark Project Completed -->
    <div x-show="showCompleteModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-1">Tandai Proyek Selesai</h3>
            <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                Apakah seluruh pekerjaan lapangan telah tuntas? Sisa saldo saat ini: <strong>Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}</strong>.
                @if($cashAdvance->current_balance > 0)
                    Status akan beralih ke <strong>NEED_SETTLEMENT</strong> dan Anda wajib mentransfer sisa uang ke rekening kantor.
                @else
                    Status akan beralih ke <strong>PENDING_VERIFICATION</strong> untuk verifikasi akhir Finance.
                @endif
            </p>

            <form action="{{ route('cash-advances.complete-project', $cashAdvance) }}" method="POST">
                @csrf
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showCompleteModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow">Ya, Selesaikan Proyek</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Submit Refund Proof (Upload Transfer Sisa Dana) -->
    <div x-show="showRefundModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-1">Upload Bukti Transfer Refund</h3>
            <p class="text-xs text-slate-500 mb-4">Kembalikan sisa saldo ke rekening PT Technodrives.</p>

            <form action="{{ route('cash-advances.submit-refund', $cashAdvance) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Yang Ditransfer (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="settlement_refund_amount" value="{{ (int)$cashAdvance->current_balance }}" required min="1" step="1"
                        class="w-full px-3 py-2 text-sm font-bold bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Transfer <span class="text-rose-500">*</span></label>
                    <input type="date" name="settlement_transferred_at" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Foto / File Bukti Transfer (JPG, PNG, PDF) <span class="text-rose-500">*</span></label>
                    <input type="file" name="settlement_refund_proof" required accept="image/*,application/pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan</label>
                    <textarea name="settlement_notes" rows="2" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showRefundModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow">Kirim Bukti Refund</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 6: Close CA (CLOSED) -->
    <div x-show="showCloseModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-purple-900 mb-1">Tutup Cash Advance (CLOSED)</h3>
            <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                Tindakan ini akan mengunci seluruh data transaksi secara permanen, menyelesaikan buku saldo proyek, dan <strong>membuka gembok pengajuan</strong> bagi karyawan. Pastikan seluruh struk telah diperiksa dan disetujui.
            </p>

            <form action="{{ route('cash-advances.close', $cashAdvance) }}" method="POST">
                @csrf
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showCloseModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs rounded-xl shadow">Konfirmasi Penutupan CA (CLOSED)</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 7: Direktur Override Gembok -->
    <div x-show="showUnlockModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-amber-900 mb-1">Buka Gembok Pengajuan (Pengecualian Direktur)</h3>
            <p class="text-xs text-slate-500 mb-4">Hanya Direktur yang berhak memberikan izin pengecualian. Alasan akan dicatat permanen di audit log.</p>

            <form action="{{ route('cash-advances.unlock-gembok', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Pengecualian Tertulis <span class="text-rose-500">*</span></label>
                    <textarea name="unlock_reason" rows="3" required minlength="10" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl" placeholder="Contoh: Kebutuhan darurat proyek baru PLN sebelum audit proyek sebelumnya selesai."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showUnlockModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow">Buka Gembok</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 8: Request Top-Up -->
    <div x-show="showTopupModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-slate-900 mb-1">Pengajuan Top-Up Dana CA</h3>
            <p class="text-xs text-slate-500 mb-4">Tambahan dana akan dievaluasi oleh Atasan, Finance (dan Direktur bila nominal besar).</p>

            <form action="{{ route('top-ups.store', $cashAdvance) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Tambahan (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" required min="50000" step="10000" class="w-full px-3 py-2 text-sm font-bold bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500" placeholder="2000000">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Kebutuhan Dana <span class="text-rose-500">*</span></label>
                    <textarea name="reason" rows="3" required minlength="10" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500" placeholder="Jelaskan rincian kebutuhan mendesak di lapangan..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showTopupModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow">Kirim Pengajuan Top-Up</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 9: Verify Expense (Finance / Direktur) -->
    <div x-show="showVerifyModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-purple-900 mb-1">Verifikasi Struk Pengeluaran</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="verifyExpenseDesc"></p>

            <form :action="'/expenses/' + verifyExpenseId + '/verify'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Hasil Verifikasi <span class="text-rose-500">*</span></label>
                    <select name="status" x-model="verifyStatus" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl font-bold">
                        <option value="VALID">VALID (Struk Sah & Diakui)</option>
                        <option value="NEEDS_REVISION">PERLU PERBAIKAN (Kembalikan ke Karyawan)</option>
                        <option value="REJECTED">DITOLAK (Kembalikan Saldo & Jadi Hutang Karyawan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan / Alasan Evaluasi</label>
                    <textarea name="verification_notes" rows="3" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl" placeholder="Catatan pemeriksa struk..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showVerifyModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold text-xs rounded-xl shadow">Simpan Hasil Verifikasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 10: Void Expense (Finance) -->
    <div x-show="showVoidModal" x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
            <h3 class="text-base font-bold text-rose-700 mb-1">VOID Transaksi Pengeluaran</h3>
            <p class="text-xs text-slate-500 mb-4" x-text="voidExpenseDesc"></p>

            <form :action="'/expenses/' + voidExpenseId + '/void'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan VOID Tertulis <span class="text-rose-500">*</span></label>
                    <textarea name="void_reason" rows="3" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl" placeholder="Jelaskan alasan pembatalan transaksi..."></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showVoidModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow">VOID Transaksi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
