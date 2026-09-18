<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_keluar', function (Blueprint $table) {
            $table->increments('id_barang_keluar');
            $table->unsignedInteger('id_kategori');
            $table->unsignedInteger('id_admin');
            $table->date('tanggal');
            $table->decimal('berat_kg', 10, 2);
            $table->decimal('harga_jual_per_kg', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('pembeli');

            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('kategori_sampah')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_admin')
                ->references('id_admin')
                ->on('admin')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluar');
    }
};
