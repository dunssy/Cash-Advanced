@extends('layouts.app')

@section('title', 'Ajukan Cash Advance & Rincian RAB')
@section('page_title', 'Pengajuan Cash Advance')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{
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
    <!-- Header & Breadcrumb -->
    <div class="mb-5">
        <a href="{{ route('cash-advances.index') }}" class="text-xs font-semibold text-bodygrey hover:text-[#050A1E] inline-flex items-center space-x-1 mb-2 font-head">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar CA</span>
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight">
                    Pengajuan Cash Advance & Rincian RAB
                </h1>
                <p class="text-xs text-bodygrey mt-1">
                    Satu Proyek dapat melibatkan beberapa karyawan dengan konsolidasi rincian di bawah Atasan.
                </p>
            </div>
            <!-- Auto-generated CA Number Preview -->
            <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#050A1E] text-white rounded-[12px] border border-slate-700 shadow-sm self-start sm:self-auto">
                <i data-lucide="hash" class="w-4 h-4 text-[#FF3C00]"></i>
                <div>
                    <span class="text-xs uppercase tracking-wider text-slate-400 block font-head">Kode / Nomor CA Otomatis</span>
                    <span class="text-xs font-mono font-bold text-white tracking-wide">{{ $previewCaNumber }}</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('cash-advances.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- PILIHAN TIPE PENGAJUAN (JIKA KARYAWAN MEMILIKI ATASAN DENGAN PROYEK AKTIF) -->
        @if($existingProjects->isNotEmpty() && auth()->user()->isKaryawan())
            <div class="card-commerce p-5 bg-slate-50/70 border-slate-200">
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-2.5">
                    Mode Pengajuan Proyek
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex items-start p-3.5 rounded-xl border cursor-pointer transition"
                        :class="submissionType === 'new' ? 'border-[#FF3C00] bg-white ring-2 ring-[#FF3C00]/20' : 'border-slate-200 bg-white/60 hover:bg-white'">
                        <input type="radio" name="submission_type" value="new" x-model="submissionType" class="mt-0.5 text-[#FF3C00] focus:ring-[#FF3C00]">
                        <div class="ml-3">
                            <span class="block text-xs font-head font-bold text-[#050A1E]">Usulkan Proyek Baru</span>
                            <span class="block text-xs text-bodygrey mt-0.5">Buat proyek baru dan ajukan estimasi RAB awal untuk tim Anda.</span>
                        </div>
                    </label>

                    <label class="relative flex items-start p-3.5 rounded-xl border cursor-pointer transition"
                        :class="submissionType === 'existing' ? 'border-[#FF3C00] bg-white ring-2 ring-[#FF3C00]/20' : 'border-slate-200 bg-white/60 hover:bg-white'">
                        <input type="radio" name="submission_type" value="existing" x-model="submissionType" class="mt-0.5 text-[#FF3C00] focus:ring-[#FF3C00]">
                        <div class="ml-3">
                            <span class="block text-xs font-head font-bold text-[#050A1E]">Gabung ke Proyek Tim yang Sudah Ada</span>
                            <span class="block text-xs text-bodygrey mt-0.5">Tambahkan rincian RAB Anda ke proyek yang telah dibuka oleh Atasan Anda.</span>
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
                    <p class="text-xs text-bodygrey mt-1">Rincian RAB yang Anda isi akan otomatis digabungkan ke dokumen proyek ini di bawah Atasan Anda.</p>
                </div>
            </div>
        @else
            <input type="hidden" name="submission_type" value="new">
        @endif

        <!-- INFORMASI PROYEK (HANYA DITAMPILKAN JIKA USULAN PROYEK BARU) -->
        <div class="card-commerce p-6 sm:p-7 space-y-5" x-show="submissionType === 'new'">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <h2 class="text-sm font-head font-bold text-[#050A1E] uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="folder-kanban" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>Informasi Proyek</span>
                </h2>
                <span class="text-xs text-bodygrey">Nomor CA: <strong class="font-mono text-slate-800">{{ $previewCaNumber }}</strong></span>
            </div>

            <!-- Nama Proyek -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                    Nama Proyek / Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="project_name" value="{{ old('project_name') }}" :required="submissionType === 'new'"
                    class="w-full input-commerce"
                    placeholder="Contoh: Instalasi & Komisioning Inverter PT ABC Gresik">
                <p class="text-xs text-bodygrey mt-1">Proyek ini akan menampung seluruh rincian kebutuhan tim teknisi & pelaksana lapangan.</p>
            </div>

            <!-- Kode Proyek (Opsional) -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                    Kode Proyek (Opsional / Otomatis)
                </label>
                <input type="text" name="project_code" value="{{ old('project_code') }}"
                    class="w-full input-commerce font-mono"
                    placeholder="Contoh: PRJ-202611-0005 (Kosongkan untuk otomatis)">
            </div>

            <!-- Tujuan & Deskripsi Umum Proyek -->
            <div>
                <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider mb-1.5">
                    Tujuan Pengajuan & Cakupan Proyek <span class="text-rose-500">*</span>
                </label>
                <textarea name="purpose" rows="3" :required="submissionType === 'new'"
                    class="w-full input-commerce leading-relaxed"
                    placeholder="Jelaskan ruang lingkup proyek operasional lapangan, lokasi kerja, durasi pelaksanaan, serta kebutuhan utama tim...">{{ old('purpose') }}</textarea>
            </div>
        </div>

        <!-- FORM RINCIAN KEBUTUHAN / RAB (RENCANA ANGGARAN BIAYA) -->
        <div class="card-commerce p-6 sm:p-7 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-sm font-head font-bold text-[#050A1E] uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="receipt-text" class="w-4 h-4 text-[#FF3C00]"></i>
                        <span>Form Rincian Proyek / RAB (Rencana Anggaran Biaya)</span>
                    </h2>
                    <p class="text-xs text-bodygrey mt-0.5">
                        Rincikan estimasi kebutuhan dana per pos pengeluaran. Struk pengeluaran nantinya akan dicatat sesuai rincian ini.
                    </p>
                </div>
                <button type="button" @click="addItem()" class="btn-secondary text-xs py-1.5 px-3 inline-flex items-center gap-1.5 self-start sm:self-auto active:scale-95 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                    <span>Tambah Baris RAB</span>
                </button>
            </div>

            <!-- Tabel Baris RAB -->
            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition space-y-3 relative">
                        <!-- Baris Atas: Kategori & Subtotal & Tombol Hapus -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#050A1E] text-white flex items-center justify-center text-xs font-mono font-bold" x-text="index + 1"></span>
                                <span class="text-xs font-head font-bold text-slate-700 uppercase tracking-wide">Item Kebutuhan #<span x-text="index + 1"></span></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-head font-bold text-[#050A1E]">
                                    Subtotal: <span class="font-mono text-[#FF3C00]">Rp <span x-text="formatRupiah(getSubtotal(item))"></span></span>
                                </span>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-slate-400 hover:text-rose-600 p-1 rounded transition" title="Hapus Baris">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Baris Input Field -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <!-- Kategori -->
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Kategori <span class="text-rose-500">*</span></label>
                                <select :name="'items[' + index + '][category]'" x-model="item.category" required class="w-full input-commerce text-xs py-2">
                                    <option value="material">Material & Perlengkapan</option>
                                    <option value="transport">Transportasi & Bensin / Tol</option>
                                    <option value="sewa_alat">Sewa Alat / Kendaraan</option>
                                    <option value="konsumsi">Konsumsi & Penginapan Tim</option>
                                    <option value="lainnya">Lain-lain Operasional</option>
                                </select>
                            </div>

                            <!-- Deskripsi / Nama Kebutuhan -->
                            <div class="sm:col-span-5">
                                <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Deskripsi / Keperluan <span class="text-rose-500">*</span></label>
                                <input type="text" :name="'items[' + index + '][item_name]'" x-model="item.item_name" required
                                    class="w-full input-commerce text-xs py-2"
                                    placeholder="Misal: Penginapan 3 Teknisi / Beli Kabel NYA 2.5">
                            </div>

                            <!-- Qty & Satuan -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Qty & Satuan <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-1">
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required min="0.01" step="any"
                                        class="w-16 input-commerce text-xs py-2 font-mono text-center" placeholder="1">
                                    <input type="text" :name="'items[' + index + '][unit]'" x-model="item.unit" required
                                        class="w-full input-commerce text-xs py-2 text-center" placeholder="hari">
                                </div>
                            </div>

                            <!-- Estimasi Harga Satuan -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Harga Satuan (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required min="0" step="1000"
                                    class="w-full input-commerce text-xs py-2 font-mono font-bold text-right text-slate-800" placeholder="0">
                            </div>
                        </div>

                        <!-- Baris Tambahan: Catatan & Pemilihan Karyawan (Jika Atasan) -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-1">
                            @if(auth()->user()->isAtasan() && $subordinates->isNotEmpty())
                                <div class="sm:col-span-4">
                                    <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Dialokasikan Untuk Karyawan:</label>
                                    <select :name="'items[' + index + '][user_id]'" x-model="item.user_id" class="w-full input-commerce text-xs py-1.5">
                                        <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (Saya / Atasan)</option>
                                        @foreach($subordinates as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->department ?: 'Karyawan' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-8">
                                    <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Catatan / Spesifikasi Teknis (Opsional):</label>
                                    <input type="text" :name="'items[' + index + '][notes]'" x-model="item.notes"
                                        class="w-full input-commerce text-xs py-1.5"
                                        placeholder="Keterangan rincian, merk, standar instalasi, atau alasan keperluan...">
                                </div>
                            @else
                                <input type="hidden" :name="'items[' + index + '][user_id]'" value="{{ auth()->id() }}">
                                <div class="sm:col-span-12">
                                    <label class="block text-xs font-head font-bold text-slate-600 uppercase mb-1">Catatan / Spesifikasi Teknis (Opsional):</label>
                                    <input type="text" :name="'items[' + index + '][notes]'" x-model="item.notes"
                                        class="w-full input-commerce text-xs py-1.5"
                                        placeholder="Keterangan rincian, merk, standar instalasi, atau alasan keperluan...">
                                </div>
                            @endif
                        </div>
                    </div>
                </template>
            </div>

            <!-- Tombol Tambah Baris -->
            <div class="pt-2">
                <button type="button" @click="addItem()" class="w-full py-2.5 border-2 border-dashed border-slate-300 hover:border-[#FF3C00] rounded-xl text-xs font-head font-bold text-slate-600 hover:text-[#FF3C00] inline-flex items-center justify-center gap-2 transition active:scale-98">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>+ Tambah Baris Kebutuhan RAB</span>
                </button>
            </div>

            <!-- Total Rekapitulasi RAB Card -->
            <div class="p-5 rounded-2xl bg-[#050A1E] text-white border border-slate-700 shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="text-xs font-head font-bold text-[#FF3C00] uppercase tracking-wider block">Total Estimasi Anggaran (RAB)</span>
                    <span class="text-2xl sm:text-3xl font-head font-extrabold text-white tracking-tight mt-0.5 block">
                        Rp <span x-text="formatRupiah(totalRAB)"></span>
                    </span>
                    <p class="text-xs text-slate-400 mt-1">Total dihitung otomatis dari akumulasi seluruh item rincian yang dimasukkan.</p>
                </div>

                <!-- Input Hidden requested_amount -->
                <input type="hidden" name="requested_amount" :value="totalRAB">

                <div class="text-right sm:border-l sm:border-slate-800 sm:pl-6">
                    <span class="text-xs font-head font-bold text-slate-400 uppercase tracking-wider block">Jumlah Pos Kebutuhan</span>
                    <span class="text-lg font-head font-bold text-white mt-0.5 block">
                        <span x-text="items.length"></span> Item
                    </span>
                </div>
            </div>
        </div>

        <!-- Ketentuan & Kepatuhan Proyek -->
        <div class="p-4 bg-[#F4F4F4] border border-cardborder rounded-[15px] text-xs text-[#050A1E] space-y-1.5">
            <span class="font-head font-bold flex items-center space-x-1.5 text-[#050A1E]">
                <i data-lucide="info" class="w-4 h-4 text-[#FF3C00]"></i>
                <span class="uppercase tracking-wide text-xs">Ketentuan Alur Baru Cash Advance Proyek:</span>
            </span>
            <ul class="list-disc list-inside space-y-1 text-bodygrey text-xs pl-1 font-body">
                <li>Seluruh rincian RAB dari karyawan untuk proyek yang sama <strong>dikonsolidasikan di bawah Atasan</strong>.</li>
                <li>Atasan yang mengajukan CA Proyek ke Direktur / Finance untuk persetujuan dan pencairan dana.</li>
                <li>Setelah dana cair ke rekening Atasan, Atasan membagi uang kas kepada masing-masing karyawan sesuai rincian RAB.</li>
                <li>Setiap karyawan wajib mengunggah bukti struk pengeluaran sesuai bagian rincian yang telah dibuatnya.</li>
            </ul>
        </div>

        <!-- Tombol Aksi Submit -->
        <div class="pt-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-200">
            <a href="{{ route('cash-advances.index') }}" class="btn-secondary py-2.5 px-4 text-xs text-center justify-center">
                Batal
            </a>
            <button type="submit" class="btn-primary py-2.5 px-6 justify-center" :disabled="totalRAB < 10000">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span x-text="submissionType === 'existing' ? 'Kirim Rincian ke Proyek Atasan' : 'Kirim Pengajuan CA Proyek'"></span>
            </button>
        </div>
    </form>
</div>
@endsection
