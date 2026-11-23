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
        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->integer('id_barang_keluar', true);
            $table->integer('id_kategori')->index('fk_barang_keluar_kategori');
            $table->integer('id_admin')->index('fk_barang_keluar_admin');
            $table->date('tanggal');
            $table->decimal('berat_kg', 10);
            $table->decimal('harga_jual_per_kg', 15);
            $table->decimal('total', 15);
            $table->string('pembeli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_keluar');
    }
};
