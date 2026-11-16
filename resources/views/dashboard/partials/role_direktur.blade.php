{{-- 
    Partial: Dashboard Role DIREKTUR (Executive / Board of Directors)
    Fokus kebutuhan Direktur:
    1. Persetujuan Khusus / Limit Tinggi (Atasan & Finance)
    2. Ringkasan Eksekutif Likuiditas Perusahaan
    3. Audit Trail & Laporan Strategis
--}}

<!-- 1. Akses Fitur Cepat Eksekutif Direktur -->
<div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
    <span class="text-[11px] font-head font-bold uppercase tracking-wider text-slate-400 block mb-3">
        Menu Kerja Direksi / Eksekutif
    </span>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
        <!-- 1. Antrean Approval Khusus -->
        <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-[#161A2B] text-[#FFC704] group-hover:scale-105 transition flex items-center justify-center shadow-xs">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#FF3C00] transition">Approval Direksi</span>
        </a>

        <!-- 2. Laporan Keuangan Eksekutif -->
        <a href="{{ route('reports.rincian-ca') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-purple-700 transition">Laporan Keuangan</span>
        </a>

        <!-- 3. Rekap Bulanan Pajak -->
        <a href="{{ route('reports.rekap-bulanan') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="calendar" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-amber-700 transition">Rekap Pajak</span>
        </a>

        <!-- 4. Audit Trail Sistem -->
        <a href="{{ route('audit-logs.index') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-slate-800 group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="history" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-slate-900 transition">Audit Trail</span>
        </a>
    </div>
</div>

<!-- 2. Empat Kartu Metrik Eksekutif Direktur -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
    <!-- Metrik 1: Approval Menunggu Direktur -->
    <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="card-commerce card-commerce-hoverable p-5 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md bg-white">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Approval Khusus</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Menunggu persetujuan Anda</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#161A2B] text-[#FFC704] flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm">
                <i data-lucide="check-square" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-4">
            <div class="text-3xl font-head font-extrabold text-[#050A1E] group-hover:text-[#FF3C00] transition">
                {{ $stats['pending_approval_count'] }}
            </div>
            <span class="inline-flex items-center text-xs font-head font-bold text-[#FF3C00] group-hover:underline">
                Lihat Antrean <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </span>
        </div>
    </a>

    <!-- Metrik 2: Permintaan Top-Up Direksi -->
    <a href="{{ route('cash-advances.index') }}" class="card-commerce card-commerce-hoverable p-5 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md bg-white">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Top-Up Menunggu</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Permintaan eskalasi dana</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#FF3C00] border border-[#FF3C00]/20 flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm">
                <i data-lucide="arrow-up-circle" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-4">
            <div class="text-3xl font-head font-extrabold text-[#050A1E] group-hover:text-[#FF3C00] transition">
                {{ $stats['pending_topup_count'] }}
            </div>
            <span class="inline-flex items-center text-xs font-head font-bold text-[#FF3C00] group-hover:underline">
                Lihat Pengajuan <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </span>
        </div>
    </a>

    <!-- Metrik 3: Total Float Kas Perusahaan -->
    <div class="card-commerce p-5 border border-cardborder bg-white shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Dana CA Beredar</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Active Float seluruh unit</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#050A1E] text-[#FFC704] flex items-center justify-center shadow-sm">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-xl font-head font-extrabold text-[#050A1E]">
                Rp {{ number_format($stats['total_active_float'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-slate-400 font-mono mt-1 block">Dana operasional di lapangan</span>
        </div>
    </div>

    <!-- Metrik 4: CA Overdue Perusahaan -->
    <div class="card-commerce p-5 border border-cardborder bg-white shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">CA Overdue</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Lewat batas 60 hari</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shadow-sm">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-3xl font-head font-extrabold text-rose-600">
                {{ $stats['overdue_count'] }}
            </div>
            <span class="text-[10px] text-rose-600 font-medium mt-1 block">Risiko kepatuhan settlement</span>
        </div>
    </div>
</div>
