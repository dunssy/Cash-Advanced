<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\SystemNotification;
use App\Models\TopUp;
use App\Models\User;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class DashboardController
 *
 * Mengelola penyajian data dan ringkasan metrik analitik pada Dashboard Utama
 * sistem Cash Advance & Settlement PT Technodrive.
 *
 * Dashboard dirancang adaptif berdasarkan peran pengguna (Role-Based Dashboard):
 * - Karyawan: Memantau status CA aktif, sisa saldo pribadi, dan status gembok pengajuan baru.
 * - Atasan: Memantau pengajuan CA dan Top-Up dari bawahan langsung yang membutuhkan persetujuan.
 * - Finance: Memantau antrean pencairan dana, verifikasi keabsahan struk, dan perputaran dana aktif (active float).
 * - Direktur: Memantau persetujuan bernominal besar/khusus, CA jatuh tempo (overdue), dan total float perusahaan.
 * - Super Admin: Memantau aktivitas operasional secara komprehensif.
 *
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama sistem.
     *
     * Alur proses kalkulasi dan rendering:
     * 1. Menjalankan `ReminderService::checkAllReminders()` untuk menyinkronkan notifikasi pengingat
     *    jatuh tempo dan tenggat waktu settlement.
     * 2. Menghitung status penguncian pengajuan baru (Gembok CA) bagi pengguna aktif.
     * 3. Menghitung ringkasan metrik statistik operasional sesuai hak akses peran pengguna.
     * 4. Memuat riwayat pengajuan Cash Advance terbaru yang terikat dengan peran pemohon/penyetuju.
     * 5. Mengambil daftar notifikasi terbaru yang belum dibaca (unread notifications).
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // 1. Eksekusi pengecekan otomatis pengingat sistem (SLA & Jatuh Tempo)
        ReminderService::checkAllReminders();

        $user = Auth::user();
        $now = Carbon::now();

        // 2. Status CA aktif dan status Gembok Pengajuan milik pengguna saat ini
        // Sesuai SOP PT Technodrive: Satu karyawan hanya boleh memiliki satu CA aktif sekaligus.
        $activeCA = CashAdvance::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        $lockingCA = $user->getActiveLockingCA();
        $hasLock = $user->hasActiveLock();

        // 3. Inisialisasi struktur array metrik dashboard
        $stats = [
            'my_active_ca' => $activeCA,
            'my_locking_ca' => $lockingCA,
            'has_lock' => $hasLock,
            'pending_approval_count' => 0,
            'pending_verification_count' => 0,
            'pending_disbursement_count' => 0,
            'overdue_count' => 0,
            'total_active_float' => 0,
            'pending_topup_count' => 0,
        ];

        // Daftar ID bawahan langsung (hanya berlaku untuk peran Atasan)
        $subordinateIds = $user->subordinates()->pluck('id')->toArray();

        // 4. Kalkulasi metrik spesifik untuk peran Atasan (Managerial View)
        if ($user->isAtasan()) {
            // Jumlah pengajuan CA bawahan yang menanti persetujuan Atasan
            $stats['pending_approval_count'] = CashAdvance::whereIn('user_id', $subordinateIds)
                ->where('status', 'PENDING_APPROVAL')
                ->whereNull('approved_by')
                ->count();

            // Jumlah pengajuan Top-Up bawahan yang menanti persetujuan Atasan
            $stats['pending_topup_count'] = TopUp::whereIn('user_id', $subordinateIds)
                ->where('status', 'PENDING_ATASAN')
                ->count();
        }

        // 5. Kalkulasi metrik spesifik untuk peran Finance (Treasury & Verification View)
        if ($user->isFinance()) {
            // Jumlah CA yang sudah disetujui Atasan dan siap dicairkan dananya oleh Finance
            $stats['pending_disbursement_count'] = CashAdvance::where('status', 'PENDING_APPROVAL')
                ->whereNotNull('approved_by')
                ->count();

            // Jumlah struk/bukti pengeluaran yang menanti proses audit & verifikasi oleh Finance
            $stats['pending_verification_count'] = Expense::where('verification_status', 'PENDING')->count();

            // Jumlah Top-Up yang menanti tinjauan Finance atau siap dicairkan
            $stats['pending_topup_count'] = TopUp::whereIn('status', ['PENDING_FINANCE', 'APPROVED'])->count();

            // Jumlah CA yang telah melampaui batas waktu penyelesaian (lewat jatuh tempo / overdue)
            $stats['overdue_count'] = CashAdvance::where('status', '!=', 'CLOSED')
                ->where('status', '!=', 'REJECTED')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->count();

            // Total dana operasional perusahaan yang sedang berada di tangan pemegang CA (Active Float)
            $stats['total_active_float'] = CashAdvance::where('status', 'ACTIVE')->get()->sum->current_balance;
        }

        // 6. Kalkulasi metrik spesifik untuk peran Direktur (Executive View)
        if ($user->isDirektur()) {
            // Direktur menangani persetujuan khusus bila pemohon adalah Atasan atau Tim Finance
            $stats['pending_approval_count'] = CashAdvance::whereHas('user', function ($q) {
                $q->whereIn('role', ['atasan', 'finance']);
            })->where('status', 'PENDING_APPROVAL')
              ->whereNull('approved_by')
              ->count();

            // Antrean pencairan dana perusahaan
            $stats['pending_disbursement_count'] = CashAdvance::where('status', 'PENDING_APPROVAL')
                ->whereNotNull('approved_by')
                ->count();

            // Pengajuan Top-Up bernominal besar / melampaui batas yang membutuhkan persetujuan Direktur
            $stats['pending_topup_count'] = TopUp::where('status', 'PENDING_DIREKTUR')->count();

            // Total CA yang berstatus jatuh tempo (Overdue) di seluruh organisasi
            $stats['overdue_count'] = CashAdvance::where('status', '!=', 'CLOSED')
                ->where('status', '!=', 'REJECTED')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->count();

            // Total saldo kas mengambang (Active Float) yang sedang berjalan
            $stats['total_active_float'] = CashAdvance::where('status', 'ACTIVE')->get()->sum->current_balance;
        }

        // 7. Pengambilan data pengajuan Cash Advance terbaru sesuai pembatasan akses data (Data Scoping)
        $recentCAsQuery = CashAdvance::with(['user', 'approver', 'disburser'])->orderByDesc('created_at');

        if ($user->isKaryawan()) {
            // Karyawan hanya dapat melihat pengajuan miliknya sendiri
            $recentCAsQuery->where('user_id', $user->id);
        } elseif ($user->isAtasan()) {
            // Atasan dapat melihat pengajuan miliknya dan seluruh bawahan langsungnya
            $recentCAsQuery->where(function ($q) use ($user, $subordinateIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('user_id', $subordinateIds);
            });
        } elseif ($user->isSuperAdmin()) {
            // Super Admin melihat sampel data ringkas sistem
            $recentCAsQuery->take(5);
        }

        $recentCAs = $recentCAsQuery->take(10)->get();

        // 8. Ambil notifikasi terkini yang belum dibaca untuk interaksi pengguna
        $notifications = $user->unreadNotifications()->take(5)->get();

        // 9. Kirimkan seluruh data analitik ke antarmuka dashboard
        return view('dashboard.index', compact('stats', 'recentCAs', 'notifications'));
    }
}
