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
        Schema::create('detail_setoran', function (Blueprint $table) {
            $table->integer('id_detail', true);
            $table->integer('id_setoran')->index('fk_detail_setoran');
            $table->integer('id_kategori')->index('fk_detail_kategori');
            $table->decimal('berat_gram', 10);      // Diubah dari berat_kg
            $table->decimal('harga_per_gram', 15);  // Diubah dari harga_per_kg
            $table->decimal('subtotal', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_setoran');
    }
};
