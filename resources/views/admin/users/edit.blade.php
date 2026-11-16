@extends('layouts.app')

@section('title', 'Edit Pengguna - ' . $user->name)
@section('page_title', 'Edit Pengguna: ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-slate-500 hover:text-retail-ink inline-flex items-center space-x-1 mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar User</span>
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Edit Data Pengguna</h1>
        <p class="text-xs text-slate-500 mt-1">Perbarui role hak akses, atasan langsung, atau informasi rekening karyawan.</p>
    </div>

    <div class="card-commerce p-6 sm:p-7">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ avatarPreview: '{{ $user->avatar_url }}' }">
            @csrf
            @method('PUT')

            <!-- Avatar Field -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row items-center sm:items-start gap-4">
                <div class="relative w-20 h-20 rounded-xl overflow-hidden border-2 border-slate-300 bg-[#161A2B] shadow-sm flex-shrink-0">
                    <template x-if="avatarPreview">
                        <img :src="avatarPreview" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!avatarPreview">
                        <div class="w-full h-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-xl uppercase">
                            {{ $user->initials }}
                        </div>
                    </template>
                </div>
                <div class="flex-1 space-y-2 w-full">
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider">Foto Profil (Avatar)</label>
                    <input type="file" name="avatar" accept="image/*"
                        @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => avatarPreview = e.target.result; reader.readAsDataURL(file); }"
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#161A2B] file:text-white hover:file:bg-[#FF3C00] file:cursor-pointer border border-slate-200 rounded-lg p-1 bg-white">
                    @if($user->avatar)
                        <label class="inline-flex items-center space-x-2 text-xs text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_avatar" value="1" @change="if ($event.target.checked) avatarPreview = null" class="rounded text-rose-600">
                            <span>Hapus foto profil saat ini</span>
                        </label>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full input-commerce">
            </div>

            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Email Kantor <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full input-commerce font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Password Baru (Kosongkan bila tidak diubah)</label>
                <input type="password" name="password" minlength="6"
                    class="w-full input-commerce" placeholder="••••••••">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full input-commerce font-bold">
                        <option value="karyawan" {{ $user->role === 'karyawan' ? 'selected' : '' }}>Karyawan (Pegawai Lapangan/Staff)</option>
                        <option value="atasan" {{ $user->role === 'atasan' ? 'selected' : '' }}>Atasan (Manager Langsung)</option>
                        <option value="finance" {{ $user->role === 'finance' ? 'selected' : '' }}>Finance (Admin Keuangan & Pajak)</option>
                        <option value="direktur" {{ $user->role === 'direktur' ? 'selected' : '' }}>Direktur (Pimpinan Perusahaan)</option>
                        <option value="super_admin" {{ $user->role === 'super_admin' ? 'selected' : '' }}>Super Admin (Pengelola Teknis)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Atasan Langsung</label>
                    <select name="manager_id" class="w-full input-commerce">
                        <option value="">-- Tanpa Atasan (Direksi) --</option>
                        @foreach($managers as $mgr)
                            <option value="{{ $mgr->id }}" {{ $user->manager_id === $mgr->id ? 'selected' : '' }}>{{ $mgr->name }} ({{ $mgr->role }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Nomor WhatsApp / HP</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full input-commerce font-mono">
            </div>

            <!-- Tag Divisi / Departemen (Maksimal 10 Divisi dengan format Tag: [Nama Divisi]) -->
            <div x-data="{
                tags: {{ json_encode(old('departments', $user->departments ?? [])) }},
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
                        Tag Departemen / Divisi <span class="text-[#FF3C00] font-normal normal-case text-xs">(Maks. 10 Divisi)</span>
                    </label>
                    <span class="text-xs font-mono px-2 py-0.5 rounded-full font-bold"
                        :class="tags.length >= maxTags ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'"
                        x-text="'# ' + tags.length + ' / ' + maxTags + ' Divisi'">
                    </span>
                </div>

                <!-- Tag Badges Display -->
                <div class="flex flex-wrap gap-1.5 min-h-[40px] p-2 bg-slate-50 rounded-lg border border-slate-200 items-center">
                    <template x-for="(tag, index) in tags" :key="index">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-mono font-medium bg-[#050A1E] text-white shadow-sm border border-[#050A1E]">
                            <span class="text-[#FFC704] font-bold">#</span>
                            <span x-text="tag"></span>
                            <button type="button" @click="removeTag(index)" class="text-slate-400 hover:text-[#FF3C00] ml-1 transition font-bold" title="Hapus tag">&times;</button>
                            <input type="hidden" name="departments[]" :value="tag">
                        </span>
                    </template>
                    <div x-show="tags.length === 0" class="inline-flex items-center space-x-1.5 py-0.5 px-2 rounded bg-white text-slate-400 font-mono text-xs border border-dashed border-slate-300">
                        <span class="font-bold">#</span>
                        <span class="font-sans italic">Tidak ada tag</span>
                    </div>
                </div>

                <!-- Input to Add New Tag -->
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 font-mono font-bold text-xs">
                            #
                        </span>
                        <input type="text" x-model="newTag" @keydown.enter.prevent="addTag()"
                            placeholder="Ketik nama divisi lalu tekan Enter atau klik Tambah Tag..."
                            :disabled="tags.length >= maxTags"
                            class="w-full input-commerce text-xs py-1.5 pl-6 disabled:bg-slate-100 disabled:cursor-not-allowed">
                    </div>
                    <button type="button" @click="addTag()"
                        :disabled="tags.length >= maxTags || !newTag.trim()"
                        class="px-3 py-1.5 bg-[#161A2B] hover:bg-[#FF3C00] text-white text-xs font-bold rounded-lg transition flex items-center space-x-1.5 disabled:opacity-40 disabled:hover:bg-[#161A2B] disabled:cursor-not-allowed cursor-pointer flex-shrink-0 shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Tag</span>
                    </button>
                </div>
            </div>

            <div class="p-4 bg-slate-50 rounded-[6px] border border-retail-surface space-y-2">
                <span class="text-xs font-bold text-retail-ink uppercase block tracking-wide">Data Rekening Bank Karyawan</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}" placeholder="Nama Bank" class="w-full input-commerce text-xs py-1.5 font-mono">
                    </div>
                    <div>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}" placeholder="No. Rekening" class="w-full input-commerce text-xs py-1.5 font-mono">
                    </div>
                    <div>
                        <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $user->bank_account_holder) }}" placeholder="Atas Nama" class="w-full input-commerce text-xs py-1.5">
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded-[2px] border-slate-300 text-retail-orange focus:ring-retail-orange">
                    <span>Akun Aktif (Bisa Login & Mengakses Sistem)</span>
                </label>
            </div>

            <div class="pt-3 flex justify-end space-x-2 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary py-2 px-4 text-xs">Batal</a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection