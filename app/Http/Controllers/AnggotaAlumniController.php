<?php

namespace App\Http\Controllers;

use App\Models\AnggotaAlumni;
use Illuminate\Http\Request;

class AnggotaAlumniController extends Controller
{
    public function index()
    {
        $anggota = AnggotaAlumni::with(['admin', 'proyekRiset'])->latest()->get();
        return response()->json($anggota);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'kepakaran' => 'nullable|string',
            'peran_lab' => 'nullable|string',
            'tahun_masuk' => 'nullable|integer',
            'tahun_lulus_keluar' => 'nullable|integer',
            'kontak_profesional' => 'nullable|string',
            'tautan_repositori_pribadi' => 'nullable|string',
            'admin_id' => 'required|exists:admins,admin_id',
        ]);

        $anggota = AnggotaAlumni::create($validated);

        return response()->json([
            'message' => 'Anggota/Alumni berhasil ditambahkan',
            'data' => $anggota
        ], 201);
    }

    public function show($id)
    {
        $anggota = AnggotaAlumni::with(['admin', 'proyekRiset', 'publikasiHibah', 'prestasiPencapaian'])->findOrFail($id);
        return response()->json($anggota);
    }

    public function update(Request $request, $id)
    {
        $anggota = AnggotaAlumni::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'kepakaran' => 'nullable|string',
            'peran_lab' => 'nullable|string',
            'tahun_masuk' => 'nullable|integer',
            'tahun_lulus_keluar' => 'nullable|integer',
            'kontak_profesional' => 'nullable|string',
            'tautan_repositori_pribadi' => 'nullable|string',
            'admin_id' => 'sometimes|required|exists:admins,admin_id',
        ]);

        $anggota->update($validated);

        return response()->json([
            'message' => 'Data Anggota/Alumni berhasil diperbarui',
            'data' => $anggota
        ]);
    }

    public function destroy($id)
    {
        $anggota = AnggotaAlumni::findOrFail($id);
        $anggota->delete();

        return response()->json(['message' => 'Anggota/Alumni berhasil dihapus']);
    }
}