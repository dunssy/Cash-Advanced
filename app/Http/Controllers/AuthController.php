<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class AuthController
 *
 * Mengelola seluruh proses autentikasi pengguna pada sistem Cash Advance & Settlement PT Technodrive.
 * Mencakup alur tampilan login standar, otentikasi kredensial (email & password),
 * fitur pengalihan cepat akun demo (Quick Login) untuk pengujian peran (role-switching),
 * serta proses logout yang aman disertai pencatatan Audit Log otomatis.
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * Menampilkan halaman login sistem.
     *
     * Jika pengguna sudah dalam keadaan terautentikasi (logged-in), maka akan langsung dialihkan
     * ke halaman dashboard utama. Untuk mempermudah demonstrasi dan pengujian alur peran
     * (Karyawan, Atasan, Finance, Direktur, Super Admin), daftar user diurutkan secara hierarkis
     * dan dikirimkan ke view login.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLogin(): View|RedirectResponse
    {
        // 1. Cek status sesi autentikasi pengguna
        // Jika sudah login, langsung arahkan ke dashboard aplikasi
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // 2. Ambil daftar user demo untuk pengujian multi-role
        // Urutan peran: Karyawan -> Atasan -> Finance -> Direktur -> Super Admin
        $demoUsers = User::orderByRaw("
            CASE role 
                WHEN 'karyawan' THEN 1 
                WHEN 'atasan' THEN 2 
                WHEN 'finance' THEN 3 
                WHEN 'direktur' THEN 4 
                WHEN 'super_admin' THEN 5 
                ELSE 6 
            END
        ")->get();

        // 3. Tampilkan antarmuka login beserta opsi user demo
        return view('auth.login', compact('demoUsers'));
    }

    /**
     * Memproses otentikasi login pengguna menggunakan email dan password.
     *
     * Alur kerja:
     * 1. Melakukan validasi input form (email dan password wajib diisi).
     * 2. Mencoba mencocokkan kredensial dengan guard autentikasi Laravel.
     * 3. Jika berhasil, lakukan regenerasi ID sesi untuk mencegah serangan Session Fixation.
     * 4. Catat aktivitas login ke dalam tabel AuditLog sebagai jejak kepatuhan sistem.
     * 5. Alihkan pengguna ke rute yang sebelumnya dituju (intended) atau dashboard default.
     * 6. Jika gagal, kembalikan ke halaman login disertai pesan error spesifik.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat kredensial pengguna.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Validasi input kredensial login
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Cek apakah opsi "Remember Me" dicentang
        $remember = $request->boolean('remember');

        // 3. Eksekusi percobaan login
        if (Auth::attempt($credentials, $remember)) {
            // Regenerasi sesi untuk keamanan (pencegahan Session Fixation Attack)
            $request->session()->regenerate();
            
            // Catat log aktivitas login ke Audit Trail
            $user = Auth::user();
            AuditLog::record('LOGIN', $user, "User {$user->name} ({$user->role}) berhasil login.");

            // Arahkan ke rute awal yang ingin diakses sebelumnya, atau rute dashboard default
            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang kembali, {$user->name}!")
                ->with('login_loader', true);
        }

        // 4. Tangani kegagalan autentikasi (kredensial tidak cocok)
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Memfasilitasi perpindahan sesi akun demo secara instan (Quick Login).
     *
     * Fitur ini sangat krusial selama fase pengembangan dan peninjauan sistem untuk
     * menguji alur kerja multi-tier (misal: pengajuan oleh Karyawan -> persetujuan Atasan ->
     * verifikasi Finance -> pengawasan Direktur) tanpa harus mengetik password berulang kali.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat user_id tujuan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function quickLogin(Request $request): RedirectResponse
    {
        // 1. Validasi keberadaan user_id yang dipilih
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // 2. Temukan user dan lakukan proses login manual
        $user = User::findOrFail($request->user_id);
        Auth::login($user);

        // 3. Regenerasi sesi untuk integritas keamanan sesi baru
        $request->session()->regenerate();

        // 4. Catat perpindahan akun ke Audit Log untuk transparansi sistem
        AuditLog::record('QUICK_LOGIN', $user, "Quick switch login sebagai {$user->name} ({$user->role}).");

        // 5. Alihkan ke dashboard utama dengan notifikasi sukses
        return redirect()->route('dashboard')
            ->with('success', "Berhasil masuk sebagai {$user->name} ({$user->role}).")
            ->with('login_loader', true);
    }

    /**
     * Memproses penghentian sesi login pengguna (Logout).
     *
     * Alur kerja:
     * 1. Mencatat aktivitas logout pengguna ke tabel AuditLog.
     * 2. Menghapus sesi autentikasi pengguna aktif.
     * 3. Melakukan invalidasi sesi dan regenerasi token proteksi CSRF.
     * 4. Mengarahkan kembali ke halaman login utama dengan pesan konfirmasi.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request sesi aktif saat ini.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        // 1. Ambil data user yang sedang aktif dan catat peristiwa logout ke Audit Log
        $user = Auth::user();
        if ($user) {
            AuditLog::record('LOGOUT', $user, "User {$user->name} telah logout.");
        }

        // 2. Bersihkan status autentikasi guard
        Auth::logout();

        // 3. Batalkan validitas sesi dan buat ulang token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 4. Alihkan pengguna kembali ke form login
        return redirect()->route('login')->with('info', 'Anda telah berhasil logout.');
    }
}
