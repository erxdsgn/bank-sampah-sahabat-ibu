<?php

namespace App\Http\Controllers;

use App\Models\KategoriSampah;

class LandingController extends Controller
{
    public function index()
    {
        $kategoriInduk = KategoriSampah::whereNull('id_induk')
            ->with([
                'hargaTerbaru',
                'anak' => function ($query) {
                    $query->with('hargaTerbaru')->orderBy('nama_kategori');
                },
            ])
            ->orderBy('nama_kategori')
            ->get();

        $linkGdrive = config('services.gemi.gdrive_url', env('GEMI_GDRIVE_URL', '#'));
        $linkMediafire = config('services.gemi.mediafire_url', env('GEMI_MEDIAFIRE_URL', '#'));

        return view('welcome', compact('kategoriInduk', 'linkGdrive', 'linkMediafire'));
    }
}
