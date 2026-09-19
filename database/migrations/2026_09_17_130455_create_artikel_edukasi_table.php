<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_edukasi', function (Blueprint $table) {
            $table->increments('id_artikel');
            $table->unsignedInteger('id_admin');
            $table->string('judul');
            $table->text('konten');
            $table->string('gambar')->nullable();
            $table->date('tanggal_publish');

            $table->foreign('id_admin')
                ->references('id_admin')
                ->on('admin')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel_edukasi');
    }
};
