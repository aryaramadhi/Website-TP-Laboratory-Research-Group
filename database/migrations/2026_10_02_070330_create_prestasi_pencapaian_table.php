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
    Schema::create('prestasi_pencapaian', function (Blueprint $table) {
        $table->id('prestasi_id');
        $table->string('nama_prestasi');
        $table->string('penyelenggara')->nullable();
        $table->string('tingkat')->nullable(); // Kampus, Nasional, Internasional
        $table->integer('tahun_diperoleh')->nullable();
        $table->text('deskripsi')->nullable();
        $table->string('dokumentasi_bukti')->nullable();
        
        // Foreign Keys
        $table->foreignId('anggota_alumni_id')->constrained('anggota_alumni', 'anggota_alumni_id')->onDelete('cascade');
        $table->foreignId('proyek_id')->nullable()->constrained('proyek_riset', 'proyek_id')->onDelete('set null');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestasi_pencapaian');
    }
};
