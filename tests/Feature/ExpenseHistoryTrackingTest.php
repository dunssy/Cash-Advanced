<?php

namespace Tests\Feature;

use App\Models\CashAdvance;
use App\Models\Expense;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseHistoryTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_karyawan_can_access_own_expense_history(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $atasan = User::where('role', 'atasan')->first();

        $ca = CashAdvance::create([
            'ca_number' => 'CA-HIST-0001',
            'user_id' => $karyawan->id,
            'manager_id' => $atasan->id,
            'project_name' => 'Proyek Inverter Gresik',
            'purpose' => 'Operasional lapangan',
            'requested_amount' => 5000000,
            'approved_amount' => 5000000,
            'status' => 'ACTIVE',
        ]);

        $exp1 = Expense::create([
            'cash_advance_id' => $ca->id,
            'user_id' => $karyawan->id,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Kabel Ducting PVC 2 inch & Spiral Conduit',
            'category' => 'material',
            'amount' => 1250000,
            'vendor_name' => 'Toko Teknik Sejahtera',
            'verification_status' => 'PENDING',
        ]);

        $this->actingAs($karyawan);
        $response = $this->get(route('expenses.history'));

        $response->assertStatus(200);
        $response->assertSee('History Penggunaan Dana');
        $response->assertSee('Kabel Ducting PVC 2 inch');
        $response->assertSee('Toko Teknik Sejahtera');
        $response->assertSee('Rp 1.250.000');
    }

    public function test_atasan_can_track_division_employee_purchases(): void
    {
        $atasan = User::where('role', 'atasan')->first();
        $karyawan = User::where('role', 'karyawan')->first();
        $otherKaryawan = User::where('role', 'karyawan')->where('id', '!=', $karyawan->id)->first();

        $ca1 = CashAdvance::create([
            'ca_number' => 'CA-HIST-0002',
            'user_id' => $karyawan->id,
            'manager_id' => $atasan->id,
            'project_name' => 'Instalasi Panel 250kW',
            'purpose' => 'Kebutuhan material',
            'requested_amount' => 6000000,
            'approved_amount' => 6000000,
            'status' => 'ACTIVE',
        ]);

        $exp1 = Expense::create([
            'cash_advance_id' => $ca1->id,
            'user_id' => $karyawan->id,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Baut M12 Stainless & Dynabolt 50 pcs',
            'category' => 'material',
            'amount' => 450000,
            'vendor_name' => 'CV Baut Perkasa',
            'verification_status' => 'VALID',
        ]);

        $ca2 = CashAdvance::create([
            'ca_number' => 'CA-HIST-0003',
            'user_id' => $otherKaryawan->id,
            'manager_id' => $atasan->id,
            'project_name' => 'Service Inverter Surabaya',
            'purpose' => 'Transport dan bensin',
            'requested_amount' => 3000000,
            'approved_amount' => 3000000,
            'status' => 'ACTIVE',
        ]);

        $exp2 = Expense::create([
            'cash_advance_id' => $ca2->id,
            'user_id' => $otherKaryawan->id,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Bensin Pertamax Mobil Operasional Innova',
            'category' => 'transport',
            'amount' => 350000,
            'vendor_name' => 'SPBU Pertamina 54.601',
            'verification_status' => 'PENDING',
        ]);

        // Login as Atasan
        $this->actingAs($atasan);
        $response = $this->get(route('expenses.history'));

        $response->assertStatus(200);
        $response->assertSee('Tracking Penggunaan Dana Divisi');
        // Atasan sees both purchases from their subordinates
        $response->assertSee('Baut M12 Stainless & Dynabolt 50 pcs');
        $response->assertSee('Bensin Pertamax Mobil Operasional Innova');
        $response->assertSee('CV Baut Perkasa');
        $response->assertSee('SPBU Pertamina 54.601');

        // Test filter by employee
        $filterResponse = $this->get(route('expenses.history', ['user_id' => $karyawan->id]));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Baut M12 Stainless & Dynabolt 50 pcs');
        $filterResponse->assertDontSee('Bensin Pertamax Mobil Operasional Innova');
    }
}
