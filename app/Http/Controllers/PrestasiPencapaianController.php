<?php

namespace App\Http\Controllers;

use App\Models\PrestasiPencapaian;
use Illuminate\Http\Request;

class PrestasiPencapaianController extends Controller
{
    public function index()
    {
        $prestasi = PrestasiPencapaian::with(['anggotaAlumni', 'proyekRiset'])->latest()->get();
        return response()->json($prestasi);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_prestasi' => 'required|string|max:255',
            'penyelenggara' => 'nullable|string',
            'tingkat' => 'nullable|string',
            'tahun_diperoleh' => 'nullable|integer',
            'deskripsi' => 'nullable|string',
            'dokumentasi_bukti' => 'nullable|string',
            'anggota_alumni_id' => 'required|exists:anggota_alumni,anggota_alumni_id',
            'proyek_id' => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        $prestasi = PrestasiPencapaian::create($validated);

        return response()->json([
            'message' => 'Prestasi/Pencapaian berhasil ditambahkan',
            'data' => $prestasi
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
            'nama_prestasi' => 'sometimes|required|string|max:255',
            'penyelenggara' => 'nullable|string',
            'tingkat' => 'nullable|string',
            'tahun_diperoleh' => 'nullable|integer',
            'deskripsi' => 'nullable|string',
            'dokumentasi_bukti' => 'nullable|string',
            'anggota_alumni_id' => 'sometimes|required|exists:anggota_alumni,anggota_alumni_id',
            'proyek_id' => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        $prestasi->update($validated);

        return response()->json([
            'message' => 'Prestasi/Pencapaian berhasil diperbarui',
            'data' => $prestasi
        ]);
    }

    public function destroy($id)
    {
        $prestasi = PrestasiPencapaian::findOrFail($id);
        $prestasi->delete();

        return response()->json(['message' => 'Prestasi/Pencapaian berhasil dihapus']);
    }
}