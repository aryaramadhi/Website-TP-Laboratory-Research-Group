<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BeritaIsu;
use App\Models\PrestasiPencapaian;
use App\Models\ProyekRiset;
use App\Models\PublikasiHibah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CmsImageUploadAndSortingTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

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
            'penulis' => 'Dr. Peneliti Utama',
            'deskripsi_singkat' => 'Deskripsi riset pengujian upload gambar.',
            'tanggal_mulai' => '2026-11-06',
            'status_proyek' => 'Sedang Berjalan',
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
        $supp1File = UploadedFile::fake()->image('news_supp1.jpg');
        $supp2File = UploadedFile::fake()->image('news_supp2.jpg');

        $response = $this->postJson('/api/berita-isu', [
            'judul_berita' => 'Berita Peluncuran Prototipe',
            'penulis' => 'Tim Redaksi TP Lab',
            'tanggal_rilis' => '2026-11-06',
            'isi_artikel_ringkas' => 'Isi berita dengan penanda [[NEWS_IMAGES]] di dalamnya.',
            'topik_utama' => 'Riset',
            'image' => $mainFile,
            'supporting_image_1' => $supp1File,
            'supporting_image_2' => $supp2File,
        ]);

        $response->assertStatus(201);
        $newsId = $response->json('data.berita_id');
        $news = BeritaIsu::findOrFail($newsId);

        $this->assertNotNull($news->gambar_pendukung);
        $this->assertNotNull($news->gambar_pendukung_1);
        $this->assertNotNull($news->gambar_pendukung_2);
        Storage::disk('public')->assertExists($news->gambar_pendukung);
        Storage::disk('public')->assertExists($news->gambar_pendukung_1);
        Storage::disk('public')->assertExists($news->gambar_pendukung_2);

        // Clean up
        $news->delete();
    }

    public function test_achievement_image_mapping_distinguishes_photo_and_pdf(): void
    {
        $photoFile = UploadedFile::fake()->image('trophy.jpg');
        $pdfFile = UploadedFile::fake()->create('certificate.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Juara 1 Hackathon IoT',
            'penulis' => 'Dr. Budi Santoso',
            'tahun_diperoleh' => 2026,
            'deskripsi' => 'Penghargaan karya terbaik.',
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
        $supp2File = UploadedFile::fake()->image('supp2.png', 600, 400);

        $createRes = $this->postJson('/api/berita-isu', [
            'judul_berita' => 'Berita Awal',
            'penulis' => 'Tim Redaksi TP Lab',
            'isi_artikel_ringkas' => "Paragraf 1\n\n[[NEWS_IMAGES]]\n\nParagraf 2",
            'tanggal_rilis' => '2026-11-10',
            'image' => $mainFile,
            'supporting_image_1' => $supp1File,
            'supporting_image_2' => $supp2File,
        ]);

        $createRes->assertStatus(201);
        $news = BeritaIsu::findOrFail($createRes->json('data.berita_id'));
        $origMain = $news->gambar_pendukung;
        $origSupp1 = $news->gambar_pendukung_1;
        $origSupp2 = $news->gambar_pendukung_2;

        Storage::disk('public')->assertExists($origMain);
        Storage::disk('public')->assertExists($origSupp1);
        Storage::disk('public')->assertExists($origSupp2);

        $updateRes = $this->postJson("/api/berita-isu/{$news->berita_id}", [
            '_method' => 'PUT',
            'judul_berita' => 'Berita Judul Baru',
            'isi_artikel_ringkas' => "Paragraf Diperbarui [[NEWS_IMAGES]]",
        ]);

        $updateRes->assertStatus(200);
        $news->refresh();
        $this->assertEquals($origMain, $news->gambar_pendukung);
        $this->assertEquals($origSupp1, $news->gambar_pendukung_1);
        $this->assertEquals($origSupp2, $news->gambar_pendukung_2);
        Storage::disk('public')->assertExists($origMain);
        Storage::disk('public')->assertExists($origSupp1);
        Storage::disk('public')->assertExists($origSupp2);
    }

    public function test_old_image_deleted_only_after_replacement_when_unused_and_preserved_when_shared(): void
    {
        $sharedFile = UploadedFile::fake()->image('shared.jpg', 800, 600);
        $createRes = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Riset Pertama',
            'penulis' => 'Dr. Peneliti Utama',
            'deskripsi_singkat' => 'Deskripsi [[RESEARCH_IMAGES]]',
            'tanggal_mulai' => '2026-11-10',
            'status_proyek' => 'Sedang Berjalan',
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
            'penulis' => 'Dr. Peneliti Utama',
            'deskripsi_singkat' => 'Test',
            'tanggal_mulai' => '2026-11-06',
            'status_proyek' => 'Sedang Berjalan',
            'image' => $textDoc,
        ]);
        $response1->assertStatus(422);
        $response1->assertJsonValidationErrors('image');

        // Large file rejection
        $response2 = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Test Gagal Ukuran',
            'penulis' => 'Dr. Peneliti Utama',
            'deskripsi_singkat' => 'Test',
            'tanggal_mulai' => '2026-11-06',
            'status_proyek' => 'Sedang Berjalan',
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
            'penulis' => 'Dr. Budi Santoso',
            'tahun_diperoleh' => 2026,
            'deskripsi' => 'Dokumen bukti awal valid.',
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
            'penulis' => 'Dr. Budi Santoso',
            'tahun_diperoleh' => 2026,
            'dokumentasi_bukti' => $dangerousFile,
        ]);
        $storeInvalidRes->assertStatus(422);
        $storeInvalidRes->assertJsonValidationErrors('dokumentasi_bukti');

        $pdf4Mb = UploadedFile::fake()->create('sertifikat_4mb.pdf', 4096, 'application/pdf');
        $update4MbRes = $this->postJson("/api/prestasi-pencapaian/{$prestasi->prestasi_id}", [
            '_method' => 'PUT',
            'nama_prestasi' => 'Juara 1 Simulasi Energi PDF 4MB',
            'document' => $pdf4Mb,
        ]);
        $update4MbRes->assertStatus(200);
        $prestasi->refresh();
        $origProofPath = $prestasi->dokumentasi_bukti;
        Storage::disk('public')->assertExists($origProofPath);

        $oversizedPdf = UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf');
        $updateOversizedRes = $this->postJson("/api/prestasi-pencapaian/{$prestasi->prestasi_id}", [
            '_method' => 'PUT',
            'nama_prestasi' => 'Gagal Ukuran PDF',
            'dokumentasi_bukti' => $oversizedPdf,
        ]);
        $updateOversizedRes->assertStatus(422);
        $updateOversizedRes->assertJsonValidationErrors('dokumentasi_bukti');

        $oversizedDocField = UploadedFile::fake()->create('besar_doc.pdf', 6000, 'application/pdf');
        $updateOversizedDocRes = $this->postJson("/api/prestasi-pencapaian/{$prestasi->prestasi_id}", [
            '_method' => 'PUT',
            'nama_prestasi' => 'Gagal Ukuran Document',
            'document' => $oversizedDocField,
        ]);
        $updateOversizedDocRes->assertStatus(422);
        $updateOversizedDocRes->assertJsonValidationErrors('document');

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
            'penulis' => 'Dr. Budi Santoso',
            'tahun_diperoleh' => 2025,
            'dokumentasi_bukti' => 'sertifikat_juara1.pdf',
        ]);
        $validRes->assertStatus(201);
        $this->assertDatabaseHas('prestasi_pencapaian', [
            'prestasi_id' => $validRes->json('data.prestasi_id'),
            'dokumentasi_bukti' => 'sertifikat_juara1.pdf',
        ]);

        $validImageProofRes = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Prestasi Upload Gambar Bukti',
            'penulis' => 'Dr. Budi Santoso',
            'tahun_diperoleh' => 2026,
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
                'penulis' => 'Dr. Budi Santoso',
                'tahun_diperoleh' => 2025,
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

    public function test_paired_supporting_and_mandatory_main_image_rules_across_cms_modules(): void
    {
        $modules = [
            [
                'endpoint' => '/api/proyek-riset',
                'model' => ProyekRiset::class,
                'idField' => 'proyek_id',
                'mainCol' => 'dokumentasi_visual_utama',
                'createPayload' => [
                    'judul_penelitian_simulasi' => 'Riset Pair UX',
                    'penulis' => 'Dr. Peneliti Utama',
                    'deskripsi_singkat' => 'Deskripsi riset awal',
                    'tanggal_mulai' => '2026-04-01',
                    'status_proyek' => 'Sedang Berjalan',
                ],
                'updatePayload' => ['judul_penelitian_simulasi' => 'Riset Pair UX Updated'],
            ],
            [
                'endpoint' => '/api/berita-isu',
                'model' => BeritaIsu::class,
                'idField' => 'berita_id',
                'mainCol' => 'gambar_pendukung',
                'createPayload' => [
                    'judul_berita' => 'Berita Pair UX',
                    'penulis' => 'Tim Redaksi TP Lab',
                    'isi_artikel_ringkas' => 'Isi berita awal',
                    'tanggal_rilis' => '2026-04-01',
                ],
                'updatePayload' => ['judul_berita' => 'Berita Pair UX Updated'],
            ],
            [
                'endpoint' => '/api/prestasi-pencapaian',
                'model' => PrestasiPencapaian::class,
                'idField' => 'prestasi_id',
                'mainCol' => 'gambar_utama',
                'createPayload' => [
                    'nama_prestasi' => 'Prestasi Pair UX',
                    'penulis' => 'Dr. Budi Santoso',
                    'tahun_diperoleh' => 2026,
                    'deskripsi' => 'Deskripsi prestasi awal',
                ],
                'updatePayload' => ['nama_prestasi' => 'Prestasi Pair UX Updated'],
            ],
        ];

        foreach ($modules as $cfg) {
            // 1. Add without main image must fail with 422.
            $missingMainRes = $this->postJson($cfg['endpoint'], $cfg['createPayload']);
            $missingMainRes->assertStatus(422);
            $missingMainRes->assertJsonValidationErrors('image');

            // 2. Add with only supporting_image_1 or only supporting_image_2 must fail with 422.
            $onlySupp1Res = $this->postJson($cfg['endpoint'], array_merge($cfg['createPayload'], [
                'image' => UploadedFile::fake()->image('main.jpg'),
                'supporting_image_1' => UploadedFile::fake()->image('supp1.jpg'),
            ]));
            $onlySupp1Res->assertStatus(422);
            $onlySupp1Res->assertJsonValidationErrors(['supporting_image_1', 'supporting_image_2']);

            $onlySupp2Res = $this->postJson($cfg['endpoint'], array_merge($cfg['createPayload'], [
                'image' => UploadedFile::fake()->image('main.jpg'),
                'supporting_image_2' => UploadedFile::fake()->image('supp2.jpg'),
            ]));
            $onlySupp2Res->assertStatus(422);
            $onlySupp2Res->assertJsonValidationErrors(['supporting_image_1', 'supporting_image_2']);

            // 3. Add with valid main image and no supporting images succeeds.
            $noPairRes = $this->postJson($cfg['endpoint'], array_merge($cfg['createPayload'], [
                'image' => UploadedFile::fake()->image('main-only.jpg'),
            ]));
            $noPairRes->assertStatus(201);
            $noPairRecord = $cfg['model']::findOrFail($noPairRes->json("data.{$cfg['idField']}"));
            $this->assertNull($noPairRecord->gambar_pendukung_1);
            $this->assertNull($noPairRecord->gambar_pendukung_2);

            // 4. Add with valid main image and paired supporting images succeeds.
            $createRes = $this->postJson($cfg['endpoint'], array_merge($cfg['createPayload'], [
                'image' => UploadedFile::fake()->image('main-orig.jpg'),
                'supporting_image_1' => UploadedFile::fake()->image('supp1-orig.jpg'),
                'supporting_image_2' => UploadedFile::fake()->image('supp2-orig.jpg'),
            ]));
            $createRes->assertStatus(201);
            $id = $createRes->json("data.{$cfg['idField']}");

            $record = $cfg['model']::findOrFail($id);
            $origMain = $record->{$cfg['mainCol']};
            $origSupp1 = $record->gambar_pendukung_1;
            $origSupp2 = $record->gambar_pendukung_2;
            Storage::disk('public')->assertExists($origMain);
            Storage::disk('public')->assertExists($origSupp1);
            Storage::disk('public')->assertExists($origSupp2);

            // 5. Edit without changing images preserves main image and both supporting images.
            $keepRes = $this->postJson("{$cfg['endpoint']}/{$id}", array_merge($cfg['updatePayload'], [
                '_method' => 'PUT',
            ]));
            $keepRes->assertStatus(200);
            $record->refresh();
            $this->assertSame($origMain, $record->{$cfg['mainCol']});
            $this->assertSame($origSupp1, $record->gambar_pendukung_1);
            $this->assertSame($origSupp2, $record->gambar_pendukung_2);

            // 6. Attempt remove_image=1 (with or without replacement) must fail with 422 and preserve old main image.
            $removeMainRes = $this->postJson("{$cfg['endpoint']}/{$id}", array_merge($cfg['updatePayload'], [
                '_method' => 'PUT',
                'remove_image' => '1',
            ]));
            $removeMainRes->assertStatus(422);
            $removeMainRes->assertJsonValidationErrors('image');
            Storage::disk('public')->assertExists($origMain);

            // 7. Partial supporting upload replacement or mixing upload with remove must fail with 422 and preserve old files.
            $partialCases = [
                ['supporting_image_1' => UploadedFile::fake()->image('only1.jpg')],
                ['supporting_image_2' => UploadedFile::fake()->image('only2.jpg')],
                [
                    'supporting_image_1' => UploadedFile::fake()->image('mix1.jpg'),
                    'remove_supporting_image_2' => '1',
                ],
                [
                    'supporting_image_1' => UploadedFile::fake()->image('mix-both.jpg'),
                    'supporting_image_2' => UploadedFile::fake()->image('mix-both2.jpg'),
                    'remove_supporting_image_1' => '1',
                    'remove_supporting_image_2' => '1',
                ],
            ];
            foreach ($partialCases as $partialPayload) {
                $failRes = $this->postJson("{$cfg['endpoint']}/{$id}", array_merge(
                    $cfg['updatePayload'],
                    ['_method' => 'PUT'],
                    $partialPayload
                ));
                $failRes->assertStatus(422);
                Storage::disk('public')->assertExists($origSupp1);
                Storage::disk('public')->assertExists($origSupp2);
            }

            // 8. Replacing both supporting images and main image succeeds and deletes previous files.
            $replacePairRes = $this->postJson("{$cfg['endpoint']}/{$id}", array_merge($cfg['updatePayload'], [
                '_method' => 'PUT',
                'image' => UploadedFile::fake()->image('main-new.png'),
                'supporting_image_1' => UploadedFile::fake()->image('supp1-new.png'),
                'supporting_image_2' => UploadedFile::fake()->image('supp2-new.png'),
            ]));
            $replacePairRes->assertStatus(200);
            $record->refresh();
            $newMain = $record->{$cfg['mainCol']};
            $newSupp1 = $record->gambar_pendukung_1;
            $newSupp2 = $record->gambar_pendukung_2;
            $this->assertNotSame($origMain, $newMain);
            $this->assertNotSame($origSupp1, $newSupp1);
            $this->assertNotSame($origSupp2, $newSupp2);
            Storage::disk('public')->assertMissing($origMain);
            Storage::disk('public')->assertMissing($origSupp1);
            Storage::disk('public')->assertMissing($origSupp2);
            Storage::disk('public')->assertExists($newMain);
            Storage::disk('public')->assertExists($newSupp1);
            Storage::disk('public')->assertExists($newSupp2);

            // 9a. Sending a single remove_supporting_image_1 flag clears BOTH columns and deletes both unreferenced files.
            $removeSingleFlagRes = $this->postJson("{$cfg['endpoint']}/{$id}", array_merge($cfg['updatePayload'], [
                '_method' => 'PUT',
                'remove_supporting_image_1' => '1',
            ]));
            $removeSingleFlagRes->assertStatus(200);
            $record->refresh();
            $this->assertNull($record->gambar_pendukung_1);
            $this->assertNull($record->gambar_pendukung_2);
            Storage::disk('public')->assertExists($newMain);
            Storage::disk('public')->assertMissing($newSupp1);
            Storage::disk('public')->assertMissing($newSupp2);

            // 9b. Re-upload pair and remove using both flags together also clears both columns and deletes files.
            $reuploadPairRes = $this->postJson("{$cfg['endpoint']}/{$id}", array_merge($cfg['updatePayload'], [
                '_method' => 'PUT',
                'supporting_image_1' => UploadedFile::fake()->image('supp1-reupload.png'),
                'supporting_image_2' => UploadedFile::fake()->image('supp2-reupload.png'),
            ]));
            $reuploadPairRes->assertStatus(200);
            $record->refresh();
            $reupSupp1 = $record->gambar_pendukung_1;
            $reupSupp2 = $record->gambar_pendukung_2;
            Storage::disk('public')->assertExists($reupSupp1);
            Storage::disk('public')->assertExists($reupSupp2);

            $removePairRes = $this->postJson("{$cfg['endpoint']}/{$id}", array_merge($cfg['updatePayload'], [
                '_method' => 'PUT',
                'remove_supporting_image_1' => '1',
                'remove_supporting_image_2' => '1',
            ]));
            $removePairRes->assertStatus(200);
            $record->refresh();
            $this->assertNull($record->gambar_pendukung_1);
            $this->assertNull($record->gambar_pendukung_2);
            Storage::disk('public')->assertMissing($reupSupp1);
            Storage::disk('public')->assertMissing($reupSupp2);
        }
    }

    public function test_edit_removal_preserves_shared_supporting_image_file(): void
    {
        $sharedRes = $this->postJson('/api/berita-isu', [
            'judul_berita' => 'Berita Berbagi Gambar 1',
            'penulis' => 'Tim Redaksi TP Lab',
            'isi_artikel_ringkas' => 'Konten 1',
            'tanggal_rilis' => '2026-04-01',
            'image' => UploadedFile::fake()->image('main-1.jpg'),
            'supporting_image_1' => UploadedFile::fake()->image('shared-gallery-1.jpg'),
            'supporting_image_2' => UploadedFile::fake()->image('shared-gallery-2.jpg'),
        ]);
        $sharedRes->assertStatus(201);
        $firstId = $sharedRes->json('data.berita_id');
        $first = BeritaIsu::findOrFail($firstId);
        $sharedPath1 = $first->gambar_pendukung_1;
        $sharedPath2 = $first->gambar_pendukung_2;

        BeritaIsu::create([
            'judul_berita' => 'Berita Berbagi Gambar 2',
            'penulis' => 'Tim Redaksi TP Lab',
            'isi_artikel_ringkas' => 'Konten 2',
            'tanggal_rilis' => '2026-04-02',
            'admin_id' => $this->admin->admin_id,
            'gambar_pendukung' => 'news/static-other.jpg',
            'gambar_pendukung_1' => $sharedPath1,
            'gambar_pendukung_2' => $sharedPath2,
        ]);

        $clearRes = $this->postJson("/api/berita-isu/{$firstId}", [
            '_method' => 'PUT',
            'judul_berita' => 'Berita Berbagi Gambar 1 Edited',
            'remove_supporting_image_1' => '1',
            'remove_supporting_image_2' => '1',
        ]);
        $clearRes->assertStatus(200);
        $first->refresh();
        $this->assertNull($first->gambar_pendukung_1);
        $this->assertNull($first->gambar_pendukung_2);
        Storage::disk('public')->assertExists($sharedPath1);
        Storage::disk('public')->assertExists($sharedPath2);
    }

    public function test_achievement_manual_author_create_update_and_validation(): void
    {
        $photoFile = UploadedFile::fake()->image('manual_author.jpg');

        $createRes = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Penghargaan Penulis Manual',
            'penulis' => 'Dr. Budi Santoso',
            'tahun_diperoleh' => 2026,
            'deskripsi' => 'Prestasi dengan nama penulis diketik manual.',
            'image' => $photoFile,
        ]);

        $createRes->assertStatus(201);
        $prestasiId = $createRes->json('data.prestasi_id');
        $this->assertDatabaseHas('prestasi_pencapaian', [
            'prestasi_id' => $prestasiId,
            'penulis' => 'Dr. Budi Santoso',
        ]);

        // Omitting or emptying penulis on API is nullable (stored as null, never overwritten by admin name).
        $nullableAuthorRes = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Prestasi Tanpa Penulis',
            'penulis' => '',
            'tahun_diperoleh' => 2026,
            'image' => UploadedFile::fake()->image('missing_author.jpg'),
        ]);
        $nullableAuthorRes->assertStatus(201);
        $this->assertDatabaseHas('prestasi_pencapaian', [
            'prestasi_id' => $nullableAuthorRes->json('data.prestasi_id'),
            'penulis' => null,
        ]);

        // Overlong penulis (>255 chars) must fail validation with 422.
        $tooLongAuthorRes = $this->postJson('/api/prestasi-pencapaian', [
            'nama_prestasi' => 'Prestasi Penulis Terlalu Panjang',
            'penulis' => str_repeat('A', 256),
            'tahun_diperoleh' => 2026,
            'image' => UploadedFile::fake()->image('long_author.jpg'),
        ]);
        $tooLongAuthorRes->assertStatus(422);
        $tooLongAuthorRes->assertJsonValidationErrors('penulis');

        // Updating penulis via _method=PUT persists new manual author name and exposes it via API.
        $updateRes = $this->postJson("/api/prestasi-pencapaian/{$prestasiId}", [
            '_method' => 'PUT',
            'penulis' => 'Prof. Siti Aminah',
        ]);
        $updateRes->assertStatus(200);

        $this->assertDatabaseHas('prestasi_pencapaian', [
            'prestasi_id' => $prestasiId,
            'penulis' => 'Prof. Siti Aminah',
        ]);

        $listRes = $this->getJson('/api/prestasi-pencapaian');
        $listRes->assertStatus(200);
        $firstItem = collect($listRes->json())->firstWhere('prestasi_id', $prestasiId);
        $this->assertNotNull($firstItem);
        $this->assertSame('Prof. Siti Aminah', $firstItem['penulis']);

        $showRes = $this->getJson("/api/prestasi-pencapaian/{$prestasiId}");
        $showRes->assertStatus(200);
        $showRes->assertJsonPath('penulis', 'Prof. Siti Aminah');
    }

    public function test_research_news_and_publication_manual_penulis_create_update_validation_and_admin_id_protection(): void
    {
        // 1. Proyek Riset: nullable API penulis (not defaulted to admin name), max:255 check, create sets admin_id from token, update blocks spoofing.
        $nullableResearchAuthor = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Riset Tanpa Penulis API',
            'deskripsi_singkat' => 'Deskripsi riset',
            'tanggal_mulai' => '2026-05-01',
            'status_proyek' => 'Sedang Berjalan',
            'image' => UploadedFile::fake()->image('riset-no-author.jpg'),
        ]);
        $nullableResearchAuthor->assertStatus(201)
            ->assertJsonPath('data.penulis', null);
        $this->assertDatabaseHas('proyek_riset', [
            'proyek_id' => $nullableResearchAuthor->json('data.proyek_id'),
            'penulis' => null,
            'admin_id' => $this->admin->admin_id,
        ]);

        $overlongResearchAuthor = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Riset Penulis Panjang',
            'penulis' => str_repeat('B', 256),
            'deskripsi_singkat' => 'Deskripsi riset',
            'tanggal_mulai' => '2026-05-01',
            'status_proyek' => 'Sedang Berjalan',
            'image' => UploadedFile::fake()->image('riset-long-author.jpg'),
        ]);
        $overlongResearchAuthor->assertStatus(422)
            ->assertJsonValidationErrors(['penulis']);

        $createResearch = $this->postJson('/api/proyek-riset', [
            'judul_penelitian_simulasi' => 'Riset Penulis Manual',
            'penulis' => '  Prof. Hendra Wijaya  ',
            'deskripsi_singkat' => 'Deskripsi riset manual penulis',
            'tanggal_mulai' => '2026-05-01',
            'status_proyek' => 'Sedang Berjalan',
            'admin_id' => 999,
            'image' => UploadedFile::fake()->image('riset-author.jpg'),
        ]);
        $createResearch->assertStatus(201)
            ->assertJsonPath('data.penulis', 'Prof. Hendra Wijaya');

        $proyekId = $createResearch->json('data.proyek_id');
        $this->assertDatabaseHas('proyek_riset', [
            'proyek_id' => $proyekId,
            'penulis' => 'Prof. Hendra Wijaya',
            'admin_id' => $this->admin->admin_id,
        ]);

        $this->getJson('/api/proyek-riset')
            ->assertStatus(200)
            ->assertJsonPath('data.0.penulis', 'Prof. Hendra Wijaya');

        $this->getJson("/api/proyek-riset/{$proyekId}")
            ->assertStatus(200)
            ->assertJsonPath('data.penulis', 'Prof. Hendra Wijaya');

        $updateResearch = $this->postJson("/api/proyek-riset/{$proyekId}", [
            '_method' => 'PUT',
            'penulis' => 'Dr. Siti Aminah',
            'admin_id' => 999,
        ]);
        $updateResearch->assertStatus(200)
            ->assertJsonPath('data.penulis', 'Dr. Siti Aminah');

        $this->assertDatabaseHas('proyek_riset', [
            'proyek_id' => $proyekId,
            'penulis' => 'Dr. Siti Aminah',
            'admin_id' => $this->admin->admin_id,
        ]);

        // 2. Berita & Isu: nullable API penulis, max:255 check, create/update/list/show expose penulis & preserve admin_id.
        $nullableNewsAuthor = $this->postJson('/api/berita-isu', [
            'judul_berita' => 'Berita Tanpa Penulis API',
            'penulis' => '',
            'tanggal_rilis' => '2026-05-02',
            'isi_artikel_ringkas' => 'Isi berita',
            'image' => UploadedFile::fake()->image('news-no-author.jpg'),
        ]);
        $nullableNewsAuthor->assertStatus(201)
            ->assertJsonPath('data.penulis', null);

        $overlongNewsAuthor = $this->postJson('/api/berita-isu', [
            'judul_berita' => 'Berita Penulis Panjang',
            'penulis' => str_repeat('C', 256),
            'tanggal_rilis' => '2026-05-02',
            'isi_artikel_ringkas' => 'Isi berita',
            'image' => UploadedFile::fake()->image('news-long-author.jpg'),
        ]);
        $overlongNewsAuthor->assertStatus(422)
            ->assertJsonValidationErrors(['penulis']);

        $createNews = $this->postJson('/api/berita-isu', [
            'judul_berita' => 'Berita Penulis Manual',
            'penulis' => '  Tim Redaksi TP Lab  ',
            'tanggal_rilis' => '2026-05-02',
            'isi_artikel_ringkas' => 'Isi berita manual penulis',
            'topik_utama' => 'Riset',
            'admin_id' => 999,
            'image' => UploadedFile::fake()->image('news-author.jpg'),
        ]);
        $createNews->assertStatus(201)
            ->assertJsonPath('data.penulis', 'Tim Redaksi TP Lab');

        $beritaId = $createNews->json('data.berita_id');
        $this->assertDatabaseHas('berita_isu', [
            'berita_id' => $beritaId,
            'penulis' => 'Tim Redaksi TP Lab',
            'admin_id' => $this->admin->admin_id,
        ]);

        $newsList = $this->getJson('/api/berita-isu');
        $newsList->assertStatus(200);
        $matchedNews = collect($newsList->json())->firstWhere('berita_id', $beritaId);
        $this->assertNotNull($matchedNews);
        $this->assertSame('Tim Redaksi TP Lab', $matchedNews['penulis']);

        $this->getJson("/api/berita-isu/{$beritaId}")
            ->assertStatus(200)
            ->assertJsonPath('penulis', 'Tim Redaksi TP Lab');

        $updateNews = $this->postJson("/api/berita-isu/{$beritaId}", [
            '_method' => 'PUT',
            'penulis' => 'Editor Utama',
            'admin_id' => 999,
        ]);
        $updateNews->assertStatus(200)
            ->assertJsonPath('data.penulis', 'Editor Utama');

        $this->assertDatabaseHas('berita_isu', [
            'berita_id' => $beritaId,
            'penulis' => 'Editor Utama',
            'admin_id' => $this->admin->admin_id,
        ]);

        // 3. Publikasi & Hibah: nullable API penulis, max:255 check, create/update/list/show expose penulis & preserve admin_id.
        $nullablePublicationAuthor = $this->postJson('/api/publikasi-hibah', [
            'judul_karya_hibah' => 'Publikasi Tanpa Penulis API',
            'tahun' => 2026,
        ]);
        $nullablePublicationAuthor->assertStatus(201)
            ->assertJsonPath('data.penulis', null);

        $overlongPublicationAuthor = $this->postJson('/api/publikasi-hibah', [
            'judul_karya_hibah' => 'Publikasi Penulis Panjang',
            'penulis' => str_repeat('D', 256),
            'tahun' => 2026,
        ]);
        $overlongPublicationAuthor->assertStatus(422)
            ->assertJsonValidationErrors(['penulis']);

        $createPublication = $this->postJson('/api/publikasi-hibah', [
            'jenis' => 'Publikasi Jurnal',
            'judul_karya_hibah' => 'Publikasi Penulis Manual',
            'penulis' => '  Dr. Eng. Tika Erna Putri  ',
            'nama_jurnal_skema' => 'IEEE Photovoltaics',
            'tahun' => 2026,
            'tautan_eksternal' => 'https://doi.org/10.1000/manual-author-test',
            'proyek_id' => $proyekId,
            'admin_id' => 999,
        ]);
        $createPublication->assertStatus(201)
            ->assertJsonPath('data.penulis', 'Dr. Eng. Tika Erna Putri');

        $publikasiId = $createPublication->json('data.publikasi_hibah_id');
        $this->assertDatabaseHas('publikasi_hibah', [
            'publikasi_hibah_id' => $publikasiId,
            'penulis' => 'Dr. Eng. Tika Erna Putri',
            'admin_id' => $this->admin->admin_id,
        ]);

        $publicationList = $this->getJson('/api/publikasi-hibah');
        $publicationList->assertStatus(200);
        $matchedPublication = collect($publicationList->json())->firstWhere('publikasi_hibah_id', $publikasiId);
        $this->assertNotNull($matchedPublication);
        $this->assertSame('Dr. Eng. Tika Erna Putri', $matchedPublication['penulis']);

        $this->getJson("/api/publikasi-hibah/{$publikasiId}")
            ->assertStatus(200)
            ->assertJsonPath('penulis', 'Dr. Eng. Tika Erna Putri');

        $updatePublication = $this->putJson("/api/publikasi-hibah/{$publikasiId}", [
            'penulis' => 'Prof. Rina Kusuma',
            'admin_id' => 999,
        ]);
        $updatePublication->assertStatus(200)
            ->assertJsonPath('data.penulis', 'Prof. Rina Kusuma');

        $this->assertDatabaseHas('publikasi_hibah', [
            'publikasi_hibah_id' => $publikasiId,
            'penulis' => 'Prof. Rina Kusuma',
            'admin_id' => $this->admin->admin_id,
        ]);
        $this->assertInstanceOf(PublikasiHibah::class, PublikasiHibah::find($publikasiId));
    }
}
