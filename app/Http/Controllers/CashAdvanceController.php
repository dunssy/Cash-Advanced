<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CashAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('info', 'Super Admin bertugas mengelola pengguna dan konfigurasi sistem.');
        }

        $query = CashAdvance::with(['user', 'approver', 'disburser', 'expenses', 'topUps'])
            ->orderByDesc('created_at');

        // Role-based visibility scoping (Dokumen hal 2-3 & hal 8)
        if ($user->isKaryawan()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isAtasan()) {
            $subordinateIds = $user->subordinates()->pluck('id')->toArray();
            $query->where(function ($q) use ($user, $subordinateIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('user_id', $subordinateIds);
            });
        }
        // Finance & Direktur see all CAs

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'OVERDUE') {
                $query->where('status', '!=', 'CLOSED')
                      ->where('status', '!=', 'REJECTED')
                      ->whereNotNull('due_date')
                      ->where('due_date', '<', Carbon::now());
            } else {
                $query->where('status', $request->status);
            }
        }

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

        $cashAdvances = $query->paginate(15)->withQueryString();

        return view('cash_advances.index', compact('cashAdvances'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            abort(403, 'Super Admin tidak memiliki wewenang mengajukan Cash Advance.');
        }

        // Gembok check: Pengajuan baru digembok sampai CA sebelumnya berstatus CLOSED (Dokumen hal 2, 7)
        if ($user->hasActiveLock()) {
            $lockingCA = $user->getActiveLockingCA();
            return view('cash_advances.locked', compact('lockingCA'));
        }

        return view('cash_advances.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            abort(403, 'Super Admin tidak dapat mengajukan Cash Advance.');
        }

        // Server-side check of Gembok
        if ($user->hasActiveLock()) {
            return back()->with('error', 'Pengajuan ditolak sistem: Anda masih memiliki Cash Advance aktif yang belum berstatus CLOSED.');
        }

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

        // Generate CA Number: CA-YYYYMM-XXXX
        $countThisMonth = CashAdvance::whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->count() + 1;
        $caNumber = sprintf('CA-%s%s-%04d', date('Y'), date('m'), $countThisMonth);

        $ca = CashAdvance::create([
            'ca_number' => $caNumber,
            'user_id' => $user->id,
            'project_name' => $validated['project_name'],
            'project_code' => $validated['project_code'],
            'purpose' => $validated['purpose'],
            'requested_amount' => $validated['requested_amount'],
            'status' => 'PENDING_APPROVAL',
        ]);

        AuditLog::record('CA_CREATED', $ca, "Pengajuan CA {$ca->ca_number} dibuat dengan nominal Rp " . number_format($ca->requested_amount, 0, ',', '.'), null, $ca->toArray());

        // Notify Approver (Atasan langsung atau Direktur bila pemohon adalah Atasan/Finance)
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

        return redirect()->route('cash-advances.show', $ca)
            ->with('success', "Cash Advance {$ca->ca_number} berhasil diajukan dan sedang menunggu persetujuan.");
    }

    public function show(CashAdvance $cashAdvance)
    {
        $this->authorizeView($cashAdvance);

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

        return view('cash_advances.show', compact('cashAdvance'));
    }

    public function approve(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        // Enforce separation of duties
        if (!$user->canApproveCA($cashAdvance)) {
            abort(403, 'Anda tidak memiliki hak menyetujui Cash Advance ini.');
        }

        if ($cashAdvance->status !== 'PENDING_APPROVAL') {
            return back()->with('error', 'Status CA saat ini bukan PENDING_APPROVAL.');
        }

        $validated = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:1000'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        $old = $cashAdvance->toArray();

        $cashAdvance->update([
            'approved_amount' => $validated['approved_amount'],
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'],
        ]);

        AuditLog::record('CA_APPROVED', $cashAdvance, "CA {$cashAdvance->ca_number} disetujui sebesar Rp " . number_format($cashAdvance->approved_amount, 0, ',', '.') . " oleh {$user->name}.", $old, $cashAdvance->toArray());

        // Notify applicant & Finance
        SystemNotification::send(
            $cashAdvance->user_id,
            'Pengajuan CA Disetujui',
            "Pengajuan {$cashAdvance->ca_number} telah disetujui sebesar Rp " . number_format($cashAdvance->approved_amount, 0, ',', '.') . ". Menunggu pencairan oleh Finance.",
            'success',
            route('cash-advances.show', $cashAdvance)
        );

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

    public function reject(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        if (!$user->canApproveCA($cashAdvance)) {
            abort(403, 'Anda tidak memiliki hak menolak Cash Advance ini.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $old = $cashAdvance->toArray();

        $cashAdvance->update([
            'status' => 'REJECTED',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        AuditLog::record('CA_REJECTED', $cashAdvance, "CA {$cashAdvance->ca_number} ditolak oleh {$user->name}. Alasan: {$validated['rejection_reason']}", $old, $cashAdvance->toArray());

        SystemNotification::send(
            $cashAdvance->user_id,
            'Pengajuan CA Ditolak',
            "Pengajuan {$cashAdvance->ca_number} ditolak. Alasan: {$validated['rejection_reason']}",
            'danger',
            route('cash-advances.show', $cashAdvance)
        );

        return back()->with('info', "Cash Advance {$cashAdvance->ca_number} telah ditolak. Gembok pengajuan pemohon dibuka.");
    }

    public function disburse(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        if (!$user->canDisburseCA($cashAdvance)) {
            abort(403, 'Hanya tim Finance yang berhak mencatat pencairan dana.');
        }

        if ($cashAdvance->status !== 'PENDING_APPROVAL' || !$cashAdvance->approved_by) {
            return back()->with('error', 'CA harus sudah disetujui sebelum dapat dicairkan.');
        }

        $validated = $request->validate([
            'disbursed_at' => ['required', 'date', 'before_or_equal:today'],
            'disbursement_ref' => ['required', 'string', 'max:100'],
            'disbursement_notes' => ['nullable', 'string'],
        ], [
            'disbursed_at.required' => 'Tanggal pencairan wajib diisi.',
            'disbursed_at.before_or_equal' => 'Tanggal pencairan tidak boleh di masa depan.',
            'disbursement_ref.required' => 'Nomor bukti transfer / voucher bank wajib diisi.',
        ]);

        $maxDays = (int) SystemSetting::get('max_ca_duration_days', 60);
        $disbursedAt = Carbon::parse($validated['disbursed_at']);
        $dueDate = $disbursedAt->copy()->addDays($maxDays);

        $old = $cashAdvance->toArray();

        $cashAdvance->update([
            'status' => 'ACTIVE',
            'disbursed_by' => $user->id,
            'disbursed_at' => $disbursedAt,
            'disbursement_ref' => $validated['disbursement_ref'],
            'disbursement_notes' => $validated['disbursement_notes'],
            'due_date' => $dueDate,
        ]);

        AuditLog::record('CA_DISBURSED', $cashAdvance, "Pencairan dana CA {$cashAdvance->ca_number} dicatat sebesar Rp " . number_format($cashAdvance->approved_amount, 0, ',', '.') . " (Ref: {$validated['disbursement_ref']}). Durasi {$maxDays} hari berjalan.", $old, $cashAdvance->toArray());

        SystemNotification::send(
            $cashAdvance->user_id,
            'Dana CA Telah Cair - Saldo Aktif',
            "Dana {$cashAdvance->ca_number} telah cair sebesar Rp " . number_format($cashAdvance->approved_amount, 0, ',', '.') . ". Hitung mundur durasi telah dimulai sampai " . $dueDate->format('d M Y') . ". Silakan catat pengeluaran secara real-time.",
            'success',
            route('cash-advances.show', $cashAdvance)
        );

        return back()->with('success', "Pencairan dana berhasil dicatat. Status CA kini ACTIVE dan saldo dapat digunakan.");
    }

    public function completeProject(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Hanya pemohon yang dapat menandai proyek selesai.');
        }

        if ($cashAdvance->status !== 'ACTIVE') {
            return back()->with('error', 'Hanya CA berstatus ACTIVE yang dapat diselesaikan.');
        }

        $balance = $cashAdvance->current_balance;

        if ($balance > 0) {
            // Saldo tersisa: wajib dikembalikan (status NEED_SETTLEMENT)
            $cashAdvance->update([
                'status' => 'NEED_SETTLEMENT',
                'project_completed_at' => now(),
            ]);

            AuditLog::record('CA_NEED_SETTLEMENT', $cashAdvance, "Proyek selesai dengan sisa saldo Rp " . number_format($balance, 0, ',', '.') . ". Status dialihkan ke NEED_SETTLEMENT.");

            return back()->with('warning', "Proyek ditandai selesai. Sisa saldo Rp " . number_format($balance, 0, ',', '.') . " wajib ditransfer kembali ke rekening kantor dalam 3 hari kerja.");
        } else {
            // Saldo Rp0: langsung ke verifikasi akhir Finance
            $cashAdvance->update([
                'status' => 'PENDING_VERIFICATION',
                'project_completed_at' => now(),
            ]);

            AuditLog::record('CA_PENDING_VERIFICATION', $cashAdvance, "Proyek selesai dengan saldo Rp0. Menunggu verifikasi akhir Finance.");

            return back()->with('info', "Proyek selesai. Seluruh bukti struk kini sedang dalam proses verifikasi akhir Finance.");
        }
    }

    public function submitRefund(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Hanya pemohon yang dapat mengunggah bukti refund.');
        }

        if ($cashAdvance->status !== 'NEED_SETTLEMENT') {
            return back()->with('error', 'CA tidak dalam status NEED_SETTLEMENT.');
        }

        $validated = $request->validate([
            'settlement_refund_amount' => ['required', 'numeric', 'min:1'],
            'settlement_transferred_at' => ['required', 'date', 'before_or_equal:today'],
            'settlement_refund_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // max 10MB
            'settlement_notes' => ['nullable', 'string'],
        ], [
            'settlement_refund_amount.required' => 'Nominal transfer refund wajib diisi.',
            'settlement_refund_proof.required' => 'Bukti struk / transfer bank wajib diunggah.',
            'settlement_refund_proof.mimes' => 'Format file bukti refund harus berupa JPG, PNG, atau PDF.',
        ]);

        $filePath = $request->file('settlement_refund_proof')->store('refunds', 'public');

        $cashAdvance->update([
            'settlement_refund_amount' => $validated['settlement_refund_amount'],
            'settlement_transferred_at' => $validated['settlement_transferred_at'],
            'settlement_refund_proof' => $filePath,
            'settlement_notes' => $validated['settlement_notes'],
            'status' => 'PENDING_VERIFICATION',
        ]);

        AuditLog::record('SETTLEMENT_REFUND_UPLOADED', $cashAdvance, "Bukti transfer refund sebesar Rp " . number_format($validated['settlement_refund_amount'], 0, ',', '.') . " diunggah oleh {$user->name}. Status berubah ke PENDING_VERIFICATION.");

        // Notify Finance
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

    public function close(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        if (!$user->canVerifyAndCloseCA($cashAdvance)) {
            abort(403, 'Anda tidak berhak menutup Cash Advance ini.');
        }

        // Validate that no expenses are still PENDING or NEEDS_REVISION
        $unverifiedCount = $cashAdvance->expenses()
            ->whereIn('verification_status', ['PENDING', 'NEEDS_REVISION'])
            ->count();

        if ($unverifiedCount > 0) {
            return back()->with('error', "Tidak dapat menutup CA: Masih terdapat {$unverifiedCount} struk pengeluaran yang belum diverifikasi atau masih berstatus PERLU PERBAIKAN.");
        }

        $old = $cashAdvance->toArray();

        $cashAdvance->update([
            'status' => 'CLOSED',
            'closed_by' => $user->id,
            'closed_at' => now(),
        ]);

        AuditLog::record('CA_CLOSED', $cashAdvance, "Cash Advance {$cashAdvance->ca_number} resmi ditutup (CLOSED) oleh {$user->name}. Gembok pengajuan baru telah terbuka.", $old, $cashAdvance->toArray());

        SystemNotification::send(
            $cashAdvance->user_id,
            'Cash Advance CLOSED',
            "CA {$cashAdvance->ca_number} ({$cashAdvance->project_name}) telah resmi ditutup (CLOSED). Gembok pengajuan telah dibuka dan Anda dapat mengajukan CA baru bila diperlukan.",
            'success',
            route('cash-advances.show', $cashAdvance)
        );

        return back()->with('success', "Cash Advance {$cashAdvance->ca_number} berhasil DITUTUP (CLOSED). Gembok pengajuan baru telah terbuka.");
    }

    public function unlockGembok(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        // Hanya Direktur yang bisa membuka gembok sebagai pengecualian (Dokumen hal 2, 3, 7)
        if (!$user->isDirektur()) {
            abort(403, 'Hanya Direktur yang berwenang membuka gembok pengajuan baru sebagai pengecualian.');
        }

        $validated = $request->validate([
            'unlock_reason' => ['required', 'string', 'min:10'],
        ], [
            'unlock_reason.required' => 'Alasan pengecualian pembukaan gembok wajib diisi.',
            'unlock_reason.min' => 'Alasan pengecualian minimal 10 karakter.',
        ]);

        $old = $cashAdvance->toArray();

        $cashAdvance->update([
            'is_unlocked' => true,
            'unlocked_by' => $user->id,
            'unlocked_at' => now(),
            'unlock_reason' => $validated['unlock_reason'],
        ]);

        AuditLog::record('GEMBOK_OVERRIDE', $cashAdvance, "Direktur {$user->name} membuka gembok pengajuan untuk karyawan {$cashAdvance->user->name}. Alasan: {$validated['unlock_reason']}", $old, $cashAdvance->toArray());

        SystemNotification::send(
            $cashAdvance->user_id,
            'Gembok Pengajuan Dibuka oleh Direktur',
            "Direktur telah memberikan izin pengecualian untuk pengajuan CA baru. Alasan: {$validated['unlock_reason']}",
            'warning',
            route('cash-advances.create')
        );

        return back()->with('success', "Gembok pengajuan berhasil dibuka secara pengecualian untuk {$cashAdvance->user->name}. Aksi ini telah dicatat permanen dalam Audit Log.");
    }

    private function authorizeView(CashAdvance $ca): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return; // Admin can inspect for configuration & audit
        }

        if ($user->isDirektur() || $user->isFinance()) {
            return; // Direktur & Finance see all CAs
        }

        if ($ca->user_id === $user->id) {
            return; // Own CA
        }

        if ($user->isAtasan() && $ca->user->manager_id === $user->id) {
            return; // Subordinate CA
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk membuka Cash Advance ini.');
    }
}
