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
        'topik_utama',
        'admin_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}