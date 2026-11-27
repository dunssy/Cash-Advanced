<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\TopUp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class SoftDeleteArchiveController
 *
 * Menyediakan antarmuka peninjauan dan pemulihan (restore) data sektor keuangan
 * dan transaksi yang telah dihapus secara lunak (Soft Delete) dari frontend.
 */
class SoftDeleteArchiveController extends Controller
{
    /**
     * Menampilkan daftar data transaksi dan keuangan yang berstatus Soft Deleted.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $this->ensureAuthorized($user);

        $tab = $request->input('tab', 'all');
        $search = trim((string) $request->input('search', ''));

        $cashAdvancesQuery = CashAdvance::onlyTrashed()
            ->with(['user', 'deleter'])
            ->orderByDesc('deleted_at');

        $expensesQuery = Expense::onlyTrashed()
            ->with([
                'user',
                'deleter',
                'cashAdvance' => fn ($q) => $q->withTrashed(),
            ])
            ->orderByDesc('deleted_at');

        $topUpsQuery = TopUp::onlyTrashed()
            ->with([
                'user',
                'deleter',
                'cashAdvance' => fn ($q) => $q->withTrashed(),
            ])
            ->orderByDesc('deleted_at');

        if ($search !== '') {
            $cashAdvancesQuery->where(function ($q) use ($search) {
                $q->where('ca_number', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('delete_reason', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });

            $expensesQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%")
                    ->orWhere('delete_reason', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });

            $topUpsQuery->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('delete_reason', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $trashedCashAdvances = $cashAdvancesQuery->get();
        $trashedExpenses = $expensesQuery->get();
        $trashedTopUps = $topUpsQuery->get();

        $counts = [
            'cash_advances' => CashAdvance::onlyTrashed()->count(),
            'expenses' => Expense::onlyTrashed()->count(),
            'top_ups' => TopUp::onlyTrashed()->count(),
        ];
        $counts['total'] = $counts['cash_advances'] + $counts['expenses'] + $counts['top_ups'];

        return view('soft_deletes.index', compact(
            'tab',
            'search',
            'trashedCashAdvances',
            'trashedExpenses',
            'trashedTopUps',
            'counts'
        ));
    }

    /**
     * Memulihkan Cash Advance yang berstatus soft delete kembali ke operasional aktif.
     */
    public function restoreCashAdvance(int $id): RedirectResponse
    {
        $user = Auth::user();
        $this->ensureAuthorized($user);

        $cashAdvance = CashAdvance::onlyTrashed()->findOrFail($id);
        $oldValues = $cashAdvance->toArray();

        $cashAdvance->restore();
        $cashAdvance->update([
            'deleted_by' => null,
            'delete_reason' => null,
        ]);

        AuditLog::record(
            'CA_RESTORED',
            $cashAdvance,
            "Cash Advance {$cashAdvance->ca_number} dipulihkan dari arsip Soft Delete oleh {$user->name}.",
            $oldValues,
            $cashAdvance->fresh()->toArray()
        );

        return back()->with('success', "Cash Advance {$cashAdvance->ca_number} berhasil dipulihkan.");
    }

    /**
     * Memulihkan transaksi pengeluaran yang berstatus soft delete.
     */
    public function restoreExpense(int $id): RedirectResponse
    {
        $user = Auth::user();
        $this->ensureAuthorized($user);

        $expense = Expense::onlyTrashed()->findOrFail($id);
        $parentCA = CashAdvance::withTrashed()->find($expense->cash_advance_id);

        if (!$parentCA || $parentCA->trashed()) {
            return back()->with('error', 'Tidak dapat memulihkan transaksi karena Cash Advance induknya masih dalam status terhapus. Pulihkan Cash Advance terlebih dahulu.');
        }

        $oldValues = $expense->toArray();

        $expense->restore();
        $expense->update([
            'deleted_by' => null,
            'delete_reason' => null,
        ]);

        AuditLog::record(
            'EXPENSE_RESTORED',
            $expense,
            "Transaksi pengeluaran #{$expense->id} ({$expense->description}) dipulihkan oleh {$user->name}.",
            $oldValues,
            $expense->fresh()->toArray()
        );

        return back()->with('success', "Transaksi pengeluaran '{$expense->description}' berhasil dipulihkan.");
    }

    /**
     * Memulihkan pengajuan Top-Up yang berstatus soft delete.
     */
    public function restoreTopUp(int $id): RedirectResponse
    {
        $user = Auth::user();
        $this->ensureAuthorized($user);

        $topUp = TopUp::onlyTrashed()->findOrFail($id);
        $parentCA = CashAdvance::withTrashed()->find($topUp->cash_advance_id);

        if (!$parentCA || $parentCA->trashed()) {
            return back()->with('error', 'Tidak dapat memulihkan Top-Up karena Cash Advance induknya masih dalam status terhapus. Pulihkan Cash Advance terlebih dahulu.');
        }

        $oldValues = $topUp->toArray();

        $topUp->restore();
        $topUp->update([
            'deleted_by' => null,
            'delete_reason' => null,
        ]);

        AuditLog::record(
            'TOPUP_RESTORED',
            $topUp,
            "Pengajuan Top-Up #{$topUp->id} sebesar Rp " . number_format($topUp->amount, 0, ',', '.') . " dipulihkan oleh {$user->name}.",
            $oldValues,
            $topUp->fresh()->toArray()
        );

        return back()->with('success', 'Pengajuan Top-Up berhasil dipulihkan.');
    }

    /**
     * Memastikan hanya Finance, Direktur, atau Super Admin yang dapat mengakses arsip soft delete.
     */
    private function ensureAuthorized(User $user): void
    {
        if (!$user->isFinance() && !$user->isDirektur() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak: Hanya Finance, Direktur, atau Super Admin yang berhak mengelola arsip data terhapus.');
        }
    }
}
