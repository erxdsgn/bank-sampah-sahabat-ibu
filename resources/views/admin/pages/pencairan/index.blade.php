@extends('admin.layouts.app')

@section('title', 'Pencairan Saldo')
@section('active', 'pencairan')
@section('crumbs', 'Transaksi Sampah | Pencairan Saldo')

<script>window.PAGE_CONFIG = { success: @json(session('success')), routes: { store: @json(route('admin.pencairan.store')), index: @json(route('admin.pencairan.index')) } };</script>


@section('content')
    @include('admin.pages.pencairan.partials.table')
    @include('admin.pages.pencairan.partials.modals')

    <link rel="stylesheet" href="{{ asset('css/admin/pages/pencairan.css') }}">
    <script src="{{ asset('js/admin/pages/pencairan.js') }}"></script>
@endsection

