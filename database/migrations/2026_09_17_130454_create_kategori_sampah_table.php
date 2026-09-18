<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_sampah', function (Blueprint $table) {
            $table->increments('id_kategori');
            $table->unsignedInteger('id_induk')->nullable();
            $table->string('nama_kategori', 100);
            $table->string('satuan', 20)->default('kg');

            $table->foreign('id_induk')
                ->references('id_kategori')
                ->on('kategori_sampah')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_sampah');
    }
};
