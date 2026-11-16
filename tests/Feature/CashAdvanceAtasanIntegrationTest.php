<?php

namespace Tests\Feature;

use App\Models\CashAdvance;
use App\Models\SystemNotification;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashAdvanceAtasanIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_cash_advance_can_store_and_retrieve_manager(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $atasan = User::where('role', 'atasan')->first();

        $ca = CashAdvance::create([
            'ca_number' => 'CA-TEST-0001',
            'user_id' => $karyawan->id,
            'manager_id' => $atasan->id,
            'project_name' => 'Proyek Test Integrasi Atasan',
            'purpose' => 'Kebutuhan pengujian integrasi akun atasan',
            'requested_amount' => 5000000,
            'status' => 'PENDING_APPROVAL',
        ]);

        $this->assertDatabaseHas('cash_advances', [
            'id' => $ca->id,
            'manager_id' => $atasan->id,
        ]);

        $this->assertEquals($atasan->id, $ca->fresh()->manager->id);
        $this->assertTrue($atasan->canApproveCA($ca));
    }

    public function test_karyawan_can_submit_ca_with_dropdown_and_integrate_to_atasan(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $atasan = User::where('role', 'atasan')->first();

        $this->actingAs($karyawan);

        // Access create form - should see dropdown data
        $response = $this->get(route('cash-advances.create'));
        $response->assertStatus(200);
        $response->assertSee($atasan->name);
        $response->assertSee('Pilih Atasan / Manager Penyetuju');
        $response->assertSee('Kategori Keperluan');

        // Submit CA with dropdown selection
        $postResponse = $this->post(route('cash-advances.store'), [
            'manager_id' => $atasan->id,
            'department' => 'Engineering & Commissioning',
            'project_category' => 'Instalasi & Komisioning Lapangan',
            'project_name' => 'Instalasi Panel Proyek Petrokimia',
            'project_code' => 'PRJ-PETRO-01',
            'purpose' => 'Pembelian kabel ducting dan sewa scaffolding 5 hari',
            'requested_amount' => 8500000,
        ]);

        $postResponse->assertSessionHas('success');

        // Verify CA in database
        $this->assertDatabaseHas('cash_advances', [
            'user_id' => $karyawan->id,
            'manager_id' => $atasan->id,
            'project_name' => 'Instalasi Panel Proyek Petrokimia',
            'requested_amount' => 8500000,
            'status' => 'PENDING_APPROVAL',
        ]);

        // Verify notification sent to the selected Atasan
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $atasan->id,
            'title' => 'Pengajuan CA Baru Masuk',
        ]);

        // Verify Atasan sees it in their index
        $this->actingAs($atasan);
        $atasanIndex = $this->get(route('cash-advances.index'));
        $atasanIndex->assertStatus(200);
        $atasanIndex->assertSee('Instalasi Panel Proyek Petrokimia');
    }

    public function test_sidebar_profile_setting_is_only_in_right_header_and_history_menu_is_present(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $this->actingAs($karyawan);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Sidebar <aside> should NOT have profile.edit link
        preg_match('/<aside class="hidden md:flex.*?<\/aside>/s', $content, $asideMatches);
        $this->assertNotEmpty($asideMatches);
        $this->assertStringNotContainsString(route('profile.edit'), $asideMatches[0]);

        // 2. Mobile drawer should NOT have profile.edit link
        preg_match('/<div x-show="mobileNavOpen".*?<\/nav>/s', $content, $drawerMatches);
        $this->assertNotEmpty($drawerMatches);
        $this->assertStringNotContainsString(route('profile.edit'), $drawerMatches[0]);

        // 3. Topbar header right dropdown SHOULD have profile.edit link
        preg_match('/<header class="bg-\[#050A1E\].*?<\/header>/s', $content, $headerMatches);
        $this->assertNotEmpty($headerMatches);
        $this->assertStringContainsString(route('profile.edit'), $headerMatches[0]);

        // 4. History / Tracking menu should be present in sidebar
        $this->assertStringContainsString(route('expenses.history'), $asideMatches[0]);
    }

    public function test_atasan_dashboard_renders_tracking_division_expenses_and_purchases(): void
    {
        $atasan = User::where('role', 'atasan')->first();
        $this->actingAs($atasan);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Tracking Dana Divisi');
        $response->assertSee('Barang Dibeli Divisi');
        $response->assertSee('Tracking Pembelian Barang Karyawan Divisi');
    }

    public function test_karyawan_cannot_see_or_select_direktur_as_manager(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $atasan = User::where('role', 'atasan')->first();
        $direktur = User::where('role', 'direktur')->first();

        $this->actingAs($karyawan);

        // 1. Karyawan opens create form
        $response = $this->get(route('cash-advances.create'));
        $response->assertStatus(200);

        // Should see Atasan in the dropdown options
        $response->assertSee($atasan->name);
        // Should NOT see Direktur in the dropdown options
        $response->assertDontSee($direktur->name . ' &bull; DIREKTUR', false);

        // 2. Karyawan attempts to submit with Direktur ID
        $postResponse = $this->post(route('cash-advances.store'), [
            'manager_id' => $direktur->id,
            'project_name' => 'Proyek Ilegal Bypass Direktur',
            'project_code' => 'PRJ-PETRO-01',
            'purpose' => 'Mencoba memilih direktur langsung',
            'items_detail' => 'Item percobaan',
            'requested_amount' => 5000000,
        ]);

        $postResponse->assertSessionHasErrors(['manager_id']);
        $this->assertDatabaseMissing('cash_advances', [
            'project_name' => 'Proyek Ilegal Bypass Direktur',
        ]);
    }

    public function test_atasan_can_see_and_select_direktur_as_manager(): void
    {
        $atasan = User::where('role', 'atasan')->first();
        $direktur = User::where('role', 'direktur')->first();

        $this->actingAs($atasan);

        // Atasan opens create form
        $response = $this->get(route('cash-advances.create'));
        $response->assertStatus(200);

        // Atasan CAN see Direktur in manager dropdown
        $response->assertSee($direktur->name);
        $response->assertSee('DIREKTUR');

        // Atasan submits CA directed to Direktur
        $postResponse = $this->post(route('cash-advances.store'), [
            'manager_id' => $direktur->id,
            'department' => 'Operasional & Engineering',
            'project_category' => 'Instalasi & Komisioning Lapangan',
            'project_name' => 'Pengadaan Modul Inverter Batch 3',
            'project_code' => 'PRJ-PETRO-01',
            'purpose' => 'Kebutuhan mendesak sparepart inverter pabrik',
            'items_detail' => "1. Control Board Modul Inverter - 1 set (Rp 12.000.000)\n2. Jasa Kalibrasi Lab - 1 job (Rp 3.000.000)",
            'requested_amount' => 15000000,
        ]);

        $postResponse->assertSessionHas('success');
        $this->assertDatabaseHas('cash_advances', [
            'user_id' => $atasan->id,
            'manager_id' => $direktur->id,
            'department' => 'Operasional & Engineering',
            'items_detail' => "1. Control Board Modul Inverter - 1 set (Rp 12.000.000)\n2. Jasa Kalibrasi Lab - 1 job (Rp 3.000.000)",
        ]);
    }

    public function test_karyawan_division_is_locked_and_derived_from_project_code(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $atasan = User::where('role', 'atasan')->first();

        // Ensure project code with specific department
        $pCode = \App\Models\ProjectCode::firstOrCreate(['code' => 'PRJ-ENG-2026'], [
            'description' => 'Proyek Khusus Tim Lapangan',
            'department' => 'Instalasi Lapangan',
            'created_by' => $atasan->id,
            'is_active' => true,
        ]);

        $this->actingAs($karyawan);

        // Karyawan sees locked division indicator in create view
        $response = $this->get(route('cash-advances.create'));
        $response->assertStatus(200);
        $response->assertSee('Ditetapkan Atasan');
        $response->assertSee('Otoritas Atasan');

        // Karyawan submits with project code
        $postResponse = $this->post(route('cash-advances.store'), [
            'manager_id' => $atasan->id,
            'department' => 'Hacker Divisi Lain', // Karyawan tried to pass custom division
            'project_name' => 'Pemasangan Sensor Suhu',
            'project_code' => 'PRJ-ENG-2026',
            'purpose' => 'Sensor suhu transformator utama',
            'items_detail' => 'Sensor RTD PT100 4 unit @ Rp 500.000',
            'requested_amount' => 2000000,
        ]);

        $postResponse->assertSessionHas('success');

        // Department must be derived from ProjectCode, NOT the arbitrary hacker input
        $this->assertDatabaseHas('cash_advances', [
            'user_id' => $karyawan->id,
            'project_code' => 'PRJ-ENG-2026',
            'department' => 'Instalasi Lapangan',
        ]);
    }

    public function test_tujuan_pengajuan_and_rincian_barang_dibeli_are_distinct_fields(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $atasan = User::where('role', 'atasan')->first();

        $this->actingAs($karyawan);

        // Check create form has both distinct labels
        $response = $this->get(route('cash-advances.create'));
        $response->assertStatus(200);
        $response->assertSee('Tujuan Pengajuan');
        $response->assertSee('Rincian Barang yang Dibeli');

        // Submit both fields
        $tujuan = 'Perjalanan dinas maintenance generator PLTU Karangkates';
        $rincianBarang = "1. AVR Generator Stamford - 1 unit (Rp 4.500.000)\n2. Dioda Rotating Rectifier - 2 pcs (Rp 1.200.000)\n3. Solar operasional & Tol - Rp 800.000";

        $postResponse = $this->post(route('cash-advances.store'), [
            'manager_id' => $atasan->id,
            'project_name' => 'Overhaul Generator PLTU',
            'project_code' => 'PRJ-PETRO-01',
            'purpose' => $tujuan,
            'items_detail' => $rincianBarang,
            'requested_amount' => 6500000,
        ]);

        $postResponse->assertSessionHas('success');

        $ca = CashAdvance::where('project_name', 'Overhaul Generator PLTU')->first();
        $this->assertNotNull($ca);
        $this->assertEquals($tujuan, $ca->purpose);
        $this->assertEquals($rincianBarang, $ca->items_detail);

        // Verify show page displays both sections separately
        $showResponse = $this->get(route('cash-advances.show', $ca));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Tujuan Pengajuan:');
        $showResponse->assertSee($tujuan);
        $showResponse->assertSee('Rincian Barang yang Dibeli');
        $showResponse->assertSee('AVR Generator Stamford');
    }

    public function test_dashboard_displays_saldo_nol_when_no_active_ca_and_fills_saldo_when_ca_is_active(): void
    {
        $karyawan = User::where('role', 'karyawan')->first();
        $this->actingAs($karyawan);

        // 1. Initial State: No active Cash Advance -> Dashboard must show Saldo Nol (Rp 0)
        $response1 = $this->get(route('dashboard'));
        $response1->assertStatus(200);
        $response1->assertSee('Sisa Saldo Kasbon Saya');
        $response1->assertSee('Rp 0');
        $response1->assertSee('Saldo Nol');
        $response1->assertSee('Ajukan CA Baru');

        // 2. Active State: Create an active CA with 15.000.000
        $ca = CashAdvance::create([
            'ca_number' => 'CA-ACTIVE-0099',
            'user_id' => $karyawan->id,
            'project_name' => 'Proyek Pengujian Saldo Aktif',
            'project_code' => 'PRJ-PETRO-01',
            'purpose' => 'Pengujian saldo terisi',
            'requested_amount' => 15000000,
            'approved_amount' => 15000000,
            'status' => 'ACTIVE',
            'due_date' => now()->addDays(30),
        ]);

        $response2 = $this->get(route('dashboard'));
        $response2->assertStatus(200);
        $response2->assertSee('Rp 15.000.000');
        $response2->assertSee('Proyek Pengujian Saldo Aktif');
        $response2->assertSee('CA-ACTIVE-0099');
        $response2->assertSee('Catat Struk');
    }
}
