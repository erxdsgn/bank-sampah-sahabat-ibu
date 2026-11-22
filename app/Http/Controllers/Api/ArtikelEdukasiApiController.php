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
                // 1. Prioritaskan Event / Acara di urutan paling atas
                ->orderByRaw("CASE WHEN jenis IN ('acara', 'event') THEN 0 ELSE 1 END ASC")
                // 2. Urutkan berdasarkan tanggal publish terbaru
                ->orderBy('tanggal_publish', 'desc')
                // 3. Jika tanggal sama, urutkan dari ID terbaru
                ->orderBy('id_artikel', 'desc');

            if ($request->has('jenis') && in_array($request->jenis, ['edukasi', 'artikel', 'acara', 'event'])) {
                if (in_array($request->jenis, ['acara', 'event'])) {
                    $query->whereIn('jenis', ['acara', 'event']);
                } else {
                    $query->where('jenis', $request->jenis);
                }
            }

            $artikel = $query->get()->map(function ($item) {
                $tipeTampilan = 'teks';
                $bannerTema = null;
                $gambarUrl = null;

                if ($item->gambar) {
                    if (str_starts_with($item->gambar, 'banner:')) {
                        $tipeTampilan = 'banner';
                        $bannerTema = str_replace('banner:', '', $item->gambar);
                    } else {
                        $tipeTampilan = 'gambar';
                        $gambarUrl = str_starts_with($item->gambar, 'http')
                            ? $item->gambar
                            : asset('uploads/artikel/' . $item->gambar);
                    }
                }

                $cleanKonten = trim(strip_tags((string) $item->konten));
                $isLink = (bool) preg_match('/^https?:\/\/[^\s]+$/i', $cleanKonten);

                return [
                    'id_artikel'      => $item->id_artikel,
                    'judul'           => $item->judul,
                    'jenis'           => $item->jenis,
                    'konten'          => $item->konten,
                    'is_link'         => $isLink,
                    'link_url'        => $isLink ? $cleanKonten : null,
                    'tipe_tampilan'   => $tipeTampilan, // 'gambar' | 'banner' | 'teks'
                    'banner_tema'     => $bannerTema,   // 'edukasi' | 'event' | 'artikel' | null
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
