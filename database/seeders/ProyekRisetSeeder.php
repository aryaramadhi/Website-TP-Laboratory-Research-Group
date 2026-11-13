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
            'deskripsi_singkat' => 'Penelitian integrasi sensor iot dan kecerdasan buatan.',
            'tanggal_mulai' => '2026-01-15',
            'status_proyek' => 'Selesai',
            'admin_id' => 1
        ]);

        ProyekRiset::create([
            'judul_penelitian_simulasi' => 'Analisis Data Riset Laboratorium TP',
            'deskripsi_singkat' => 'Simulasi pemrosesan data riset secara otomatis.',
            'tanggal_mulai' => '2026-06-01',
            'status_proyek' => 'Sedang Berjalan',
            'admin_id' => 1
        ]);
    }
}