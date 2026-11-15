<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Class ProfileController
 *
 * Mengelola pengaturan profil pribadi (biodata dan foto profil / avatar) untuk
 * setiap pengguna yang sedang login di sistem Cash Advance PT Technodrive.
 *
 * Karakteristik Pengaturan Profil Mandiri:
 * 1. Setiap karyawan dapat memperbarui data personal mandiri: nama, kontak, departemen,
 *    data rekening penerimaan dana kas operasional, dan kata sandi baru.
 * 2. Pengguna dapat mengunggah atau mengganti foto profil pribadi (format JPG/PNG/WEBP).
 * 3. Hak Akses Peran (Role) Bersifat Terkunci (Read-Only):
 *    Pengguna biasa dilarang dan tidak dapat mengubah hak akses peran (role) mereka sendiri
 *    ("bukan memilih role lagi"), karena wewenang penetapan peran adalah hak mutlak Super Admin / HRD.
 * 4. Setiap perubahan profil dicatat dalam Audit Log untuk transparansi sistem.
 *
 * @package App\Http\Controllers
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan formulir pengaturan profil dan biodata pengguna yang sedang login.
     *
     * Data yang disajikan mencakup:
     * - Pratinjau foto profil saat ini (atau inisial nama jika belum ada foto).
     * - Informasi peran (role) akun dan atasan langsung yang disajikan secara informatif (read-only).
     * - Isian data personal, kontak, dan nomor rekening perbankan.
     *
     * @return \Illuminate\View\View
     */
    public function edit(): View
    {
        // 1. Ambil data pengguna yang sedang login beserta relasi atasan langsung
        $user = Auth::user();
        $user->load('manager');

        // 2. Tampilkan view formulir profil
        return view('profile.edit', compact('user'));
    }

    /**
     * Memperbarui biodata dan foto profil pengguna yang sedang login.
     *
     * Alur kerja:
     * 1. Validasi input formulir: nama, email unik (mengecualikan ID sendiri), nomor telepon,
     *    departemen, data rekening bank, berkas foto avatar, dan konfirmasi kata sandi lama/baru.
     * 2. Menjaga Integritas Peran: Nilai 'role' tidak diambil dari formulir dan tidak diizinkan diubah.
     * 3. Pemrosesan Foto Profil:
     *    - Jika opsi hapus foto dicentang: hapus file lama dan kosongkan kolom avatar.
     *    - Jika ada berkas foto baru: simpan ke direktori 'avatars' di storage publik dan perbarui path avatar.
     * 4. Pembaruan Password:
     *    - Hanya diproses jika kata sandi baru diisi dan kata sandi saat ini valid.
     * 5. Merekam perubahan profil ke dalam Audit Log.
     * 6. Mengembalikan respon sukses ke halaman profil.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat berkas foto dan biodata baru.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // 1. Validasi data masukan dari pengguna
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable'],
            'departments' => ['nullable'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // Maksimal 5MB
            'remove_avatar' => ['nullable', 'boolean'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email kantor wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'avatar.image' => 'Berkas foto profil harus berupa file gambar.',
            'avatar.mimes' => 'Format foto harus berupa JPG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran file foto maksimal adalah 5MB.',
            'current_password.current_password' => 'Kata sandi saat ini yang Anda masukkan tidak sesuai.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $oldValues = $user->toArray();

        // 2. Siapkan data biodata yang diizinkan untuk diperbarui secara mandiri
        // Pemrosesan tag divisi (maksimal 10 divisi dicantumkan sebagai nama tag)
        $departmentsInput = $request->input('departments', $request->input('department'));
        if (is_string($departmentsInput) && str_starts_with(trim($departmentsInput), '[') && str_ends_with(trim($departmentsInput), ']')) {
            $departmentsInput = json_decode($departmentsInput, true);
        }
        if (is_array($departmentsInput)) {
            $departmentsInput = array_slice(array_values(array_filter(array_map('trim', $departmentsInput))), 0, 10);
        }

        // Perhatikan: 'role' sengaja TIDAK dimasukkan agar tidak dapat diubah oleh user sendiri.
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department' => $departmentsInput ?: null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
        ];

        // 3. Penanganan penghapusan foto profil
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = null;
        }

        // 4. Penanganan unggah berkas foto profil baru
        if ($request->hasFile('avatar')) {
            // Hapus file avatar lama jika ada dan tersimpan di storage lokal
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Simpan foto baru ke folder 'avatars' di storage disk public
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        // 5. Penanganan perubahan password baru jika diisi
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // 6. Simpan pembaruan data pengguna ke basis data
        $user->update($data);

        // 7. Catat pembaruan profil ke Audit Log untuk transparansi
        AuditLog::record(
            'PROFILE_UPDATED',
            $user,
            "Pengguna {$user->name} memperbarui biodata dan profil pribadinya.",
            $oldValues,
            $user->toArray()
        );

        // 8. Kembalikan ke halaman profil dengan pesan sukses
        return back()->with('success', 'Biodata dan foto profil Anda berhasil diperbarui.');
    }
}
