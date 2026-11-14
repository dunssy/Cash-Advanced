<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class SettingController
 *
 * Mengelola konfigurasi parameter global sistem Cash Advance & Settlement PT Technodrive.
 *
 * Controller ini secara eksklusif dikhususkan untuk Super Admin (Admin IT) guna mengatur
 * parameter bisnis kritis seperti:
 * - Batas durasi maksimal masa aktif Cash Advance (default: 60 hari).
 * - Ambang batas nominal pengajuan Top-Up yang memerlukan persetujuan Direktur.
 * - Ambang batas persentase Top-Up terhadap nilai awal CA (default: 50%).
 * - Batas frekuensi pengajuan Top-Up sebelum eskalasi ke Direktur (default: 2 kali).
 * - Ambang batas peringatan saldo menipis (default: 20%).
 *
 * @package App\Http\Controllers\Admin
 */
class SettingController extends Controller
{
    /**
     * Menampilkan halaman konfigurasi parameter sistem.
     *
     * Memastikan hanya pengguna dengan hak akses Super Admin yang dapat membuka halaman ini,
     * lalu mengambil seluruh daftar pengaturan sistem dari tabel SystemSetting.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // 1. Verifikasi hak akses Super Admin
        $this->ensureSuperAdmin();

        // 2. Ambil seluruh data konfigurasi sistem
        $settings = SystemSetting::all();

        // 3. Tampilkan antarmuka manajemen konfigurasi
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Menyimpan pembaruan konfigurasi parameter sistem.
     *
     * Alur kerja:
     * 1. Memverifikasi hak akses Super Admin.
     * 2. Merekam konfigurasi nilai lama (old values) untuk keperluan perbandingan jejak audit.
     * 3. Melakukan pembaruan nilai setiap kunci pengaturan (key-value pair) ke basis data.
     * 4. Mengambil konfigurasi nilai baru (new values).
     * 5. Mencatat aktivitas pembaruan konfigurasi ke dalam AuditLog secara permanen.
     * 6. Mengalihkan kembali pengguna disertai notifikasi keberhasilan.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat array data pengaturan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        // 1. Verifikasi hak akses Super Admin
        $this->ensureSuperAdmin();

        // 2. Ambil data input pengaturan dari formulir
        $settingsData = $request->input('settings', []);

        // 3. Rekam nilai konfigurasi saat ini sebelum diperbarui (Audit Trail baseline)
        $oldValues = SystemSetting::all()->pluck('value', 'key')->toArray();

        // 4. Perbarui nilai setiap parameter pengaturan di basis data
        foreach ($settingsData as $key => $value) {
            SystemSetting::where('key', $key)->update(['value' => $value]);
        }

        // 5. Rekam nilai konfigurasi terbaru setelah pembaruan disimpan
        $newValues = SystemSetting::all()->pluck('value', 'key')->toArray();

        // 6. Catat perubahan konfigurasi global ke dalam tabel AuditLog
        AuditLog::record(
            'CONFIG_UPDATED',
            null,
            "Konfigurasi sistem diperbarui oleh Super Admin.",
            $oldValues,
            $newValues
        );

        // 7. Kembalikan ke halaman sebelumnya dengan pesan notifikasi sukses
        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }

    /**
     * Memastikan pengguna yang sedang login memiliki peran Super Admin.
     *
     * Jika pengguna bukan Super Admin, eksekusi akan langsung dihentikan
     * dengan melempar HTTP 403 Forbidden Error.
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
