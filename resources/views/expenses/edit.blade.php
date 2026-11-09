@extends('layouts.app')

@section('title', 'Edit Pengeluaran #' . $expense->id)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="text-xs font-semibold text-bodygrey hover:text-[#050A1E] inline-flex items-center space-x-1 mb-2 font-head">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke CA {{ $cashAdvance->ca_number }}</span>
        </a>
        <h1 class="text-xl sm:text-2xl font-head font-bold text-[#050A1E] tracking-tight">Edit Transaksi Pengeluaran</h1>
        <p class="text-xs text-bodygrey mt-1">Perubahan data sebelum verifikasi Finance akan dicatat otomatis dalam riwayat audit log.</p>
    </div>

    @if($expense->verification_status === 'NEEDS_REVISION')
        <div class="mb-5 p-4 bg-orange-50 border border-[#FF3C00]/40 rounded-[15px] text-xs text-orange-950 flex items-start space-x-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-[#FF3C00] flex-shrink-0 mt-0.5"></i>
            <div>
                <span class="font-head font-bold uppercase tracking-wide text-[#050A1E]">Catatan Perbaikan dari Finance:</span>
                <p class="mt-1 leading-relaxed italic">"{{ $expense->verification_notes }}"</p>
                <span class="text-[11px] text-orange-900 block mt-1">Silakan sesuaikan data atau unggah ulang struk yang lebih jelas di bawah ini.</span>
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
                    <input type="number" name="amount" value="{{ old('amount', (int)$expense->amount) }}" required min="1" step="1"
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
                <div class="flex flex-wrap items-center gap-3">
                    @forelse($expense->attachments as $att)
                        <div class="flex items-center space-x-3 p-2 bg-slate-50 border border-slate-200 rounded-xl hover:border-slate-300 transition">
                            <a href="{{ $att->url }}" target="_blank" class="w-14 h-14 rounded-lg border border-slate-200 overflow-hidden bg-white flex items-center justify-center hover:border-retail-orange transition flex-shrink-0 group relative" title="Klik untuk buka: {{ $att->file_name }}">
                                @if(in_array(strtolower($att->file_type), ['jpg', 'jpeg', 'png', 'webp']))
                                    <img src="{{ $att->url }}" alt="Struk" class="w-full h-full object-cover group-hover:scale-105 transition duration-200" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')">
                                    <div class="hidden w-full h-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <i data-lucide="image" class="w-5 h-5"></i>
                                    </div>
                                @else
                                    <i data-lucide="file-text" class="w-6 h-6 text-slate-500"></i>
                                @endif
                            </a>
                            <div class="pr-2">
                                <a href="{{ $att->url }}" target="_blank" class="text-xs font-semibold text-slate-900 hover:text-retail-orange hover:underline truncate max-w-[200px] block" title="{{ $att->file_name }}">
                                    {{ $att->file_name }}
                                </a>
                                <span class="text-[11px] text-slate-500 block font-mono mt-0.5">
                                    {{ round($att->file_size / 1024, 1) }} KB &bull; <span class="uppercase font-bold text-slate-600">{{ $att->file_type }}</span>
                                </span>
                            </div>
                        </div>
                    @empty
                        <span class="text-xs text-slate-400 italic">Belum ada file struk terunggah untuk transaksi ini.</span>
                    @endforelse
                </div>
            </div>

            <!-- Upload Tambahan Foto Struk Baru -->
            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">
                    Unggah Struk Pengganti / Tambahan (Opsional)
                </label>
                <input type="file" name="receipts[]" multiple accept="image/*,application/pdf"
                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-retail-ink hover:file:bg-slate-300 cursor-pointer">
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('cash-advances.show', $cashAdvance) }}" class="btn-secondary py-2.5 px-5 text-xs text-center justify-center">
                    Batal
                </a>
                <button type="submit" class="btn-primary py-2.5 px-6 text-xs justify-center">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection