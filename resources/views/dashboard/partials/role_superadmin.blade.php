{{-- 
    Partial: Dashboard Role SUPER_ADMIN (IT Administration & System Config)
    Fokus kebutuhan Super Admin:
    1. Manajemen Akun Pengguna & Hak Akses
    2. Konfigurasi Parameter Global Sistem
    3. Pengawasan Log Audit Keamanan
--}}

<div class="card-commerce p-6 border border-cardborder rounded-2xl bg-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center space-x-2 text-xs font-head font-bold uppercase tracking-wider text-slate-500 mb-1">
            <i data-lucide="shield" class="w-4 h-4 text-[#FF3C00]"></i>
            <span>Administrasi & Pengaturan Sistem</span>
        </div>
        <h3 class="text-base font-head font-bold text-[#050A1E]">Pusat Konfigurasi Master & Pengguna</h3>
        <p class="text-xs text-[#7A7A7A] mt-1 font-body max-w-xl">
            Super Admin bertanggung jawab atas kelola pengguna master, hak akses peran, relasi departemen, dan audit integritas sistem.
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
        <a href="{{ route('admin.users.index') }}" class="btn-brand text-xs py-2.5 px-4 shadow-sm">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            <span>Kelola User</span>
        </a>
        <a href="{{ route('admin.settings.index') }}" class="btn-secondary text-xs py-2.5 px-4 shadow-sm">
            <i data-lucide="settings" class="w-3.5 h-3.5"></i>
            <span>Parameter Global</span>
        </a>
        <a href="{{ route('soft-deletes.index') }}" class="btn-secondary text-xs py-2.5 px-4 shadow-sm">
            <i data-lucide="archive" class="w-3.5 h-3.5"></i>
            <span>Arsip Data</span>
        </a>
    </div>
</div>
