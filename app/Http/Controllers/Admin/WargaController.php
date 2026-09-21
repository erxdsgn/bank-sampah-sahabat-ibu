<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // 1. Ditambahkan import Controller
use App\Models\Warga;
use Illuminate\Http\Request;

class WargaController extends Controller
{
    /**
     * Menampilkan daftar warga
     */
    public function index()
    {
        $warga = Warga::orderBy('id_warga', 'desc')->get();

        // 2. Disesuaikan dengan struktur views/admin/pages/warga.blade.php
        return view('admin.pages.warga', compact('warga'));
    }

    /**
     * Menampilkan form tambah warga
     */
    public function create()
    {
        $warga = Warga::orderBy('id_warga', 'desc')->get();

        return view('admin.pages.form-warga', compact('warga'));
    }

    /**
     * Menyimpan data warga baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|max:16|unique:warga,nik',
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:14',
            'jumlah_anggota_keluarga' => 'nullable|integer|min:1',
            'alamat' => 'required|string',
        ]);

        $warga = Warga::create([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'no_hp' => $validated['no_hp'] ?? null,
            'jumlah_anggota_keluarga' => $validated['jumlah_anggota_keluarga'] ?? 0,
            'alamat' => $validated['alamat'] ?? null,
            'saldo' => 0,
            'tanggal_daftar' => now(),
        ]);

        return response()->json([
            'message' => 'Data warga berhasil ditambahkan.',
            'data' => $warga,
        ]);
    }

    /**
     * Menampilkan detail warga
     */
    public function show($id)
    {
        $warga = Warga::findOrFail($id);

        return view('admin.pages.warga', compact('warga'));
    }

    /**
     * Menampilkan form edit warga
     */
    public function edit($id)
    {
        $warga = Warga::findOrFail($id);

        return view('admin.pages.form-warga', compact('warga'));
    }

    /**
     * Memperbarui data warga
     */
    public function update(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);

        $validated = $request->validate([
            'nik' => 'required|unique:warga,nik,' . $id . ',id_warga',
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'jumlah_anggota_keluarga' => 'nullable|integer|min:0',
            'saldo' => 'nullable|numeric|min:0',
            'tanggal_daftar' => 'nullable|date',
        ]);

        $warga->update([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'no_hp' => $validated['no_hp'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'jumlah_anggota_keluarga' => $validated['jumlah_anggota_keluarga'] ?? 0,
            'saldo' => $validated['saldo'] ?? $warga->saldo,
            'tanggal_daftar' => $validated['tanggal_daftar'] ?? $warga->tanggal_daftar,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $warga]);
        }

        return redirect()
            ->route('admin.warga')
            ->with('success', 'Data warga berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);

        $warga->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.warga')
            ->with('success', 'Data warga berhasil dihapus.');
    }
}
