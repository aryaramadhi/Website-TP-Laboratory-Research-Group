<?php

namespace App\Models;

use App\Traits\HandlesImageUploads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProyekRiset extends Model
{
    use HasFactory;

    protected $table = 'proyek_riset';
    protected $primaryKey = 'proyek_id';

    protected $fillable = [
        'judul_penelitian_simulasi',
        'penulis',
        'deskripsi_singkat',
        'dokumentasi_visual_utama',
        'gambar_pendukung_1',
        'gambar_pendukung_2',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_proyek',
        'admin_id',
    ];

    protected $appends = [
        'image_url',
        'supporting_image_1_url',
        'supporting_image_2_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->dokumentasi_visual_utama);
    }

    public function getSupportingImage1UrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->gambar_pendukung_1);
    }

    public function getSupportingImage2UrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->gambar_pendukung_2);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
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
