@extends('layouts.app')

@section('title', 'Laporan 1: Rincian per CA')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Laporan 1: Rincian per Cash Advance</h1>
            <p class="text-xs text-slate-500 mt-0.5">Untuk Finance, Atasan, dan Karyawan. Rincian lengkap buku saldo dan bukti struk per proyek.</p>
        </div>

        @if($selectedCA)
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.rincian-ca', ['ca_id' => $selectedCA->id, 'export' => 'csv']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center space-x-1.5">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Ekspor CSV / Excel</span>
                </a>
                <button onclick="window.print()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center space-x-1.5">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak Dokumen</span>
                </button>
            </div>
        @endif
    </div>

    <!-- CA Selector (No Print) -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 no-print">
        <form action="{{ route('reports.rincian-ca') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <label class="text-xs font-bold text-slate-700 uppercase whitespace-nowrap">Pilih Cash Advance:</label>
            <select name="ca_id" onchange="this.form.submit()" class="w-full sm:max-w-md px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl font-medium">
                @foreach($allCAs as $ca)
                    <option value="{{ $ca->id }}" {{ $selectedCA && $selectedCA->id === $ca->id ? 'selected' : '' }}>
                        {{ $ca->ca_number }} - {{ $ca->project_name }} ({{ $ca->user->name }}) [{{ $ca->status }}]
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl">
                Tampilkan
            </button>
        </form>
    </div>

    @if($selectedCA)
        <!-- Printable Report Sheet -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8 print-full">
            <!-- Company Letterhead Header -->
            <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">PT TECHNODRIVES INDONESIA</h2>
                    <p class="text-xs text-slate-600">Engineering, Drives & Automation Systems</p>
                    <p class="text-[11px] text-slate-500">Jl. Rungkut Industri Raya No. 45, Surabaya, Jawa Timur</p>
                </div>
                <div class="text-right">
                    <h3 class="text-sm font-extrabold uppercase text-slate-800">LEMBAR PERTANGGUNGJAWABAN CASH ADVANCE</h3>
                    <span class="text-xs font-bold text-sky-700 block mt-0.5">{{ $selectedCA->ca_number }}</span>
                    <span class="text-[10px] text-slate-400 block">Dicetak: {{ date('d M Y, H:i') }}</span>
                </div>
            </div>

            <!-- Project & Personnel Details -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs mb-6">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama Karyawan</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $selectedCA->user->name }}</span>
                    <span class="text-slate-500 block text-[11px]">{{ $selectedCA->user->department }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Proyek & Kode</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $selectedCA->project_name }}</span>
                    <span class="text-slate-500 block text-[11px]">{{ $selectedCA->project_code ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Tanggal Dicairkan</span>
                    <span class="font-bold text-slate-800">{{ $selectedCA->disbursed_at ? $selectedCA->disbursed_at->format('d/m/Y') : '-' }}</span>
                    <span class="text-slate-500 block text-[11px]">Ref: {{ $selectedCA->disbursement_ref ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Status CA Saat Ini</span>
                    <span class="font-extrabold uppercase text-xs inline-block mt-0.5 px-2 py-0.5 rounded bg-white border border-slate-300">
                        {{ $selectedCA->status }}
                    </span>
                </div>
            </div>

            <!-- Summary Table of Balances -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center mb-6">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block">CA Awal</span>
                    <span class="text-sm font-bold text-slate-900 block mt-1">Rp {{ number_format($selectedCA->approved_amount ?: $selectedCA->requested_amount, 0, ',', '.') }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block">Top-Up Cair (+)</span>
                    <span class="text-sm font-bold text-emerald-600 block mt-1">+Rp {{ number_format($selectedCA->disbursed_topups_total, 0, ',', '.') }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block">Pengeluaran (-)</span>
                    <span class="text-sm font-bold text-rose-600 block mt-1">-Rp {{ number_format($selectedCA->recorded_expenses_total, 0, ',', '.') }}</span>
                </div>
                <div class="p-3 bg-sky-50 rounded-xl border-2 border-sky-300">
                    <span class="text-[10px] text-sky-800 uppercase font-bold block">Sisa Saldo Kas</span>
                    <span class="text-base font-extrabold text-sky-700 block mt-0.5">Rp {{ number_format($selectedCA->current_balance, 0, ',', '.') }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold block">Talangan Disetujui</span>
                    <span class="text-sm font-bold text-amber-700 block mt-1">Rp {{ number_format($selectedCA->approved_talangan_total, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Expense List Table -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden mb-6">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-2.5">No</th>
                            <th class="px-3 py-2.5">Tanggal</th>
                            <th class="px-3 py-2.5">Keterangan</th>
                            <th class="px-3 py-2.5">Kategori</th>
                            <th class="px-3 py-2.5">Vendor</th>
                            <th class="px-3 py-2.5 text-right">Nominal</th>
                            <th class="px-3 py-2.5 text-center">Tipe</th>
                            <th class="px-3 py-2.5 text-center">Ketepatan</th>
                            <th class="px-3 py-2.5 text-center">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($selectedCA->expenses as $idx => $exp)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 text-slate-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $exp->transaction_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $exp->description }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $exp->category_label }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ $exp->vendor_name }}</td>
                                <td class="px-3 py-2 text-right font-bold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($exp->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="text-[10px] font-semibold {{ $exp->is_talangan ? 'text-amber-700 font-bold' : 'text-slate-600' }}">
                                        {{ $exp->is_talangan ? 'Talangan' : 'Kas CA' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    @if($exp->is_late)
                                        <span class="text-rose-600 font-bold text-[10px]">TERLAMBAT</span>
                                    @else
                                        <span class="text-emerald-600 text-[10px]">Tepat Waktu</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center whitespace-nowrap">
                                    <span class="text-[10px] font-bold uppercase
                                        {{ $exp->verification_status === 'VALID' ? 'text-emerald-700 font-extrabold' : '' }}
                                        {{ $exp->verification_status === 'PENDING' ? 'text-blue-600' : '' }}
                                        {{ $exp->verification_status === 'NEEDS_REVISION' ? 'text-amber-600' : '' }}
                                        {{ $exp->verification_status === 'REJECTED' ? 'text-rose-600' : '' }}
                                        {{ $exp->verification_status === 'VOID' ? 'text-slate-400 line-through' : '' }}
                                    ">
                                        {{ $exp->verification_status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-6 text-center text-slate-400">Belum ada pengeluaran tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Signatures / Separation of Duties Audit Box -->
            <div class="grid grid-cols-4 gap-4 text-center text-xs pt-4 border-t border-slate-200">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Pemohon</span>
                    <div class="h-16 flex items-end justify-center font-bold text-slate-900 border-b border-slate-300 pb-1">
                        {{ $selectedCA->user->name }}
                    </div>
                    <span class="text-[10px] text-slate-400 block mt-1">Karyawan / Teknisi</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Menyetujui (Atasan)</span>
                    <div class="h-16 flex items-end justify-center font-bold text-slate-900 border-b border-slate-300 pb-1">
                        {{ $selectedCA->approver?->name ?: '(Belum Disetujui)' }}
                    </div>
                    <span class="text-[10px] text-slate-400 block mt-1">Manager Operasional / Proyek</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Mencairkan Dana</span>
                    <div class="h-16 flex items-end justify-center font-bold text-slate-900 border-b border-slate-300 pb-1">
                        {{ $selectedCA->disburser?->name ?: '(Belum Cair)' }}
                    </div>
                    <span class="text-[10px] text-slate-400 block mt-1">Finance & Kasir</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Verifikasi & Penutupan</span>
                    <div class="h-16 flex items-end justify-center font-bold text-slate-900 border-b border-slate-300 pb-1">
                        {{ $selectedCA->closer?->name ?: '(Menunggu Verifikasi)' }}
                    </div>
                    <span class="text-[10px] text-slate-400 block mt-1">Finance / Direktur</span>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
