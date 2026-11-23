@extends('admin.layouts.app')

@section('title', 'Keuangan')
@section('active', 'keuangan')
@section('crumbs', 'Keuangan & Produk | Keuangan')

@section('content')

@php
    // Mengurutkan transaksi: Tanggal terbaru di atas.
    // Jika tanggal sama, diurutkan berdasarkan ID terbesar (terbaru) di atas.
    $sortedTransaksi = $transaksi->sort(function ($a, $b) {
        if ($a->tanggal === $b->tanggal) {
            return $b->id_kas <=> $a->id_kas;
        }
        return $b->tanggal <=> $a->tanggal;
     });
@endphp

<script>
window.KeuanganPageConfig = {
    saldoKasSistem: @json($saldoKas ?? 0),
    flashSuccess: @json(session('success')),
    flashError: @json(session('error')),
    urlStore: @json(route('admin.keuangan.store'))
};
</script>

<div class="keuangan-page">

    @include('admin.pages.keuangan.partials.hero')

    @include('admin.pages.keuangan.partials.summary')

    @include('admin.pages.keuangan.partials.table')

    @include('admin.pages.keuangan.partials.toast')

    @include('admin.pages.keuangan.partials.modal-transaksi')

    @include('admin.pages.keuangan.partials.modal-detail')

    @include('admin.pages.keuangan.partials.modal-hapus')

</div>

    <link rel="stylesheet" href="{{ asset('css/admin/pages/keuangan.css') }}">
    <script src="{{ asset('js/admin/pages/keuangan.js') }}"></script>

@endsection
