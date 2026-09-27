<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtikelEdukasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ArtikelEdukasiController extends Controller
{
    /**
     * Menampilkan halaman Artikel & Edukasi.
     *
     * PENTING: kirim koleksi Eloquent apa adanya (bukan di-map jadi array),
     * karena view mengakses field-nya dengan sintaks objek ($item->id_artikel,
     * $item->jenis, dst) dan butuh SEMUA kolom (termasuk `jenis`) untuk modal
     * edit & filter kategori.
     */
    public function index()
    {
        $artikel = ArtikelEdukasi::orderBy('id_artikel', 'desc')->get();

        return view('admin.pages.artikel', compact('artikel'));
    }

    /**
     * Menormalkan input tanggal_publish sebelum divalidasi.
     *
     * Form mengirim string kosong '' saat admin menekan tombol
     * "Simpan sebagai Draft", apa pun isi field tanggalnya. String kosong
     * TIDAK valid untuk rule 'date', jadi ia harus diubah eksplisit menjadi
     * null di sini -- tidak digantungkan pada middleware
     * ConvertEmptyStringsToNull, supaya perilaku ini konsisten walau
     * middleware itu tidak aktif di rute ini.
     */
    private function normalisasiTanggalPublish(Request $request): void
    {
        $request->merge([
            'tanggal_publish' => $request->filled('tanggal_publish')
                ? $request->input('tanggal_publish')
                : null,
        ]);
    }

    /**
     * Menyimpan artikel baru.
     */
    public function store(Request $request)
    {
        $this->normalisasiTanggalPublish($request);

        $validated = $request->validate([
            'judul'           => 'required|string|max:255',
            'jenis'           => 'required|in:edukasi,artikel,acara',
            'konten'          => 'required|string',
            'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tanggal_publish' => 'nullable|date',
        ]);

        if ($request->hasFile('gambar')) {
            $gambar = $request->file('gambar');
            $namaGambar = time() . '_' . $gambar->getClientOriginalName();

            $gambar->move(public_path('uploads/artikel'), $namaGambar);

            $validated['gambar'] = $namaGambar;
        }

        // Pastikan key selalu ada, walau validate() bisa saja mengecualikannya
        // ketika nilainya null dan field tidak "required". Nilainya TIDAK
        // di-default ke hari ini di sini -- kalau di-default, konten yang
        // disimpan sebagai draft (tanggal_publish = null) akan langsung
        // dianggap dipublikasikan. Biarkan null berarti draft; JS sudah
        // mengisi tanggal hari ini sendiri saat aksi = 'publish'.
        $validated['tanggal_publish'] = $validated['tanggal_publish'] ?? null;

        $validated['id_admin'] = Auth::id() ?? 1;

        $artikel = ArtikelEdukasi::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Konten berhasil ditambahkan.',
            'data'    => $artikel,
        ]);
    }

    /**
     * Mengubah artikel.
     */
    public function update(Request $request, $id)
    {
        $artikel = ArtikelEdukasi::findOrFail($id);

        $this->normalisasiTanggalPublish($request);

        $validated = $request->validate([
            'judul'           => 'required|string|max:255',
            'jenis'           => 'required|in:edukasi,artikel,acara',
            'konten'          => 'required|string',
            'gambar'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tanggal_publish' => 'nullable|date',
        ]);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($artikel->gambar) {
                $gambarLama = public_path('uploads/artikel/' . $artikel->gambar);

                if (file_exists($gambarLama)) {
                    unlink($gambarLama);
                }
            }

            $gambar = $request->file('gambar');
            $namaGambar = time() . '_' . $gambar->getClientOriginalName();

            $gambar->move(public_path('uploads/artikel'), $namaGambar);

            $validated['gambar'] = $namaGambar;
        }

        // Sama seperti di store(): nilai null berarti admin menekan
        // "Simpan sebagai Draft", jadi tanggal_publish HARUS tetap null
        // (bukan di-default ke hari ini atau ke tanggal lama), supaya
        // konten yang sudah publish pun bisa dikembalikan menjadi draft.
        $validated['tanggal_publish'] = $validated['tanggal_publish'] ?? null;

        $artikel->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Konten berhasil diperbarui.',
            'data'    => $artikel,
        ]);
    }

    /**
     * Menghapus artikel.
     *
     * Dipanggil lewat fetch() dari blade, jadi HARUS balas JSON —
     * bukan redirect (fetch tidak mengikuti redirect sebagai "sukses").
     */
    public function destroy($id)
    {
        $artikel = ArtikelEdukasi::findOrFail($id);

        if ($artikel->gambar) {
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
}
