<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('anggota_alumni', function (Blueprint $table) {
        $table->id('anggota_alumni_id');
        $table->string('nama_lengkap');
        $table->string('kepakaran')->nullable();
        $table->string('peran_lab')->nullable(); // Peneliti Utama, Tim Riset, Alumni
        $table->integer('tahun_masuk')->nullable();
        $table->integer('tahun_lulus_keluar')->nullable();
        $table->string('kontak_profesional')->nullable();
        $table->string('tautan_repositori_pribadi')->nullable();

        // Foreign Key to admins
        $table->foreignId('admin_id')->constrained('admins', 'admin_id')->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_alumni');
    }
};
