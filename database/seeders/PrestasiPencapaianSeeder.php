<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrestasiPencapaian;

class PrestasiPencapaianSeeder extends Seeder
{
    public function run(): void
    {
        PrestasiPencapaian::create([
            'nama_prestasi' => 'Juara 1 Lomba Inovasi Teknologi Nasional',
            'penyelenggara' => 'Kementerian Riset dan Teknologi',
            'tingkat' => 'Nasional',
            'tahun_diperoleh' => 2025,
            'deskripsi' => 'Penghargaan atas inovasi sistem pemantauan lingkungan berbasis sensor IoT.',
            'dokumentasi_bukti' => 'sertifikat_juara1.pdf',
            'anggota_alumni_id' => 1,
            'proyek_id' => 1,
        ]);
    }
}