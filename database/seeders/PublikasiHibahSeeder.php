<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PublikasiHibah;

class PublikasiHibahSeeder extends Seeder
{
    public function run(): void
    {
        PublikasiHibah::create([
            'jenis' => 'Publikasi Jurnal',
            'judul_karya_hibah' => 'Penerapan Deep Learning pada Pengolahan Citra Medis',
            'nama_jurnal_skema' => 'IEEE Transactions on Medical Imaging',
            'tahun' => 2025,
            'deskripsi_pencapaian' => 'Riset klasifikasi otomatis penyakit paru-paru berbasis model CNN.',
            'tautan_eksternal' => 'https://doi.org/10.1000/xyz123',
            'proyek_id' => 1,
            'admin_id' => 1,
        ]);
    }
}