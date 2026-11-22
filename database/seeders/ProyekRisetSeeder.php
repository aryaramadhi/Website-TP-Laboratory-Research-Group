<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProyekRiset;

class ProyekRisetSeeder extends Seeder
{
    public function run(): void
    {
        ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Pengembangan Sistem Sensor Berbasis AI',
            'deskripsi_singkat'         => 'Penelitian integrasi sensor iot dan kecerdasan buatan. [[RESEARCH_IMAGES]]',
            'dokumentasi_visual_utama'  => 'images/research/default-1.jpg',
            'gambar_pendukung_1'        => null,
            'gambar_pendukung_2'        => null,
            'tanggal_mulai'             => '2026-01-15',
            'tanggal_selesai'           => '2026-12-31',
            'status_proyek'             => 'Selesai',
            'admin_id'                  => 1,
        ]);

        ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Analisis Data Riset Laboratorium TP',
            'deskripsi_singkat'         => 'Simulasi pemrosesan data riset secara otomatis.',
            'dokumentasi_visual_utama'  => 'images/research/default-2.jpg',
            'gambar_pendukung_1'        => null,
            'gambar_pendukung_2'        => null,
            'tanggal_mulai'             => '2026-06-01',
            'tanggal_selesai'           => null,
            'status_proyek'             => 'Sedang Berjalan',
            'admin_id'                  => 1,
        ]);
    }
}
