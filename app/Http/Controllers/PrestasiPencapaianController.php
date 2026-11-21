<?php

namespace App\Http\Controllers;

use App\Models\PrestasiPencapaian;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $validated = $request->validate([
            'nama_prestasi'      => 'required|string|max:255',
            'penulis'            => 'nullable|string|max:255',
            'penyelenggara'      => 'nullable|string|max:255',
            'tingkat'            => 'nullable|string|max:100',
            'tahun_diperoleh'    => 'nullable|integer',
            'deskripsi'          => 'nullable|string',
            'dokumentasi_bukti'  => 'nullable|string',
            'proyek_id'          => 'nullable|exists:proyek_riset,proyek_id',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_utama'       => 'nullable',
            'supporting_image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'document'           => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
        ]);

        // Gambar utama wajib saat pembuatan
        $mainImageFile = $request->file('image') ?? $request->file('gambar_utama');
        if (!$mainImageFile && empty($validated['gambar_utama'])) {
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

        if ($mainImageFile) {
            $validated['gambar_utama'] = $this->uploadCmsFile($mainImageFile, 'prestasi-pencapaian');
        }

        if ($request->hasFile('supporting_image_1')) {
            $validated['gambar_pendukung_1'] = $this->uploadCmsFile($request->file('supporting_image_1'), 'prestasi-pencapaian');
        }

        if ($request->hasFile('supporting_image_2')) {
            $validated['gambar_pendukung_2'] = $this->uploadCmsFile($request->file('supporting_image_2'), 'prestasi-pencapaian');
        }

        if ($request->hasFile('document')) {
            $validated['dokumentasi_bukti'] = $this->uploadCmsFile($request->file('document'), 'prestasi-pencapaian/documents');
        }

        unset($validated['image'], $validated['supporting_image_1'], $validated['supporting_image_2'], $validated['document']);

        $prestasi = PrestasiPencapaian::create($validated);

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
        $prestasi = PrestasiPencapaian::findOrFail($id);

        $validated = $request->validate([
            'nama_prestasi'      => 'sometimes|required|string|max:255',
            'penulis'            => 'nullable|string|max:255',
            'penyelenggara'      => 'nullable|string|max:255',
            'tingkat'            => 'nullable|string|max:100',
            'tahun_diperoleh'    => 'nullable|integer',
            'deskripsi'          => 'nullable|string',
            'dokumentasi_bukti'  => 'nullable|string',
            'proyek_id'          => 'nullable|exists:proyek_riset,proyek_id',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gambar_utama'       => 'nullable',
            'supporting_image_1' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'supporting_image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'document'           => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
        ]);

        $this->validateSupportingImagePair(
            $request,
            $prestasi,
            'supporting_image_1',
            'supporting_image_2',
            'gambar_pendukung_1',
            'gambar_pendukung_2'
        );

        // Handle update gambar utama
        $mainImageFile = $request->file('image') ?? $request->file('gambar_utama');
        if ($mainImageFile) {
            $oldImage = $prestasi->gambar_utama;
            $validated['gambar_utama'] = $this->uploadCmsFile($mainImageFile, 'prestasi-pencapaian');
            $this->deleteCmsFile($oldImage, $prestasi);
        }

        // Handle penghapusan gambar pendukung jika ada flag remove
        $remove1 = filter_var($request->input('remove_supporting_image_1', false), FILTER_VALIDATE_BOOLEAN);
        $remove2 = filter_var($request->input('remove_supporting_image_2', false), FILTER_VALIDATE_BOOLEAN);

        if ($remove1 || $remove2) {
            $this->deleteCmsFile($prestasi->gambar_pendukung_1, $prestasi);
            $this->deleteCmsFile($prestasi->gambar_pendukung_2, $prestasi);
            $validated['gambar_pendukung_1'] = null;
            $validated['gambar_pendukung_2'] = null;
        }

        // Handle file baru gambar pendukung
        if ($request->hasFile('supporting_image_1')) {
            $old1 = $prestasi->gambar_pendukung_1;
            $validated['gambar_pendukung_1'] = $this->uploadCmsFile($request->file('supporting_image_1'), 'prestasi-pencapaian');
            $this->deleteCmsFile($old1, $prestasi);
        }

        if ($request->hasFile('supporting_image_2')) {
            $old2 = $prestasi->gambar_pendukung_2;
            $validated['gambar_pendukung_2'] = $this->uploadCmsFile($request->file('supporting_image_2'), 'prestasi-pencapaian');
            $this->deleteCmsFile($old2, $prestasi);
        }

        // Handle update dokumen bukti
        if ($request->hasFile('document')) {
            $oldDoc = $prestasi->dokumentasi_bukti;
            $validated['dokumentasi_bukti'] = $this->uploadCmsFile($request->file('document'), 'prestasi-pencapaian/documents');
            $this->deleteCmsFile($oldDoc, $prestasi);
        }

        unset($validated['image'], $validated['supporting_image_1'], $validated['supporting_image_2'], $validated['document']);

        $prestasi->update($validated);

        return response()->json([
            'message' => 'Prestasi/Pencapaian berhasil diperbarui',
            'data'    => $prestasi->fresh('proyekRiset')
        ]);
    }

    public function destroy($id)
    {
        $prestasi = PrestasiPencapaian::findOrFail($id);

        $this->deleteCmsFile($prestasi->gambar_utama, $prestasi);
        $this->deleteCmsFile($prestasi->gambar_pendukung_1, $prestasi);
        $this->deleteCmsFile($prestasi->gambar_pendukung_2, $prestasi);
        $this->deleteCmsFile($prestasi->dokumentasi_bukti, $prestasi);

        $prestasi->delete();

        return response()->json(['message' => 'Prestasi/Pencapaian berhasil dihapus']);
    }
}
