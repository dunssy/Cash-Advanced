@extends('layouts.app')

@section('title', 'Laporan 4: Rekap Performa Karyawan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan 4: Rekap Kepatuhan Karyawan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Untuk Finance dan Direktur. Memantau kedisiplinan pencatatan struk tepat waktu dan pengembalian sisa dana per karyawan.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.rekap-karyawan', ['export' => 'csv']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center space-x-1.5">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Ekspor CSV / Excel</span>
            </a>
            <button onclick="window.print()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center space-x-1.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Rekap</span>
            </button>
        </div>
    </div>

    <!-- Paper Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8 print-full">
        <!-- Letterhead -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                <p class="text-xs text-slate-600">Departemen SDM & Finance - Evaluasi Kepatuhan Operasional</p>
                <h3 class="text-sm font-extrabold uppercase text-slate-800 mt-2">REKAPITULASI KEPATUHAN & KETERLAMBATAN PER KARYAWAN</h3>
            </div>
            <div class="text-right text-xs">
                <span class="font-bold text-slate-800 block">Posisi: {{ date('d F Y') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Total Personil: {{ $employees->count() }} Karyawan</span>
            </div>
        </div>

        <!-- Table -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Nama Karyawan</th>
                        <th class="px-4 py-3">Departemen</th>
                        <th class="px-4 py-3 text-center">Total CA</th>
                        <th class="px-4 py-3 text-right">Total Nilai CA</th>
                        <th class="px-4 py-3 text-center">Telat Catat Struk (> 1 Hari)</th>
                        <th class="px-4 py-3 text-center">Telat Refund Sisa (> 3 Hari)</th>
                        <th class="px-4 py-3 text-center">CA Aktif</th>
                        <th class="px-4 py-3 text-center">CA Overdue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $row)
                        @php
                            $user = $row['user'];
                            $hasDisciplinaryRisk = ($row['late_expense_count'] > 2 || $row['overdue_count'] > 0);
                        @endphp
                        <tr class="hover:bg-slate-50 {{ $hasDisciplinaryRisk ? 'bg-amber-50/30' : '' }}">
                            <td class="px-4 py-3 font-bold text-slate-900 whitespace-nowrap">
                                {{ $user->name }}
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $user->email }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $user->department ?: '-' }}</td>
                            <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $row['total_ca'] }}</td>
                            <td class="px-4 py-3 text-right font-medium text-slate-800 whitespace-nowrap">
                                Rp {{ number_format($row['total_nominal'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($row['late_expense_count'] > 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                                        {{ $row['late_expense_count'] }} kali
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-medium">0 (Tertib)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($row['late_refund_count'] > 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        {{ $row['late_refund_count'] }} kali
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-medium">0 (Tertib)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-sky-700 whitespace-nowrap">
                                {{ $row['active_count'] }} CA
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($row['overdue_count'] > 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-rose-600 text-white animate-pulse">
                                        {{ $row['overdue_count'] }} OVERDUE
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Tidak ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-600 space-y-1">
            <span class="font-bold text-slate-800 block">Keterangan Indikator Kepatuhan:</span>
            <ul class="list-disc list-inside text-[11px] text-slate-500 space-y-0.5">
                <li><strong>Telat Catat Struk:</strong> Transaksi dicatat lebih dari 1 hari kalender sejak tanggal transaksi riil (diberi flag TERLAMBAT).</li>
                <li><strong>Telat Refund Sisa:</strong> Sisa dana proyek ditransfer melewati batas 3 hari kerja setelah status NEED_SETTLEMENT.</li>
                <li><strong>Overdue:</strong> Cash Advance yang belum tuntas melewati batas maksimal 2 bulan sejak pencairan.</li>
            </ul>
        </div>
    </div>

</div>
@endsection
