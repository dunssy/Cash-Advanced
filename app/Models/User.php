<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'department',
        'phone',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function cashAdvances()
    {
        return $this->hasMany(CashAdvance::class);
    }

    public function notifications()
    {
        return $this->hasMany(SystemNotification::class)->orderByDesc('created_at');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(SystemNotification::class)->where('is_read', false)->orderByDesc('created_at');
    }

    // Role check helpers
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDirektur(): bool
    {
        return $this->role === 'direktur';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    public function isAtasan(): bool
    {
        return $this->role === 'atasan';
    }

    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    // Can this user submit a CA? All roles EXCEPT Super Admin can submit CAs ("Setiap pengguna otomatis juga berperan sebagai Karyawan untuk CA miliknya sendiri. Super Admin: Tidak boleh")
    public function canSubmitCashAdvance(): bool
    {
        return !$this->isSuperAdmin();
    }

    // Check if user has an active CA that locks new CA creation
    public function hasActiveLock(): bool
    {
        return $this->cashAdvances()
            ->whereNotIn('status', ['CLOSED', 'REJECTED'])
            ->where('is_unlocked', false)
            ->exists();
    }

    // Get the currently locking CA
    public function getActiveLockingCA()
    {
        return $this->cashAdvances()
            ->whereNotIn('status', ['CLOSED', 'REJECTED'])
            ->where('is_unlocked', false)
            ->first();
    }

    // Pemisahan tugas: Who can approve CA
    public function canApproveCA(CashAdvance $ca): bool
    {
        // Pengguna tidak bisa menyetujui CA miliknya sendiri, apa pun rolenya
        if ($this->id === $ca->user_id) {
            return false;
        }

        // Super Admin tidak boleh menyetujui
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Bila pemohon adalah Atasan atau Finance, approval naik ke Direktur
        if ($ca->user->isAtasan() || $ca->user->isFinance()) {
            return $this->isDirektur();
        }

        // Pemohon adalah Karyawan biasa: Atasan langsung (atau Direktur)
        if ($this->isDirektur()) {
            return true;
        }

        return $this->isAtasan() && $ca->user->manager_id === $this->id;
    }

    // Who can verify receipts & close CA
    public function canVerifyAndCloseCA(CashAdvance $ca): bool
    {
        // Pengguna tidak bisa memverifikasi CA miliknya sendiri
        if ($this->id === $ca->user_id) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return false;
        }

        // Bila pemohon adalah Finance, verifikasi struk dan tutup CA dilakukan Direktur
        if ($ca->user->isFinance()) {
            return $this->isDirektur();
        }

        // Untuk pemohon selain Finance: dilakukan Finance atau Direktur
        return $this->isFinance() || $this->isDirektur();
    }

    // Who can record disbursement (Finance only, unless applicant is Finance, but even then Finance role handles bank disbursement)
    public function canDisburseCA(CashAdvance $ca): bool
    {
        if ($this->isSuperAdmin()) {
            return false;
        }

        return $this->isFinance();
    }
}
