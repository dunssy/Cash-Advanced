@extends('layouts.app')

@section('title', 'Ajukan Cash Advance Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="{{ route('cash-advances.index') }}" class="text-xs font-semibold text-bodygrey hover:text-[#050A1E] inline-flex items-center space-x-1 mb-2 font-head">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar CA</span>
        </a>
        <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight">Form Pengajuan Cash Advance</h1>
        <p class="text-xs text-bodygrey mt-1">Prinsip Tata Kelola: Satu CA = Satu Proyek = Satu Buku Saldo Terpisah.</p>
    </div>

    <div class="card-commerce p-6 sm:p-7 border border-cardborder rounded-2xl bg-white shadow-sm">
        <form action="{{ route('cash-advances.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- ========================================================================= --}}
            {{-- BAGIAN 1: PEMILIHAN ATASAN PENYETUJU (ROLE-BASED: KARYAWAN vs ATASAN)     --}}
            {{-- - Karyawan: Hanya dapat memilih Atasan langsung (Direktur dilarang).      --}}
            {{-- - Atasan: Menargetkan persetujuan ke Direktur Utama.                      --}}
            {{-- ========================================================================= --}}
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span>Pilih Atasan / Manager Penyetuju <span class="text-rose-500">*</span></span>
                    <span class="text-[10px] text-[#FF3C00] font-normal lowercase">terintegrasi ke akun atasan</span>
                </label>
                <div class="relative">
                    <select name="manager_id" required class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                        <option value="">-- {{ auth()->user()->isKaryawan() ? 'Pilih Atasan Divisi Anda (Bukan Direktur)' : 'Pilih Atasan / Direktur Penyetuju' }} --</option>
                        @foreach($managers ?? [] as $mgr)
                            @php
                                $isCurrentManager = (old('manager_id', auth()->user()->manager_id) == $mgr->id);
                                $mgrDepts = !empty($mgr->departments) ? implode(', ', array_slice($mgr->departments, 0, 2)) : '';
                            @endphp
                            <option value="{{ $mgr->id }}" {{ $isCurrentManager ? 'selected' : '' }}>
                                {{ $mgr->name }} &bull; {{ strtoupper($mgr->role) }} {{ $mgrDepts ? '(# ' . $mgrDepts . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <p class="text-[11px] text-bodygrey mt-1">
                    @if(auth()->user()->isKaryawan())
                        <span class="text-slate-600 font-medium">Catatan:</span> Karyawan hanya memilih Atasan langsung divisi Anda. Posisi Direktur hanya dapat diakses atau dipilih oleh akun Atasan.
                    @else
                        Pengajuan CA ini akan otomatis terhubung ke akun Atasan / Direktur yang dipilih untuk proses approval.
                    @endif
                </p>
                @error('manager_id')
                    <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- ========================================================================= --}}
            {{-- BAGIAN 2: DIVISI PEMOHON & KATEGORI (ROLE-BASED: ATASAN vs KARYAWAN)      --}}
            {{-- - Karyawan: Terkunci (Readonly / Otoritas Atasan via Kode Proyek).        --}}
            {{-- - Atasan: Bebas menentukan dan memilih Divisi penanggung jawab anggaran.  --}}
            {{-- ========================================================================= --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Divisi Pemohon -->
                <div>
                    @if(auth()->user()->isKaryawan())
                        {{-- Karyawan TIDAK BISA memilih divisi, divisi ditetapkan oleh Atasan via Kode Proyek --}}
                        <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Divisi Pemohon</span>
                            <span class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded flex items-center gap-1 font-mono">
                                <i data-lucide="lock" class="w-3 h-3 text-[#FF3C00]"></i> Ditetapkan Atasan
                            </span>
                        </label>
                        @php
                            $userPrimaryDept = !empty(auth()->user()->departments) ? auth()->user()->departments[0] : (auth()->user()->department ?: 'Divisi Proyek Terkait');
                        @endphp
                        <div class="p-2.5 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-between min-h-[42px]">
                            <span id="karyawan-divisi-display" class="text-xs font-head font-bold text-slate-800">
                                {{ old('department', $userPrimaryDept) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">Otoritas Atasan</span>
                        </div>
                        <input type="hidden" name="department" id="department-hidden-input" value="{{ old('department', $userPrimaryDept) }}">
                        <p class="text-[10px] text-bodygrey mt-1">Divisi ditentukan oleh Atasan sesuai kode proyek yang Anda pilih.</p>
                    @else
                        {{-- Atasan / Manajemen DAPAT memilih divisi pemohon --}}
                        <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                            Divisi Pemohon <span class="text-rose-500">*</span>
                        </label>
                        <select name="department" required class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                            @php
                                $userPrimaryDept = !empty(auth()->user()->departments) ? auth()->user()->departments[0] : (auth()->user()->department ?: '');
                            @endphp
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions ?? [] as $div)
                                <option value="{{ $div }}" {{ old('department', $userPrimaryDept) === $div ? 'selected' : '' }}>
                                    {{ $div }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-bodygrey mt-1">Pilih divisi yang membebankan anggaran kasbon ini.</p>
                    @endif
                </div>

                <!-- Dropdown Kategori Pengeluaran -->
                <div>
                    <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                        Kategori Keperluan <span class="text-rose-500">*</span>
                    </label>
                    <select name="project_category" required class="w-full input-commerce text-xs font-head font-semibold text-slate-800">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat }}" {{ old('project_category') === $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-bodygrey mt-1">Kategori utama kebutuhan operasional.</p>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- BAGIAN 3: KODE PROYEK RESMI ATASAN & NAMA PROYEK MANUAL                   --}}
            {{-- ========================================================================= --}}
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span>Pilih Kode Proyek Resmi <span class="text-rose-500">*</span></span>
                    <span class="text-[10px] text-[#FF3C00] font-normal lowercase">disediakan oleh atasan</span>
                </label>
                <select name="project_code" id="project_code_select" required class="w-full input-commerce font-mono font-bold text-xs text-slate-800">
                    <option value="" data-department="{{ !empty(auth()->user()->departments) ? auth()->user()->departments[0] : (auth()->user()->department ?: '') }}">
                        -- Pilih Kode Proyek Resmi dari Atasan --
                    </option>
                    @foreach($projectCodes ?? [] as $pCode)
                        <option value="{{ $pCode->code }}" 
                            data-department="{{ $pCode->department }}"
                            {{ old('project_code') === $pCode->code ? 'selected' : '' }}>
                            {{ $pCode->code }} &bull; {{ $pCode->description ?: $pCode->department ?: 'Proyek Operasional' }} {{ $pCode->department ? '(# ' . $pCode->department . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-bodygrey mt-1">
                    Karyawan memilih kode proyek yang telah disahkan oleh Atasan. Divisi anggaran otomatis terkunci mengikuti proyek Atasan ini.
                </p>
                @error('project_code')
                    <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama Proyek (Diketuk Manual) -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                    Nama Proyek / Kegiatan (Ketik Manual) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="project_name" value="{{ old('project_name') }}" required
                    class="w-full input-commerce"
                    placeholder="Contoh: Instalasi & Komisioning Inverter PT ABC Gresik">
                <p class="text-[11px] text-bodygrey mt-1">Tuliskan nama detail proyek atau kegiatan spesifik yang Anda laksanakan di lapangan.</p>
                @error('project_name')
                    <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- ========================================================================= --}}
            {{-- BAGIAN 4: PEMISAHAN TUJUAN PENGAJUAN & RINCIAN BARANG DIBELI              --}}
            {{-- ========================================================================= --}}
            <!-- 4.1 TUJUAN PENGAJUAN -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="target" class="w-4 h-4 text-[#2E3087]"></i>
                        <span>Tujuan Pengajuan (Maksud & Keperluan) <span class="text-rose-500">*</span></span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-normal">maksud kegiatan umum</span>
                </label>
                <textarea name="purpose" rows="3" required
                    class="w-full input-commerce leading-relaxed text-xs"
                    placeholder="Jelaskan maksud dan tujuan pengajuan dana ini (misal: Pelaksanaan pekerjaan penarikan kabel power inverter dan komisioning panel ATS pabrik Gresik selama 4 hari)...">{{ old('purpose') }}</textarea>
                <p class="text-[11px] text-bodygrey mt-1">
                    Jelaskan latar belakang atau objektif dari kegiatan yang memerlukan dana kasbon ini.
                </p>
                @error('purpose')
                    <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- 4.2 RINCIAN BARANG YANG DIBELI -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-[#FF3C00]"></i>
                        <span>Rincian Barang yang Dibeli (Estimasi Item & Biaya) <span class="text-rose-500">*</span></span>
                    </span>
                    <span class="text-[10px] text-[#FF3C00] font-normal font-mono font-bold">tracking akun atasan</span>
                </label>
                <textarea name="items_detail" rows="4" required
                    class="w-full input-commerce leading-relaxed font-mono text-xs bg-slate-50/70 focus:bg-white"
                    placeholder="Tuliskan estimasi rincian barang/material/jasa yang akan dibeli:&#10;1. MCB 3 Phase 100A Schneider - 2 unit @ Rp 1.500.000&#10;2. Kabel Tembaga NYY 4x16mm - 40 meter @ Rp 85.000&#10;3. Terminal Scun & Selang Spiral - 1 paket @ Rp 350.000&#10;4. Sewa Tangga Scaffolding 3 Set (5 hari) - Rp 750.000">{{ old('items_detail') }}</textarea>
                <p class="text-[11px] text-bodygrey mt-1">
                    Rincian barang ini akan muncul di dashboard Atasan untuk tracking serta acuan saat Anda mengunggah struk/nota belanja.
                </p>
                @error('items_detail')
                    <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nominal Pengajuan -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                    Nominal Pengajuan (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-head font-bold text-slate-400">Rp</span>
                    <input type="number" name="requested_amount" value="{{ old('requested_amount') }}" required min="10000" step="1"
                        class="w-full pl-11 input-commerce font-head font-bold text-[#050A1E]"
                        placeholder="10000000">
                </div>
                <p class="text-[11px] text-bodygrey mt-1">Nominal yang diajukan akan dievaluasi oleh Atasan / Direktur sebelum dicairkan Finance.</p>
                @error('requested_amount')
                    <p class="text-xs text-rose-500 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Information Card -->
            <div class="p-4 bg-[#F4F4F4] border border-cardborder rounded-[15px] text-xs text-[#050A1E] space-y-1.5">
                <span class="font-head font-bold flex items-center space-x-1.5 text-[#050A1E]">
                    <i data-lucide="info" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span class="uppercase tracking-wide text-[11px]">Ketentuan Kepatuhan CA & Integrasi Atasan:</span>
                </span>
                <ul class="list-disc list-inside space-y-1 text-bodygrey text-[11px] pl-1 font-body">
                    <li>Setelah diajukan, permohonan ini langsung masuk ke antrean approval <strong>Akun Atasan</strong> yang dipilih.</li>
                    <li>Atasan dapat memantau dan mentracking <strong>seluruh barang yang dibeli</strong> saat Anda mencatat struk.</li>
                    <li>Masa aktif CA maksimal <strong>2 bulan (60 hari)</strong> sejak dana dicairkan.</li>
                    <li>Pengajuan CA baru akan <strong>digembok otomatis</strong> sampai CA ini berstatus <strong>CLOSED</strong>.</li>
                </ul>
            </div>

            <div class="pt-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.index') }}" class="btn-secondary py-2.5 px-4 text-xs text-center justify-center">
                    Batal
                </a>
                <button type="submit" class="btn-primary py-2.5 px-5 justify-center shadow-sm">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Pengajuan CA</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const projectCodeSelect = document.getElementById('project_code_select');
        const divisiDisplay = document.getElementById('karyawan-divisi-display');
        const departmentInput = document.getElementById('department-hidden-input');

        if (projectCodeSelect && divisiDisplay) {
            projectCodeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const dept = selectedOption.getAttribute('data-department');
                if (dept && dept.trim() !== '') {
                    divisiDisplay.textContent = dept;
                    if (departmentInput) {
                        departmentInput.value = dept;
                    }
                }
            });
        }
    });
</script>
@endsection
