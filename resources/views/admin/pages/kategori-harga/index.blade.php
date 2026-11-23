@extends('admin.layouts.app')

@section('title', 'Kategori & Harga Sampah')
@section('active', 'kategori-harga')
@section('crumbs', 'Master Data | Kategori & Harga Sampah')

@section('content')

@php
        // 1. Ringkasan statistik
        $totalInduk = $kategoriInduk->count();
        $totalSub = $kategoriInduk->sum(fn($k) => $k->anak->count());
        $totalHarga = $kategoriInduk->reduce(function ($carry, $k) {
            $carry += $k->hargaTerbaru ? 1 : 0;
            $carry += $k->anak->filter(fn($s) => $s->hargaTerbaru)->count();
            return $carry;
        }, 0);
        $totalBelum = $totalInduk + $totalSub - $totalHarga;

        // Helper: singkatan satuan -> nama lengkap
        $formatSatuan = function ($satuan) {
            $map = [
                'kg' => 'Kilogram (kg)',
                'pcs' => 'Pieces (pcs)',
                'gram' => 'Gram (gram)',
                'liter' => 'Liter (L)',
                'set' => 'Set (set)',
                'unit' => 'Unit (unit)',
            ];
            $satuanLower = strtolower(trim((string) $satuan));
            return $map[$satuanLower] ?? ($satuan ? ucfirst($satuan) : '-');
        };

        // 2. Riwayat harga per kategori
        $riwayatMap = [];
        $mapRiwayat = function ($kat) use (&$riwayatMap) {
            $riwayatMap[$kat->id_kategori] = collect($kat->hargaSampah ?? [])
                ->sortByDesc('tanggal_berlaku')
                ->map(function ($h) use ($kat) {
                    $tgl = $h->tanggal_berlaku ? \Carbon\Carbon::parse($h->tanggal_berlaku) : null;
                    return [
                        'id_harga' => $h->id_harga,
                        'harga' => (float) $h->harga_satuan,
                        'tanggal' => $tgl ? $tgl->translatedFormat('d M Y') : '-',
                        'tanggal_raw' => $tgl ? $tgl->format('Y-m-d') : '',
                        'delete_url' => route('admin.kategori-harga.harga.destroy', [
                            $kat->id_kategori,
                            'hargaSampah' => $h->id_harga,
                        ]),
                    ];
                })
                ->values();
        };

        foreach ($kategoriInduk as $k) {
            $mapRiwayat($k);
            foreach ($k->anak as $s) {
                $mapRiwayat($s);
            }
        }

        // 3. Daftar kategori untuk rekap bulanan
        $daftarKategori = [];
        foreach ($kategoriInduk as $k) {
            $daftarKategori[] = [
                'id' => $k->id_kategori,
                'nama' => $k->nama_kategori,
                'satuan' => $formatSatuan($k->satuan),
                'sub' => false,
            ];
            foreach ($k->anak as $s) {
                $daftarKategori[] = [
                    'id' => $s->id_kategori,
                    'nama' => $s->nama_kategori,
                    'satuan' => $formatSatuan($s->satuan),
                    'sub' => true,
                ];
            }
        }

        // SVG reusable
        $iconEdit =
            '<svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        $iconHarga =
            '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M15 8.5c-.6-.7-1.6-1.1-2.8-1.1-1.7 0-2.8.9-2.8 2.2 0 1.2 1 1.8 2.8 2.2 1.8.4 2.8 1 2.8 2.2 0 1.3-1.1 2.2-2.9 2.2-1.3 0-2.4-.5-3.1-1.3M12 5v14"></path></svg>';
        $iconHapus =
            '<svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6M14 11v6"></path></svg>';
    @endphp


<script>
window.KategoriHargaPageConfig = {
    riwayatData: @json($riwayatMap),
    daftarKategori: @json($daftarKategori),
    urlKategoriStore: @json(route('admin.kategori-harga.store')),
    urlKategoriUpdate: @json(route('admin.kategori-harga.update', ':id')),
    urlKategoriHapus: @json(route('admin.kategori-harga.destroy', ':id')),
    flashSuccess: @json(session('success'))
};
</script>

    @include('admin.pages.kategori-harga.partials.hero')

    @include('admin.pages.kategori-harga.partials.summary')

    @include('admin.pages.kategori-harga.partials.table')

    @include('admin.pages.kategori-harga.partials.toast')

    @include('admin.pages.kategori-harga.partials.modal-kategori')

    @include('admin.pages.kategori-harga.partials.modal-harga')

    @include('admin.pages.kategori-harga.partials.modal-hapus')

    @include('admin.pages.kategori-harga.partials.modal-bulanan')

    <link rel="stylesheet" href="{{ asset('css/admin/pages/kategori-harga.css') }}">
    <script src="{{ asset('js/admin/pages/kategori-harga.js') }}"></script>

@endsection
