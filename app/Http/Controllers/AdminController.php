<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::all();
        return response()->json($admins);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
            'nama_lengkap' => 'nullable|string|max:255',
            'peran' => 'nullable|string|max:255',
            'status_akses' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $admin = Admin::create($validated);

        return response()->json([
            'message' => 'Admin berhasil ditambahkan',
            'data' => $admin
        ], 201);
    }

    public function show($id)
    {
        $admin = Admin::with(['proyekRiset', 'anggotaAlumni', 'publikasiHibah', 'beritaIsu'])->findOrFail($id);
        return response()->json($admin);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $validated = $request->validate([
            'email' => 'sometimes|required|email|unique:admins,email,' . $id . ',admin_id',
            'password' => 'nullable|string|min:6',
            'nama_lengkap' => 'nullable|string|max:255',
            'peran' => 'nullable|string|max:255',
            'status_akses' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $admin->update($validated);

        return response()->json([
            'message' => 'Data admin berhasil diperbarui',
            'data' => $admin
        ]);
    }

    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Admin berhasil dihapus']);
    }
}