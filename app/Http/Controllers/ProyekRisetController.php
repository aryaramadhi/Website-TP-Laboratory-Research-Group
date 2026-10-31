<?php

namespace App\Http\Controllers;

use App\Models\ProyekRiset;
use Illuminate\Http\Request;

class ProyekRisetController extends Controller
{
    public function index()
    {
        $proyek = ProyekRiset::all();
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
        ]);

        $proyek = ProyekRiset::create($validated);

        return response()->json([
            'message' => 'Proyek riset berhasil ditambahkan',
            'data'    => $proyek
        ], 201);
    }

    public function show(string $id)
    {
        $proyek = ProyekRiset::find($id);

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
        ]);

        $proyek->update($validated);

        return response()->json([
            'message' => 'Proyek riset berhasil diperbarui',
            'data'    => $proyek
        ], 200);
    }

    public function destroy(string $id)
    {
        $proyek = ProyekRiset::find($id);

        if (!$proyek) {
            return response()->json(['message' => 'Proyek riset tidak ditemukan'], 404);
        }

        $proyek->delete();

        return response()->json([
            'message' => 'Proyek riset berhasil dihapus'
        ], 200);
    }
}