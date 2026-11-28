@extends('admin.errors.layout')
@section('code', '419')
@section('tag', 'Page Expired (CSRF)')
@section('title', 'Halaman Kedaluwarsa')
@section('message', 'Sesi keamanan formulir telah habis karena halaman terlalu lama terbuka sebelum dikirim.')
@section('hint', 'Muat ulang halaman untuk mendapatkan token baru, lalu kirim ulang formulir Anda.')
@section('icon', 'fa-solid fa-hourglass-half')
@section('icon_style', 'bg-amber-50 border-amber-200/80 text-amber-600')
@section('actions')
    <button type="button" onclick="location.reload()" class="w-full sm:w-auto px-6 py-3 rounded-full bg-gradient-to-r from-[#28B446] to-[#1E9638] text-white font-display font-semibold text-sm shadow-green-glow transition flex items-center justify-center gap-2 cursor-pointer">
        <i class="fa-solid fa-rotate-right text-xs"></i> <span>Muat Ulang Halaman</span>
    </button>
    <a href="{{ url('/') }}" class="w-full sm:w-auto px-5 py-3 rounded-full bg-white hover:bg-green-50 text-brand-forest border border-green-200 font-display font-semibold text-sm transition flex items-center justify-center gap-2">
        <i class="fa-solid fa-house text-xs text-brand"></i> <span>Beranda</span>
    </a>
@endsection
