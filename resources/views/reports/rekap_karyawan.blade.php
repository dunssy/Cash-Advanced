@extends('layouts.app')

@section('title', 'Rekap Kepatuhan Karyawan')
@section('page_title', 'Rekap Kepatuhan Karyawan')

@section('content')
<div class="space-y-4">

    <!-- Tombol Aksi Dokumen (Ekspor Excel & Cetak Dokumen) -->
    <div class="grid grid-cols-2 sm:flex sm:items-center sm:justify-end gap-2.5 w-full no-print">
        <a href="{{ route('reports.rekap-karyawan', ['export' => 'csv']) }}" class="btn-primary text-xs py-2 px-3.5 shadow-xs justify-center w-full sm:w-auto">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            <span>Ekspor Excel</span>
        </a>
        <button onclick="window.print()" class="btn-dark text-xs py-2 px-3.5 shadow-xs justify-center w-full sm:w-auto">
            <i class="bi bi-printer"></i>
            <span>Cetak Dokumen</span>
        </button>
    </div>

    <!-- Paper Card -->
    <div class="card-commerce p-4 sm:p-8 print-full">
        <!-- Letterhead -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex flex-col sm:flex-row print:flex-row sm:items-start sm:justify-between gap-2.5">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-retail-darkInk tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                <p class="text-xs text-slate-600">Departemen SDM & Finance - Evaluasi Kepatuhan Operasional</p>
                <h3 class="text-xs sm:text-sm font-bold uppercase text-slate-800 mt-2">REKAPITULASI KEPATUHAN & KETERLAMBATAN PER KARYAWAN</h3>
            </div>
            <div class="text-left sm:text-right print:text-right border-t border-slate-200 sm:border-t-0 pt-2 sm:pt-0 text-xs font-mono flex-shrink-0">
                <span class="font-bold text-retail-ink block">Posisi: {{ date('d/m/Y') }}</span>
                <span class="text-xs text-slate-400 block mt-0.5">Total Personil: {{ $employees->count() }} Karyawan</span>
            </div>
        </div>

        <!-- Mobile Cards View -->
        <div class="md:hidden print:hidden border border-slate-200 rounded-[15px] overflow-hidden divide-y divide-slate-100">
            @forelse($employees as $row)
                @php
                    $user = $row['user'];
                    $hasDisciplinaryRisk = ($row['late_expense_count'] > 2 || $row['overdue_count'] > 0);
                @endphp
                <div class="p-3.5 space-y-2.5 {{ $hasDisciplinaryRisk ? 'bg-amber-50/40' : 'bg-white' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <span class="font-bold text-retail-ink text-sm block truncate">{{ $user->name }}</span>
                            <span class="text-[11px] font-mono text-slate-400 block truncate">{{ $user->email }}</span>
                            <span class="text-[11px] text-slate-600 block mt-0.5">{{ $user->department ?: '-' }}</span>
                        </div>
                        @if($row['overdue_count'] > 0)
                            <span class="badge-retail bg-rose-700 text-white font-bold text-[11px] flex-shrink-0">
                                {{ $row['overdue_count'] }} OVERDUE
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-[11px] text-slate-400 block">Total CA & Nilai</span>
                            <span class="font-mono font-bold text-slate-800 block">{{ $row['total_ca'] }} CA &bull; Rp {{ number_format($row['total_nominal'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400 block">CA Aktif</span>
                            <span class="font-mono font-bold text-retail-ink block">{{ $row['active_count'] }} CA</span>
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400 block">Telat Catat (&gt; 1 Hari)</span>
                            @if($row['late_expense_count'] > 0)
                                <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 text-[11px] mt-0.5">{{ $row['late_expense_count'] }} kali</span>
                            @else
                                <span class="text-emerald-700 font-semibold text-[11px]">0 (Tertib)</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-400 block">Telat Refund (&gt; 3 Hari)</span>
                            @if($row['late_refund_count'] > 0)
                                <span class="badge-retail bg-amber-50 text-amber-900 border-amber-300 text-[11px] mt-0.5">{{ $row['late_refund_count'] }} kali</span>
                            @else
                                <span class="text-emerald-700 font-semibold text-[11px]">0 (Tertib)</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs">Tidak ada data karyawan.</div>
            @endforelse
        </div>

        <!-- Table (Desktop & Print) -->
        <div class="hidden md:block print:block border border-slate-200 rounded-[15px] overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono min-w-[700px] table-technodrives">
                <thead>
                    <tr>
                        <th class="px-3.5 py-2.5">Nama Karyawan</th>
                        <th class="px-3.5 py-2.5">Departemen</th>
                        <th class="px-3.5 py-2.5 text-center">Total CA</th>
                        <th class="px-3.5 py-2.5 text-right">Total Nilai CA</th>
                        <th class="px-3.5 py-2.5 text-center">Telat Catat (&gt; 1 Hari)</th>
                        <th class="px-3.5 py-2.5 text-center">Telat Refund (&gt; 3 Hari)</th>
                        <th class="px-3.5 py-2.5 text-center">CA Aktif</th>
                        <th class="px-3.5 py-2.5 text-center">CA Overdue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $row)
                        @php
                            $user = $row['user'];
                            $hasDisciplinaryRisk = ($row['late_expense_count'] > 2 || $row['overdue_count'] > 0);
                        @endphp
                        <tr class="hover:bg-slate-50 {{ $hasDisciplinaryRisk ? 'bg-amber-50/40' : '' }}">
                            <td class="px-3.5 py-2.5 font-bold text-retail-ink whitespace-nowrap font-sans">
                                {{ $user->name }}
                                <span class="block text-xs font-mono text-slate-400 font-normal">{{ $user->email }}</span>
                            </td>
                            <td class="px-3.5 py-2.5 text-slate-600 whitespace-nowrap font-sans">{{ $user->department ?: '-' }}</td>
                            <td class="px-3.5 py-2.5 text-center font-bold text-slate-800">{{ $row['total_ca'] }}</td>
                            <td class="px-3.5 py-2.5 text-right font-bold text-slate-800 whitespace-nowrap">
                                Rp {{ number_format($row['total_nominal'], 0, ',', '.') }}
                            </td>
                            <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                @if($row['late_expense_count'] > 0)
                                    <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300">
                                        {{ $row['late_expense_count'] }} kali
                                    </span>
                                @else
                                    <span class="text-emerald-700 font-semibold font-sans">0 (Tertib)</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                @if($row['late_refund_count'] > 0)
                                    <span class="badge-retail bg-amber-50 text-amber-900 border-amber-300">
                                        {{ $row['late_refund_count'] }} kali
                                    </span>
                                @else
                                    <span class="text-emerald-700 font-semibold font-sans">0 (Tertib)</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 text-center font-bold text-retail-ink whitespace-nowrap">
                                {{ $row['active_count'] }} CA
                            </td>
                            <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                @if($row['overdue_count'] > 0)
                                    <span class="badge-retail bg-rose-700 text-white font-bold">
                                        {{ $row['overdue_count'] }} OVERDUE
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 font-sans">Tidak ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div>
@endsection