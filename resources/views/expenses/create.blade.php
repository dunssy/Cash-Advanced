@extends('layouts.app')

@section('title', 'Catat Pengeluaran Struk - ' . $cashAdvance->ca_number)

@section('content')
<div class="max-w-2xl mx-auto" x-data="{
    amount: '',
    isTalangan: false,
    isLostReceipt: false,
    currentBalance: {{ (float)$cashAdvance->current_balance }},
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
            <span class="text-[10px] text-[#FF3C00] uppercase tracking-wider block font-head font-bold">Sisa Saldo Kas Aktif</span>
            <span class="text-xl sm:text-2xl font-head font-bold text-white mt-0.5 block">
                Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}
            </span>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-slate-400 uppercase font-semibold block font-head">Proyek</span>
            <span class="text-xs font-bold text-white max-w-[180px] truncate block font-head">{{ $cashAdvance->project_name }}</span>
            <span class="text-[10px] font-mono text-[#FF3C00] block">{{ $cashAdvance->ca_number }}</span>
        </div>
    </div>

    <div class="card-commerce p-6 sm:p-7">
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

                <!-- Live Saldo Guard Alert (Dokumen hal 5) -->
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
                        <span class="font-bold">Saldo Tidak Cukup:</span> Nominal pengeluaran melebihi sisa kas (<strong class="font-mono">Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}</strong>). Sistem mengunci saldo agar tidak bernilai negatif. Gunakan opsi <strong>Talangan Pribadi (Kondisi Darurat)</strong> di bawah atau minta Top-Up ke Atasan.
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
                        Nama Toko / Vendor <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" required
                        class="w-full input-commerce"
                        placeholder="Contoh: SPBU Pertamina 54.601 / Toko Baut Jaya">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                        No. Faktur / NPWP Vendor (Opsional)
                    </label>
                    <input type="text" name="vendor_tax_number" value="{{ old('vendor_tax_number') }}"
                        class="w-full input-commerce font-mono"
                        placeholder="Contoh: 01.234.567.8-091.000">
                </div>
            </div>

            <!-- 6. Catatan Tambahan -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Catatan Tambahan (Opsional)
                </label>
                <textarea name="notes" rows="2" class="w-full input-commerce" placeholder="Penjelasan teknis bila diperlukan..."></textarea>
            </div>

            <!-- 7. Opsi Talangan Darurat (Dokumen hal 5) -->
            <div class="p-4 bg-amber-50/80 border border-amber-300 rounded-xl space-y-3">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_talangan" value="1" x-model="isTalangan" class="mt-0.5 rounded border-amber-400 text-retail-orange focus:ring-retail-orange">
                    <div>
                        <span class="block text-xs font-bold text-amber-950 uppercase tracking-wide">Gunakan Talangan Pribadi (Kondisi Darurat)</span>
                        <span class="text-[11px] text-amber-900 leading-relaxed block mt-0.5">
                            Centang jika Anda membayar menggunakan uang pribadi terlebih dahulu karena saldo kas tidak mencukupi di lapangan. Tidak memotong sisa saldo CA dan akan direimburse perusahaan saat settlement.
                        </span>
                    </div>
                </label>

                <div x-show="isTalangan" x-cloak 
                    x-transition:enter="transition ease-out duration-150 transform"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100 transform"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="pt-2.5 border-t border-amber-200">
                    <label class="block text-xs font-bold text-amber-950 uppercase mb-1">
                        Alasan Kondisi Darurat <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="talangan_emergency_reason" class="w-full input-commerce bg-white" placeholder="Contoh: Toko lokal hanya menerima tunai saat malam hari & saldo habis.">
                </div>
            </div>

            <!-- 8. Opsi Tanpa Struk / Struk Hilang (Dokumen hal 7) -->
            <div class="p-4 bg-slate-50 border border-retail-surface rounded-xl space-y-3">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_lost_receipt" value="1" x-model="isLostReceipt" class="mt-0.5 rounded border-slate-300 text-retail-orange focus:ring-retail-orange">
                    <div>
                        <span class="block text-xs font-bold text-retail-ink uppercase tracking-wide">Tanpa Struk Fisik / Struk Hilang</span>
                        <span class="text-[11px] text-slate-600 leading-relaxed block mt-0.5">
                            Jika struk fisik hilang atau vendor informal tidak menyediakan nota, Anda wajib mengunggah Surat Pernyataan resmi dan memerlukan persetujuan Atasan.
                        </span>
                    </div>
                </label>

                <!-- Upload Surat Pernyataan if lost receipt -->
                <div x-show="isLostReceipt" x-cloak 
                    x-transition:enter="transition ease-out duration-150 transform"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100 transform"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="pt-2.5 border-t border-slate-200">
                    <label class="block text-xs font-bold text-retail-ink uppercase mb-1">
                        Upload Surat Pernyataan Bermaterai / TTD (PDF / Foto) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="lost_receipt_statement_file" accept="image/*,application/pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-retail-ink hover:file:bg-slate-300">
                </div>
            </div>

            <!-- 9. Upload Foto Struk Dari HP (1 sampai 5 file) -->
            <div x-show="!isLostReceipt">
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Foto Struk / Nota Pembelian (1-5 Foto / PDF) <span class="text-rose-500">*</span>
                </label>
                <div class="border-2 border-dashed border-cardborder hover:border-[#FF3C00] rounded-[15px] p-6 text-center transition bg-[#F4F4F4]/50">
                    <div class="flex flex-col items-center justify-center space-y-2.5">
                        <div class="w-12 h-12 rounded-full bg-white text-[#FF3C00] border border-cardborder flex items-center justify-center shadow-sm">
                            <i data-lucide="camera" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <span class="text-sm font-head font-bold text-[#050A1E] block">Ambil Foto Langsung dari HP atau Pilih File</span>
                            <span class="text-xs font-mono text-bodygrey block mt-0.5">JPG, PNG, atau PDF (Maks. 5 file, maks 10MB/file)</span>
                        </div>
                        <input type="file" name="receipts[]" multiple accept="image/*,application/pdf" capture="environment"
                            class="text-xs text-bodygrey file:mr-3 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-head file:font-semibold file:bg-[#050A1E] file:text-white hover:file:bg-[#FF3B00] cursor-pointer pt-2 transition">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="btn-secondary py-2.5 px-5 text-xs text-center justify-center">
                    Batal
                </a>
                <button type="submit" :disabled="isOverBalance"
                    class="btn-primary py-2.5 px-6 text-xs justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengeluaran</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection