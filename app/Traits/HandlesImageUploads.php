<?php

namespace App\Traits;

use App\Models\BeritaIsu;
use App\Models\PrestasiPencapaian;
use App\Models\ProyekRiset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

trait HandlesImageUploads
{
    /**
     * Memvalidasi bahwa pasangan gambar pendukung harus lengkap (keduanya terisi)
     * atau kosong keduanya (atomik).
     *
     * @param Request $request
     * @param Model|null $model Model saat ini (untuk update)
     * @param string $field1 Nama input file gambar pendukung 1
     * @param string $field2 Nama input file gambar pendukung 2
     * @param string $col1 Nama kolom database untuk gambar pendukung 1
     * @param string $col2 Nama kolom database untuk gambar pendukung 2
     * @throws ValidationException
     */
    public function validateSupportingImagePair(
        Request $request,
        ?Model $model = null,
        string $field1 = 'supporting_image_1',
        string $field2 = 'supporting_image_2',
        string $col1 = 'gambar_pendukung_1',
        string $col2 = 'gambar_pendukung_2'
    ): void {
        $hasFile1 = $request->hasFile($field1);
        $hasFile2 = $request->hasFile($field2);

        $remove1 = filter_var($request->input('remove_' . $field1, false), FILTER_VALIDATE_BOOLEAN);
        $remove2 = filter_var($request->input('remove_' . $field2, false), FILTER_VALIDATE_BOOLEAN);

        // Jika salah satu ditandai untuk dihapus, kedua gambar pendukung harus dihapus
        if ($remove1 || $remove2) {
            $effectiveHas1 = $hasFile1;
            $effectiveHas2 = $hasFile2;
        } else {
            $effectiveHas1 = $hasFile1 || ($model && !empty($model->{$col1}));
            $effectiveHas2 = $hasFile2 || ($model && !empty($model->{$col2}));
        }

        // Keduanya harus ada atau keduanya harus tidak ada
        if ($effectiveHas1 xor $effectiveHas2) {
            throw ValidationException::withMessages([
                $field1 => ['Pasangan gambar pendukung harus lengkap (keduanya terisi) atau dikosongkan keduanya.'],
                $field2 => ['Pasangan gambar pendukung harus lengkap (keduanya terisi) atau dikosongkan keduanya.'],
            ]);
        }
    }

    /**
     * Menyimpan file yang diunggah ke disk public.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return string Path relatif file yang disimpan
     */
    public function uploadCmsFile($file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Menghapus file fisik dari disk public jika tidak lagi direferensikan.
     *
     * @param string|null $path
     * @param Model|null $excludeModel
     */
    public function deleteCmsFile(?string $path, ?Model $excludeModel = null): void
    {
        if (empty($path)) {
            return;
        }

        // Jangan hapus aset eksternal atau gambar statis bawaan
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'images/')) {
            return;
        }

        $cleanPath = ltrim(preg_replace('/^(\/?storage\/)/', '', $path), '/');

        // Pastikan file tidak digunakan oleh record lain sebelum dihapus
        if (!$this->isPathReferencedElsewhere($cleanPath, $excludeModel)) {
            if (Storage::disk('public')->exists($cleanPath)) {
                Storage::disk('public')->delete($cleanPath);
            }
        }
    }

    /**
     * Mengecek apakah path file masih direferensikan oleh record lain di database.
     *
     * @param string $path
     * @param Model|null $excludeModel
     * @return bool
     */
    public function isPathReferencedElsewhere(string $path, ?Model $excludeModel = null): bool
    {
        $cleanPath = ltrim(preg_replace('/^(\/?storage\/)/', '', $path), '/');

        // Cek proyek_riset
        $proyekQuery = ProyekRiset::query()->where(function ($q) use ($cleanPath) {
            $q->where('dokumentasi_visual_utama', $cleanPath)
              ->orWhere('gambar_pendukung_1', $cleanPath)
              ->orWhere('gambar_pendukung_2', $cleanPath);
        });
        if ($excludeModel instanceof ProyekRiset) {
            $proyekQuery->where('proyek_id', '!=', $excludeModel->proyek_id);
        }
        if ($proyekQuery->exists()) {
            return true;
        }

        // Cek berita_isu
        $beritaQuery = BeritaIsu::query()->where(function ($q) use ($cleanPath) {
            $q->where('gambar_pendukung', $cleanPath)
              ->orWhere('gambar_pendukung_1', $cleanPath)
              ->orWhere('gambar_pendukung_2', $cleanPath);
        });
        if ($excludeModel instanceof BeritaIsu) {
            $beritaQuery->where('berita_id', '!=', $excludeModel->berita_id);
        }
        if ($beritaQuery->exists()) {
            return true;
        }

        // Cek prestasi_pencapaian
        $prestasiQuery = PrestasiPencapaian::query()->where(function ($q) use ($cleanPath) {
            $q->where('gambar_utama', $cleanPath)
              ->orWhere('gambar_pendukung_1', $cleanPath)
              ->orWhere('gambar_pendukung_2', $cleanPath)
              ->orWhere('dokumentasi_bukti', $cleanPath);
        });
        if ($excludeModel instanceof PrestasiPencapaian) {
            $prestasiQuery->where('prestasi_id', '!=', $excludeModel->prestasi_id);
        }
        if ($prestasiQuery->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Memformat path relatif menjadi URL yang dapat diakses publik (/storage/... atau /images/...).
     *
     * @param string|null $path
     * @return string|null
     */
    public static function formatAssetUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return '/' . $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/' . $path;
        }

        return '/storage/' . ltrim($path, '/');
    }
}
