<?php

namespace App\Http\Controllers;

use App\Models\BeritaIsu;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;

class BeritaIsuController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $berita = BeritaIsu::with('admin')
            ->orderByDesc('created_at')
            ->orderByDesc('berita_id')
            ->get();

        return response()->json($berita);
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
            'judul_berita'        => 'required|string|max:255',
            'penulis'             => 'nullable|string|max:255',
            'tanggal_rilis'       => 'nullable|date',
            'isi_artikel_ringkas' => 'nullable|string',
            'gambar_pendukung'    => 'nullable',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_1'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1'  => 'nullable',
            'supporting_image_2'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2'  => 'nullable',
            'topik_utama'         => 'nullable|string|max:100',
        ]);

        $this->ensureMainImagePresentOnStore($request, 'gambar_pendukung');
        $this->validateSupportingImagePair($request, false);

        $penulis = isset($validated['penulis']) ? trim((string) $validated['penulis']) : '';
        $data = [
            'judul_berita'        => $validated['judul_berita'],
            'penulis'             => $penulis !== '' ? $penulis : null,
            'tanggal_rilis'       => $validated['tanggal_rilis'] ?? null,
            'isi_artikel_ringkas' => $validated['isi_artikel_ringkas'] ?? null,
            'topik_utama'         => $validated['topik_utama'] ?? null,
            'admin_id'            => $adminId,
        ];

        $mainFile = $request->file('image') ?? $request->file('gambar_pendukung');
        if ($mainFile) {
            $data['gambar_pendukung'] = $this->uploadCmsFile($mainFile, 'news');
        } elseif ($request->filled('gambar_pendukung') && $this->isValidPathString($request->input('gambar_pendukung'))) {
            $data['gambar_pendukung'] = $request->input('gambar_pendukung');
        }

        $this->applySupportingImagesOnStore($request, 'news/gallery', $data);

        $berita = BeritaIsu::create($data);

        return response()->json([
            'message' => 'Berita/Isu berhasil dibuat',
            'data'    => $berita->fresh('admin')
        ], 201);
    }

    public function show($id)
    {
        $berita = BeritaIsu::with('admin')->findOrFail($id);
        return response()->json($berita);
    }

    public function update(Request $request, $id)
    {
        $adminId = $request->user()?->admin_id;
        if (!$adminId) {
            return response()->json([
                'message' => 'Akses ditolak. Identitas admin tidak valid.'
            ], 403);
        }

        $berita = BeritaIsu::findOrFail($id);

        if (!$request->has('penulis') && $request->has('author')) {
            $request->merge(['penulis' => $request->input('author')]);
        }

        $validated = $request->validate([
            'judul_berita'              => 'sometimes|required|string|max:255',
            'penulis'                   => 'nullable|string|max:255',
            'tanggal_rilis'             => 'nullable|date',
            'isi_artikel_ringkas'       => 'nullable|string',
            'gambar_pendukung'          => 'nullable',
            'image'                     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_1'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1'        => 'nullable',
            'supporting_image_2'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2'        => 'nullable',
            'remove_image'              => 'nullable|in:0,1,true,false',
            'remove_supporting_image_1' => 'nullable|in:0,1,true,false',
            'remove_supporting_image_2' => 'nullable|in:0,1,true,false',
            'topik_utama'               => 'nullable|string|max:100',
        ]);

        $this->rejectMainImageRemovalOnUpdate($request);
        $this->validateSupportingImagePair($request, true, $berita);

        $data = [];
        foreach (['judul_berita', 'penulis', 'tanggal_rilis', 'isi_artikel_ringkas', 'topik_utama'] as $field) {
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

        $mainFile = $request->file('image') ?? $request->file('gambar_pendukung');
        if ($mainFile) {
            $oldPath = $berita->gambar_pendukung;
            $newPath = $this->uploadCmsFile($mainFile, 'news');
            if ($newPath) {
                $data['gambar_pendukung'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_pendukung') && $this->isValidPathString($request->input('gambar_pendukung'))) {
            $data['gambar_pendukung'] = $request->input('gambar_pendukung');
        }

        $this->applySupportingImagesOnUpdate($request, $berita, 'news/gallery', $data, $pathsToDelete);

        $berita->update($data);
        $this->deleteStoredImages($pathsToDelete, $berita);

        return response()->json([
            'message' => 'Berita/Isu berhasil diperbarui',
            'data'    => $berita->fresh('admin')
        ]);
    }

    public function destroy($id)
    {
        $berita = BeritaIsu::findOrFail($id);

        $pathsToDelete = [
            $berita->gambar_pendukung,
            $berita->gambar_pendukung_1,
            $berita->gambar_pendukung_2,
        ];

        $berita->delete();
        $this->deleteStoredImages($pathsToDelete);

        return response()->json(['message' => 'Berita/Isu berhasil dihapus']);
    }
}
