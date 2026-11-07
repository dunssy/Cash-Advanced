@extends('layouts.app')

@section('title', 'Daftar Cash Advance')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Daftar Cash Advance</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                @if(auth()->user()->isKaryawan())
                    Menampilkan seluruh Cash Advance milik Anda.
                @elseif(auth()->user()->isAtasan())
                    Menampilkan Cash Advance milik Anda dan tim bawahan langsung.
                @else
                    Menampilkan seluruh Cash Advance perusahaan (PT Technodrives Indonesia).
                @endif
            </p>
        </div>

        @if(auth()->user()->canSubmitCashAdvance())
            <a href="{{ route('cash-advances.create') }}" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Ajukan CA Baru</span>
            </a>
        @endif
    </div>

    <!-- Filter Status Tabs & Search -->
    <div class="card-commerce p-4 space-y-3">
        <!-- Status Tabs -->
        <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 text-xs font-semibold">
            @php $currentStatus = request('status'); @endphp
            <a href="{{ route('cash-advances.index') }}" class="px-3 py-1.5 rounded-[3px] whitespace-nowrap transition {{ !$currentStatus ? 'bg-retail-ink text-retail-orange font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Semua
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="px-3 py-1.5 rounded-[3px] whitespace-nowrap transition {{ $currentStatus === 'PENDING_APPROVAL' ? 'bg-retail-ink text-retail-orange font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Menunggu Approval
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'ACTIVE']) }}" class="px-3 py-1.5 rounded-[3px] whitespace-nowrap transition {{ $currentStatus === 'ACTIVE' ? 'bg-retail-ink text-retail-orange font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Aktif (Berjalan)
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'NEED_SETTLEMENT']) }}" class="px-3 py-1.5 rounded-[3px] whitespace-nowrap transition {{ $currentStatus === 'NEED_SETTLEMENT' ? 'bg-retail-ink text-retail-orange font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Butuh Refund Sisa
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'PENDING_VERIFICATION']) }}" class="px-3 py-1.5 rounded-[3px] whitespace-nowrap transition {{ $currentStatus === 'PENDING_VERIFICATION' ? 'bg-retail-ink text-retail-orange font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Verifikasi Finance
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'CLOSED']) }}" class="px-3 py-1.5 rounded-[3px] whitespace-nowrap transition {{ $currentStatus === 'CLOSED' ? 'bg-retail-ink text-retail-orange font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                Ditutup (CLOSED)
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'OVERDUE']) }}" class="px-3 py-1.5 rounded-[3px] whitespace-nowrap transition {{ $currentStatus === 'OVERDUE' ? 'bg-rose-700 text-white font-bold' : 'text-rose-600 hover:bg-rose-50' }}">
                OVERDUE
            </a>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('cash-advances.index') }}" method="GET" class="flex items-center space-x-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full pl-9 input-commerce"
                    placeholder="Cari nomor CA, nama proyek, atau nama karyawan...">
            </div>
            <button type="submit" class="btn-dark py-2 px-4">
                Cari
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('cash-advances.index') }}" class="btn-secondary py-2 px-3 text-xs">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table (Design.md: card commerce, 1px border #DDDDDD) -->
    <div class="card-commerce overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-retail-surface">
                    <tr>
                        <th class="px-4 py-3.5">No. CA</th>
                        <th class="px-4 py-3.5">Pemohon</th>
                        <th class="px-4 py-3.5">Proyek</th>
                        <th class="px-4 py-3.5 text-right">CA Awal</th>
                        <th class="px-4 py-3.5 text-right">Sisa Saldo</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Durasi / Sisa</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cashAdvances as $ca)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3.5 font-mono font-bold text-retail-ink">
                                {{ $ca->ca_number }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-slate-900">{{ $ca->user->name }}</span>
                                <span class="block text-[10px] text-slate-500">{{ $ca->user->department }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-medium text-slate-900">{{ $ca->project_name }}</span>
                                <span class="block text-[10px] font-mono text-slate-400">{{ $ca->project_code ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-800">
                                Rp {{ number_format($ca->approved_amount ?: $ca->requested_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-extrabold {{ $ca->current_balance > 0 ? 'text-retail-ink' : 'text-slate-400' }}">
                                Rp {{ number_format($ca->current_balance, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="badge-retail
                                    {{ $ca->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : '' }}
                                    {{ $ca->status === 'PENDING_APPROVAL' ? 'bg-sky-50 text-sky-800 border-sky-300' : '' }}
                                    {{ $ca->status === 'NEED_SETTLEMENT' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                                    {{ $ca->status === 'PENDING_VERIFICATION' ? 'bg-purple-50 text-purple-800 border-purple-300' : '' }}
                                    {{ $ca->status === 'CLOSED' ? 'bg-slate-100 text-slate-700 border-slate-300' : '' }}
                                    {{ $ca->status === 'REJECTED' ? 'bg-rose-50 text-rose-800 border-rose-300' : '' }}
                                ">
                                    {{ $ca->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono">
                                @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                    @if($ca->is_overdue)
                                        <span class="badge-retail bg-rose-100 text-rose-700 border-rose-300 font-bold">OVERDUE</span>
                                    @else
                                        <span class="text-slate-700 font-semibold">{{ $ca->days_remaining }} hr</span>
                                    @endif
                                @elseif($ca->status === 'CLOSED')
                                    <span class="text-slate-400">Lunas</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <a href="{{ route('cash-advances.show', $ca) }}" class="btn-secondary py-1.5 px-3 text-xs">
                                    <span>Detail</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                Tidak ada data Cash Advance yang sesuai kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cashAdvances->hasPages())
            <div class="p-4 bg-slate-50 border-t border-retail-surface">
                {{ $cashAdvances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection