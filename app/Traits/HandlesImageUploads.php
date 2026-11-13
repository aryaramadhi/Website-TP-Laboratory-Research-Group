<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    protected function hasMainImageInput(Request $request, string $legacyField, bool $allowProofFallback = false): bool
    {
        if ($request->hasFile('image') || $request->hasFile($legacyField)) {
            return true;
        }
        if ($request->filled($legacyField) && $this->isValidPathString($request->input($legacyField))) {
            return true;
        }
        if ($allowProofFallback) {
            if ($request->hasFile('dokumentasi_bukti') ||
                ($request->filled('dokumentasi_bukti') && $this->isValidPathString($request->input('dokumentasi_bukti')))) {
                return true;
            }
        }
        return false;
    }

    protected function ensureMainImagePresentOnStore(Request $request, string $legacyField, bool $allowProofFallback = false): void
    {
        if (!$this->hasMainImageInput($request, $legacyField, $allowProofFallback)) {
            throw ValidationException::withMessages([
                'image' => ['Gambar utama wajib diisi.'],
            ]);
        }
    }

    protected function rejectMainImageRemovalOnUpdate(Request $request): void
    {
        if ($request->boolean('remove_image')) {
            throw ValidationException::withMessages([
                'image' => ['Gambar utama wajib diisi dan tidak dapat dihapus.'],
            ]);
        }
    }

    protected function hasSupportingImageInput(Request $request, string $fileKey, string $legacyKey): bool
    {
        return $request->hasFile($fileKey)
            || $request->hasFile($legacyKey)
            || ($request->filled($legacyKey) && $this->isValidPathString($request->input($legacyKey)));
    }

    protected function validateSupportingImagePair(Request $request, bool $isUpdate = false, ?object $record = null): void
    {
        $pairMsg = 'Gambar pendukung harus diisi berpasangan. Pilih kedua gambar atau kosongkan keduanya.';
        $removeMsg = 'Gambar pendukung harus dihapus berpasangan.';

        $hasSupp1 = $this->hasSupportingImageInput($request, 'supporting_image_1', 'gambar_pendukung_1');
        $hasSupp2 = $this->hasSupportingImageInput($request, 'supporting_image_2', 'gambar_pendukung_2');

        if (!$isUpdate) {
            if ($hasSupp1 xor $hasSupp2) {
                throw ValidationException::withMessages([
                    'supporting_image_1' => [$pairMsg],
                    'supporting_image_2' => [$pairMsg],
                ]);
            }
            return;
        }

        $remove1 = $request->boolean('remove_supporting_image_1');
        $remove2 = $request->boolean('remove_supporting_image_2');

        if ($remove1 xor $remove2) {
            throw ValidationException::withMessages([
                'supporting_image_1' => [$removeMsg],
                'supporting_image_2' => [$removeMsg],
            ]);
        }

        if (($remove1 || $remove2) && ($hasSupp1 || $hasSupp2)) {
            throw ValidationException::withMessages([
                'supporting_image_1' => [$pairMsg],
                'supporting_image_2' => [$pairMsg],
            ]);
        }

        if ($hasSupp1 xor $hasSupp2) {
            throw ValidationException::withMessages([
                'supporting_image_1' => [$pairMsg],
                'supporting_image_2' => [$pairMsg],
            ]);
        }

        if ($record && !$hasSupp1 && !$hasSupp2 && !$remove1 && !$remove2) {
            $hasExisting1 = !empty($record->gambar_pendukung_1);
            $hasExisting2 = !empty($record->gambar_pendukung_2);
            if ($hasExisting1 xor $hasExisting2) {
                throw ValidationException::withMessages([
                    'supporting_image_1' => [$pairMsg],
                    'supporting_image_2' => [$pairMsg],
                ]);
            }
        }
    }

    protected function applySupportingImagesOnStore(Request $request, string $galleryDir, array &$data): void
    {
        foreach ([1, 2] as $slot) {
            $fileKey = "supporting_image_{$slot}";
            $colKey = "gambar_pendukung_{$slot}";
            $file = $request->file($fileKey) ?? $request->file($colKey);

            if ($file) {
                $data[$colKey] = $this->handleImageUpload($file, $galleryDir);
            } elseif ($request->filled($colKey) && $this->isValidPathString($request->input($colKey))) {
                $data[$colKey] = $request->input($colKey);
            }
        }
    }

    protected function applySupportingImagesOnUpdate(
        Request $request,
        object $record,
        string $galleryDir,
        array &$data,
        array &$pathsToDelete
    ): void {
        $hasSupp1 = $this->hasSupportingImageInput($request, 'supporting_image_1', 'gambar_pendukung_1');
        $hasSupp2 = $this->hasSupportingImageInput($request, 'supporting_image_2', 'gambar_pendukung_2');

        if ($hasSupp1 && $hasSupp2) {
            foreach ([1, 2] as $slot) {
                $fileKey = "supporting_image_{$slot}";
                $colKey = "gambar_pendukung_{$slot}";
                $file = $request->file($fileKey) ?? $request->file($colKey);
                $oldPath = $record->{$colKey};

                if ($file) {
                    $newPath = $this->handleImageUpload($file, $galleryDir);
                    if ($newPath) {
                        $data[$colKey] = $newPath;
                        if ($oldPath && $oldPath !== $newPath) {
                            $pathsToDelete[] = $oldPath;
                        }
                    }
                } elseif ($request->filled($colKey) && $this->isValidPathString($request->input($colKey))) {
                    $newPath = $request->input($colKey);
                    $data[$colKey] = $newPath;
                    if ($oldPath && $oldPath !== $newPath) {
                        $pathsToDelete[] = $oldPath;
                    }
                }
            }
        } elseif ($request->boolean('remove_supporting_image_1') && $request->boolean('remove_supporting_image_2')) {
            if ($record->gambar_pendukung_1) {
                $pathsToDelete[] = $record->gambar_pendukung_1;
            }
            if ($record->gambar_pendukung_2) {
                $pathsToDelete[] = $record->gambar_pendukung_2;
            }
            $data['gambar_pendukung_1'] = null;
            $data['gambar_pendukung_2'] = null;
        }
    }
}
