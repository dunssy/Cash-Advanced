@extends('layouts.app')

@section('title', 'Audit Trail Sistem')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <i data-lucide="shield" class="w-4 h-4 text-sky-600"></i>
                <span>Keamanan & Kepatuhan (Prinsip 5: Tidak Ada Data Yang Dihapus)</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Audit Trail Sistem</h1>
            <p class="text-xs text-slate-500 mt-0.5">Catatan permanen (immutable) seluruh aksi, persetujuan, verifikasi, void, pembukaan gembok, dan perubahan konfigurasi.</p>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <form action="{{ route('audit-logs.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <!-- Search -->
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-sky-500"
                    placeholder="Cari deskripsi aksi, nama pengguna, IP address...">
            </div>

            <!-- Action Filter -->
            <div>
                <select name="action" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">-- Semua Jenis Aksi --</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="flex-grow px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl">
                    Filter
                </button>
                @if(request('search') || request('action'))
                    <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Waktu & IP</th>
                        <th class="px-4 py-3.5">Pelaksana</th>
                        <th class="px-4 py-3.5 text-center">Aksi (Action)</th>
                        <th class="px-4 py-3.5">Objek & ID</th>
                        <th class="px-4 py-3.5">Deskripsi Tindakan</th>
                        <th class="px-4 py-3.5 text-center">Detail Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition" x-data="{ openDetail: false }">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-bold text-slate-800 block">{{ $log->user_name ?: 'System' }}</span>
                                <span class="text-[10px] text-slate-400 uppercase font-semibold">{{ $log->user_role ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black tracking-wide
                                    {{ str_contains($log->action, 'REJECT') || str_contains($log->action, 'VOID') ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ str_contains($log->action, 'APPROVE') || str_contains($log->action, 'VALID') ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ str_contains($log->action, 'OVERRIDE') ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ str_contains($log->action, 'DISBURSE') ? 'bg-sky-100 text-sky-800' : '' }}
                                    {{ !str_contains($log->action, 'REJECT') && !str_contains($log->action, 'APPROVE') && !str_contains($log->action, 'OVERRIDE') && !str_contains($log->action, 'DISBURSE') && !str_contains($log->action, 'VOID') ? 'bg-slate-100 text-slate-700' : '' }}
                                ">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                {{ $log->record_type ?: '-' }} #{{ $log->record_id ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 max-w-md">
                                <span class="leading-relaxed">{{ $log->description }}</span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($log->old_values || $log->new_values)
                                    <button @click="openDetail = !openDetail" class="text-sky-600 hover:text-sky-800 font-bold text-[11px] underline">
                                        <span x-text="openDetail ? 'Tutup' : 'Lihat Diff'"></span>
                                    </button>

                                    <!-- Diff Drawer / Popup -->
                                    <div x-show="openDetail" x-cloak class="mt-2 text-left p-3 bg-slate-900 text-slate-200 rounded-xl font-mono text-[10px] max-w-xs overflow-x-auto">
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
                                    <span class="text-slate-400 text-[10px]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada riwayat audit log tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
