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
        Schema::create('harga_sampah', function (Blueprint $table) {
            $table->integer('id_harga', true);
            $table->integer('id_kategori')->index('fk_harga_kategori');
            $table->integer('id_admin')->index('fk_harga_admin');
            $table->decimal('harga_per_kg', 15);
            $table->date('tanggal_berlaku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_sampah');
    }
};
