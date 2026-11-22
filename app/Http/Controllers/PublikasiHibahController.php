<?php

namespace App\Http\Controllers;

use App\Models\PublikasiHibah;
use Illuminate\Http\Request;

class PublikasiHibahController extends Controller
{
    public function index()
    {
        $publikasi = PublikasiHibah::with(['admin', 'proyekRiset'])
            ->orderByDesc('tahun')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($publikasi);
    }

    public function store(Request $request)
    {
        $adminId = $request->user()?->admin_id;
        if (!$adminId) {
            return response()->json([
                'message' => 'Akses ditolak. Identitas admin tidak valid.'
            ], 403);
        }

        $this->sanitizeExternalLinkInput($request);
        if (!$request->has('penulis') && $request->has('author')) {
            $request->merge(['penulis' => $request->input('author')]);
        }

        $validated = $request->validate([
            'jenis'                => 'nullable|string',
            'judul_karya_hibah'    => 'required|string|max:255',
            'penulis'              => 'nullable|string|max:255',
            'nama_jurnal_skema'    => 'nullable|string',
            'tahun'                => 'nullable|integer',
            'deskripsi_pencapaian' => 'nullable|string',
            'tautan_eksternal'     => 'nullable|url:http,https|max:255|unique:publikasi_hibah,tautan_eksternal',
            'proyek_id'            => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        $penulis = isset($validated['penulis']) ? trim((string) $validated['penulis']) : '';
        $validated['penulis'] = $penulis !== '' ? $penulis : null;
        $validated['admin_id'] = $adminId;

        $publikasi = PublikasiHibah::create($validated);

        return response()->json([
            'message' => 'Publikasi/Hibah berhasil ditambahkan',
            'data'    => $publikasi->fresh(['admin', 'proyekRiset'])
        ], 201);
    }

    public function show($id)
    {
        $publikasi = PublikasiHibah::with(['admin', 'proyekRiset'])->findOrFail($id);
        return response()->json($publikasi);
    }

    public function update(Request $request, $id)
    {
        $adminId = $request->user()?->admin_id;
        if (!$adminId) {
            return response()->json([
                'message' => 'Akses ditolak. Identitas admin tidak valid.'
            ], 403);
        }

        $publikasi = PublikasiHibah::findOrFail($id);

        $this->sanitizeExternalLinkInput($request);
        if (!$request->has('penulis') && $request->has('author')) {
            $request->merge(['penulis' => $request->input('author')]);
        }

        $validated = $request->validate([
            'jenis'                => 'nullable|string',
            'judul_karya_hibah'    => 'sometimes|required|string|max:255',
            'penulis'              => 'nullable|string|max:255',
            'nama_jurnal_skema'    => 'nullable|string',
            'tahun'                => 'nullable|integer',
            'deskripsi_pencapaian' => 'nullable|string',
            'tautan_eksternal'     => 'nullable|url:http,https|max:255|unique:publikasi_hibah,tautan_eksternal,' . $id . ',publikasi_hibah_id',
            'proyek_id'            => 'nullable|exists:proyek_riset,proyek_id',
        ]);

        if (array_key_exists('penulis', $validated)) {
            $trimmedPenulis = is_string($validated['penulis']) ? trim($validated['penulis']) : '';
            $validated['penulis'] = $trimmedPenulis !== '' ? $trimmedPenulis : null;
        }
        unset($validated['admin_id']);

        $publikasi->update($validated);

        return response()->json([
            'message' => 'Publikasi/Hibah berhasil diperbarui',
            'data'    => $publikasi->fresh(['admin', 'proyekRiset'])
        ]);
    }

    public function destroy($id)
    {
        $publikasi = PublikasiHibah::findOrFail($id);
        $publikasi->delete();

        return response()->json(['message' => 'Publikasi/Hibah berhasil dihapus']);
    }

    protected function sanitizeExternalLinkInput(Request $request): void
    {
        if (!$request->has('tautan_eksternal')) {
            return;
        }

        $link = trim((string) $request->input('tautan_eksternal'));
        if ($link === '' || $link === 'https://' || $link === 'http://') {
            $request->merge(['tautan_eksternal' => null]);
            return;
        }

        if (preg_match('/^10\.\d{4,9}\/[-._;()\/:A-Za-z0-9]+$/', $link)) {
            $request->merge(['tautan_eksternal' => 'https://doi.org/' . $link]);
        }
    }
}
