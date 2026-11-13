<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AnggotaAlumni;
use App\Models\BeritaIsu;
use App\Models\PrestasiPencapaian;
use App\Models\ProyekRiset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CmsImageUploadAndSortingTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected AnggotaAlumni $alumni;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = Admin::firstOrCreate(
            ['email' => 'admin@tplab.com'],
            [
                'nama_lengkap' => 'Administrator Utama',
                'password' => bcrypt('password123'),
                'peran' => 'Admin',
                'status_akses' => true,
            ]
        );

        $this->alumni = AnggotaAlumni::firstOrCreate(
            ['nama_lengkap' => 'Dr. Budi Santoso'],
            [
                'kepakaran' => 'Artificial Intelligence & IoT',
                'peran_lab' => 'Peneliti Utama',
                'tahun_masuk' => 2020,
                'admin_id' => $this->admin->admin_id,
            ]
        );

        Sanctum::actingAs($this->admin, ['*']);
    }

    public function test_research_sorting_latest_first(): void
    {
        $older = ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset Lama',
            'deskripsi_singkat' => 'Deskripsi Lama',
            'tanggal_mulai' => '2025-01-01',
            'status_proyek' => 'Selesai',
            'admin_id' => $this->admin->admin_id,
            'created_at' => now()->subDays(5),
        ]);

        $newer = ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset Terbaru',
            'deskripsi_singkat' => 'Deskripsi Baru',
            'tanggal_mulai' => '2026-01-01',
            'status_proyek' => 'Sedang Berjalan',
            'admin_id' => $this->admin->admin_id,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/proyek-riset');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals($newer->proyek_id, $data[0]['proyek_id']);

        // Clean up test items
        $older->delete();
        $newer->delete();
    }

    public function test_research_main_and_supporting_images_upload(): void
    {
        $mainFile = UploadedFile::fake()->image('main.jpg', 600, 400);
        $supp1File = UploadedFile::fake()->image('supp1.png', 400, 300);
        $supp2File = UploadedFile::fake()->image('supp2.webp', 400, 300);

        $response = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Riset Dengan Gambar Lengkap',
            'deskripsi_singkat' => 'Deskripsi riset pengujian upload gambar.',
            'tanggal_mulai' => '2026-11-06',
            'status_proyek' => 'Sedang Berjalan',
            'admin_id' => $this->admin->admin_id,
            'image' => $mainFile,
            'supporting_image_1' => $supp1File,
            'supporting_image_2' => $supp2File,
        ]);

        $response->assertStatus(201);
        $resData = $response->json('data');
        $proyekId = $resData['proyek_id'];

        $proyek = ProyekRiset::findOrFail($proyekId);
        $this->assertNotNull($proyek->dokumentasi_visual_utama);
        $this->assertNotNull($proyek->gambar_pendukung_1);
        $this->assertNotNull($proyek->gambar_pendukung_2);

        Storage::disk('public')->assertExists($proyek->dokumentasi_visual_utama);
        Storage::disk('public')->assertExists($proyek->gambar_pendukung_1);
        Storage::disk('public')->assertExists($proyek->gambar_pendukung_2);

        $this->assertStringStartsWith('/storage/researches', $proyek->image_url);
        $this->assertStringStartsWith('/storage/researches/gallery', $proyek->supporting_image_1_url);
        $this->assertStringStartsWith('/storage/researches/gallery', $proyek->supporting_image_2_url);

        // Test update without changing images preserves existing files
        $updateResponse = $this->postJson("/api/proyek-riset/{$proyekId}", [
            '_method' => 'PUT',
            'judul_penelitian_simulasi' => 'Riset Dengan Gambar Lengkap (Updated)',
        ]);
        $updateResponse->assertStatus(200);

        $proyek->refresh();
        $this->assertEquals('Riset Dengan Gambar Lengkap (Updated)', $proyek->judul_penelitian_simulasi);
        Storage::disk('public')->assertExists($proyek->dokumentasi_visual_utama);

        // Test delete cleans up stored files
        $deleteResponse = $this->deleteJson("/api/proyek-riset/{$proyekId}");
        $deleteResponse->assertStatus(200);

        Storage::disk('public')->assertMissing($proyek->dokumentasi_visual_utama);
        Storage::disk('public')->assertMissing($proyek->gambar_pendukung_1);
        Storage::disk('public')->assertMissing($proyek->gambar_pendukung_2);
    }

    public function test_news_image_upload_and_update(): void
    {
        $mainFile = UploadedFile::fake()->image('news_main.jpg');
        $suppFile = UploadedFile::fake()->image('news_supp.jpg');

        $response = $this->postJson('/api/berita-isu', [
            'judul_berita' => 'Berita Peluncuran Prototipe',
            'tanggal_rilis' => '2026-11-06',
            'isi_artikel_ringkas' => 'Isi berita dengan penanda [[NEWS_IMAGES]] di dalamnya.',
            'topik_utama' => 'Riset',
            'admin_id' => $this->admin->admin_id,
            'image' => $mainFile,
            'supporting_image_1' => $suppFile,
        ]);

        $response->assertStatus(201);
        $newsId = $response->json('data.berita_id');
        $news = BeritaIsu::findOrFail($newsId);

        $this->assertNotNull($news->gambar_pendukung);
        $this->assertNotNull($news->gambar_pendukung_1);
        Storage::disk('public')->assertExists($news->gambar_pendukung);
        Storage::disk('public')->assertExists($news->gambar_pendukung_1);

        // Clean up
        $news->delete();
    }

    public function test_achievement_image_mapping_distinguishes_photo_and_pdf(): void
    {
        $photoFile = UploadedFile::fake()->image('trophy.jpg');
        $pdfFile = UploadedFile::fake()->create('certificate.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Juara 1 Hackathon IoT',
            'tahun_diperoleh' => 2026,
            'deskripsi' => 'Penghargaan karya terbaik.',
            'anggota_alumni_id' => $this->alumni->anggota_alumni_id,
            'image' => $photoFile,
            'dokumentasi_bukti' => $pdfFile,
        ]);

        $response->assertStatus(201);
        $prestasiId = $response->json('data.prestasi_id');
        $prestasi = PrestasiPencapaian::findOrFail($prestasiId);

        $this->assertNotNull($prestasi->gambar_utama);
        $this->assertNotNull($prestasi->dokumentasi_bukti);

        // Photo must be stored in achievements and accessed as image_url
        $this->assertStringContainsString('achievements/', $prestasi->gambar_utama);
        $this->assertStringStartsWith('/storage/achievements/', $prestasi->image_url);

        // Document must be accessed as document_url
        $this->assertNotNull($prestasi->document_url);
        $this->assertStringEndsWith('.pdf', $prestasi->document_url);

        // Clean up
        $prestasi->delete();
    }

    public function test_updating_text_only_preserves_all_existing_images(): void
    {
        $mainFile = UploadedFile::fake()->image('main.jpg', 800, 600);
        $supp1File = UploadedFile::fake()->image('supp1.png', 600, 400);

        $createRes = $this->postJson('/api/berita-isu', [
            'judul_berita' => 'Berita Awal',
            'isi_artikel_ringkas' => "Paragraf 1\n\n[[NEWS_IMAGES]]\n\nParagraf 2",
            'tanggal_rilis' => '2026-11-10',
            'admin_id' => $this->admin->admin_id,
            'image' => $mainFile,
            'supporting_image_1' => $supp1File,
        ]);

        $createRes->assertStatus(201);
        $news = BeritaIsu::findOrFail($createRes->json('data.berita_id'));
        $origMain = $news->gambar_pendukung;
        $origSupp1 = $news->gambar_pendukung_1;

        Storage::disk('public')->assertExists($origMain);
        Storage::disk('public')->assertExists($origSupp1);

        $updateRes = $this->postJson("/api/berita-isu/{$news->berita_id}", [
            '_method' => 'PUT',
            'judul_berita' => 'Berita Judul Baru',
            'isi_artikel_ringkas' => "Paragraf Diperbarui [[NEWS_IMAGES]]",
        ]);

        $updateRes->assertStatus(200);
        $news->refresh();
        $this->assertEquals($origMain, $news->gambar_pendukung);
        $this->assertEquals($origSupp1, $news->gambar_pendukung_1);
        Storage::disk('public')->assertExists($origMain);
        Storage::disk('public')->assertExists($origSupp1);
    }

    public function test_old_image_deleted_only_after_replacement_when_unused_and_preserved_when_shared(): void
    {
        $sharedFile = UploadedFile::fake()->image('shared.jpg', 800, 600);
        $createRes = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Riset Pertama',
            'deskripsi_singkat' => 'Deskripsi [[RESEARCH_IMAGES]]',
            'tanggal_mulai' => '2026-11-10',
            'status_proyek' => 'Sedang Berjalan',
            'admin_id' => $this->admin->admin_id,
            'image' => $sharedFile,
        ]);

        $createRes->assertStatus(201);
        $riset1 = ProyekRiset::findOrFail($createRes->json('data.proyek_id'));
        $sharedPath = $riset1->dokumentasi_visual_utama;
        Storage::disk('public')->assertExists($sharedPath);

        $riset2 = ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Riset Kedua Berbagi Gambar',
            'deskripsi_singkat' => 'Menggunakan path sama',
            'tanggal_mulai' => '2026-11-11',
            'status_proyek' => 'Sedang Berjalan',
            'admin_id' => $this->admin->admin_id,
            'dokumentasi_visual_utama' => $sharedPath,
        ]);

        $replacementFile = UploadedFile::fake()->image('replacement.jpg', 800, 600);
        $replaceRes = $this->postJson("/api/proyek-riset/{$riset1->proyek_id}", [
            '_method' => 'PUT',
            'judul_penelitian_simulasi' => 'Riset Pertama Diganti Gambar',
            'image' => $replacementFile,
        ]);

        $replaceRes->assertStatus(200);
        $riset1->refresh();
        Storage::disk('public')->assertExists($riset1->dokumentasi_visual_utama);
        // Shared path must remain on disk because riset2 still references it.
        Storage::disk('public')->assertExists($sharedPath);

        $this->deleteJson("/api/proyek-riset/{$riset2->proyek_id}")->assertStatus(200);
        Storage::disk('public')->assertMissing($sharedPath);
    }

    public function test_image_validation_rejects_invalid_types_and_excessive_size(): void
    {
        $textDoc = UploadedFile::fake()->create('not_an_image.txt', 10, 'text/plain');
        $largeImage = UploadedFile::fake()->create('giant.jpg', 3000, 'image/jpeg'); // 3MB > 2MB

        // Invalid type rejection
        $response1 = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Test Gagal Format',
            'deskripsi_singkat' => 'Test',
            'tanggal_mulai' => '2026-11-06',
            'status_proyek' => 'Sedang Berjalan',
            'admin_id' => $this->admin->admin_id,
            'image' => $textDoc,
        ]);
        $response1->assertStatus(422);
        $response1->assertJsonValidationErrors('image');

        // Large file rejection
        $response2 = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Test Gagal Ukuran',
            'deskripsi_singkat' => 'Test',
            'tanggal_mulai' => '2026-11-06',
            'status_proyek' => 'Sedang Berjalan',
            'admin_id' => $this->admin->admin_id,
            'image' => $largeImage,
        ]);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors('image');
    }

    public function test_achievement_proof_document_rejects_invalid_or_oversized_files_and_preserves_old_file(): void
    {
        $photoFile = UploadedFile::fake()->image('trophy.jpg');
        $pdfFile = UploadedFile::fake()->create('lama.pdf', 500, 'application/pdf');

        $createRes = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Juara 1 Simulasi Energi',
            'tahun_diperoleh' => 2026,
            'deskripsi' => 'Dokumen bukti awal valid.',
            'anggota_alumni_id' => $this->alumni->anggota_alumni_id,
            'image' => $photoFile,
            'dokumentasi_bukti' => $pdfFile,
        ]);

        $createRes->assertStatus(201);
        $prestasi = PrestasiPencapaian::findOrFail($createRes->json('data.prestasi_id'));
        $origMainPath = $prestasi->gambar_utama;
        $origProofPath = $prestasi->dokumentasi_bukti;

        Storage::disk('public')->assertExists($origMainPath);
        Storage::disk('public')->assertExists($origProofPath);

        $dangerousFile = UploadedFile::fake()->create('exploit.php', 50, 'application/x-httpd-php');
        $storeInvalidRes = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Percobaan Upload Berbahaya',
            'tahun_diperoleh' => 2026,
            'anggota_alumni_id' => $this->alumni->anggota_alumni_id,
            'dokumentasi_bukti' => $dangerousFile,
        ]);
        $storeInvalidRes->assertStatus(422);
        $storeInvalidRes->assertJsonValidationErrors('dokumentasi_bukti');

        $oversizedPdf = UploadedFile::fake()->create('besar.pdf', 3000, 'application/pdf');
        $updateOversizedRes = $this->postJson("/api/prestasi-pencapaian/{$prestasi->prestasi_id}", [
            '_method' => 'PUT',
            'nama_prestasi' => 'Gagal Ukuran PDF',
            'dokumentasi_bukti' => $oversizedPdf,
        ]);
        $updateOversizedRes->assertStatus(422);
        $updateOversizedRes->assertJsonValidationErrors('dokumentasi_bukti');

        $exeFile = UploadedFile::fake()->create('script.exe', 100, 'application/octet-stream');
        $updateDangerousRes = $this->postJson("/api/prestasi-pencapaian/{$prestasi->prestasi_id}", [
            '_method' => 'PUT',
            'nama_prestasi' => 'Gagal Ekstensi EXE',
            'dokumentasi_bukti' => $exeFile,
        ]);
        $updateDangerousRes->assertStatus(422);
        $updateDangerousRes->assertJsonValidationErrors('dokumentasi_bukti');

        $prestasi->refresh();
        $this->assertEquals($origProofPath, $prestasi->dokumentasi_bukti);
        $this->assertEquals($origMainPath, $prestasi->gambar_utama);
        Storage::disk('public')->assertExists($origProofPath);
        Storage::disk('public')->assertExists($origMainPath);

        $updateWithoutDocRes = $this->postJson("/api/prestasi-pencapaian/{$prestasi->prestasi_id}", [
            '_method' => 'PUT',
            'nama_prestasi' => 'Juara 1 Simulasi Energi Updated',
        ]);
        $updateWithoutDocRes->assertStatus(200);
        $prestasi->refresh();
        $this->assertEquals('Juara 1 Simulasi Energi Updated', $prestasi->nama_prestasi);
        $this->assertEquals($origProofPath, $prestasi->dokumentasi_bukti);
        $this->assertEquals($origMainPath, $prestasi->gambar_utama);
        Storage::disk('public')->assertExists($origProofPath);
        Storage::disk('public')->assertExists($origMainPath);
    }

    public function test_achievement_proof_document_accepts_legacy_string_path_and_rejects_unsafe_string(): void
    {
        $validRes = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Prestasi Path Legacy',
            'tahun_diperoleh' => 2025,
            'anggota_alumni_id' => $this->alumni->anggota_alumni_id,
            'dokumentasi_bukti' => 'sertifikat_juara1.pdf',
        ]);
        $validRes->assertStatus(201);
        $this->assertDatabaseHas('prestasi_pencapaian', [
            'prestasi_id' => $validRes->json('data.prestasi_id'),
            'dokumentasi_bukti' => 'sertifikat_juara1.pdf',
        ]);

        $validImageProofRes = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Prestasi Upload Gambar Bukti',
            'tahun_diperoleh' => 2026,
            'anggota_alumni_id' => $this->alumni->anggota_alumni_id,
            'dokumentasi_bukti' => UploadedFile::fake()->image('bukti_legacy.png'),
        ]);
        $validImageProofRes->assertStatus(201);

        $unsafeInputs = [
            'malware.exe',
            '../../etc/passwd.pdf',
            '..\\secret.pdf',
            'https://evil.com/fake.pdf',
            'JavaScript:alert(1)/a.pdf',
        ];

        foreach ($unsafeInputs as $unsafeInput) {
            $unsafeRes = $this->postJson('/api/prestasi-pencapaian', [
                'nama_prestasi' => 'Prestasi Input Tidak Aman',
                'tahun_diperoleh' => 2025,
                'anggota_alumni_id' => $this->alumni->anggota_alumni_id,
                'dokumentasi_bukti' => $unsafeInput,
            ]);
            $unsafeRes->assertStatus(422);
            $unsafeRes->assertJsonValidationErrors('dokumentasi_bukti');
        }
    }

    public function test_public_pages_regression(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/riset')->assertStatus(200);
        $this->get('/publikasi')->assertStatus(200);
        $this->get('/berita')->assertStatus(200);
        $this->get('/prestasi')->assertStatus(200);
    }
}
