<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('prestasi_pencapaian')) {
            Schema::table('prestasi_pencapaian', function (Blueprint $table) {
                if (!Schema::hasColumn('prestasi_pencapaian', 'penulis')) {
                    $table->string('penulis')->nullable()->after('nama_prestasi');
                }
                if (Schema::hasColumn('prestasi_pencapaian', 'anggota_alumni_id')) {
                    $table->unsignedBigInteger('anggota_alumni_id')->nullable()->change();
                }
            });

            // Backfill data lama jika ada record yang belum memiliki nilai penulis manual (kompatibel lintas DB)
            if (Schema::hasTable('anggota_alumni')) {
                $rows = DB::table('prestasi_pencapaian')
                    ->join('anggota_alumni', 'prestasi_pencapaian.anggota_alumni_id', '=', 'anggota_alumni.anggota_alumni_id')
                    ->whereNull('prestasi_pencapaian.penulis')
                    ->whereNotNull('prestasi_pencapaian.anggota_alumni_id')
                    ->select('prestasi_pencapaian.prestasi_id', 'anggota_alumni.nama_lengkap')
                    ->get();

                foreach ($rows as $row) {
                    DB::table('prestasi_pencapaian')
                        ->where('prestasi_id', $row->prestasi_id)
                        ->update(['penulis' => $row->nama_lengkap]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('prestasi_pencapaian')) {
            Schema::table('prestasi_pencapaian', function (Blueprint $table) {
                if (Schema::hasColumn('prestasi_pencapaian', 'penulis')) {
                    $table->dropColumn('penulis');
                }
            });
        }
    }
};
