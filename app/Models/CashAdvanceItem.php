<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashAdvanceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cash_advance_id',
        'user_id',
        'category',
        'item_name',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
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

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'cash_advance_item_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'material' => 'Material & Perlengkapan',
            'transport' => 'Transportasi & Bensin',
            'sewa_alat' => 'Sewa Alat & Kendaraan',
            'konsumsi' => 'Konsumsi & Makan',
            'lainnya' => 'Lain-lain',
            default => ucfirst($this->category),
        };
    }

    public function getRealizedAmountAttribute(): float
    {
        return (float) $this->expenses()
            ->whereNotIn('verification_status', ['REJECTED', 'VOID'])
            ->sum('amount');
    }
}
