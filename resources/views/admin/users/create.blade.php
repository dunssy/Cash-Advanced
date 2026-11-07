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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Departemen / Divisi</label>
                    <input type="text" name="department" value="{{ old('department') }}"
                        class="w-full input-commerce" placeholder="Engineering / Operasional">
                </div>
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1">Nomor WhatsApp / HP</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full input-commerce font-mono" placeholder="08123456789">
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