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
        <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 inline-flex items-center space-x-1 mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke CA {{ $cashAdvance->ca_number }}</span>
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Catat Pengeluaran Real-Time</h1>
        <p class="text-xs text-slate-500 mt-1">Unggah foto struk saat transaksi terjadi agar saldo langsung terpotong akurat.</p>
    </div>

    <!-- Saldo Header Widget -->
    <div class="mb-5 p-4 rounded-2xl bg-gradient-to-r from-sky-900 to-brand-900 text-white shadow-md flex items-center justify-between">
        <div>
            <span class="text-[10px] text-sky-200 uppercase tracking-wider block font-semibold">Sisa Saldo Kas Aktif</span>
            <span class="text-xl sm:text-2xl font-black text-white mt-0.5 block">
                Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}
            </span>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-sky-300 block">Proyek</span>
            <span class="text-xs font-bold text-white max-w-[150px] truncate block">{{ $cashAdvance->project_name }}</span>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('expenses.store', $cashAdvance) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- 1. Tanggal Transaksi -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tanggal Transaksi <span class="text-rose-500">*</span>
                </label>
                @php
                    $minDate = $cashAdvance->disbursed_at ? $cashAdvance->disbursed_at->format('Y-m-d') : '2020-01-01';
                @endphp
                <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" min="{{ $minDate }}" required
                    class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none">
                <p class="text-[11px] text-slate-400 mt-1">
                    Wajib dicatat maksimal 1 hari setelah transaksi. Lewat 1 hari ditandai <strong class="text-rose-600">TERLAMBAT</strong>.
                </p>
            </div>

            <!-- 2. Nominal Pengeluaran -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nominal Transaksi (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-bold text-slate-400">Rp</span>
                    <input type="number" name="amount" x-model="amount" value="{{ old('amount') }}" required min="1" step="100"
                        class="w-full pl-11 pr-3.5 py-2.5 text-base font-extrabold text-slate-900 bg-slate-50 border rounded-xl focus:ring-2 outline-none transition"
                        :class="isOverBalance ? 'border-rose-500 focus:ring-rose-500 bg-rose-50' : 'border-slate-300 focus:ring-sky-500 focus:bg-white'"
                        placeholder="Contoh: 150000">
                </div>

                <!-- Live Saldo Guard Alert (Dokumen hal 5) -->
                <div x-show="isOverBalance" x-cloak class="mt-2 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex items-start space-x-2">
                    <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold">Saldo tidak cukup!</span> Pengeluaran ini melebihi sisa saldo kas (Rp {{ number_format($cashAdvance->current_balance, 0, ',', '.') }}). Sistem mengunci saldo agar tidak pernah negatif. Silakan gunakan opsi <strong>Talangan Darurat</strong> di bawah atau ajukan Top-Up dana.
                    </div>
                </div>
            </div>

            <!-- 3. Kategori Pengeluaran (Memudahkan Rekap Pajak) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Kategori Pengeluaran (Untuk Rekap Pajak) <span class="text-rose-500">*</span>
                </label>
                <select name="category" required class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none">
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
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Keterangan Pengeluaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="description" value="{{ old('description') }}" required
                    class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none"
                    placeholder="Contoh: Beli kabel ducting & konektor MC4 10 pcs">
            </div>

            <!-- 5. Nama Vendor / Toko & No Faktur/NPWP -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Toko / Vendor <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" required
                        class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none"
                        placeholder="Contoh: SPBU Pertamina 54.601 / Toko Baut Jaya">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        No. Faktur / NPWP Vendor (Opsional)
                    </label>
                    <input type="text" name="vendor_tax_number" value="{{ old('vendor_tax_number') }}"
                        class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none"
                        placeholder="Contoh: 01.234.567.8-091.000">
                </div>
            </div>

            <!-- 6. Catatan Tambahan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan Tambahan (Opsional)
                </label>
                <textarea name="notes" rows="2" class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none" placeholder="Penjelasan teknis bila diperlukan..."></textarea>
            </div>

            <!-- 7. Opsi Talangan Darurat (Dokumen hal 5) -->
            <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-2xl space-y-3">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_talangan" value="1" x-model="isTalangan" class="mt-0.5 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                    <div>
                        <span class="block text-xs font-bold text-amber-900 uppercase">Gunakan Talangan Pribadi (Kondisi Darurat)</span>
                        <span class="text-[11px] text-amber-800 leading-relaxed block mt-0.5">
                            Centang jika Anda membayar menggunakan uang pribadi terlebih dahulu karena saldo CA tidak mencukupi di lapangan. Tidak memotong saldo CA dan akan direimburse saat settlement.
                        </span>
                    </div>
                </label>

                <div x-show="isTalangan" x-cloak class="pt-2 border-t border-amber-200">
                    <label class="block text-xs font-bold text-amber-900 uppercase mb-1">
                        Alasan Kondisi Darurat <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="talangan_emergency_reason" class="w-full px-3 py-2 text-xs bg-white border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none" placeholder="Contoh: Toko lokal hanya menerima tunai saat malam hari & saldo habis.">
                </div>
            </div>

            <!-- 8. Opsi Tanpa Struk / Struk Hilang (Dokumen hal 7) -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" name="is_lost_receipt" value="1" x-model="isLostReceipt" class="mt-0.5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <div>
                        <span class="block text-xs font-bold text-slate-800 uppercase">Tanpa Struk Fisik / Struk Hilang</span>
                        <span class="text-[11px] text-slate-500 leading-relaxed block mt-0.5">
                            Jika struk fisik hilang atau vendor tidak menyediakan nota, Anda wajib mengunggah Surat Pernyataan resmi dan memerlukan persetujuan Atasan.
                        </span>
                    </div>
                </label>

                <!-- Upload Surat Pernyataan if lost receipt -->
                <div x-show="isLostReceipt" x-cloak class="pt-2 border-t border-slate-200">
                    <label class="block text-xs font-bold text-slate-800 uppercase mb-1">
                        Upload Surat Pernyataan Bermaterai / TTD (PDF / Foto) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="lost_receipt_statement_file" accept="image/*,application/pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                </div>
            </div>

            <!-- 9. Upload Foto Struk Dari HP (1 sampai 5 file) -->
            <div x-show="!isLostReceipt">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Foto Struk / Nota Pembelian (1-5 Foto / PDF) <span class="text-rose-500">*</span>
                </label>
                <div class="border-2 border-dashed border-slate-300 hover:border-sky-500 rounded-2xl p-5 text-center transition bg-slate-50/50">
                    <div class="flex flex-col items-center justify-center space-y-2">
                        <div class="w-10 h-10 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center">
                            <i data-lucide="camera" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">Ambil Foto Langsung dari HP atau Pilih File</span>
                        <span class="text-[10px] text-slate-400">JPG, PNG, atau PDF (Maks. 5 file, maks 10MB/file)</span>
                        <input type="file" name="receipts[]" multiple accept="image/*,application/pdf" capture="environment"
                            class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-600 file:text-white hover:file:bg-sky-700 cursor-pointer pt-2">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800">
                    Batal
                </a>
                <button type="submit" :disabled="isOverBalance"
                    class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition flex items-center space-x-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengeluaran</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
