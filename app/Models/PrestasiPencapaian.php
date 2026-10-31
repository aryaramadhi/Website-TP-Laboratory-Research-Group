<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestasiPencapaian extends Model
{
    use HasFactory;

    protected $table = 'prestasi_pencapaian';
    protected $primaryKey = 'prestasi_id';

    protected $fillable = [
        'nama_prestasi',
        'penyelenggara',
        'tingkat',
        'tahun_diperoleh',
        'deskripsi',
        'dokumentasi_bukti',
        'anggota_alumni_id',
        'proyek_id',
    ];

    public function anggotaAlumni()
    {
        return $this->belongsTo(AnggotaAlumni::class, 'anggota_alumni_id');
    }

    public function proyekRiset()
    {
        return $this->belongsTo(ProyekRiset::class, 'proyek_id');
    }
}