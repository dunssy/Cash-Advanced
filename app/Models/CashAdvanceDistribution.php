<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashAdvanceDistribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cash_advance_id',
        'giver_id',
        'receiver_id',
        'amount',
        'distributed_at',
        'transfer_proof',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'distributed_at' => 'datetime',
        ];
    }

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function giver()
    {
        return $this->belongsTo(User::class, 'giver_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getTransferProofUrlAttribute(): ?string
    {
        if (empty($this->transfer_proof)) {
            return null;
        }

        return asset('storage/' . ltrim($this->transfer_proof, '/'));
    }
}
