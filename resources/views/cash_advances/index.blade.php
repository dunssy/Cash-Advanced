@extends('layouts.app')

@section('title', 'Daftar Cash Advance')

@section('content')
<div class="space-y-6">

    <!-- Print Only Header -->
    <div class="hidden print:block mb-6 border-b-2 border-[#050A1E] pb-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('assets/images/logo.png') }}" alt="PT Technodrives" class="h-8 w-auto">
                <div>
                    <h1 class="text-base font-bold font-head text-[#050A1E]">PT TECHNODRIVES INDONESIA</h1>
                    <h2 class="text-xs font-semibold font-head text-[#FF3C00]">Laporan {{ auth()->user()->isKaryawan() ? 'Cash Advance Saya' : 'Daftar Cash Advance' }}</h2>
                </div>
            </div>
            <div class="text-right text-[11px] text-[#7A7A7A] font-body">
                <p>Dicetak: {{ date('d/m/Y H:i') }} WIB</p>
                <p>Oleh: <strong>{{ auth()->user()->name }}</strong> ({{ strtoupper(auth()->user()->role) }})</p>
                @if(request('status'))<p class="font-bold text-[#050A1E]">Filter Status: {{ request('status') }}</p>@endif
            </div>
        </div>
    </div>

    <!-- Header Screen -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight">
                {{ auth()->user()->isKaryawan() ? 'Cash Advance Saya' : 'Daftar Cash Advance' }}
            </h1>
            <p class="text-xs text-bodygrey mt-0.5 font-body">
                @if(auth()->user()->isKaryawan())
                    Menampilkan seluruh riwayat Cash Advance milik Anda.
                @elseif(auth()->user()->isAtasan())
                    Menampilkan Cash Advance milik Anda dan tim bawahan langsung.
                @else
                    Menampilkan seluruh Cash Advance perusahaan (PT Technodrives Indonesia).
                @endif
            </p>
        </div>

        @if(auth()->user()->isKaryawan() || auth()->user()->isAtasan())
            <a href="{{ route('cash-advances.create') }}" class="btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Ajukan CA Baru</span>
            </a>
        @endif
    </div>

    <!-- Filter Status & Search Form (Ringkas & Dropdown) -->
    <div class="card-commerce p-4 no-print">
        <form action="{{ route('cash-advances.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full pl-9 input-commerce text-xs sm:text-sm"
                    placeholder="Cari nomor CA, nama proyek, atau nama karyawan...">
            </div>

            <div class="w-full sm:w-56 flex-shrink-0">
                <select name="status" onchange="this.form.submit()" class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                    <option value="">Semua Status</option>
                    <option value="PENDING_APPROVAL" {{ request('status') === 'PENDING_APPROVAL' ? 'selected' : '' }}>Menunggu Approval / Pencairan</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Aktif (Berjalan)</option>
                    <option value="NEED_SETTLEMENT" {{ request('status') === 'NEED_SETTLEMENT' ? 'selected' : '' }}>Butuh Settlement</option>
                    <option value="PENDING_VERIFICATION" {{ request('status') === 'PENDING_VERIFICATION' ? 'selected' : '' }}>Verifikasi Finance</option>
                    <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>Ditutup (CLOSED)</option>
                    <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>OVERDUE</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="btn-dark py-2.5 px-4 flex-1 sm:flex-initial text-xs">
                    Cari
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('cash-advances.index') }}" class="btn-secondary py-2.5 px-3 text-xs">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table & Mobile Cards -->
    <div class="card-commerce overflow-hidden">
        <!-- Mobile Card List View (md:hidden - Tidak perlu geser horizontal) -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($cashAdvances as $ca)
                <a href="{{ route('cash-advances.show', $ca) }}" class="block p-4 bg-white hover:bg-slate-50 transition space-y-3 group">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-head font-bold text-xs text-white bg-[#050A1E] px-2.5 py-1 rounded-md">
                            {{ $ca->ca_number }}
                        </span>
                        <span class="badge-retail text-[10px]
                            {{ $ca->status === 'PENDING_APPROVAL' ? ($ca->approved_by ? 'bg-sky-50 text-sky-800 border-sky-300' : 'badge-pending-approval') : '' }}
                            {{ $ca->status === 'REJECTED' ? 'badge-rejected' : '' }}
                            {{ $ca->status === 'ACTIVE' ? 'badge-active' : '' }}
                            {{ $ca->status === 'NEED_SETTLEMENT' ? 'badge-need-settlement' : '' }}
                            {{ $ca->status === 'PENDING_VERIFICATION' ? 'badge-pending-verification' : '' }}
                            {{ $ca->status === 'CLOSED' ? 'badge-closed' : '' }}
                        ">
                            @if($ca->status === 'PENDING_APPROVAL')
                                {{ $ca->approved_by ? 'Disetujui • Menunggu Pencairan' : 'Menunggu Approval' }}
                            @endif
                            @if($ca->status === 'REJECTED') Ditolak @endif
                            @if($ca->status === 'ACTIVE') Aktif @endif
                            @if($ca->status === 'NEED_SETTLEMENT') Wajib Settlement @endif
                            @if($ca->status === 'PENDING_VERIFICATION') Verifikasi Akhir @endif
                            @if($ca->status === 'CLOSED') Closed / Selesai @endif
                        </span>
                    </div>

                    <div>
                        <h3 class="font-head font-bold text-sm text-[#050A1E] leading-snug">{{ $ca->project_name }}</h3>
                        <div class="flex items-center space-x-2 text-xs text-[#7A7A7A] mt-1 font-body">
                            <span class="font-medium text-slate-800">{{ $ca->user->name }}</span>
                            <span>&bull;</span>
                            <span>{{ $ca->user->department ?: 'Staff' }}</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-[10px] font-head text-[#7A7A7A] uppercase font-bold">Sisa Saldo Kas</span>
                        <span class="font-head font-extrabold text-sm {{ $ca->status === 'CLOSED' ? 'text-emerald-700' : ($ca->current_balance > 0 ? 'text-[#FF3C00]' : 'text-slate-800') }}">
                            @if($ca->status === 'CLOSED')
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5 stroke-[2.5] text-emerald-600"></i>
                                    <span>Rp 0 (Lunas)</span>
                                </span>
                            @else
                                Rp {{ number_format($ca->current_balance, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100 font-head">
                        <div>
                            @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                @if($ca->is_overdue)
                                    <span class="badge-retail bg-rose-100 text-rose-800 border-rose-300 font-bold text-[10px]">OVERDUE</span>
                                @else
                                    <span class="text-xs text-slate-600">Durasi: <strong class="text-slate-900 font-mono">{{ $ca->days_remaining }} hari</strong></span>
                                @endif
                            @elseif($ca->status === 'CLOSED')
                                <span class="text-xs text-slate-500 font-semibold inline-flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Selesai</span>
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#050A1E] text-white text-xs font-head font-bold shadow-sm group-hover:bg-[#FF3C00] transition">
                            <span>Buka CA</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </div>
                </a>
            @empty
                <div class="py-12 text-center text-[#7A7A7A] text-sm font-body">
                    Tidak ada data Cash Advance yang sesuai kriteria pencarian.
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View (hidden md:block) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm table-technodrives">
                <thead>
                    <tr>
                        @if(!auth()->user()->isKaryawan())
                            <th class="whitespace-nowrap">No. CA</th>
                            <th class="whitespace-nowrap">Pemohon</th>
                        @endif
                        <th>Proyek</th>
                        <th class="text-right whitespace-nowrap">Sisa Saldo</th>
                        <th class="text-center whitespace-nowrap">Durasi</th>
                        <th class="text-center whitespace-nowrap">Status</th>
                        <th class="text-center whitespace-nowrap no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashAdvances as $ca)
                        <tr class="hover:bg-slate-50/80 transition">
                            @if(!auth()->user()->isKaryawan())
                                <td class="font-head font-bold text-[#050A1E] whitespace-nowrap">
                                    {{ $ca->ca_number }}
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="font-head font-bold text-[#050A1E] block">{{ $ca->user->name }}</span>
                                    <span class="block text-xs text-[#7A7A7A] font-body">{{ $ca->user->department }}</span>
                                </td>
                            @endif
                            <td>
                                @if(auth()->user()->isKaryawan())
                                    <div class="flex items-center space-x-2">
                                        <span class="font-head font-bold text-[11px] text-white bg-[#050A1E] px-2 py-0.5 rounded-md inline-block whitespace-nowrap">
                                            {{ $ca->ca_number }}
                                        </span>
                                        <span class="font-head font-bold text-sm text-[#050A1E] block truncate max-w-sm">{{ $ca->project_name }}</span>
                                    </div>
                                    @if($ca->project_code)
                                        <span class="block text-xs font-mono text-[#7A7A7A] mt-0.5">{{ $ca->project_code }}</span>
                                    @endif
                                @else
                                    <span class="font-medium text-[#050A1E] block">{{ $ca->project_name }}</span>
                                    <span class="block text-xs font-head text-[#7A7A7A]">{{ $ca->project_code ?: '-' }}</span>
                                @endif
                            </td>
                            <td class="text-right font-head font-extrabold whitespace-nowrap {{ $ca->status === 'CLOSED' || $ca->current_balance == 0 ? 'text-emerald-700' : 'text-[#FF3C00]' }}">
                                @if($ca->status === 'CLOSED')
                                    <span class="inline-flex items-center gap-1 text-emerald-700">
                                        <i data-lucide="check" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                                        <span>Rp 0 (Lunas)</span>
                                    </span>
                                @elseif($ca->current_balance == 0 && ($ca->status === 'ACTIVE' || $ca->status === 'PENDING_VERIFICATION'))
                                    <span class="text-slate-600 font-bold">Rp 0</span>
                                @else
                                    Rp {{ number_format($ca->current_balance, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="text-center font-head whitespace-nowrap">
                                @if($ca->status === 'ACTIVE' || $ca->status === 'NEED_SETTLEMENT')
                                    @if($ca->is_overdue)
                                        <span class="badge-retail badge-rejected">OVERDUE</span>
                                    @else
                                        <span class="text-[#050A1E] font-semibold text-xs">{{ $ca->days_remaining }} hr</span>
                                    @endif
                                @elseif($ca->status === 'CLOSED')
                                    <span class="text-[#7A7A7A] text-xs">Selesai</span>
                                @else
                                    <span class="text-[#7A7A7A] text-xs">-</span>
                                @endif
                            </td>
                            <td class="text-center whitespace-nowrap">
                                <span class="badge-retail
                                    {{ $ca->status === 'PENDING_APPROVAL' ? ($ca->approved_by ? 'bg-sky-50 text-sky-800 border-sky-300' : 'badge-pending-approval') : '' }}
                                    {{ $ca->status === 'REJECTED' ? 'badge-rejected' : '' }}
                                    {{ $ca->status === 'ACTIVE' ? 'badge-active' : '' }}
                                    {{ $ca->status === 'NEED_SETTLEMENT' ? 'badge-need-settlement' : '' }}
                                    {{ $ca->status === 'PENDING_VERIFICATION' ? 'badge-pending-verification' : '' }}
                                    {{ $ca->status === 'CLOSED' ? 'badge-closed' : '' }}
                                ">
                                    @if($ca->status === 'PENDING_APPROVAL')
                                        {{ $ca->approved_by ? 'Disetujui • Menunggu Pencairan' : 'Menunggu Approval' }}
                                    @endif
                                    @if($ca->status === 'REJECTED') Ditolak @endif
                                    @if($ca->status === 'ACTIVE') Aktif @endif
                                    @if($ca->status === 'NEED_SETTLEMENT') Wajib Settlement @endif
                                    @if($ca->status === 'PENDING_VERIFICATION') Verifikasi Akhir @endif
                                    @if($ca->status === 'CLOSED') Closed / Selesai @endif
                                </span>
                            </td>
                            <td class="text-center whitespace-nowrap no-print">
                                <a href="{{ route('cash-advances.show', $ca) }}" class="btn-secondary py-1.5 px-4 text-xs inline-flex items-center space-x-1">
                                    <span>Detail</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isKaryawan() ? 5 : 7 }}" class="py-12 text-center text-[#7A7A7A] text-sm font-body">
                                Tidak ada data Cash Advance yang sesuai kriteria pencarian.
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