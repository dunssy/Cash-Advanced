{{-- 
    Partial: Dashboard Role FINANCE (Treasury & Financial Audit)
    Fokus kebutuhan Finance:
    1. Akses Cepat Pencairan Dana & Audit Struk
    2. Monitoring Likuiditas Dana Beredar (Active Float) & SLA Overdue
    3. Laporan Rekapitulasi Pajak & Keuangan
--}}

<!-- 1. Akses Fitur Cepat Khusus Finance -->
<div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
    <span class="text-[11px] font-head font-bold uppercase tracking-wider text-slate-400 block mb-3">
        Menu Kerja Tim Finance
    </span>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-3">
        <!-- 1. Pencairan Dana CA -->
        <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="wallet" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-emerald-700 transition">Pencairan CA</span>
        </a>

        <!-- 2. Audit & Verifikasi Struk -->
        <a href="{{ route('cash-advances.index', ['status' => 'ACTIVE']) }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#2E3087] group-hover:bg-[#2E3087] group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="receipt-text" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#2E3087] transition">Audit Struk</span>
        </a>

        <!-- 3. Laporan Rincian CA -->
        <a href="{{ route('reports.rincian-ca') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-purple-700 transition">Laporan CA</span>
        </a>

        <!-- 4. Rekap Bulanan Pajak -->
        <a href="{{ route('reports.rekap-bulanan') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="calendar" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-amber-700 transition">Rekap Pajak</span>
        </a>

        <!-- 5. Audit Trail -->
        <a href="{{ route('audit-logs.index') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-slate-800 group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="history" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-slate-900 transition">Audit Trail</span>
        </a>
    </div>
</div>

<!-- 2. Empat Kartu Metrik Keuangan Perusahaan -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
    <!-- Metrik 1: CA Siap Dicairkan -->
    <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="card-commerce card-commerce-hoverable p-5 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md bg-white">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Pencairan CA</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">CA siap dicairkan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-4">
            <div class="text-3xl font-head font-extrabold text-[#050A1E] group-hover:text-[#FF3C00] transition">
                {{ $stats['pending_disbursement_count'] }}
            </div>
            <span class="inline-flex items-center text-xs font-head font-bold text-[#FF3C00] group-hover:underline">
                Cairkan <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </span>
        </div>
    </a>

    <!-- Metrik 2: Struk Menunggu Verifikasi -->
    <a href="{{ route('cash-advances.index', ['status' => 'ACTIVE']) }}" class="card-commerce card-commerce-hoverable p-5 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md bg-white">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Verifikasi Struk</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Struk menunggu validasi</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#161A2B] text-white flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm">
                <i data-lucide="receipt" class="w-5 h-5 text-sky-400"></i>
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-4">
            <div class="text-3xl font-head font-extrabold text-[#050A1E] group-hover:text-[#FF3C00] transition">
                {{ $stats['pending_verification_count'] ?? 0 }}
            </div>
            <span class="inline-flex items-center text-xs font-head font-bold text-[#FF3C00] group-hover:underline">
                Audit <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </span>
        </div>
    </a>

    <!-- Metrik 3: Total Dana Float Beredar -->
    <div class="card-commerce p-5 border border-cardborder bg-white shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Dana CA Beredar</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Active Float lapangan</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#050A1E] text-[#FFC704] flex items-center justify-center shadow-sm">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="text-xl font-head font-extrabold text-[#050A1E]">
                Rp {{ number_format($stats['total_active_float'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-slate-400 font-mono mt-1 block">Dana aktif di tangan pemohon</span>
        </div>
    </div>

    <!-- Metrik 4: CA Overdue -->
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
            <span class="text-[10px] text-rose-600 font-medium mt-1 block">Perlu tindak lanjut settlement</span>
        </div>
    </div>
</div>
