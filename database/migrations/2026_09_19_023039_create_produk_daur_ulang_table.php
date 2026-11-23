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
        Schema::create('produk_daur_ulang', function (Blueprint $table) {
            $table->integer('id_produk', true);
            $table->integer('id_admin')->index('fk_produk_admin');
            $table->string('nama_produk');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 15);
            $table->string('foto')->nullable();
            $table->string('nomor_wa', 20)->nullable();
            $table->date('tanggal_upload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_daur_ulang');
    }
};
