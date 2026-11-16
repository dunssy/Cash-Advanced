# CA Atasan Tracking, Dropdown Pengajuan, & Sidebar Reorganization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reorganize sidebar (remove profile setting from sidebar so it only lives in the top-right header), add "History Dana Terpakai" to the sidebar, integrate new CA submission directly to Atasan account with dropdowns, and provide Atasan account with dedicated tracking of funds used & items purchased by division employees.

**Architecture:** 
1. Database migration adding `manager_id` foreign key to `cash_advances` table for direct binding to the approving Atasan.
2. Layout adjustments in `layouts/app.blade.php` removing profile settings from both desktop and mobile sidebars while keeping and polishing the top-right header user dropdown.
3. Form enhancement in `cash_advances.create` adding dropdowns for selecting Atasan/Manager, Division, and Project Category.
4. Controller and route additions for `expenses.history` providing comprehensive tracking of funds used and goods purchased, scoped by role (Karyawan sees own purchases, Atasan tracks all purchases of their division's employees with employee dropdown filter, Finance/Direktur sees company-wide).
5. Atasan dashboard enhancements displaying recent division purchases and quick tracking link.

**Tech Stack:** Laravel 11, Blade, Tailwind CSS, Alpine.js, Lucide Icons, PHPUnit.

**Spec:** User request in conversation prompt.

---

### Task 1: Database Migration - Add `manager_id` to `cash_advances` Table

**Files:**
- Create: `database/migrations/2026_11_16_100000_add_manager_id_to_cash_advances_table.php`
- Modify: `app/Models/CashAdvance.php`
- Test: `tests/Feature/CashAdvanceAtasanIntegrationTest.php`

- [ ] Step 1: Create migration `2026_11_16_100000_add_manager_id_to_cash_advances_table.php` adding nullable `manager_id` foreignId referencing `users(id)`.
- [ ] Step 2: Update `CashAdvance` model `$fillable` and add `manager()` relation.
- [ ] Step 3: Run migration via `php artisan migrate`.
- [ ] Step 4: Write test verifying that `CashAdvance` can store `manager_id` and load the `manager` relation.

---

### Task 2: Remove Profile Settings from Sidebar & Polish Top-Right Header

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

- [ ] Step 1: Remove user profile widget/link from desktop sidebar (lines 58-105).
- [ ] Step 2: Remove user profile widget/link from mobile drawer sidebar (lines 472-503).
- [ ] Step 3: Verify the top-right user profile dropdown in header contains `Pengaturan Profil (Biodata & Foto)` pointing to `route('profile.edit')`.
- [ ] Step 4: Add "History Dana Terpakai" (or "Tracking Dana Divisi") link to both desktop sidebar and mobile drawer under Menu Utama.

---

### Task 3: Enhance "Ajukan CA Baru" with Dropdowns & Atasan Integration

**Files:**
- Modify: `app/Http/Controllers/CashAdvanceController.php`
- Modify: `resources/views/cash_advances/create.blade.php`
- Test: `tests/Feature/CashAdvanceAtasanIntegrationTest.php`

- [ ] Step 1: Update `CashAdvanceController::create` to pass `$managers` (users with role 'atasan' and 'direktur') and list of standard divisions to the view.
- [ ] Step 2: Update `CashAdvanceController::store` to validate `manager_id` from dropdown, assign `manager_id` on the CA, update the user's `manager_id` if needed, and send notification to that Atasan.
- [ ] Step 3: Update `resources/views/cash_advances/create.blade.php` to include:
  - Dropdown "Pilih Atasan / Manager Penyetuju" (`<select name="manager_id">`)
  - Dropdown "Divisi / Unit Kerja" (`<select name="department">`)
  - Dropdown "Kategori Pengeluaran / Proyek" (`<select name="project_category">`)
- [ ] Step 4: Write feature test verifying that submitting CA with selected Atasan links the CA to that Atasan and sends notification.

---

### Task 4: Implement "History Dana Terpakai" & Atasan Tracking

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/ExpenseController.php`
- Create: `resources/views/expenses/history.blade.php`
- Test: `tests/Feature/ExpenseHistoryTrackingTest.php`

- [ ] Step 1: Register route `Route::get('/expenses/history', [ExpenseController::class, 'history'])->name('expenses.history');` in `routes/web.php`.
- [ ] Step 2: Implement `ExpenseController::history` with role-based scoping:
  - Karyawan: only their own expenses.
  - Atasan: expenses of employees in their division / subordinates.
  - Finance/Direktur/SuperAdmin: company-wide.
  - Filters: by employee, CA, category, status, search (goods/vendor), date range.
  - Statistics: total spent, items count, verified total, pending count.
- [ ] Step 3: Create `resources/views/expenses/history.blade.php` with:
  - Dynamic title ("Tracking Penggunaan Dana & Pembelian Barang Divisi" for Atasan vs "History Penggunaan Dana Saya" for Karyawan).
  - KPI summary cards.
  - Filter form (employee dropdown for Atasan, category dropdown, status dropdown, search bar).
  - Table showing: Tanggal, Karyawan & Divisi, Proyek/CA, Nama Barang/Detail Pengeluaran (`description`), Kategori, Vendor, Nominal, Struk, Status Verifikasi.
- [ ] Step 4: Write feature test testing history access and data scoping for Karyawan and Atasan.

---

### Task 5: Integrate Tracking into Atasan Dashboard

**Files:**
- Modify: `app/Http/Controllers/DashboardController.php`
- Modify: `resources/views/dashboard/index.blade.php`

- [ ] Step 1: In `DashboardController::index`, for Atasan, query the recent 5 purchases/expenses made by division employees (`recentDivisionExpenses`).
- [ ] Step 2: In `resources/views/dashboard/index.blade.php`, for Atasan, display the "Tracking Pembelian Barang Karyawan Divisi" section with employee name, items bought, nominal, date, receipt status, and link to full history.

---

### Task 6: Verification & End-to-End Testing

- [ ] Step 1: Run all feature tests (`php artisan test`).
- [ ] Step 2: Verify desktop and mobile responsiveness and design styling consistency.
- [ ] Step 3: Manual check via tinker / route test for all roles.
