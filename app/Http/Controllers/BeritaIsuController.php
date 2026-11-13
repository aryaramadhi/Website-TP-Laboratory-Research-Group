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
            ->orderBy('created_at', 'desc')
            ->orderBy('berita_id', 'desc')
            ->get();

        return response()->json($berita);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_berita'         => 'required|string|max:255',
            'tanggal_rilis'        => 'nullable|date',
            'isi_artikel_ringkas'  => 'nullable|string',
            'gambar_pendukung'     => 'nullable',
            'image'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_1'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1'   => 'nullable',
            'supporting_image_2'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2'   => 'nullable',
            'topik_utama'          => 'nullable|string',
            'admin_id'             => 'required|exists:admins,admin_id',
        ]);

        $data = [
            'judul_berita'        => $validated['judul_berita'],
            'tanggal_rilis'       => $validated['tanggal_rilis'] ?? null,
            'isi_artikel_ringkas' => $validated['isi_artikel_ringkas'] ?? null,
            'topik_utama'         => $validated['topik_utama'] ?? null,
            'admin_id'            => $validated['admin_id'],
        ];

        // Main image
        $mainFile = $request->file('image') ?? $request->file('gambar_pendukung');
        if ($mainFile) {
            $data['gambar_pendukung'] = $this->handleImageUpload($mainFile, 'news');
        } elseif ($request->filled('gambar_pendukung') && $this->isValidPathString($request->input('gambar_pendukung'))) {
            $data['gambar_pendukung'] = $request->input('gambar_pendukung');
        }

        // Supporting image 1
        $supp1File = $request->file('supporting_image_1') ?? $request->file('gambar_pendukung_1');
        if ($supp1File) {
            $data['gambar_pendukung_1'] = $this->handleImageUpload($supp1File, 'news/gallery');
        } elseif ($request->filled('gambar_pendukung_1') && $this->isValidPathString($request->input('gambar_pendukung_1'))) {
            $data['gambar_pendukung_1'] = $request->input('gambar_pendukung_1');
        }

        // Supporting image 2
        $supp2File = $request->file('supporting_image_2') ?? $request->file('gambar_pendukung_2');
        if ($supp2File) {
            $data['gambar_pendukung_2'] = $this->handleImageUpload($supp2File, 'news/gallery');
        } elseif ($request->filled('gambar_pendukung_2') && $this->isValidPathString($request->input('gambar_pendukung_2'))) {
            $data['gambar_pendukung_2'] = $request->input('gambar_pendukung_2');
        }

        $berita = BeritaIsu::create($data);

        return response()->json([
            'message' => 'Berita/Isu berhasil dibuat',
            'data' => $berita->fresh()
        ], 201);
    }

    public function show($id)
    {
        $berita = BeritaIsu::with('admin')->findOrFail($id);
        return response()->json($berita);
    }

    public function update(Request $request, $id)
    {
        $berita = BeritaIsu::findOrFail($id);

        $validated = $request->validate([
            'judul_berita'         => 'sometimes|required|string|max:255',
            'tanggal_rilis'        => 'nullable|date',
            'isi_artikel_ringkas'  => 'nullable|string',
            'gambar_pendukung'     => 'nullable',
            'image'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_1'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_1'   => 'nullable',
            'supporting_image_2'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung_2'        => 'nullable',
            'remove_image'              => 'nullable|in:0,1,true,false',
            'remove_supporting_image_1' => 'nullable|in:0,1,true,false',
            'remove_supporting_image_2' => 'nullable|in:0,1,true,false',
            'topik_utama'               => 'nullable|string',
            'admin_id'                  => 'sometimes|required|exists:admins,admin_id',
        ]);

        $mainFile = $request->file('image') ?? $request->file('gambar_pendukung');
        $hasMainPathInput = $request->filled('gambar_pendukung')
            && $this->isValidPathString($request->input('gambar_pendukung'));
        if ($request->boolean('remove_image') && !$mainFile && !$hasMainPathInput) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'image' => ['Gambar utama wajib diisi dan tidak dapat dihapus tanpa gambar pengganti.'],
            ]);
        }

        $data = [];
        foreach (['judul_berita', 'tanggal_rilis', 'isi_artikel_ringkas', 'topik_utama', 'admin_id'] as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        $pathsToDelete = [];

        $mainFile = $request->file('image') ?? $request->file('gambar_pendukung');
        if ($mainFile) {
            $oldPath = $berita->gambar_pendukung;
            $newPath = $this->handleImageUpload($mainFile, 'news');
            if ($newPath) {
                $data['gambar_pendukung'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_pendukung') && $this->isValidPathString($request->input('gambar_pendukung'))) {
            $data['gambar_pendukung'] = $request->input('gambar_pendukung');
        }

        $supp1File = $request->file('supporting_image_1') ?? $request->file('gambar_pendukung_1');
        if ($supp1File) {
            $oldPath = $berita->gambar_pendukung_1;
            $newPath = $this->handleImageUpload($supp1File, 'news/gallery');
            if ($newPath) {
                $data['gambar_pendukung_1'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_pendukung_1') && $this->isValidPathString($request->input('gambar_pendukung_1'))) {
            $data['gambar_pendukung_1'] = $request->input('gambar_pendukung_1');
        } elseif ($request->boolean('remove_supporting_image_1')) {
            if ($berita->gambar_pendukung_1) {
                $pathsToDelete[] = $berita->gambar_pendukung_1;
            }
            $data['gambar_pendukung_1'] = null;
        }

        $supp2File = $request->file('supporting_image_2') ?? $request->file('gambar_pendukung_2');
        if ($supp2File) {
            $oldPath = $berita->gambar_pendukung_2;
            $newPath = $this->handleImageUpload($supp2File, 'news/gallery');
            if ($newPath) {
                $data['gambar_pendukung_2'] = $newPath;
                if ($oldPath && $oldPath !== $newPath) {
                    $pathsToDelete[] = $oldPath;
                }
            }
        } elseif ($request->filled('gambar_pendukung_2') && $this->isValidPathString($request->input('gambar_pendukung_2'))) {
            $data['gambar_pendukung_2'] = $request->input('gambar_pendukung_2');
        } elseif ($request->boolean('remove_supporting_image_2')) {
            if ($berita->gambar_pendukung_2) {
                $pathsToDelete[] = $berita->gambar_pendukung_2;
            }
            $data['gambar_pendukung_2'] = null;
        }

        $berita->update($data);
        $this->deleteStoredImages($pathsToDelete);

        return response()->json([
            'message' => 'Berita/Isu berhasil diperbarui',
            'data' => $berita->fresh()
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
