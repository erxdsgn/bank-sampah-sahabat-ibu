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

        // Hitung pemasukan dan pengeluaran secara case-insensitive
        $totalPemasukan = (float) $transaksi->filter(function ($item) {
            $jenis = is_array($item) ? ($item['jenis'] ?? '') : ($item->jenis ?? '');
            return strtolower($jenis) === 'pemasukan';
        })->sum('jumlah');

        $totalPengeluaran = (float) $transaksi->filter(function ($item) {
            $jenis = is_array($item) ? ($item['jenis'] ?? '') : ($item->jenis ?? '');
            return strtolower($jenis) === 'pengeluaran';
        })->sum('jumlah');

        $saldoKas        = $totalPemasukan - $totalPengeluaran;
        $jumlahTransaksi = $transaksi->count();

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

    public function show(Keuangan $keuangan)
    {
        return response()->json($keuangan);
    }

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

    private function validasi(Request $request): array
    {
        $validated = $request->validate([
            'jenis'      => ['required', Rule::in(['Pemasukan', 'Pengeluaran', 'pemasukan', 'pengeluaran'])],
            'tanggal'    => ['required', 'date'],
            'kategori'   => ['required', 'string', 'max:100'],
            'keterangan' => ['required', 'string'],
            'jumlah'     => ['required', 'numeric', 'min:1', 'max:49999999'],
        ]);

        return [
            'jenis'              => ucfirst(strtolower($validated['jenis'])),
            'tanggal'            => $validated['tanggal'],
            'kategori_transaksi' => $validated['kategori'],
            'keterangan'         => $validated['keterangan'] ?? null,
            'jumlah'             => $validated['jumlah'],
        ];
    }
}
