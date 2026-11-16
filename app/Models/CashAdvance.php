<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CashAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'ca_number',
        'user_id',
        'project_name',
        'project_code',
        'purpose',
        'requested_amount',
        'approved_amount',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'disbursed_by',
        'disbursed_at',
        'disbursement_ref',
        'disbursement_notes',
        'due_date',
        'is_unlocked',
        'unlocked_by',
        'unlocked_at',
        'unlock_reason',
        'project_completed_at',
        'settlement_refund_amount',
        'settlement_refund_proof',
        'settlement_transferred_at',
        'settlement_notes',
        'closed_by',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'settlement_refund_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'disbursed_at' => 'datetime',
            'due_date' => 'datetime',
            'unlocked_at' => 'datetime',
            'project_completed_at' => 'datetime',
            'settlement_transferred_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_unlocked' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function disburser()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function unlocker()
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    public function topUps()
    {
        return $this->hasMany(TopUp::class);
    }

    public function items()
    {
        return $this->hasMany(CashAdvanceItem::class);
    }

    public function distributions()
    {
        return $this->hasMany(CashAdvanceDistribution::class)->orderByDesc('distributed_at');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class)->orderByDesc('transaction_date');
    }

    /**
     * Total dana yang sudah didistribusikan Atasan ke Karyawan.
     */
    public function getDistributedTotalAttribute(): float
    {
        return (float) $this->distributions()->sum('amount');
    }

    /**
     * Sisa dana di kas Atasan yang belum dibagikan ke anggota tim proyek.
     */
    public function getUndistributedAmountAttribute(): float
    {
        $base = (float) ($this->approved_amount ?? $this->requested_amount ?? 0);
        return max(0, $base - $this->distributed_total);
    }

    /**
     * Total dana yang diterima oleh user tertentu pada CA proyek ini.
     */
    public function userDistributionTotal(int $userId): float
    {
        return (float) $this->distributions()->where('receiver_id', $userId)->sum('amount');
    }

    /**
     * Total pengeluaran normal yang dicatat oleh user tertentu pada CA proyek ini.
     */
    public function userExpensesTotal(int $userId): float
    {
        return (float) $this->expenses()
            ->where('user_id', $userId)
            ->where('is_talangan', false)
            ->whereNotIn('verification_status', ['REJECTED', 'VOID'])
            ->sum('amount');
    }

    /**
     * Sisa saldo kas yang dipegang oleh user tertentu.
     */
    public function userCurrentBalance(int $userId): float
    {
        if ($this->status === 'CLOSED') {
            return 0.0;
        }

        // Jika user adalah PIC CA / Atasan:
        if ($this->user_id === $userId) {
            // Saldo Atasan = Saldo CA - Saldo yang sudah dibagikan ke karyawan
            return max(0, $this->current_balance - $this->distributed_total);
        }

        // Untuk anggota tim / karyawan:
        $received = $this->userDistributionTotal($userId);
        $spent = $this->userExpensesTotal($userId);
        return max(0, $received - $spent);
    }

    // Hitungan Saldo Otomatis (Prinsip 3: Saldo dihitung sistem, tidak pernah diketik manusia)
    // Rumus: Saldo = CA awal + Top-up cair - Pengeluaran tercatat
    public function getDisbursedTopupsTotalAttribute(): float
    {
        return (float) $this->topUps()->where('status', 'DISBURSED')->sum('amount');
    }

    public function getRecordedExpensesTotalAttribute(): float
    {
        // Pengeluaran normal tercatat (tidak termasuk talangan, dan tidak termasuk yang Ditolak/Void)
        return (float) $this->expenses()
            ->where('is_talangan', false)
            ->whereNotIn('verification_status', ['REJECTED', 'VOID'])
            ->sum('amount');
    }

    public function getValidExpensesTotalAttribute(): float
    {
        return (float) $this->expenses()
            ->where('is_talangan', false)
            ->where('verification_status', 'VALID')
            ->sum('amount');
    }

    public function getTalanganTotalAttribute(): float
    {
        return (float) $this->expenses()
            ->where('is_talangan', true)
            ->whereNotIn('verification_status', ['REJECTED', 'VOID'])
            ->sum('amount');
    }

    public function getApprovedTalanganTotalAttribute(): float
    {
        return (float) $this->expenses()
            ->where('is_talangan', true)
            ->where('verification_status', 'VALID')
            ->sum('amount');
    }

    public function getCurrentBalanceAttribute(): float
    {
        // Jika CA sudah resmi ditutup (CLOSED) oleh Finance, sisa saldo kas dipegang karyawan adalah 0 (Lunas)
        if ($this->status === 'CLOSED') {
            return 0.0;
        }

        $initial = (float) ($this->approved_amount ?? $this->requested_amount ?? 0);
        $topups = $this->disbursed_topups_total;
        $expenses = $this->recorded_expenses_total;

        $balance = max(0, $initial + $topups - $expenses);

        // Jika ada sisa saldo tapi sudah dikembalikan dan dikonfirmasi oleh finance (closed)
        if ($this->settlement_refund_amount > 0 && ($this->status === 'CLOSED' || $this->closed_at !== null)) {
            return 0.0;
        }

        return $balance;
    }

    // Hasil settlement = Saldo - Talangan disetujui (Dokumen hal 6)
    public function getSettlementResultAttribute(): float
    {
        if ($this->status === 'CLOSED') {
            return 0.0;
        }

        return $this->current_balance - $this->approved_talangan_total;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'CLOSED' || $this->status === 'REJECTED') {
            return false;
        }

        return $this->due_date && Carbon::now()->greaterThan($this->due_date);
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->due_date) {
            return 0;
        }

        $diff = Carbon::now()->diffInDays($this->due_date, false);
        return (int) $diff;
    }

    public function getIsLowBalanceAttribute(): bool
    {
        if ($this->status !== 'ACTIVE') {
            return false;
        }

        $base = (float) ($this->approved_amount ?: $this->requested_amount);
        if ($base <= 0) return false;

        $ratio = $this->current_balance / $base;
        return $ratio < 0.20; // Peringatan saldo tersisa < 20%
    }
}
