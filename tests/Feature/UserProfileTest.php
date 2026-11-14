<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::where('role', 'karyawan')->first();
        $this->actingAs($user);

        $response = $this->get('/profile');
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
        $response->assertSee('Hak Akses Peran');
    }

    public function test_user_can_update_biodata_and_role_cannot_be_changed(): void
    {
        $user = User::where('role', 'karyawan')->first();
        $this->actingAs($user);

        $response = $this->put('/profile', [
            'name' => 'Ahmad Maulidun Updated',
            'email' => $user->email,
            'phone' => '089911223344',
            'department' => 'Field Technical Operations',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Ahmad Maulidun Updated',
            'role' => 'direktur', // Percobaan manipulasi role tidak boleh berefek!
        ]);

        $response->assertSessionHas('success');
        $user->refresh();

        $this->assertEquals('Ahmad Maulidun Updated', $user->name);
        $this->assertEquals('089911223344', $user->phone);
        $this->assertEquals('Field Technical Operations', $user->department);
        // Pastikan role tetap karyawan dan TIDAK berubah menjadi direktur!
        $this->assertEquals('karyawan', $user->role);
    }

    public function test_user_can_upload_new_avatar(): void
    {
        Storage::fake('public');

        $user = User::where('role', 'karyawan')->first();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('my-office-photo.jpg', 300, 300);

        $response = $this->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertSessionHas('success');
        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_user_can_remove_avatar(): void
    {
        Storage::fake('public');

        $user = User::where('role', 'karyawan')->first();
        $this->actingAs($user);

        // Upload first
        $file = UploadedFile::fake()->image('avatar.jpg');
        $this->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $user->refresh();
        $this->assertNotNull($user->avatar);

        // Remove
        $response = $this->put('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'remove_avatar' => 1,
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertNull($user->avatar);
    }
}
