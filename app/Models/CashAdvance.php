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

    public function expenses()
    {
        return $this->hasMany(Expense::class)->orderByDesc('transaction_date');
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
        $initial = (float) ($this->approved_amount ?? $this->requested_amount ?? 0);
        $topups = $this->disbursed_topups_total;
        $expenses = $this->recorded_expenses_total;

        return max(0, $initial + $topups - $expenses);
    }

    // Hasil settlement = Saldo - Talangan disetujui (Dokumen hal 6)
    public function getSettlementResultAttribute(): float
    {
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
