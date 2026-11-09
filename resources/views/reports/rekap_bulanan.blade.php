@extends('layouts.app')

@section('title', 'Laporan 2: Rekap Bulanan Pajak & Pembukuan')

@section('content')
<div class="space-y-6">

    @include('reports._report_nav')

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Laporan 2: Rekap Bulanan Pajak & Pembukuan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Untuk Finance dan Direktur. Total CA, pengeluaran valid per kategori untuk pelaporan pajak, dan sisa refund.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.rekap-bulanan', ['month' => $month, 'year' => $year, 'export' => 'csv']) }}" class="btn-primary text-xs py-2 px-3.5">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Ekspor CSV / Excel</span>
            </a>
            <button onclick="window.print()" class="btn-dark text-xs py-2 px-3.5">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Rekap</span>
            </button>
        </div>
    </div>

    <!-- Month & Year Filter Form (No Print) -->
    <div class="card-commerce p-4 no-print">
        <form action="{{ route('reports.rekap-bulanan') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold text-retail-ink uppercase tracking-wider">Bulan:</label>
                <select name="month" class="input-commerce text-xs py-1.5 font-sans">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold text-retail-ink uppercase tracking-wider">Tahun:</label>
                <select name="year" class="input-commerce text-xs py-1.5 font-mono">
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="btn-dark text-xs py-1.5 px-4">
                Tampilkan Rekap
            </button>
        </form>
    </div>

    <!-- Report Paper Card -->
    <div class="card-commerce p-6 sm:p-8 print-full">
        <!-- Header -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-retail-darkInk tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                <p class="text-xs text-slate-600">Departemen Finance, Accounting & Perpajakan</p>
                <h3 class="text-xs sm:text-sm font-bold uppercase text-slate-800 mt-2">REKAPITULASI BULANAN CASH ADVANCE & BIAYA FISKAL</h3>
            </div>
            <div class="text-right text-xs font-mono">
                <span class="font-bold text-retail-ink block text-sm">Periode: {{ \Carbon\Carbon::create(null, (int)$month, 1)->translatedFormat('F') }} {{ $year }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Total Dokumen: {{ $cas->count() }} CA</span>
            </div>
        </div>

        <!-- Metric Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6 font-mono">
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-500 uppercase font-bold font-sans block">Total Dana Dicairkan</span>
                <span class="text-sm sm:text-base font-bold text-slate-900 block mt-1">Rp {{ number_format($totalDana, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">CA: {{ number_format($totalCAInitial, 0, ',', '.') }}</span>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-500 uppercase font-bold font-sans block">Biaya Valid (Pajak)</span>
                <span class="text-sm sm:text-base font-bold text-emerald-700 block mt-1">Rp {{ number_format($totalValidExpense, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Struk lengkap & sah</span>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-500 uppercase font-bold font-sans block">Total Sisa Dana Di-Refund</span>
                <span class="text-sm sm:text-base font-bold text-purple-700 block mt-1">Rp {{ number_format($totalRefund, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Masuk rekening kantor</span>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-500 uppercase font-bold font-sans block">Outstanding / Belum Selesai</span>
                <span class="text-sm sm:text-base font-bold text-amber-800 block mt-1">{{ $cas->whereNotIn('status', ['CLOSED', 'REJECTED'])->count() }} Dokumen</span>
                <span class="text-[10px] text-slate-400 block mt-0.5 font-sans">Sedang berjalan</span>
            </div>
        </div>

        <!-- Breakdown Pengeluaran Fiskal per Kategori (Tabel Pajak) -->
        <div class="mb-6">
            <h4 class="text-xs font-bold uppercase tracking-wider text-retail-ink mb-2">
                Rincian Biaya Riil per Kategori (Untuk Pembukuan & Pelaporan SPT Masa)
            </h4>
            <div class="border border-slate-200 rounded-xl overflow-hidden table-responsive">
                <table class="w-full text-left text-xs font-mono min-w-[650px]">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase border-b border-retail-surface font-sans">
                        <tr>
                            <th class="px-3.5 py-2.5">Kategori Beban</th>
                            <th class="px-3.5 py-2.5">Keterangan Akun Pembukuan</th>
                            <th class="px-3.5 py-2.5 text-right">Total Valid (Rp)</th>
                            <th class="px-3.5 py-2.5 text-right">Porsi (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="px-3.5 py-2 font-bold text-slate-900 font-sans">Material & Perlengkapan</td>
                            <td class="px-3.5 py-2 text-slate-500 font-sans">Pembelian kabel, konektor, sparepart lokal proyek</td>
                            <td class="px-3.5 py-2 text-right font-bold text-slate-800">Rp {{ number_format($categories['material'], 0, ',', '.') }}</td>
                            <td class="px-3.5 py-2 text-right font-semibold text-slate-600">
                                {{ $totalValidExpense > 0 ? number_format(($categories['material'] / $totalValidExpense) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3.5 py-2 font-bold text-slate-900 font-sans">Transportasi & BBM / Tol</td>
                            <td class="px-3.5 py-2 text-slate-500 font-sans">BBM kendaraan operasional, struk tol, tiket perjalanan tim</td>
                            <td class="px-3.5 py-2 text-right font-bold text-slate-800">Rp {{ number_format($categories['transport'], 0, ',', '.') }}</td>
                            <td class="px-3.5 py-2 text-right font-semibold text-slate-600">
                                {{ $totalValidExpense > 0 ? number_format(($categories['transport'] / $totalValidExpense) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3.5 py-2 font-bold text-slate-900 font-sans">Sewa Alat & Kendaraan</td>
                            <td class="px-3.5 py-2 text-slate-500 font-sans">Sewa scaffolding, crane kecil, pickup lokal, alat ukur</td>
                            <td class="px-3.5 py-2 text-right font-bold text-slate-800">Rp {{ number_format($categories['sewa_alat'], 0, ',', '.') }}</td>
                            <td class="px-3.5 py-2 text-right font-semibold text-slate-600">
                                {{ $totalValidExpense > 0 ? number_format(($categories['sewa_alat'] / $totalValidExpense) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3.5 py-2 font-bold text-slate-900 font-sans">Konsumsi & Penginapan</td>
                            <td class="px-3.5 py-2 text-slate-500 font-sans">Hotel/losmen teknisi lapangan, makan dinas lembur</td>
                            <td class="px-3.5 py-2 text-right font-bold text-slate-800">Rp {{ number_format($categories['konsumsi'], 0, ',', '.') }}</td>
                            <td class="px-3.5 py-2 text-right font-semibold text-slate-600">
                                {{ $totalValidExpense > 0 ? number_format(($categories['konsumsi'] / $totalValidExpense) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        <tr>
                            <td class="px-3.5 py-2 font-bold text-slate-900 font-sans">Lain-lain Operasional</td>
                            <td class="px-3.5 py-2 text-slate-500 font-sans">Fotokopi berkas, perijinan kawasan industri, porter, dll</td>
                            <td class="px-3.5 py-2 text-right font-bold text-slate-800">Rp {{ number_format($categories['lainnya'], 0, ',', '.') }}</td>
                            <td class="px-3.5 py-2 text-right font-semibold text-slate-600">
                                {{ $totalValidExpense > 0 ? number_format(($categories['lainnya'] / $totalValidExpense) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        <tr class="bg-slate-50 font-bold border-t border-retail-surface">
                            <td colspan="2" class="px-3.5 py-2.5 text-retail-ink uppercase font-sans">Total Beban Riil Diakui</td>
                            <td class="px-3.5 py-2.5 text-right text-emerald-700 text-sm">Rp {{ number_format($totalValidExpense, 0, ',', '.') }}</td>
                            <td class="px-3.5 py-2.5 text-right text-retail-ink">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daftar CA Periode Ini -->
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-retail-ink mb-2">Daftar CA Bulan Terpilih</h4>
            <div class="border border-slate-200 rounded-xl overflow-hidden table-responsive">
                <table class="w-full text-left text-xs font-mono min-w-[700px]">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase border-b border-retail-surface font-sans">
                        <tr>
                            <th class="px-3 py-2.5">No. CA</th>
                            <th class="px-3 py-2.5">Karyawan</th>
                            <th class="px-3 py-2.5">Proyek</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                            <th class="px-3 py-2.5 text-right">CA Awal</th>
                            <th class="px-3 py-2.5 text-right">Top-Up</th>
                            <th class="px-3 py-2.5 text-right">Biaya Valid</th>
                            <th class="px-3 py-2.5 text-right">Refund Sisa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($cas as $c)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-bold text-retail-ink">{{ $c->ca_number }}</td>
                                <td class="px-3 py-2 font-sans">{{ $c->user->name }}</td>
                                <td class="px-3 py-2 font-sans">{{ $c->project_name }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span class="badge-retail text-[10px]">{{ $c->status }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">Rp {{ number_format($c->approved_amount ?: $c->requested_amount, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-emerald-700">+Rp {{ number_format($c->disbursed_topups_total, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-bold text-slate-800">Rp {{ number_format($c->valid_expenses_total, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-purple-700">Rp {{ number_format($c->settlement_refund_amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-slate-400 font-sans">Tidak ada transaksi Cash Advance pada periode bulan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Signs -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs pt-8 border-t border-retail-surface mt-8 font-sans">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Dibuat Oleh (Finance & Pajak)</span>
                <div class="h-16 flex items-end justify-center font-bold text-slate-900 border-b border-slate-300 pb-1">
                    {{ auth()->user()->isFinance() ? auth()->user()->name : 'Dewi Lestari' }}
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Admin Keuangan & Perpajakan</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold">Mengetahui & Menyetujui</span>
                <div class="h-16 flex items-end justify-center font-bold text-slate-900 border-b border-slate-300 pb-1">
                    Bambang Soedirgo
                </div>
                <span class="text-[10px] text-slate-400 block mt-1">Direktur Utama</span>
            </div>
        </div>
    </div>

</div>
@endsection