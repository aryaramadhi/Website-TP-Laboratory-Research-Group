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
        Schema::table('proyek_riset', function (Blueprint $table) {
            if (!Schema::hasColumn('proyek_riset', 'gambar_pendukung_1')) {
                $table->string('gambar_pendukung_1')->nullable()->after('dokumentasi_visual_utama');
            }
            if (!Schema::hasColumn('proyek_riset', 'gambar_pendukung_2')) {
                $table->string('gambar_pendukung_2')->nullable()->after('gambar_pendukung_1');
            }
        });

        Schema::table('berita_isu', function (Blueprint $table) {
            if (!Schema::hasColumn('berita_isu', 'gambar_pendukung_1')) {
                $table->string('gambar_pendukung_1')->nullable()->after('gambar_pendukung');
            }
            if (!Schema::hasColumn('berita_isu', 'gambar_pendukung_2')) {
                $table->string('gambar_pendukung_2')->nullable()->after('gambar_pendukung_1');
            }
        });

        Schema::table('prestasi_pencapaian', function (Blueprint $table) {
            if (!Schema::hasColumn('prestasi_pencapaian', 'gambar_utama')) {
                $table->string('gambar_utama')->nullable()->after('nama_prestasi');
            }
            if (!Schema::hasColumn('prestasi_pencapaian', 'gambar_pendukung_1')) {
                $table->string('gambar_pendukung_1')->nullable()->after('dokumentasi_bukti');
            }
            if (!Schema::hasColumn('prestasi_pencapaian', 'gambar_pendukung_2')) {
                $table->string('gambar_pendukung_2')->nullable()->after('gambar_pendukung_1');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyek_riset', function (Blueprint $table) {
            if (Schema::hasColumn('proyek_riset', 'gambar_pendukung_2')) {
                $table->dropColumn('gambar_pendukung_2');
            }
            if (Schema::hasColumn('proyek_riset', 'gambar_pendukung_1')) {
                $table->dropColumn('gambar_pendukung_1');
            }
        });

        Schema::table('berita_isu', function (Blueprint $table) {
            if (Schema::hasColumn('berita_isu', 'gambar_pendukung_2')) {
                $table->dropColumn('gambar_pendukung_2');
            }
            if (Schema::hasColumn('berita_isu', 'gambar_pendukung_1')) {
                $table->dropColumn('gambar_pendukung_1');
            }
        });

        Schema::table('prestasi_pencapaian', function (Blueprint $table) {
            if (Schema::hasColumn('prestasi_pencapaian', 'gambar_pendukung_2')) {
                $table->dropColumn('gambar_pendukung_2');
            }
            if (Schema::hasColumn('prestasi_pencapaian', 'gambar_pendukung_1')) {
                $table->dropColumn('gambar_pendukung_1');
            }
            if (Schema::hasColumn('prestasi_pencapaian', 'gambar_utama')) {
                $table->dropColumn('gambar_utama');
            }
        });
    }
};
