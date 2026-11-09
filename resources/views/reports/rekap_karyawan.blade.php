@extends('layouts.app')

@section('title', 'Laporan 4: Rekap Kepatuhan Karyawan')

@section('content')
<div class="space-y-6">

    @include('reports._report_nav')

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Laporan 4: Rekap Kepatuhan Karyawan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Untuk Finance dan Direktur. Memantau kedisiplinan pencatatan struk tepat waktu dan pengembalian sisa dana per karyawan.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.rekap-karyawan', ['export' => 'csv']) }}" class="btn-primary text-xs py-2 px-3.5">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Ekspor CSV / Excel</span>
            </a>
            <button onclick="window.print()" class="btn-dark text-xs py-2 px-3.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Rekap</span>
            </button>
        </div>
    </div>

    <!-- Paper Card -->
    <div class="card-commerce p-6 sm:p-8 print-full">
        <!-- Letterhead -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-retail-darkInk tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                <p class="text-xs text-slate-600">Departemen SDM & Finance - Evaluasi Kepatuhan Operasional</p>
                <h3 class="text-xs sm:text-sm font-bold uppercase text-slate-800 mt-2">REKAPITULASI KEPATUHAN & KETERLAMBATAN PER KARYAWAN</h3>
            </div>
            <div class="text-right text-xs font-mono">
                <span class="font-bold text-retail-ink block">Posisi: {{ date('d/m/Y') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Total Personil: {{ $employees->count() }} Karyawan</span>
            </div>
        </div>

        <!-- Table -->
        <div class="border border-slate-200 rounded-xl overflow-hidden table-responsive">
            <table class="w-full text-left text-xs font-mono min-w-[700px]">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase border-b border-retail-surface font-sans">
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
                                <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $user->email }}</span>
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

        <div class="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
            <span class="font-bold text-retail-ink block text-[11px] uppercase tracking-wide">Keterangan Indikator Kepatuhan:</span>
            <ul class="list-disc list-inside text-[11px] text-slate-500 space-y-0.5">
                <li><strong>Telat Catat Struk:</strong> Transaksi dicatat lebih dari 1 hari kalender sejak transaksi terjadi (label TERLAMBAT).</li>
                <li><strong>Telat Refund Sisa:</strong> Sisa kas ditransfer melewati batas 3 hari kerja setelah status NEED_SETTLEMENT.</li>
                <li><strong>Overdue:</strong> Cash Advance yang belum tuntas melewati batas maksimal 2 bulan (60 hari) sejak pencairan dana.</li>
            </ul>
        </div>
    </div>

</div>
@endsection