<?php

namespace App\Http\Controllers;

use App\Models\ProyekRiset;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProyekRisetController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $proyek = ProyekRiset::with('admin')
            ->orderByDesc('created_at')
            ->orderByDesc('proyek_id')
            ->get();

        return response()->json([
            'message' => 'Daftar proyek riset berhasil diambil',
            'data'    => $proyek
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
            'admin_id'                 => 'nullable|exists:admins,admin_id',
            'image'                    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'dokumentasi_visual_utama' => 'nullable',
            'supporting_image_1'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_2'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Gambar utama wajib diunggah saat pembuatan baru
        $mainImageFile = $request->file('image') ?? $request->file('dokumentasi_visual_utama');
        if (!$mainImageFile && empty($validated['dokumentasi_visual_utama'])) {
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
            $validated['dokumentasi_visual_utama'] = $this->uploadCmsFile($mainImageFile, 'proyek-riset');
        }

        if ($request->hasFile('supporting_image_1')) {
            $validated['gambar_pendukung_1'] = $this->uploadCmsFile($request->file('supporting_image_1'), 'proyek-riset');
        }

        if ($request->hasFile('supporting_image_2')) {
            $validated['gambar_pendukung_2'] = $this->uploadCmsFile($request->file('supporting_image_2'), 'proyek-riset');
        }

        // Bersihkan key file temporary
        unset($validated['image'], $validated['supporting_image_1'], $validated['supporting_image_2']);

        $proyek = ProyekRiset::create($validated);

        return response()->json([
            'message' => 'Proyek riset berhasil ditambahkan',
            'data'    => $proyek->fresh('admin')
        ], 201);
    }

    public function show(string $id)
    {
        $proyek = ProyekRiset::with(['admin', 'anggotaAlumni', 'publikasiHibah', 'prestasiPencapaian'])->find($id);

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
            'admin_id'                 => 'nullable|exists:admins,admin_id',
            'image'                    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'dokumentasi_visual_utama' => 'nullable',
            'supporting_image_1'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_2'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Validasi pasangan gambar pendukung secara atomik
        $this->validateSupportingImagePair(
            $request,
            $proyek,
            'supporting_image_1',
            'supporting_image_2',
            'gambar_pendukung_1',
            'gambar_pendukung_2'
        );

        if ($request->has('admin_id')) {
            $validated['admin_id'] = $request->input('admin_id') ?? $request->user()?->admin_id ?? $proyek->admin_id;
        }

        // Handle update gambar utama
        $mainImageFile = $request->file('image') ?? $request->file('dokumentasi_visual_utama');
        if ($mainImageFile) {
            $oldImage = $proyek->dokumentasi_visual_utama;
            $validated['dokumentasi_visual_utama'] = $this->uploadCmsFile($mainImageFile, 'proyek-riset');
            $this->deleteCmsFile($oldImage, $proyek);
        }

        // Handle penghapusan gambar pendukung jika ada flag remove
        $remove1 = filter_var($request->input('remove_supporting_image_1', false), FILTER_VALIDATE_BOOLEAN);
        $remove2 = filter_var($request->input('remove_supporting_image_2', false), FILTER_VALIDATE_BOOLEAN);

        if ($remove1 || $remove2) {
            $this->deleteCmsFile($proyek->gambar_pendukung_1, $proyek);
            $this->deleteCmsFile($proyek->gambar_pendukung_2, $proyek);
            $validated['gambar_pendukung_1'] = null;
            $validated['gambar_pendukung_2'] = null;
        }

        // Handle file baru gambar pendukung
        if ($request->hasFile('supporting_image_1')) {
            $old1 = $proyek->gambar_pendukung_1;
            $validated['gambar_pendukung_1'] = $this->uploadCmsFile($request->file('supporting_image_1'), 'proyek-riset');
            $this->deleteCmsFile($old1, $proyek);
        }

        if ($request->hasFile('supporting_image_2')) {
            $old2 = $proyek->gambar_pendukung_2;
            $validated['gambar_pendukung_2'] = $this->uploadCmsFile($request->file('supporting_image_2'), 'proyek-riset');
            $this->deleteCmsFile($old2, $proyek);
        }

        unset($validated['image'], $validated['supporting_image_1'], $validated['supporting_image_2']);

        $proyek->update($validated);

        return response()->json([
            'message' => 'Proyek riset berhasil diperbarui',
            'data'    => $proyek->fresh('admin')
        ], 200);
    }

    public function destroy(string $id)
    {
        $proyek = ProyekRiset::find($id);

        if (!$proyek) {
            return response()->json(['message' => 'Proyek riset tidak ditemukan'], 404);
        }

        $this->deleteCmsFile($proyek->dokumentasi_visual_utama, $proyek);
        $this->deleteCmsFile($proyek->gambar_pendukung_1, $proyek);
        $this->deleteCmsFile($proyek->gambar_pendukung_2, $proyek);

        $proyek->delete();

        return response()->json([
            'message' => 'Proyek riset berhasil dihapus'
        ], 200);
    }
}
