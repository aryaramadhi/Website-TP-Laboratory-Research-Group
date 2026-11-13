<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProyekRiset extends Model
{
    use HasFactory;

    protected $table = 'proyek_riset';
    protected $primaryKey = 'proyek_id';

    protected $fillable = [
        'judul_penelitian_simulasi',
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
        return $this->formatImageUrl($this->dokumentasi_visual_utama);
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
