<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cash_advance_id',
        'cash_advance_item_id',
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
        'deleted_by',
        'delete_reason',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'integer',
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

    public function item()
    {
        return $this->belongsTo(CashAdvanceItem::class, 'cash_advance_item_id');
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

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Menentukan apakah pengguna berhak menghapus (soft delete) transaksi pengeluaran ini dari frontend.
     */
    public function canBeSoftDeletedBy(User $user): bool
    {
        $ca = $this->cashAdvance;
        if (!$ca || $ca->status === 'CLOSED') {
            return false;
        }

        if ($user->isFinance() || $user->isDirektur() || $user->isSuperAdmin()) {
            return $this->verification_status !== 'VALID';
        }

        if ($this->user_id === $user->id) {
            return $ca->status === 'ACTIVE'
                && !in_array($this->verification_status, ['VALID', 'VOID'], true);
        }

        return false;
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

    public function getLostReceiptStatementUrlAttribute(): ?string
    {
        if (!$this->lost_receipt_statement_file) {
            return null;
        }
        $path = ltrim($this->lost_receipt_statement_file, '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        return asset('storage/' . $path);
    }
}
