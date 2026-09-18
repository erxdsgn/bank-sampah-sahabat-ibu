<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_setoran', function (Blueprint $table) {
            $table->increments('id_detail');
            $table->unsignedInteger('id_setoran');
            $table->unsignedInteger('id_kategori');
            $table->decimal('berat_kg', 10, 2);
            $table->decimal('harga_per_kg', 15, 2);
            $table->decimal('subtotal', 15, 2);

            $table->foreign('id_setoran')
                ->references('id_setoran')
                ->on('penyetoran')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('kategori_sampah')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_setoran');
    }
};
