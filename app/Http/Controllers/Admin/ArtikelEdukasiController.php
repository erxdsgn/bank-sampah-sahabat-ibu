<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtikelEdukasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $this->normalisasiTanggalPublish($request);

        $validated = $request->validate([
            'judul'           => 'required|string|max:80',
            'jenis'           => 'required|in:edukasi,artikel,acara',
            'konten'          => 'required|string',
            'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gambar_url'      => 'nullable|url',
            'tanggal_publish' => 'nullable|date',
        ]);

        // Tangani jika ada file gambar yang diupload
        if ($request->hasFile('gambar')) {
            $gambar = $request->file('gambar');
            $namaGambar = time() . '_' . $gambar->getClientOriginalName();
            $gambar->move(public_path('uploads/artikel'), $namaGambar);
            $validated['gambar'] = $namaGambar;
        }
        // Tangani jika gambar diambil dari hasil import URL otomatis
        elseif ($request->filled('gambar_url')) {
            $validated['gambar'] = $request->input('gambar_url');
        } else {
            $validated['gambar'] = null;
        }

        unset($validated['gambar_url']);
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
            'jenis'           => 'required|in:edukasi,artikel,acara',
            'konten'          => 'required|string',
            'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'gambar_url'      => 'nullable|url',
            'tanggal_publish' => 'nullable|date',
        ]);

        if ($request->hasFile('gambar')) {
            if ($artikel->gambar && !str_starts_with($artikel->gambar, 'http')) {
                $gambarLama = public_path('uploads/artikel/' . $artikel->gambar);
                if (file_exists($gambarLama)) {
                    unlink($gambarLama);
                }
            }

            $gambar = $request->file('gambar');
            $namaGambar = time() . '_' . $gambar->getClientOriginalName();
            $gambar->move(public_path('uploads/artikel'), $namaGambar);
            $validated['gambar'] = $namaGambar;
        } elseif ($request->filled('gambar_url')) {
            $validated['gambar'] = $request->input('gambar_url');
        } else {
            // Tetap gunakan gambar lama jika tidak ada input baru
            unset($validated['gambar']);
        }

        unset($validated['gambar_url']);
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

        if ($artikel->gambar && !str_starts_with($artikel->gambar, 'http')) {
            $path = public_path('uploads/artikel/' . $artikel->gambar);
            if (file_exists($path)) {
                unlink($path);
            }
        }

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

            // Ambil OpenGraph Title atau HTML Title
            preg_match('/<meta property="og:title" content="([^"]+)"/i', $html, $titleMatches);
            if (empty($titleMatches[1])) {
                preg_match('/<title>(.*?)<\/title>/i', $html, $titleMatches);
            }
            $judul = isset($titleMatches[1]) ? html_entity_decode($titleMatches[1]) : '';

            // Ambil OpenGraph Image
            preg_match('/<meta property="og:image" content="([^"]+)"/i', $html, $imageMatches);
            $gambar = $imageMatches[1] ?? '';

            // Ambil isi paragraf teks artikel
            preg_match_all('/<p>(.*?)<\/p>/i', $html, $paragraphMatches);
            $paragraphs = array_map(fn($p) => strip_tags($p), $paragraphMatches[1] ?? []);
            $konten = implode("\n\n", array_slice(array_filter($paragraphs), 0, 5));

            return response()->json([
                'success' => true,
                'judul' => trim($judul),
                'jenis' => 'edukasi',
                'gambar' => $gambar,
                'konten' => trim($konten)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan parsing: ' . $e->getMessage()], 500);
        }
    }
}
