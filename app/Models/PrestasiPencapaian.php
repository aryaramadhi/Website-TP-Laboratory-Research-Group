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
        if ($this->gambar_utama) {
            return HandlesImageUploads::formatAssetUrl($this->gambar_utama);
        }

        // Backward compatibility fallback: if dokumentasi_bukti is an image (not PDF), use it
        if ($this->dokumentasi_bukti && !$this->isPdf($this->dokumentasi_bukti)) {
            return HandlesImageUploads::formatAssetUrl($this->dokumentasi_bukti);
        }

        return null;
    }

    public function getDocumentUrlAttribute(): ?string
    {
        if ($this->dokumentasi_bukti && $this->isPdf($this->dokumentasi_bukti)) {
            return HandlesImageUploads::formatAssetUrl($this->dokumentasi_bukti);
        }

        return null;
    }

    public function getSupportingImage1UrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->gambar_pendukung_1);
    }

    public function getSupportingImage2UrlAttribute(): ?string
    {
        return HandlesImageUploads::formatAssetUrl($this->gambar_pendukung_2);
    }

    protected function isPdf(?string $path): bool
    {
        if (!$path) {
            return false;
        }
        $clean = strtolower(trim(explode('?', $path)[0]));
        return str_ends_with($clean, '.pdf');
    }

    public function proyekRiset()
    {
        return $this->belongsTo(ProyekRiset::class, 'proyek_id');
    }
}
