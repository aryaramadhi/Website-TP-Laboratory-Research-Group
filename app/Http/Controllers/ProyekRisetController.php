<?php

namespace App\Http\Controllers;

use App\Models\ProyekRiset;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;

class ProyekRisetController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $proyek = ProyekRiset::with('admin')
            ->orderBy('created_at', 'desc')
            ->orderBy('proyek_id', 'desc')
            ->get();

        return response()->json([
            'message' => 'Daftar proyek riset berhasil diambil',
            'data' => $proyek
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_penelitian_simulasi' => 'required|string|max:255',
            'deskripsi_singkat'        => 'required|string',
            'tanggal_mulai'            => 'required|date',
            'tanggal_selesai'          => 'nullable|date',
            'status_proyek'            => 'required|string|max:50',
            'admin_id'                 => 'required|exists:admins,admin_id',
            'image'                    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'dokumentasi_visual_utama' => 'nullable',
            'supporting_image_1'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1'       => 'nullable',
            'supporting_image_2'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2'       => 'nullable',
        ]);

        $data = [
            'judul_penelitian_simulasi' => $validated['judul_penelitian_simulasi'],
            'deskripsi_singkat'        => $validated['deskripsi_singkat'],
            'tanggal_mulai'            => $validated['tanggal_mulai'],
            'tanggal_selesai'          => $validated['tanggal_selesai'] ?? null,
            'status_proyek'            => $validated['status_proyek'],
            'admin_id'                 => $validated['admin_id'],
        ];

        // Main image upload
        $mainFile = $request->file('image') ?? $request->file('dokumentasi_visual_utama');
        if ($mainFile) {
            $data['dokumentasi_visual_utama'] = $this->handleImageUpload($mainFile, 'researches');
        } elseif ($request->filled('dokumentasi_visual_utama') && $this->isValidPathString($request->input('dokumentasi_visual_utama'))) {
            $data['dokumentasi_visual_utama'] = $request->input('dokumentasi_visual_utama');
        }

        // Supporting image 1
        $supp1File = $request->file('supporting_image_1') ?? $request->file('gambar_pendukung_1');
        if ($supp1File) {
            $data['gambar_pendukung_1'] = $this->handleImageUpload($supp1File, 'researches/gallery');
        } elseif ($request->filled('gambar_pendukung_1') && $this->isValidPathString($request->input('gambar_pendukung_1'))) {
            $data['gambar_pendukung_1'] = $request->input('gambar_pendukung_1');
        }

        // Supporting image 2
        $supp2File = $request->file('supporting_image_2') ?? $request->file('gambar_pendukung_2');
        if ($supp2File) {
            $data['gambar_pendukung_2'] = $this->handleImageUpload($supp2File, 'researches/gallery');
        } elseif ($request->filled('gambar_pendukung_2') && $this->isValidPathString($request->input('gambar_pendukung_2'))) {
            $data['gambar_pendukung_2'] = $request->input('gambar_pendukung_2');
        }

        $proyek = ProyekRiset::create($data);

        return response()->json([
            'message' => 'Proyek riset berhasil ditambahkan',
            'data'    => $proyek->fresh()
        ], 201);
    }

    public function show(string $id)
    {
        $proyek = ProyekRiset::with('admin')->find($id);

        if (!$proyek) {
            return response()->json(['message' => 'Proyek riset tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail proyek riset berhasil diambil',
            'data'    => $proyek
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $proyek = ProyekRiset::find($id);

        if (!$proyek) {
            return response()->json(['message' => 'Proyek riset tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'judul_penelitian_simulasi' => 'sometimes|required|string|max:255',
            'deskripsi_singkat'        => 'sometimes|required|string',
            'tanggal_mulai'            => 'sometimes|required|date',
            'tanggal_selesai'          => 'nullable|date',
            'status_proyek'            => 'sometimes|required|string|max:50',
            'admin_id'                 => 'sometimes|required|exists:admins,admin_id',
            'image'                    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'dokumentasi_visual_utama' => 'nullable',
            'supporting_image_1'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1'       => 'nullable',
            'supporting_image_2'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2'       => 'nullable',
        ]);

        $data = [];
        foreach (['judul_penelitian_simulasi', 'deskripsi_singkat', 'tanggal_mulai', 'status_proyek', 'admin_id'] as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }
        if ($request->has('tanggal_selesai')) {
            $data['tanggal_selesai'] = $request->input('tanggal_selesai');
        }

        $pathsToDelete = [];

        $mainFile = $request->file('image') ?? $request->file('dokumentasi_visual_utama');
        if ($mainFile) {
            $oldPath = $proyek->dokumentasi_visual_utama;
            $newPath = $this->handleImageUpload($mainFile, 'researches');
            if ($newPath) {
                $data['dokumentasi_visual_utama'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('dokumentasi_visual_utama') && $this->isValidPathString($request->input('dokumentasi_visual_utama'))) {
            $data['dokumentasi_visual_utama'] = $request->input('dokumentasi_visual_utama');
        }

        $supp1File = $request->file('supporting_image_1') ?? $request->file('gambar_pendukung_1');
        if ($supp1File) {
            $oldPath = $proyek->gambar_pendukung_1;
            $newPath = $this->handleImageUpload($supp1File, 'researches/gallery');
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
            $oldPath = $proyek->gambar_pendukung_2;
            $newPath = $this->handleImageUpload($supp2File, 'researches/gallery');
            if ($newPath) {
                $data['gambar_pendukung_2'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_pendukung_2') && $this->isValidPathString($request->input('gambar_pendukung_2'))) {
            $data['gambar_pendukung_2'] = $request->input('gambar_pendukung_2');
        }

        $proyek->update($data);
        $this->deleteStoredImages($pathsToDelete);

        return response()->json([
            'message' => 'Proyek riset berhasil diperbarui',
            'data'    => $proyek->fresh()
        ], 200);
    }

    public function destroy(string $id)
    {
        $proyek = ProyekRiset::find($id);

        if (!$proyek) {
            return response()->json(['message' => 'Proyek riset tidak ditemukan'], 404);
        }

        $pathsToDelete = [
            $proyek->dokumentasi_visual_utama,
            $proyek->gambar_pendukung_1,
            $proyek->gambar_pendukung_2,
        ];

        $proyek->delete();
        $this->deleteStoredImages($pathsToDelete);

        return response()->json([
            'message' => 'Proyek riset berhasil dihapus'
        ], 200);
    }
}
