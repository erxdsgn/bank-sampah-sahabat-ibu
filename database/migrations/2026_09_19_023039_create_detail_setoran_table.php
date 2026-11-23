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
            $table->decimal('berat_kg', 10);
            $table->decimal('harga_per_kg', 15);
            $table->decimal('subtotal', 15);
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
