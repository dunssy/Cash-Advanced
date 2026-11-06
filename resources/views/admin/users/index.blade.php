@extends('layouts.app')

@section('title', 'Kelola Pengguna - Super Admin')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                <i data-lucide="shield-alert" class="w-4 h-4 text-rose-600"></i>
                <span>Super Admin IT Panel (Dokumen Hal 2-3)</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna & Relasi Atasan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Mengatur akun karyawan (~25 orang), hak akses peran (role), dan hierarki atasan langsung.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow transition">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah User Baru</span>
        </a>
    </div>

    <!-- User Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5">Nama & Email</th>
                        <th class="px-4 py-3.5">Peran (Role)</th>
                        <th class="px-4 py-3.5">Atasan Langsung</th>
                        <th class="px-4 py-3.5">Departemen</th>
                        <th class="px-4 py-3.5">Rekening Bank (Reimburse)</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $u->name }}</span>
                                <span class="text-[11px] text-slate-500 font-mono">{{ $u->email }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase
                                    {{ $u->role === 'direktur' ? 'bg-amber-100 text-amber-900' : '' }}
                                    {{ $u->role === 'finance' ? 'bg-purple-100 text-purple-900' : '' }}
                                    {{ $u->role === 'atasan' ? 'bg-blue-100 text-blue-900' : '' }}
                                    {{ $u->role === 'karyawan' ? 'bg-emerald-100 text-emerald-900' : '' }}
                                    {{ $u->role === 'super_admin' ? 'bg-rose-100 text-rose-900' : '' }}
                                ">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($u->manager)
                                    <span class="font-semibold text-slate-800">{{ $u->manager->name }}</span>
                                    <span class="block text-[10px] text-slate-400">({{ $u->manager->role }})</span>
                                @else
                                    <span class="text-slate-400 italic">Tidak ada (Direksi / Puncak)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
                                {{ $u->department ?: '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($u->bank_name)
                                    <span class="font-semibold text-slate-800">{{ $u->bank_name }} - {{ $u->bank_account_number }}</span>
                                    <span class="block text-[10px] text-slate-400">a.n {{ $u->bank_account_holder }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($u->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <a href="{{ route('admin.users.edit', $u) }}" class="px-3 py-1 bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 font-bold rounded-lg transition inline-flex items-center space-x-1">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span>Edit</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada pengguna terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
