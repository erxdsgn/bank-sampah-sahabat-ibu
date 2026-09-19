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
        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->foreign(['id_induk'], 'fk_kategori_induk')->references(['id_kategori'])->on('kategori_sampah')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_sampah', function (Blueprint $table) {
            $table->dropForeign('fk_kategori_induk');
        });
    }
};
