@extends('layouts.app')

@section('title', 'Catat Pengeluaran Struk - ' . $cashAdvance->ca_number)
@section('page_title', 'Catat Pengeluaran Struk')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{
    amount: '',
    isTalangan: false,
    isLostReceipt: false,
    currentBalance: {{ (float)($userBalance ?? $cashAdvance->current_balance) }},
    selectedItemId: '{{ old('cash_advance_item_id', '') }}',
    rabItems: {{ json_encode($userRabItems ?? []) }},
    onItemChange(itemId) {
        let it = this.rabItems.find(x => x.id == itemId);
        if (it) {
            let catSelect = document.querySelector('select[name=category]');
            if (catSelect) catSelect.value = it.category;
            let descInput = document.querySelector('input[name=description]');
            if (descInput && !descInput.value) descInput.value = it.item_name;
        }
    },
    get isOverBalance() {
        if (this.isTalangan) return false;
        return parseFloat(this.amount || 0) > this.currentBalance;
    }
}">
    <!-- Top Breadcrumb -->
    <div class="mb-5">
        <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="text-xs font-semibold text-bodygrey hover:text-[#050A1E] inline-flex items-center space-x-1 mb-2 font-head">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke CA {{ $cashAdvance->ca_number }}</span>
        </a>
        <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight">Catat Pengeluaran Real-Time</h1>
        <p class="text-xs text-bodygrey mt-1">Unggah foto struk saat transaksi terjadi agar saldo kas terpotong langsung secara akurat.</p>
    </div>

    <!-- Saldo Header Widget (PT Technodrives Navy Summary Band) -->
    <div class="mb-5 p-4 sm:p-5 rounded-[15px] bg-[#050A1E] text-white border border-cardborder shadow-md flex items-center justify-between">
        <div>
            <span class="text-xs text-[#FF3C00] uppercase tracking-wider block font-head font-bold">
                {{ $cashAdvance->user_id === auth()->id() ? 'Sisa Kas Aktif di PIC' : 'Sisa Kas dipegang Anda' }}
            </span>
            <span class="text-xl sm:text-2xl font-head font-bold text-white mt-0.5 block">
                Rp {{ number_format($userBalance ?? $cashAdvance->current_balance, 0, ',', '.') }}
            </span>
            @if($cashAdvance->user_id !== auth()->id())
                <span class="text-xs text-slate-400 block mt-0.5 font-mono">
                    Total diterima dari Atasan: Rp {{ number_format($cashAdvance->userDistributionTotal(auth()->id()), 0, ',', '.') }}
                </span>
            @endif
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-400 uppercase font-semibold block font-head">Proyek</span>
            <span class="text-xs font-bold text-white max-w-[180px] truncate block font-head">{{ $cashAdvance->project_name }}</span>
            <span class="text-xs font-mono text-[#FF3C00] block">{{ $cashAdvance->ca_number }}</span>
        </div>
    </div>

    <div class="card-commerce p-6 sm:p-7">
        <form action="{{ route('expenses.store', $cashAdvance) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- 0. Kaitkan dengan Pos Rincian RAB (Kebutuhan Alur Baru) -->
            @if(isset($userRabItems) && $userRabItems->isNotEmpty())
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                    <label class="block text-xs font-head font-bold text-[#050A1E] uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                        <span>Kaitkan dengan Rincian RAB Anda</span>
                    </label>
                    <select name="cash_advance_item_id" x-model="selectedItemId" @change="onItemChange($event.target.value)" class="w-full input-commerce text-xs">
                        <option value="">-- Pengeluaran Tambahan / Di Luar RAB --</option>
                        @foreach($userRabItems as $ri)
                            <option value="{{ $ri->id }}" {{ old('cash_advance_item_id') == $ri->id ? 'selected' : '' }}>
                                [{{ $ri->category_label }}] {{ $ri->item_name }} (RAB: Rp {{ number_format($ri->subtotal, 0, ',', '.') }} &bull; Realisasi: Rp {{ number_format($ri->realized_amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-bodygrey">
                        Memilih pos RAB otomatis mengisi kategori dan mempermudah pencocokan bukti struk dengan anggaran yang telah diajukan.
                    </p>
                </div>
            @endif

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
                <p class="text-xs text-slate-400 mt-1">
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
                        <span class="font-bold">Saldo Kas Anda Tidak Cukup:</span> Nominal pengeluaran melebihi sisa dana kas yang Anda pegang (<strong class="font-mono">Rp {{ number_format($userBalance ?? $cashAdvance->current_balance, 0, ',', '.') }}</strong>). Sistem mengunci saldo agar tidak negatif. Gunakan opsi <strong>Talangan Pribadi (Kondisi Darurat)</strong> di bawah atau minta Atasan Anda membagikan dana tambahan.
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
                    <option value="transport" {{ old('category') === 'transport' ? 'selected' : '' }}>Transportasi & Bensin / Tol</option>
                    <option value="sewa_alat" {{ old('category') === 'sewa_alat' ? 'selected' : '' }}>Sewa Alat / Kendaraan / Scaffolding</option>
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

            <!-- 5. Nama Vendor / Toko & No Faktur/NPWP -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                        Nama Toko / Vendor / SPBU <span class="text-rose-500">*</span>
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
                    Catatan Keperluan Lapangan (Opsional)
                </label>
                <textarea name="notes" rows="2"
                    class="w-full input-commerce"
                    placeholder="Contoh: Pembelian mendadak karena kabel bawaan kurang 15 meter...">{{ old('notes') }}</textarea>
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
                        <span class="text-xs font-bold text-slate-800 block font-head">Tanpa Struk Fisik / Struk Hilang (Darurat)</span>
                        <span class="text-xs text-slate-600 leading-relaxed block font-body">
                            Centang ini jika transaksi tidak memiliki struk cetak resmi (misal: ojek pangkalan lokal, warung kecil tanpa nota, kuli angkut). Wajib mengunggah surat pernyataan bermaterai/bertandatangan.
                        </span>
                    </div>
                </label>
            </div>

            <!-- 8. Upload File: Struk Foto ATAU Surat Pernyataan -->
            <div class="pt-2 border-t border-slate-100">
                <!-- Jika Lost Receipt: Upload Surat Pernyataan -->
                <div x-show="isLostReceipt" x-cloak class="space-y-2">
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider">
                        Upload Surat Pernyataan Kehilangan Struk (Bermaterai / TTD) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="lost_receipt_statement_file" accept="image/*,.pdf"
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#050A1E] file:text-white hover:file:bg-slate-800 cursor-pointer">
                    <p class="text-xs text-slate-400">Format: JPG, PNG, atau PDF. Wajib disetujui Atasan / Direktur sebelum CA ditutup.</p>
                </div>

                <!-- Jika Normal: Upload Foto Struk / Nota (Maksimal 5) -->
                <div x-show="!isLostReceipt" class="space-y-2">
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider">
                        Foto / File Struk & Nota Transaksi <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="receipts[]" multiple accept="image/*,.pdf"
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FF3C00] file:text-white hover:file:bg-[#e03500] cursor-pointer">
                    <p class="text-xs text-slate-400">
                        Pilih 1 sampai 5 foto struk yang jelas dan terbaca. Pastikan tanggal, toko, dan total nominal terlihat terang.
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="btn-secondary py-2.5 px-4 text-xs text-center justify-center">
                    Batal
                </a>
                <button type="submit" class="btn-primary py-2.5 px-6 justify-center" :disabled="isOverBalance">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan & Potong Saldo Kas</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
