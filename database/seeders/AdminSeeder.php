<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'email' => 'admin@tplab.com',
            'password' => Hash::make('password123'),
            'nama_lengkap' => 'Administrator Utama',
            'peran' => 'Admin',
            'status_akses' => 1,
        ]);
    }
}