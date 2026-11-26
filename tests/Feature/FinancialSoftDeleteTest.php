<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\TopUp;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_karyawan_can_soft_delete_pending_expense_and_data_remains_in_backend(): void
    {
        $karyawan = User::where('email', 'karyawan@technodrives.co.id')->firstOrFail();
        $ca = CashAdvance::where('user_id', $karyawan->id)->where('status', 'ACTIVE')->firstOrFail();
        $pendingExpense = $ca->expenses()->where('verification_status', 'PENDING')->firstOrFail();

        $balanceBefore = $ca->current_balance;
        $expenseAmount = (float) $pendingExpense->amount;

        $this->actingAs($karyawan);

        $response = $this->delete(route('expenses.destroy', $pendingExpense), [
            'delete_reason' => 'Salah input nominal struk, akan diinput ulang.',
        ]);

        $response->assertRedirect(route('cash-advances.show', $ca));
        $response->assertSessionHas('success');

        // Hilang dari query frontend biasa
        $this->assertNull(Expense::find($pendingExpense->id));

        // Masih ada di backend database (Soft Delete)
        $this->assertSoftDeleted('expenses', [
            'id' => $pendingExpense->id,
            'deleted_by' => $karyawan->id,
            'delete_reason' => 'Salah input nominal struk, akan diinput ulang.',
        ]);

        // Saldo CA otomatis pulih karena transaksi soft-deleted tidak dihitung
        $ca->refresh();
        $this->assertEquals($balanceBefore + $expenseAmount, $ca->current_balance);

        // Audit trail tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'EXPENSE_SOFT_DELETED',
            'record_type' => 'Expense',
            'record_id' => $pendingExpense->id,
        ]);
    }

    public function test_valid_expense_cannot_be_soft_deleted_even_by_finance(): void
    {
        $finance = User::where('role', 'finance')->firstOrFail();
        $validExpense = Expense::where('verification_status', 'VALID')->firstOrFail();

        $this->actingAs($finance);

        $response = $this->delete(route('expenses.destroy', $validExpense), [
            'delete_reason' => 'Percobaan hapus transaksi valid.',
        ]);

        $response->assertStatus(403);
        $this->assertNotSoftDeleted('expenses', ['id' => $validExpense->id]);
    }

    public function test_soft_delete_requires_valid_reason(): void
    {
        $karyawan = User::where('email', 'karyawan@technodrives.co.id')->firstOrFail();
        $pendingExpense = Expense::where('user_id', $karyawan->id)
            ->where('verification_status', 'PENDING')
            ->firstOrFail();

        $this->actingAs($karyawan);

        $response = $this->delete(route('expenses.destroy', $pendingExpense), [
            'delete_reason' => 'abc',
        ]);

        $response->assertSessionHasErrors('delete_reason');
        $this->assertNotSoftDeleted('expenses', ['id' => $pendingExpense->id]);
    }

    public function test_karyawan_cannot_soft_delete_active_ca_but_can_delete_own_pending_ca_and_number_stays_unique(): void
    {
        $karyawan = User::where('email', 'karyawan@technodrives.co.id')->firstOrFail();
        $activeCa = CashAdvance::where('user_id', $karyawan->id)->where('status', 'ACTIVE')->firstOrFail();

        $this->actingAs($karyawan);

        // Karyawan tidak boleh menghapus CA yang sudah ACTIVE
        $this->delete(route('cash-advances.destroy', $activeCa), [
            'delete_reason' => 'Mencoba hapus CA aktif.',
        ])->assertStatus(403);

        // Buat CA PENDING_APPROVAL milik atasan (yang belum punya CA aktif)
        $atasan = User::where('role', 'atasan')->firstOrFail();
        $pendingCa = CashAdvance::where('user_id', $atasan->id)->where('status', 'PENDING_APPROVAL')->firstOrFail();

        $this->actingAs($atasan);
        $this->assertTrue($atasan->hasActiveLock());

        $response = $this->delete(route('cash-advances.destroy', $pendingCa), [
            'delete_reason' => 'Proyek ditunda oleh klien.',
        ]);

        $response->assertRedirect(route('cash-advances.index'));
        $this->assertSoftDeleted('cash_advances', [
            'id' => $pendingCa->id,
            'deleted_by' => $atasan->id,
        ]);

        // Gembok pengajuan terbuka kembali setelah CA pending di-soft-delete
        $atasan->refresh();
        $this->assertFalse($atasan->hasActiveLock());

        // Pengajuan CA baru tidak bentrok ca_number dengan CA yang ter-soft-delete
        $storeResponse = $this->post(route('cash-advances.store'), [
            'project_name' => 'Proyek Pengganti PLTU Paiton',
            'purpose' => 'Survey ulang setelah jadwal baru disepakati.',
            'requested_amount' => 3500000,
        ]);

        $storeResponse->assertRedirect();
        $newCa = CashAdvance::where('user_id', $atasan->id)->latest('id')->firstOrFail();
        $this->assertNotEquals($pendingCa->ca_number, $newCa->ca_number);
    }

    public function test_top_up_soft_delete_rules_for_pending_and_disbursed(): void
    {
        $karyawan = User::where('email', 'karyawan@technodrives.co.id')->firstOrFail();
        $finance = User::where('role', 'finance')->firstOrFail();
        $ca = CashAdvance::where('user_id', $karyawan->id)->where('status', 'ACTIVE')->firstOrFail();

        $disbursedTopUp = $ca->topUps()->where('status', 'DISBURSED')->firstOrFail();

        // Top-up yang sudah DISBURSED tidak boleh dihapus bahkan oleh Finance
        $this->actingAs($finance);
        $this->delete(route('top-ups.destroy', $disbursedTopUp), [
            'delete_reason' => 'Coba hapus top-up cair.',
        ])->assertStatus(403);

        // Buat Top-Up baru berstatus PENDING_ATASAN
        $pendingTopUp = TopUp::create([
            'cash_advance_id' => $ca->id,
            'user_id' => $karyawan->id,
            'amount' => 1000000,
            'reason' => 'Kebutuhan tambahan transport lapangan mendadak.',
            'status' => 'PENDING_ATASAN',
            'requires_direktur_approval' => false,
        ]);

        $this->actingAs($karyawan);
        $this->delete(route('top-ups.destroy', $pendingTopUp), [
            'delete_reason' => 'Tidak jadi butuh tambahan dana.',
        ])->assertRedirect(route('cash-advances.show', $ca));

        $this->assertSoftDeleted('top_ups', [
            'id' => $pendingTopUp->id,
            'deleted_by' => $karyawan->id,
        ]);
    }

    public function test_finance_can_view_soft_delete_archive_and_restore_records(): void
    {
        $karyawan = User::where('email', 'karyawan@technodrives.co.id')->firstOrFail();
        $finance = User::where('role', 'finance')->firstOrFail();
        $ca = CashAdvance::where('user_id', $karyawan->id)->where('status', 'ACTIVE')->firstOrFail();
        $expense = $ca->expenses()->where('verification_status', 'PENDING')->firstOrFail();

        // Karyawan dilarang membuka halaman arsip soft delete backend
        $this->actingAs($karyawan)
            ->get(route('soft-deletes.index'))
            ->assertStatus(403);

        // Soft delete expense & parent CA
        $this->actingAs($karyawan)->delete(route('expenses.destroy', $expense), [
            'delete_reason' => 'Terhapus tidak sengaja oleh karyawan.',
        ]);

        $this->actingAs($finance)->delete(route('cash-advances.destroy', $ca), [
            'delete_reason' => 'Arsip sementara untuk audit internal.',
        ]);

        // Finance melihat kedua data tetap ada di halaman Arsip Soft Delete
        $archiveResponse = $this->actingAs($finance)->get(route('soft-deletes.index'));
        $archiveResponse->assertStatus(200);
        $archiveResponse->assertSee($ca->ca_number);
        $archiveResponse->assertSee($expense->description);

        // Restore expense sebelum CA induk dipulihkan harus ditolak
        $this->actingAs($finance)
            ->post(route('soft-deletes.expenses.restore', $expense->id))
            ->assertSessionHas('error');
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);

        // Pulihkan CA induk terlebih dahulu
        $this->actingAs($finance)
            ->post(route('soft-deletes.cash-advances.restore', $ca->id))
            ->assertSessionHas('success');
        $this->assertNotSoftDeleted('cash_advances', ['id' => $ca->id]);

        // Baru pulihkan Expense
        $this->actingAs($finance)
            ->post(route('soft-deletes.expenses.restore', $expense->id))
            ->assertSessionHas('success');
        $this->assertNotSoftDeleted('expenses', ['id' => $expense->id]);

        // Pastikan kolom deleted_by & delete_reason bersih kembali dan log restore tercatat
        $expense->refresh();
        $this->assertNull($expense->deleted_by);
        $this->assertNull($expense->delete_reason);
        $this->assertDatabaseHas('audit_logs', ['action' => 'CA_RESTORED', 'record_id' => $ca->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'EXPENSE_RESTORED', 'record_id' => $expense->id]);
    }
}
