<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials()
    {
        $admin = Admin::create([
            'email'        => 'admin@tplab.com',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Administrator Utama',
            'peran'        => 'Ketua Grup Riset',
            'status_akses' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'admin@tplab.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'admin' => ['admin_id', 'email', 'nama_lengkap', 'peran', 'status_akses', 'last_login']
            ]);

        $admin->refresh();
        $this->assertNotNull($admin->last_login);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        Admin::create([
            'email'        => 'admin@tplab.com',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Administrator Utama',
            'status_akses' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'admin@tplab.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Kredensial login tidak valid.'
            ]);
    }

    public function test_disabled_admin_cannot_login()
    {
        Admin::create([
            'email'        => 'disabled@tplab.com',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Admin Nonaktif',
            'status_akses' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'disabled@tplab.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Akun admin dinonaktifkan atau tidak memiliki izin akses.'
            ]);
    }

    public function test_authenticated_admin_can_access_me_and_logout()
    {
        $admin = Admin::create([
            'email'        => 'admin@tplab.com',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Administrator Utama',
            'status_akses' => true,
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $meResponse->assertStatus(200)
            ->assertJsonPath('data.email', 'admin@tplab.com');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson(['message' => 'Logout berhasil']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
