<?php

namespace App\Http\Controllers;

use App\Models\BeritaIsu;
use Illuminate\Http\Request;

class BeritaIsuController extends Controller
{
    public function index()
    {
        $berita = BeritaIsu::with('admin')->latest()->get();
        return response()->json($berita);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_berita' => 'required|string|max:255',
            'tanggal_rilis' => 'nullable|date',
            'isi_artikel_ringkas' => 'nullable|string',
            'gambar_pendukung' => 'nullable|string',
            'topik_utama' => 'nullable|string',
            'admin_id' => 'required|exists:admins,admin_id',
        ]);

        $berita = BeritaIsu::create($validated);

        return response()->json([
            'message' => 'Berita/Isu berhasil dibuat',
            'data' => $berita
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
            'judul_berita' => 'sometimes|required|string|max:255',
            'tanggal_rilis' => 'nullable|date',
            'isi_artikel_ringkas' => 'nullable|string',
            'gambar_pendukung' => 'nullable|string',
            'topik_utama' => 'nullable|string',
            'admin_id' => 'sometimes|required|exists:admins,admin_id',
        ]);

        $berita->update($validated);

        return response()->json([
            'message' => 'Berita/Isu berhasil diperbarui',
            'data' => $berita
        ]);
    }

    public function destroy($id)
    {
        $berita = BeritaIsu::findOrFail($id);
        $berita->delete();

        return response()->json(['message' => 'Berita/Isu berhasil dihapus']);
    }
}