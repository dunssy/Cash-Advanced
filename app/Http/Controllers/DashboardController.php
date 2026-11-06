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

class DashboardController extends Controller
{
    public function index()
    {
        ReminderService::checkAllReminders();

        $user = Auth::user();
        $now = Carbon::now();

        // 1. Own active CA & lock status (for every user who can submit CA)
        $activeCA = CashAdvance::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        $lockingCA = $user->getActiveLockingCA();
        $hasLock = $user->hasActiveLock();

        // 2. Role-specific metrics
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

        // Subordinate IDs for Atasan
        $subordinateIds = $user->subordinates()->pluck('id')->toArray();

        if ($user->isAtasan()) {
            $stats['pending_approval_count'] = CashAdvance::whereIn('user_id', $subordinateIds)
                ->where('status', 'PENDING_APPROVAL')
                ->count();

            $stats['pending_topup_count'] = TopUp::whereIn('user_id', $subordinateIds)
                ->where('status', 'PENDING_ATASAN')
                ->count();
        }

        if ($user->isFinance()) {
            $stats['pending_disbursement_count'] = CashAdvance::where('status', 'PENDING_APPROVAL')
                ->whereNotNull('approved_by')
                ->count();

            $stats['pending_verification_count'] = Expense::where('verification_status', 'PENDING')->count();

            $stats['pending_topup_count'] = TopUp::where('status', 'PENDING_FINANCE')->count();

            $stats['overdue_count'] = CashAdvance::where('status', '!=', 'CLOSED')
                ->where('status', '!=', 'REJECTED')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->count();

            $stats['total_active_float'] = CashAdvance::where('status', 'ACTIVE')->get()->sum->current_balance;
        }

        if ($user->isDirektur()) {
            // Direktur handles approvals if applicant is Atasan or Finance
            $stats['pending_approval_count'] = CashAdvance::whereHas('user', function ($q) {
                $q->whereIn('role', ['atasan', 'finance']);
            })->where('status', 'PENDING_APPROVAL')->count();

            $stats['pending_topup_count'] = TopUp::where('status', 'PENDING_DIREKTUR')->count();

            $stats['overdue_count'] = CashAdvance::where('status', '!=', 'CLOSED')
                ->where('status', '!=', 'REJECTED')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->count();

            $stats['total_active_float'] = CashAdvance::where('status', 'ACTIVE')->get()->sum->current_balance;
        }

        // Recent Cash Advances relevant to this user
        $recentCAsQuery = CashAdvance::with(['user', 'approver', 'disburser'])->orderByDesc('created_at');

        if ($user->isKaryawan()) {
            $recentCAsQuery->where('user_id', $user->id);
        } elseif ($user->isAtasan()) {
            $recentCAsQuery->where(function ($q) use ($user, $subordinateIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('user_id', $subordinateIds);
            });
        } elseif ($user->isSuperAdmin()) {
            $recentCAsQuery->take(5);
        }

        $recentCAs = $recentCAsQuery->take(10)->get();

        // Recent Notifications
        $notifications = $user->unreadNotifications()->take(5)->get();

        return view('dashboard.index', compact('stats', 'recentCAs', 'notifications'));
    }
}
