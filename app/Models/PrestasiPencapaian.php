<?php

namespace App\Models;

use App\Traits\HandlesImageUploads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestasiPencapaian extends Model
{
    use HasFactory;

    protected $table = 'prestasi_pencapaian';
    protected $primaryKey = 'prestasi_id';

    protected $fillable = [
        'nama_prestasi',
        'penulis',
        'gambar_utama',
        'penyelenggara',
        'tingkat',
        'tahun_diperoleh',
        'deskripsi',
        'dokumentasi_bukti',
        'gambar_pendukung_1',
        'gambar_pendukung_2',
        'anggota_alumni_id',
        'proyek_id',
    ];

    protected $appends = [
        'image_url',
        'document_url',
        'supporting_image_1_url',
        'supporting_image_2_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->gambar_utama);
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->dokumentasi_bukti);
    }

    public function getSupportingImage1UrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->gambar_pendukung_1);
    }

    public function getSupportingImage2UrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->gambar_pendukung_2);
    }

    public function anggotaAlumni()
    {
        return $this->belongsTo(AnggotaAlumni::class, 'anggota_alumni_id');
    }

    public function proyekRiset()
    {
        return $this->belongsTo(ProyekRiset::class, 'proyek_id');
    }
}
