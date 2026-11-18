<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestasi_pencapaian', function (Blueprint $table) {
            if (!Schema::hasColumn('prestasi_pencapaian', 'penulis')) {
                $table->string('penulis')->nullable()->after('nama_prestasi');
            }
            $table->unsignedBigInteger('anggota_alumni_id')->nullable()->change();
        });

        $rows = DB::table('prestasi_pencapaian')
            ->whereNull('penulis')
            ->whereNotNull('anggota_alumni_id')
            ->get(['prestasi_id', 'anggota_alumni_id']);

        foreach ($rows as $row) {
            $nama = DB::table('anggota_alumni')
                ->where('anggota_alumni_id', $row->anggota_alumni_id)
                ->value('nama_lengkap');

            if ($nama) {
                DB::table('prestasi_pencapaian')
                    ->where('prestasi_id', $row->prestasi_id)
                    ->update(['penulis' => $nama]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('prestasi_pencapaian', function (Blueprint $table) {
            if (Schema::hasColumn('prestasi_pencapaian', 'penulis')) {
                $table->dropColumn('penulis');
            }
        });
    }
};
