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
    Schema::create('berita_isu', function (Blueprint $table) {
        $table->id('berita_id');
        $table->string('judul_berita');
        $table->date('tanggal_rilis')->nullable();
        $table->text('isi_artikel_ringkas')->nullable();
        $table->string('gambar_pendukung')->nullable();
        $table->string('topik_utama')->nullable();

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
        Schema::dropIfExists('berita_isu');
    }
};
