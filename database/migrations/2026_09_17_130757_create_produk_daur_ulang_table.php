<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_daur_ulang', function (Blueprint $table) {
            $table->increments('id_produk');
            $table->unsignedInteger('id_admin');
            $table->string('nama_produk');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 15, 2);
            $table->string('foto')->nullable();
            $table->string('nomor_wa', 20)->nullable();
            $table->date('tanggal_upload');

            $table->foreign('id_admin')
                ->references('id_admin')
                ->on('admin')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_daur_ulang');
    }
};
