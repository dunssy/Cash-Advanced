<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Class CashAdvanceController
 *
 * Mengelola seluruh siklus hidup (lifecycle) Cash Advance (Uang Muka Kerja)
 * dan proses Settlement (Pertanggungjawaban Akhir) pada sistem PT Technodrive.
 *
 * Siklus Hidup Standar Cash Advance:
 * 1. Pengajuan (SUBMISSION): Karyawan mengajukan estimasi biaya proyek dengan nomor otomatis CA-YYYYMM-XXXX.
 * 2. Kebijakan Gembok (LOCK SYSTEM): Pengajuan baru diblokir bila pemohon masih memiliki CA aktif/belum CLOSED.
 * 3. Persetujuan (APPROVAL): Disetujui oleh Atasan langsung atau Direktur (jika pemohon adalah Atasan/Finance).
 * 4. Pencairan Dana (DISBURSEMENT): Dicatat oleh Finance, mengubah status menjadi ACTIVE, dan mengaktifkan SLA durasi maksimal (default 60 hari).
 * 5. Penyelesaian Proyek (PROJECT COMPLETION):
 *    - Jika sisa saldo kas > Rp0 -> Status beralih ke 'NEED_SETTLEMENT' (wajib transfer refund sisa saldo dalam 3 hari kerja).
 *    - Jika saldo kas = Rp0 -> Status beralih ke 'PENDING_VERIFICATION' (menanti verifikasi akhir Finance).
 * 6. Penutupan Resmi (CLOSED): Dilakukan oleh Finance/Direktur setelah seluruh struk terverifikasi VALID dan sisa saldo lunas.
 * 7. Pengecualian Gembok (OVERRIDE): Direktur memiliki diskresi khusus membuka gembok pengajuan baru dengan alasan tercatat.
 *
 * @package App\Http\Controllers
 */
class CashAdvanceController extends Controller
{
    /**
     * Menampilkan daftar Cash Advance dengan pembatasan hak akses berbasis peran (Role-Based Visibility).
     *
     * Cakupan visibilitas data (Dokumen SOP hal 2-3 & hal 8):
     * - Karyawan: Hanya dapat melihat pengajuan miliknya sendiri.
     * - Atasan: Melihat pengajuan miliknya dan seluruh bawahan langsungnya.
     * - Finance, Direktur, Super Admin: Memiliki visibilitas penuh atas seluruh CA di perusahaan.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat parameter filter dan pencarian.
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // 1. Inisialisasi kueri dasar dengan eager loading relasi pendukung
        $query = CashAdvance::with(['user', 'approver', 'disburser', 'expenses', 'topUps'])
            ->orderByDesc('created_at');

        // 2. Pembatasan cakupan data sesuai peran pengguna (Data Scoping)
        if ($user->isKaryawan()) {
            // Karyawan hanya melihat data miliknya
            $query->where('user_id', $user->id);
        } elseif ($user->isAtasan()) {
            // Atasan melihat data pribadi dan data bawahan langsungnya
            $subordinateIds = $user->subordinates()->pluck('id')->toArray();
            $query->where(function ($q) use ($user, $subordinateIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('user_id', $subordinateIds);
            });
        }
        // Finance, Direktur, dan Super Admin dapat memantau seluruh CA perusahaan tanpa filter pemilik

        // 3. Filter berdasarkan status Cash Advance
        if ($request->filled('status')) {
            if ($request->status === 'OVERDUE') {
                // Kategori OVERDUE: Belum CLOSED/REJECTED, memiliki tanggal due_date, dan telah melewati waktu sekarang
                $query->where('status', '!=', 'CLOSED')
                      ->where('status', '!=', 'REJECTED')
                      ->whereNotNull('due_date')
                      ->where('due_date', '<', Carbon::now());
            } else {
                $query->where('status', $request->status);
            }
        }

        // 4. Filter pencarian teks bebas (Nomor CA, Nama Proyek, Kode Proyek, Nama Pemohon)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ca_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 5. Terapkan paginasi 5 data per halaman dengan mempertahankan query string URL
        $cashAdvances = $query->paginate(5)->withQueryString();

        // 6. Tampilkan ke antarmuka indeks Cash Advance
        return view('cash_advances.index', compact('cashAdvances'));
    }

    /**
     * Menampilkan formulir pengajuan Cash Advance baru.
     *
     * Menerapkan aturan integritas sistem:
     * 1. Super Admin dilarang mengajukan CA (pemisahan peran sistem administrasi vs operasional kas).
     * 2. Kebijakan Gembok Pengajuan: Jika user masih memiliki CA aktif/belum CLOSED, formulir digembok
     *    dan dialihkan ke halaman peringatan gembok (locked view).
     *
     * @return \Illuminate\View\View
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika Super Admin mencoba mengakses.
     */
    public function create(): View
    {
        $user = Auth::user();

        // 1. Super Admin bertindak sebagai administrator sistem dan tidak terlibat transaksi kas operasional
        if ($user->isSuperAdmin()) {
            abort(403, 'Super Admin tidak memiliki wewenang mengajukan Cash Advance.');
        }

        // 2. Evaluasi Gembok Pengajuan (Dokumen SOP hal 2 & hal 7)
        // Satu karyawan hanya berhak memegang 1 CA aktif pada satu waktu hingga statusnya resmi CLOSED
        if ($user->hasActiveLock()) {
            $lockingCA = $user->getActiveLockingCA();
            return view('cash_advances.locked', compact('lockingCA'));
        }

        // 3. Render antarmuka pembuatan pengajuan CA baru
        return view('cash_advances.create');
    }

    /**
     * Menyimpan berkas pengajuan Cash Advance baru ke dalam basis data.
     *
     * Alur kerja:
     * 1. Validasi server-side status gembok pengajuan pemohon.
     * 2. Validasi kelengkapan data (proyek, tujuan, dan nominal minimal Rp 10.000).
     * 3. Pembuatan nomor urut unik sistem: CA-YYYYMM-XXXX.
     * 4. Penyimpanan data CA dengan status awal 'PENDING_APPROVAL'.
     * 5. Pencatatan ke Audit Log.
     * 6. Penerbitan notifikasi otomatis kepada pihak penyetuju (Atasan langsung atau Direktur).
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat formulir pengajuan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // 1. Proteksi peran
        if ($user->isSuperAdmin()) {
            abort(403, 'Super Admin tidak dapat mengajukan Cash Advance.');
        }

        // 2. Pengecekan ulang Gembok pada sisi server (Server-Side Enforcement)
        if ($user->hasActiveLock()) {
            return back()->with('error', 'Pengajuan ditolak sistem: Anda masih memiliki Cash Advance aktif yang belum berstatus CLOSED.');
        }

        // 3. Validasi formulir pengajuan
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'project_code' => ['nullable', 'string', 'max:50'],
            'purpose' => ['required', 'string'],
            'requested_amount' => ['required', 'numeric', 'min:10000'],
        ], [
            'project_name.required' => 'Nama proyek wajib diisi.',
            'purpose.required' => 'Tujuan pengajuan wajib diisi.',
            'requested_amount.min' => 'Nominal pengajuan minimal Rp 10.000.',
        ]);

        // 4. Penomoran Dokumen Otomatis: CA-YYYYMM-XXXX
        $countThisMonth = CashAdvance::whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->count() + 1;
        $caNumber = sprintf('CA-%s%s-%04d', date('Y'), date('m'), $countThisMonth);

        // 5. Simpan record Cash Advance baru
        $ca = CashAdvance::create([
            'ca_number' => $caNumber,
            'user_id' => $user->id,
            'project_name' => $validated['project_name'],
            'project_code' => $validated['project_code'],
            'purpose' => $validated['purpose'],
            'requested_amount' => $validated['requested_amount'],
            'status' => 'PENDING_APPROVAL',
        ]);

        // 6. Rekam peristiwa pembuatan CA ke Audit Log
        AuditLog::record(
            'CA_CREATED',
            $ca,
            "Pengajuan CA {$ca->ca_number} dibuat dengan nominal Rp " . number_format($ca->requested_amount, 0, ',', '.'),
            null,
            $ca->toArray()
        );

        // 7. Pengiriman notifikasi persetujuan:
        // Jika pemohon adalah Atasan atau tim Finance -> Eskalasi persetujuan ke Direktur
        // Jika pemohon adalah Karyawan -> Notifikasi ke Atasan langsung
        if ($user->isAtasan() || $user->isFinance()) {
            $direkturs = User::where('role', 'direktur')->get();
            foreach ($direkturs as $dir) {
                SystemNotification::send(
                    $dir->id,
                    'Persetujuan Khusus Pengajuan CA',
                    "Pengajuan {$ca->ca_number} ({$ca->project_name}) oleh {$user->name} ({$user->role}) memerlukan persetujuan Direktur.",
                    'warning',
                    route('cash-advances.show', $ca)
                );
            }
        } elseif ($user->manager_id) {
            SystemNotification::send(
                $user->manager_id,
                'Pengajuan CA Baru Masuk',
                "Bawahan Anda, {$user->name}, mengajukan {$ca->ca_number} untuk proyek {$ca->project_name}.",
                'info',
                route('cash-advances.show', $ca)
            );
        }

        // 8. Alihkan pengguna ke halaman detail CA dengan pesan konfirmasi
        return redirect()->route('cash-advances.show', $ca)
            ->with('success', "Cash Advance {$ca->ca_number} berhasil diajukan dan sedang menunggu persetujuan.");
    }

    /**
     * Menampilkan rincian detail Cash Advance, riwayat transaksi, top-up, dan settlement.
     *
     * Melakukan otorisasi pembacaan data sebelum memuat seluruh relasi terkait secara eager:
     * - Data atasan pemohon, penyetuju, pencair dana, dan penutup CA.
     * - Seluruh riwayat Top-Up dan persetujuannya.
     * - Seluruh rincian transaksi pengeluaran beserta berkas struk fisik dan jejak audit perubahannya.
     *
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance yang diminta.
     * @return \Illuminate\View\View
     */
    public function show(CashAdvance $cashAdvance): View
    {
        // 1. Verifikasi hak akses membaca dokumen
        $this->authorizeView($cashAdvance);

        // 2. Eager loading seluruh relasi data terkait
        $cashAdvance->load([
            'user.manager',
            'approver',
            'disburser',
            'closer',
            'unlocker',
            'topUps.approverAtasan',
            'topUps.approverFinance',
            'topUps.approverDirektur',
            'topUps.disburser',
            'expenses.attachments',
            'expenses.verifier',
            'expenses.voider',
            'expenses.histories.user',
        ]);

        // 3. Tampilkan halaman detail Cash Advance
        return view('cash_advances.show', compact('cashAdvance'));
    }

    /**
     * Menyetujui pengajuan Cash Advance oleh Atasan langsung atau Direktur.
     *
     * Alur kerja:
     * 1. Menegakkan pemisahan tugas (Separation of Duties): Pemohon dilarang menyetujui pengajuannya sendiri.
     * 2. Memastikan status dokumen saat ini adalah 'PENDING_APPROVAL'.
     * 3. Memvalidasi nominal yang disetujui (dapat disetujui penuh atau disesuaikan).
     * 4. Memperbarui nominal disetujui, identitas penyetuju, dan waktu persetujuan.
     * 5. Mencatat ke Audit Log.
     * 6. Mengirimkan notifikasi ke pemohon serta seluruh tim Finance bahwa dana siap dicairkan.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat nominal dan catatan persetujuan.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance yang disetujui.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Penegakan Pemisahan Tugas (Separation of Duties)
        if (!$user->canApproveCA($cashAdvance)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak untuk menyetujui Cash Advance ini.');
        }

        // 2. Validasi status dokumen
        if ($cashAdvance->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Status CA saat ini bukan PENDING_APPROVAL.');
        }

        // 3. Validasi nominal yang disetujui
        $validated = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:1000'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        $old = $cashAdvance->toArray();

        // 4. Perbarui data persetujuan CA
        $cashAdvance->update([
            'approved_amount' => $validated['approved_amount'],
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'],
        ]);

        // 5. Catat peristiwa persetujuan ke Audit Log
        AuditLog::record(
            'CA_APPROVED',
            $cashAdvance,
            "CA {$cashAdvance->ca_number} disetujui sebesar Rp " . number_format($cashAdvance->approved_amount, 0, ',', '.') . " oleh {$user->name}.",
            $old,
            $cashAdvance->toArray()
        );

        // 6. Kirim notifikasi konfirmasi ke pemohon
        SystemNotification::send(
            $cashAdvance->user_id,
            'Pengajuan CA Disetujui',
            "Pengajuan {$cashAdvance->ca_number} telah disetujui sebesar Rp " . number_format($cashAdvance->approved_amount, 0, ',', '.') . ". Menunggu pencairan oleh Finance.",
            'success',
            route('cash-advances.show', $cashAdvance)
        );

        // 7. Kirim notifikasi ke tim Finance untuk proses pencairan dana
        $finances = User::where('role', 'finance')->get();
        foreach ($finances as $fin) {
            SystemNotification::send(
                $fin->id,
                'CA Siap Dicairkan',
                "CA {$cashAdvance->ca_number} ({$cashAdvance->user->name}) telah disetujui dan siap dicairkan.",
                'info',
                route('cash-advances.show', $cashAdvance)
            );
        }

        return back()->with('success', "Cash Advance {$cashAdvance->ca_number} berhasil disetujui.");
    }

    /**
     * Menolak pengajuan Cash Advance disertai alasan penolakan.
     *
     * Alur kerja:
     * 1. Menegakkan hak penolakan oleh Atasan/Direktur berwenang.
     * 2. Memvalidasi kewajiban penyertaan alasan penolakan (rejection_reason).
     * 3. Mengubah status CA menjadi 'REJECTED'.
     * 4. Secara otomatis membuka gembok pengajuan pemohon karena CA berakhir ditolak.
     * 5. Mencatat ke Audit Log dan menerbitkan notifikasi penolakan ke pemohon.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat alasan penolakan.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance yang ditolak.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi penolakan
        if (!$user->canApproveCA($cashAdvance)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak menolak Cash Advance ini.');
        }

        // 2. Validasi input alasan penolakan
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $old = $cashAdvance->toArray();

        // 3. Ubah status menjadi REJECTED
        $cashAdvance->update([
            'status' => 'REJECTED',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // 4. Catat peristiwa penolakan ke Audit Log
        AuditLog::record(
            'CA_REJECTED',
            $cashAdvance,
            "CA {$cashAdvance->ca_number} ditolak oleh {$user->name}. Alasan: {$validated['rejection_reason']}",
            $old,
            $cashAdvance->toArray()
        );

        // 5. Kirim notifikasi penolakan ke pemohon
        SystemNotification::send(
            $cashAdvance->user_id,
            'Pengajuan CA Ditolak',
            "Pengajuan {$cashAdvance->ca_number} ditolak. Alasan: {$validated['rejection_reason']}",
            'danger',
            route('cash-advances.show', $cashAdvance)
        );

        return back()->with('info', "Cash Advance {$cashAdvance->ca_number} telah ditolak. Gembok pengajuan pemohon dibuka.");
    }

    /**
     * Mencatat pencairan dana operasional (Disbursement) ke pemegang Cash Advance oleh tim Finance.
     *
     * Alur kerja:
     * 1. Memeriksa otorisasi perbendaharaan (Finance atau Direktur).
     * 2. Memastikan CA telah disetujui sebelumnya.
     * 3. Memvalidasi nomor referensi bukti transfer bank / voucher kas.
     * 4. Menghitung tanggal jatuh tempo (due_date) berdasarkan durasi maksimal (default 60 hari dari SystemSetting).
     * 5. Mengubah status CA menjadi 'ACTIVE' sehingga karyawan dapat mulai mencatat transaksi pengeluaran.
     * 6. Mencatat ke Audit Log dan memberitahukan pemohon bahwa hitung mundur durasi proyek telah dimulai.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat referensi transfer dan tanggal cair.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance yang dicairkan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disburse(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi pencairan dana kas
        if (!$user->canDisburseCA($cashAdvance)) {
            abort(403, 'Akses ditolak: Hanya tim Finance atau Direktur yang berhak mencatat pencairan dana.');
        }

        // 2. Validasi prasyarat: CA wajib berstatus PENDING_APPROVAL dan sudah ada approval resmi
        if ($cashAdvance->status !== 'PENDING_APPROVAL' || !$cashAdvance->approved_by) {
            return back()->with('error', 'CA harus sudah disetujui sebelum dapat dicairkan.');
        }

        // 3. Validasi input bukti transfer
        $validated = $request->validate([
            'disbursed_at' => ['required', 'date', 'before_or_equal:today'],
            'disbursement_ref' => ['required', 'string', 'max:100'],
            'disbursement_notes' => ['nullable', 'string'],
        ], [
            'disbursed_at.required' => 'Tanggal pencairan wajib diisi.',
            'disbursed_at.before_or_equal' => 'Tanggal pencairan tidak boleh di masa depan.',
            'disbursement_ref.required' => 'Nomor bukti transfer / voucher bank wajib diisi.',
        ]);

        // 4. Kalkulasi jatuh tempo SLA durasi maksimal CA (default: 60 hari kerja/kalender)
        $maxDays = (int) SystemSetting::get('max_ca_duration_days', 60);
        $disbursedAt = Carbon::parse($validated['disbursed_at']);
        $dueDate = $disbursedAt->copy()->addDays($maxDays);

        $old = $cashAdvance->toArray();

        // 5. Ubah status menjadi ACTIVE dan simpan data pencairan
        $cashAdvance->update([
            'status' => 'ACTIVE',
            'disbursed_by' => $user->id,
            'disbursed_at' => $disbursedAt,
            'disbursement_ref' => $validated['disbursement_ref'],
            'disbursement_notes' => $validated['disbursement_notes'],
            'due_date' => $dueDate,
        ]);

        // 6. Catat aktivitas pencairan ke Audit Log
        AuditLog::record(
            'CA_DISBURSED',
            $cashAdvance,
            "Pencairan dana CA {$cashAdvance->ca_number} dicatat sebesar Rp " . number_format($cashAdvance->approved_amount, 0, ',', '.') . " (Ref: {$validated['disbursement_ref']}). Durasi {$maxDays} hari berjalan.",
            $old,
            $cashAdvance->toArray()
        );

        // 7. Berikan notifikasi ke pemohon bahwa dana siap digunakan dan batas waktu berjalan
        SystemNotification::send(
            $cashAdvance->user_id,
            'Dana CA Telah Cair - Saldo Aktif',
            "Dana {$cashAdvance->ca_number} telah cair sebesar Rp " . number_format($cashAdvance->approved_amount, 0, ',', '.') . ". Hitung mundur durasi telah dimulai sampai " . $dueDate->format('d M Y') . ". Silakan catat pengeluaran secara real-time.",
            'success',
            route('cash-advances.show', $cashAdvance)
        );

        return back()->with('success', "Pencairan dana berhasil dicatat. Status CA kini ACTIVE dan saldo dapat digunakan.");
    }

    /**
     * Menandai pekerjaan proyek telah selesai dilaksanakan (Project Completion).
     *
     * Alur evaluasi saldo akhir:
     * - Jika sisa saldo kas > Rp0:
     *   Status dialihkan ke 'NEED_SETTLEMENT'. Karyawan diwajibkan menyetorkan kembali sisa dana
     *   ke rekening kantor maksimal dalam waktu 3 hari kerja.
     * - Jika sisa saldo kas = Rp0:
     *   Status langsung dialihkan ke 'PENDING_VERIFICATION' untuk menunggu verifikasi penutupan akhir oleh Finance.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance yang diselesaikan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeProject(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi kepemilikan: Hanya pemohon yang berhak menyatakan proyeknya selesai
        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Akses ditolak: Hanya pemohon yang dapat menandai proyek selesai.');
        }

        // 2. Validasi status dokumen
        if ($cashAdvance->status !== 'ACTIVE') {
            return back()->with('error', 'Hanya CA berstatus ACTIVE yang dapat diselesaikan.');
        }

        $balance = $cashAdvance->current_balance;

        // 3. Penentuan alur transisi berdasarkan sisa saldo
        if ($balance > 0) {
            // Sisa saldo > 0: Wajib dikembalikan oleh karyawan (status NEED_SETTLEMENT)
            $cashAdvance->update([
                'status' => 'NEED_SETTLEMENT',
                'project_completed_at' => now(),
            ]);

            AuditLog::record(
                'CA_NEED_SETTLEMENT',
                $cashAdvance,
                "Proyek selesai dengan sisa saldo Rp " . number_format($balance, 0, ',', '.') . ". Status dialihkan ke NEED_SETTLEMENT."
            );

            return back()->with('warning', "Proyek ditandai selesai. Sisa saldo Rp " . number_format($balance, 0, ',', '.') . " wajib ditransfer kembali ke rekening kantor dalam 3 hari kerja.");
        } else {
            // Sisa saldo = Rp 0: Langsung melangkah ke verifikasi final Finance
            $cashAdvance->update([
                'status' => 'PENDING_VERIFICATION',
                'project_completed_at' => now(),
            ]);

            AuditLog::record(
                'CA_PENDING_VERIFICATION',
                $cashAdvance,
                "Proyek selesai dengan saldo Rp0. Menunggu verifikasi akhir Finance."
            );

            return back()->with('info', "Proyek selesai. Seluruh bukti struk kini sedang dalam proses verifikasi akhir Finance.");
        }
    }

    /**
     * Mengunggah bukti setor pengembalian sisa dana kas (Settlement Refund).
     *
     * Alur kerja:
     * 1. Memeriksa otorisasi pemohon dan status CA wajib 'NEED_SETTLEMENT'.
     * 2. Memvalidasi nominal pengembalian dan file bukti transfer bank (JPG, PNG, PDF maks 10MB).
     * 3. Menyimpan berkas bukti setor ke folder public storage 'refunds'.
     * 4. Memperbarui status CA menjadi 'PENDING_VERIFICATION'.
     * 5. Mencatat ke Audit Log dan memberi tahu tim Finance untuk memvalidasi setoran masuk.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat bukti refund.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance terkait.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitRefund(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi kepemilikan
        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Akses ditolak: Hanya pemohon yang dapat mengunggah bukti refund.');
        }

        // 2. Validasi status
        if ($cashAdvance->status !== 'NEED_SETTLEMENT') {
            return back()->with('error', 'Cash Advance tidak dalam status NEED_SETTLEMENT.');
        }

        // 3. Validasi input formulir bukti setor refund
        $validated = $request->validate([
            'settlement_refund_amount' => ['required', 'numeric', 'min:1'],
            'settlement_transferred_at' => ['required', 'date', 'before_or_equal:today'],
            'settlement_refund_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // maks 10MB
            'settlement_notes' => ['nullable', 'string'],
        ], [
            'settlement_refund_amount.required' => 'Nominal transfer refund wajib diisi.',
            'settlement_refund_proof.required' => 'Bukti struk / transfer bank wajib diunggah.',
            'settlement_refund_proof.mimes' => 'Format file bukti refund harus berupa JPG, PNG, atau PDF.',
        ]);

        // 4. Simpan berkas bukti transfer ke disk public
        $filePath = $request->file('settlement_refund_proof')->store('refunds', 'public');

        // 5. Perbarui data settlement dan alihkan status ke PENDING_VERIFICATION
        $cashAdvance->update([
            'settlement_refund_amount' => $validated['settlement_refund_amount'],
            'settlement_transferred_at' => $validated['settlement_transferred_at'],
            'settlement_refund_proof' => $filePath,
            'settlement_notes' => $validated['settlement_notes'],
            'status' => 'PENDING_VERIFICATION',
        ]);

        // 6. Catat aktivitas ke Audit Log
        AuditLog::record(
            'SETTLEMENT_REFUND_UPLOADED',
            $cashAdvance,
            "Bukti transfer refund sebesar Rp " . number_format($validated['settlement_refund_amount'], 0, ',', '.') . " diunggah oleh {$user->name}. Status berubah ke PENDING_VERIFICATION."
        );

        // 7. Notifikasi kepada tim Finance untuk rekonsiliasi mutasi rekening bank
        $finances = User::where('role', 'finance')->get();
        foreach ($finances as $fin) {
            SystemNotification::send(
                $fin->id,
                'Bukti Refund Sisa CA Masuk',
                "Karyawan {$user->name} telah mengunggah bukti refund Rp " . number_format($validated['settlement_refund_amount'], 0, ',', '.') . " untuk {$cashAdvance->ca_number}. Silakan verifikasi.",
                'info',
                route('cash-advances.show', $cashAdvance)
            );
        }

        return back()->with('success', "Bukti refund sisa dana berhasil diunggah. Menunggu verifikasi akhir Finance untuk penutupan CA.");
    }

    /**
     * Menutup dokumen Cash Advance secara resmi dan permanen (Status CLOSED).
     *
     * Prasyarat Integritas Penutupan CA (Closing Check):
     * 1. Pemohon tidak boleh menutup pengajuannya sendiri (Pemisahan Tugas).
     * 2. Seluruh struk pengeluaran wajib sudah diverifikasi (tidak boleh ada yang PENDING atau NEEDS_REVISION).
     * 3. Saat status resmi CLOSED, Gembok Pengajuan pemohon secara otomatis terbuka kembali
     *    sehingga karyawan diperkenankan mengajukan Cash Advance baru untuk proyek berikutnya.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance yang ditutup.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function close(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi penutupan CA (Finance atau Direktur)
        if (!$user->canVerifyAndCloseCA($cashAdvance)) {
            abort(403, 'Akses ditolak: Anda tidak berhak menutup Cash Advance ini.');
        }

        // 2. Validasi Integritas Struk: Pastikan tidak ada transaksi yang menggantung tanpa verifikasi
        $unverifiedCount = $cashAdvance->expenses()
            ->whereIn('verification_status', ['PENDING', 'NEEDS_REVISION'])
            ->count();

        if ($unverifiedCount > 0) {
            return back()->with('error', "Tidak dapat menutup CA: Masih terdapat {$unverifiedCount} struk pengeluaran yang belum diverifikasi atau masih berstatus PERLU PERBAIKAN.");
        }

        $old = $cashAdvance->toArray();

        // 3. Perbarui status menjadi CLOSED
        $cashAdvance->update([
            'status' => 'CLOSED',
            'closed_by' => $user->id,
            'closed_at' => now(),
        ]);

        // 4. Catat peristiwa penutupan ke Audit Log
        AuditLog::record(
            'CA_CLOSED',
            $cashAdvance,
            "Cash Advance {$cashAdvance->ca_number} resmi ditutup (CLOSED) oleh {$user->name}. Gembok pengajuan baru telah terbuka.",
            $old,
            $cashAdvance->toArray()
        );

        // 5. Berikan notifikasi resmi kepada pemohon bahwa gembok pengajuan telah dibuka kembali
        SystemNotification::send(
            $cashAdvance->user_id,
            'Cash Advance CLOSED',
            "CA {$cashAdvance->ca_number} ({$cashAdvance->project_name}) telah resmi ditutup (CLOSED). Gembok pengajuan telah dibuka dan Anda dapat mengajukan CA baru bila diperlukan.",
            'success',
            route('cash-advances.show', $cashAdvance)
        );

        return back()->with('success', "Cash Advance {$cashAdvance->ca_number} berhasil DITUTUP (CLOSED). Gembok pengajuan baru telah terbuka.");
    }

    /**
     * Membuka gembok pengajuan secara pengecualian khusus (Executive Override) oleh Direktur.
     *
     * Sesuai ketentuan SOP PT Technodrive (Dokumen hal 2, 3, 7):
     * Hanya Direktur yang memiliki wewenang diskresioner untuk membuka gembok pengajuan baru
     * bagi karyawan yang CA sebelumnya belum berstatus CLOSED, dengan syarat mencantumkan
     * alasan pengecualian secara mendalam (minimal 10 karakter) yang dicatat permanen di Audit Trail.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat alasan pembukaan gembok.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance aktif yang mengunci pengajuan.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unlockGembok(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi diskresi khusus: Eksklusif hak Direktur
        if (!$user->isDirektur()) {
            abort(403, 'Akses ditolak: Hanya Direktur yang berwenang membuka gembok pengajuan baru sebagai pengecualian.');
        }

        // 2. Validasi input alasan pengecualian
        $validated = $request->validate([
            'unlock_reason' => ['required', 'string', 'min:10'],
        ], [
            'unlock_reason.required' => 'Alasan pengecualian pembukaan gembok wajib diisi.',
            'unlock_reason.min' => 'Alasan pengecualian minimal 10 karakter.',
        ]);

        $old = $cashAdvance->toArray();

        // 3. Simpan status pembukaan gembok khusus
        $cashAdvance->update([
            'is_unlocked' => true,
            'unlocked_by' => $user->id,
            'unlocked_at' => now(),
            'unlock_reason' => $validated['unlock_reason'],
        ]);

        // 4. Catat tindakan diskresi ini ke Audit Log secara permanen
        AuditLog::record(
            'GEMBOK_OVERRIDE',
            $cashAdvance,
            "Direktur {$user->name} membuka gembok pengajuan untuk karyawan {$cashAdvance->user->name}. Alasan: {$validated['unlock_reason']}",
            $old,
            $cashAdvance->toArray()
        );

        // 5. Beritahukan pemohon bahwa izin pengecualian telah disetujui Direktur
        SystemNotification::send(
            $cashAdvance->user_id,
            'Gembok Pengajuan Dibuka oleh Direktur',
            "Direktur telah memberikan izin pengecualian untuk pengajuan CA baru. Alasan: {$validated['unlock_reason']}",
            'warning',
            route('cash-advances.create')
        );

        return back()->with('success', "Gembok pengajuan berhasil dibuka secara pengecualian untuk {$cashAdvance->user->name}. Aksi ini telah dicatat permanen dalam Audit Log.");
    }

    /**
     * Memeriksa otorisasi hak akses melihat (view permission) dokumen Cash Advance.
     *
     * Aturan Hak Akses Dokumen:
     * - Super Admin dapat memeriksa dokumen untuk kebutuhan pemeliharaan & audit sistem.
     * - Direktur dan tim Finance berhak membuka seluruh dokumen CA.
     * - Pemohon berhak membuka dokumen CA miliknya sendiri.
     * - Atasan berhak membuka dokumen CA milik bawahan langsungnya.
     * - Pengguna lain di luar kriteria di atas akan ditolak (HTTP 403 Forbidden).
     *
     * @param  \App\Models\CashAdvance  $ca Model Cash Advance yang sedang diperiksa.
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika otorisasi gagal.
     */
    private function authorizeView(CashAdvance $ca): void
    {
        $user = Auth::user();

        // 1. Super Admin memiliki hak inspeksi global
        if ($user->isSuperAdmin()) {
            return;
        }

        // 2. Direktur dan Finance memiliki hak tinjau manajerial dan fiskal menyeluruh
        if ($user->isDirektur() || $user->isFinance()) {
            return;
        }

        // 3. Pemohon berhak melihat dokumen miliknya sendiri
        if ($ca->user_id === $user->id) {
            return;
        }

        // 4. Atasan berhak melihat dokumen bawahan langsungnya
        if ($user->isAtasan() && $ca->user->manager_id === $user->id) {
            return;
        }

        // 5. Tolak akses jika tidak memenuhi salah satu kondisi di atas
        abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk membuka Cash Advance ini.');
    }
}
