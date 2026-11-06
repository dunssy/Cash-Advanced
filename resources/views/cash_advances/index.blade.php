@extends('layouts.app')

@section('title', 'Daftar Cash Advance')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Cash Advance</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                @if(auth()->user()->isKaryawan())
                    Menampilkan seluruh Cash Advance milik Anda.
                @elseif(auth()->user()->isAtasan())
                    Menampilkan Cash Advance milik Anda dan tim bawahan langsung.
                @else
                    Menampilkan seluruh Cash Advance perusahaan (PT Technodrives).
                @endif
            </p>
        </div>

        @if(auth()->user()->canSubmitCashAdvance())
            <a href="{{ route('cash-advances.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Ajukan CA Baru</span>
            </a>
        @endif
    </div>

    <!-- Filter Status Tabs & Search -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 space-y-3">
        <!-- Status Tabs -->
        <div class="flex items-center space-x-1 overflow-x-auto pb-1 text-xs font-semibold">
            @php $currentStatus = request('status'); @endphp
            <a href="{{ route('cash-advances.index') }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ !$currentStatus ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Semua
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ $currentStatus === 'PENDING_APPROVAL' ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Menunggu Approval
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'ACTIVE']) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ $currentStatus === 'ACTIVE' ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Aktif (Berjalan)
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'NEED_SETTLEMENT']) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ $currentStatus === 'NEED_SETTLEMENT' ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Butuh Refund Sisa
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'PENDING_VERIFICATION']) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ $currentStatus === 'PENDING_VERIFICATION' ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Verifikasi Finance
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'CLOSED']) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ $currentStatus === 'CLOSED' ? 'bg-brand-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Ditutup (CLOSED)
            </a>
            <a href="{{ route('cash-advances.index', ['status' => 'OVERDUE']) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ $currentStatus === 'OVERDUE' ? 'bg-rose-600 text-white font-bold' : 'text-rose-600 hover:bg-rose-50' }}">
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
                    class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none"
                    placeholder="Cari nomor CA, nama proyek, atau nama karyawan...">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl">
                Cari
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('cash-advances.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
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
                            <td class="px-4 py-3 font-bold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('cash-advances.show', $ca) }}" class="hover:text-sky-600">
                                    {{ $ca->ca_number }}
                                </a>
                                @if($ca->is_unlocked)
                                    <span class="block text-[9px] text-amber-600 font-semibold">Override Direktur</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-medium text-slate-800 block">{{ $ca->user->name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $ca->user->department }}</span>
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <span class="font-medium text-slate-900 line-clamp-1">{{ $ca->project_name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $ca->project_code ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-700 whitespace-nowrap">
                                Rp {{ number_format($ca->approved_amount ?: $ca->requested_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold whitespace-nowrap
                                {{ $ca->current_balance > 0 ? 'text-sky-600' : 'text-slate-400' }}
                            ">
                                Rp {{ number_format($ca->current_balance, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                    {{ $ca->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $ca->status === 'PENDING_APPROVAL' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $ca->status === 'NEED_SETTLEMENT' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $ca->status === 'PENDING_VERIFICATION' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $ca->status === 'CLOSED' ? 'bg-slate-100 text-slate-700' : '' }}
                                    {{ $ca->status === 'REJECTED' ? 'bg-rose-100 text-rose-800' : '' }}
                                ">
                                    {{ $ca->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                    @if($ca->is_overdue)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-700">
                                            OVERDUE
                                        </span>
                                    @else
                                        <span class="text-slate-700 font-semibold">{{ $ca->days_remaining }} Hari</span>
                                    @endif
                                @elseif($ca->status === 'CLOSED')
                                    <span class="text-emerald-600 font-semibold">Tuntas</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <a href="{{ route('cash-advances.show', $ca) }}" class="px-3 py-1 bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 font-bold rounded-lg transition inline-flex items-center space-x-1">
                                    <span>Detail</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="folder-search" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Tidak ada data Cash Advance ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cashAdvances->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $cashAdvances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
