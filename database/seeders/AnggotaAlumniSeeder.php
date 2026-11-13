<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnggotaAlumni;

class AnggotaAlumniSeeder extends Seeder
{
    public function run(): void
    {
        AnggotaAlumni::create([
            'nama_lengkap' => 'Dr. Budi Santoso',
            'kepakaran' => 'Artificial Intelligence & IoT',
            'peran_lab' => 'Peneliti Utama',
            'tahun_masuk' => 2020,
            'tahun_lulus_keluar' => 2024,
            'kontak_profesional' => 'budi.santoso@email.com',
            'tautan_repositori_pribadi' => 'https://github.com/budisantoso',
            'admin_id' => 1,
        ]);
    }
}