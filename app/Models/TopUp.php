<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopUp extends Model
{
    use HasFactory;

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
}
