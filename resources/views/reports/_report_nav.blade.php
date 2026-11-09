<div class="no-print mb-4">
    <div class="card-commerce p-3 sm:p-3.5 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
        <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-full bg-[#050A1E] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                <i data-lucide="bar-chart-3" class="w-4 h-4 text-[#FF3C00]"></i>
            </div>
            <div>
                <span class="block text-[10px] font-head font-bold text-bodygrey uppercase tracking-wider">Kategori Laporan:</span>
                <span class="block text-xs font-head font-bold text-[#050A1E]">
                    @if(request()->routeIs('reports.rincian-ca'))
                        1. Rincian per Cash Advance
                    @elseif(request()->routeIs('reports.rekap-bulanan'))
                        2. Rekap Bulanan Pajak
                    @elseif(request()->routeIs('reports.outstanding'))
                        3. CA Menggantung (Outstanding)
                    @elseif(request()->routeIs('reports.rekap-karyawan'))
                        4. Rekap per Karyawan
                    @else
                        Pilih Jenis Laporan
                    @endif
                </span>
            </div>
        </div>

        <!-- Dropdown Navigasi Laporan -->
        <div class="w-full sm:w-72">
            <div class="relative">
                <select onchange="if(this.value) window.location.href=this.value" 
                    class="w-full input-brand font-head font-semibold text-xs text-[#050A1E] bg-[#F4F4F4] hover:bg-white border border-cardborder focus:border-[#FF3C00] shadow-sm cursor-pointer transition pr-8">
                    <option value="{{ route('reports.rincian-ca') }}" {{ request()->routeIs('reports.rincian-ca') ? 'selected' : '' }}>
                        1. Rincian per CA
                    </option>
                    @if(auth()->user()->isFinance() || auth()->user()->isDirektur() || auth()->user()->isSuperAdmin())
                        <option value="{{ route('reports.rekap-bulanan') }}" {{ request()->routeIs('reports.rekap-bulanan') ? 'selected' : '' }}>
                            2. Rekap Bulanan Pajak
                        </option>
                        <option value="{{ route('reports.outstanding') }}" {{ request()->routeIs('reports.outstanding') ? 'selected' : '' }}>
                            3. CA Menggantung (Outstanding)
                        </option>
                        <option value="{{ route('reports.rekap-karyawan') }}" {{ request()->routeIs('reports.rekap-karyawan') ? 'selected' : '' }}>
                            4. Rekap per Karyawan
                        </option>
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>
