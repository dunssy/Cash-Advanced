{{-- 
    Partial: Dashboard Role KARYAWAN (Employee / Field Staff)
    Fokus kebutuhan Karyawan:
    1. Akses Cepat Pengajuan CA & Unggah Struk Belanja
    2. Ringkasan Pengeluaran Pribadi & Validasi Nota
--}}

<!-- 1. Akses Fitur Cepat Khusus Karyawan -->
<div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
    <span class="text-[11px] font-head font-bold uppercase tracking-wider text-slate-400 block mb-3">
        Menu Kerja Karyawan
    </span>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3">
        <!-- 1. Ajukan CA Baru -->
        <a href="{{ route('cash-advances.create') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-orange-50 text-[#FF3C00] group-hover:bg-[#FF3C00] group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#FF3C00] transition">Ajukan CA Baru</span>
        </a>

        <!-- 2. Catat Struk Belanja -->
        @if($stats['my_active_ca'])
            <a href="{{ route('expenses.create', $stats['my_active_ca']) }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition flex items-center justify-center">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-head font-bold text-slate-800 group-hover:text-emerald-700 transition">Catat Struk</span>
            </a>
        @else
            <div class="p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-center flex flex-col items-center justify-center gap-1.5 opacity-60 cursor-not-allowed" title="Perlu ada CA aktif untuk mencatat pengeluaran">
                <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-400 flex items-center justify-center">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-head font-medium text-slate-500">Catat Struk</span>
            </div>
        @endif

        <!-- 3. Riwayat Belanja / Struk -->
        <a href="{{ route('expenses.history') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#2E3087] group-hover:bg-[#2E3087] group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="receipt-text" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#2E3087] transition">Riwayat Belanja</span>
        </a>

        <!-- 4. Daftar CA Saya -->
        <a href="{{ route('cash-advances.index') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-[#050A1E] group-hover:bg-[#050A1E] group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="receipt" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#050A1E] transition">CA Saya</span>
        </a>
    </div>
</div>

<!-- 2. Empat Kartu Metrik Karyawan -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
    <!-- Metrik 1: Total Pengeluaran Pribadi -->
    <div class="card-commerce p-5 border border-cardborder bg-white shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Total Pengeluaran</span>
            <div class="w-8 h-8 rounded-lg bg-orange-50 text-[#FF3C00] flex items-center justify-center">
                <i data-lucide="wallet" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="text-2xl font-head font-extrabold text-[#050A1E] mt-3">
            Rp {{ number_format($stats['karyawan_total_spent'] ?? 0, 0, ',', '.') }}
        </div>
        <span class="text-[11px] text-bodygrey mt-1 block">Akumulasi seluruh nota belanja</span>
    </div>

    <!-- Metrik 2: Struk Terverifikasi -->
    <div class="card-commerce p-5 border border-cardborder bg-white shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Struk Terverifikasi</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="text-3xl font-head font-extrabold text-emerald-600 mt-3">
            {{ $stats['karyawan_valid_receipts'] ?? 0 }}
        </div>
        <span class="text-[11px] text-bodygrey mt-1 block">Nota sah & diaudit Finance</span>
    </div>

    <!-- Metrik 3: Riwayat CA Pribadi -->
    <div class="card-commerce p-5 border border-cardborder bg-white shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Riwayat CA</span>
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                <i data-lucide="receipt" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="text-3xl font-head font-extrabold text-[#050A1E] mt-3">
            {{ $stats['karyawan_ca_count'] ?? 0 }}
        </div>
        <span class="text-[11px] text-bodygrey mt-1 block">Total pengajuan dibuat</span>
    </div>

    <!-- Metrik 4: Tautan Status Pengajuan -->
    <a href="{{ route('cash-advances.index') }}" class="card-commerce card-commerce-hoverable p-5 border border-cardborder bg-white hover:border-[#FF3C00] transition flex flex-col justify-between shadow-sm">
        <div>
            <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Status CA Saya</span>
            <span class="text-xs text-slate-500 mt-1 block">Lihat status pengajuan & riwayat saldo</span>
        </div>
        <span class="text-xs font-head font-bold text-[#FF3C00] inline-flex items-center gap-1 mt-4">
            Buka Riwayat <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </span>
    </a>
</div>
