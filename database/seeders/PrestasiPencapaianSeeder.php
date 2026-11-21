<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrestasiPencapaian;

class PrestasiPencapaianSeeder extends Seeder
{
    public function run(): void
    {
        PrestasiPencapaian::create([
            'nama_prestasi'      => 'Juara 1 Lomba Inovasi Teknologi Nasional',
            'penulis'            => 'Dr. Budi Santoso',
            'gambar_utama'       => 'images/prestasi/default.jpg',
            'penyelenggara'      => 'Kementerian Riset dan Teknologi',
            'tingkat'            => 'Nasional',
            'tahun_diperoleh'    => 2025,
            'deskripsi'          => 'Penghargaan atas inovasi sistem pemantauan lingkungan berbasis sensor IoT. [[ACHIEVEMENT_IMAGES]]',
            'dokumentasi_bukti'  => 'sertifikat_juara1.pdf',
            'gambar_pendukung_1' => null,
            'gambar_pendukung_2' => null,
            'proyek_id'          => 1,
        ]);
    }
}
