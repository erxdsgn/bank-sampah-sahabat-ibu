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
        Schema::table('detail_setoran', function (Blueprint $table) {
            $table->foreign(['id_kategori'], 'fk_detail_kategori')->references(['id_kategori'])->on('kategori_sampah')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_setoran'], 'fk_detail_setoran')->references(['id_setoran'])->on('penyetoran')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_setoran', function (Blueprint $table) {
            $table->dropForeign('fk_detail_kategori');
            $table->dropForeign('fk_detail_setoran');
        });
    }
};
