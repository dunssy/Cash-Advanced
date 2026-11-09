@extends('layouts.app')

@section('title', 'Audit Trail Sistem')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <i data-lucide="shield" class="w-4 h-4 text-retail-cyan"></i>
                <span>Keamanan & Kepatuhan (Prinsip 5: Tidak Ada Data Yang Dihapus)</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Audit Trail Sistem</h1>
            <p class="text-xs text-slate-500 mt-0.5">Catatan permanen (immutable) seluruh aksi, persetujuan, verifikasi, void, pembukaan gembok, dan perubahan konfigurasi.</p>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card-commerce p-4">
        <form action="{{ route('audit-logs.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <!-- Search -->
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full input-commerce"
                    placeholder="Cari deskripsi aksi, nama pengguna, IP address...">
            </div>

            <!-- Action Filter -->
            <div>
                <select name="action" class="w-full input-commerce">
                    <option value="">-- Semua Jenis Aksi --</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="btn-dark py-2 px-4 flex-grow">
                    Filter
                </button>
                @if(request('search') || request('action'))
                    <a href="{{ route('audit-logs.index') }}" class="btn-secondary py-2 px-3 text-xs">
                        Reset
                    </a>
                @endif
            </div>
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
                            <span class="text-[10px] text-slate-400 font-mono">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                        </div>
                        <span class="badge-retail text-[10px] uppercase font-bold
                            {{ str_contains($log->action, 'REJECT') || str_contains($log->action, 'VOID') ? 'bg-[#FF3C00] text-white' : '' }}
                            {{ str_contains($log->action, 'APPROVE') || str_contains($log->action, 'VALID') ? 'bg-[#050A1E] text-white' : '' }}
                            {{ str_contains($log->action, 'OVERRIDE') ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                            {{ str_contains($log->action, 'DISBURSE') ? 'bg-white border-2 border-[#FF3C00] text-[#FF3C00]' : '' }}
                            {{ !str_contains($log->action, 'REJECT') && !str_contains($log->action, 'APPROVE') && !str_contains($log->action, 'OVERRIDE') && !str_contains($log->action, 'DISBURSE') && !str_contains($log->action, 'VOID') ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1]' : '' }}
                        ">
                            {{ $log->action }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                        <div class="font-sans">
                            <span class="font-bold text-slate-900">{{ $log->user_name ?: 'System' }}</span>
                            <span class="text-[10px] text-slate-500 uppercase font-mono ml-1 font-semibold">({{ $log->user_role ?: '-' }})</span>
                        </div>
                        <span class="text-[11px] text-slate-500 font-mono font-semibold">{{ $log->record_type ?: '-' }} #{{ $log->record_id ?: '-' }}</span>
                    </div>

                    <p class="text-xs text-slate-700 leading-relaxed font-sans">{{ $log->description }}</p>

                    @if($log->old_values || $log->new_values)
                        <div class="pt-1">
                            <button @click="openDetail = !openDetail" class="text-retail-cyan hover:underline font-bold text-xs flex items-center space-x-1">
                                <i data-lucide="code" class="w-3.5 h-3.5"></i>
                                <span x-text="openDetail ? 'Tutup Detail Diff' : 'Lihat Detail Perubahan Nilai (Diff)'"></span>
                            </button>

                            <div x-show="openDetail" x-cloak class="mt-2 text-left p-3 bg-retail-darkInk text-slate-200 rounded-lg border border-retail-ink font-mono text-[10px] overflow-x-auto shadow-lg">
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
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider border-b border-retail-surface font-sans">
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
                                <span class="text-[10px] text-slate-400 font-mono">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-sans">
                                <span class="font-bold text-slate-900 block">{{ $log->user_name ?: 'System' }}</span>
                                <span class="text-[10px] text-slate-500 uppercase font-mono font-semibold">{{ $log->user_role ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="badge-retail text-[10px] uppercase font-bold
                                    {{ str_contains($log->action, 'REJECT') || str_contains($log->action, 'VOID') ? 'bg-[#FF3C00] text-white' : '' }}
                                    {{ str_contains($log->action, 'APPROVE') || str_contains($log->action, 'VALID') ? 'bg-[#050A1E] text-white' : '' }}
                                    {{ str_contains($log->action, 'OVERRIDE') ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                    {{ str_contains($log->action, 'DISBURSE') ? 'bg-white border-2 border-[#FF3C00] text-[#FF3C00]' : '' }}
                                    {{ !str_contains($log->action, 'REJECT') && !str_contains($log->action, 'APPROVE') && !str_contains($log->action, 'OVERRIDE') && !str_contains($log->action, 'DISBURSE') && !str_contains($log->action, 'VOID') ? 'bg-[#F4F4F4] text-[#7A7A7A] border border-[#E1E1E1]' : '' }}
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
                                    <button @click="openDetail = !openDetail" class="text-retail-cyan hover:underline font-bold text-[11px]">
                                        <span x-text="openDetail ? 'Tutup' : 'Lihat Diff'"></span>
                                    </button>

                                    <!-- Diff Drawer / Popup -->
                                    <div x-show="openDetail" x-cloak class="mt-2 text-left p-3 bg-retail-darkInk text-slate-200 rounded-lg border border-retail-ink font-mono text-[10px] max-w-xs overflow-x-auto shadow-lg">
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