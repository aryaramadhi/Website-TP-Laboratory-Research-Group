<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('prestasi_pencapaian', 'anggota_alumni_id')) {
            Schema::table('prestasi_pencapaian', function (Blueprint $table) {
                $table->dropForeign(['anggota_alumni_id']);
                $table->dropColumn('anggota_alumni_id');
            });
        }

        Schema::dropIfExists('jnc_anggota_kontribusi_publikasi');
        Schema::dropIfExists('jnc_proyek_anggota');

        Schema::dropIfExists('anggota_alumni');
    }

    public function down(): void
    {
        Schema::create('anggota_alumni', function (Blueprint $table) {
            $table->id('anggota_alumni_id');
            $table->string('nama_lengkap');
            $table->string('kepakaran')->nullable();
            $table->string('peran_lab')->nullable();
            $table->integer('tahun_masuk')->nullable();
            $table->integer('tahun_lulus_keluar')->nullable();
            $table->string('kontak_profesional')->nullable();
            $table->string('tautan_repositori_pribadi')->nullable();
            $table->unsignedBigInteger('admin_id');
            $table->timestamps();

            $table->foreign('admin_id')->references('admin_id')->on('admins')->onDelete('cascade');
        });

        Schema::create('jnc_proyek_anggota', function (Blueprint $table) {
            $table->id('jnc_proyek_anggota_id');
            $table->unsignedBigInteger('proyek_id');
            $table->unsignedBigInteger('anggota_alumni_id');
            $table->string('peran_spesifik')->nullable();
            $table->timestamps();

            $table->foreign('proyek_id')->references('proyek_id')->on('proyek_riset')->onDelete('cascade');
            $table->foreign('anggota_alumni_id')->references('anggota_alumni_id')->on('anggota_alumni')->onDelete('cascade');
        });

        Schema::create('jnc_anggota_kontribusi_publikasi', function (Blueprint $table) {
            $table->id('jnc_kontribusi_id');
            $table->unsignedBigInteger('anggota_alumni_id');
            $table->unsignedBigInteger('publikasi_id');
            $table->integer('urutan_penulis')->nullable();
            $table->timestamps();

            $table->foreign('anggota_alumni_id')->references('anggota_alumni_id')->on('anggota_alumni')->onDelete('cascade');
            $table->foreign('publikasi_id')->references('publikasi_hibah_id')->on('publikasi_hibah')->onDelete('cascade');
        });

        Schema::table('prestasi_pencapaian', function (Blueprint $table) {
            $table->unsignedBigInteger('anggota_alumni_id')->nullable()->after('gambar_pendukung_2');
            $table->foreign('anggota_alumni_id')->references('anggota_alumni_id')->on('anggota_alumni')->onDelete('cascade');
        });
    }
};
