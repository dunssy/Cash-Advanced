@extends('layouts.app')

@section('title', 'Arsip Soft Delete Keuangan & Transaksi')
@section('page_title', 'Arsip Soft Delete (Data Terhapus)')

@section('content')
<div class="space-y-4" x-data="{ activeTab: '{{ in_array($tab, ['all', 'cash_advances', 'expenses', 'top_ups'], true) ? $tab : 'all' }}' }">

    <!-- Info Banner -->
    <div class="bg-[#050A1E] text-white rounded-[15px] p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#FF3C00]/20 border border-[#FF3C00]/40 flex items-center justify-center flex-shrink-0">
                <i data-lucide="archive" class="w-5 h-5 text-[#FF3C00]"></i>
            </div>
            <div>
                <h2 class="text-sm font-head font-bold text-white mb-0.5">Arsip Data Transaksi & Sektor Keuangan (Soft Delete)</h2>
                <p class="text-xs text-slate-300 leading-relaxed mb-0">
                    Seluruh data Cash Advance, Transaksi Pengeluaran, dan Top-Up yang dihapus dari frontend tetap tersimpan utuh di basis data backend beserta jejak pelaku dan alasannya, serta dapat dipulihkan kapan saja.
                </p>
            </div>
        </div>
        <a href="{{ route('cash-advances.index') }}" class="btn-secondary py-2 px-4 text-xs rounded-full whitespace-nowrap self-start sm:self-center">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar CA</span>
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="card-commerce p-4">
            <span class="block text-xs font-bold uppercase text-slate-500 font-head">Total Diarsipkan</span>
            <span class="block text-2xl font-mono font-bold text-[#050A1E] mt-1">{{ $counts['total'] }}</span>
        </div>
        <div class="card-commerce p-4">
            <span class="block text-xs font-bold uppercase text-slate-500 font-head">Cash Advance</span>
            <span class="block text-2xl font-mono font-bold text-[#FF3C00] mt-1">{{ $counts['cash_advances'] }}</span>
        </div>
        <div class="card-commerce p-4">
            <span class="block text-xs font-bold uppercase text-slate-500 font-head">Transaksi Pengeluaran</span>
            <span class="block text-2xl font-mono font-bold text-slate-800 mt-1">{{ $counts['expenses'] }}</span>
        </div>
        <div class="card-commerce p-4">
            <span class="block text-xs font-bold uppercase text-slate-500 font-head">Pengajuan Top-Up</span>
            <span class="block text-2xl font-mono font-bold text-slate-800 mt-1">{{ $counts['top_ups'] }}</span>
        </div>
    </div>

    <!-- Search & Tabs -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5">
            <button type="button" @click="activeTab = 'all'"
                :class="activeTab === 'all' ? 'bg-[#050A1E] text-white' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                class="px-3.5 py-1.5 rounded-full text-xs font-head font-bold transition">
                Semua ({{ $counts['total'] }})
            </button>
            <button type="button" @click="activeTab = 'cash_advances'"
                :class="activeTab === 'cash_advances' ? 'bg-[#050A1E] text-white' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                class="px-3.5 py-1.5 rounded-full text-xs font-head font-bold transition">
                Cash Advance ({{ $trashedCashAdvances->count() }})
            </button>
            <button type="button" @click="activeTab = 'expenses'"
                :class="activeTab === 'expenses' ? 'bg-[#050A1E] text-white' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                class="px-3.5 py-1.5 rounded-full text-xs font-head font-bold transition">
                Pengeluaran ({{ $trashedExpenses->count() }})
            </button>
            <button type="button" @click="activeTab = 'top_ups'"
                :class="activeTab === 'top_ups' ? 'bg-[#050A1E] text-white' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                class="px-3.5 py-1.5 rounded-full text-xs font-head font-bold transition">
                Top-Up ({{ $trashedTopUps->count() }})
            </button>
        </div>

        <form action="{{ route('soft-deletes.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" :value="activeTab">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}"
                    class="pl-9 input-commerce text-xs bg-white border border-slate-300 rounded-[12px]"
                    placeholder="Cari nomor CA, keterangan, alasan...">
            </div>
            <button type="submit" class="btn-dark py-2 px-4 text-xs rounded-full">Cari</button>
            @if($search !== '')
                <a href="{{ route('soft-deletes.index') }}" class="btn-secondary py-2 px-3.5 text-xs rounded-full">Reset</a>
            @endif
        </form>
    </div>

    <!-- Section 1: Cash Advances Terhapus -->
    <div x-show="activeTab === 'all' || activeTab === 'cash_advances'" class="card-commerce overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-head font-bold uppercase tracking-wider text-[#050A1E] flex items-center gap-2">
                <i data-lucide="briefcase" class="w-4 h-4 text-[#FF3C00]"></i>
                <span>Cash Advance Terhapus ({{ $trashedCashAdvances->count() }})</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs table-technodrives">
                <thead>
                    <tr>
                        <th class="py-2.5 px-4">No. CA & Proyek</th>
                        <th class="py-2.5 px-4">Pemohon</th>
                        <th class="py-2.5 px-4 text-right">Nominal</th>
                        <th class="py-2.5 px-4 text-center">Status Terakhir</th>
                        <th class="py-2.5 px-4">Info Soft Delete</th>
                        <th class="py-2.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($trashedCashAdvances as $ca)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-[#050A1E] block">{{ $ca->ca_number }}</span>
                                <span class="text-slate-700 font-medium block">{{ $ca->project_name }}</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800 block">{{ $ca->user?->name ?? '-' }}</span>
                                <span class="text-slate-500">{{ $ca->user?->department ?: '-' }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($ca->approved_amount ?: $ca->requested_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="badge-retail bg-slate-100 text-slate-700 border-slate-300">{{ $ca->status }}</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <span class="block text-slate-800 font-semibold">Oleh: {{ $ca->deleter?->name ?? 'System' }}</span>
                                <span class="block font-mono text-slate-500">{{ $ca->deleted_at?->format('d/m/Y H:i') }}</span>
                                @if($ca->delete_reason)
                                    <span class="block text-rose-700 italic mt-0.5">"{{ $ca->delete_reason }}"</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <form action="{{ route('soft-deletes.cash-advances.restore', $ca->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-success text-xs py-1.5 px-3.5 rounded-full inline-flex items-center gap-1.5">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>Pulihkan</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data Cash Advance yang berstatus soft delete.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Transaksi Pengeluaran Terhapus -->
    <div x-show="activeTab === 'all' || activeTab === 'expenses'" class="card-commerce overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-head font-bold uppercase tracking-wider text-[#050A1E] flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-[#FF3C00]"></i>
                <span>Transaksi Pengeluaran Terhapus ({{ $trashedExpenses->count() }})</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs table-technodrives">
                <thead>
                    <tr>
                        <th class="py-2.5 px-4">Tanggal & CA</th>
                        <th class="py-2.5 px-4">Keterangan & Vendor</th>
                        <th class="py-2.5 px-4 text-right">Nominal</th>
                        <th class="py-2.5 px-4 text-center">Status Verifikasi</th>
                        <th class="py-2.5 px-4">Info Soft Delete</th>
                        <th class="py-2.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($trashedExpenses as $exp)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-900 block">{{ $exp->transaction_date?->format('d/m/Y') }}</span>
                                <span class="font-mono text-slate-600 block">
                                    {{ $exp->cashAdvance?->ca_number ?? '-' }}
                                    @if($exp->cashAdvance?->trashed())
                                        <span class="text-rose-600 font-sans">(CA Terhapus)</span>
                                    @endif
                                </span>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <span class="font-bold text-slate-900 block">{{ $exp->description }}</span>
                                <span class="text-slate-500 block">{{ $exp->vendor_name }} &bull; {{ $exp->category_label }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($exp->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="badge-retail bg-slate-100 text-slate-700 border-slate-300">{{ $exp->verification_status }}</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <span class="block text-slate-800 font-semibold">Oleh: {{ $exp->deleter?->name ?? 'System' }}</span>
                                <span class="block font-mono text-slate-500">{{ $exp->deleted_at?->format('d/m/Y H:i') }}</span>
                                @if($exp->delete_reason)
                                    <span class="block text-rose-700 italic mt-0.5">"{{ $exp->delete_reason }}"</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <form action="{{ route('soft-deletes.expenses.restore', $exp->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-success text-xs py-1.5 px-3.5 rounded-full inline-flex items-center gap-1.5">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>Pulihkan</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada transaksi pengeluaran yang berstatus soft delete.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Pengajuan Top-Up Terhapus -->
    <div x-show="activeTab === 'all' || activeTab === 'top_ups'" class="card-commerce overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-head font-bold uppercase tracking-wider text-[#050A1E] flex items-center gap-2">
                <i data-lucide="arrow-up-circle" class="w-4 h-4 text-[#FF3C00]"></i>
                <span>Pengajuan Top-Up Terhapus ({{ $trashedTopUps->count() }})</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs table-technodrives">
                <thead>
                    <tr>
                        <th class="py-2.5 px-4">No. CA & Pemohon</th>
                        <th class="py-2.5 px-4">Alasan Top-Up</th>
                        <th class="py-2.5 px-4 text-right">Nominal</th>
                        <th class="py-2.5 px-4 text-center">Status Terakhir</th>
                        <th class="py-2.5 px-4">Info Soft Delete</th>
                        <th class="py-2.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($trashedTopUps as $tu)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-900 block">{{ $tu->cashAdvance?->ca_number ?? '-' }}</span>
                                <span class="text-slate-600 block">{{ $tu->user?->name ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs text-slate-700">{{ $tu->reason }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($tu->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="badge-retail bg-slate-100 text-slate-700 border-slate-300">{{ $tu->status }}</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs">
                                <span class="block text-slate-800 font-semibold">Oleh: {{ $tu->deleter?->name ?? 'System' }}</span>
                                <span class="block font-mono text-slate-500">{{ $tu->deleted_at?->format('d/m/Y H:i') }}</span>
                                @if($tu->delete_reason)
                                    <span class="block text-rose-700 italic mt-0.5">"{{ $tu->delete_reason }}"</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <form action="{{ route('soft-deletes.top-ups.restore', $tu->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-success text-xs py-1.5 px-3.5 rounded-full inline-flex items-center gap-1.5">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>Pulihkan</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada pengajuan Top-Up yang berstatus soft delete.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
