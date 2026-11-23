@extends('admin.layouts.app')

@section('title', 'Katalog Barang Daur Ulang')
@section('active', 'katalog')
@section('crumbs', 'Keuangan & Produk | Katalog Barang')

@section('content')

<script>
window.KatalogPageConfig = {
    flashSuccess: @json(session('success')),
    urlStore: @json(route('admin.katalog.store')),
    urlIndex: @json(route('admin.katalog.index'))
};
</script>

<div class="katalog-page">

    @include('admin.pages.katalog.partials.hero')

    @include('admin.pages.katalog.partials.grid')

    @include('admin.pages.katalog.partials.toast')

    @include('admin.pages.katalog.partials.modal-detail')

    @include('admin.pages.katalog.partials.modal-tambah')

    @include('admin.pages.katalog.partials.modal-edit')

    @include('admin.pages.katalog.partials.modal-hapus')

</div>

    <link rel="stylesheet" href="{{ asset('css/admin/pages/katalog.css') }}">
    <script src="{{ asset('js/admin/pages/katalog.js') }}"></script>

@endsection
