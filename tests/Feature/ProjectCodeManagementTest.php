<?php

namespace Tests\Feature;

use App\Models\CashAdvance;
use App\Models\ProjectCode;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCodeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_atasan_can_view_and_create_project_codes(): void
    {
        $atasan = User::where('role', 'atasan')->first();
        $this->actingAs($atasan);

        $response = $this->get(route('project-codes.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Kode Proyek');

        $postResponse = $this->post(route('project-codes.store'), [
            'code' => 'PRJ-2026-TEST01',
            'department' => 'Operasional & Engineering',
            'description' => 'Proyek Pengujian Kode Otomatis Atasan',
        ]);

        $postResponse->assertSessionHas('success');
        $this->assertDatabaseHas('project_codes', [
            'code' => 'PRJ-2026-TEST01',
            'created_by' => $atasan->id,
            'is_active' => true,
        ]);
    }

    public function test_karyawan_cannot_manage_project_codes(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $this->actingAs($karyawan);

        $response = $this->get(route('project-codes.index'));
        $response->assertStatus(403);

        $postResponse = $this->post(route('project-codes.store'), [
            'code' => 'PRJ-ILLEGAL-01',
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_karyawan_can_select_project_code_from_dropdown_and_type_project_name_manually(): void
    {
        $atasan = User::where('role', 'atasan')->first();
        $karyawan = User::where('role', 'karyawan')->first();

        // Create official project code by Atasan
        $pCode = ProjectCode::create([
            'code' => 'PRJ-2026-GRESIK99',
            'description' => 'Proyek Khusus Pupuk Petrokimia',
            'department' => 'Operasional & Engineering',
            'created_by' => $atasan->id,
            'is_active' => true,
        ]);

        $this->actingAs($karyawan);

        // Karyawan opens form - should see dropdown for Kode Proyek
        $formResponse = $this->get(route('cash-advances.create'));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Pilih Kode Proyek Resmi');
        $formResponse->assertSee('PRJ-2026-GRESIK99');
        $formResponse->assertSee('Nama Proyek / Kegiatan (Ketik Manual)');

        // Submit form with chosen project code and manually typed project name
        $submitResponse = $this->post(route('cash-advances.store'), [
            'manager_id' => $atasan->id,
            'department' => 'Engineering & Commissioning',
            'project_category' => 'Instalasi & Komisioning Lapangan',
            'project_code' => 'PRJ-2026-GRESIK99', // From dropdown
            'project_name' => 'Instalasi Inverter 315kW Unit 4 Petrokimia', // Typed manually
            'purpose' => 'Biaya akomodasi teknisi dan sewa alat pengangkat crane',
            'requested_amount' => 7500000,
        ]);

        $submitResponse->assertSessionHas('success');

        $this->assertDatabaseHas('cash_advances', [
            'user_id' => $karyawan->id,
            'manager_id' => $atasan->id,
            'project_code' => 'PRJ-2026-GRESIK99',
            'project_name' => 'Instalasi Inverter 315kW Unit 4 Petrokimia',
            'requested_amount' => 7500000,
        ]);
    }
}
