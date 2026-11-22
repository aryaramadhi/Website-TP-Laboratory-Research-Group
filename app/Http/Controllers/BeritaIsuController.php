<?php

namespace App\Http\Controllers;

use App\Models\BeritaIsu;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $validated = $request->validate([
            'judul_berita'        => 'required|string|max:255',
            'tanggal_rilis'       => 'nullable|date',
            'isi_artikel_ringkas' => 'nullable|string',
            'topik_utama'         => 'nullable|string|max:100',
            'admin_id'            => 'nullable|exists:admins,admin_id',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung'    => 'nullable',
            'supporting_image_1'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_2'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Gambar utama (disimpan pada kolom gambar_pendukung) wajib diunggah saat store
        $mainImageFile = $request->file('image') ?? $request->file('gambar_pendukung');
        if (!$mainImageFile && empty($validated['gambar_pendukung'])) {
            throw ValidationException::withMessages([
                'image' => ['Gambar utama wajib diunggah.']
            ]);
        }

        // Validasi pasangan gambar pendukung secara atomik
        $this->validateSupportingImagePair(
            $request,
            null,
            'supporting_image_1',
            'supporting_image_2',
            'gambar_pendukung_1',
            'gambar_pendukung_2'
        );

        $validated['admin_id'] = $request->user()?->admin_id ?? $request->input('admin_id') ?? 1;

        if ($mainImageFile) {
            $validated['gambar_pendukung'] = $this->uploadCmsFile($mainImageFile, 'berita-isu');
        }

        if ($request->hasFile('supporting_image_1')) {
            $validated['gambar_pendukung_1'] = $this->uploadCmsFile($request->file('supporting_image_1'), 'berita-isu');
        }

        if ($request->hasFile('supporting_image_2')) {
            $validated['gambar_pendukung_2'] = $this->uploadCmsFile($request->file('supporting_image_2'), 'berita-isu');
        }

        unset($validated['image'], $validated['supporting_image_1'], $validated['supporting_image_2']);

        $berita = BeritaIsu::create($validated);

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
        $berita = BeritaIsu::findOrFail($id);

        $validated = $request->validate([
            'judul_berita'        => 'sometimes|required|string|max:255',
            'tanggal_rilis'       => 'nullable|date',
            'isi_artikel_ringkas' => 'nullable|string',
            'topik_utama'         => 'nullable|string|max:100',
            'admin_id'            => 'nullable|exists:admins,admin_id',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_pendukung'    => 'nullable',
            'supporting_image_1'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_2'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $this->validateSupportingImagePair(
            $request,
            $berita,
            'supporting_image_1',
            'supporting_image_2',
            'gambar_pendukung_1',
            'gambar_pendukung_2'
        );

        if ($request->has('admin_id')) {
            $validated['admin_id'] = $request->input('admin_id') ?? $request->user()?->admin_id ?? $berita->admin_id;
        }

        // Handle update gambar utama
        $mainImageFile = $request->file('image') ?? $request->file('gambar_pendukung');
        if ($mainImageFile) {
            $oldImage = $berita->gambar_pendukung;
            $validated['gambar_pendukung'] = $this->uploadCmsFile($mainImageFile, 'berita-isu');
            $this->deleteCmsFile($oldImage, $berita);
        }

        // Handle penghapusan gambar pendukung jika ada flag remove
        $remove1 = filter_var($request->input('remove_supporting_image_1', false), FILTER_VALIDATE_BOOLEAN);
        $remove2 = filter_var($request->input('remove_supporting_image_2', false), FILTER_VALIDATE_BOOLEAN);

        if ($remove1 || $remove2) {
            $this->deleteCmsFile($berita->gambar_pendukung_1, $berita);
            $this->deleteCmsFile($berita->gambar_pendukung_2, $berita);
            $validated['gambar_pendukung_1'] = null;
            $validated['gambar_pendukung_2'] = null;
        }

        // Handle file baru gambar pendukung
        if ($request->hasFile('supporting_image_1')) {
            $old1 = $berita->gambar_pendukung_1;
            $validated['gambar_pendukung_1'] = $this->uploadCmsFile($request->file('supporting_image_1'), 'berita-isu');
            $this->deleteCmsFile($old1, $berita);
        }

        if ($request->hasFile('supporting_image_2')) {
            $old2 = $berita->gambar_pendukung_2;
            $validated['gambar_pendukung_2'] = $this->uploadCmsFile($request->file('supporting_image_2'), 'berita-isu');
            $this->deleteCmsFile($old2, $berita);
        }

        unset($validated['image'], $validated['supporting_image_1'], $validated['supporting_image_2']);

        $berita->update($validated);

        return response()->json([
            'message' => 'Berita/Isu berhasil diperbarui',
            'data'    => $berita->fresh('admin')
        ]);
    }

    public function destroy($id)
    {
        $berita = BeritaIsu::findOrFail($id);

        $this->deleteCmsFile($berita->gambar_pendukung, $berita);
        $this->deleteCmsFile($berita->gambar_pendukung_1, $berita);
        $this->deleteCmsFile($berita->gambar_pendukung_2, $berita);

        $berita->delete();

        return response()->json(['message' => 'Berita/Isu berhasil dihapus']);
    }
}
