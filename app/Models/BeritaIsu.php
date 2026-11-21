<?php

namespace App\Models;

use App\Traits\HandlesImageUploads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaIsu extends Model
{
    use HasFactory;

    protected $table = 'berita_isu';
    protected $primaryKey = 'berita_id';

    protected $fillable = [
        'judul_berita',
        'tanggal_rilis',
        'isi_artikel_ringkas',
        'gambar_pendukung',
        'gambar_pendukung_1',
        'gambar_pendukung_2',
        'topik_utama',
        'admin_id',
    ];

    protected $appends = [
        'image_url',
        'supporting_image_1_url',
        'supporting_image_2_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->gambar_pendukung);
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
}
