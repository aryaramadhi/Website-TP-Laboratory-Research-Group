<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BeritaIsu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BeritaIsuApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = Admin::create([
            'email'        => 'admin@tplab.com',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Administrator Utama',
            'status_akses' => true,
        ]);

        $this->token = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_public_can_list_berita_ordered_by_desc()
    {
        BeritaIsu::create([
            'judul_berita'        => 'Berita Lama',
            'tanggal_rilis'       => '2025-01-01',
            'isi_artikel_ringkas' => 'Konten lama',
            'gambar_pendukung'    => 'images/news/lama.jpg',
            'topik_utama'         => 'Riset',
            'admin_id'            => $this->admin->admin_id,
        ]);

        BeritaIsu::create([
            'judul_berita'        => 'Berita Baru',
            'tanggal_rilis'       => '2026-03-01',
            'isi_artikel_ringkas' => 'Konten baru',
            'gambar_pendukung'    => 'images/news/baru.jpg',
            'topik_utama'         => 'Prestasi Lab',
            'admin_id'            => $this->admin->admin_id,
        ]);

        $response = $this->getJson('/api/berita-isu');

        $response->assertStatus(200);
        $this->assertEquals('Berita Baru', $response->json('0.judul_berita'));
        $this->assertNotNull($response->json('0.image_url'));
    }

    public function test_store_berita_requires_main_image()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/berita-isu', [
                'judul_berita' => 'Berita Tanpa Gambar',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_berita_fails_with_unpaired_supporting_images()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/berita-isu', [
                'judul_berita'       => 'Berita Pasangan Gambar Tidak Lengkap',
                'image'              => UploadedFile::fake()->image('main.jpg'),
                'supporting_image_1' => UploadedFile::fake()->image('sup1.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supporting_image_1', 'supporting_image_2']);
    }

    public function test_admin_can_store_berita_with_images()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/berita-isu', [
                'judul_berita'        => 'Berita Teknologi Terkini',
                'tanggal_rilis'       => '2026-03-15',
                'isi_artikel_ringkas' => 'Isi ringkas artikel [[NEWS_IMAGES]]',
                'topik_utama'         => 'Teknologi BIPV',
                'image'               => UploadedFile::fake()->image('news-main.jpg'),
                'supporting_image_1'  => UploadedFile::fake()->image('sup1.jpg'),
                'supporting_image_2'  => UploadedFile::fake()->image('sup2.jpg'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.judul_berita', 'Berita Teknologi Terkini');

        $berita = BeritaIsu::first();
        $this->assertNotNull($berita->gambar_pendukung);
        $this->assertNotNull($berita->gambar_pendukung_1);
        $this->assertNotNull($berita->gambar_pendukung_2);

        Storage::disk('public')->assertExists($berita->gambar_pendukung);
        Storage::disk('public')->assertExists($berita->gambar_pendukung_1);
        Storage::disk('public')->assertExists($berita->gambar_pendukung_2);
    }

    public function test_admin_can_update_berita()
    {
        $berita = BeritaIsu::create([
            'judul_berita'     => 'Judul Awal',
            'gambar_pendukung' => 'berita-isu/main.jpg',
            'admin_id'         => $this->admin->admin_id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/berita-isu/' . $berita->berita_id, [
                'judul_berita' => 'Judul Berita Diperbarui',
                'topik_utama'  => 'Prestasi Lab',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.judul_berita', 'Judul Berita Diperbarui');
    }

    public function test_admin_can_delete_berita()
    {
        $path = 'berita-isu/delete.jpg';
        Storage::disk('public')->put($path, 'content');

        $berita = BeritaIsu::create([
            'judul_berita'     => 'Berita Dihapus',
            'gambar_pendukung' => $path,
            'admin_id'         => $this->admin->admin_id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/berita-isu/' . $berita->berita_id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('berita_isu', ['berita_id' => $berita->berita_id]);
        Storage::disk('public')->assertMissing($path);
    }
}
