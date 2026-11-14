<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class NotificationController
 *
 * Mengelola penyajian dan pembaruan status notifikasi internal sistem
 * Cash Advance & Settlement PT Technodrive.
 *
 * Sistem notifikasi internal digunakan untuk memberi tahu pengguna secara real-time
 * mengenai perubahan status dokumen penting, seperti:
 * - Pengajuan Cash Advance baru yang butuh persetujuan.
 * - Pengajuan Top-Up dan eskalasi persetujuan Direktur.
 * - Pencairan dana oleh Finance yang memulai hitung mundur SLA.
 * - Hasil verifikasi struk (Valid, Perlu Perbaikan, Ditolak, Void).
 * - Peringatan saldo menipis (< 20%) dan peringatan keterlambatan (overdue).
 *
 * @package App\Http\Controllers
 */
class NotificationController extends Controller
{
    /**
     * Menampilkan daftar riwayat notifikasi milik pengguna yang sedang login.
     *
     * Data disajikan secara terpaginasi sebanyak 20 notifikasi per halaman,
     * diurutkan dari yang paling baru.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // 1. Ambil relasi notifikasi milik pengguna yang sedang aktif dengan paginasi
        $notifications = Auth::user()->notifications()->paginate(20);

        // 2. Tampilkan ke halaman indeks notifikasi
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Menandai satu notifikasi tertentu sebagai telah dibaca (is_read = true).
     *
     * Alur kerja:
     * 1. Memverifikasi kepemilikan notifikasi (mencegah pengguna lain membaca/mengubah notifikasi orang lain).
     * 2. Mengubah status 'is_read' menjadi true.
     * 3. Jika notifikasi memuat tautan rujukan (action link), alihkan pengguna langsung ke halaman dokumen terkait.
     * 4. Jika tidak memuat tautan, kembalikan ke halaman sebelumnya.
     *
     * @param  \App\Models\SystemNotification  $notification Model notifikasi yang akan diperbarui.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead(SystemNotification $notification): RedirectResponse
    {
        // 1. Otorisasi kepemilikan: Pengguna hanya boleh menandai notifikasinya sendiri
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak untuk mengakses notifikasi ini.');
        }

        // 2. Perbarui status dibaca
        $notification->update(['is_read' => true]);

        // 3. Jika ada rute tujuan (link dokumen spesifik), alihkan pengguna langsung ke rute tersebut
        if ($notification->link) {
            return redirect($notification->link);
        }

        // 4. Default: kembalikan ke halaman sebelumnya
        return back();
    }

    /**
     * Menandai seluruh notifikasi yang belum dibaca milik pengguna aktif sebagai telah dibaca.
     *
     * Fungsionalitas "Tandai Semua Sudah Dibaca" untuk membersihkan badge notifikasi
     * yang masih pending pada antarmuka pengguna.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead(): RedirectResponse
    {
        // 1. Perbarui seluruh notifikasi aktif milik pengguna yang masih bernilai is_read = false
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        // 2. Kembalikan ke halaman sebelumnya dengan pesan konfirmasi
        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
