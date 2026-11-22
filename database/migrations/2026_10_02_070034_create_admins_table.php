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
    Schema::create('admins', function (Blueprint $table) {
        $table->id('admin_id');
        $table->string('email')->unique();
        $table->string('password'); // hashed
        $table->string('nama_lengkap')->nullable();
        $table->string('peran')->nullable(); // Ketua Grup Riset, Admin Staf
        $table->boolean('status_akses')->default(true); // full access, partial
        $table->dateTime('last_login')->nullable();
        $table->timestamps();
    });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
