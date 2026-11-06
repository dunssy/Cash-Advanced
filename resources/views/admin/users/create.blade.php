@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 inline-flex items-center space-x-1 mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar User</span>
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Pengguna Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Daftarkan akun karyawan, atasan, atau finance PT Technodrives.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Kantor (@technodrives.co.id) <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500" placeholder="nama@technodrives.co.id">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Awal <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required minlength="6"
                    class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500" placeholder="Minimal 6 karakter">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                    <select name="role" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl font-bold">
                        <option value="karyawan">Karyawan (Pegawai Lapangan/Staff)</option>
                        <option value="atasan">Atasan (Manager Langsung)</option>
                        <option value="finance">Finance (Admin Keuangan & Pajak)</option>
                        <option value="direktur">Direktur (Pimpinan Perusahaan)</option>
                        <option value="super_admin">Super Admin (Pengelola Teknis)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Atasan Langsung</label>
                    <select name="manager_id" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl">
                        <option value="">-- Tanpa Atasan (Direksi) --</option>
                        @foreach($managers as $mgr)
                            <option value="{{ $mgr->id }}">{{ $mgr->name }} ({{ $mgr->role }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Departemen / Divisi</label>
                    <input type="text" name="department" value="{{ old('department') }}"
                        class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl" placeholder="Engineering / Operasional">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp / HP</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl" placeholder="08123456789">
                </div>
            </div>

            <!-- Bank Details for Reimbursements -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <span class="text-xs font-bold text-slate-700 uppercase block">Data Rekening Bank Karyawan (Untuk Pembayaran Reimburse / Gaji)</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Nama Bank (BCA, Mandiri)" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="No. Rekening" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder') }}" placeholder="Atas Nama Rekening" class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-2 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600">Batal</a>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow">Simpan User</button>
            </div>
        </form>
    </div>
</div>
@endsection
