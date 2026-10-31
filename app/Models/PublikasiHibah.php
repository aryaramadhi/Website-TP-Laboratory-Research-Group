<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublikasiHibah extends Model
{
    use HasFactory;

    protected $table = 'publikasi_hibah';
    protected $primaryKey = 'publikasi_hibah_id';

    protected $fillable = [
        'jenis',
        'judul_karya_hibah',
        'nama_jurnal_skema',
        'tahun',
        'deskripsi_pencapaian',
        'tautan_eksternal',
        'proyek_id',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function proyekRiset()
    {
        return $this->belongsTo(ProyekRiset::class, 'proyek_id');
    }

    public function penulis()
    {
        return $this->belongsToMany(AnggotaAlumni::class, 'jnc_anggota_kontribusi_publikasi', 'publikasi_id', 'anggota_alumni_id')
                    ->withPivot('urutan_penulis')
                    ->withTimestamps();
    }
}