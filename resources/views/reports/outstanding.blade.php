@extends('layouts.app')

@section('title', 'Laporan 3: CA Menggantung (Outstanding)')

@section('content')
<div class="space-y-6">

    <!-- Header & Action (Design.md) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Laporan 3: CA Menggantung (Outstanding)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Untuk Finance dan Direktur. Memantau seluruh Cash Advance yang belum CLOSED, sisa saldo kas, dan penanda OVERDUE.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.outstanding', ['export' => 'csv']) }}" class="btn-primary text-xs py-2 px-3.5">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Ekspor CSV / Excel</span>
            </a>
            <button onclick="window.print()" class="btn-dark text-xs py-2 px-3.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Dokumen</span>
            </button>
        </div>
    </div>

    <!-- Outstanding Paper -->
    <div class="card-commerce p-6 sm:p-8 print-full">
        <!-- Letterhead -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-retail-darkInk tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                <p class="text-xs text-slate-600">Sistem Pengendalian Internal & Kas Operasional</p>
                <h3 class="text-xs sm:text-sm font-bold uppercase text-slate-800 mt-2">DAFTAR CASH ADVANCE MENGGANTUNG (OUTSTANDING CA)</h3>
            </div>
            <div class="text-right text-xs font-mono">
                <span class="font-bold text-retail-ink block">Posisi: {{ date('d/m/Y') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Total Menggantung: {{ $outstandingCAs->count() }} CA</span>
            </div>
        </div>

        <!-- Metric Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6 font-mono">
            <div class="bg-slate-50 p-3.5 rounded-[6px] border border-retail-surface">
                <span class="text-[10px] text-slate-500 uppercase font-bold font-sans block">Total Kas Menggantung</span>
                <span class="text-base sm:text-lg font-bold text-retail-ink block mt-1">
                    Rp {{ number_format($outstandingCAs->sum->current_balance, 0, ',', '.') }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5 font-sans">Saldo aktif di tangan karyawan</span>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-[6px] border border-retail-surface">
                <span class="text-[10px] text-amber-900 uppercase font-bold font-sans block">Menunggu Refund Sisa (Need Settlement)</span>
                <span class="text-base sm:text-lg font-bold text-amber-800 block mt-1">
                    {{ $outstandingCAs->where('status', 'NEED_SETTLEMENT')->count() }} Proyek
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5 font-sans">Proyek tuntas / wajib transfer sisa</span>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-[6px] border border-retail-surface">
                <span class="text-[10px] text-rose-800 uppercase font-bold font-sans block">Status Lewat Waktu (OVERDUE)</span>
                <span class="text-base sm:text-lg font-bold text-rose-600 block mt-1">
                    {{ $outstandingCAs->filter->is_overdue->count() }} CA
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5 font-sans">Melebihi durasi batas 2 bulan</span>
            </div>
        </div>

        <!-- Table -->
        <div class="border border-retail-surface rounded-[6px] overflow-hidden">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase border-b border-retail-surface font-sans">
                    <tr>
                        <th class="px-3 py-2.5">No. CA</th>
                        <th class="px-3 py-2.5">Karyawan</th>
                        <th class="px-3 py-2.5">Proyek</th>
                        <th class="px-3 py-2.5">Tanggal Cair</th>
                        <th class="px-3 py-2.5">Jatuh Tempo</th>
                        <th class="px-3 py-2.5 text-center">Sisa Waktu</th>
                        <th class="px-3 py-2.5 text-center">Overdue?</th>
                        <th class="px-3 py-2.5 text-right">CA Awal</th>
                        <th class="px-3 py-2.5 text-right">Sisa Saldo Kas</th>
                        <th class="px-3 py-2.5 text-center no-print font-sans">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($outstandingCAs as $ca)
                        <tr class="hover:bg-slate-50 {{ $ca->is_overdue ? 'bg-rose-50/40' : '' }}">
                            <td class="px-3 py-2.5 font-bold text-retail-ink whitespace-nowrap">{{ $ca->ca_number }}</td>
                            <td class="px-3 py-2.5 font-sans whitespace-nowrap">
                                <span class="font-bold text-slate-800">{{ $ca->user->name }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $ca->user->department }}</span>
                            </td>
                            <td class="px-3 py-2.5 font-sans">
                                <span class="font-medium text-slate-900">{{ $ca->project_name }}</span>
                                <span class="block text-[10px] font-mono text-slate-400">{{ $ca->project_code ?: '-' }}</span>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ $ca->disbursed_at ? $ca->disbursed_at->format('d/m/Y') : '-' }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ $ca->due_date ? $ca->due_date->format('d/m/Y') : '-' }}</td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                    <span class="font-bold {{ $ca->days_remaining <= 7 ? 'text-amber-800' : 'text-slate-700' }}">{{ $ca->days_remaining }} hr</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                @if($ca->is_overdue)
                                    <span class="badge-retail bg-rose-100 text-rose-700 border-rose-300 font-bold">OVERDUE</span>
                                @else
                                    <span class="badge-retail bg-emerald-50 text-emerald-800 border-emerald-300">Aman</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                Rp {{ number_format($ca->approved_amount ?: $ca->requested_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2.5 text-right font-extrabold text-retail-ink whitespace-nowrap">
                                Rp {{ number_format($ca->current_balance, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap no-print font-sans">
                                <a href="{{ route('cash-advances.show', $ca) }}" class="btn-secondary py-1 px-2.5 text-xs">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400 font-sans">Semua Cash Advance dalam kondisi lunas (tidak ada yang menggantung).</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection