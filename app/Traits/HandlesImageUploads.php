<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImageUploads
{
    /**
     * Upload an image file to the public disk and return its relative path.
     */
    protected function handleImageUpload(?UploadedFile $file, string $directory): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = Str::random(32) . '.' . strtolower($extension);

        return $file->storeAs($directory, $fileName, 'public');
    }

    /**
     * Delete a stored image after verifying no remaining DB record references it.
     */
    protected function deleteStoredImage(?string $path): void
    {
        $cleanPath = $this->normalizeStoredPath($path);
        if (!$cleanPath || $this->isImagePathInUse($cleanPath)) {
            return;
        }

        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        }
    }

    /**
     * Delete multiple stored images after database persistence succeeds.
     *
     * @param array<int, ?string> $paths
     */
    protected function deleteStoredImages(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            $this->deleteStoredImage($path);
        }
    }

    protected function normalizeStoredPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $cleanPath = ltrim($path, '/');

        // Never delete template/seeder assets or external URLs.
        if (str_starts_with($cleanPath, 'images/') ||
            str_starts_with($cleanPath, 'http://') ||
            str_starts_with($cleanPath, 'https://')) {
            return null;
        }

        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        return $cleanPath !== '' ? $cleanPath : null;
    }

    protected function isImagePathInUse(string $cleanPath): bool
    {
        $candidates = [$cleanPath, 'storage/' . $cleanPath];
        $tableColumns = [
            'proyek_riset' => ['dokumentasi_visual_utama', 'gambar_pendukung_1', 'gambar_pendukung_2'],
            'berita_isu' => ['gambar_pendukung', 'gambar_pendukung_1', 'gambar_pendukung_2'],
            'prestasi_pencapaian' => ['gambar_utama', 'dokumentasi_bukti', 'gambar_pendukung_1', 'gambar_pendukung_2'],
        ];

        foreach ($tableColumns as $table => $columns) {
            $exists = DB::table($table)->where(function ($query) use ($columns, $candidates) {
                foreach ($columns as $column) {
                    $query->orWhereIn($column, $candidates);
                }
            })->exists();

            if ($exists) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a string is a valid image path and not a fake browser path.
     */
    protected function isValidPathString(?string $value): bool
    {
        if (!$value || !is_string($value)) {
            return false;
        }

        $trimmed = trim($value);
        if ($trimmed === '' ||
            str_contains(strtolower($trimmed), 'fakepath') ||
            str_starts_with($trimmed, 'data:') ||
            str_starts_with($trimmed, 'javascript:')) {
            return false;
        }

        return true;
    }
}
