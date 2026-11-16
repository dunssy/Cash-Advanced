{{-- 
    Partial: Tabel Riwayat Cash Advance Terbaru
    Menampilkan permohonan dana terkini sesuai hak akses (scoping) peran pengguna.
--}}
<div class="card-commerce overflow-hidden border border-cardborder rounded-2xl bg-white shadow-sm">
    <div class="bg-slate-50 px-4 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
        <div>
            <h3 class="text-xs sm:text-sm font-head font-bold uppercase tracking-wider text-[#050A1E]">
                Pengajuan Cash Advance Terbaru
            </h3>
            <p class="text-[11px] text-bodygrey font-body">Daftar permohonan dana dan riwayat aktivitas kasbon</p>
        </div>
        <a href="{{ route('cash-advances.index') }}" class="text-xs font-head font-bold text-[#FF3C00] hover:underline flex items-center gap-1">
            <span>Lihat Seluruhnya</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-body">
            <thead>
                <tr class="bg-slate-100/60 border-b border-slate-200 text-[#050A1E] font-head font-bold uppercase text-[10px] tracking-wider">
                    <th class="py-3 px-4">No. CA & Tanggal</th>
                    <th class="py-3 px-4">Pemohon & Divisi</th>
                    <th class="py-3 px-4">Proyek</th>
                    <th class="py-3 px-4 text-right">Nominal Diajukan</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentCAs ?? [] as $ca)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-[#050A1E] text-xs block">{{ $ca->ca_number }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $ca->created_at->format('d/m/Y') }}</span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-head font-bold text-slate-800 text-xs">{{ $ca->user->name }}</div>
                            <div class="text-[10px] text-slate-500">{{ $ca->department ?: ($ca->user->department ?: '-') }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800 text-xs line-clamp-1">{{ $ca->project_name }}</div>
                            @if($ca->project_code)
                                <span class="font-mono text-[10px] text-slate-400 font-medium">Kode: {{ $ca->project_code }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold text-slate-900">
                            Rp {{ number_format($ca->requested_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($ca->status === 'ACTIVE')
                                <span class="badge-retail bg-emerald-50 text-emerald-700 border-emerald-300 font-bold text-[10px]">AKTIF</span>
                            @elseif($ca->status === 'PENDING_APPROVAL')
                                <span class="badge-retail bg-amber-50 text-amber-800 border-amber-300 font-bold text-[10px]">MENUNGGU</span>
                            @elseif($ca->status === 'CLOSED')
                                <span class="badge-retail bg-slate-100 text-slate-700 border-slate-300 font-bold text-[10px]">CLOSED</span>
                            @elseif($ca->status === 'REJECTED')
                                <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 font-bold text-[10px]">DITOLAK</span>
                            @else
                                <span class="badge-retail bg-blue-50 text-blue-700 border-blue-300 font-bold text-[10px]">{{ $ca->status }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('cash-advances.show', $ca) }}" class="text-xs font-head font-bold text-[#FF3C00] hover:underline inline-flex items-center gap-1">
                                <span>Detail</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                            Belum ada pengajuan Cash Advance yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
