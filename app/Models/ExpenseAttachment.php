<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'file_hash',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function getUrlAttribute(): string
    {
        $path = ltrim($this->file_path, '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        return asset('storage/' . $path);
    }
}
