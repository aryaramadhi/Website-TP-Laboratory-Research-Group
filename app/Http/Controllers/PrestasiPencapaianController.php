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
        $prestasi = PrestasiPencapaian::with(['anggotaAlumni', 'proyekRiset'])
            ->orderBy('created_at', 'desc')
            ->orderBy('prestasi_id', 'desc')
            ->get();

        return response()->json($prestasi);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_prestasi'      => 'required|string|max:255',
            'penyelenggara'      => 'nullable|string',
            'tingkat'            => 'nullable|string',
            'tahun_diperoleh'    => 'nullable|integer',
            'deskripsi'          => 'nullable|string',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_utama'       => 'nullable',
            'dokumentasi_bukti'  => $this->dokumentasiBuktiValidationRule($request),
            'supporting_image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1' => 'nullable',
            'supporting_image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2' => 'nullable',
            'anggota_alumni_id'  => 'required|exists:anggota_alumni,anggota_alumni_id',
            'proyek_id'          => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        $this->ensureMainImagePresentOnStore($request, 'gambar_utama', true);
        $this->validateSupportingImagePair($request, false);

        $data = [
            'nama_prestasi'     => $validated['nama_prestasi'],
            'penyelenggara'     => $validated['penyelenggara'] ?? null,
            'tingkat'           => $validated['tingkat'] ?? null,
            'tahun_diperoleh'   => $validated['tahun_diperoleh'] ?? null,
            'deskripsi'         => $validated['deskripsi'] ?? null,
            'anggota_alumni_id' => $validated['anggota_alumni_id'],
            'proyek_id'         => $validated['proyek_id'] ?? null,
        ];

        $mainFile = $request->file('image') ?? $request->file('gambar_utama');
        if ($mainFile) {
            $data['gambar_utama'] = $this->handleImageUpload($mainFile, 'achievements');
        } elseif ($request->filled('gambar_utama') && $this->isValidPathString($request->input('gambar_utama'))) {
            $data['gambar_utama'] = $request->input('gambar_utama');
        }

        if ($request->hasFile('dokumentasi_bukti')) {
            $proofFile = $request->file('dokumentasi_bukti');
            $data['dokumentasi_bukti'] = $proofFile->store('achievements/documents', 'public');
        } elseif ($request->filled('dokumentasi_bukti') && $this->isValidPathString($request->input('dokumentasi_bukti'))) {
            $data['dokumentasi_bukti'] = $request->input('dokumentasi_bukti');
        }

        $this->applySupportingImagesOnStore($request, 'achievements/gallery', $data);

        $prestasi = PrestasiPencapaian::create($data);

        return response()->json([
            'message' => 'Prestasi/Pencapaian berhasil ditambahkan',
            'data' => $prestasi->fresh(['anggotaAlumni', 'proyekRiset'])
        ], 201);
    }

    public function show($id)
    {
        $prestasi = PrestasiPencapaian::with(['anggotaAlumni', 'proyekRiset'])->findOrFail($id);
        return response()->json($prestasi);
    }

    public function update(Request $request, $id)
    {
        $prestasi = PrestasiPencapaian::findOrFail($id);

        $validated = $request->validate([
            'nama_prestasi'      => 'sometimes|required|string|max:255',
            'penyelenggara'      => 'nullable|string',
            'tingkat'            => 'nullable|string',
            'tahun_diperoleh'    => 'nullable|integer',
            'deskripsi'          => 'nullable|string',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_utama'       => 'nullable',
            'dokumentasi_bukti'  => $this->dokumentasiBuktiValidationRule($request),
            'supporting_image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1' => 'nullable',
            'supporting_image_2'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2'        => 'nullable',
            'remove_image'              => 'nullable|in:0,1,true,false',
            'remove_supporting_image_1' => 'nullable|in:0,1,true,false',
            'remove_supporting_image_2' => 'nullable|in:0,1,true,false',
            'anggota_alumni_id'         => 'sometimes|required|exists:anggota_alumni,anggota_alumni_id',
            'proyek_id'                 => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        $this->rejectMainImageRemovalOnUpdate($request);
        $this->validateSupportingImagePair($request, true, $prestasi);

        $data = [];
        foreach (['nama_prestasi', 'penyelenggara', 'tingkat', 'tahun_diperoleh', 'deskripsi', 'anggota_alumni_id', 'proyek_id'] as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        $pathsToDelete = [];

        $mainFile = $request->file('image') ?? $request->file('gambar_utama');
        if ($mainFile) {
            $oldPath = $prestasi->gambar_utama;
            $newPath = $this->handleImageUpload($mainFile, 'achievements');
            if ($newPath) {
                $data['gambar_utama'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_utama') && $this->isValidPathString($request->input('gambar_utama'))) {
            $data['gambar_utama'] = $request->input('gambar_utama');
        }

        if ($request->hasFile('dokumentasi_bukti')) {
            $proofFile = $request->file('dokumentasi_bukti');
            $oldPath = $prestasi->dokumentasi_bukti;
            $newPath = $proofFile->store('achievements/documents', 'public');
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
        $this->deleteStoredImages($pathsToDelete);

        return response()->json([
            'message' => 'Prestasi/Pencapaian berhasil diperbarui',
            'data' => $prestasi->fresh(['anggotaAlumni', 'proyekRiset'])
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
            return 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:2048';
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
