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

        $this->ensureMainImagePresentOnStore($request, 'dokumentasi_visual_utama');
        $this->validateSupportingImagePair($request, false);

        $data = [
            'judul_penelitian_simulasi' => $validated['judul_penelitian_simulasi'],
            'deskripsi_singkat'        => $validated['deskripsi_singkat'],
            'tanggal_mulai'            => $validated['tanggal_mulai'],
            'tanggal_selesai'          => $validated['tanggal_selesai'] ?? null,
            'status_proyek'            => $validated['status_proyek'],
            'admin_id'                 => $validated['admin_id'],
        ];

        $mainFile = $request->file('image') ?? $request->file('dokumentasi_visual_utama');
        if ($mainFile) {
            $data['dokumentasi_visual_utama'] = $this->handleImageUpload($mainFile, 'researches');
        } elseif ($request->filled('dokumentasi_visual_utama') && $this->isValidPathString($request->input('dokumentasi_visual_utama'))) {
            $data['dokumentasi_visual_utama'] = $request->input('dokumentasi_visual_utama');
        }

        $this->applySupportingImagesOnStore($request, 'researches/gallery', $data);

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
            'remove_image'             => 'nullable|in:0,1,true,false',
            'remove_supporting_image_1' => 'nullable|in:0,1,true,false',
            'remove_supporting_image_2' => 'nullable|in:0,1,true,false',
        ]);

        $this->rejectMainImageRemovalOnUpdate($request);
        $this->validateSupportingImagePair($request, true, $proyek);

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

        $this->applySupportingImagesOnUpdate($request, $proyek, 'researches/gallery', $data, $pathsToDelete);

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
