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
        'avatar',
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

    public function cashAdvanceItems()
    {
        return $this->hasMany(CashAdvanceItem::class);
    }

    public function receivedDistributions()
    {
        return $this->hasMany(CashAdvanceDistribution::class, 'receiver_id');
    }

    public function notifications()
    {
        return $this->hasMany(SystemNotification::class)->orderByDesc('created_at');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(SystemNotification::class)->where('is_read', false)->orderByDesc('created_at');
    }

    /**
     * Mendapatkan URL gambar avatar/foto profil pengguna.
     * Mengembalikan URL file lokal atau URL eksternal (Unsplash), atau null jika belum diatur.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        return asset('storage/' . ltrim($this->avatar, '/'));
    }

    /**
     * Mendapatkan 2 huruf inisial nama pengguna sebagai avatar fallback.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Accessor untuk nilai string departemen (backward compatibility).
     * Jika tersimpan sebagai array JSON, mengembalikan daftar nama divisi dipisah koma.
     */
    public function getDepartmentAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_string($value) && str_starts_with(trim($value), '[') && str_ends_with(trim($value), ']')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return implode(', ', $decoded);
            }
        }

        return $value;
    }

    /**
     * Mutator untuk kolom department.
     * Menerima array tag divisi (maksimal 10), string JSON, atau string koma,
     * dan menyimpannya secara terstandarisasi sebagai JSON array di basis data.
     */
    public function setDepartmentAttribute($value): void
    {
        if (is_array($value)) {
            $tags = array_slice(array_values(array_filter(array_map('trim', $value))), 0, 10);
            $this->attributes['department'] = !empty($tags) ? json_encode($tags, JSON_UNESCAPED_UNICODE) : null;
            return;
        }

        if (is_string($value) && str_starts_with(trim($value), '[') && str_ends_with(trim($value), ']')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $tags = array_slice(array_values(array_filter(array_map('trim', $decoded))), 0, 10);
                $this->attributes['department'] = !empty($tags) ? json_encode($tags, JSON_UNESCAPED_UNICODE) : null;
                return;
            }
        }

        if (is_string($value) && !empty(trim($value))) {
            $parts = preg_split('/[,;]+/', $value);
            $tags = array_slice(array_values(array_filter(array_map('trim', $parts))), 0, 10);
            $this->attributes['department'] = !empty($tags) ? json_encode($tags, JSON_UNESCAPED_UNICODE) : null;
            return;
        }

        $this->attributes['department'] = null;
    }

    /**
     * Mendapatkan daftar tag divisi dalam bentuk array murni (maksimal 10 divisi).
     *
     * @return array<int, string>
     */
    public function getDepartmentsAttribute(): array
    {
        $raw = $this->attributes['department'] ?? null;
        if (empty($raw)) {
            return [];
        }

        if (str_starts_with(trim($raw), '[') && str_ends_with(trim($raw), ']')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_slice(array_values(array_filter(array_map('trim', $decoded))), 0, 10);
            }
        }

        $parts = preg_split('/[,;]+/', $raw);
        return array_slice(array_values(array_filter(array_map('trim', $parts))), 0, 10);
    }

    /**
     * Mendapatkan tag divisi dengan format nama tag (contoh: "#Operasional").
     *
     * @return array<int, string>
     */
    public function getDepartmentTagsAttribute(): array
    {
        return array_map(fn($d) => '#' . $d, $this->departments);
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

    // Who can record disbursement (Finance & Direktur)
    public function canDisburseCA(CashAdvance $ca): bool
    {
        // Pengguna tidak bisa mencairkan CA miliknya sendiri
        if ($this->id === $ca->user_id) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return false;
        }

        return $this->isFinance() || $this->isDirektur();
    }
}
