<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PrestasiPencapaian;
use App\Models\ProyekRiset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrestasiPencapaianApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected string $token;
    protected ProyekRiset $proyek;

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

        $this->proyek = ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset Induk',
            'penulis'                   => 'Dr. Eng. Tika Erna Putri',
            'deskripsi_singkat'         => 'Deskripsi riset induk',
            'dokumentasi_visual_utama'  => 'images/research/default.jpg',
            'tanggal_mulai'             => '2026-01-01',
            'status_proyek'             => 'Sedang Berjalan',
            'admin_id'                  => $this->admin->admin_id,
        ]);
    }

    public function test_public_can_list_prestasi_ordered_by_desc()
    {
        PrestasiPencapaian::create([
            'nama_prestasi'   => 'Prestasi 2025',
            'penulis'         => 'Penulis Lama',
            'gambar_utama'    => 'images/prestasi/lama.jpg',
            'tahun_diperoleh' => 2025,
            'tingkat'         => 'Nasional',
        ]);

        PrestasiPencapaian::create([
            'nama_prestasi'   => 'Prestasi 2026',
            'penulis'         => 'Dr. Budi Santoso',
            'gambar_utama'    => 'images/prestasi/baru.jpg',
            'tahun_diperoleh' => 2026,
            'tingkat'         => 'Internasional',
        ]);

        $response = $this->getJson('/api/prestasi-pencapaian');

        $response->assertStatus(200);
        $this->assertEquals('Prestasi 2026', $response->json('0.nama_prestasi'));
        $this->assertEquals('Dr. Budi Santoso', $response->json('0.penulis'));
    }

    public function test_store_requires_main_image()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/prestasi-pencapaian', [
                'nama_prestasi' => 'Juara Inovasi',
                'penulis'       => 'Dr. Budi Santoso',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_admin_can_store_prestasi_with_manual_author_and_images()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/prestasi-pencapaian', [
                'nama_prestasi'      => 'Juara 1 Lomba Riset Nasional',
                'penulis'            => 'Dr. Budi Santoso, M.T.',
                'penyelenggara'      => 'Kemendikbudristek',
                'tingkat'            => 'Nasional',
                'tahun_diperoleh'    => 2026,
                'deskripsi'          => 'Deskripsi prestasi [[ACHIEVEMENT_IMAGES]]',
                'proyek_id'          => $this->proyek->proyek_id,
                'image'              => UploadedFile::fake()->image('achievement-main.jpg'),
                'supporting_image_1' => UploadedFile::fake()->image('sup1.jpg'),
                'supporting_image_2' => UploadedFile::fake()->image('sup2.jpg'),
                'document'           => UploadedFile::fake()->create('sertifikat.pdf', 500, 'application/pdf'),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.nama_prestasi', 'Juara 1 Lomba Riset Nasional')
            ->assertJsonPath('data.penulis', 'Dr. Budi Santoso, M.T.')
            ->assertJsonPath('data.tahun_diperoleh', 2026)
            ->assertJsonPath('data.proyek_id', $this->proyek->proyek_id);

        $prestasi = PrestasiPencapaian::first();
        $this->assertEquals('Dr. Budi Santoso, M.T.', $prestasi->penulis);
        $this->assertNotNull($prestasi->gambar_utama);
        $this->assertNotNull($prestasi->gambar_pendukung_1);
        $this->assertNotNull($prestasi->gambar_pendukung_2);
        $this->assertNotNull($prestasi->dokumentasi_bukti);

        Storage::disk('public')->assertExists($prestasi->gambar_utama);
        Storage::disk('public')->assertExists($prestasi->gambar_pendukung_1);
        Storage::disk('public')->assertExists($prestasi->gambar_pendukung_2);
        Storage::disk('public')->assertExists($prestasi->dokumentasi_bukti);
    }

    public function test_admin_can_update_prestasi_including_author()
    {
        $prestasi = PrestasiPencapaian::create([
            'nama_prestasi'   => 'Nama Awal',
            'penulis'         => 'Penulis Awal',
            'gambar_utama'    => 'achievements/main.jpg',
            'tahun_diperoleh' => 2025,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/prestasi-pencapaian/' . $prestasi->prestasi_id, [
                'nama_prestasi' => 'Nama Baru',
                'penulis'       => 'Penulis Baru Yang Diedit',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama_prestasi', 'Nama Baru')
            ->assertJsonPath('data.penulis', 'Penulis Baru Yang Diedit');

        $prestasi->refresh();
        $this->assertEquals('Penulis Baru Yang Diedit', $prestasi->penulis);
    }

    public function test_admin_can_delete_prestasi()
    {
        $mainPath = 'achievements/del-main.jpg';
        Storage::disk('public')->put($mainPath, 'content');

        $prestasi = PrestasiPencapaian::create([
            'nama_prestasi' => 'Akan Dihapus',
            'penulis'       => 'Dr. Budi Santoso',
            'gambar_utama'  => $mainPath,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/prestasi-pencapaian/' . $prestasi->prestasi_id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('prestasi_pencapaian', ['prestasi_id' => $prestasi->prestasi_id]);
        Storage::disk('public')->assertMissing($mainPath);
    }
}
