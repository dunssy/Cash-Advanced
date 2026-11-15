@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-slate-500 hover:text-retail-ink inline-flex items-center space-x-1 mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar User</span>
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Tambah Pengguna Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Daftarkan akun karyawan, atasan, atau finance PT Technodrives Indonesia.</p>
    </div>

    <div class="card-commerce p-6 sm:p-7">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full input-commerce">
            </div>

            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Email Kantor (@technodrives.co.id) <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full input-commerce font-mono" placeholder="nama@technodrives.co.id">
            </div>

            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Password Awal <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required minlength="6"
                    class="w-full input-commerce" placeholder="Minimal 6 karakter">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full input-commerce font-bold">
                        <option value="karyawan">Karyawan (Pegawai Lapangan/Staff)</option>
                        <option value="atasan">Atasan (Manager Langsung)</option>
                        <option value="finance">Finance (Admin Keuangan & Pajak)</option>
                        <option value="direktur">Direktur (Pimpinan Perusahaan)</option>
                        <option value="super_admin">Super Admin (Pengelola Teknis)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Atasan Langsung</label>
                    <select name="manager_id" class="w-full input-commerce">
                        <option value="">-- Tanpa Atasan (Direksi) --</option>
                        @foreach($managers as $mgr)
                            <option value="{{ $mgr->id }}">{{ $mgr->name }} ({{ $mgr->role }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Nomor WhatsApp / HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full input-commerce font-mono" placeholder="08123456789">
            </div>

            <!-- Tag Divisi / Departemen (Maksimal 10 Divisi dengan format Tag: [Nama Divisi]) -->
            <div x-data="{
                tags: {{ json_encode(old('departments', [])) }},
                newTag: '',
                maxTags: 10,
                addTag() {
                    let val = this.newTag.trim();
                    if (!val) return;
                    if (this.tags.length >= this.maxTags) return;
                    if (!this.tags.includes(val)) {
                        this.tags.push(val);
                    }
                    this.newTag = '';
                },
                removeTag(index) {
                    this.tags.splice(index, 1);
                }
            }" class="space-y-2 pt-2 border-t border-slate-100">
                <input type="hidden" name="departments" value="">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider">
                        Tag Departemen / Divisi <span class="text-[#FF3C00] font-normal normal-case text-[11px]">(Maks. 10 Divisi)</span>
                    </label>
                    <span class="text-[11px] font-mono px-2 py-0.5 rounded-full font-bold"
                        :class="tags.length >= maxTags ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'"
                        x-text="tags.length + ' / ' + maxTags + ' Divisi'">
                    </span>
                </div>

                <!-- Tag Badges Display -->
                <div class="flex flex-wrap gap-1.5 min-h-[40px] p-2 bg-slate-50 rounded-lg border border-slate-200 items-center">
                    <template x-for="(tag, index) in tags" :key="index">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-mono font-medium bg-[#050A1E] text-white shadow-sm border border-[#050A1E]">
                            <span class="text-[#FFC704] font-bold">Tag:</span>
                            <span x-text="tag"></span>
                            <button type="button" @click="removeTag(index)" class="text-slate-400 hover:text-[#FF3C00] ml-1 transition font-bold" title="Hapus tag">&times;</button>
                            <input type="hidden" name="departments[]" :value="tag">
                        </span>
                    </template>
                    <span x-show="tags.length === 0" class="text-xs text-slate-400 italic py-1 px-1">
                        Belum ada tag divisi. Masukkan nama divisi di bawah ini (maksimal 10 divisi).
                    </span>
                </div>

                <!-- Input to Add New Tag -->
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input type="text" x-model="newTag" @keydown.enter.prevent="addTag()"
                            placeholder="Ketik nama divisi lalu tekan Enter atau klik Tambah Tag..."
                            :disabled="tags.length >= maxTags"
                            class="w-full input-commerce text-xs py-1.5 disabled:bg-slate-100 disabled:cursor-not-allowed">
                    </div>
                    <button type="button" @click="addTag()"
                        :disabled="tags.length >= maxTags || !newTag.trim()"
                        class="px-3 py-1.5 bg-[#161A2B] hover:bg-[#FF3C00] text-white text-xs font-bold rounded-lg transition flex items-center space-x-1.5 disabled:opacity-40 disabled:hover:bg-[#161A2B] disabled:cursor-not-allowed cursor-pointer flex-shrink-0 shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Tag</span>
                    </button>
                </div>
            </div>

            <!-- Bank Details for Reimbursements -->
            <div class="p-4 bg-slate-50 rounded-[6px] border border-retail-surface space-y-2">
                <span class="text-xs font-bold text-retail-ink uppercase block tracking-wide">Rekening Bank Karyawan (Reimbursement)</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank (BCA/Mandiri)" class="w-full input-commerce text-xs py-1.5 font-mono">
                    </div>
                    <div>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="No. Rekening" class="w-full input-commerce text-xs py-1.5 font-mono">
                    </div>
                    <div>
                        <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder') }}" placeholder="Atas Nama" class="w-full input-commerce text-xs py-1.5">
                    </div>
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-2 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary py-2 px-4 text-xs">Batal</a>
                <button type="submit" class="btn-primary">Simpan User</button>
            </div>
        </form>
    </div>
</div>
@endsection