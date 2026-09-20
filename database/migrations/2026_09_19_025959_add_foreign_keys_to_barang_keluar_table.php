<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->foreign(['id_admin'], 'fk_barang_keluar_admin')->references(['id_admin'])->on('admin')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_kategori'], 'fk_barang_keluar_kategori')->references(['id_kategori'])->on('kategori_sampah')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_keluar', function (Blueprint $table) {
            $table->dropForeign('fk_barang_keluar_admin');
            $table->dropForeign('fk_barang_keluar_kategori');
        });
    }
};
