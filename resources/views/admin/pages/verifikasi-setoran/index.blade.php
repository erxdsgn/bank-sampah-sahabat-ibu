@extends('admin.layouts.app')

@section('title', 'Verifikasi Data Setoran')
@section('active', 'verifikasi-setoran')
@section('crumbs', 'Transaksi Sampah | Verifikasi Data Setoran')

@section('content')

    @php
        $fmtHarga = fn($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
        $fmtJumlah = fn($n) => rtrim(rtrim(number_format((float) $n, 3, ',', '.'), '0'), ',');

        $kategoriMap = $kategoriSampah->keyBy(function ($k) {
            return $k->id_kategori ?? $k->id;
        });

        $normalisasiSatuan = function ($satuan) {
            $satuan = strtolower(trim((string) $satuan));

            return match ($satuan) {
                'g', 'gram' => 'gram',
                'kg', 'kilogram', 'kilogram (kg)' => 'kg',
                'pcs', 'piece', 'pieces' => 'pcs',
                'l', 'liter', 'litre', 'liter (l)' => 'liter',
                'set' => 'set',
                'unit' => 'unit',
                default => 'gram',
            };
        };

        $namaSatuan = function ($satuan) use ($normalisasiSatuan) {
            return match ($normalisasiSatuan($satuan)) {
                'gram' => 'Gram',
                'kg' => 'Kilogram',
                'pcs' => 'Pieces',
                'liter' => 'Liter',
                'set' => 'Set',
                'unit' => 'Unit',
                default => 'Gram',
            };
        };

        $simbolSatuan = function ($satuan) use ($normalisasiSatuan) {
            return match ($normalisasiSatuan($satuan)) {
                'gram' => 'gram',
                'kg' => 'kg',
                'pcs' => 'pcs',
                'liter' => 'L',
                'set' => 'set',
                'unit' => 'unit',
                default => 'gram',
            };
        };
    @endphp

    @include('admin.pages.verifikasi-setoran.partials.hero-summary')
    @include('admin.pages.verifikasi-setoran.partials.table')

    <div class="toast-wrap" id="toastWrap"></div>
    @include('admin.pages.verifikasi-setoran.partials.modals')


    <link rel="stylesheet" href="{{ asset('css/admin/pages/verifikasi-setoran.css') }}">
    <script src="{{ asset('js/admin/pages/verifikasi-setoran.js') }}"></script>

@endsection
