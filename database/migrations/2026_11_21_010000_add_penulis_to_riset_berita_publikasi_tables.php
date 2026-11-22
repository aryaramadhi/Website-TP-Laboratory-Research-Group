<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom penulis manual ke proyek_riset, berita_isu, dan publikasi_hibah.
     */
    public function up(): void
    {
        $tables = [
            'proyek_riset'    => 'judul_penelitian_simulasi',
            'berita_isu'      => 'judul_berita',
            'publikasi_hibah' => 'judul_karya_hibah',
        ];

        foreach ($tables as $tableName => $afterColumn) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'penulis')) {
                Schema::table($tableName, function (Blueprint $table) use ($afterColumn) {
                    $table->string('penulis', 255)->nullable()->after($afterColumn);
                });
            }
        }
    }

    /**
     * Hapus kolom penulis jika rollback dijalankan.
     */
    public function down(): void
    {
        foreach (['proyek_riset', 'berita_isu', 'publikasi_hibah'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'penulis')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('penulis');
                });
            }
        }
    }
};
