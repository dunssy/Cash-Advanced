@extends('layouts.app')

@section('title', 'Laporan 1: Rincian per CA')

@section('content')
<div class="space-y-6">

    <!-- Header & Action (Design.md) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Laporan 1: Rincian per Cash Advance</h1>
            <p class="text-xs text-slate-500 mt-0.5">Untuk Finance, Atasan, dan Karyawan. Rincian lengkap buku saldo dan bukti struk per proyek.</p>
        </div>

        @if($selectedCA)
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.rincian-ca', ['ca_id' => $selectedCA->id, 'export' => 'csv']) }}" class="btn-primary text-xs py-2 px-3.5">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Ekspor CSV / Excel</span>
                </a>
                <button onclick="window.print()" class="btn-dark text-xs py-2 px-3.5">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Dokumen</span>
                </button>
            </div>
        @endif
    </div>

    <!-- CA Selector (No Print) -->
    <div class="card-commerce p-4 no-print">
        <form action="{{ route('reports.rincian-ca') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <label class="text-xs font-bold text-retail-ink uppercase tracking-wider whitespace-nowrap">Pilih Cash Advance:</label>
            <select name="ca_id" onchange="this.form.submit()" class="w-full sm:max-w-md input-commerce font-mono text-xs">
                @foreach($allCAs as $ca)
                    <option value="{{ $ca->id }}" {{ $selectedCA && $selectedCA->id === $ca->id ? 'selected' : '' }}>
                        {{ $ca->ca_number }} &bull; {{ $ca->project_name }} ({{ $ca->user->name }}) [{{ $ca->status }}]
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-dark text-xs py-2 px-4">
                Tampilkan
            </button>
        </form>
    </div>

    @if($selectedCA)
        <!-- Printable Report Sheet -->
        <div class="card-commerce p-6 sm:p-8 print-full">
            <!-- Company Letterhead Header -->
            <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-retail-darkInk tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                    <p class="text-xs text-slate-600">Engineering, Drives & Automation Systems</p>
                    <p class="text-[11px] text-slate-500 font-mono">Jl. Rungkut Industri Raya No. 45, Surabaya, Jawa Timur</p>
                </div>
                <div class="text-right">
                    <h3 class="text-xs sm:text-sm font-bold uppercase text-slate-800">LEMBAR PERTANGGUNGJAWABAN CASH ADVANCE</h3>
                    <span class="text-xs font-mono font-bold text-retail-orange block mt-0.5">{{ $selectedCA->ca_number }}</span>
                    <span class="text-[10px] font-mono text-slate-400 block">Dicetak: {{ date('d/m/Y H:i') }}</span>
                </div>
            </div>

            <!-- Project & Personnel Details -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-[6px] border border-retail-surface text-xs mb-6 font-mono">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold font-sans">Nama Karyawan</span>
                    <span class="font-bold text-slate-900 text-sm font-sans">{{ $selectedCA->user->name }}</span>
                    <span class="text-slate-500 block text-[11px] font-sans">{{ $selectedCA->user->department }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold font-sans">Proyek & Kode</span>
                    <span class="font-bold text-slate-900 text-sm font-sans">{{ $selectedCA->project_name }}</span>
                    <span class="text-slate-500 block text-[11px]">{{ $selectedCA->project_code ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold font-sans">Tanggal Dicairkan</span>
                    <span class="font-bold text-slate-800">{{ $selectedCA->disbursed_at ? $selectedCA->disbursed_at->format('d/m/Y') : '-' }}</span>
                    <span class="text-slate-500 block text-[11px]">Ref: {{ $selectedCA->disbursement_ref ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold font-sans">Status CA</span>
                    <span class="badge-retail mt-1 bg-white border-retail-surface">
                        {{ $selectedCA->status }}
                    </span>
                </div>
            </div>

            <!-- Summary Table of Balances -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center mb-6 font-mono">
                <div class="p-3 bg-slate-50 rounded-[3px] border border-retail-surface">
                    <span class="block text-[10px] font-bold uppercase text-slate-500 font-sans">CA Disetujui</span>
                    <span class="block text-sm font-bold text-slate-800 mt-1">
                        Rp {{ number_format($selectedCA->approved_amount ?: $selectedCA->requested_amount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 bg-slate-50 rounded-[3px] border border-retail-surface">
                    <span class="block text-[10px] font-bold uppercase text-slate-500 font-sans">Top-Up (+)</span>
                    <span class="block text-sm font-bold text-emerald-700 mt-1">
                        +Rp {{ number_format($selectedCA->disbursed_topups_total, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 bg-slate-50 rounded-[3px] border border-retail-surface">
                    <span class="block text-[10px] font-bold uppercase text-slate-500 font-sans">Pengeluaran (-)</span>
                    <span class="block text-sm font-bold text-rose-700 mt-1">
                        -Rp {{ number_format($selectedCA->recorded_expenses_total, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 bg-slate-50 rounded-[3px] border border-retail-surface">
                    <span class="block text-[10px] font-bold uppercase text-slate-500 font-sans">Sisa Saldo Kas</span>
                    <span class="block text-sm font-extrabold text-retail-ink mt-1">
                        Rp {{ number_format($selectedCA->current_balance, 0, ',', '.') }}
                    </span>
                </div>
                <div class="p-3 bg-amber-50 rounded-[3px] border border-amber-300 col-span-2 sm:col-span-1">
                    <span class="block text-[10px] font-bold uppercase text-amber-900 font-sans">Hasil Settlement</span>
                    <span class="block text-sm font-black text-amber-900 mt-1">
                        Rp {{ number_format($selectedCA->settlement_result, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Detailed Expenses Table -->
            <div class="border border-retail-surface rounded-[6px] overflow-hidden mb-6">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-retail-surface">
                        <tr>
                            <th class="px-3 py-2.5">No</th>
                            <th class="px-3 py-2.5">Tanggal</th>
                            <th class="px-3 py-2.5">Uraian / Keterangan</th>
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
                                        <span class="block text-[10px] text-amber-700 font-semibold font-mono">[Talangan Pribadi]</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ $exp->vendor_name }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="badge-retail text-[10px] bg-slate-100 text-slate-700 border-slate-200">
                                        {{ $exp->category_label }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right font-mono font-bold whitespace-nowrap text-slate-900">
                                    {{ number_format($exp->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <span class="badge-retail text-[9px] uppercase font-bold
                                        {{ $exp->verification_status === 'VALID' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : '' }}
                                        {{ $exp->verification_status === 'PENDING' ? 'bg-sky-50 text-sky-800 border-sky-300' : '' }}
                                        {{ $exp->verification_status === 'NEEDS_REVISION' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                                        {{ $exp->verification_status === 'REJECTED' ? 'bg-rose-50 text-rose-800 border-rose-300' : '' }}
                                        {{ $exp->verification_status === 'VOID' ? 'bg-slate-100 text-slate-700 border-slate-300' : '' }}
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
                            <td colspan="5" class="px-3 py-2 text-right uppercase text-[11px] font-sans">Total Pengeluaran Valid</td>
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
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Staff / Field Engineer</span>
                </div>
                <div>
                    <span class="text-slate-500 block mb-12">Disetujui Oleh (Atasan/Direktur),</span>
                    <span class="font-bold text-slate-900 block border-b border-slate-400 pb-1 max-w-[180px] mx-auto">{{ $selectedCA->approver?->name ?: '(...........................)' }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Atasan Langsung</span>
                </div>
                <div>
                    <span class="text-slate-500 block mb-12">Diverifikasi & Tutup (Finance),</span>
                    <span class="font-bold text-slate-900 block border-b border-slate-400 pb-1 max-w-[180px] mx-auto">{{ $selectedCA->closer?->name ?: '(...........................)' }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Finance & Accounting</span>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection