@extends('admin.errors.layout')
@section('code', '403')
@section('tag', 'Tidak Memiliki Hak Akses')
@section('title', 'Akses Halaman Ditolak')
@section('message', $exception->getMessage() ?: 'Akun Anda sudah masuk, namun tidak memiliki izin untuk membuka halaman ini.')
@section('hint', 'Halaman ini khusus untuk pengurus/administrator aktif Bank Sampah Sahabat Ibu.')
@section('icon', 'fa-solid fa-shield-halved')
@section('icon_style', 'bg-red-50 border-red-200/80 text-red-600')
@section('actions')
    <a href="{{ url('/') }}" class="w-full sm:w-auto px-6 py-3 rounded-full bg-gradient-to-r from-[#28B446] to-[#1E9638] text-white font-display font-semibold text-sm shadow-green-glow transition flex items-center justify-center gap-2">
        <i class="fa-solid fa-house text-xs"></i> <span>Kembali ke Beranda</span>
    </a>
    <a href="{{ route('login') }}" class="w-full sm:w-auto px-5 py-3 rounded-full bg-white hover:bg-green-50 text-brand-forest border border-green-200 font-display font-semibold text-sm transition flex items-center justify-center gap-2">
        <i class="fa-solid fa-user-shield text-xs text-brand"></i> <span>Login Admin</span>
    </a>
@endsection
