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
        // 1. Tambah kolom gambar pendukung pada proyek_riset
        if (Schema::hasTable('proyek_riset')) {
            Schema::table('proyek_riset', function (Blueprint $table) {
                if (!Schema::hasColumn('proyek_riset', 'gambar_pendukung_1')) {
                    $table->string('gambar_pendukung_1')->nullable()->after('dokumentasi_visual_utama');
                }
                if (!Schema::hasColumn('proyek_riset', 'gambar_pendukung_2')) {
                    $table->string('gambar_pendukung_2')->nullable()->after('gambar_pendukung_1');
                }
            });
        }

        // 2. Tambah kolom gambar pendukung pada berita_isu (gambar_pendukung = gambar utama)
        if (Schema::hasTable('berita_isu')) {
            Schema::table('berita_isu', function (Blueprint $table) {
                if (!Schema::hasColumn('berita_isu', 'gambar_pendukung_1')) {
                    $table->string('gambar_pendukung_1')->nullable()->after('gambar_pendukung');
                }
                if (!Schema::hasColumn('berita_isu', 'gambar_pendukung_2')) {
                    $table->string('gambar_pendukung_2')->nullable()->after('gambar_pendukung_1');
                }
            });
        }

        // 3. Tambah kolom gambar utama & pendukung pada prestasi_pencapaian
        if (Schema::hasTable('prestasi_pencapaian')) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('proyek_riset')) {
            Schema::table('proyek_riset', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('proyek_riset', 'gambar_pendukung_1')) {
                    $columns[] = 'gambar_pendukung_1';
                }
                if (Schema::hasColumn('proyek_riset', 'gambar_pendukung_2')) {
                    $columns[] = 'gambar_pendukung_2';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('berita_isu')) {
            Schema::table('berita_isu', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('berita_isu', 'gambar_pendukung_1')) {
                    $columns[] = 'gambar_pendukung_1';
                }
                if (Schema::hasColumn('berita_isu', 'gambar_pendukung_2')) {
                    $columns[] = 'gambar_pendukung_2';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('prestasi_pencapaian')) {
            Schema::table('prestasi_pencapaian', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('prestasi_pencapaian', 'gambar_utama')) {
                    $columns[] = 'gambar_utama';
                }
                if (Schema::hasColumn('prestasi_pencapaian', 'gambar_pendukung_1')) {
                    $columns[] = 'gambar_pendukung_1';
                }
                if (Schema::hasColumn('prestasi_pencapaian', 'gambar_pendukung_2')) {
                    $columns[] = 'gambar_pendukung_2';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
