<?php

namespace App\Http\Controllers;

use App\Models\PublikasiHibah;
use Illuminate\Http\Request;

class PublikasiHibahController extends Controller
{
    public function index()
    {
        $publikasi = PublikasiHibah::with(['admin', 'proyekRiset', 'penulis'])
            ->orderByDesc('tahun')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($publikasi);
    }

    public function store(Request $request)
    {
        // Sanitasi tautan eksternal jika bernilai 'https://' atau string kosong
        if ($request->has('tautan_eksternal')) {
            $link = trim((string) $request->input('tautan_eksternal'));
            if ($link === '' || $link === 'https://' || $link === 'http://') {
                $request->merge(['tautan_eksternal' => null]);
            }
        }

        $validated = $request->validate([
            'jenis'                => 'nullable|string',
            'judul_karya_hibah'    => 'required|string|max:255',
            'nama_jurnal_skema'    => 'nullable|string',
            'tahun'                => 'nullable|integer',
            'deskripsi_pencapaian' => 'nullable|string',
            'tautan_eksternal'     => 'nullable|string|unique:publikasi_hibah,tautan_eksternal',
            'proyek_id'            => 'nullable|exists:proyek_riset,proyek_id',
            'admin_id'             => 'nullable|exists:admins,admin_id',
        ]);

        $validated['admin_id'] = $request->user()?->admin_id ?? $request->input('admin_id') ?? 1;

        $publikasi = PublikasiHibah::create($validated);

        return response()->json([
            'message' => 'Publikasi/Hibah berhasil ditambahkan',
            'data'    => $publikasi->fresh(['admin', 'proyekRiset', 'penulis'])
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

        if ($request->has('tautan_eksternal')) {
            $link = trim((string) $request->input('tautan_eksternal'));
            if ($link === '' || $link === 'https://' || $link === 'http://') {
                $request->merge(['tautan_eksternal' => null]);
            }
        }

        $validated = $request->validate([
            'jenis'                => 'nullable|string',
            'judul_karya_hibah'    => 'sometimes|required|string|max:255',
            'nama_jurnal_skema'    => 'nullable|string',
            'tahun'                => 'nullable|integer',
            'deskripsi_pencapaian' => 'nullable|string',
            'tautan_eksternal'     => 'nullable|string|unique:publikasi_hibah,tautan_eksternal,' . $id . ',publikasi_hibah_id',
            'proyek_id'            => 'nullable|exists:proyek_riset,proyek_id',
            'admin_id'             => 'nullable|exists:admins,admin_id',
        ]);

        if ($request->has('admin_id')) {
            $validated['admin_id'] = $request->input('admin_id') ?? $request->user()?->admin_id ?? $publikasi->admin_id;
        }

        $publikasi->update($validated);

        return response()->json([
            'message' => 'Publikasi/Hibah berhasil diperbarui',
            'data'    => $publikasi->fresh(['admin', 'proyekRiset', 'penulis'])
        ]);
    }

    public function destroy($id)
    {
        $publikasi = PublikasiHibah::findOrFail($id);
        $publikasi->delete();

        return response()->json(['message' => 'Publikasi/Hibah berhasil dihapus']);
    }
}
