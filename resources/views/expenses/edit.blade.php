@extends('layouts.app')

@section('title', 'Edit Pengeluaran #' . $expense->id)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="text-xs font-semibold text-slate-500 hover:text-retail-ink inline-flex items-center space-x-1 mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke CA {{ $cashAdvance->ca_number }}</span>
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Edit Transaksi Pengeluaran</h1>
        <p class="text-xs text-slate-500 mt-1">Perubahan data sebelum verifikasi Finance akan dicatat otomatis dalam riwayat audit log.</p>
    </div>

    @if($expense->verification_status === 'NEEDS_REVISION')
        <div class="mb-5 p-4 bg-amber-50 border border-amber-300 rounded-[6px] text-xs text-amber-950 flex items-start space-x-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-retail-orange flex-shrink-0 mt-0.5"></i>
            <div>
                <span class="font-bold uppercase tracking-wide">Catatan Perbaikan dari Finance:</span>
                <p class="mt-1 leading-relaxed italic">"{{ $expense->verification_notes }}"</p>
                <span class="text-[11px] text-amber-900 block mt-1">Silakan sesuaikan data atau unggah ulang struk yang lebih jelas di bawah ini.</span>
            </div>
        </div>
    @endif

    <div class="card-commerce p-6 sm:p-7">
        <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Tanggal Transaksi -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Tanggal Transaksi <span class="text-rose-500">*</span>
                </label>
                @php
                    $minDate = $cashAdvance->disbursed_at ? $cashAdvance->disbursed_at->format('Y-m-d') : '2020-01-01';
                @endphp
                <input type="date" name="transaction_date" value="{{ old('transaction_date', $expense->transaction_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" min="{{ $minDate }}" required
                    class="w-full input-commerce font-mono">
            </div>

            <!-- Nominal -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Nominal Transaksi (Rp) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-mono font-bold text-slate-400">Rp</span>
                    <input type="number" name="amount" value="{{ old('amount', (int)$expense->amount) }}" required min="1" step="any"
                        class="w-full pl-11 input-commerce font-mono font-bold text-slate-900">
                </div>
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Kategori Pengeluaran <span class="text-rose-500">*</span>
                </label>
                <select name="category" required class="w-full input-commerce">
                    <option value="material" {{ $expense->category === 'material' ? 'selected' : '' }}>Material & Perlengkapan</option>
                    <option value="transport" {{ $expense->category === 'transport' ? 'selected' : '' }}>Transportasi & Bensin / Tol</option>
                    <option value="sewa_alat" {{ $expense->category === 'sewa_alat' ? 'selected' : '' }}>Sewa Alat / Kendaraan</option>
                    <option value="konsumsi" {{ $expense->category === 'konsumsi' ? 'selected' : '' }}>Konsumsi & Penginapan Tim</option>
                    <option value="lainnya" {{ $expense->category === 'lainnya' ? 'selected' : '' }}>Lain-lain</option>
                </select>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Keterangan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="description" value="{{ old('description', $expense->description) }}" required
                    class="w-full input-commerce">
            </div>

            <!-- Vendor -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                        Nama Vendor / Toko <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="vendor_name" value="{{ old('vendor_name', $expense->vendor_name) }}" required
                        class="w-full input-commerce">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                        No. Faktur / NPWP Vendor
                    </label>
                    <input type="text" name="vendor_tax_number" value="{{ old('vendor_tax_number', $expense->vendor_tax_number) }}"
                        class="w-full input-commerce font-mono">
                </div>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Catatan Tambahan
                </label>
                <textarea name="notes" rows="2" class="w-full input-commerce">{{ old('notes', $expense->notes) }}</textarea>
            </div>

            <!-- Struk Sebelumnya -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Struk Yang Sudah Diunggah
                </label>
                <div class="flex items-center space-x-2">
                    @foreach($expense->attachments as $att)
                        <a href="{{ $att->url }}" target="_blank" class="w-12 h-12 rounded-[3px] border border-retail-surface overflow-hidden bg-slate-50 flex items-center justify-center hover:border-retail-orange transition" title="{{ $att->file_name }}">
                            @if(in_array(strtolower($att->file_type), ['jpg', 'jpeg', 'png', 'webp']))
                                <img src="{{ $att->url }}" alt="Struk" class="w-full h-full object-cover">
                            @else
                                <i data-lucide="file-text" class="w-5 h-5 text-slate-500"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Upload Tambahan Foto Struk Baru -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Unggah Struk Pengganti / Tambahan (Opsional)
                </label>
                <input type="file" name="receipts[]" multiple accept="image/*,application/pdf"
                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-[3px] file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-retail-ink hover:file:bg-slate-300">
            </div>

            <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="btn-secondary py-2 px-4 text-xs">
                    Batal
                </a>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection