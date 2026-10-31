<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyekRiset extends Model
{
    use HasFactory;

    protected $table = 'proyek_riset';
    protected $primaryKey = 'proyek_id';

    protected $fillable = [
        'judul_penelitian_simulasi',
        'deskripsi_singkat',
        'dokumentasi_visual_utama',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_proyek',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function anggotaAlumni()
    {
        return $this->belongsToMany(AnggotaAlumni::class, 'jnc_proyek_anggota', 'proyek_id', 'anggota_alumni_id')
                    ->withPivot('peran_spesifik')
                    ->withTimestamps();
    }

    public function publikasiHibah()
    {
        return $this->hasMany(PublikasiHibah::class, 'proyek_id');
    }

    public function prestasiPencapaian()
    {
        return $this->hasMany(PrestasiPencapaian::class, 'proyek_id');
    }
}