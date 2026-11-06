<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\TopUp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopUpController extends Controller
{
    public function store(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Hanya pemegang CA yang dapat mengajukan Top-Up.');
        }

        if ($cashAdvance->status !== 'ACTIVE') {
            return back()->with('error', 'Top-up hanya dapat diajukan untuk CA berstatus ACTIVE.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:50000'],
            'reason' => ['required', 'string', 'min:10'],
        ], [
            'amount.min' => 'Nominal pengajuan Top-Up minimal Rp 50.000.',
            'reason.min' => 'Alasan kebutuhan Top-Up minimal 10 karakter.',
        ]);

        $amount = (float) $validated['amount'];
        $initialCA = (float) ($cashAdvance->approved_amount ?: $cashAdvance->requested_amount);

        // Check if Direktur approval is required
        $nominalLimit = (float) SystemSetting::get('topup_nominal_threshold', 5000000);
        $percentLimit = (float) SystemSetting::get('topup_percent_threshold', 50);
        $maxTimesLimit = (int) SystemSetting::get('topup_max_times_before_direktur', 2);

        $existingTopupCount = $cashAdvance->topUps()->count();
        $isOverNominal = ($amount > $nominalLimit);
        $isOverPercent = ($initialCA > 0 && ($amount / $initialCA * 100) > $percentLimit);
        $isOverCount = ($existingTopupCount >= $maxTimesLimit);

        $requiresDirektur = ($isOverNominal || $isOverPercent || $isOverCount);

        $topup = TopUp::create([
            'cash_advance_id' => $cashAdvance->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'reason' => $validated['reason'],
            'status' => 'PENDING_ATASAN',
            'requires_direktur_approval' => $requiresDirektur,
        ]);

        AuditLog::record('TOPUP_REQUESTED', $topup, "Pengajuan Top-Up Rp " . number_format($amount, 0, ',', '.') . " untuk {$cashAdvance->ca_number} diajukan oleh {$user->name}." . ($requiresDirektur ? ' (Memerlukan persetujuan Direktur)' : ''));

        // Notify Atasan
        if ($user->manager_id) {
            SystemNotification::send(
                $user->manager_id,
                'Pengajuan Top-Up Dana CA',
                "Bawahan Anda, {$user->name}, mengajukan Top-Up sebesar Rp " . number_format($amount, 0, ',', '.') . " untuk {$cashAdvance->ca_number}.",
                'info',
                route('cash-advances.show', $cashAdvance)
            );
        }

        return back()->with('success', "Pengajuan Top-Up sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil diajukan dan sedang menunggu persetujuan Atasan.");
    }

    public function approveAtasan(Request $request, TopUp $topUp)
    {
        $user = Auth::user();

        // Pemisahan tugas: Pemohon tidak bisa approve sendiri
        if ($topUp->user_id === $user->id) {
            abort(403, 'Anda tidak dapat menyetujui pengajuan Top-Up milik sendiri.');
        }

        if (!$user->isAtasan() && !$user->isDirektur()) {
            abort(403, 'Akses ditolak.');
        }

        if ($topUp->status !== 'PENDING_ATASAN') {
            return back()->with('error', 'Status Top-Up bukan PENDING_ATASAN.');
        }

        $topUp->update([
            'approved_by_atasan' => $user->id,
            'atasan_approved_at' => now(),
            'status' => 'PENDING_FINANCE', // Tahap 2: Finance
        ]);

        AuditLog::record('TOPUP_APPROVED_ATASAN', $topUp, "Top-Up #{$topUp->id} disetujui Atasan ({$user->name}). Status beralih ke PENDING_FINANCE.");

        // Notify Finance
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

    public function approveFinance(Request $request, TopUp $topUp)
    {
        $user = Auth::user();

        if ($topUp->user_id === $user->id) {
            abort(403, 'Anda tidak dapat menyetujui pengajuan milik sendiri.');
        }

        if (!$user->isFinance() && !$user->isDirektur()) {
            abort(403, 'Akses ditolak.');
        }

        if ($topUp->status !== 'PENDING_FINANCE') {
            return back()->with('error', 'Status Top-Up bukan PENDING_FINANCE.');
        }

        $nextStatus = $topUp->requires_direktur_approval ? 'PENDING_DIREKTUR' : 'APPROVED';

        $topUp->update([
            'approved_by_finance' => $user->id,
            'finance_approved_at' => now(),
            'status' => $nextStatus,
        ]);

        AuditLog::record('TOPUP_APPROVED_FINANCE', $topUp, "Top-Up #{$topUp->id} disetujui Finance ({$user->name}). Status beralih ke {$nextStatus}.");

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

        return back()->with('success', $nextStatus === 'APPROVED' ? 'Top-Up telah disetujui penuh dan siap dicairkan oleh Finance.' : 'Top-Up disetujui Finance dan diteruskan ke Direktur (melebihi batas ambang).');
    }

    public function approveDirektur(Request $request, TopUp $topUp)
    {
        $user = Auth::user();

        if (!$user->isDirektur()) {
            abort(403, 'Hanya Direktur yang dapat menyetujui tahap ini.');
        }

        if ($topUp->user_id === $user->id) {
            abort(403, 'Anda tidak dapat menyetujui pengajuan milik sendiri.');
        }

        $topUp->update([
            'approved_by_direktur' => $user->id,
            'direktur_approved_at' => now(),
            'status' => 'APPROVED',
        ]);

        AuditLog::record('TOPUP_APPROVED_DIREKTUR', $topUp, "Top-Up #{$topUp->id} disetujui Direktur ({$user->name}). Status kini APPROVED.");

        return back()->with('success', 'Top-Up telah disetujui Direktur dan siap dicairkan oleh Finance.');
    }

    public function reject(Request $request, TopUp $topUp)
    {
        $user = Auth::user();

        if ($topUp->user_id === $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        $topUp->update([
            'status' => 'REJECTED',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        AuditLog::record('TOPUP_REJECTED', $topUp, "Top-Up #{$topUp->id} ditolak oleh {$user->name}. Alasan: {$validated['rejection_reason']}");

        SystemNotification::send(
            $topUp->user_id,
            'Top-Up Ditolak',
            "Pengajuan Top-Up Rp " . number_format($topUp->amount, 0, ',', '.') . " ditolak. Alasan: {$validated['rejection_reason']}",
            'danger',
            route('cash-advances.show', $topUp->cash_advance_id)
        );

        return back()->with('info', 'Pengajuan Top-Up telah ditolak.');
    }

    public function disburse(Request $request, TopUp $topUp)
    {
        $user = Auth::user();

        if (!$user->isFinance() && !$user->isDirektur()) {
            abort(403, 'Hanya tim Finance yang dapat mencatat pencairan Top-Up.');
        }

        if ($topUp->status !== 'APPROVED') {
            return back()->with('error', 'Top-up belum disetujui sepenuhnya.');
        }

        $validated = $request->validate([
            'disbursement_ref' => ['required', 'string', 'max:100'],
            'disbursement_notes' => ['nullable', 'string'],
        ], [
            'disbursement_ref.required' => 'Nomor referensi transfer pencairan top-up wajib diisi.',
        ]);

        $topUp->update([
            'status' => 'DISBURSED',
            'disbursed_by' => $user->id,
            'disbursed_at' => now(),
            'disbursement_ref' => $validated['disbursement_ref'],
            'disbursement_notes' => $validated['disbursement_notes'] ?? null,
        ]);

        AuditLog::record('TOPUP_DISBURSED', $topUp, "Dana Top-Up Rp " . number_format($topUp->amount, 0, ',', '.') . " dicatat cair (Ref: {$validated['disbursement_ref']}). Saldo CA bertambah otomatis.");

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
