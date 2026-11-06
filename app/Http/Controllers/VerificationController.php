<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function verifyExpense(Request $request, Expense $expense)
    {
        $user = Auth::user();
        $cashAdvance = $expense->cashAdvance;

        // Separation of duties check
        if (!$user->canVerifyAndCloseCA($cashAdvance)) {
            abort(403, 'Anda tidak memiliki hak memverifikasi struk pada Cash Advance ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:VALID,NEEDS_REVISION,REJECTED'],
            'verification_notes' => ['nullable', 'string'],
        ]);

        $status = $validated['status'];
        $notes = $validated['verification_notes'];

        if (($status === 'NEEDS_REVISION' || $status === 'REJECTED') && empty($notes)) {
            return back()->with('error', 'Catatan verifikasi wajib diisi untuk status Perlu Perbaikan atau Ditolak.');
        }

        $oldStatus = $expense->verification_status;

        $expense->update([
            'verification_status' => $status,
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);

        $actionDesc = match ($status) {
            'VALID' => 'diverifikasi VALID',
            'NEEDS_REVISION' => 'ditandai PERLU PERBAIKAN',
            'REJECTED' => 'DITOLAK (saldo dipulihkan dan menjadi kewajiban karyawan)',
        };

        AuditLog::record('EXPENSE_VERIFIED', $expense, "Struk transaksi #{$expense->id} ({$expense->description}) {$actionDesc} oleh {$user->name}. Catatan: {$notes}");

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

        SystemNotification::send(
            $expense->user_id,
            $notifTitle,
            "Transaksi '{$expense->description}' ({$cashAdvance->ca_number}) {$actionDesc}. " . ($notes ? "Catatan: {$notes}" : ''),
            $notifType,
            route('cash-advances.show', $cashAdvance)
        );

        return back()->with('success', "Status struk pengeluaran berhasil diperbarui menjadi {$status}.");
    }
}
