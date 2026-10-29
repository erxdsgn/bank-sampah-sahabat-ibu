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
            $table->id('id_artikel'); // Diubah menjadi id() agar bertipe BigInt
            $table->unsignedBigInteger('id_admin')->index('fk_artikel_admin'); // Diubah ke unsignedBigInteger
            $table->string('judul');
            $table->text('konten');
            $table->string('gambar')->nullable();
            $table->date('tanggal_publish');
            $table->timestamps();
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
