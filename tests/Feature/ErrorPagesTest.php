<?php

namespace Tests\Feature;

use App\Models\CashAdvance;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $karyawan;
    private User $otherKaryawan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('role', 'super_admin')->firstOrFail();
        $this->karyawan = User::where('role', 'karyawan')->firstOrFail();
        $this->otherKaryawan = User::where('role', 'karyawan')
            ->where('id', '!=', $this->karyawan->id)
            ->firstOrFail();
    }

    public function test_super_admin_can_access_admin_pages_with_status_200(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index'))
            ->assertStatus(200);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.index'))
            ->assertStatus(200);
    }

    public function test_unauthorized_user_gets_custom_403_page_when_accessing_admin_or_restricted_resource(): void
    {
        $response = $this->actingAs($this->karyawan)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
        $response->assertSee('403');
        $response->assertSee('Forbidden');
        $response->assertSee('Anda tidak memiliki izin untuk mengakses halaman ini.');
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee('assets/images/errors/403.png');
        $response->assertSee('assets/css/errors.css');
        $response->assertSee(route('dashboard'));
        $response->assertDontSee(route('admin.users.index'));
        $response->assertDontSee('Menu Utama');
        $response->assertDontSee('Stack trace');

        // Pastikan tombol kembali mengarah ke halaman yang dapat diakses pengguna (200)
        $this->actingAs($this->karyawan)
            ->get(route('dashboard'))
            ->assertStatus(200);
    }

    public function test_user_cannot_access_other_users_cash_advance_and_receives_403(): void
    {
        $otherCa = CashAdvance::create([
            'ca_number' => 'CA-ERR-0001',
            'user_id' => $this->otherKaryawan->id,
            'project_name' => 'Proyek Rahasia Divisi Lain',
            'project_code' => 'PRJ-ERR-01',
            'purpose' => 'Keperluan pengujian otorisasi',
            'requested_amount' => 1500000,
            'status' => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($this->karyawan)
            ->get(route('cash-advances.show', $otherCa));

        $response->assertStatus(403);
        $response->assertSee('assets/images/errors/403.png');
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee(route('dashboard'));
    }

    public function test_unauthenticated_guest_still_redirects_to_login_on_protected_routes(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_unregistered_url_returns_custom_404_page_for_guest(): void
    {
        $response = $this->get('/halaman-tidak-terdaftar-xyz');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Not Found');
        $response->assertSee('Halaman yang kamu cari tidak ditemukan.');
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee('assets/images/errors/404.png');
        $response->assertSee('assets/css/errors.css');
        $response->assertSee(route('login'));

        $this->get(route('login'))->assertStatus(200);
    }

    public function test_missing_route_model_binding_returns_custom_404_page_with_dashboard_link(): void
    {
        $response = $this->actingAs($this->karyawan)
            ->get('/cash-advances/999999');

        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('Not Found');
        $response->assertSee('Halaman yang kamu cari tidak ditemukan.');
        $response->assertSee('assets/images/errors/404.png');
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee(route('dashboard'));
    }

    public function test_json_and_api_requests_receive_json_error_responses(): void
    {
        $forbiddenJson = $this->actingAs($this->karyawan)
            ->getJson(route('admin.users.index'));

        $forbiddenJson->assertStatus(403);
        $forbiddenJson->assertHeader('Content-Type', 'application/json');
        $forbiddenJson->assertJsonStructure(['message']);

        $notFoundJson = $this->actingAs($this->karyawan)
            ->getJson('/api/tidak-ada-endpoint');

        $notFoundJson->assertStatus(404);
        $notFoundJson->assertHeader('Content-Type', 'application/json');
        $notFoundJson->assertJsonStructure(['message']);
    }
}
