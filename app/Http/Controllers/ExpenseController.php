<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\ExpenseAttachment;
use App\Models\ExpenseHistory;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function create(CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Hanya pemilik Cash Advance yang dapat mencatat pengeluaran.');
        }

        if ($cashAdvance->status !== 'ACTIVE') {
            return redirect()->route('cash-advances.show', $cashAdvance)
                ->with('error', 'Pengeluaran hanya dapat dicatat saat CA berstatus ACTIVE.');
        }

        return view('expenses.create', compact('cashAdvance'));
    }

    public function store(Request $request, CashAdvance $cashAdvance)
    {
        $user = Auth::user();

        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($cashAdvance->status !== 'ACTIVE') {
            return back()->with('error', 'Status CA tidak aktif.');
        }

        $minDate = $cashAdvance->disbursed_at ? $cashAdvance->disbursed_at->format('Y-m-d') : '2020-01-01';

        $rules = [
            'transaction_date' => ['required', 'date', 'before_or_equal:today', "after_or_equal:{$minDate}"],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:material,transport,sewa_alat,konsumsi,lainnya'],
            'amount' => ['required', 'numeric', 'min:1'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'vendor_tax_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'is_talangan' => ['nullable', 'boolean'],
            'talangan_emergency_reason' => ['nullable', 'string'],
            'is_lost_receipt' => ['nullable', 'boolean'],
            'lost_receipt_statement_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'receipts' => ['nullable', 'array', 'max:5'],
            'receipts.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];

        $messages = [
            'transaction_date.before_or_equal' => 'Tanggal transaksi tidak boleh di masa depan.',
            'transaction_date.after_or_equal' => 'Tanggal transaksi tidak boleh sebelum tanggal pencairan dana CA (' . Carbon::parse($minDate)->format('d/m/Y') . ').',
            'amount.min' => 'Nominal pengeluaran harus lebih dari Rp 0.',
            'category.in' => 'Kategori pengeluaran tidak valid.',
            'receipts.max' => 'Maksimal 5 foto struk per transaksi.',
        ];

        $validated = $request->validate($rules, $messages);

        $isTalangan = $request->boolean('is_talangan');
        $amount = (float) $validated['amount'];
        $currentBalance = $cashAdvance->current_balance;

        // Kunci input saldo (Dokumen hal 5):
        // Pengeluaran yang membuat saldo di bawah Rp0 ditolak: "Saldo tidak cukup. Ajukan top-up."
        if (!$isTalangan) {
            if ($amount > $currentBalance) {
                return back()->withInput()->withErrors([
                    'amount' => "Saldo tidak cukup! Sisa saldo saat ini: Rp " . number_format($currentBalance, 0, ',', '.') . ", sedangkan pengeluaran diajukan: Rp " . number_format($amount, 0, ',', '.') . ". Silakan ajukan Top-Up terlebih dahulu.",
                ]);
            }
        } else {
            // Talangan wajib menyertakan alasan darurat
            if (empty($validated['talangan_emergency_reason'])) {
                return back()->withInput()->withErrors([
                    'talangan_emergency_reason' => 'Alasan kondisi darurat wajib diisi untuk transaksi Talangan pribadi.',
                ]);
            }
        }

        $isLostReceipt = $request->boolean('is_lost_receipt');
        $lostReceiptPath = null;

        if ($isLostReceipt) {
            if (!$request->hasFile('lost_receipt_statement_file')) {
                return back()->withInput()->withErrors([
                    'lost_receipt_statement_file' => 'Surat pernyataan bermaterai / tanda tangan wajib diunggah bila tidak ada struk fisik.',
                ]);
            }
            $lostReceiptPath = $request->file('lost_receipt_statement_file')->store('lost_receipts', 'public');
        } else {
            if (!$request->hasFile('receipts') || count($request->file('receipts')) === 0) {
                return back()->withInput()->withErrors([
                    'receipts' => 'Minimal satu foto/file struk atau nota wajib diunggah.',
                ]);
            }
        }

        // Perekaman dibatasi maksimal 1 hari setelah transaksi. Lewat itu ditandai TERLAMBAT di dashboard (Dokumen hal 7)
        $txDate = Carbon::parse($validated['transaction_date'])->startOfDay();
        $daysDiff = $txDate->diffInDays(Carbon::now()->startOfDay(), false);
        $isLate = ($daysDiff > 1);

        // Peringatan Duplikat (Dokumen hal 7)
        $potentialDuplicate = Expense::where('cash_advance_id', $cashAdvance->id)
            ->where('transaction_date', $validated['transaction_date'])
            ->where('amount', $amount)
            ->where('vendor_name', $validated['vendor_name'])
            ->whereNotIn('verification_status', ['REJECTED', 'VOID'])
            ->exists();

        $expense = Expense::create([
            'cash_advance_id' => $cashAdvance->id,
            'user_id' => $user->id,
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'amount' => $amount,
            'vendor_name' => $validated['vendor_name'],
            'vendor_tax_number' => $validated['vendor_tax_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_talangan' => $isTalangan,
            'talangan_emergency_reason' => $isTalangan ? $validated['talangan_emergency_reason'] : null,
            'is_lost_receipt' => $isLostReceipt,
            'lost_receipt_statement_file' => $lostReceiptPath,
            'is_late' => $isLate,
            'verification_status' => 'PENDING',
        ]);

        // Upload and process receipt files
        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $hash = hash_file('sha256', $file->getRealPath());
                $path = $file->store('receipts', 'public');

                ExpenseAttachment::create([
                    'expense_id' => $expense->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'file_hash' => $hash,
                ]);
            }
        }

        AuditLog::record('EXPENSE_CREATED', $expense, "Pengeluaran Rp " . number_format($expense->amount, 0, ',', '.') . " ({$expense->description}) dicatat pada CA {$cashAdvance->ca_number}." . ($isLate ? ' [TERLAMBAT]' : ''), null, $expense->toArray());

        // Check if remaining balance triggers < 20% warning
        $cashAdvance->refresh();
        if ($cashAdvance->is_low_balance) {
            SystemNotification::send(
                $user->id,
                'Peringatan Saldo Menipis (< 20%)',
                "Sisa saldo CA {$cashAdvance->ca_number} tersisa Rp " . number_format($cashAdvance->current_balance, 0, ',', '.') . " (< 20%). Segera ajukan Top-Up jika ada pengeluaran berikutnya.",
                'warning',
                route('cash-advances.show', $cashAdvance)
            );
        }

        $msg = "Pengeluaran Rp " . number_format($expense->amount, 0, ',', '.') . " berhasil dicatat.";
        if ($isLate) {
            $msg .= " (Catatan: Transaksi ditandai TERLAMBAT karena dicatat lebih dari 1 hari setelah kejadian).";
        }
        if ($potentialDuplicate) {
            $msg .= " PERHATIAN: Sistem mendeteksi transaksi serupa (nominal, vendor, tanggal sama).";
        }

        return redirect()->route('cash-advances.show', $cashAdvance)->with('success', $msg);
    }

    public function edit(Expense $expense)
    {
        $user = Auth::user();

        if ($expense->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        // Karyawan hanya bisa mengubah transaksi sebelum diverifikasi (Dokumen hal 7)
        if (in_array($expense->verification_status, ['VALID', 'VOID'])) {
            return redirect()->route('cash-advances.show', $expense->cash_advance_id)
                ->with('error', 'Transaksi yang sudah VALID atau VOID tidak dapat diubah lagi.');
        }

        $cashAdvance = $expense->cashAdvance;

        return view('expenses.edit', compact('expense', 'cashAdvance'));
    }

    public function update(Request $request, Expense $expense)
    {
        $user = Auth::user();

        if ($expense->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if (in_array($expense->verification_status, ['VALID', 'VOID'])) {
            return back()->with('error', 'Transaksi sudah tidak dapat diubah.');
        }

        $cashAdvance = $expense->cashAdvance;
        $minDate = $cashAdvance->disbursed_at ? $cashAdvance->disbursed_at->format('Y-m-d') : '2020-01-01';

        $validated = $request->validate([
            'transaction_date' => ['required', 'date', 'before_or_equal:today', "after_or_equal:{$minDate}"],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:material,transport,sewa_alat,konsumsi,lainnya'],
            'amount' => ['required', 'numeric', 'min:1'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'vendor_tax_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'receipts' => ['nullable', 'array', 'max:5'],
            'receipts.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $oldValues = $expense->toArray();

        // Save history of changes (Dokumen hal 7: "riwayat perubahan tersimpan")
        ExpenseHistory::create([
            'expense_id' => $expense->id,
            'user_id' => $user->id,
            'changes' => [
                'old' => $oldValues,
                'new' => $validated,
            ],
            'created_at' => now(),
        ]);

        $expense->update([
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'vendor_name' => $validated['vendor_name'],
            'vendor_tax_number' => $validated['vendor_tax_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'verification_status' => 'PENDING', // Reset to PENDING if was NEEDS_REVISION
        ]);

        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $hash = hash_file('sha256', $file->getRealPath());
                $path = $file->store('receipts', 'public');

                ExpenseAttachment::create([
                    'expense_id' => $expense->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'file_hash' => $hash,
                ]);
            }
        }

        AuditLog::record('EXPENSE_UPDATED', $expense, "Transaksi #{$expense->id} diperbarui oleh {$user->name}.", $oldValues, $expense->toArray());

        return redirect()->route('cash-advances.show', $cashAdvance)
            ->with('success', 'Transaksi pengeluaran berhasil diperbarui dan diajukan ulang untuk verifikasi.');
    }

    public function voidExpense(Request $request, Expense $expense)
    {
        $user = Auth::user();

        // Koreksi lewat void oleh Finance dengan alasan (Dokumen hal 1, 7)
        if (!$user->isFinance() && !$user->isDirektur()) {
            abort(403, 'Hanya Finance atau Direktur yang berhak melakukan VOID transaksi.');
        }

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'min:5'],
        ], [
            'void_reason.required' => 'Alasan VOID wajib diisi.',
        ]);

        $oldValues = $expense->toArray();

        $expense->update([
            'verification_status' => 'VOID',
            'void_by' => $user->id,
            'void_at' => now(),
            'void_reason' => $validated['void_reason'],
        ]);

        AuditLog::record('EXPENSE_VOIDED', $expense, "Transaksi #{$expense->id} di-VOID oleh {$user->name}. Alasan: {$validated['void_reason']}", $oldValues, $expense->toArray());

        SystemNotification::send(
            $expense->user_id,
            'Transaksi Telah Di-VOID',
            "Transaksi '{$expense->description}' sebesar Rp " . number_format($expense->amount, 0, ',', '.') . " di-VOID oleh Finance. Alasan: {$validated['void_reason']}",
            'danger',
            route('cash-advances.show', $expense->cash_advance_id)
        );

        return back()->with('success', "Transaksi berhasil di-VOID. Saldo sistem telah dikembalikan.");
    }

    public function approveLostReceipt(Request $request, Expense $expense)
    {
        $user = Auth::user();

        // Struk hilang: persetujuan atasan (Dokumen hal 7)
        if (!$user->isAtasan() && !$user->isDirektur()) {
            abort(403, 'Hanya Atasan atau Direktur yang dapat menyetujui surat pernyataan kehilangan struk.');
        }

        $expense->update([
            'lost_receipt_approved_by' => $user->id,
            'lost_receipt_approved_at' => now(),
        ]);

        AuditLog::record('LOST_RECEIPT_APPROVED', $expense, "Surat pernyataan kehilangan struk transaksi #{$expense->id} disetujui oleh {$user->name}.");

        return back()->with('success', 'Surat pernyataan kehilangan struk telah disetujui atasan. Transaksi siap dinilai oleh Finance.');
    }
}
