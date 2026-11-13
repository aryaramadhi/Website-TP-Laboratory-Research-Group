<?php

namespace App\Models;

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
        return $this->formatImageUrl($this->gambar_pendukung);
    }

    public function getSupportingImage1UrlAttribute(): ?string
    {
        return $this->formatImageUrl($this->gambar_pendukung_1);
    }

    public function getSupportingImage2UrlAttribute(): ?string
    {
        return $this->formatImageUrl($this->gambar_pendukung_2);
    }

    protected function formatImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $trimmed = trim($path);
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        $clean = ltrim($trimmed, '/');
        if (str_starts_with($clean, 'images/')) {
            return '/' . $clean;
        }

        if (str_starts_with($clean, 'storage/')) {
            return '/' . $clean;
        }

        return '/storage/' . $clean;
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
