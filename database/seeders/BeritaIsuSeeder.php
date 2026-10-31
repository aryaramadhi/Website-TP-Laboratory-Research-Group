<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BeritaIsu;

class BeritaIsuSeeder extends Seeder
{
    public function run(): void
    {
        BeritaIsu::create([
            'judul_berita' => 'Kunjungan Industri Lab TP ke Industri Teknologi AI',
            'tanggal_rilis' => '2026-03-10',
            'isi_artikel_ringkas' => 'Studi banding dan penjajakan kerja sama riset bidang kecerdasan buatan.',
            'gambar_pendukung' => 'kunjungan_lab.jpg',
            'topik_utama' => 'Kegiatan Riset',
            'admin_id' => 1,
        ]);
    }
}