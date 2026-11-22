<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ProyekRiset;
use App\Models\PublikasiHibah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublikasiHibahApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected string $token;
    protected ProyekRiset $proyek;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'email'        => 'admin@tplab.com',
            'password'     => Hash::make('password123'),
            'nama_lengkap' => 'Administrator Utama',
            'status_akses' => true,
        ]);

        $this->token = $this->admin->createToken('test-token')->plainTextToken;

        $this->proyek = ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset AI',
            'penulis'                   => 'Dr. Eng. Tika Erna Putri',
            'deskripsi_singkat'         => 'Deskripsi riset',
            'tanggal_mulai'             => '2026-01-01',
            'status_proyek'             => 'Sedang Berjalan',
            'admin_id'                  => $this->admin->admin_id,
        ]);
    }

    public function test_public_can_list_publikasi_ordered_by_year_desc()
    {
        PublikasiHibah::create([
            'judul_karya_hibah' => 'Publikasi 2025',
            'penulis'           => 'Prof. Hendra',
            'tahun'             => 2025,
            'admin_id'          => $this->admin->admin_id,
        ]);

        PublikasiHibah::create([
            'judul_karya_hibah' => 'Publikasi 2026',
            'penulis'           => 'Dr. Eng. Tika Erna Putri',
            'tahun'             => 2026,
            'admin_id'          => $this->admin->admin_id,
        ]);

        $response = $this->getJson('/api/publikasi-hibah');

        $response->assertStatus(200);
        $this->assertEquals(2026, $response->json('0.tahun'));
        $this->assertEquals('Publikasi 2026', $response->json('0.judul_karya_hibah'));
        $this->assertEquals('Dr. Eng. Tika Erna Putri', $response->json('0.penulis'));
    }

    public function test_admin_can_store_publikasi_and_sanitizes_dummy_link()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/publikasi-hibah', [
                'jenis'                => 'Publikasi Jurnal',
                'judul_karya_hibah'    => 'Penerapan Deep Learning pada Citra Medis',
                'penulis'              => 'Prof. Hendra',
                'nama_jurnal_skema'    => 'IEEE Transactions on Medical Imaging',
                'tahun'                => 2026,
                'deskripsi_pencapaian' => 'Deskripsi publikasi riset',
                'tautan_eksternal'     => 'https://',
                'proyek_id'            => $this->proyek->proyek_id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.judul_karya_hibah', 'Penerapan Deep Learning pada Citra Medis')
            ->assertJsonPath('data.penulis', 'Prof. Hendra')
            ->assertJsonPath('data.tautan_eksternal', null);

        $this->assertDatabaseHas('publikasi_hibah', [
            'judul_karya_hibah' => 'Penerapan Deep Learning pada Citra Medis',
            'penulis'           => 'Prof. Hendra',
            'tautan_eksternal'  => null,
            'proyek_id'         => $this->proyek->proyek_id,
        ]);
    }

    public function test_admin_can_update_publikasi()
    {
        $publikasi = PublikasiHibah::create([
            'judul_karya_hibah' => 'Judul Awal',
            'penulis'           => 'Penulis Awal',
            'tahun'             => 2025,
            'admin_id'          => $this->admin->admin_id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/publikasi-hibah/' . $publikasi->publikasi_hibah_id, [
                'judul_karya_hibah' => 'Judul Baru Diperbarui',
                'tahun'             => 2026,
                'tautan_eksternal'  => 'https://doi.org/10.1000/xyz123',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.judul_karya_hibah', 'Judul Baru Diperbarui')
            ->assertJsonPath('data.tahun', 2026)
            ->assertJsonPath('data.tautan_eksternal', 'https://doi.org/10.1000/xyz123');
    }

    public function test_admin_can_delete_publikasi()
    {
        $publikasi = PublikasiHibah::create([
            'judul_karya_hibah' => 'Publikasi Dihapus',
            'penulis'           => 'Penulis Dihapus',
            'admin_id'          => $this->admin->admin_id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/publikasi-hibah/' . $publikasi->publikasi_hibah_id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('publikasi_hibah', ['publikasi_hibah_id' => $publikasi->publikasi_hibah_id]);
    }

    public function test_admin_cannot_store_or_update_publikasi_with_invalid_external_url()
    {
        $storeResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/publikasi-hibah', [
                'judul_karya_hibah' => 'Publikasi Link Tidak Valid',
                'penulis'           => 'Prof. Hendra',
                'nama_jurnal_skema' => 'Jurnal Uji',
                'tahun'             => 2026,
                'tautan_eksternal'  => 'bukan-url-valid',
            ]);

        $storeResponse->assertStatus(422)
            ->assertJsonValidationErrors(['tautan_eksternal']);

        $publikasi = PublikasiHibah::create([
            'judul_karya_hibah' => 'Publikasi Valid Awal',
            'penulis'           => 'Prof. Hendra',
            'tahun'             => 2026,
            'admin_id'          => $this->admin->admin_id,
        ]);

        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/publikasi-hibah/' . $publikasi->publikasi_hibah_id, [
                'tautan_eksternal' => 'bukan-url-valid',
            ]);

        $updateResponse->assertStatus(422)
            ->assertJsonValidationErrors(['tautan_eksternal']);
    }
}
