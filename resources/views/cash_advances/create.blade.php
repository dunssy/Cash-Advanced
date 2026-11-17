@extends('layouts.app')

@section('title', 'Ajukan Cash Advance & Rincian RAB')
@section('page_title', 'Pengajuan Cash Advance')

@section('content')
<div class="w-full space-y-6" x-data="{
    submissionType: '{{ $existingProjects->isNotEmpty() ? (old('submission_type', 'new')) : 'new' }}',
    existingCaId: '{{ old('existing_ca_id', '') }}',
    items: {{ old('items') ? json_encode(old('items')) : json_encode([
        ['item_name' => '', 'category' => 'transport', 'quantity' => 1, 'unit' => 'hari', 'unit_price' => 0, 'notes' => '', 'user_id' => auth()->id()]
    ]) }},
    addItem() {
        this.items.push({
            item_name: '',
            category: 'material',
            quantity: 1,
            unit: 'unit',
            unit_price: 0,
            notes: '',
            user_id: '{{ auth()->id() }}'
        });
        this.$nextTick(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    },
    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },
    getSubtotal(item) {
        let q = parseFloat(item.quantity) || 0;
        let p = parseFloat(item.unit_price) || 0;
        return q * p;
    },
    get totalRAB() {
        return this.items.reduce((sum, it) => sum + this.getSubtotal(it), 0);
    },
    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
}">
    <!-- Header & Breadcrumb (Full Width) -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-2 border-bottom">
        <div>
            <a href="{{ route('cash-advances.index') }}" class="btn-secondary btn-sm px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-xs rounded-full text-xs font-head transition active:scale-95">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                <span>Kembali ke Daftar CA</span>
            </a>
        </div>

        <!-- Header Info Badges (Nomor CA & Running Total) -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="px-3.5 py-2 bg-white rounded-full border shadow-xs d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background-color: #050A1E;">
                    <i class="bi bi-hash fs-6" style="color: #FF3C00;"></i>
                </div>
                <div>
                    <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 11px;">Nomor Dokumen</span>
                    <span class="d-block font-mono fw-bold text-dark text-xs">{{ $previewCaNumber }}</span>
                </div>
            </div>

            <div class="px-4 py-2 rounded-full text-white shadow-xs d-flex align-items-center gap-2" style="background-color: #050A1E;">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background-color: rgba(255, 60, 0, 0.2);">
                    <i class="bi bi-cash-stack fs-6 text-danger"></i>
                </div>
                <div>
                    <span class="d-block text-secondary text-uppercase fw-bold" style="font-size: 11px; color: #94A3B8 !important;">Estimasi Total RAB</span>
                    <span class="d-block font-mono fw-bold text-xs" style="color: #FF3C00;">
                        Rp <span x-text="formatRupiah(totalRAB)"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('cash-advances.store') }}" method="POST" class="space-y-5">
        @csrf

        <!-- PILIHAN TIPE PENGAJUAN (JIKA KARYAWAN MEMILIKI ATASAN DENGAN PROYEK AKTIF) -->
        @if($existingProjects->isNotEmpty() && auth()->user()->isKaryawan())
            <div class="card-commerce p-5 bg-white border border-slate-200">
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-2.5">
                    Mode Pengajuan Proyek
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="relative flex items-start p-3.5 rounded-xl border cursor-pointer transition"
                        :class="submissionType === 'new' ? 'border-[#FF3C00] bg-orange-50/20 ring-2 ring-[#FF3C00]/20' : 'border-slate-200 bg-white hover:bg-slate-50'">
                        <input type="radio" name="submission_type" value="new" x-model="submissionType" class="mt-0.5 text-[#FF3C00] focus:ring-[#FF3C00]">
                        <div class="ml-3">
                            <span class="block text-xs font-head font-bold text-[#050A1E]">Usulkan Proyek Baru</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Buat usulan proyek baru dan ajukan estimasi RAB awal untuk tim lapangan Anda.</span>
                        </div>
                    </label>

                    <label class="relative flex items-start p-3.5 rounded-xl border cursor-pointer transition"
                        :class="submissionType === 'existing' ? 'border-[#FF3C00] bg-orange-50/20 ring-2 ring-[#FF3C00]/20' : 'border-slate-200 bg-white hover:bg-slate-50'">
                        <input type="radio" name="submission_type" value="existing" x-model="submissionType" class="mt-0.5 text-[#FF3C00] focus:ring-[#FF3C00]">
                        <div class="ml-3">
                            <span class="block text-xs font-head font-bold text-[#050A1E]">Gabung ke Proyek Tim yang Sudah Ada</span>
                            <span class="block text-xs text-slate-500 mt-0.5">Tambahkan rincian pos RAB Anda ke proyek yang telah dibuka oleh Atasan Anda.</span>
                        </div>
                    </label>
                </div>

                <!-- Dropdown Pilih Proyek Tim yang Ada -->
                <div x-show="submissionType === 'existing'" x-cloak class="mt-4 pt-4 border-t border-slate-200">
                    <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                        Pilih Proyek Tim dari Atasan <span class="text-rose-500">*</span>
                    </label>
                    <select name="existing_ca_id" x-model="existingCaId" class="w-full input-commerce" :required="submissionType === 'existing'">
                        <option value="" disabled selected>-- Pilih Proyek yang Sedang Berjalan --</option>
                        @foreach($existingProjects as $ep)
                            <option value="{{ $ep->id }}">
                                {{ $ep->ca_number }} - {{ $ep->project_name }} ({{ $ep->status }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <input type="hidden" name="submission_type" value="new">
        @endif

        <!-- 1. INFORMASI PROYEK (HANYA DITAMPILKAN JIKA USULAN PROYEK BARU) -->
        <div class="card-commerce p-5 sm:p-6 space-y-4" x-show="submissionType === 'new'">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <h2 class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="folder-kanban" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>Informasi Proyek & Ruang Lingkup</span>
                </h2>
                <span class="text-xs text-slate-400 font-mono">Kode CA: <strong class="text-slate-800">{{ $previewCaNumber }}</strong></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Nama Proyek -->
                <div class="md:col-span-8">
                    <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                        Nama Proyek <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="project_name" value="{{ old('project_name') }}" :required="submissionType === 'new'"
                        class="w-full input-commerce"
                        placeholder="Contoh: Instalasi & Komisioning Inverter PT ABC Gresik">
                </div>

                <!-- Kode Proyek (Opsional) -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                        Kode Proyek (Opsional)
                    </label>
                    <input type="text" name="project_code" value="{{ old('project_code') }}"
                        class="w-full input-commerce font-mono"
                        placeholder="Contoh: PRJ-202611-0005">
                </div>

                <!-- Tujuan & Deskripsi Umum Proyek -->
                <div class="md:col-span-12">
                    <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                        Tujuan Pengajuan & Cakupan Proyek <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="purpose" rows="2" :required="submissionType === 'new'"
                        class="w-full input-commerce leading-relaxed"
                        placeholder="Jelaskan ruang lingkup proyek operasional lapangan, lokasi kerja, estimasi durasi pelaksanaan, serta kebutuhan utama tim...">{{ old('purpose') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 2. FORM RINCIAN KEBUTUHAN / RAB (RENCANA ANGGARAN BIAYA) -->
        <div class="card-commerce p-5 sm:p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="receipt-text" class="w-4 h-4 text-[#FF3C00]"></i>
                        <span>Rencana Anggaran Biaya (RAB) Proyek</span>
                    </h2>
                </div>
                <button type="button" @click="addItem()" class="btn-brand text-xs py-1.5 px-4 rounded-full inline-flex items-center gap-1.5 self-start sm:self-auto shadow-sm active:scale-95 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Baris RAB</span>
                </button>
            </div>

            <!-- DESKTOP INTERACTIVE TABLE (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto rounded-[15px] border border-slate-200">
                <table class="w-full text-xs text-left table-technodrives">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-3 text-center" style="width: 40px;">#</th>
                            <th class="py-2.5 px-3" style="width: 170px;">Kategori <span class="text-rose-500">*</span></th>
                            <th class="py-2.5 px-3" style="min-width: 220px;">Deskripsi <span class="text-rose-500">*</span></th>
                            <th class="py-2.5 px-3 text-center" style="width: 140px;">Volume <span class="text-rose-500">*</span></th>
                            <th class="py-2.5 px-3 text-right" style="width: 150px;">Harga Satuan (Rp) <span class="text-rose-500">*</span></th>
                            <th class="py-2.5 px-3 text-right" style="width: 150px;">Subtotal (Rp)</th>
                            @if(auth()->user()->isAtasan() && $subordinates->isNotEmpty())
                                <th class="py-2.5 px-3" style="width: 170px;">Alokasi PIC</th>
                            @endif
                            <th class="py-2.5 px-3 text-center" style="width: 50px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-50/70 transition">
                                <!-- Nomor -->
                                <td class="py-2.5 px-3 text-center">
                                    <span class="w-6 h-6 rounded-full bg-[#050A1E] text-white d-inline-flex align-items-center justify-content-center text-xs font-mono font-bold" x-text="index + 1"></span>
                                </td>

                                <!-- Kategori -->
                                <td class="py-2.5 px-3">
                                    <select :name="'items[' + index + '][category]'" x-model="item.category" required class="w-full input-commerce text-xs py-1.5 font-head font-semibold text-slate-800">
                                        <option value="material">Material & Perlengkapan</option>
                                        <option value="transport">Transportasi & BBM</option>
                                        <option value="sewa_alat">Sewa Alat</option>
                                        <option value="konsumsi">Konsumsi & Penginapan</option>
                                        <option value="lainnya">Lain-lain Operasional</option>
                                    </select>
                                </td>

                                <!-- Deskripsi / Keperluan -->
                                <td class="py-2.5 px-3">
                                    <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required
                                        class="w-full input-commerce text-xs py-1.5"
                                        placeholder="Misal: Beli kabel ducting & MC4 10 pcs">
                                </td>

                                <!-- Volume (Qty & Satuan) -->
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-1">
                                        <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required min="0.01" step="any"
                                            class="w-16 input-commerce text-xs py-1.5 font-mono text-center" placeholder="1">
                                        <input type="text" :name="'items[' + index + '][unit]'" x-model="item.unit" required
                                            class="w-full input-commerce text-xs py-1.5 text-center" placeholder="hari">
                                    </div>
                                </td>

                                <!-- Harga Satuan -->
                                <td class="py-2.5 px-3">
                                    <input type="number" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required min="0" step="1000"
                                        class="w-full input-commerce text-xs py-1.5 font-mono font-bold text-right text-slate-800" placeholder="0">
                                </td>

                                <!-- Subtotal Real-Time -->
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-xs whitespace-nowrap text-slate-900">
                                    Rp <span x-text="formatRupiah(getSubtotal(item))"></span>
                                </td>

                                <!-- Alokasi PIC (Jika Atasan) -->
                                @if(auth()->user()->isAtasan() && $subordinates->isNotEmpty())
                                    <td class="py-2.5 px-3">
                                        <select :name="'items[' + index + '][user_id]'" x-model="item.user_id" class="w-full input-commerce text-xs py-1.5 font-head">
                                            <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (Saya)</option>
                                            @foreach($subordinates as $sub)
                                                <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->department ?: 'Karyawan' }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @else
                                    <input type="hidden" :name="'items[' + index + '][user_id]'" value="{{ auth()->id() }}">
                                @endif

                                <!-- Tombol Hapus -->
                                <td class="py-2.5 px-3 text-center">
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="Hapus Baris Ini">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-slate-50 font-head font-bold border-t border-slate-200">
                        <tr>
                            <td colspan="{{ (auth()->user()->isAtasan() && $subordinates->isNotEmpty()) ? 5 : 4 }}" class="py-2.5 px-3 text-right uppercase text-xs text-slate-600">
                                Total Akumulasi RAB:
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-xs font-extrabold text-[#FF3C00] whitespace-nowrap">
                                Rp <span x-text="formatRupiah(totalRAB)"></span>
                            </td>
                            <td colspan="2" class="py-2.5 px-3 text-xs text-slate-500 font-normal">
                                (<span x-text="items.length"></span> Pos Kebutuhan)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- MOBILE CARDS VIEW (md:hidden) -->
            <div class="md:hidden space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 rounded-xl border border-slate-200 bg-white space-y-3 shadow-xs">
                        <div class="flex items-center justify-between border-b pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#050A1E] text-white d-inline-flex align-items-center justify-content-center text-xs font-mono font-bold" x-text="index + 1"></span>
                                <span class="text-xs font-head font-bold text-slate-800">Pos Kebutuhan #<span x-text="index + 1"></span></span>
                            </div>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-500 p-1" title="Hapus">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div>
                            <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Kategori <span class="text-rose-500">*</span></label>
                            <select :name="'items[' + index + '][category]'" x-model="item.category" required class="w-full input-commerce text-xs">
                                <option value="material">Material & Perlengkapan</option>
                                <option value="transport">Transportasi & BBM</option>
                                <option value="sewa_alat">Sewa Alat</option>
                                <option value="konsumsi">Konsumsi & Penginapan</option>
                                <option value="lainnya">Lain-lain Operasional</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Deskripsi <span class="text-rose-500">*</span></label>
                            <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required
                                class="w-full input-commerce text-xs"
                                placeholder="Misal: Beli kabel ducting & MC4 10 pcs">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Volume (Qty & Satuan)</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required min="0.01" step="any"
                                        class="w-16 input-commerce text-xs font-mono text-center" placeholder="1">
                                    <input type="text" :name="'items[' + index + '][unit]'" x-model="item.unit" required
                                        class="w-full input-commerce text-xs text-center" placeholder="hari">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Harga Satuan (Rp)</label>
                                <input type="number" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required min="0" step="1000"
                                    class="w-full input-commerce text-xs font-mono font-bold text-right" placeholder="0">
                            </div>
                        </div>

                        <div class="p-2.5 rounded-lg bg-slate-50 border flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-head font-bold">Subtotal:</span>
                            <span class="font-mono font-bold text-[#FF3C00]">Rp <span x-text="formatRupiah(getSubtotal(item))"></span></span>
                        </div>

                        @if(auth()->user()->isAtasan() && $subordinates->isNotEmpty())
                            <div>
                                <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Alokasi PIC Anggota:</label>
                                <select :name="'items[' + index + '][user_id]'" x-model="item.user_id" class="w-full input-commerce text-xs">
                                    <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (Saya)</option>
                                    @foreach($subordinates as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->department ?: 'Karyawan' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                    </div>
                </template>
            </div>

            <!-- Tombol Tambah Baris Bawah -->
            <div class="pt-1">
                <button type="button" @click="addItem()" class="w-full py-2.5 border-2 border-dashed border-slate-300 hover:border-[#FF3C00] rounded-xl text-xs font-head font-bold text-slate-600 hover:text-[#FF3C00] inline-flex items-center justify-center gap-2 transition active:scale-98 bg-white hover:bg-orange-50/20">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>Tambah Baris RAB Baru</span>
                </button>
            </div>

            <!-- Total Rekapitulasi RAB Card (Full Width & Executive Dark Navy) -->
            <div class="p-4 sm:p-5 rounded-2xl text-white border shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4" style="background-color: #050A1E; border-color: #161A2B;">
                <div>
                    <span class="text-xs font-head font-bold uppercase tracking-wider block" style="color: #FF3C00;">Total Akumulasi Pengajuan RAB</span>
                    <span class="text-2xl sm:text-3xl font-head font-extrabold text-white tracking-tight mt-0.5 block font-mono">
                        Rp <span x-text="formatRupiah(totalRAB)"></span>
                    </span>
                </div>

                <!-- Input Hidden requested_amount -->
                <input type="hidden" name="requested_amount" :value="totalRAB">

                <div class="text-sm-end sm:border-l sm:border-slate-800 sm:pl-6">
                    <span class="text-xs font-head font-bold uppercase tracking-wider block" style="color: #94A3B8;">Jumlah Pos Kebutuhan</span>
                    <span class="h4 font-head font-bold text-white mt-0.5 mb-0 block">
                        <span x-text="items.length"></span> Item Pos
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. ACTION BUTTONS BAR (Clean & Full Width) -->
        <div class="pt-3 pb-4 d-flex flex-column-reverse flex-sm-row align-items-sm-center justify-content-between gap-3 border-top">
            <a href="{{ route('cash-advances.index') }}" class="btn-secondary px-5 py-2.5 text-xs fw-semibold rounded-full shadow-xs">
                <i class="bi bi-x-circle me-1"></i>
                <span>Batal & Kembali</span>
            </a>
            <button type="submit" class="btn-brand px-6 py-2.5 text-xs fw-semibold shadow-sm rounded-full d-inline-flex align-items-center justify-content-center gap-2" :disabled="totalRAB < 10000">
                <i class="bi bi-send-fill"></i>
                <span x-text="submissionType === 'existing' ? 'Kirim Rincian ke Proyek Atasan' : 'Kirim Pengajuan CA Proyek'"></span>
            </button>
        </div>
    </form>
</div>
@endsection
