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
            $table->id('id_detail');
            $table->unsignedBigInteger('id_setoran')->index('fk_detail_setoran');
            $table->unsignedBigInteger('id_kategori')->index('fk_detail_kategori');
            $table->decimal('berat_gram', 10, 2);
            $table->decimal('harga_per_gram', 15, 2);
            $table->decimal('subtotal', 15, 2);
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
