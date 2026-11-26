@extends('layouts.app')

@section('title', 'Catat Pengeluaran Struk - ' . $cashAdvance->ca_number)
@section('page_title', 'Catat Pengeluaran Struk')

@section('content')
<div class="w-full" x-data="{
    amount: '',
    isTalangan: false,
    isLostReceipt: false,
    currentBalance: {{ (float)$cashAdvance->current_balance }},
    get isOverBalance() {
        if (this.isTalangan) return false;
        return parseFloat(this.amount || 0) > this.currentBalance;
    }
}">
    <!-- Top Navigation -->
    <div class="mb-4 pb-2 border-b border-slate-200">
        <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="btn-secondary btn-sm px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-xs rounded-full text-xs font-head transition active:scale-95">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Kembali ke CA {{ $cashAdvance->ca_number }}</span>
        </a>
    </div>

    <!-- Saldo Header Widget (PT Technodrives Navy Summary Band) -->
    <div class="mb-5 p-4 sm:p-5 rounded-[15px] bg-[#050A1E] text-white border border-cardborder shadow-md flex items-center justify-between">
        <div>
            <span class="text-xs text-[#FF3C00] uppercase tracking-wider block font-head font-bold">
                Sisa Saldo Kas Aktif
            </span>
            <span class="text-xl sm:text-2xl font-head font-bold text-white mt-0.5 block">
                Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}
            </span>
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-400 uppercase font-semibold block font-head">Proyek</span>
            <span class="text-xs font-bold text-white max-w-xs sm:max-w-md truncate block font-head">{{ $cashAdvance->project_name }}</span>
            <span class="text-xs font-mono text-[#FF3C00] block">{{ $cashAdvance->ca_number }}</span>
        </div>
    </div>

    <div class="card-commerce p-6 sm:p-7 w-full">
        <form action="{{ route('expenses.store', $cashAdvance) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- 1. Tanggal Transaksi -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Tanggal Transaksi <span class="text-rose-500">*</span>
                </label>
                @php
                    $minDate = $cashAdvance->disbursed_at ? $cashAdvance->disbursed_at->format('Y-m-d') : '2020-01-01';
                @endphp
                <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" min="{{ $minDate }}" required
                    class="w-full input-commerce font-mono">
                <p class="text-[11px] text-slate-400 mt-1">
                    Wajib dicatat maksimal 1 hari setelah transaksi. Lewat 1 hari otomatis dilabeli <strong class="text-rose-600 font-bold">TERLAMBAT</strong>.
                </p>
            </div>

            <!-- 2. Nominal Pengeluaran -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Nominal Transaksi (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-mono font-bold text-slate-400">Rp</span>
                    <input type="number" name="amount" x-model="amount" value="{{ old('amount') }}" required min="1" step="1"
                        class="w-full pl-11 input-commerce font-mono font-bold text-sm sm:text-base text-retail-ink"
                        :class="isOverBalance ? 'border-rose-500 bg-rose-50/50' : ''"
                        placeholder="Contoh: 150000">
                </div>

                <!-- Live Saldo Guard Alert -->
                <div x-show="isOverBalance" x-cloak 
                    x-transition:enter="transition ease-out duration-150 transform"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100 transform"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="mt-2.5 p-4 bg-rose-50 border border-rose-300 rounded-xl text-xs text-rose-900 flex items-start space-x-3">
                    <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5"></i>
                    <div class="leading-relaxed">
                        <span class="font-bold">Saldo Kas Tidak Cukup:</span> Nominal pengeluaran melebihi sisa saldo kas aktif (<strong class="font-mono">Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}</strong>). Sistem mengunci saldo agar tidak negatif. Gunakan opsi <strong>Talangan Pribadi (Kondisi Darurat)</strong> di bawah atau ajukan Top-Up dana.
                    </div>
                </div>
            </div>

            <!-- 3. Kategori Pengeluaran (Memudahkan Rekap Pajak) -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Kategori Pengeluaran (Untuk Rekap Pajak) <span class="text-rose-500">*</span>
                </label>
                <select name="category" required class="w-full input-commerce">
                    <option value="" disabled selected>-- Pilih Kategori --</option>
                    <option value="material" {{ old('category') === 'material' ? 'selected' : '' }}>Material & Perlengkapan</option>
                    <option value="transport" {{ old('category') === 'transport' ? 'selected' : '' }}>Transportasi & Bensin</option>
                    <option value="sewa_alat" {{ old('category') === 'sewa_alat' ? 'selected' : '' }}>Sewa Alat</option>
                    <option value="konsumsi" {{ old('category') === 'konsumsi' ? 'selected' : '' }}>Konsumsi & Penginapan Tim</option>
                    <option value="lainnya" {{ old('category') === 'lainnya' ? 'selected' : '' }}>Lain-lain Operasional</option>
                </select>
            </div>

            <!-- 4. Keterangan Barang / Jasa -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Keterangan Pengeluaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="description" value="{{ old('description') }}" required
                    class="w-full input-commerce"
                    placeholder="Contoh: Beli kabel ducting & konektor MC4 10 pcs">
            </div>

            <!-- 5. Nama Vendor & No Faktur -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                        Nama Toko / Vendor <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" required
                        class="w-full input-commerce"
                        placeholder="Contoh: Toko Listrik Terang Abadi">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                        No. Faktur / NPWP Vendor (Opsional)
                    </label>
                    <input type="text" name="vendor_tax_number" value="{{ old('vendor_tax_number') }}"
                        class="w-full input-commerce font-mono"
                        placeholder="Contoh: 01.234.567.8-901.000">
                </div>
            </div>

            <!-- 6. Catatan Tambahan (Opsional) -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Catatan Tambahan (Opsional)
                </label>
                <textarea name="notes" rows="2" class="w-full input-commerce text-xs"
                    placeholder="Catatan pendukung terkait transaksi ini...">{{ old('notes') }}</textarea>
            </div>

            <!-- 7. Toggle Opsi Khusus: Tanpa Struk & Talangan Pribadi -->
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <span class="block text-xs font-bold text-retail-ink uppercase tracking-wider">Kondisi Khusus Transaksi:</span>

                <!-- Checkbox Talangan Pribadi -->
                <label class="flex items-start space-x-3 p-3 bg-amber-50/70 border border-amber-200 rounded-xl cursor-pointer hover:bg-amber-50 transition">
                    <input type="checkbox" name="is_talangan" value="1" x-model="isTalangan"
                        class="mt-0.5 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                    <div>
                        <span class="text-xs font-bold text-amber-900 block font-head">Talangan Pribadi (Kondisi Darurat)</span>
                        <span class="text-xs text-amber-800 leading-relaxed block font-body">
                            Centang ini jika saldo kas Anda habis di lapangan dan Anda terpaksa menalangi dengan uang pribadi. Akan diganti kantor saat Settlement.
                        </span>
                    </div>
                </label>

                <!-- Input Alasan Darurat jika Talangan dicentang -->
                <div x-show="isTalangan" x-cloak class="p-3 bg-amber-50 rounded-xl border border-amber-300 space-y-2">
                    <label class="block text-xs font-bold text-amber-950 uppercase">
                        Alasan Kondisi Darurat Talangan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="talangan_emergency_reason" rows="2"
                        class="w-full input-commerce bg-white"
                        placeholder="Contoh: Pekerjaan di site malam hari butuh genset tambahan, saldo kas habis dan bank sudah cut-off...">{{ old('talangan_emergency_reason') }}</textarea>
                </div>

                <!-- Checkbox Tanpa Struk Fisik -->
                <label class="flex items-start space-x-3 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100 transition">
                    <input type="checkbox" name="is_lost_receipt" value="1" x-model="isLostReceipt"
                        class="mt-0.5 rounded border-slate-300 text-slate-800 focus:ring-slate-500">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block font-head">Tanpa Struk Fisik (Darurat)</span>
                        <span class="text-xs text-slate-600 leading-relaxed block font-body">
                            Centang ini jika transaksi tidak memiliki struk cetak resmi (misal: ojek pangkalan lokal, warung kecil tanpa nota, kuli angkut). Wajib mengunggah surat pernyataan bermaterai atau bertandatangan.
                        </span>
                    </div>
                </label>
            </div>

            <!-- 8. Upload File: Struk Foto ATAU Surat Pernyataan -->
            <div class="pt-2 border-t border-slate-100">
                <!-- Jika Lost Receipt: Upload Surat Pernyataan -->
                <div x-show="isLostReceipt" x-cloak class="space-y-2">
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider">
                        Upload Surat Pernyataan Kehilangan Struk (Bermaterai) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="lost_receipt_statement_file" id="lost-receipt-input" accept="image/*,.pdf" capture="environment"
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#050A1E] file:text-white hover:file:bg-slate-800 cursor-pointer">
                </div>

                <!-- Jika Normal: Upload Foto Struk / Nota (Maksimal 5) -->
                <div x-show="!isLostReceipt" class="space-y-3">
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider">
                        Foto Struk Transaksi (1-5 Foto atau PDF) <span class="text-rose-500">*</span>
                    </label>

                    <!-- Input file utama yang dikirim ke server -->
                    <input type="file" name="receipts[]" id="receipts-main-input" multiple accept="image/*,.pdf" class="hidden">
                    <!-- Input khusus kamera HP (membuka kamera belakang langsung) -->
                    <input type="file" id="receipts-camera-input" accept="image/*" capture="environment" class="hidden">
                    <!-- Input khusus galeri atau dokumen PDF -->
                    <input type="file" id="receipts-gallery-input" multiple accept="image/*,.pdf" class="hidden">

                    <div class="flex flex-wrap items-center gap-2.5">
                        <button type="button" id="btn-open-camera"
                            class="btn-brand py-2.5 px-4 text-xs inline-flex items-center gap-2 rounded-xl shadow-xs">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <span>Ambil Foto Kamera</span>
                        </button>
                        <button type="button" id="btn-open-gallery"
                            class="btn-secondary py-2.5 px-4 text-xs inline-flex items-center gap-2 rounded-xl shadow-xs">
                            <i data-lucide="image" class="w-4 h-4 text-slate-600"></i>
                            <span>Pilih dari Galeri atau PDF</span>
                        </button>
                        <span id="receipts-counter" class="text-xs font-mono text-slate-500 ml-auto">0/5 file dipilih</span>
                    </div>

                    <!-- Preview Thumbnail Struk -->
                    <div id="receipts-preview-grid" class="hidden grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 pt-1"></div>

                    <p class="text-xs text-slate-400">
                        Gunakan kamera HP untuk memotret struk langsung di lokasi atau pilih 1 sampai 5 file dari galeri. Pastikan tanggal, toko, dan total nominal terlihat jelas.
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="btn-secondary py-2.5 px-5 text-xs text-center justify-center rounded-full">
                    Batal
                </a>
                <button type="submit" class="btn-brand py-2.5 px-6 justify-center rounded-full" :disabled="isOverBalance">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan & Potong Saldo Kas</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainInput = document.getElementById('receipts-main-input');
    const cameraInput = document.getElementById('receipts-camera-input');
    const galleryInput = document.getElementById('receipts-gallery-input');
    const btnCamera = document.getElementById('btn-open-camera');
    const btnGallery = document.getElementById('btn-open-gallery');
    const previewGrid = document.getElementById('receipts-preview-grid');
    const counterEl = document.getElementById('receipts-counter');

    const MAX_FILES = 5;
    let selectedFiles = [];

    function syncMainInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach((file) => dt.items.add(file));
        mainInput.files = dt.files;
        counterEl.textContent = `${selectedFiles.length}/${MAX_FILES} file dipilih`;
        renderPreviews();
    }

    function addFiles(fileList) {
        const incoming = Array.from(fileList || []);
        for (const file of incoming) {
            if (selectedFiles.length >= MAX_FILES) {
                alert('Maksimal 5 foto atau file struk per transaksi.');
                break;
            }
            selectedFiles.push(file);
        }
        syncMainInput();
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';
        if (selectedFiles.length === 0) {
            previewGrid.classList.add('hidden');
            return;
        }
        previewGrid.classList.remove('hidden');

        selectedFiles.forEach((file, idx) => {
            const card = document.createElement('div');
            card.className = 'relative border border-slate-200 rounded-xl bg-slate-50 overflow-hidden p-1.5 flex flex-col items-center';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 text-white text-xs font-bold flex items-center justify-center shadow hover:bg-rose-700 transition z-10';
            removeBtn.title = 'Hapus file';
            removeBtn.textContent = '×';
            removeBtn.addEventListener('click', function () {
                selectedFiles.splice(idx, 1);
                syncMainInput();
            });
            card.appendChild(removeBtn);

            if (file.type && file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.className = 'w-full h-24 object-cover rounded-lg';
                img.alt = file.name;
                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
                card.appendChild(img);
            } else {
                const pdfBox = document.createElement('div');
                pdfBox.className = 'w-full h-24 rounded-lg bg-slate-100 flex flex-col items-center justify-center text-slate-500 p-2';
                pdfBox.innerHTML = '<span class="text-xs font-bold uppercase">PDF</span>';
                card.appendChild(pdfBox);
            }

            const nameEl = document.createElement('span');
            nameEl.className = 'text-[11px] text-slate-600 truncate w-full text-center mt-1 px-1';
            nameEl.textContent = file.name;
            card.appendChild(nameEl);

            previewGrid.appendChild(card);
        });
    }

    btnCamera.addEventListener('click', function () {
        cameraInput.value = '';
        cameraInput.click();
    });

    btnGallery.addEventListener('click', function () {
        galleryInput.value = '';
        galleryInput.click();
    });

    cameraInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            addFiles(this.files);
        }
    });

    galleryInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            addFiles(this.files);
        }
    });
});
</script>
@endpush
@endsection
