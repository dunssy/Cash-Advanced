@extends('layouts.app')

@section('title', 'Rincian per Cash Advance')
@section('page_title', 'Rincian per Cash Advance')

@section('content')
<div class="space-y-3.5">

    <!-- Toolbar Pemilihan CA & Tombol Aksi (Tanpa Background & Clean) -->
    <div class="no-print pb-1">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Form Pemilihan Dokumen CA (Tanpa Background & Otomatis) -->
            <form action="{{ route('reports.rincian-ca') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 flex-grow">
                <label for="ca_selector" class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider whitespace-nowrap mb-0">
                    Pilih Cash Advance:
                </label>
                <div class="w-full sm:max-w-xl">
                    <select id="ca_selector" name="ca_id" onchange="this.form.submit()" class="w-full input-commerce font-mono text-xs py-2 bg-white cursor-pointer hover:border-slate-400 focus:border-[#FF3C00] shadow-2xs">
                        @foreach($allCAs ?? [] as $ca)
                            <option value="{{ $ca->id }}" {{ $selectedCA && $selectedCA->id === $ca->id ? 'selected' : '' }}>
                                {{ $ca->ca_number }} &bull; {{ $ca->project_name }} ({{ $ca->user->name }}) [{{ $ca->status }}]
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <!-- Tombol Aksi Dokumen (Ekspor Excel & Cetak Dokumen) -->
            @if($selectedCA)
                <div class="flex items-center space-x-2 flex-shrink-0">
                    <a href="{{ route('reports.rincian-ca', ['ca_id' => $selectedCA->id, 'export' => 'csv']) }}" class="btn-primary text-xs py-2 px-3.5 shadow-xs">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Ekspor Excel</span>
                    </a>
                    <button onclick="window.print()" class="btn-dark text-xs py-2 px-3.5 shadow-xs">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Dokumen</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($selectedCA)
        <!-- Printable Report Sheet -->
        <div class="card-commerce p-6 sm:p-8 print-full">
            <!-- Company Letterhead Header -->
            <div class="border-b-2 border-slate-900 pb-2.5 mb-2.5 flex items-start justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-retail-darkInk tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                    <p class="text-xs text-slate-600">Engineering, Drives & Automation Systems</p>
                    <p class="text-xs text-slate-500 font-mono">Jl. Rungkut Industri Raya No. 45, Surabaya, Jawa Timur</p>
                </div>
                <div class="text-right">
                    <h3 class="text-xs sm:text-sm font-bold uppercase text-slate-800">LEMBAR PERTANGGUNGJAWABAN CASH ADVANCE</h3>
                    <span class="text-xs font-mono font-bold text-retail-orange block mt-0.5">{{ $selectedCA->ca_number }}</span>
                    <span class="text-xs font-mono text-slate-400 block">Dicetak: {{ date('d/m/Y H:i') }}</span>
                </div>
            </div>

            <!-- Project & Personnel Details -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 sm:gap-6 p-4 sm:p-5 bg-slate-50 rounded-[15px] border border-slate-200 text-xs mb-6 font-mono">
                <div class="sm:col-span-3">
                    <span class="text-slate-400 block text-xs uppercase font-bold font-sans mb-1">Nama Karyawan</span>
                    <span class="font-bold text-slate-900 text-sm font-sans block leading-tight">{{ $selectedCA->user->name }}</span>
                    <span class="text-slate-500 block text-xs font-sans mt-0.5">{{ $selectedCA->user->department }}</span>
                </div>
                <div class="sm:col-span-4">
                    <span class="text-slate-400 block text-xs uppercase font-bold font-sans mb-1">Proyek & Kode</span>
                    <span class="font-bold text-slate-900 text-sm font-sans block leading-snug">{{ $selectedCA->project_name }}</span>
                    <span class="text-slate-500 block text-xs mt-0.5 font-mono">{{ $selectedCA->project_code ?: '-' }}</span>
                </div>
                <div class="sm:col-span-3 sm:ps-6 sm:border-s sm:border-slate-200">
                    <span class="text-slate-400 block text-xs uppercase font-bold font-sans mb-1">Tanggal Dicairkan</span>
                    <span class="font-bold text-slate-800 font-mono text-sm block">{{ $selectedCA->disbursed_at ? $selectedCA->disbursed_at->format('d/m/Y') : '-' }}</span>
                    <span class="text-slate-500 block text-xs mt-0.5 font-mono">Ref: {{ $selectedCA->disbursement_ref ?: '-' }}</span>
                </div>
                <div class="sm:col-span-2 text-start sm:text-end sm:ps-2">
                    <span class="text-slate-400 block text-xs uppercase font-bold font-sans mb-1">Status CA</span>
                    <div class="mt-1 flex sm:justify-end">
                        <span class="badge-retail bg-white border border-slate-300 shadow-2xs">
                            {{ $selectedCA->status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Summary Table of Balances -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center mb-6 font-mono">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="block text-xs font-bold uppercase text-slate-500 font-sans">CA Disetujui</span>
                    <span class="block text-sm font-bold text-slate-800 mt-1">
                        Rp {{ number_format($selectedCA->approved_amount ?: $selectedCA->requested_amount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="block text-xs font-bold uppercase text-slate-500 font-sans">Top-Up (+)</span>
                    <span class="block text-sm font-bold text-emerald-700 mt-1">
                        +Rp {{ number_format($selectedCA->disbursed_topups_total, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="block text-xs font-bold uppercase text-slate-500 font-sans">Pengeluaran (-)</span>
                    <span class="block text-sm font-bold text-rose-700 mt-1">
                        -Rp {{ number_format($selectedCA->recorded_expenses_total, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="block text-xs font-bold uppercase text-slate-500 font-sans">Sisa Saldo Kas</span>
                    <span class="block text-sm font-extrabold {{ $selectedCA->status === 'CLOSED' ? 'text-emerald-700' : 'text-retail-ink' }} mt-1">
                        @if($selectedCA->status === 'CLOSED')
                            Rp 0 (Lunas)
                        @else
                            Rp {{ number_format($selectedCA->current_balance, 0, ',', '.') }}
                        @endif
                    </span>
                </div>
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-300 col-span-2 sm:col-span-1">
                    <span class="block text-xs font-bold uppercase text-amber-900 font-sans">Hasil Settlement</span>
                    <span class="block text-sm font-black {{ $selectedCA->status === 'CLOSED' ? 'text-emerald-700' : 'text-amber-900' }} mt-1">
                        @if($selectedCA->status === 'CLOSED')
                            Rp 0 (Selesai)
                        @else
                            Rp {{ number_format($selectedCA->settlement_result, 0, ',', '.') }}
                        @endif
                    </span>
                </div>
            </div>

            <!-- Detailed Expenses Table -->
            <div class="border border-slate-200 rounded-[15px] overflow-hidden mb-6 table-responsive">
                <table class="w-full text-left text-xs min-w-[700px] table-technodrives">
                    <thead>
                        <tr>
                            <th class="px-3 py-2.5">No</th>
                            <th class="px-3 py-2.5">Tanggal</th>
                            <th class="px-3 py-2.5">Keterangan</th>
                            <th class="px-3 py-2.5">Vendor</th>
                            <th class="px-3 py-2.5">Kategori</th>
                            <th class="px-3 py-2.5 text-right">Nominal (Rp)</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($selectedCA->expenses as $idx => $exp)
                            <tr class="{{ $exp->verification_status === 'VOID' ? 'line-through opacity-40 bg-slate-50' : '' }}">
                                <td class="px-3 py-2 font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-3 py-2 font-mono whitespace-nowrap">{{ $exp->transaction_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">
                                    <span class="font-medium text-slate-900">{{ $exp->description }}</span>
                                    @if($exp->is_talangan)
                                        <span class="block text-xs text-amber-700 font-semibold font-mono">[Talangan Pribadi]</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ $exp->vendor_name }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="badge-retail text-xs bg-slate-100 text-slate-700 border-slate-200">
                                        {{ $exp->category_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right font-mono font-bold whitespace-nowrap text-slate-900">
                                    {{ number_format($exp->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <span class="badge-retail text-xs uppercase font-bold
                                        {{ $exp->verification_status === 'VALID' ? 'bg-[#16A34A] text-white shadow-sm' : '' }}
                                        {{ $exp->verification_status === 'PENDING' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                        {{ $exp->verification_status === 'NEEDS_REVISION' ? 'bg-white border-2 border-[#FF3C00] text-[#FF3C00]' : '' }}
                                        {{ $exp->verification_status === 'REJECTED' ? 'bg-[#DC2626] text-white shadow-sm' : '' }}
                                        {{ $exp->verification_status === 'VOID' ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1] line-through' : '' }}
                                    ">
                                        {{ $exp->verification_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada pengeluaran tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-retail-surface font-mono">
                        <tr>
                            <td colspan="5" class="px-3 py-2 text-right uppercase text-xs font-sans">Total Pengeluaran Valid</td>
                            <td class="px-3 py-2 text-right text-sm text-retail-ink">
                                Rp {{ number_format($selectedCA->valid_expenses_total, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Signatures / Approval Block (Printable) -->
            <div class="grid grid-cols-3 gap-6 pt-6 border-t-2 border-slate-200 text-center text-xs">
                <div>
                    <span class="text-slate-500 block mb-12">Dibuat Oleh (Pemohon),</span>
                    <span class="font-bold text-slate-900 block border-b border-slate-400 pb-1 max-w-[180px] mx-auto">{{ $selectedCA->user->name }}</span>
                    <span class="text-xs text-slate-400 mt-0.5 block">Field Engineer</span>
                </div>
                <div>
                    <span class="text-slate-500 block mb-12">Disetujui Oleh,</span>
                    <span class="font-bold text-slate-900 block border-b border-slate-400 pb-1 max-w-[180px] mx-auto">{{ $selectedCA->approver?->name ?: '(...........................)' }}</span>
                    <span class="text-xs text-slate-400 mt-0.5 block">Atasan Langsung</span>
                </div>
                <div>
                    <span class="text-slate-500 block mb-12">Diverifikasi & Tutup (Finance),</span>
                    <span class="font-bold text-slate-900 block border-b border-slate-400 pb-1 max-w-[180px] mx-auto">{{ $selectedCA->closer?->name ?: '(...........................)' }}</span>
                    <span class="text-xs text-slate-400 mt-0.5 block">Finance & Accounting</span>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection