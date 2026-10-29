<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_sampah', function (Blueprint $table) {
            $table->id('id_harga');
            $table->unsignedBigInteger('id_kategori')->index('fk_harga_kategori');
            $table->unsignedBigInteger('id_admin')->index('fk_harga_admin');
            $table->decimal('harga_satuan', 15, 2); // Diubah ke harga_satuan
            $table->date('tanggal_berlaku');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_sampah');
    }
};
