<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\TopUp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class TopUpController
 *
 * Mengelola siklus pengajuan, persetujuan bertingkat (multi-tier approval),
 * hingga pencairan penambahan saldo (Top-Up) pada Cash Advance aktif di PT Technodrive.
 *
 * Alur Bisnis Top-Up:
 * 1. Pengajuan oleh Karyawan ketika saldo kas di lapangan menipis sebelum proyek selesai.
 * 2. Evaluasi Ambang Batas:
 *    - Bila nominal <= batas nominal (Rp 5.000.000), persentase <= 50% nilai awal CA,
 *      dan frekuensi pengajuan belum melampaui batas (2x), maka alur persetujuan:
 *      Atasan Langsung -> Tim Finance -> Pencairan.
 *    - Bila melampaui salah satu ambang batas di atas, maka sistem secara otomatis menandai
 *      bahwa pengajuan memerlukan Persetujuan Direktur (Tahap 3) sebelum dapat dicairkan.
 * 3. Pencairan dana oleh Finance yang otomatis menambahkan saldo aktif CA berjalan.
 *
 * @package App\Http\Controllers
 */
class TopUpController extends Controller
{
    /**
     * Mengajukan penambahan saldo (Top-Up) untuk Cash Advance yang sedang aktif.
     *
     * Alur kerja:
     * 1. Memeriksa kepemilikan CA (hanya pemegang CA yang dapat meminta Top-Up).
     * 2. Memastikan status CA adalah ACTIVE.
     * 3. Memvalidasi nominal pengajuan (minimal Rp 50.000) dan alasan kebutuhan dana.
     * 4. Memeriksa kriteria eskalasi ke Direktur (nominal > ambang, % > ambang, atau jumlah pengajuan >= limit).
     * 5. Menyimpan pengajuan Top-Up dengan status awal PENDING_ATASAN.
     * 6. Mencatat ke Audit Log dan menerbitkan notifikasi ke Atasan langsung pemohon.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat nominal dan alasan top-up.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance terkait.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi: Hanya pemegang CA yang berhak mengajukan penambahan saldo
        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Akses ditolak: Hanya pemegang Cash Advance yang dapat mengajukan Top-Up.');
        }

        // 2. Validasi Status CA: Hanya CA yang sedang ACTIVE yang dapat ditambah saldonya
        if ($cashAdvance->status !== 'ACTIVE') {
            return back()->with('error', 'Top-Up hanya dapat diajukan untuk Cash Advance yang berstatus ACTIVE.');
        }

        // 3. Validasi input nominal dan alasan kebutuhan
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:50000'],
            'reason' => ['required', 'string', 'min:10'],
        ], [
            'amount.required' => 'Nominal pengajuan Top-Up wajib diisi.',
            'amount.min' => 'Nominal pengajuan Top-Up minimal Rp 50.000.',
            'reason.required' => 'Alasan kebutuhan Top-Up wajib diisi.',
            'reason.min' => 'Alasan kebutuhan Top-Up minimal 10 karakter.',
        ]);

        $amount = (float) $validated['amount'];
        $initialCA = (float) ($cashAdvance->approved_amount ?: $cashAdvance->requested_amount);

        // 4. Evaluasi Kebijakan Bisnis: Apakah memerlukan eskalasi Persetujuan Direktur?
        // Parameter diambil dari tabel SystemSetting (dapat dikonfigurasi Super Admin)
        $nominalLimit = (float) SystemSetting::get('topup_nominal_threshold', 5000000);
        $percentLimit = (float) SystemSetting::get('topup_percent_threshold', 50);
        $maxTimesLimit = (int) SystemSetting::get('topup_max_times_before_direktur', 2);

        $existingTopupCount = $cashAdvance->topUps()->count();
        $isOverNominal = ($amount > $nominalLimit);
        $isOverPercent = ($initialCA > 0 && ($amount / $initialCA * 100) > $percentLimit);
        $isOverCount = ($existingTopupCount >= $maxTimesLimit);

        // Jika salah satu batas terlampaui, wajibkan persetujuan Direktur
        $requiresDirektur = ($isOverNominal || $isOverPercent || $isOverCount);

        // 5. Simpan record Top-Up ke basis data
        $topup = TopUp::create([
            'cash_advance_id' => $cashAdvance->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'reason' => $validated['reason'],
            'status' => 'PENDING_ATASAN',
            'requires_direktur_approval' => $requiresDirektur,
        ]);

        // 6. Catat riwayat pengajuan ke Audit Log
        AuditLog::record(
            'TOPUP_REQUESTED',
            $topup,
            "Pengajuan Top-Up Rp " . number_format($amount, 0, ',', '.') . " untuk {$cashAdvance->ca_number} diajukan oleh {$user->name}." . ($requiresDirektur ? ' (Memerlukan persetujuan Direktur)' : '')
        );

        // 7. Terbitkan notifikasi ke Atasan langsung pemohon
        if ($user->manager_id) {
            SystemNotification::send(
                $user->manager_id,
                'Pengajuan Top-Up Dana CA',
                "Bawahan Anda, {$user->name}, mengajukan Top-Up sebesar Rp " . number_format($amount, 0, ',', '.') . " untuk {$cashAdvance->ca_number}.",
                'info',
                route('cash-advances.show', $cashAdvance)
            );
        }

        // 8. Kembalikan ke halaman detail CA dengan pesan konfirmasi
        return back()->with('success', "Pengajuan Top-Up sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil diajukan dan sedang menunggu persetujuan Atasan.");
    }

    /**
     * Menyetujui pengajuan Top-Up pada Persetujuan Tahap 1 (Atasan Langsung).
     *
     * Alur kerja:
     * 1. Menegakkan pemisahan tugas: Pemohon tidak dapat menyetujui pengajuannya sendiri.
     * 2. Memastikan user memiliki peran Atasan atau Direktur.
     * 3. Memastikan status pengajuan saat ini adalah PENDING_ATASAN.
     * 4. Memperbarui status ke PENDING_FINANCE dan mencatat timestamp persetujuan atasan.
     * 5. Mencatat ke Audit Log dan meneruskan notifikasi ke tim Finance.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request.
     * @param  \App\Models\TopUp  $topUp Model TopUp yang akan disetujui.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveAtasan(Request $request, TopUp $topUp): RedirectResponse
    {
        $user = Auth::user();

        // 1. Pemisahan Tugas (Separation of Duties): Dilarang approve pengajuan sendiri
        if ($topUp->user_id === $user->id) {
            abort(403, 'Akses ditolak: Anda tidak dapat menyetujui pengajuan Top-Up milik sendiri.');
        }

        // 2. Otorisasi peran
        if (!$user->isAtasan() && !$user->isDirektur()) {
            abort(403, 'Akses ditolak: Hanya Atasan atau Direktur yang dapat menyetujui tahap ini.');
        }

        // 3. Validasi status transisi
        if ($topUp->status !== 'PENDING_ATASAN') {
            return back()->with('error', 'Status Top-Up saat ini bukan PENDING_ATASAN.');
        }

        // 4. Perbarui status ke tahap berikutnya (Tahap 2: Finance)
        $topUp->update([
            'approved_by_atasan' => $user->id,
            'atasan_approved_at' => now(),
            'status' => 'PENDING_FINANCE',
        ]);

        // 5. Catat persetujuan tahap 1 ke Audit Log
        AuditLog::record(
            'TOPUP_APPROVED_ATASAN',
            $topUp,
            "Top-Up #{$topUp->id} disetujui Atasan ({$user->name}). Status beralih ke PENDING_FINANCE."
        );

        // 6. Notifikasi kepada seluruh tim Finance untuk peninjauan anggaran/ketersediaan kas
        $finances = User::where('role', 'finance')->get();
        foreach ($finances as $fin) {
            SystemNotification::send(
                $fin->id,
                'Persetujuan Top-Up Tahap 2 (Finance)',
                "Top-Up {$topUp->cashAdvance->ca_number} Rp " . number_format($topUp->amount, 0, ',', '.') . " telah disetujui Atasan dan menunggu approval Finance.",
                'info',
                route('cash-advances.show', $topUp->cash_advance_id)
            );
        }

        return back()->with('success', 'Top-Up tahap 1 (Atasan) berhasil disetujui.');
    }

    /**
     * Menyetujui pengajuan Top-Up pada Persetujuan Tahap 2 (Tim Finance).
     *
     * Alur kerja:
     * 1. Menegakkan pemisahan tugas.
     * 2. Memastikan user memiliki peran Finance atau Direktur.
     * 3. Memastikan status pengajuan adalah PENDING_FINANCE.
     * 4. Menentukan rute status berikutnya:
     *    - Jika `requires_direktur_approval` bernilai true, status beralih ke PENDING_DIREKTUR.
     *    - Jika false, status beralih ke APPROVED (disetujui penuh & siap dicairkan).
     * 5. Mencatat ke Audit Log dan mengirim notifikasi sesuai eskalasi.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request.
     * @param  \App\Models\TopUp  $topUp Model TopUp yang akan diverifikasi Finance.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveFinance(Request $request, TopUp $topUp): RedirectResponse
    {
        $user = Auth::user();

        // 1. Pemisahan tugas
        if ($topUp->user_id === $user->id) {
            abort(403, 'Akses ditolak: Anda tidak dapat menyetujui pengajuan milik sendiri.');
        }

        // 2. Otorisasi peran Finance / Direktur
        if (!$user->isFinance() && !$user->isDirektur()) {
            abort(403, 'Akses ditolak: Hanya tim Finance atau Direktur yang dapat menyetujui tahap ini.');
        }

        // 3. Validasi status transisi
        if ($topUp->status !== 'PENDING_FINANCE') {
            return back()->with('error', 'Status Top-Up saat ini bukan PENDING_FINANCE.');
        }

        // 4. Tentukan apakah perlu eskalasi ke Direktur atau langsung APPROVED
        $nextStatus = $topUp->requires_direktur_approval ? 'PENDING_DIREKTUR' : 'APPROVED';

        $topUp->update([
            'approved_by_finance' => $user->id,
            'finance_approved_at' => now(),
            'status' => $nextStatus,
        ]);

        // 5. Catat ke Audit Log
        AuditLog::record(
            'TOPUP_APPROVED_FINANCE',
            $topUp,
            "Top-Up #{$topUp->id} disetujui Finance ({$user->name}). Status beralih ke {$nextStatus}."
        );

        // 6. Jika perlu eskalasi ke Direktur, kirimkan notifikasi khusus ke Direktur
        if ($nextStatus === 'PENDING_DIREKTUR') {
            $direkturs = User::where('role', 'direktur')->get();
            foreach ($direkturs as $dir) {
                SystemNotification::send(
                    $dir->id,
                    'Persetujuan Top-Up Khusus (Direktur)',
                    "Top-Up {$topUp->cashAdvance->ca_number} melebihi batas (Rp " . number_format($topUp->amount, 0, ',', '.') . ") dan membutuhkan persetujuan Direktur.",
                    'warning',
                    route('cash-advances.show', $topUp->cash_advance_id)
                );
            }
        }

        return back()->with('success', $nextStatus === 'APPROVED' 
            ? 'Top-Up telah disetujui penuh dan siap dicairkan oleh Finance.' 
            : 'Top-Up disetujui Finance dan diteruskan ke Direktur (melebihi batas ambang kebijakan).');
    }

    /**
     * Menyetujui pengajuan Top-Up bernominal besar / melampaui ambang batas oleh Direktur.
     *
     * Alur kerja:
     * 1. Memastikan otorisasi peran eksklusif Direktur.
     * 2. Menegakkan pemisahan tugas.
     * 3. Memperbarui status Top-Up menjadi APPROVED.
     * 4. Mencatat persetujuan eksekutif ke Audit Log.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request.
     * @param  \App\Models\TopUp  $topUp Model TopUp yang membutuhkan persetujuan Direktur.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveDirektur(Request $request, TopUp $topUp): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi peran Direktur
        if (!$user->isDirektur()) {
            abort(403, 'Akses ditolak: Hanya Direktur yang berwenang memberikan persetujuan tahap ini.');
        }

        // 2. Pemisahan tugas
        if ($topUp->user_id === $user->id) {
            abort(403, 'Akses ditolak: Anda tidak dapat menyetujui pengajuan milik sendiri.');
        }

        // 3. Ubah status menjadi APPROVED (disetujui penuh)
        $topUp->update([
            'approved_by_direktur' => $user->id,
            'direktur_approved_at' => now(),
            'status' => 'APPROVED',
        ]);

        // 4. Catat persetujuan Direktur ke Audit Log
        AuditLog::record(
            'TOPUP_APPROVED_DIREKTUR',
            $topUp,
            "Top-Up #{$topUp->id} disetujui Direktur ({$user->name}). Status kini APPROVED."
        );

        return back()->with('success', 'Top-Up telah disetujui Direktur dan siap dicairkan oleh Finance.');
    }

    /**
     * Menolak pengajuan Top-Up dengan menyertakan alasan penolakan.
     *
     * Alur kerja:
     * 1. Memeriksa otorisasi (pemohon tidak boleh menolak miliknya sendiri).
     * 2. Memvalidasi kewajiban pengisian alasan penolakan (rejection_reason).
     * 3. Memperbarui status menjadi REJECTED.
     * 4. Merekam ke Audit Log dan menerbitkan notifikasi penolakan kepada pemohon.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat alasan penolakan.
     * @param  \App\Models\TopUp  $topUp Model TopUp yang ditolak.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, TopUp $topUp): RedirectResponse
    {
        $user = Auth::user();

        // 1. Pemisahan tugas
        if ($topUp->user_id === $user->id) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Validasi alasan penolakan
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan pengajuan Top-Up wajib diisi.',
        ]);

        // 3. Perbarui status menjadi REJECTED
        $topUp->update([
            'status' => 'REJECTED',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // 4. Catat penolakan ke Audit Log
        AuditLog::record(
            'TOPUP_REJECTED',
            $topUp,
            "Top-Up #{$topUp->id} ditolak oleh {$user->name}. Alasan: {$validated['rejection_reason']}"
        );

        // 5. Kirim notifikasi bahaya/peringatan ke pemohon
        SystemNotification::send(
            $topUp->user_id,
            'Top-Up Ditolak',
            "Pengajuan Top-Up Rp " . number_format($topUp->amount, 0, ',', '.') . " ditolak. Alasan: {$validated['rejection_reason']}",
            'danger',
            route('cash-advances.show', $topUp->cash_advance_id)
        );

        return back()->with('info', 'Pengajuan Top-Up telah ditolak.');
    }

    /**
     * Mencatat pencairan dana Top-Up ke rekening kas pemohon.
     *
     * Alur kerja:
     * 1. Memastikan otorisasi peran (hanya Finance atau Direktur).
     * 2. Memastikan status pengajuan sudah berstatus APPROVED.
     * 3. Memvalidasi nomor referensi bukti transfer/voucher bank (disbursement_ref).
     * 4. Memperbarui status Top-Up menjadi DISBURSED.
     * 5. Secara otomatis, kalkulasi saldo aktif pada model CashAdvance akan bertambah.
     * 6. Merekam pencairan ke Audit Log dan mengirimkan notifikasi ke pemohon bahwa saldo bertambah.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat referensi dan catatan transfer.
     * @param  \App\Models\TopUp  $topUp Model TopUp yang akan dicairkan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disburse(Request $request, TopUp $topUp): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi peran perbendaharaan (Finance atau Direktur)
        if (!$user->isFinance() && !$user->isDirektur()) {
            abort(403, 'Akses ditolak: Hanya tim Finance atau Direktur yang dapat mencatat pencairan Top-Up.');
        }

        // 2. Validasi prasyarat: Status wajib sudah APPROVED
        if ($topUp->status !== 'APPROVED') {
            return back()->with('error', 'Top-Up belum disetujui sepenuhnya sehingga belum dapat dicairkan.');
        }

        // 3. Validasi nomor referensi transfer bank
        $validated = $request->validate([
            'disbursement_ref' => ['required', 'string', 'max:100'],
            'disbursement_notes' => ['nullable', 'string'],
        ], [
            'disbursement_ref.required' => 'Nomor referensi transfer pencairan top-up wajib diisi.',
        ]);

        // 4. Simpan status pencairan dana
        $topUp->update([
            'status' => 'DISBURSED',
            'disbursed_by' => $user->id,
            'disbursed_at' => now(),
            'disbursement_ref' => $validated['disbursement_ref'],
            'disbursement_notes' => $validated['disbursement_notes'] ?? null,
        ]);

        // 5. Catat aktivitas pencairan dana ke Audit Log
        AuditLog::record(
            'TOPUP_DISBURSED',
            $topUp,
            "Dana Top-Up Rp " . number_format($topUp->amount, 0, ',', '.') . " dicatat cair (Ref: {$validated['disbursement_ref']}). Saldo CA bertambah otomatis."
        );

        // 6. Terbitkan notifikasi ke pemohon bahwa dana telah masuk dan siap dipakai
        SystemNotification::send(
            $topUp->user_id,
            'Dana Top-Up Telah Cair',
            "Top-Up Rp " . number_format($topUp->amount, 0, ',', '.') . " telah cair dan ditambahkan ke saldo aktif CA {$topUp->cashAdvance->ca_number}.",
            'success',
            route('cash-advances.show', $topUp->cash_advance_id)
        );

        return back()->with('success', "Pencairan Top-Up berhasil dicatat. Saldo CA telah bertambah sebesar Rp " . number_format($topUp->amount, 0, ',', '.') . ".");
    }
}
