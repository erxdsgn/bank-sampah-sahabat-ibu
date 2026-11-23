@extends('admin.layouts.app')

@section('title', 'Artikel & Edukasi')
@section('active', 'artikel')
@section('crumbs', 'Konten & Laporan | Artikel & Edukasi')

@section('content')

@php
    $totalKonten = $artikel->count();
    $totalPublikasi = $artikel->whereNotNull('tanggal_publish')->count();
    $totalDraft = $artikel->whereNull('tanggal_publish')->count();
    $totalAcara = $artikel->whereIn('jenis', ['event', 'acara'])->count();

    $labelJenis = [
        'edukasi' => ['Edukasi Sampah', 'badge--info'],
        'artikel' => ['Artikel', 'badge--info'],
        'event'   => ['Event', 'badge--purple'],
        'acara'   => ['Event', 'badge--purple'],
    ];

    $bannerConfig = [
        'edukasi' => ['label' => 'Banner Edukasi', 'icon' => '🌱', 'class' => 'banner-grad--edukasi'],
        'event'   => ['label' => 'Banner Event',   'icon' => '📅', 'class' => 'banner-grad--event'],
        'artikel' => ['label' => 'Banner Artikel', 'icon' => '📰', 'class' => 'banner-grad--artikel'],
    ];

    $preview = $artikel->whereNotNull('tanggal_publish')->first() ?? $artikel->first();
@endphp



<script src="https://cdn.jsdelivr.net/npm/@ckeditor/ckeditor5-build-classic@41.4.2/build/ckeditor.js"></script>

<script>
window.ArtikelPageConfig = {
    dataArtikel: @json($artikel),
    jsLabelJenis: @json(array_map(fn($v) => $v[0], $labelJenis)),
    urlArtikelStore: @json(route('admin.artikel.store')),
    urlArtikelUpdate: @json(url('/admin/pages/artikel') . '/:id'),
    urlArtikelDestroy: @json(url('/admin/pages/artikel') . '/:id'),
    urlGambarArtikel: @json(asset('uploads/artikel')),
    flashSuccess: @json(session('success'))
};
</script>

<div class="artikel-page">

    @include('admin.pages.artikel.partials.hero')

    @include('admin.pages.artikel.partials.summary')

    @include('admin.pages.artikel.partials.list-preview')

    @include('admin.pages.artikel.partials.form')

    @include('admin.pages.artikel.partials.toast')

    @include('admin.pages.artikel.partials.modal-detail')

    @include('admin.pages.artikel.partials.modal-hapus')

</div>

    <link rel="stylesheet" href="{{ asset('css/admin/pages/artikel.css') }}">
    <script src="{{ asset('js/admin/pages/artikel.js') }}"></script>

@endsection
