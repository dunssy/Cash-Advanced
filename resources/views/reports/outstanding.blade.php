@extends('layouts.app')

@section('title', 'Laporan 3: CA Menggantung (Outstanding)')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan 3: CA Menggantung (Outstanding)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Untuk Finance dan Direktur. Memantau seluruh Cash Advance yang belum CLOSED, sisa saldo kas, dan penanda OVERDUE.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.outstanding', ['export' => 'csv']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center space-x-1.5">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Ekspor CSV / Excel</span>
            </a>
            <button onclick="window.print()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center space-x-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Dokumen</span>
            </button>
        </div>
    </div>

    <!-- Outstanding Paper -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8 print-full">
        <!-- Letterhead -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                <p class="text-xs text-slate-600">Sistem Pengendalian Internal & Kas Operasional</p>
                <h3 class="text-sm font-extrabold uppercase text-slate-800 mt-2">DAFTAR CASH ADVANCE MENGGANTUNG (OUTSTANDING CA)</h3>
            </div>
            <div class="text-right text-xs">
                <span class="font-bold text-slate-800 block">Posisi: {{ date('d F Y') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Total Menggantung: {{ $outstandingCAs->count() }} CA</span>
            </div>
        </div>

        <!-- Metric Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-500 uppercase font-bold block">Total Kas Menggantung di Karyawan</span>
                <span class="text-xl font-black text-slate-900 block mt-1">
                    Rp {{ number_format($outstandingCAs->sum->current_balance, 0, ',', '.') }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Saldo aktif yang belum dipertanggungjawabkan</span>
            </div>

            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                <span class="text-[10px] text-amber-800 uppercase font-bold block">Menunggu Refund Sisa (Need Settlement)</span>
                <span class="text-xl font-black text-amber-900 block mt-1">
                    {{ $outstandingCAs->where('status', 'NEED_SETTLEMENT')->count() }} Proyek
                </span>
                <span class="text-[10px] text-amber-700 block mt-0.5">Proyek selesai / sisa dana wajib ditransfer</span>
            </div>

            <div class="bg-rose-50 p-4 rounded-xl border border-rose-200">
                <span class="text-[10px] text-rose-800 uppercase font-bold block">Status Lewat Batas Waktu (OVERDUE)</span>
                <span class="text-xl font-black text-rose-600 block mt-1">
                    {{ $outstandingCAs->filter->is_overdue->count() }} CA
                </span>
                <span class="text-[10px] text-rose-700 block mt-0.5">Melebihi durasi batas 2 bulan</span>
            </div>
        </div>

        <!-- Table -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-3">No. CA</th>
                        <th class="px-3 py-3">Karyawan</th>
                        <th class="px-3 py-3">Proyek</th>
                        <th class="px-3 py-3">Tanggal Cair</th>
                        <th class="px-3 py-3">Jatuh Tempo</th>
                        <th class="px-3 py-3 text-center">Sisa Waktu</th>
                        <th class="px-3 py-3 text-center">Overdue?</th>
                        <th class="px-3 py-3 text-right">CA Awal</th>
                        <th class="px-3 py-3 text-right">Sisa Saldo Kas</th>
                        <th class="px-3 py-3 text-center no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($outstandingCAs as $ca)
                        <tr class="hover:bg-slate-50 {{ $ca->is_overdue ? 'bg-rose-50/40' : '' }}">
                            <td class="px-3 py-2.5 font-bold text-slate-900 whitespace-nowrap">{{ $ca->ca_number }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <span class="font-medium text-slate-800 block">{{ $ca->user->name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $ca->user->department }}</span>
                            </td>
                            <td class="px-3 py-2.5 max-w-xs">
                                <span class="font-medium text-slate-800 block truncate">{{ $ca->project_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $ca->project_code ?: '-' }}</span>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ $ca->disbursed_at ? $ca->disbursed_at->format('d/m/Y') : '-' }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap">{{ $ca->due_date ? $ca->due_date->format('d/m/Y') : '-' }}</td>
                            <td class="px-3 py-2.5 text-center font-semibold">
                                @if($ca->due_date)
                                    {{ $ca->days_remaining }} Hari
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                @if($ca->is_overdue)
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white">
                                        OVERDUE
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-medium text-[11px]">Normal</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right font-medium whitespace-nowrap">
                                Rp {{ number_format($ca->approved_amount ?: $ca->requested_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2.5 text-right font-extrabold text-sky-700 whitespace-nowrap text-sm">
                                Rp {{ number_format($ca->current_balance, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2.5 text-center no-print whitespace-nowrap">
                                <a href="{{ route('cash-advances.show', $ca) }}" class="text-sky-600 hover:text-sky-800 font-bold">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400">Tidak ada Cash Advance menggantung. Semua CA telah selesai dan lunas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
