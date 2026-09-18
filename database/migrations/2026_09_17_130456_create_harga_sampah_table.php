<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harga_sampah', function (Blueprint $table) {
            $table->increments('id_harga');
            $table->unsignedInteger('id_kategori');
            $table->unsignedInteger('id_admin');
            $table->decimal('harga_per_kg', 15, 2);
            $table->date('tanggal_berlaku');

            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('kategori_sampah')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_admin')
                ->references('id_admin')
                ->on('admin')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_sampah');
    }
};
