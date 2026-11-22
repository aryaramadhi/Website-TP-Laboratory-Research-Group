<?php

namespace App\Http\Controllers;

use App\Models\PrestasiPencapaian;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;

class PrestasiPencapaianController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $prestasi = PrestasiPencapaian::with('proyekRiset')
            ->orderByDesc('created_at')
            ->orderByDesc('prestasi_id')
            ->get();

        return response()->json($prestasi);
    }

    public function store(Request $request)
    {
        $adminId = $request->user()?->admin_id;
        if (!$adminId) {
            return response()->json([
                'message' => 'Akses ditolak. Identitas admin tidak valid.'
            ], 403);
        }

        if (!$request->has('penulis') && $request->has('author')) {
            $request->merge(['penulis' => $request->input('author')]);
        }

        $validated = $request->validate([
            'nama_prestasi'      => 'required|string|max:255',
            'penulis'            => 'nullable|string|max:255',
            'penyelenggara'      => 'nullable|string|max:255',
            'tingkat'            => 'nullable|string|max:100',
            'tahun_diperoleh'    => 'nullable|integer',
            'deskripsi'          => 'nullable|string',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_utama'       => 'nullable',
            'dokumentasi_bukti'  => $this->dokumentasiBuktiValidationRule($request),
            'document'           => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
            'supporting_image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1' => 'nullable',
            'supporting_image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2' => 'nullable',
            'proyek_id'          => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        $this->ensureMainImagePresentOnStore($request, 'gambar_utama', true);
        $this->validateSupportingImagePair($request, false);

        $penulis = isset($validated['penulis']) ? trim((string) $validated['penulis']) : '';
        $data = [
            'nama_prestasi'   => $validated['nama_prestasi'],
            'penulis'         => $penulis !== '' ? $penulis : null,
            'penyelenggara'   => $validated['penyelenggara'] ?? null,
            'tingkat'         => $validated['tingkat'] ?? null,
            'tahun_diperoleh' => $validated['tahun_diperoleh'] ?? null,
            'deskripsi'       => $validated['deskripsi'] ?? null,
            'proyek_id'       => $validated['proyek_id'] ?? null,
        ];

        $mainFile = $request->file('image') ?? $request->file('gambar_utama');
        if ($mainFile) {
            $data['gambar_utama'] = $this->uploadCmsFile($mainFile, 'achievements');
        } elseif ($request->filled('gambar_utama') && $this->isValidPathString($request->input('gambar_utama'))) {
            $data['gambar_utama'] = $request->input('gambar_utama');
        }

        $proofFile = $request->file('dokumentasi_bukti') ?? $request->file('document');
        if ($proofFile) {
            $data['dokumentasi_bukti'] = $this->uploadCmsFile($proofFile, 'achievements/documents');
        } elseif ($request->filled('dokumentasi_bukti') && $this->isValidPathString($request->input('dokumentasi_bukti'))) {
            $data['dokumentasi_bukti'] = $request->input('dokumentasi_bukti');
        }

        $this->applySupportingImagesOnStore($request, 'achievements/gallery', $data);

        $prestasi = PrestasiPencapaian::create($data);

        return response()->json([
            'message' => 'Prestasi/Pencapaian berhasil ditambahkan',
            'data'    => $prestasi->fresh('proyekRiset')
        ], 201);
    }

    public function show($id)
    {
        $prestasi = PrestasiPencapaian::with('proyekRiset')->findOrFail($id);
        return response()->json($prestasi);
    }

    public function update(Request $request, $id)
    {
        $adminId = $request->user()?->admin_id;
        if (!$adminId) {
            return response()->json([
                'message' => 'Akses ditolak. Identitas admin tidak valid.'
            ], 403);
        }

        $prestasi = PrestasiPencapaian::findOrFail($id);

        if (!$request->has('penulis') && $request->has('author')) {
            $request->merge(['penulis' => $request->input('author')]);
        }

        $validated = $request->validate([
            'nama_prestasi'             => 'sometimes|required|string|max:255',
            'penulis'                   => 'nullable|string|max:255',
            'penyelenggara'             => 'nullable|string|max:255',
            'tingkat'                   => 'nullable|string|max:100',
            'tahun_diperoleh'           => 'nullable|integer',
            'deskripsi'                 => 'nullable|string',
            'image'                     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_utama'              => 'nullable',
            'dokumentasi_bukti'         => $this->dokumentasiBuktiValidationRule($request),
            'document'                  => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
            'supporting_image_1'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1'        => 'nullable',
            'supporting_image_2'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2'        => 'nullable',
            'remove_image'              => 'nullable|in:0,1,true,false',
            'remove_supporting_image_1' => 'nullable|in:0,1,true,false',
            'remove_supporting_image_2' => 'nullable|in:0,1,true,false',
            'proyek_id'                 => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        $this->rejectMainImageRemovalOnUpdate($request);
        $this->validateSupportingImagePair($request, true, $prestasi);

        $data = [];
        foreach (['nama_prestasi', 'penulis', 'penyelenggara', 'tingkat', 'tahun_diperoleh', 'deskripsi', 'proyek_id'] as $field) {
            if ($request->has($field)) {
                $val = $validated[$field] ?? $request->input($field);
                if ($field === 'penulis') {
                    $trimmed = is_string($val) ? trim($val) : '';
                    $data[$field] = $trimmed !== '' ? $trimmed : null;
                } else {
                    $data[$field] = $val;
                }
            }
        }

        $pathsToDelete = [];

        $mainFile = $request->file('image') ?? $request->file('gambar_utama');
        if ($mainFile) {
            $oldPath = $prestasi->gambar_utama;
            $newPath = $this->uploadCmsFile($mainFile, 'achievements');
            if ($newPath) {
                $data['gambar_utama'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_utama') && $this->isValidPathString($request->input('gambar_utama'))) {
            $data['gambar_utama'] = $request->input('gambar_utama');
        }

        $proofFile = $request->file('dokumentasi_bukti') ?? $request->file('document');
        if ($proofFile) {
            $oldPath = $prestasi->dokumentasi_bukti;
            $newPath = $this->uploadCmsFile($proofFile, 'achievements/documents');
            if ($newPath) {
                $data['dokumentasi_bukti'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('dokumentasi_bukti') && $this->isValidPathString($request->input('dokumentasi_bukti'))) {
            $data['dokumentasi_bukti'] = $request->input('dokumentasi_bukti');
        }

        $this->applySupportingImagesOnUpdate($request, $prestasi, 'achievements/gallery', $data, $pathsToDelete);

        $prestasi->update($data);
        $this->deleteStoredImages($pathsToDelete, $prestasi);

        return response()->json([
            'message' => 'Prestasi/Pencapaian berhasil diperbarui',
            'data'    => $prestasi->fresh('proyekRiset')
        ]);
    }

    public function destroy($id)
    {
        $prestasi = PrestasiPencapaian::findOrFail($id);

        $pathsToDelete = [
            $prestasi->gambar_utama,
            $prestasi->dokumentasi_bukti,
            $prestasi->gambar_pendukung_1,
            $prestasi->gambar_pendukung_2,
        ];

        $prestasi->delete();
        $this->deleteStoredImages($pathsToDelete);

        return response()->json(['message' => 'Prestasi/Pencapaian berhasil dihapus']);
    }

    protected function dokumentasiBuktiValidationRule(Request $request): string|array
    {
        if ($request->hasFile('dokumentasi_bukti')) {
            return 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120';
        }

        return [
            'nullable',
            'string',
            'max:255',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || trim((string) $value) === '') {
                    return;
                }

                $trimmed = trim((string) $value);
                if (!$this->isValidPathString($trimmed) ||
                    str_contains($trimmed, '..') ||
                    str_contains($trimmed, '//') ||
                    str_contains($trimmed, '\\') ||
                    !preg_match('/\A\/?[a-zA-Z0-9_.\-\/]+\.(pdf|jpe?g|png|webp)\z/i', $trimmed)) {
                    $fail('Format jalur dokumentasi bukti tidak valid.');
                }
            },
        ];
    }
}
