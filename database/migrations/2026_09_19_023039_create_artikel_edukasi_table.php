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
        Schema::create('artikel_edukasi', function (Blueprint $table) {
            $table->integer('id_artikel', true);
            $table->integer('id_admin')->index('fk_artikel_admin');
            $table->string('judul');
            $table->text('konten');
            $table->string('gambar')->nullable();
            $table->date('tanggal_publish');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel_edukasi');
    }
};
