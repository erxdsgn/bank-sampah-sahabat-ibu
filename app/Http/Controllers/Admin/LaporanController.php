<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /**
     * Daftar riwayat + pencarian + filter + ringkasan.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'jenis', 'status', 'dari', 'sampai']);

        $base = Transaksi::filter($filters);

        // Ringkasan dihitung dari hasil filter. Nilai uang & berat hanya menghitung
        // transaksi berstatus "selesai".
        $ringkasan = [
            // Total berat sampah dari setoran warga (kg).
            'kg' => TransaksiItem::whereHas('transaksi', function ($q) use ($filters) {
                $q->filter($filters)->where('jenis', 'setoran')->where('status', 'selesai');
            })->sum('berat'),

            'jumlah'    => (clone $base)->count(),
            'pencairan' => (clone $base)->where('jenis', 'pencairan')->where('status', 'selesai')->sum('total'),

            // Pemasukan = uang hasil penjualan sampah ke pengepul.
            'pemasukan' => (clone $base)->where('jenis', 'penjualan')->where('status', 'selesai')->sum('total'),
        ];

        $transaksis = $base
            ->with('warga')
            ->latest('tanggal')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pages.laporan', compact('transaksis', 'ringkasan', 'filters'));
    }

    public function create(): View
    {
        return view('admin.pages.laporan-form', [
            'transaksi' => new Transaksi(['tanggal' => now(), 'status' => 'selesai', 'jenis' => 'setoran']),
            'wargas'    => $this->wargas(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $transaksi = DB::transaction(function () use ($data) {
            $transaksi = Transaksi::create([
                'kode'       => Transaksi::generateKode(),
                'warga_id'   => $data['warga_id'] ?? null,
                'jenis'      => $data['jenis'],
                'tanggal'    => $data['tanggal'],
                'status'     => $data['status'],
                'keterangan' => $data['keterangan'] ?? null,
                'total'      => 0,
            ]);

            $this->syncItems($transaksi, $data);

            return $transaksi;
        });

        return redirect()
            ->route('admin.laporan.show', $transaksi)
            ->with('success', "Transaksi {$transaksi->kode} berhasil ditambahkan.");
    }

    /**
     * Detail satu transaksi (rincian item).
     */
    public function show(Transaksi $transaksi): View
    {
        $transaksi->load(['warga', 'items']);

        return view('admin.pages.laporan-detail', compact('transaksi'));
    }

    public function edit(Transaksi $transaksi): View
    {
        $transaksi->load('items');

        return view('admin.pages.laporan-form', [
            'transaksi' => $transaksi,
            'wargas'    => $this->wargas(),
        ]);
    }

    public function update(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($transaksi, $data) {
            $transaksi->update([
                'warga_id'   => $data['warga_id'] ?? null,
                'jenis'      => $data['jenis'],
                'tanggal'    => $data['tanggal'],
                'status'     => $data['status'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $this->syncItems($transaksi, $data);
        });

        return redirect()
            ->route('admin.laporan.show', $transaksi)
            ->with('success', "Transaksi {$transaksi->kode} berhasil diperbarui.");
    }

    public function destroy(Transaksi $transaksi): RedirectResponse
    {
        $kode = $transaksi->kode;
        $transaksi->delete(); // item ikut terhapus (cascadeOnDelete)

        return redirect()
            ->route('admin.laporan.index')
            ->with('success', "Transaksi {$kode} berhasil dihapus.");
    }

    /* ---------------------------------------------------------------------- */
    /* Helper                                                                 */
    /* ---------------------------------------------------------------------- */

    private function wargas()
    {
        return Warga::orderBy(Transaksi::kolomNamaWarga())->get();
    }

    private function validated(Request $request): array
    {
        $jenis       = $request->input('jenis');
        $isPencairan = $jenis === 'pencairan';

        return $request->validate([
            'jenis'      => ['required', Rule::in(array_keys(Transaksi::JENIS))],
            'tanggal'    => ['required', 'date'],
            'status'     => ['required', Rule::in(array_keys(Transaksi::STATUS))],
            'warga_id'   => [
                Rule::requiredIf(in_array($jenis, ['setoran', 'pencairan'], true)),
                'nullable',
                Rule::exists((new Warga())->getTable(), (new Warga())->getKeyName()),
            ],
            'keterangan' => ['nullable', 'string', 'max:500'],

            // Pencairan: cukup nominal. Setoran/penjualan: rincian item.
            'nominal'              => [Rule::requiredIf($isPencairan), 'nullable', 'numeric', 'min:1'],
            'items'                => [Rule::requiredIf(! $isPencairan), 'nullable', 'array', 'min:1'],
            'items.*.nama_item'    => ['required', 'string', 'max:100'],
            'items.*.berat'        => ['required', 'numeric', 'min:0.01'],
            'items.*.harga_per_kg' => ['required', 'numeric', 'min:0'],
        ], [
            'warga_id.required'             => 'Pilih warga untuk jenis transaksi ini.',
            'warga_id.exists'               => 'Warga yang dipilih tidak ditemukan.',
            'nominal.required'              => 'Nominal pencairan wajib diisi.',
            'items.required'                => 'Tambahkan minimal satu item sampah.',
            'items.*.nama_item.required'    => 'Nama item sampah wajib diisi.',
            'items.*.berat.required'        => 'Berat item wajib diisi.',
            'items.*.berat.min'             => 'Berat item minimal 0,01 kg.',
            'items.*.harga_per_kg.required' => 'Harga per kg wajib diisi.',
        ]);
    }

    /**
     * Simpan ulang rincian item dan hitung total.
     */
    private function syncItems(Transaksi $transaksi, array $data): void
    {
        $transaksi->items()->delete();

        if ($data['jenis'] === 'pencairan') {
            $transaksi->update(['total' => $data['nominal']]);

            return;
        }

        $total = 0;

        foreach ($data['items'] as $item) {
            $subtotal = round($item['berat'] * $item['harga_per_kg'], 2);
            $total   += $subtotal;

            $transaksi->items()->create([
                'nama_item'    => $item['nama_item'],
                'berat'        => $item['berat'],
                'harga_per_kg' => $item['harga_per_kg'],
                'subtotal'     => $subtotal,
            ]);
        }

        $transaksi->update(['total' => $total]);
    }
}