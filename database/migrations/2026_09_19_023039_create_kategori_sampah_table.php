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
        Schema::create('kategori_sampah', function (Blueprint $table) {
            $table->integer('id_kategori', true);
            $table->integer('id_induk')->nullable()->index('fk_kategori_induk');
            $table->string('nama_kategori', 100);
            $table->string('satuan', 20)->default('kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_sampah');
    }
};
