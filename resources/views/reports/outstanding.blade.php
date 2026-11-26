@extends('layouts.app')

@section('title', 'CA Menggantung (Outstanding)')
@section('page_title', 'CA Menggantung (Outstanding)')

@section('content')
<div class="space-y-4">

    <!-- Tombol Aksi Dokumen (Ekspor Excel & Cetak Dokumen) -->
    <div class="grid grid-cols-2 sm:flex sm:items-center sm:justify-end gap-2.5 no-print w-full">
        <a href="{{ route('reports.outstanding', ['export' => 'csv']) }}" class="btn-primary text-xs py-2 px-3.5 shadow-xs justify-center w-full sm:w-auto">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            <span>Ekspor Excel</span>
        </a>
        <button onclick="window.print()" class="btn-dark text-xs py-2 px-3.5 shadow-xs justify-center w-full sm:w-auto">
            <i class="bi bi-printer"></i>
            <span>Cetak Dokumen</span>
        </button>
    </div>

    <!-- Outstanding Paper -->
    <div class="card-commerce p-4 sm:p-8 print-full">
        <!-- Letterhead -->
        <div class="border-b-2 border-slate-900 pb-4 mb-5 flex flex-col sm:flex-row print:flex-row sm:items-start sm:justify-between gap-2.5">
            <div>
                <h2 class="text-base sm:text-xl font-bold text-retail-darkInk tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                <p class="text-xs text-slate-600">Sistem Pengendalian Internal & Kas Operasional</p>
                <h3 class="text-xs sm:text-sm font-bold uppercase text-slate-800 mt-2">DAFTAR CASH ADVANCE MENGGANTUNG (OUTSTANDING CA)</h3>
            </div>
            <div class="text-left sm:text-right print:text-right text-xs font-mono border-t border-slate-200 sm:border-t-0 pt-2 sm:pt-0">
                <span class="font-bold text-retail-ink block">Posisi: {{ date('d/m/Y') }}</span>
                <span class="text-xs text-slate-400 block mt-0.5">Total Menggantung: {{ $outstandingCAs->count() }} CA</span>
            </div>
        </div>

        <!-- Metric Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3 mb-5 font-mono">
            <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-200">
                <span class="text-xs text-slate-500 uppercase font-bold font-sans block">Total Kas Menggantung</span>
                <span class="text-base sm:text-lg font-bold text-retail-ink block mt-1">
                    Rp {{ number_format($outstandingCAs->sum->current_balance, 0, ',', '.') }}
                </span>
                <span class="text-xs text-slate-400 block mt-0.5 font-sans">Saldo aktif di tangan karyawan</span>
            </div>

            <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-200">
                <span class="text-xs text-amber-900 uppercase font-bold font-sans block">Menunggu Refund Sisa (Need Settlement)</span>
                <span class="text-base sm:text-lg font-bold text-amber-800 block mt-1">
                    {{ $outstandingCAs->where('status', 'NEED_SETTLEMENT')->count() }} Proyek
                </span>
                <span class="text-xs text-slate-400 block mt-0.5 font-sans">Proyek tuntas, wajib transfer sisa kas</span>
            </div>

            <div class="bg-slate-50 p-3 sm:p-3.5 rounded-xl border border-slate-200">
                <span class="text-xs text-rose-800 uppercase font-bold font-sans block">Status Lewat Waktu (OVERDUE)</span>
                <span class="text-base sm:text-lg font-bold text-rose-600 block mt-1">
                    {{ $outstandingCAs->filter->is_overdue->count() }} CA
                </span>
                <span class="text-xs text-slate-400 block mt-0.5 font-sans">Melebihi durasi batas 2 bulan</span>
            </div>
        </div>

        <!-- Mobile Cards View -->
        <div class="md:hidden print:hidden border border-slate-200 rounded-[15px] overflow-hidden divide-y divide-slate-100">
            @forelse($outstandingCAs as $ca)
                <div class="p-3.5 space-y-2.5 {{ $ca->is_overdue ? 'bg-rose-50/40' : 'bg-white' }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono font-bold text-xs text-white bg-[#050A1E] px-2 py-0.5 rounded">{{ $ca->ca_number }}</span>
                        <div class="flex items-center gap-2">
                            @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                <span class="font-mono text-xs font-bold {{ $ca->is_overdue ? 'text-rose-600' : ($ca->days_remaining <= 7 ? 'text-amber-800' : 'text-slate-700') }}">
                                    {{ $ca->days_remaining }} hr
                                </span>
                            @endif
                            <a href="{{ route('cash-advances.show', $ca) }}" class="btn-secondary py-1 px-2.5 text-[11px] rounded-full">
                                Detail
                            </a>
                        </div>
                    </div>

                    <div>
                        <p class="font-bold text-slate-900 text-xs leading-snug">{{ $ca->project_name }}</p>
                        <p class="text-[11px] text-slate-600 mt-0.5">{{ $ca->user->name }} @if($ca->user->department) &bull; {{ $ca->user->department }} @endif</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100 font-mono">
                        <div>
                            <span class="text-[11px] text-slate-400 font-sans block">Tgl Cair / Jatuh Tempo</span>
                            <span class="text-[11px] text-slate-700 block">{{ $ca->disbursed_at ? $ca->disbursed_at->format('d/m/Y') : '-' }} &rarr; {{ $ca->due_date ? $ca->due_date->format('d/m/Y') : '-' }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 font-sans block">Sisa Saldo</span>
                            <span class="font-extrabold text-retail-ink text-xs block">Rp {{ number_format($ca->current_balance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs">Semua Cash Advance dalam kondisi lunas (tidak ada yang menggantung).</div>
            @endforelse
        </div>

        <!-- Table (Desktop & Print) -->
        <div class="hidden md:block print:block border border-slate-200 rounded-[15px] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono table-technodrives">
                    <thead>
                        <tr>
                            <th class="px-3 py-2.5 whitespace-nowrap">No. CA</th>
                            <th class="px-3 py-2.5">Karyawan</th>
                            <th class="px-3 py-2.5 min-w-[160px]">Proyek</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Tgl Cair</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Jatuh Tempo</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Sisa Waktu</th>
                            <th class="px-3 py-2.5 text-right whitespace-nowrap">CA Awal</th>
                            <th class="px-3 py-2.5 text-right whitespace-nowrap">Sisa Saldo</th>
                            <th class="px-3 py-2.5 text-center no-print font-sans whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($outstandingCAs as $ca)
                            <tr class="hover:bg-slate-50 {{ $ca->is_overdue ? 'bg-rose-50/40' : '' }}">
                                <td class="px-3 py-2.5 font-bold text-retail-ink whitespace-nowrap">{{ $ca->ca_number }}</td>
                                <td class="px-3 py-2.5 font-sans">
                                    <span class="font-bold text-slate-800 block">{{ $ca->user->name }}</span>
                                    <span class="block text-[11px] text-slate-400">{{ $ca->user->department }}</span>
                                </td>
                                <td class="px-3 py-2.5 font-sans min-w-[160px]">
                                    <span class="font-medium text-slate-900 block leading-snug">{{ $ca->project_name }}</span>
                                    <span class="block text-[11px] font-mono text-slate-400">{{ $ca->project_code ?: '-' }}</span>
                                </td>
                                <td class="px-3 py-2.5 whitespace-nowrap">{{ $ca->disbursed_at ? $ca->disbursed_at->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-2.5 whitespace-nowrap">{{ $ca->due_date ? $ca->due_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                        <span class="font-bold {{ $ca->is_overdue ? 'text-rose-600' : ($ca->days_remaining <= 7 ? 'text-amber-800' : 'text-slate-700') }}">
                                            {{ $ca->days_remaining }} hr
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                    Rp {{ number_format($ca->approved_amount ?: $ca->requested_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-extrabold text-retail-ink whitespace-nowrap">
                                    Rp {{ number_format($ca->current_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2.5 text-center whitespace-nowrap no-print font-sans">
                                    <a href="{{ route('cash-advances.show', $ca) }}" class="btn-secondary py-1 px-3 text-xs rounded-full">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 font-sans">Semua Cash Advance dalam kondisi lunas (tidak ada yang menggantung).</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection