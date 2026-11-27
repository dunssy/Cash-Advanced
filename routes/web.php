<?php

use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashAdvanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectCodeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SoftDeleteArchiveController;
use App\Http\Controllers\TopUpController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticationss
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile & Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Cash Advance Lifecycle
    Route::get('/cash-advances', [CashAdvanceController::class, 'index'])->name('cash-advances.index');
    Route::get('/cash-advances/create', [CashAdvanceController::class, 'create'])->name('cash-advances.create');
    Route::post('/cash-advances', [CashAdvanceController::class, 'store'])->name('cash-advances.store');
    Route::get('/cash-advances/{cashAdvance}', [CashAdvanceController::class, 'show'])->name('cash-advances.show');
    Route::post('/cash-advances/{cashAdvance}/approve', [CashAdvanceController::class, 'approve'])->name('cash-advances.approve');
    Route::post('/cash-advances/{cashAdvance}/reject', [CashAdvanceController::class, 'reject'])->name('cash-advances.reject');
    Route::post('/cash-advances/{cashAdvance}/disburse', [CashAdvanceController::class, 'disburse'])->name('cash-advances.disburse');
    Route::post('/cash-advances/{cashAdvance}/complete-project', [CashAdvanceController::class, 'completeProject'])->name('cash-advances.complete-project');
    Route::post('/cash-advances/{cashAdvance}/submit-refund', [CashAdvanceController::class, 'submitRefund'])->name('cash-advances.submit-refund');
    Route::post('/cash-advances/{cashAdvance}/close', [CashAdvanceController::class, 'close'])->name('cash-advances.close');
    Route::post('/cash-advances/{cashAdvance}/unlock-gembok', [CashAdvanceController::class, 'unlockGembok'])->name('cash-advances.unlock-gembok');
    Route::delete('/cash-advances/{cashAdvance}', [CashAdvanceController::class, 'destroy'])->name('cash-advances.destroy');

    // Expense Tracking & History
    Route::get('/expenses/history', [ExpenseController::class, 'history'])->name('expenses.history');
    Route::get('/cash-advances/{cashAdvance}/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/cash-advances/{cashAdvance}/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('/expenses/{expense}/void', [ExpenseController::class, 'voidExpense'])->name('expenses.void');
    Route::post('/expenses/{expense}/approve-lost-receipt', [ExpenseController::class, 'approveLostReceipt'])->name('expenses.approve-lost-receipt');

    // Expense Verification
    Route::post('/expenses/{expense}/verify', [VerificationController::class, 'verifyExpense'])->name('expenses.verify');

    // Top-Up Management
    Route::post('/cash-advances/{cashAdvance}/top-ups', [TopUpController::class, 'store'])->name('top-ups.store');
    Route::delete('/top-ups/{topUp}', [TopUpController::class, 'destroy'])->name('top-ups.destroy');
    Route::post('/top-ups/{topUp}/approve-atasan', [TopUpController::class, 'approveAtasan'])->name('top-ups.approve-atasan');
    Route::post('/top-ups/{topUp}/approve-finance', [TopUpController::class, 'approveFinance'])->name('top-ups.approve-finance');
    Route::post('/top-ups/{topUp}/approve-direktur', [TopUpController::class, 'approveDirektur'])->name('top-ups.approve-direktur');
    Route::post('/top-ups/{topUp}/reject', [TopUpController::class, 'reject'])->name('top-ups.reject');
    Route::post('/top-ups/{topUp}/disburse', [TopUpController::class, 'disburse'])->name('top-ups.disburse');

    // Reports (4 Core Reports)
    Route::get('/reports/rincian-ca', [ReportController::class, 'rincianCA'])->name('reports.rincian-ca');
    Route::get('/reports/rekap-bulanan', [ReportController::class, 'rekapBulanan'])->name('reports.rekap-bulanan');
    Route::get('/reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
    Route::get('/reports/rekap-karyawan', [ReportController::class, 'rekapKaryawan'])->name('reports.rekap-karyawan');

    // Audit Trail & Arsip Soft Delete
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/soft-deletes', [SoftDeleteArchiveController::class, 'index'])->name('soft-deletes.index');
    Route::post('/soft-deletes/cash-advances/{id}/restore', [SoftDeleteArchiveController::class, 'restoreCashAdvance'])->name('soft-deletes.cash-advances.restore');
    Route::post('/soft-deletes/expenses/{id}/restore', [SoftDeleteArchiveController::class, 'restoreExpense'])->name('soft-deletes.expenses.restore');
    Route::post('/soft-deletes/top-ups/{id}/restore', [SoftDeleteArchiveController::class, 'restoreTopUp'])->name('soft-deletes.top-ups.restore');

    // Master Kode Proyek (Atasan & Manajemen)
    Route::get('/project-codes', [ProjectCodeController::class, 'index'])->name('project-codes.index');
    Route::post('/project-codes', [ProjectCodeController::class, 'store'])->name('project-codes.store');
    Route::post('/project-codes/{projectCode}/toggle-status', [ProjectCodeController::class, 'toggleStatus'])->name('project-codes.toggle-status');
    Route::delete('/project-codes/{projectCode}', [ProjectCodeController::class, 'destroy'])->name('project-codes.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Super Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// Fallback to serve uploaded public storage files when symlink is not available (e.g., Windows development)
Route::get('/storage/{path}', function (string $path) {
    $filePath = storage_path('app/public/' . $path);
    $basePath = realpath(storage_path('app/public'));
    $resolvedPath = realpath($filePath);

    if (!$resolvedPath || !str_starts_with($resolvedPath, $basePath) || !file_exists($resolvedPath)) {
        abort(404);
    }

    $mimeType = mime_content_type($resolvedPath) ?: 'application/octet-stream';

    return response()->file($resolvedPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.local.file');

