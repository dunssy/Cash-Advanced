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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Class ExpenseController
 *
 * Mengelola pencatatan transaksi pengeluaran riil, pengunggahan struk digital,
 * penanganan kasus struk hilang (lost receipt), pengeluaran talangan pribadi,
 * deteksi duplikasi, hingga pembatalan (VOID) transaksi pada sistem Cash Advance PT Technodrive.
 *
 * Ketentuan Bisnis Kritis:
 * 1. Kunci Input Saldo: Nominal pengeluaran dilarang melebihi sisa saldo kas aktif (kecuali Talangan).
 * 2. SLA Pencatatan: Transaksi wajib dicatat maksimal H+1 dari tanggal kejadian; jika terlambat,
 *    sistem otomatis menandai flag 'is_late' sebagai bahan evaluasi kedisiplinan karyawan.
 * 3. Integritas Struk: Setiap bukti transaksi dihitung hash SHA-256 miliknya untuk menjamin integritas file.
 * 4. Struk Hilang: Wajib mengunggah surat pernyataan bermaterai dan memperoleh persetujuan Atasan.
 * 5. Audit Trail & Immutability: Transaksi yang sudah diverifikasi VALID tidak boleh diedit; koreksi
 *    hanya dapat dilakukan oleh tim Finance melalui mekanisme VOID dengan alasan tercatat.
 *
 * @package App\Http\Controllers
 */
class ExpenseController extends Controller
{
    /**
     * Menampilkan formulir pencatatan transaksi pengeluaran baru.
     *
     * Alur kerja:
     * 1. Memeriksa kepemilikan CA (hanya pemegang dana yang berhak mencatat pengeluaran).
     * 2. Memastikan status CA adalah ACTIVE.
     * 3. Menampilkan view form pencatatan pengeluaran beserta info sisa saldo berjalan.
     *
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance yang sedang aktif.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(CashAdvance $cashAdvance): View|RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi kepemilikan
        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Akses ditolak: Hanya pemilik Cash Advance yang dapat mencatat pengeluaran.');
        }

        // 2. Validasi status CA
        if ($cashAdvance->status !== 'ACTIVE') {
            return redirect()->route('cash-advances.show', $cashAdvance)
                ->with('error', 'Pengeluaran hanya dapat dicatat saat status Cash Advance adalah ACTIVE.');
        }

        // 3. Tampilkan antarmuka pencatatan pengeluaran
        return view('expenses.create', compact('cashAdvance'));
    }

    /**
     * Menyimpan transaksi pengeluaran baru ke dalam basis data.
     *
     * Alur kerja komprehensif:
     * 1. Memvalidasi batasan tanggal (tidak boleh di masa depan & tidak boleh sebelum pencairan dana CA).
     * 2. Menjalankan Aturan Kunci Saldo: Pengeluaran normal tidak boleh melebihi sisa saldo aktif.
     * 3. Menjalankan Aturan Talangan: Jika saldo habis namun mendesak, wajib mencantumkan alasan darurat.
     * 4. Menjalankan Aturan Struk Hilang: Wajib mengunggah surat pernyataan bermaterai/tanda tangan.
     * 5. Menjalankan Aturan Keterlambatan: Pencatatan > 1 hari dari tanggal transaksi ditandai TERLAMBAT.
     * 6. Menjalankan Deteksi Duplikasi: Memeriksa apakah ada transaksi serupa (nominal, tanggal, vendor).
     * 7. Menyimpan entitas Expense dengan status verifikasi awal 'PENDING'.
     * 8. Menyimpan lampiran file bukti struk dengan kalkulasi hash SHA-256.
     * 9. Mencatat aktivitas ke Audit Log.
     * 10. Memeriksa ambang batas peringatan saldo menipis (< 20%) dan menerbitkan notifikasi jika terpicu.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat data pengeluaran dan lampiran.
     * @param  \App\Models\CashAdvance  $cashAdvance Model Cash Advance terkait.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, CashAdvance $cashAdvance): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi kepemilikan
        if ($cashAdvance->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Validasi status keaktifan CA
        if ($cashAdvance->status !== 'ACTIVE') {
            return back()->with('error', 'Status Cash Advance tidak aktif.');
        }

        // Batas minimal tanggal transaksi: Tidak boleh mendahului tanggal pencairan dana CA
        $minDate = $cashAdvance->disbursed_at ? $cashAdvance->disbursed_at->format('Y-m-d') : '2020-01-01';

        // 3. Aturan validasi formulir pengeluaran
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

        // 4. KUNCI INPUT SALDO (SOP Dokumen hal 5)
        // Pengeluaran yang membuat saldo di bawah Rp 0 dilarang sistem kecuali dinyatakan sebagai Talangan
        if (!$isTalangan) {
            if ($amount > $currentBalance) {
                return back()->withInput()->withErrors([
                    'amount' => "Saldo tidak cukup! Sisa saldo saat ini: Rp " . number_format($currentBalance, 0, ',', '.') . ", sedangkan pengeluaran diajukan: Rp " . number_format($amount, 0, ',', '.') . ". Silakan ajukan Top-Up terlebih dahulu.",
                ]);
            }
        } else {
            // Transaksi Talangan pribadi wajib melampirkan alasan kondisi darurat
            if (empty($validated['talangan_emergency_reason'])) {
                return back()->withInput()->withErrors([
                    'talangan_emergency_reason' => 'Alasan kondisi darurat wajib diisi untuk transaksi Talangan pribadi.',
                ]);
            }
        }

        // 5. Penanganan bukti struk fisik vs surat pernyataan kehilangan struk
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

        // 6. SLA Perekaman Real-Time: Dibatasi maksimal 1 hari setelah transaksi (Dokumen hal 7)
        $txDate = Carbon::parse($validated['transaction_date'])->startOfDay();
        $daysDiff = $txDate->diffInDays(Carbon::now()->startOfDay(), false);
        $isLate = ($daysDiff > 1);

        // 7. Deteksi Dini Transaksi Duplikat (Peringatan Sistem Dokumen hal 7)
        $potentialDuplicate = Expense::where('cash_advance_id', $cashAdvance->id)
            ->where('transaction_date', $validated['transaction_date'])
            ->where('amount', $amount)
            ->where('vendor_name', $validated['vendor_name'])
            ->whereNotIn('verification_status', ['REJECTED', 'VOID'])
            ->exists();

        // 8. Buat record transaksi pengeluaran baru
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

        // 9. Simpan file lampiran struk beserta hash SHA-256 untuk audit keamanan berkas
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

        // 10. Catat rekaman transaksi ke dalam Audit Log
        AuditLog::record(
            'EXPENSE_CREATED',
            $expense,
            "Pengeluaran Rp " . number_format($expense->amount, 0, ',', '.') . " ({$expense->description}) dicatat pada CA {$cashAdvance->ca_number}." . ($isLate ? ' [TERLAMBAT]' : ''),
            null,
            $expense->toArray()
        );

        // 11. Cek ambang batas peringatan saldo menipis (< 20%)
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

        // 12. Susun pesan umpan balik interaktif untuk antarmuka pengguna
        $msg = "Pengeluaran Rp " . number_format($expense->amount, 0, ',', '.') . " berhasil dicatat.";
        if ($isLate) {
            $msg .= " (Catatan: Transaksi ditandai TERLAMBAT karena dicatat lebih dari 1 hari setelah kejadian).";
        }
        if ($potentialDuplicate) {
            $msg .= " PERHATIAN: Sistem mendeteksi transaksi serupa (nominal, vendor, tanggal sama).";
        }

        return redirect()->route('cash-advances.show', $cashAdvance)->with('success', $msg);
    }

    /**
     * Menampilkan formulir edit transaksi pengeluaran.
     *
     * Karyawan hanya diperbolehkan merevisi transaksi selama status verifikasinya
     * belum 'VALID' dan belum 'VOID' (misal berstatus PENDING atau NEEDS_REVISION).
     *
     * @param  \App\Models\Expense  $expense Model transaksi pengeluaran yang ingin diedit.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Expense $expense): View|RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi kepemilikan transaksi
        if ($expense->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Proteksi Immutability: Transaksi VALID atau VOID dilarang diubah oleh karyawan (Dokumen hal 7)
        if (in_array($expense->verification_status, ['VALID', 'VOID'])) {
            return redirect()->route('cash-advances.show', $expense->cash_advance_id)
                ->with('error', 'Transaksi yang sudah VALID atau VOID tidak dapat diubah lagi.');
        }

        $cashAdvance = $expense->cashAdvance;

        return view('expenses.edit', compact('expense', 'cashAdvance'));
    }

    /**
     * Memperbarui data transaksi pengeluaran yang sudah dicatat sebelumnya.
     *
     * Alur kerja:
     * 1. Memeriksa otorisasi pemilik dan status belum terkunci (bukan VALID/VOID).
     * 2. Memvalidasi data input pembaruan.
     * 3. Menyimpan snapshot perubahan lama vs baru ke dalam ExpenseHistory (immutable audit trail).
     * 4. Memperbarui record Expense dan mereset status verifikasi menjadi 'PENDING' untuk audit ulang.
     * 5. Mengunggah lampiran struk tambahan jika dilampirkan.
     * 6. Merekam ke Audit Log.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat revisi data transaksi.
     * @param  \App\Models\Expense  $expense Model transaksi pengeluaran yang direvisi.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi pemilik
        if ($expense->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Cegah modifikasi transaksi yang sudah tervalidasi
        if (in_array($expense->verification_status, ['VALID', 'VOID'])) {
            return back()->with('error', 'Transaksi sudah tidak dapat diubah.');
        }

        $cashAdvance = $expense->cashAdvance;
        $minDate = $cashAdvance->disbursed_at ? $cashAdvance->disbursed_at->format('Y-m-d') : '2020-01-01';

        // 3. Validasi masukan pembaruan
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

        // 4. Rekam jejak perubahan ke tabel ExpenseHistory (SOP Dokumen hal 7: "riwayat perubahan tersimpan")
        ExpenseHistory::create([
            'expense_id' => $expense->id,
            'user_id' => $user->id,
            'changes' => [
                'old' => $oldValues,
                'new' => $validated,
            ],
            'created_at' => now(),
        ]);

        // 5. Perbarui data transaksi dan kembalikan status ke PENDING untuk verifikasi ulang tim Finance
        $expense->update([
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'vendor_name' => $validated['vendor_name'],
            'vendor_tax_number' => $validated['vendor_tax_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'verification_status' => 'PENDING', // Reset ke PENDING jika sebelumnya NEEDS_REVISION
        ]);

        // 6. Unggah struk baru jika ada penambahan bukti berkas
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

        // 7. Catat pembaruan ke Audit Log
        AuditLog::record(
            'EXPENSE_UPDATED',
            $expense,
            "Transaksi #{$expense->id} diperbarui oleh {$user->name}.",
            $oldValues,
            $expense->toArray()
        );

        return redirect()->route('cash-advances.show', $cashAdvance)
            ->with('success', 'Transaksi pengeluaran berhasil diperbarui dan diajukan ulang untuk verifikasi.');
    }

    /**
     * Membatalkan transaksi pengeluaran melalui mekanisme VOID oleh Finance/Direktur.
     *
     * Mekanisme VOID digunakan jika terjadi kesalahan fatal pada transaksi yang telah tercatat.
     * Sesuai ketentuan SOP, koreksi hanya boleh dilakukan via VOID disertai alasan jelas.
     * Dana yang di-VOID akan secara otomatis dipulihkan kembali ke saldo kas aktif sistem.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request yang memuat alasan pembatalan (void_reason).
     * @param  \App\Models\Expense  $expense Model transaksi pengeluaran yang akan di-VOID.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voidExpense(Request $request, Expense $expense): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi peran perbendaharaan: Hanya Finance atau Direktur yang berhak melakukan VOID
        if (!$user->isFinance() && !$user->isDirektur()) {
            abort(403, 'Akses ditolak: Hanya Finance atau Direktur yang berhak melakukan VOID transaksi.');
        }

        // 2. Validasi kewajiban penyertaan alasan VOID
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'min:5'],
        ], [
            'void_reason.required' => 'Alasan VOID wajib diisi.',
            'void_reason.min' => 'Alasan VOID minimal 5 karakter.',
        ]);

        $oldValues = $expense->toArray();

        // 3. Perbarui status transaksi menjadi VOID
        $expense->update([
            'verification_status' => 'VOID',
            'void_by' => $user->id,
            'void_at' => now(),
            'void_reason' => $validated['void_reason'],
        ]);

        // 4. Catat peristiwa VOID ke Audit Log
        AuditLog::record(
            'EXPENSE_VOIDED',
            $expense,
            "Transaksi #{$expense->id} di-VOID oleh {$user->name}. Alasan: {$validated['void_reason']}",
            $oldValues,
            $expense->toArray()
        );

        // 5. Beri tahu pemilik transaksi bahwa pengeluarannya telah dibatalkan
        SystemNotification::send(
            $expense->user_id,
            'Transaksi Telah Di-VOID',
            "Transaksi '{$expense->description}' sebesar Rp " . number_format($expense->amount, 0, ',', '.') . " di-VOID oleh Finance. Alasan: {$validated['void_reason']}",
            'danger',
            route('cash-advances.show', $expense->cash_advance_id)
        );

        return back()->with('success', "Transaksi berhasil di-VOID. Saldo sistem telah dikembalikan.");
    }

    /**
     * Menyetujui surat pernyataan kehilangan struk fisik oleh Atasan/Direktur.
     *
     * Sesuai ketentuan SOP, jika struk fisik hilang dan diganti dengan surat pernyataan,
     * transaksi tersebut wajib mendapatkan persetujuan manajerial (Atasan/Direktur)
     * sebelum dapat dinilai keabsahannya oleh tim Finance.
     *
     * @param  \Illuminate\Http\Request  $request Objek HTTP Request.
     * @param  \App\Models\Expense  $expense Model transaksi pengeluaran dengan struk hilang.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveLostReceipt(Request $request, Expense $expense): RedirectResponse
    {
        $user = Auth::user();

        // 1. Otorisasi manajerial
        if (!$user->isAtasan() && !$user->isDirektur()) {
            abort(403, 'Akses ditolak: Hanya Atasan atau Direktur yang dapat menyetujui surat pernyataan kehilangan struk.');
        }

        // 2. Simpan persetujuan kehilangan struk
        $expense->update([
            'lost_receipt_approved_by' => $user->id,
            'lost_receipt_approved_at' => now(),
        ]);

        // 3. Catat ke Audit Log
        AuditLog::record(
            'LOST_RECEIPT_APPROVED',
            $expense,
            "Surat pernyataan kehilangan struk transaksi #{$expense->id} disetujui oleh {$user->name}."
        );

        return back()->with('success', 'Surat pernyataan kehilangan struk telah disetujui atasan. Transaksi siap dinilai oleh Finance.');
    }
}
