<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Class UserController
 *
 * Mengelola siklus hidup data pengguna (User Management / CRUD) dalam sistem
 * Cash Advance & Settlement PT Technodrive.
 *
 * Fungsionalitas pengontrol ini dibatasi secara ketat hanya untuk peran Super Admin.
 * Mencakup tugas krusial seperti:
 * - Menampilkan direktori user perusahaan beserta filter hierarki dan peran.
 * - Mendaftarkan karyawan baru dengan penetapan atasan langsung (manager_id) dan rekening bank operasional.
 * - Memperbarui informasi personal, mutasi departemen, rotasi atasan, hingga status keaktifan akun.
 * - Mencatat setiap tindakan penambahan atau perubahan profil ke dalam Audit Log untuk akuntabilitas.
 *
 * @package App\Http\Controllers\Admin
 */
class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna terdaftar dalam sistem.
     *
     * Mendukung pemfilteran berdasarkan peran (role) dan pencarian kata kunci (search)
     * yang mencakup nama, email, serta departemen pengguna, dilengkapi dengan paginasi.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat parameter filter/pencarian.
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        // 1. Pastikan hanya Super Admin yang memiliki hak akses
        $this->ensureSuperAdmin();

        // 2. Siapkan kueri data user beserta relasi atasan (manager)
        $query = User::with('manager')->orderBy('name');

        // 3. Filter berdasarkan peran pengguna jika dipilih
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // 4. Filter pencarian teks bebas (nama, email, departemen)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // 5. Terapkan paginasi 15 data per halaman dengan mempertahankan query string URL
        $users = $query->paginate(15)->withQueryString();

        // 6. Tampilkan ke view daftar pengguna
        return view('admin.users.index', compact('users'));
    }

    /**
     * Menampilkan formulir pendaftaran pengguna baru.
     *
     * Mengambil daftar user yang memiliki peran 'atasan' atau 'direktur'
     * untuk dijadikan pilihan atasan langsung bagi akun baru yang akan dibuat.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        // 1. Pastikan hanya Super Admin yang dapat mengakses formulir pembuatan user
        $this->ensureSuperAdmin();

        // 2. Ambil daftar atasan potensial (user dengan peran atasan atau direktur)
        $managers = User::whereIn('role', ['atasan', 'direktur'])->get();

        // 3. Render antarmuka formulir tambah user
        return view('admin.users.create', compact('managers'));
    }

    /**
     * Menyimpan data pengguna baru ke dalam basis data.
     *
     * Alur kerja:
     * 1. Melakukan validasi input form secara ketat beserta pesan kesalahan berbahasa Indonesia.
     * 2. Melakukan hashing pada password sebelum persistensi data.
     * 3. Membuat entitas User baru dengan status aktif default.
     * 4. Merekam aktivitas penambahan user ke tabel AuditLog.
     * 5. Mengalihkan kembali ke daftar user dengan notifikasi sukses.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request dari form tambah user.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Verifikasi hak akses Super Admin
        $this->ensureSuperAdmin();

        // 2. Validasi input data dari formulir pembuatan akun
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:karyawan,atasan,finance,direktur,super_admin'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
        ],
        // Pesan kesalahan kustom berbahasa Indonesia saat validasi gagal
        [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'manager_id.exists' => 'Atasan tidak valid.',
            'department.max' => 'Nama departemen maksimal 100 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'bank_name.max' => 'Nama bank maksimal 50 karakter.',
            'bank_account_number.max' => 'Nomor rekening bank maksimal 50 karakter.',
            'bank_account_holder.max' => 'Nama pemilik rekening bank maksimal 100 karakter.',
        ]);

        // 3. Simpan entitas User baru ke basis data dengan enkripsi password (Bcrypt/Argon2)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'manager_id' => $validated['manager_id'] ?: null,
            'department' => $validated['department'],
            'phone' => $validated['phone'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'],
            'is_active' => true,
        ]);

        // 4. Catat peristiwa pembuatan user ke dalam Audit Log sistem
        AuditLog::record(
            'USER_CREATED',
            $user,
            "User baru {$user->name} ({$user->email}, role: {$user->role}) dibuat oleh Super Admin."
        );

        // 5. Alihkan ke halaman indeks user disertai pesan keberhasilan
        return redirect()->route('admin.users.index')->with('success', "User {$user->name} berhasil dibuat.");
    }

    /**
     * Menampilkan formulir edit data pengguna.
     *
     * Mengambil data pengguna terpilih dan daftar kandidat atasan (kecuali user itu sendiri
     * guna menghindari relasi sirkular atau hierarki diri sendiri).
     *
     * @param  \App\Models\User  $user Model user yang akan diedit.
     * @return \Illuminate\View\View
     */
    public function edit(User $user): View
    {
        // 1. Verifikasi hak akses Super Admin
        $this->ensureSuperAdmin();

        // 2. Ambil daftar calon atasan (mengecualikan user yang bersangkutan)
        $managers = User::whereIn('role', ['atasan', 'direktur'])
            ->where('id', '!=', $user->id)
            ->get();

        // 3. Tampilkan antarmuka edit profil user
        return view('admin.users.edit', compact('user', 'managers'));
    }

    /**
     * Memperbarui informasi data pengguna di basis data.
     *
     * Alur kerja:
     * 1. Memvalidasi data masukan dari formulir (termasuk pengecualian unique email untuk user ini).
     * 2. Merekam status lama (old values) untuk keperluan komparasi Audit Trail.
     * 3. Memperbarui password hanya jika input password baru diisi oleh Super Admin.
     * 4. Memperbarui data pengguna pada basis data.
     * 5. Merekam ringkasan perubahan ke dalam AuditLog.
     * 6. Mengalihkan kembali ke daftar pengguna dengan notifikasi sukses.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat perubahan data user.
     * @param  \App\Models\User  $user Model user yang akan diperbarui.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // 1. Verifikasi hak akses Super Admin
        $this->ensureSuperAdmin();

        // 2. Validasi data pembaruan
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:karyawan,atasan,finance,direktur,super_admin'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ],
        [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'manager_id.exists' => 'Atasan tidak valid.',
            'department.max' => 'Nama departemen maksimal 100 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'bank_name.max' => 'Nama bank maksimal 50 karakter.',
            'bank_account_number.max' => 'Nomor rekening bank maksimal 50 karakter.',
            'bank_account_holder.max' => 'Nama pemilik rekening bank maksimal 100 karakter.',
        ]);

        // 3. Simpan data sebelum diperbarui untuk perbandingan jejak audit
        $oldValues = $user->toArray();

        // 4. Siapkan array data yang akan diperbarui
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'manager_id' => $validated['manager_id'] ?: null,
            'department' => $validated['department'],
            'phone' => $validated['phone'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'],
            'is_active' => $request->boolean('is_active'),
        ];

        // 5. Perbarui password hanya jika formulir password baru diisi
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // 6. Simpan pembaruan ke basis data
        $user->update($data);

        // 7. Catat pembaruan profil user ke dalam Audit Log
        AuditLog::record(
            'USER_UPDATED',
            $user,
            "Data user {$user->name} diperbarui oleh Super Admin.",
            $oldValues,
            $user->toArray()
        );

        // 8. Alihkan kembali ke daftar user dengan pesan sukses
        return redirect()->route('admin.users.index')->with('success', "Data user {$user->name} berhasil diperbarui.");
    }

    /**
     * Memastikan pengguna yang sedang mengakses memiliki peran Super Admin.
     *
     * Apabila otorisasi gagal, akan melempar HTTP 403 Forbidden Error.
     *
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function ensureSuperAdmin(): void
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak: Halaman ini hanya dapat diakses oleh Super Admin.');
        }
    }
}
