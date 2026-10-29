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
            $table->id('id_barang_keluar');
            $table->unsignedBigInteger('id_admin')->index('fk_barang_keluar_admin');
            $table->unsignedBigInteger('id_kategori')->index('fk_barang_keluar_kategori');
            $table->date('tanggal');
            $table->decimal('berat_gram', 10, 2);
            $table->decimal('harga_jual_per_gram', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('pembeli');
            $table->timestamps();
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
