<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cash_advance_id',
        'user_id',
        'amount',
        'reason',
        'status',
        'requires_direktur_approval',
        'approved_by_atasan',
        'atasan_approved_at',
        'approved_by_finance',
        'finance_approved_at',
        'approved_by_direktur',
        'direktur_approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'disbursed_by',
        'disbursed_at',
        'disbursement_ref',
        'disbursement_notes',
        'deleted_by',
        'delete_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requires_direktur_approval' => 'boolean',
            'atasan_approved_at' => 'datetime',
            'finance_approved_at' => 'datetime',
            'direktur_approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'disbursed_at' => 'datetime',
        ];
    }

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approverAtasan()
    {
        return $this->belongsTo(User::class, 'approved_by_atasan');
    }

    public function approverFinance()
    {
        return $this->belongsTo(User::class, 'approved_by_finance');
    }

    public function approverDirektur()
    {
        return $this->belongsTo(User::class, 'approved_by_direktur');
    }

    public function disburser()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Menentukan apakah pengguna berhak menghapus (soft delete) pengajuan Top-Up ini dari frontend.
     */
    public function canBeSoftDeletedBy(User $user): bool
    {
        $ca = $this->cashAdvance;
        if (!$ca || $ca->status === 'CLOSED') {
            return false;
        }

        if ($user->isFinance() || $user->isDirektur() || $user->isSuperAdmin()) {
            return $this->status !== 'DISBURSED';
        }

        if ($this->user_id === $user->id) {
            return $ca->status === 'ACTIVE'
                && in_array($this->status, ['PENDING_ATASAN', 'REJECTED'], true);
        }

        return false;
    }
}
