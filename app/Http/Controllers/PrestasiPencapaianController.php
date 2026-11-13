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
            'dokumentasi_bukti'  => 'nullable',
            'supporting_image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1' => 'nullable',
            'supporting_image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2' => 'nullable',
            'anggota_alumni_id'  => 'required|exists:anggota_alumni,anggota_alumni_id',
            'proyek_id'          => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        $data = [
            'nama_prestasi'     => $validated['nama_prestasi'],
            'penyelenggara'     => $validated['penyelenggara'] ?? null,
            'tingkat'           => $validated['tingkat'] ?? null,
            'tahun_diperoleh'   => $validated['tahun_diperoleh'] ?? null,
            'deskripsi'         => $validated['deskripsi'] ?? null,
            'anggota_alumni_id' => $validated['anggota_alumni_id'],
            'proyek_id'         => $validated['proyek_id'] ?? null,
        ];

        // Main photo upload
        $mainFile = $request->file('image') ?? $request->file('gambar_utama');
        if ($mainFile) {
            $data['gambar_utama'] = $this->handleImageUpload($mainFile, 'achievements');
        } elseif ($request->filled('gambar_utama') && $this->isValidPathString($request->input('gambar_utama'))) {
            $data['gambar_utama'] = $request->input('gambar_utama');
        }

        // Proof document (file or string)
        if ($request->hasFile('dokumentasi_bukti')) {
            $proofFile = $request->file('dokumentasi_bukti');
            $data['dokumentasi_bukti'] = $proofFile->store('achievements/documents', 'public');
        } elseif ($request->filled('dokumentasi_bukti') && $this->isValidPathString($request->input('dokumentasi_bukti'))) {
            $data['dokumentasi_bukti'] = $request->input('dokumentasi_bukti');
        }

        // Supporting image 1
        $supp1File = $request->file('supporting_image_1') ?? $request->file('gambar_pendukung_1');
        if ($supp1File) {
            $data['gambar_pendukung_1'] = $this->handleImageUpload($supp1File, 'achievements/gallery');
        } elseif ($request->filled('gambar_pendukung_1') && $this->isValidPathString($request->input('gambar_pendukung_1'))) {
            $data['gambar_pendukung_1'] = $request->input('gambar_pendukung_1');
        }

        // Supporting image 2
        $supp2File = $request->file('supporting_image_2') ?? $request->file('gambar_pendukung_2');
        if ($supp2File) {
            $data['gambar_pendukung_2'] = $this->handleImageUpload($supp2File, 'achievements/gallery');
        } elseif ($request->filled('gambar_pendukung_2') && $this->isValidPathString($request->input('gambar_pendukung_2'))) {
            $data['gambar_pendukung_2'] = $request->input('gambar_pendukung_2');
        }

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
            'dokumentasi_bukti'  => 'nullable',
            'supporting_image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1' => 'nullable',
            'supporting_image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2' => 'nullable',
            'anggota_alumni_id'  => 'sometimes|required|exists:anggota_alumni,anggota_alumni_id',
            'proyek_id'          => 'nullable|exists:proyek_riset,proyek_id',
        ]);

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

        $supp1File = $request->file('supporting_image_1') ?? $request->file('gambar_pendukung_1');
        if ($supp1File) {
            $oldPath = $prestasi->gambar_pendukung_1;
            $newPath = $this->handleImageUpload($supp1File, 'achievements/gallery');
            if ($newPath) {
                $data['gambar_pendukung_1'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_pendukung_1') && $this->isValidPathString($request->input('gambar_pendukung_1'))) {
            $data['gambar_pendukung_1'] = $request->input('gambar_pendukung_1');
        }

        $supp2File = $request->file('supporting_image_2') ?? $request->file('gambar_pendukung_2');
        if ($supp2File) {
            $oldPath = $prestasi->gambar_pendukung_2;
            $newPath = $this->handleImageUpload($supp2File, 'achievements/gallery');
            if ($newPath) {
                $data['gambar_pendukung_2'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_pendukung_2') && $this->isValidPathString($request->input('gambar_pendukung_2'))) {
            $data['gambar_pendukung_2'] = $request->input('gambar_pendukung_2');
        }

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
}
