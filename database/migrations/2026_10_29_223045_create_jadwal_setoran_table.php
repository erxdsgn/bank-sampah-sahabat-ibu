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
        Schema::create('jadwal_setoran', function (Blueprint $table) {
            $table->uuid('id_jadwal')->primary();
            $table->string('nama_agenda');
            $table->date('tanggal_setoran');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('wilayah')->nullable();
            $table->integer('kuota_maksimal')->default(50);
            $table->enum('status', ['draft', 'dipublikasi', 'selesai', 'dibatalkan'])->default('dipublikasi');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_setoran');
    }
};
