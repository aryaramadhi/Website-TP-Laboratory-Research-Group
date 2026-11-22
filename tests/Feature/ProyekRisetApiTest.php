<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ProyekRiset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProyekRisetApiTest extends TestCase
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
            'peran'        => 'Ketua Grup Riset',
            'status_akses' => true,
        ]);

        $this->token = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_public_can_list_proyek_riset_ordered_by_desc()
    {
        ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset Lama',
            'penulis'                   => 'Dr. Peneliti Lama',
            'deskripsi_singkat'         => 'Deskripsi riset lama',
            'dokumentasi_visual_utama'  => 'images/research/lama.jpg',
            'tanggal_mulai'             => '2025-01-01',
            'status_proyek'             => 'Selesai',
            'admin_id'                  => $this->admin->admin_id,
        ]);

        ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset Baru',
            'penulis'                   => 'Dr. Peneliti Baru',
            'deskripsi_singkat'         => 'Deskripsi riset baru',
            'dokumentasi_visual_utama'  => 'images/research/baru.jpg',
            'tanggal_mulai'             => '2026-01-01',
            'status_proyek'             => 'Sedang Berjalan',
            'admin_id'                  => $this->admin->admin_id,
        ]);

        $response = $this->getJson('/api/proyek-riset');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'proyek_id',
                        'judul_penelitian_simulasi',
                        'penulis',
                        'deskripsi_singkat',
                        'dokumentasi_visual_utama',
                        'gambar_pendukung_1',
                        'gambar_pendukung_2',
                        'image_url',
                        'supporting_image_1_url',
                        'supporting_image_2_url',
                        'status_proyek',
                        'admin'
                    ]
                ]
            ]);

        $this->assertEquals('Riset Baru', $response->json('data.0.judul_penelitian_simulasi'));
        $this->assertEquals('Dr. Peneliti Baru', $response->json('data.0.penulis'));
    }

    public function test_store_requires_main_image()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/proyek-riset', [
                'judul_penelitian_simulasi' => 'Riset Tanpa Gambar',
                'penulis'                   => 'Dr. Andi',
                'deskripsi_singkat'         => 'Deskripsi riset',
                'tanggal_mulai'             => '2026-01-01',
                'status_proyek'             => 'Sedang Berjalan',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_store_fails_with_unpaired_supporting_images()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/proyek-riset', [
                'judul_penelitian_simulasi' => 'Riset Gambar Ganjil',
                'penulis'                   => 'Dr. Andi',
                'deskripsi_singkat'         => 'Deskripsi riset',
                'tanggal_mulai'             => '2026-01-01',
                'status_proyek'             => 'Sedang Berjalan',
                'image'                     => UploadedFile::fake()->image('main.jpg'),
                'supporting_image_1'        => UploadedFile::fake()->image('sup1.jpg'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supporting_image_1', 'supporting_image_2']);
    }

    public function test_admin_can_store_proyek_riset_with_all_images()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/proyek-riset', [
                'judul_penelitian_simulasi' => 'Riset AI Lengkap',
                'penulis'                   => 'Dr. Andi',
                'deskripsi_singkat'         => 'Deskripsi lengkap riset [[RESEARCH_IMAGES]]',
                'tanggal_mulai'             => '2026-02-01',
                'status_proyek'             => 'Sedang Berjalan',
                'image'                     => UploadedFile::fake()->image('main.png'),
                'supporting_image_1'        => UploadedFile::fake()->image('sup1.png'),
                'supporting_image_2'        => UploadedFile::fake()->image('sup2.png'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.judul_penelitian_simulasi', 'Riset AI Lengkap')
            ->assertJsonPath('data.penulis', 'Dr. Andi');

        $proyek = ProyekRiset::first();
        $this->assertEquals('Dr. Andi', $proyek->penulis);
        $this->assertNotNull($proyek->dokumentasi_visual_utama);
        $this->assertNotNull($proyek->gambar_pendukung_1);
        $this->assertNotNull($proyek->gambar_pendukung_2);
        $this->assertStringStartsWith('/storage/', $proyek->image_url);
        $this->assertStringStartsWith('/storage/', $proyek->supporting_image_1_url);
        $this->assertStringStartsWith('/storage/', $proyek->supporting_image_2_url);

        Storage::disk('public')->assertExists($proyek->dokumentasi_visual_utama);
        Storage::disk('public')->assertExists($proyek->gambar_pendukung_1);
        Storage::disk('public')->assertExists($proyek->gambar_pendukung_2);
    }

    public function test_admin_can_update_proyek_riset_via_post_and_put()
    {
        $proyek = ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Judul Awal',
            'penulis'                   => 'Penulis Awal',
            'deskripsi_singkat'         => 'Deskripsi awal',
            'dokumentasi_visual_utama'  => 'researches/awal.jpg',
            'tanggal_mulai'             => '2026-01-01',
            'status_proyek'             => 'Sedang Berjalan',
            'admin_id'                  => $this->admin->admin_id,
        ]);

        $responsePut = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/proyek-riset/' . $proyek->proyek_id, [
                'judul_penelitian_simulasi' => 'Judul Diperbarui PUT',
                'status_proyek'             => 'Selesai',
            ]);

        $responsePut->assertStatus(200)
            ->assertJsonPath('data.judul_penelitian_simulasi', 'Judul Diperbarui PUT')
            ->assertJsonPath('data.status_proyek', 'Selesai');

        $responsePost = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/proyek-riset/' . $proyek->proyek_id, [
                '_method'                   => 'PUT',
                'judul_penelitian_simulasi' => 'Judul Diperbarui POST Multipart',
            ]);

        $responsePost->assertStatus(200)
            ->assertJsonPath('data.judul_penelitian_simulasi', 'Judul Diperbarui POST Multipart');
    }

    public function test_admin_can_remove_supporting_images()
    {
        $path1 = 'researches/gallery/temp1.jpg';
        $path2 = 'researches/gallery/temp2.jpg';
        Storage::disk('public')->put($path1, 'dummy1');
        Storage::disk('public')->put($path2, 'dummy2');

        $proyek = ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset Dengan Pendukung',
            'penulis'                   => 'Dr. Andi',
            'deskripsi_singkat'         => 'Deskripsi',
            'dokumentasi_visual_utama'  => 'researches/main.jpg',
            'gambar_pendukung_1'        => $path1,
            'gambar_pendukung_2'        => $path2,
            'tanggal_mulai'             => '2026-01-01',
            'status_proyek'             => 'Sedang Berjalan',
            'admin_id'                  => $this->admin->admin_id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/proyek-riset/' . $proyek->proyek_id, [
                'remove_supporting_image_1' => 'true',
            ]);

        $response->assertStatus(200);

        $proyek->refresh();
        $this->assertNull($proyek->gambar_pendukung_1);
        $this->assertNull($proyek->gambar_pendukung_2);
        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertMissing($path2);

        $path3 = 'researches/gallery/temp3.jpg';
        $path4 = 'researches/gallery/temp4.jpg';
        Storage::disk('public')->put($path3, 'dummy3');
        Storage::disk('public')->put($path4, 'dummy4');

        $proyek->update([
            'gambar_pendukung_1' => $path3,
            'gambar_pendukung_2' => $path4,
        ]);

        $responsePair = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/proyek-riset/' . $proyek->proyek_id, [
                'remove_supporting_image_1' => '1',
                'remove_supporting_image_2' => '1',
            ]);

        $responsePair->assertStatus(200);
        $proyek->refresh();
        $this->assertNull($proyek->gambar_pendukung_1);
        $this->assertNull($proyek->gambar_pendukung_2);
        Storage::disk('public')->assertMissing($path3);
        Storage::disk('public')->assertMissing($path4);
    }

    public function test_admin_can_delete_proyek_riset()
    {
        $mainPath = 'researches/main-delete.jpg';
        Storage::disk('public')->put($mainPath, 'content');

        $proyek = ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset Akan Dihapus',
            'penulis'                   => 'Dr. Andi',
            'deskripsi_singkat'         => 'Deskripsi',
            'dokumentasi_visual_utama'  => $mainPath,
            'tanggal_mulai'             => '2026-01-01',
            'status_proyek'             => 'Sedang Berjalan',
            'admin_id'                  => $this->admin->admin_id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/proyek-riset/' . $proyek->proyek_id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Proyek riset berhasil dihapus']);

        $this->assertDatabaseMissing('proyek_riset', ['proyek_id' => $proyek->proyek_id]);
        Storage::disk('public')->assertMissing($mainPath);
    }
}
