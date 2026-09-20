<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriSampah;
use Illuminate\Http\Request;

class KategoriSampahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategoriInduk = KategoriSampah::whereNull('id_induk')
            ->with(['children.hargaAktif', 'hargaAktif'])
            ->get();

        return view('admin.pages.kategori-sampah.index', compact('kategoriInduk'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $indukList = KategoriSampah::whereNull('id_induk')->get();
        return view('admin.pages.kategori-sampah.create', compact('indukList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_induk' => 'nullable|exist:kategori_sampah,id_kategori',
            'nama_kategori' => 'required|string|max:100',
            'satuan' => 'required|string|max:20',
        ]);

        KategoriSampah::create($validated);
        return redirect()->route('admin.kategori-sampah.index')->with('success', 'Kategori sampah berhasil ditambahkan.');
    }


    /**
     * Display the specified resource.
     */
    public function show(KategoriSampah $kategoriSampah)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KategoriSampah $kategoriSampah)
    {
        $kategoriInduk = KategoriSampah::whereNull('id_induk')->where('id_kategori', '!=', $kategoriSampah->id_kategori)->orderBy('nama_kategori')->get();
        return view('admin.pages.kategori-sampah.edit', compact('kategoriSampah', 'kategoriInduk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KategoriSampah $kategoriSampah)
    {
        $validated = $request->validate([
            'id_induk' => 'nullable|exist:kategori_sampah, id_kategori',
            'nama_kategori' => 'required|string|max:100',
            'satuan' => 'required|string|max:20',
        ]);

        $kategoriSampah->update($validated);
        return redirect()->route('admin.kategori-sampah.index')->with('success', 'Kategori sampah berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KategoriSampah $kategoriSampah)
    {
        $kategoriSampah->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori sampah berhasil dihapus.'
            ]);
        }
        return redirect()->route('admin.kategori-sampah.index')->with('success', 'Kategori sampah berhasil dihapus.');


    }
}
