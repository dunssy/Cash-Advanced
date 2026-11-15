@extends('layouts.app')

@section('title', 'Pengaturan Profil & Biodata - ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="inline-flex items-center space-x-2 text-xs font-head font-bold uppercase tracking-wider text-slate-500 mb-1">
                <i data-lucide="user-cog" class="w-3.5 h-3.5 text-[#FF3C00]"></i>
                <span>Pengaturan Akun</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold font-head text-[#050A1E] tracking-tight">Profil & Biodata Pengguna</h1>
            <p class="text-xs text-slate-500 font-body mt-0.5">
                Kelola informasi biodata personal, foto profil kerja, dan rekening bank pencairan kas operasional Anda.
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-secondary text-xs self-start sm:self-auto inline-flex items-center space-x-1.5 py-2 px-3">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Profile Form -->
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ avatarPreview: '{{ $user->avatar_url }}' }">
        @csrf
        @method('PUT')

        <!-- Card 1: Foto Profil & Info Peran (Role Read-Only) -->
        <div class="card-commerce p-5 sm:p-6 bg-white border border-cardborder rounded-2xl shadow-sm">
            <h2 class="text-sm font-bold font-head text-[#050A1E] uppercase tracking-wider mb-4 flex items-center space-x-2">
                <i data-lucide="camera" class="w-4 h-4 text-[#FF3C00]"></i>
                <span>Foto Profil & Status Akun</span>
            </h2>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <!-- Avatar Preview -->
                <div class="flex flex-col items-center space-y-2 flex-shrink-0">
                    <div class="relative w-28 h-28 rounded-2xl overflow-hidden border-2 border-slate-200 bg-[#161A2B] shadow-md group">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!avatarPreview">
                            <div class="w-full h-full bg-[#FF3C00] text-white flex items-center justify-center font-head font-bold text-2xl uppercase">
                                {{ $user->initials }}
                            </div>
                        </template>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">Format: JPG, PNG, WEBP</span>
                </div>

                <!-- Avatar Actions & Read-Only Role Information -->
                <div class="flex-1 space-y-4 w-full">
                    <div>
                        <label class="block text-xs font-bold text-[#050A1E] uppercase tracking-wider mb-1.5 font-head">
                            Unggah Foto Profil Baru
                        </label>
                        <input type="file" name="avatar" accept="image/*"
                            @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => avatarPreview = e.target.result; reader.readAsDataURL(file); }"
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#161A2B] file:text-white hover:file:bg-[#FF3C00] file:cursor-pointer file:transition border border-cardborder rounded-xl p-1.5 bg-slate-50">
                        <p class="text-[11px] text-slate-400 mt-1">Disarankan menggunakan foto potret kerja formal / *headshot* profesional (maks. 5MB).</p>
                    </div>

                    @if($user->avatar)
                        <label class="inline-flex items-center space-x-2 text-xs text-rose-600 font-semibold cursor-pointer">
                            <input type="checkbox" name="remove_avatar" value="1" @change="if ($event.target.checked) avatarPreview = null" class="rounded text-rose-600 focus:ring-rose-500">
                            <span>Hapus foto profil dan gunakan inisial nama standar</span>
                        </label>
                    @endif

                    <!-- Protected Role Badge Notice (Bukan memilih role lagi) -->
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-start space-x-3">
                        <div class="p-1.5 rounded-lg bg-slate-200 text-slate-600 flex-shrink-0 mt-0.5">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                <span class="text-xs font-bold text-[#050A1E] font-head">Hak Akses Peran:</span>
                                <span class="badge-retail text-[11px] uppercase font-bold
                                    {{ $user->role === 'direktur' ? 'bg-[#FFC704] text-[#050A1E]' : '' }}
                                    {{ $user->role === 'finance' ? 'bg-[#161A2B] text-white' : '' }}
                                    {{ $user->role === 'atasan' ? 'bg-[#050A1E] text-white' : '' }}
                                    {{ $user->role === 'karyawan' ? 'bg-[#F4F4F4] text-[#050A1E] border border-cardborder' : '' }}
                                    {{ $user->role === 'super_admin' ? 'bg-[#FF3C00] text-white' : '' }}
                                ">
                                    {{ strtoupper($user->role) }}
                                </span>
                                @if($user->manager)
                                    <span class="text-[11px] text-slate-500">Atasan: <strong>{{ $user->manager->name }}</strong></span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Peran akun ditetapkan oleh manajemen/HRD untuk tata kelola kepatuhan (*Separation of Duties*) dan tidak dapat diubah sendiri.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Biodata Diri & Kontak -->
        <div class="card-commerce p-5 sm:p-6 bg-white border border-cardborder rounded-2xl shadow-sm space-y-4">
            <h2 class="text-sm font-bold font-head text-[#050A1E] uppercase tracking-wider mb-2 flex items-center space-x-2">
                <i data-lucide="user" class="w-4 h-4 text-[#FF3C00]"></i>
                <span>Biodata Personal & Kontak</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full input-commerce rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                        Email Kantor <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full input-commerce rounded-xl font-mono text-xs">
                </div>
            </div>

                <div>
                    <label class="block text-xs font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                        Nomor HP / WhatsApp
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                        placeholder="Contoh: 081234567890"
                        class="w-full input-commerce rounded-xl font-mono text-xs">
                </div>
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
            }" class="space-y-2 pt-3 border-t border-slate-100">
                <input type="hidden" name="departments" value="">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-[#050A1E] uppercase tracking-wider font-head">
                        Tag Departemen / Divisi <span class="text-[#FF3C00] font-normal normal-case text-[11px]">(Maks. 10 Divisi)</span>
                    </label>
                    <span class="text-[11px] font-mono px-2 py-0.5 rounded-full font-bold"
                        :class="tags.length >= maxTags ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'"
                        x-text="tags.length + ' / ' + maxTags + ' Divisi'">
                    </span>
                </div>

                <!-- Tag Badges Display -->
                <div class="flex flex-wrap gap-1.5 min-h-[42px] p-2 bg-[#F8F9FA] rounded-xl border border-slate-200 items-center">
                    <template x-for="(tag, index) in tags" :key="index">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-mono font-medium bg-[#050A1E] text-white shadow-sm border border-[#050A1E] group">
                            <span class="text-[#FFC704] font-bold">Tag:</span>
                            <span x-text="tag"></span>
                            <button type="button" @click="removeTag(index)" class="text-slate-400 hover:text-[#FF3C00] ml-1 transition font-bold" title="Hapus tag">&times;</button>
                            <input type="hidden" name="departments[]" :value="tag">
                        </span>
                    </template>
                    <span x-show="tags.length === 0" class="text-xs text-slate-400 italic py-1 px-1">
                        Belum ada tag divisi. Tambahkan nama divisi di bawah ini (maksimal 10 divisi).
                    </span>
                </div>

                <!-- Input to Add New Tag -->
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <input type="text" x-model="newTag" @keydown.enter.prevent="addTag()"
                            placeholder="Ketik nama divisi lalu tekan Enter atau klik tombol Tambah Tag..."
                            :disabled="tags.length >= maxTags"
                            class="w-full input-commerce rounded-xl text-xs py-2 disabled:bg-slate-100 disabled:cursor-not-allowed">
                    </div>
                    <button type="button" @click="addTag()"
                        :disabled="tags.length >= maxTags || !newTag.trim()"
                        class="px-4 py-2 bg-[#161A2B] hover:bg-[#FF3C00] text-white text-xs font-head font-bold rounded-xl transition flex items-center space-x-1.5 disabled:opacity-40 disabled:hover:bg-[#161A2B] disabled:cursor-not-allowed cursor-pointer flex-shrink-0 shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Tag Divisi</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-500 font-body">
                    Ketik nama divisi (contoh: <em>Operasional</em>, <em>Engineering</em>, <em>Logistik</em>) lalu tekan <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded text-[10px] font-mono">Enter</kbd> atau klik tombol <strong>Tambah Tag Divisi</strong>. Otomatis tercantum sebagai <code>Tag: [Nama Divisi]</code>.
                </p>
            </div>
        </div>

        <!-- Card 3: Rekening Bank Pencairan Kas -->
        <div class="card-commerce p-5 sm:p-6 bg-white border border-cardborder rounded-2xl shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold font-head text-[#050A1E] uppercase tracking-wider flex items-center space-x-2">
                    <i data-lucide="credit-card" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>Rekening Bank Operasional</span>
                </h2>
                <span class="text-[10px] text-slate-400 font-mono">Tujuan transfer pencairan CA / Top-Up</span>
            </div>

            <p class="text-xs text-slate-500 font-body">
                Pastikan informasi rekening bank di bawah ini valid dan sesuai dengan buku tabungan Anda untuk kelancaran transfer pencairan dana oleh tim Finance.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                        Nama Bank
                    </label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}"
                        placeholder="Contoh: BCA / Mandiri / BNI"
                        class="w-full input-commerce rounded-xl font-mono text-xs">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                        Nomor Rekening
                    </label>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}"
                        placeholder="Nomor Rekening"
                        class="w-full input-commerce rounded-xl font-mono text-xs">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                        Nama Pemilik Rekening
                    </label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $user->bank_account_holder) }}"
                        placeholder="Atas Nama Pemilik"
                        class="w-full input-commerce rounded-xl text-xs">
                </div>
            </div>
        </div>

        <!-- Card 4: Ganti Kata Sandi (Opsional) -->
        <div class="card-commerce p-5 sm:p-6 bg-white border border-cardborder rounded-2xl shadow-sm space-y-4" x-data="{ changePassword: false }">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold font-head text-[#050A1E] uppercase tracking-wider flex items-center space-x-2">
                    <i data-lucide="key-round" class="w-4 h-4 text-[#FF3C00]"></i>
                    <span>Keamanan & Kata Sandi</span>
                </h2>
                <button type="button" @click="changePassword = !changePassword" class="text-xs font-bold font-head text-[#FF3C00] hover:underline cursor-pointer">
                    <span x-show="!changePassword">+ Ubah Kata Sandi</span>
                    <span x-show="changePassword">- Batalkan Ubah Sandi</span>
                </button>
            </div>

            <div x-show="changePassword" x-transition class="space-y-3 pt-2 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                        Kata Sandi Saat Ini
                    </label>
                    <input type="password" name="current_password" placeholder="Masukkan kata sandi lama Anda"
                        class="w-full input-commerce rounded-xl text-xs">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                            Kata Sandi Baru (Min. 6 Karakter)
                        </label>
                        <input type="password" name="password" minlength="6" placeholder="Kata sandi baru"
                            class="w-full input-commerce rounded-xl text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#050A1E] uppercase tracking-wider mb-1 font-head">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input type="password" name="password_confirmation" minlength="6" placeholder="Ulangi kata sandi baru"
                            class="w-full input-commerce rounded-xl text-xs">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end space-x-3 pt-2">
            <a href="{{ route('dashboard') }}" class="btn-secondary py-2.5 px-5 text-xs font-head font-bold rounded-xl">
                Batal
            </a>
            <button type="submit" class="btn-primary py-2.5 px-6 text-xs font-head font-bold rounded-xl shadow-md hover:shadow-lg transition">
                Simpan Pembaruan Profil
            </button>
        </div>
    </form>
</div>
@endsection
