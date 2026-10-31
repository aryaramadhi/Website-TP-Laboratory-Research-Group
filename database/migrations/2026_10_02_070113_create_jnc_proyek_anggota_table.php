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
    Schema::create('jnc_proyek_anggota', function (Blueprint $table) {
        $table->id('jnc_proyek_anggota_id');
        $table->foreignId('proyek_id')->constrained('proyek_riset', 'proyek_id')->onDelete('cascade');
        $table->foreignId('anggota_alumni_id')->constrained('anggota_alumni', 'anggota_alumni_id')->onDelete('cascade');
        $table->string('peran_spesifik')->nullable(); // Lead Simulator
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jnc_proyek_anggota');
    }
};
