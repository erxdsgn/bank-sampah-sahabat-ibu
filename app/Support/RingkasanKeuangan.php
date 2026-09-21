<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RingkasanKeuangan
{
    /**
     * Set 'setoran' ke false agar pengeluaran uang tunai tidak terhitung dua kali
     * dengan 'pencairan'.
     */
    public const SUMBER_AKTIF = [
        'kas'       => true,
        'setoran'   => false, // Di-nonaktifkan untuk menghindari double-counting pengeluaran
        'pencairan' => true,  // Aktif: mencerminkan arus kas tunai keluar
        'penjualan' => true,
    ];

    public const ABAIKAN_KAS_OTOMATIS = true;

    public const STATUS_PENCAIRAN_DIHITUNG = ['selesai', 'berhasil', 'disetujui', 'approved', 'dicairkan'];

    private const LABEL = [
        'kas'       => 'Kas Manual',
        'setoran'   => 'Setoran',
        'pencairan' => 'Pencairan Saldo',
        'penjualan' => 'Penjualan Pengepul',
    ];

    public static function transaksi(): Collection
    {
        $semua = collect();

        if (self::SUMBER_AKTIF['kas']) {
            $semua = $semua->concat(self::dariKas());
        }
        if (self::SUMBER_AKTIF['setoran']) {
            $semua = $semua->concat(self::dariSetoran());
        }
        if (self::SUMBER_AKTIF['pencairan']) {
            $semua = $semua->concat(self::dariPencairan());
        }
        if (self::SUMBER_AKTIF['penjualan']) {
            $semua = $semua->concat(self::dariPenjualan());
        }

        return $semua->sortByDesc('tanggal')->values();
    }

    private static function dariKas(): Collection
    {
        $query = DB::table('kas');

        if (self::ABAIKAN_KAS_OTOMATIS && Schema::hasColumn('kas', 'tipe_referensi')) {
            $query->whereNull('tipe_referensi');
        }

        return $query->get()->map(fn ($r) => self::baris(
            'kas',
            (int) $r->id_kas,
            $r->tanggal,
            ucfirst(strtolower(trim((string) $r->jenis))),
            $r->kategori_transaksi ?? null,
            $r->keterangan ?? null,
            $r->jumlah
        ));
    }

    private static function dariSetoran(): Collection
    {
        return DB::table('penyetoran as p')
            ->leftJoin('warga as w', 'w.id_warga', '=', 'p.id_warga')
            ->where('p.status', 'approved')
            ->select('p.tanggal_setoran', 'p.total_nilai', 'p.total_berat', 'w.nama')
            ->get()
            ->map(fn ($r) => self::baris(
                'setoran',
                null,
                $r->tanggal_setoran,
                'Pengeluaran',
                'Pembelian Sampah',
                'Setoran sampah ' . ($r->nama ?? '-') . ' (' . number_format((float) $r->total_berat, 0, ',', '.') . ' gram)',
                $r->total_nilai
            ));
    }

    private static function dariPencairan(): Collection
    {
        return DB::table('pencairan_saldo as c')
            ->leftJoin('warga as w', 'w.id_warga', '=', 'c.id_warga')
            ->whereIn(DB::raw('LOWER(c.status)'), self::STATUS_PENCAIRAN_DIHITUNG)
            ->select('c.tanggal_pencairan', 'c.jumlah', 'c.metode_transfer', 'w.nama')
            ->get()
            ->map(fn ($r) => self::baris(
                'pencairan',
                null,
                $r->tanggal_pencairan,
                'Pengeluaran',
                'Pencairan Saldo',
                'Pencairan saldo ' . ($r->nama ?? '-') . ($r->metode_transfer ? ' via ' . $r->metode_transfer : ''),
                $r->jumlah
            ));
    }

    private static function dariPenjualan(): Collection
    {
        return DB::table('barang_keluar as b')
            ->leftJoin('kategori_sampah as k', 'k.id_kategori', '=', 'b.id_kategori')
            ->select('b.tanggal', 'b.total', 'b.berat_gram', 'b.pembeli', 'k.nama_kategori')
            ->get()
            ->map(fn ($r) => self::baris(
                'penjualan',
                null,
                $r->tanggal,
                'Pemasukan',
                'Penjualan Sampah',
                'Penjualan ' . number_format((float) $r->berat_gram, 0, ',', '.') . ' gram '
                    . ($r->nama_kategori ?? '') . ' ke ' . ($r->pembeli ?? '-'),
                $r->total
            ));
    }

    private static function baris(string $sumber, ?int $idKas, $tanggal, string $jenis, ?string $kategori, ?string $keterangan, $jumlah): object
    {
        $tgl = Carbon::parse($tanggal ?: now());

        return (object) [
            'id_kas'       => $idKas,
            'sumber'       => $sumber,
            'sumber_label' => self::LABEL[$sumber],
            'otomatis'     => $sumber !== 'kas',
            'tanggal'      => $tgl->toDateString(),
            'periode'      => $tgl->translatedFormat('F Y'),
            'jenis'        => $jenis,
            'kategori'     => $kategori ?: '-',
            'keterangan'   => $keterangan,
            'jumlah'       => (float) $jumlah,
        ];
    }
}
