<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_sampah', function (Blueprint $table) {
            $table->id('id_kategori');
            $table->unsignedBigInteger('id_induk')->nullable()->index('fk_kategori_induk');
            $table->string('nama_kategori', 100)->unique();
            $table->enum('satuan', ['gram', 'kg', 'pcs', 'liter', 'unit', 'set']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_sampah');
    }
};
