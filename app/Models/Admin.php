<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'admins';
    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'email',
        'password',
        'nama_lengkap',
        'peran',
        'status_akses',
        'last_login',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'status_akses' => 'boolean',
    ];

    public function proyekRiset()
    {
        return $this->hasMany(ProyekRiset::class, 'admin_id');
    }

    public function anggotaAlumni()
    {
        return $this->hasMany(AnggotaAlumni::class, 'admin_id');
    }

    public function publikasiHibah()
    {
        return $this->hasMany(PublikasiHibah::class, 'admin_id');
    }

    public function beritaIsu()
    {
        return $this->hasMany(BeritaIsu::class, 'admin_id');
    }
}