<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_advance_id',
        'user_id',
        'transaction_date',
        'description',
        'category',
        'amount',
        'vendor_name',
        'vendor_tax_number',
        'notes',
        'is_talangan',
        'talangan_emergency_reason',
        'is_lost_receipt',
        'lost_receipt_statement_file',
        'lost_receipt_approved_by',
        'lost_receipt_approved_at',
        'is_late',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'void_by',
        'void_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'is_talangan' => 'boolean',
            'is_lost_receipt' => 'boolean',
            'is_late' => 'boolean',
            'lost_receipt_approved_at' => 'datetime',
            'verified_at' => 'datetime',
            'void_at' => 'datetime',
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

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function voider()
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    public function lostReceiptApprover()
    {
        return $this->belongsTo(User::class, 'lost_receipt_approved_by');
    }

    public function attachments()
    {
        return $this->hasMany(ExpenseAttachment::class);
    }

    public function histories()
    {
        return $this->hasMany(ExpenseHistory::class)->orderByDesc('created_at');
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
}
