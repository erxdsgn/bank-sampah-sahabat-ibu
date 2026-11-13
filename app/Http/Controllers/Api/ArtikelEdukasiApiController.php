<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArtikelEdukasi;
use Illuminate\Http\Request;

class ArtikelEdukasiApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ArtikelEdukasi::whereNotNull('tanggal_publish')
                // 1. Prioritaskan 'acara' (Event) selalu di paling atas
                ->orderByRaw("CASE WHEN jenis = 'acara' THEN 0 ELSE 1 END ASC")
                // 2. Urutkan berdasarkan tanggal publish terbaru
                ->orderBy('tanggal_publish', 'desc')
                // 3. Jika tanggal publish sama (diupload di hari yang sama), urutkan dari ID terbaru
                ->orderBy('id_artikel', 'desc');

            if ($request->has('jenis') && in_array($request->jenis, ['edukasi', 'artikel', 'acara'])) {
                $query->where('jenis', $request->jenis);
            }

            $artikel = $query->get()->map(function ($item) {
                // Sekaligus menangani gambar hasil import URL (http/https) agar tidak rusak saat di-asset()
                $gambarUrl = null;
                if ($item->gambar) {
                    $gambarUrl = str_starts_with($item->gambar, 'http')
                        ? $item->gambar
                        : asset('uploads/artikel/' . $item->gambar);
                }

                return [
                    'id_artikel'      => $item->id_artikel,
                    'judul'           => $item->judul,
                    'jenis'           => $item->jenis,
                    'konten'          => $item->konten,
                    'gambar'          => $gambarUrl,
                    'tanggal_publish' => $item->tanggal_publish ? optional($item->tanggal_publish)->format('Y-m-d') : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memuat data artikel, edukasi, dan acara.',
                'data'    => $artikel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Server: ' . $e->getMessage(),
            ], 500);
        }
    }
}
