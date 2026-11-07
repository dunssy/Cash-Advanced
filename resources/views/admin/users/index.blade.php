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
            <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Manajemen Pengguna & Relasi Atasan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Mengatur akun karyawan (~25 orang), hak akses peran (role), dan hierarki atasan langsung.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah User Baru</span>
        </a>
    </div>

    <!-- User Table -->
    <div class="card-commerce overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider border-b border-retail-surface font-sans">
                    <tr>
                        <th class="px-4 py-3">Nama & Email</th>
                        <th class="px-4 py-3">Peran (Role)</th>
                        <th class="px-4 py-3">Atasan Langsung</th>
                        <th class="px-4 py-3">Departemen</th>
                        <th class="px-4 py-3">Rekening Bank (Reimburse)</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-sans">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-bold text-slate-900 block">{{ $u->name }}</span>
                                <span class="text-[11px] text-slate-500 font-mono">{{ $u->email }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="badge-retail text-[10px] uppercase font-bold
                                    {{ $u->role === 'direktur' ? 'bg-amber-50 text-amber-900 border-amber-300' : '' }}
                                    {{ $u->role === 'finance' ? 'bg-purple-50 text-purple-900 border-purple-300' : '' }}
                                    {{ $u->role === 'atasan' ? 'bg-blue-50 text-blue-900 border-blue-300' : '' }}
                                    {{ $u->role === 'karyawan' ? 'bg-emerald-50 text-emerald-900 border-emerald-300' : '' }}
                                    {{ $u->role === 'super_admin' ? 'bg-rose-50 text-rose-900 border-rose-300' : '' }}
                                ">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($u->manager)
                                    <span class="font-bold text-slate-800">{{ $u->manager->name }}</span>
                                    <span class="block text-[10px] font-mono text-slate-400">({{ $u->manager->role }})</span>
                                @else
                                    <span class="text-slate-400 italic font-mono text-[11px]">Direksi (Puncak)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
                                {{ $u->department ?: '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-mono text-xs">
                                @if($u->bank_name)
                                    <span class="font-bold text-slate-800">{{ $u->bank_name }} - {{ $u->bank_account_number }}</span>
                                    <span class="block text-[10px] text-slate-400 font-sans">a.n {{ $u->bank_account_holder }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($u->is_active)
                                    <span class="badge-retail bg-emerald-50 text-emerald-800 border-emerald-300">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge-retail bg-slate-100 text-slate-600 border-slate-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn-secondary py-1 px-2.5 text-xs">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span>Edit</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada data user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection