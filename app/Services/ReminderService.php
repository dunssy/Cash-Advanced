<?php

namespace App\Services;

use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;

class ReminderService
{
    public static function checkAllReminders(): void
    {
        $today = Carbon::today();

        // 1. Check Active CAs for Countdown & Low Balance
        $activeCAs = CashAdvance::with(['user.manager'])->where('status', 'ACTIVE')->get();

        foreach ($activeCAs as $ca) {
            $user = $ca->user;
            $manager = $user?->manager;

            // Auto-check Overdue / Auto-transition to NEED_SETTLEMENT if 2 months expired
            if ($ca->due_date && Carbon::now()->greaterThan($ca->due_date)) {
                if ($ca->current_balance > 0) {
                    $ca->update(['status' => 'NEED_SETTLEMENT']);
                    
                    self::notifyOnce(
                        $user->id,
                        'ca_need_settlement_' . $ca->id,
                        'Batas Waktu CA Habis - Sisa Dana Wajib Dikembalikan',
                        "Masa aktif {$ca->ca_number} ({$ca->project_name}) telah habis. Sisa saldo Rp " . number_format($ca->current_balance, 0, ',', '.') . " wajib ditransfer kembali dalam 3 hari kerja.",
                        'danger',
                        route('cash-advances.show', $ca)
                    );
                }
            }

            // Reminders H-7, H-3, H-0 (Due date)
            if ($ca->due_date) {
                $daysRemaining = Carbon::now()->diffInDays($ca->due_date, false);

                if ($daysRemaining == 7) {
                    $key = 'h7_' . $ca->id . '_' . $today->toDateString();
                    self::notifyOnce($user->id, $key, 'Pengingat H-7 Jatuh Tempo CA', "CA {$ca->ca_number} akan jatuh tempo dalam 7 hari.", 'warning', route('cash-advances.show', $ca));
                    if ($manager) {
                        self::notifyOnce($manager->id, $key . '_mgr', 'Pengingat H-7 Jatuh Tempo Bawahan', "CA {$ca->ca_number} milik {$user->name} akan jatuh tempo dalam 7 hari.", 'warning', route('cash-advances.show', $ca));
                    }
                } elseif ($daysRemaining == 3) {
                    $key = 'h3_' . $ca->id . '_' . $today->toDateString();
                    self::notifyOnce($user->id, $key, 'Pengingat H-3 Jatuh Tempo CA', "PENTING: CA {$ca->ca_number} tersisa 3 hari lagi. Segera catat semua pengeluaran atau persiapkan settlement.", 'danger', route('cash-advances.show', $ca));
                    if ($manager) {
                        self::notifyOnce($manager->id, $key . '_mgr', 'Pengingat H-3 Jatuh Tempo Bawahan', "CA {$ca->ca_number} milik {$user->name} tersisa 3 hari lagi.", 'danger', route('cash-advances.show', $ca));
                    }
                } elseif ($daysRemaining <= 0) {
                    $key = 'h0_' . $ca->id . '_' . $today->toDateString();
                    self::notifyOnce($user->id, $key, 'Hari Jatuh Tempo CA!', "Hari ini adalah batas waktu penyelesaian CA {$ca->ca_number}.", 'danger', route('cash-advances.show', $ca));
                }
            }

            // Low Balance Reminder (< 20%)
            if ($ca->is_low_balance) {
                $key = 'low_bal_' . $ca->id;
                self::notifyOnce(
                    $user->id,
                    $key,
                    'Peringatan: Saldo CA Kurang dari 20%',
                    "Sisa saldo CA {$ca->ca_number} saat ini Rp " . number_format($ca->current_balance, 0, ',', '.') . " (< 20%). Segera ajukan Top-Up bila masih ada kebutuhan proyek.",
                    'warning',
                    route('cash-advances.show', $ca)
                );
                if ($manager) {
                    self::notifyOnce(
                        $manager->id,
                        $key . '_mgr',
                        'Saldo CA Bawahan Menipis (< 20%)',
                        "Saldo CA {$ca->ca_number} ({$user->name}) tersisa Rp " . number_format($ca->current_balance, 0, ',', '.') . " (< 20%).",
                        'info',
                        route('cash-advances.show', $ca)
                    );
                }
            }
        }

        // 2. Overdue CAs: Alert Finance & Direktur
        $overdueCAs = CashAdvance::where('status', '!=', 'CLOSED')
            ->where('status', '!=', 'REJECTED')
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::now())
            ->get();

        if ($overdueCAs->isNotEmpty()) {
            $finances = User::where('role', 'finance')->get();
            $direkturs = User::where('role', 'direktur')->get();

            foreach ($overdueCAs as $ca) {
                $key = 'overdue_' . $ca->id . '_' . $today->toDateString();
                foreach ($finances as $fin) {
                    self::notifyOnce($fin->id, $key . '_fin', 'Peringatan CA OVERDUE', "CA {$ca->ca_number} ({$ca->user->name}) telah melewati batas waktu (OVERDUE).", 'danger', route('cash-advances.show', $ca));
                }
                foreach ($direkturs as $dir) {
                    self::notifyOnce($dir->id, $key . '_dir', 'Laporan CA OVERDUE', "CA {$ca->ca_number} ({$ca->user->name}) berstatus OVERDUE.", 'danger', route('cash-advances.show', $ca));
                }
            }
        }

        // 3. Finance reminder: receipts pending verification > 2 business days
        $pendingExpenses = Expense::where('verification_status', 'PENDING')
            ->where('created_at', '<=', Carbon::now()->subDays(2))
            ->get();

        if ($pendingExpenses->isNotEmpty()) {
            $finances = User::where('role', 'finance')->get();
            $count = $pendingExpenses->count();
            $key = 'fin_pending_verify_' . $today->toDateString();

            foreach ($finances as $fin) {
                self::notifyOnce(
                    $fin->id,
                    $key,
                    'Struk Menunggu Verifikasi > 2 Hari',
                    "Terdapat {$count} struk pengeluaran yang menunggu verifikasi lebih dari 2 hari.",
                    'warning',
                    route('cash-advances.index', ['status' => 'PENDING_VERIFICATION'])
                );
            }
        }
    }

    private static function notifyOnce(int $userId, string $key, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        // Avoid spamming identical notifications
        $exists = SystemNotification::where('user_id', $userId)
            ->where('title', $title)
            ->where('created_at', '>=', Carbon::now()->subHours(12))
            ->exists();

        if (!$exists) {
            SystemNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
                'is_read' => false,
                'created_at' => now(),
            ]);
        }
    }
}
