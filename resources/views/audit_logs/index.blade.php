@extends('layouts.app')

@section('title', 'Audit Trail Sistem')
@section('page_title', 'Audit Trail Sistem')

@section('content')
<div class="space-y-4">

    <!-- Filter & Search (Clean & Tanpa Background) -->
    <div class="no-print">
        <form action="{{ route('audit-logs.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-2.5">
            <!-- Search -->
            <div class="relative flex-grow">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full pl-10 input-commerce text-xs sm:text-sm bg-white border border-slate-300 rounded-[12px] shadow-2xs focus:border-[#FF3C00]"
                    placeholder="Cari deskripsi aksi, nama pengguna, IP address...">
            </div>

            <!-- Action Filter Dropdown (Otomatis onchange) -->
            <div class="w-full sm:w-64 flex-shrink-0">
                <select name="action" onchange="this.form.submit()" class="w-full input-commerce text-xs font-head font-semibold text-slate-800 bg-white border border-slate-300 rounded-[12px] shadow-2xs">
                    <option value="">Semua Jenis Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            @if(request('search') || request('action'))
                <div class="flex-shrink-0">
                    <a href="{{ route('audit-logs.index') }}" class="btn-secondary btn-sm px-4 py-2 text-xs rounded-full shadow-2xs inline-flex items-center gap-1 font-head font-semibold">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset</span>
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Audit Logs Container -->
    <div class="card-commerce overflow-hidden">
        <!-- Mobile Card List View (Visible on mobile md:hidden - Mobile-first no horizontal scrolling) -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($logs as $log)
                <div class="p-4 space-y-2.5 hover:bg-slate-50/80 transition" x-data="{ openDetail: false }">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-retail-ink font-mono block">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                            <span class="text-xs text-slate-400 font-mono">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                        </div>
                        <span class="badge-retail text-xs uppercase font-bold
                            {{ str_contains($log->action, 'REJECT') || str_contains($log->action, 'VOID') || str_contains($log->action, 'DELETE') ? 'bg-[#FF3C00] text-white' : '' }}
                            {{ str_contains($log->action, 'RESTORE') ? 'bg-emerald-700 text-white' : '' }}
                            {{ str_contains($log->action, 'APPROVE') || str_contains($log->action, 'VALID') ? 'bg-[#050A1E] text-white' : '' }}
                            {{ str_contains($log->action, 'OVERRIDE') ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                            {{ str_contains($log->action, 'DISBURSE') ? 'bg-white border-2 border-[#FF3C00] text-[#FF3C00]' : '' }}
                            {{ !str_contains($log->action, 'REJECT') && !str_contains($log->action, 'APPROVE') && !str_contains($log->action, 'OVERRIDE') && !str_contains($log->action, 'DISBURSE') && !str_contains($log->action, 'VOID') && !str_contains($log->action, 'DELETE') && !str_contains($log->action, 'RESTORE') ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1]' : '' }}
                        ">
                            {{ $log->action }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                        <div class="font-sans">
                            <span class="font-bold text-slate-900">{{ $log->user_name ?: 'System' }}</span>
                            <span class="text-xs text-slate-500 uppercase font-mono ml-1 font-semibold">({{ $log->user_role ?: '-' }})</span>
                        </div>
                        <span class="text-xs text-slate-500 font-mono font-semibold">{{ $log->record_type ?: '-' }} #{{ $log->record_id ?: '-' }}</span>
                    </div>

                    <p class="text-xs text-slate-700 leading-relaxed font-sans">{{ $log->description }}</p>

                    @if($log->old_values || $log->new_values)
                        <div class="pt-1">
                            <button @click="openDetail = !openDetail" class="text-retail-cyan hover:text-cyan-800 font-bold text-xs flex items-center space-x-1">
                                <i data-lucide="code" class="w-3.5 h-3.5"></i>
                                <span x-text="openDetail ? 'Tutup Detail Diff' : 'Lihat Detail Perubahan Nilai (Diff)'"></span>
                            </button>

                            <div x-show="openDetail" x-cloak class="mt-2 text-left p-3 bg-retail-darkInk text-slate-200 rounded-lg border border-retail-ink font-mono text-xs overflow-x-auto shadow-lg">
                                @if($log->old_values)
                                    <div class="text-rose-400 font-bold mb-1">--- Old Values:</div>
                                    <pre class="whitespace-pre-wrap">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @endif
                                @if($log->new_values)
                                    <div class="text-emerald-400 font-bold mt-2 mb-1">+++ New Values:</div>
                                    <pre class="whitespace-pre-wrap">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 font-sans text-xs">Belum ada riwayat audit log tercatat.</div>
            @endforelse
        </div>

        <!-- Desktop Table View (Visible only on md:block and above) -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs font-mono table-technodrives">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Waktu & IP</th>
                        <th class="px-4 py-3">Pelaksana</th>
                        <th class="px-4 py-3 text-center">Aksi (Action)</th>
                        <th class="px-4 py-3">Objek & ID</th>
                        <th class="px-4 py-3">Deskripsi Tindakan</th>
                        <th class="px-4 py-3 text-center">Detail Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition" x-data="{ openDetail: false }">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-bold text-retail-ink block">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                <span class="text-xs text-slate-400 font-mono">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-sans">
                                <span class="font-bold text-slate-900 block">{{ $log->user_name ?: 'System' }}</span>
                                <span class="text-xs text-slate-500 uppercase font-mono font-semibold">{{ $log->user_role ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="badge-retail text-xs uppercase font-bold
                                    {{ str_contains($log->action, 'REJECT') || str_contains($log->action, 'VOID') || str_contains($log->action, 'DELETE') ? 'bg-[#FF3C00] text-white' : '' }}
                                    {{ str_contains($log->action, 'RESTORE') ? 'bg-emerald-700 text-white' : '' }}
                                    {{ str_contains($log->action, 'APPROVE') || str_contains($log->action, 'VALID') ? 'bg-[#050A1E] text-white' : '' }}
                                    {{ str_contains($log->action, 'OVERRIDE') ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                    {{ str_contains($log->action, 'DISBURSE') ? 'bg-white border-2 border-[#FF3C00] text-[#FF3C00]' : '' }}
                                    {{ !str_contains($log->action, 'REJECT') && !str_contains($log->action, 'APPROVE') && !str_contains($log->action, 'OVERRIDE') && !str_contains($log->action, 'DISBURSE') && !str_contains($log->action, 'VOID') && !str_contains($log->action, 'DELETE') && !str_contains($log->action, 'RESTORE') ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1]' : '' }}
                                ">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500 font-mono">
                                {{ $log->record_type ?: '-' }} #{{ $log->record_id ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 max-w-md font-sans">
                                <span class="leading-relaxed">{{ $log->description }}</span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap font-sans">
                                @if($log->old_values || $log->new_values)
                                    <button @click="openDetail = !openDetail" class="text-retail-cyan hover:text-cyan-800 font-bold text-xs">
                                        <span x-text="openDetail ? 'Tutup' : 'Lihat Diff'"></span>
                                    </button>

                                    <!-- Diff Drawer / Popup -->
                                    <div x-show="openDetail" x-cloak class="mt-2 text-left p-3 bg-retail-darkInk text-slate-200 rounded-lg border border-retail-ink font-mono text-xs max-w-xs overflow-x-auto shadow-lg">
                                        @if($log->old_values)
                                            <div class="text-rose-400 font-bold mb-1">--- Old Values:</div>
                                            <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @endif
                                        @if($log->new_values)
                                            <div class="text-emerald-400 font-bold mt-2 mb-1">+++ New Values:</div>
                                            <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 font-sans">Belum ada riwayat audit log tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-retail-surface bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection