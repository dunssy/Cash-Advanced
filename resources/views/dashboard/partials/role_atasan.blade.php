{{-- 
    Partial: Dashboard Role ATASAN (Supervisor / Head of Division)
    Fokus kebutuhan Atasan:
    1. Akses Cepat Fitur Manajemen Divisi (Approval, Tracking, Master Kode Proyek)
    2. Metrik Persetujuan Bawahan & Akumulasi Belanja Divisi
    3. Tabel Monitoring Real-time Pembelian Barang oleh Karyawan Divisi
--}}

<!-- 1. Akses Fitur Cepat Khusus Atasan -->
<div class="card-commerce p-4 sm:p-5 border border-cardborder rounded-2xl bg-white shadow-sm">
    <span class="text-[11px] font-head font-bold uppercase tracking-wider text-slate-400 block mb-3">
        Menu Kerja Atasan Divisi
    </span>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
        <!-- 1. Antrean Approval CA -->
        <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-[#161A2B] text-[#FFC704] group-hover:scale-105 transition flex items-center justify-center shadow-xs">
                <i data-lucide="check-square" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#FF3C00] transition">Approval CA</span>
        </a>

        <!-- 2. Tracking Dana & Pembelian Divisi -->
        <a href="{{ route('expenses.history') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#2E3087] group-hover:bg-[#2E3087] group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="binoculars" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#2E3087] transition">Tracking Divisi</span>
        </a>

        <!-- 3. Master Kode Proyek Resmi -->
        <a href="{{ route('project-codes.index') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="folder-kanban" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-amber-700 transition">Kode Proyek</span>
        </a>

        <!-- 4. Ajukan CA Baru (Bila Atasan Memerlukan Kas Operasional) -->
        <a href="{{ route('cash-advances.create') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-orange-50 text-[#FF3C00] group-hover:bg-[#FF3C00] group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#FF3C00] transition">Ajukan CA Saya</span>
        </a>

        <!-- 5. Daftar Seluruh CA Divisi -->
        <a href="{{ route('cash-advances.index') }}" class="p-3 rounded-xl border border-slate-200 hover:border-[#FF3C00] hover:bg-orange-50/30 transition text-center group flex flex-col items-center justify-center gap-1.5">
            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-[#050A1E] group-hover:text-white transition flex items-center justify-center">
                <i data-lucide="receipt" class="w-4 h-4"></i>
            </div>
            <span class="text-xs font-head font-bold text-slate-800 group-hover:text-[#050A1E] transition">Daftar CA</span>
        </a>
    </div>
</div>

<!-- 2. Empat Kartu Metrik Operasional Atasan -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
    <!-- Metrik 1: Approval CA Menunggu -->
    <a href="{{ route('cash-advances.index', ['status' => 'PENDING_APPROVAL']) }}" class="card-commerce card-commerce-hoverable p-5 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md bg-white">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Approval CA</span>
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

    <!-- Metrik 2: Permintaan Top-Up Menunggu -->
    <a href="{{ route('cash-advances.index') }}" class="card-commerce card-commerce-hoverable p-5 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md bg-white">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Top-Up Menunggu</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Permintaan dana tambahan</span>
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

    <!-- Metrik 3: Tracking Dana Divisi -->
    <a href="{{ route('expenses.history') }}" class="card-commerce card-commerce-hoverable p-5 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md bg-white">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Tracking Dana Divisi</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Total dana dipakai staf</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#2E3087] flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm">
                <i data-lucide="binoculars" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-4">
            <div class="text-xl font-head font-extrabold text-[#050A1E] group-hover:text-[#FF3C00] transition truncate" title="Rp {{ number_format($stats['division_expense_total'] ?? 0, 0, ',', '.') }}">
                Rp {{ number_format($stats['division_expense_total'] ?? 0, 0, ',', '.') }}
            </div>
            <span class="inline-flex items-center text-xs font-head font-bold text-[#FF3C00] group-hover:underline">
                Tracking <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </span>
        </div>
    </a>

    <!-- Metrik 4: Barang Dibeli Divisi -->
    <a href="{{ route('expenses.history') }}" class="card-commerce card-commerce-hoverable p-5 group transition duration-200 block border border-cardborder hover:border-[#FF3C00] hover:shadow-md bg-white">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider block">Barang Dibeli Divisi</span>
                <span class="text-[11px] text-[#7A7A7A] mt-0.5 block font-body">Item belanja staf</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform shadow-sm">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            </div>
        </div>
        <div class="flex items-baseline justify-between mt-4">
            <div class="text-3xl font-head font-extrabold text-[#050A1E] group-hover:text-[#FF3C00] transition">
                {{ $stats['division_expense_count'] ?? 0 }} <span class="text-xs font-normal text-slate-500">Item</span>
            </div>
            <span class="inline-flex items-center text-xs font-head font-bold text-[#FF3C00] group-hover:underline">
                Lihat Barang <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
            </span>
        </div>
    </a>
</div>

<!-- 3. Tabel Monitoring: Tracking Pembelian Barang Karyawan Divisi -->
<div class="card-commerce overflow-hidden border border-cardborder rounded-2xl bg-white shadow-sm">
    <div class="bg-slate-50 px-4 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-lg bg-orange-50 text-[#FF3C00] flex items-center justify-center">
                <i data-lucide="binoculars" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-xs sm:text-sm font-head font-bold uppercase tracking-wider text-[#050A1E]">
                    Tracking Pembelian Barang Karyawan Divisi
                </h3>
                <p class="text-[11px] text-bodygrey font-body">Pantau apa saja barang yang telah dibeli oleh akun karyawan divisi Anda</p>
            </div>
        </div>
        <a href="{{ route('expenses.history') }}" class="btn-dark py-1.5 px-3.5 text-xs font-head font-bold inline-flex items-center gap-1.5 shadow-sm">
            <i data-lucide="list" class="w-3.5 h-3.5"></i>
            <span>Buka Seluruh Tracking Divisi</span>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-body">
            <thead>
                <tr class="bg-slate-100/60 border-b border-slate-200 text-[#050A1E] font-head font-bold uppercase text-[10px] tracking-wider">
                    <th class="py-3 px-4">Tanggal & Karyawan</th>
                    <th class="py-3 px-4">Proyek / No. CA</th>
                    <th class="py-3 px-4">Barang / Deskripsi Pengeluaran</th>
                    <th class="py-3 px-4">Kategori & Vendor</th>
                    <th class="py-3 px-4 text-right">Nominal (Rp)</th>
                    <th class="py-3 px-4 text-center">Status Struk</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentDivisionExpenses ?? [] as $divExp)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-mono font-bold text-slate-800 text-xs">
                                {{ $divExp->transaction_date ? $divExp->transaction_date->format('d/m/Y') : '-' }}
                            </div>
                            <div class="flex items-center gap-1.5 mt-1">
                                <div class="w-5 h-5 rounded-full bg-[#161A2B] text-white flex items-center justify-center text-[9px] font-head font-bold">
                                    {{ $divExp->user ? $divExp->user->initials : 'U' }}
                                </div>
                                <span class="text-[11px] font-head font-semibold text-slate-700">
                                    {{ $divExp->user ? $divExp->user->name : '-' }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($divExp->cashAdvance)
                                <span class="font-mono text-[11px] text-[#FF3C00] font-bold block">{{ $divExp->cashAdvance->ca_number }}</span>
                                <span class="text-xs text-slate-800 block line-clamp-1">{{ $divExp->cashAdvance->project_name }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 text-xs">
                                {{ $divExp->description }}
                            </div>
                            @if($divExp->notes)
                                <span class="text-[11px] text-slate-500 italic block line-clamp-1">"{{ $divExp->notes }}"</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="badge-retail text-[9px] font-head uppercase font-bold
                                {{ $divExp->category === 'material' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                {{ $divExp->category === 'transport' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}
                                {{ $divExp->category === 'sewa_alat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                {{ $divExp->category === 'konsumsi' ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}
                                {{ $divExp->category === 'lainnya' ? 'bg-slate-100 text-slate-700 border-slate-200' : '' }}
                            ">
                                {{ $divExp->category_label }}
                            </span>
                            <div class="text-[11px] text-slate-600 mt-1 truncate max-w-[120px]">
                                {{ $divExp->vendor_name ?: '-' }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap font-head font-bold text-slate-900">
                            Rp {{ number_format($divExp->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($divExp->verification_status === 'VALID')
                                <span class="badge-retail bg-emerald-50 text-emerald-700 border-emerald-300 font-bold text-[10px]">VALID</span>
                            @elseif($divExp->verification_status === 'PENDING')
                                <span class="badge-retail bg-amber-50 text-amber-800 border-amber-300 font-bold text-[10px]">MENUNGGU</span>
                            @elseif($divExp->verification_status === 'REJECTED')
                                <span class="badge-retail bg-rose-50 text-rose-700 border-rose-300 font-bold text-[10px]">DITOLAK</span>
                            @else
                                <span class="badge-retail bg-slate-100 text-slate-600 border-slate-300 font-bold text-[10px]">{{ $divExp->verification_status }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                            Belum ada catatan pembelian barang oleh karyawan divisi saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
