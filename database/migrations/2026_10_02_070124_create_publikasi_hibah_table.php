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
    Schema::create('publikasi_hibah', function (Blueprint $table) {
        $table->id('publikasi_hibah_id');
        $table->string('jenis')->nullable(); // Publikasi Jurnal, Hibah Riset
        $table->string('judul_karya_hibah');
        $table->string('nama_jurnal_skema')->nullable();
        $table->integer('tahun')->nullable();
        $table->text('deskripsi_pencapaian')->nullable();
        $table->string('tautan_eksternal')->unique()->nullable();
        
        // Foreign Keys
        $table->foreignId('proyek_id')->nullable()->constrained('proyek_riset', 'proyek_id')->onDelete('set null');
        $table->foreignId('admin_id')->constrained('admins', 'admin_id')->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publikasi_hibah');
    }
};
