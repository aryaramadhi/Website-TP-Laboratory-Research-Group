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
    Schema::create('proyek_riset', function (Blueprint $table) {
        $table->id('proyek_id');
        $table->string('judul_penelitian_simulasi');
        $table->text('deskripsi_singkat')->nullable();
        $table->string('dokumentasi_visual_utama')->nullable();
        $table->date('tanggal_mulai')->nullable();
        $table->date('tanggal_selesai')->nullable();
        $table->string('status_proyek')->nullable(); // Sedang Berjalan, Selesai

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
        Schema::dropIfExists('proyek_riset');
    }
};
