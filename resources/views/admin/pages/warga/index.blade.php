@extends('admin.layouts.app')

@section('title', 'Data Warga')
@section('active', 'warga')
@section('crumbs', 'Master Data | Data Warga')

<script>window.PAGE_CONFIG = { success: @json(session('success')), routes: { leaderboard: @json(route('admin.warga.leaderboard-popup')), store: @json(route('admin.warga.store')) } };</script>


@section('content')
    @include('admin.pages.warga.partials.hero-summary')
    @include('admin.pages.warga.partials.table')
    @include('admin.pages.warga.partials.modals')

    <link rel="stylesheet" href="{{ asset('css/admin/pages/warga.css') }}">
    <script src="{{ asset('js/admin/pages/warga.js') }}"></script>
@endsection


