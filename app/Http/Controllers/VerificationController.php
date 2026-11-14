<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class VerificationController
 *
 * Mengelola proses audit dan verifikasi bukti pengeluaran (struk/nota/kuitansi fisik)
 * yang dicatat oleh pemegang Cash Advance pada sistem PT Technodrive.
 *
 * Proses verifikasi dijalankan oleh tim Finance atau Direktur dengan menerapkan
 * prinsip Pemisahan Tugas (Separation of Duties):
 * - Pemohon dilarang keras memverifikasi pengeluarannya sendiri.
 * - Setiap struk dinilai keabsahan formal dan materialnya:
 *   1. VALID: Transaksi diakui sah memotong saldo kas kantor.
 *   2. NEEDS_REVISION: Bukti buram/kurang jelas, karyawan wajib merevisi/unggah ulang bukti baru.
 *   3. REJECTED: Pengeluaran tidak sesuai ketentuan; nominal ditolak dan menjadi tanggung jawab pribadi karyawan.
 *
 * @package App\Http\Controllers
 */
class VerificationController extends Controller
{
    /**
     * Memproses verifikasi status keabsahan struk/bukti transaksi pengeluaran.
     *
     * Alur kerja:
     * 1. Memeriksa otorisasi Pemisahan Tugas via helper `$user->canVerifyAndCloseCA($cashAdvance)`.
     * 2. Memvalidasi status masukan (VALID, NEEDS_REVISION, atau REJECTED).
     * 3. Mewajibkan catatan evaluasi (verification_notes) jika status adalah perbaikan atau penolakan.
     * 4. Memperbarui status verifikasi, petugas pemverifikasi, dan waktu verifikasi pada model Expense.
     * 5. Mencatat hasil verifikasi ke tabel AuditLog untuk akuntabilitas fiskal.
     * 6. Mengirimkan notifikasi langsung ke karyawan pemilik transaksi mengenai hasil penilaian.
     * 7. Mengembalikan respon ke halaman sebelumnya dengan pesan sukses.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat keputusan verifikasi dan catatan.
     * @param  \App\Models\Expense  $expense Model transaksi pengeluaran yang diverifikasi.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika pengguna tidak berhak memverifikasi.
     */
    public function verifyExpense(Request $request, Expense $expense): RedirectResponse
    {
        $user = Auth::user();
        $cashAdvance = $expense->cashAdvance;

        // 1. Pemeriksaan Hak Akses & Prinsip Pemisahan Tugas (Separation of Duties)
        // Pemohon CA tidak diperkenankan memverifikasi pengeluarannya sendiri
        if (!$user->canVerifyAndCloseCA($cashAdvance)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak untuk memverifikasi struk pada Cash Advance ini.');
        }

        // 2. Validasi input keputusan verifikasi
        $validated = $request->validate([
            'status' => ['required', 'in:VALID,NEEDS_REVISION,REJECTED'],
            'verification_notes' => ['nullable', 'string'],
        ]);

        $status = $validated['status'];
        $notes = $validated['verification_notes'];

        // 3. Kebijakan Bisnis: Status Perbaikan (NEEDS_REVISION) atau Penolakan (REJECTED) wajib menyertakan alasan
        if (($status === 'NEEDS_REVISION' || $status === 'REJECTED') && empty($notes)) {
            return back()->with('error', 'Catatan verifikasi wajib diisi untuk status Perlu Perbaikan atau Ditolak.');
        }

        $oldStatus = $expense->verification_status;

        // 4. Perbarui status verifikasi transaksi pada basis data
        $expense->update([
            'verification_status' => $status,
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);

        // 5. Tentukan deskripsi narasi audit log berdasarkan hasil keputusan
        $actionDesc = match ($status) {
            'VALID' => 'diverifikasi VALID',
            'NEEDS_REVISION' => 'ditandai PERLU PERBAIKAN',
            'REJECTED' => 'DITOLAK (saldo dipulihkan dan menjadi kewajiban karyawan)',
        };

        // 6. Rekam peristiwa verifikasi ke dalam Audit Trail
        AuditLog::record(
            'EXPENSE_VERIFIED',
            $expense,
            "Struk transaksi #{$expense->id} ({$expense->description}) {$actionDesc} oleh {$user->name}. Catatan: {$notes}"
        );

        // 7. Siapkan parameter notifikasi kepada pemegang CA
        $notifType = match ($status) {
            'VALID' => 'success',
            'NEEDS_REVISION' => 'warning',
            'REJECTED' => 'danger',
        };

        $notifTitle = match ($status) {
            'VALID' => 'Struk Pengeluaran Disetujui (Valid)',
            'NEEDS_REVISION' => 'Struk Perlu Perbaikan!',
            'REJECTED' => 'Struk Pengeluaran Ditolak',
        };

        // 8. Kirim notifikasi sistem secara otomatis
        SystemNotification::send(
            $expense->user_id,
            $notifTitle,
            "Transaksi '{$expense->description}' ({$cashAdvance->ca_number}) {$actionDesc}. " . ($notes ? "Catatan: {$notes}" : ''),
            $notifType,
            route('cash-advances.show', $cashAdvance)
        );

        // 9. Berikan respon balik ke antarmuka pengguna
        return back()->with('success', "Status struk pengeluaran berhasil diperbarui menjadi {$status}.");
    }
}
