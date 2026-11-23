@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('active', 'dashboard')
@section('crumbs', 'Dashboard')

@section('content')

@php
        // Riwayat setoran: terbaru di atas
        $riwayat = collect($riwayatSetoran)->sortByDesc('id_setoran')->values();

        $opsi = collect($opsiSatuan);
    @endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>


<script>
window.DashboardPageConfig = {
    opsiSatuan: @json($opsiSatuan),
    dataBulananPerSatuan: @json($setoranPerBulan),
    dataJenisPerSatuan: @json($statistikJenisSampah),
    dataTotalSampahPerSatuan: @json($totalJumlahSampahPerSatuan ?? [])
};
</script>

    @include('admin.pages.dashboard.partials.hero')

    @include('admin.pages.dashboard.partials.statistics')

    @include('admin.pages.dashboard.partials.charts')

    @include('admin.pages.dashboard.partials.history')

    <link rel="stylesheet" href="{{ asset('css/admin/pages/dashboard.css') }}">
    <script src="{{ asset('js/admin/pages/dashboard.js') }}"></script>

@endsection
