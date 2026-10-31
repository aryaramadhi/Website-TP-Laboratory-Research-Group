<?php

namespace App\Http\Controllers;

use App\Models\PublikasiHibah;
use Illuminate\Http\Request;

class PublikasiHibahController extends Controller
{
    public function index()
    {
        $publikasi = PublikasiHibah::with(['admin', 'proyekRiset', 'penulis'])->latest()->get();
        return response()->json($publikasi);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => 'nullable|string',
            'judul_karya_hibah' => 'required|string|max:255',
            'nama_jurnal_skema' => 'nullable|string',
            'tahun' => 'nullable|integer',
            'deskripsi_pencapaian' => 'nullable|string',
            'tautan_eksternal' => 'nullable|string|unique:publikasi_hibah,tautan_eksternal',
            'proyek_id' => 'nullable|exists:proyek_riset,proyek_id',
            'admin_id' => 'required|exists:admins,admin_id',
        ]);

        $publikasi = PublikasiHibah::create($validated);

        return response()->json([
            'message' => 'Publikasi/Hibah berhasil ditambahkan',
            'data' => $publikasi
        ], 201);
    }

    public function show($id)
    {
        $publikasi = PublikasiHibah::with(['admin', 'proyekRiset', 'penulis'])->findOrFail($id);
        return response()->json($publikasi);
    }

    public function update(Request $request, $id)
    {
        $publikasi = PublikasiHibah::findOrFail($id);

        $validated = $request->validate([
            'jenis' => 'nullable|string',
            'judul_karya_hibah' => 'sometimes|required|string|max:255',
            'nama_jurnal_skema' => 'nullable|string',
            'tahun' => 'nullable|integer',
            'deskripsi_pencapaian' => 'nullable|string',
            'tautan_eksternal' => 'nullable|string|unique:publikasi_hibah,tautan_eksternal,' . $id . ',publikasi_hibah_id',
            'proyek_id' => 'nullable|exists:proyek_riset,proyek_id',
            'admin_id' => 'sometimes|required|exists:admins,admin_id',
        ]);

        $publikasi->update($validated);

        return response()->json([
            'message' => 'Publikasi/Hibah berhasil diperbarui',
            'data' => $publikasi
        ]);
    }

    public function destroy($id)
    {
        $publikasi = PublikasiHibah::findOrFail($id);
        $publikasi->delete();

        return response()->json(['message' => 'Publikasi/Hibah berhasil dihapus']);
    }
}