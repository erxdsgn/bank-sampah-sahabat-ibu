<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use App\Support\RingkasanKeuangan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $transaksi = RingkasanKeuangan::transaksi();

        $totalPemasukan   = (float) $transaksi->where('jenis', 'Pemasukan')->sum('jumlah');
        $totalPengeluaran = (float) $transaksi->where('jenis', 'Pengeluaran')->sum('jumlah');
        $saldoKas         = $totalPemasukan - $totalPengeluaran;
        $jumlahTransaksi  = $transaksi->count();

        // Daftar periode unik (Bulan Tahun), urut dari yang terbaru
        $periodeList = $transaksi
            ->pluck('periode')
            ->filter()
            ->unique()
            ->values();

        return view('admin.pages.keuangan', compact(
            'transaksi',
            'totalPemasukan',
            'totalPengeluaran',
            'saldoKas',
            'jumlahTransaksi',
            'periodeList'
        ));
    }

    /**
     * Simpan transaksi baru (manual oleh admin).
     */
    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['id_admin'] = Auth::id() ?? 1;

        $transaksi = Keuangan::create($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi berhasil ditambahkan.',
                'data'    => $transaksi
            ]);
        }

        return redirect()
            ->route('admin.keuangan.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu transaksi (dipakai untuk modal detail via AJAX/JSON).
     */
    public function show(Keuangan $keuangan)
    {
        return response()->json($keuangan);
    }

    /**
     * Perbarui transaksi.
     *
     * DINONAKTIFKAN: transaksi yang sudah tersimpan tidak boleh diedit sama sekali,
     * baik transaksi kas manual maupun transaksi otomatis dari modul lain.
     * Transaksi hanya bisa dilihat (show) atau dihapus (destroy).
     */
    public function update(Request $request, Keuangan $keuangan)
    {
        $pesan = 'Transaksi yang sudah tersimpan tidak dapat diedit. '
            . 'Silakan hapus transaksi ini lalu buat transaksi baru jika diperlukan perubahan.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'error', 'message' => $pesan], 422);
        }

        return redirect()
            ->route('admin.keuangan.index')
            ->with('error', $pesan);
    }

    /**
     * Hapus transaksi.
     */
    public function destroy(Keuangan $keuangan)
    {
        if ($keuangan->tipe_referensi) {
            $pesan = 'Transaksi ini tercatat otomatis dari modul lain ('
                . $keuangan->tipe_referensi
                . ') dan tidak bisa dihapus manual di sini.';

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => $pesan], 422);
            }

            return redirect()
                ->route('admin.keuangan.index')
                ->with('error', $pesan);
        }

        $keuangan->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi berhasil dihapus.'
            ]);
        }

        return redirect()
            ->route('admin.keuangan.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Aturan validasi untuk store (transaksi manual saja).
     */
    private function validasi(Request $request): array
    {
        $validated = $request->validate([
            'jenis'      => ['required', Rule::in(['Pemasukan', 'Pengeluaran', 'pemasukan', 'pengeluaran'])],
            'tanggal'    => ['required', 'date'],
            'kategori'   => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'jumlah'     => ['required', 'numeric', 'min:0'],
        ]);

        return [
            'jenis'              => strtolower($validated['jenis']),
            'tanggal'            => $validated['tanggal'],
            'kategori_transaksi' => $validated['kategori'],
            'keterangan'         => $validated['keterangan'] ?? null,
            'jumlah'             => $validated['jumlah'],
        ];
    }
}
