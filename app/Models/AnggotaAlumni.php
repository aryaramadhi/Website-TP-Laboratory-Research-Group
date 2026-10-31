<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaAlumni extends Model
{
    use HasFactory;

    protected $table = 'anggota_alumni';
    protected $primaryKey = 'anggota_alumni_id';

    protected $fillable = [
        'nama_lengkap',
        'kepakaran',
        'peran_lab',
        'tahun_masuk',
        'tahun_lulus_keluar',
        'kontak_profesional',
        'tautan_repositori_pribadi',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function proyekRiset()
    {
        return $this->belongsToMany(ProyekRiset::class, 'jnc_proyek_anggota', 'anggota_alumni_id', 'proyek_id')
                    ->withPivot('peran_spesifik')
                    ->withTimestamps();
    }

    public function publikasiHibah()
    {
        return $this->belongsToMany(PublikasiHibah::class, 'jnc_anggota_kontribusi_publikasi', 'anggota_alumni_id', 'publikasi_id')
                    ->withPivot('urutan_penulis')
                    ->withTimestamps();
    }

    public function prestasiPencapaian()
    {
        return $this->hasMany(PrestasiPencapaian::class, 'anggota_alumni_id');
    }
}