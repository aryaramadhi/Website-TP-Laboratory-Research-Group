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
    Schema::create('jnc_anggota_kontribusi_publikasi', function (Blueprint $table) {
        $table->id('jnc_kontribusi_id');
        $table->foreignId('anggota_alumni_id')->constrained('anggota_alumni', 'anggota_alumni_id')->onDelete('cascade');
        $table->foreignId('publikasi_id')->constrained('publikasi_hibah', 'publikasi_hibah_id')->onDelete('cascade');
        $table->integer('urutan_penulis')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jnc_anggota_kontribusi_publikasi');
    }
};
