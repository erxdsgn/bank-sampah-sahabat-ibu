<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtikelEdukasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ArtikelEdukasiController extends Controller
{
    public function index()
    {
        $artikel = ArtikelEdukasi::orderBy('id_artikel', 'desc')->get();

        return view('admin.pages.artikel', compact('artikel'));
    }

    private function normalisasiTanggalPublish(Request $request): void
    {
        $request->merge([
            'tanggal_publish' => $request->filled('tanggal_publish')
                ? $request->input('tanggal_publish')
                : null,
        ]);
    }

    private function hapusFileGambarLama(?string $gambar): void
    {
        if ($gambar && !str_starts_with($gambar, 'http') && !str_starts_with($gambar, 'banner:')) {
            $path = public_path('uploads/artikel/' . $gambar);
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function store(Request $request)
    {
        $this->normalisasiTanggalPublish($request);

        $validated = $request->validate([
            'judul'           => 'required|string|max:80',
            'jenis'           => 'required|in:edukasi,artikel,event,acara',
            'mode_media'      => 'required|in:gambar,banner_edukasi,banner_event,banner_artikel,tanpa_gambar',
            'sumber_konten'   => 'nullable|in:manual,link',
            'link_url'        => 'required_if:sumber_konten,link|nullable|url',
            'konten'          => 'required_unless:sumber_konten,link|nullable|string',
            'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gambar_url'      => 'nullable|url',
            'tanggal_publish' => 'nullable|date',
        ]);

        if (($validated['sumber_konten'] ?? 'manual') === 'link') {
            $validated['konten'] = trim($validated['link_url']);
        }

        $modeMedia = $validated['mode_media'];

        if ($modeMedia === 'gambar') {
            if ($request->hasFile('gambar')) {
                $gambar = $request->file('gambar');
                $namaGambar = time() . '_' . $gambar->getClientOriginalName();
                $gambar->move(public_path('uploads/artikel'), $namaGambar);
                $validated['gambar'] = $namaGambar;
            } elseif ($request->filled('gambar_url')) {
                $validated['gambar'] = $request->input('gambar_url');
            } else {
                $validated['gambar'] = null;
            }
        } elseif (str_starts_with($modeMedia, 'banner_')) {
            $tema = str_replace('banner_', '', $modeMedia);
            $validated['gambar'] = 'banner:' . $tema;
        } else {
            $validated['gambar'] = null;
        }

        unset($validated['gambar_url'], $validated['mode_media'], $validated['sumber_konten'], $validated['link_url']);
        $validated['tanggal_publish'] = $validated['tanggal_publish'] ?? null;
        $validated['id_admin'] = Auth::id() ?? 1;

        $artikel = ArtikelEdukasi::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Konten berhasil ditambahkan.',
            'data'    => $artikel,
        ]);
    }

    public function update(Request $request, $id)
    {
        $artikel = ArtikelEdukasi::findOrFail($id);

        $this->normalisasiTanggalPublish($request);

        $validated = $request->validate([
            'judul'           => 'required|string|max:80',
            'jenis'           => 'required|in:edukasi,artikel,event,acara',
            'mode_media'      => 'required|in:gambar,banner_edukasi,banner_event,banner_artikel,tanpa_gambar',
            'sumber_konten'   => 'nullable|in:manual,link',
            'link_url'        => 'required_if:sumber_konten,link|nullable|url',
            'konten'          => 'required_unless:sumber_konten,link|nullable|string',
            'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gambar_url'      => 'nullable|url',
            'tanggal_publish' => 'nullable|date',
        ]);

        if (($validated['sumber_konten'] ?? 'manual') === 'link') {
            $validated['konten'] = trim($validated['link_url']);
        }

        $modeMedia = $validated['mode_media'];

        if ($modeMedia === 'gambar') {
            if ($request->hasFile('gambar')) {
                $this->hapusFileGambarLama($artikel->gambar);

                $gambar = $request->file('gambar');
                $namaGambar = time() . '_' . $gambar->getClientOriginalName();
                $gambar->move(public_path('uploads/artikel'), $namaGambar);
                $validated['gambar'] = $namaGambar;
            } elseif ($request->filled('gambar_url')) {
                $this->hapusFileGambarLama($artikel->gambar);
                $validated['gambar'] = $request->input('gambar_url');
            } elseif ($artikel->gambar && str_starts_with($artikel->gambar, 'banner:')) {
                $validated['gambar'] = null;
            } else {
                unset($validated['gambar']);
            }
        } elseif (str_starts_with($modeMedia, 'banner_')) {
            $this->hapusFileGambarLama($artikel->gambar);
            $tema = str_replace('banner_', '', $modeMedia);
            $validated['gambar'] = 'banner:' . $tema;
        } else {
            $this->hapusFileGambarLama($artikel->gambar);
            $validated['gambar'] = null;
        }

        unset($validated['gambar_url'], $validated['mode_media'], $validated['sumber_konten'], $validated['link_url']);
        $validated['tanggal_publish'] = $validated['tanggal_publish'] ?? null;

        $artikel->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Konten berhasil diperbarui.',
            'data'    => $artikel,
        ]);
    }

    public function destroy($id)
    {
        $artikel = ArtikelEdukasi::findOrFail($id);
        $this->hapusFileGambarLama($artikel->gambar);
        $artikel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Konten berhasil dihapus.',
        ]);
    }

    public function fetchUrl(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        try {
            $response = Http::timeout(10)->get($request->url);
            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Gagal mengakses halaman web tersebut.'], 400);
            }

            $html = $response->body();

            preg_match('/<meta property="og:title" content="([^"]+)"/i', $html, $titleMatches);
            if (empty($titleMatches[1])) {
                preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatches);
            }
            $judul = isset($titleMatches[1]) ? html_entity_decode($titleMatches[1]) : '';

            preg_match('/<meta property="og:image" content="([^"]+)"/i', $html, $imageMatches);
            $gambar = $imageMatches[1] ?? '';

            preg_match_all('/<p>(.*?)<\/p>/i', $html, $paragraphMatches);
            $paragraphs = array_map(fn($p) => strip_tags($p), $paragraphMatches[1] ?? []);
            $konten = implode("\n\n", array_slice(array_filter($paragraphs), 0, 5));

            return response()->json([
                'success' => true,
                'judul'   => trim($judul),
                'jenis'   => 'edukasi',
                'gambar'  => $gambar,
                'konten'  => trim($konten),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan parsing: ' . $e->getMessage()], 500);
        }
    }
}
