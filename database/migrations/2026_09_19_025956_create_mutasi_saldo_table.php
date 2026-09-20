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
        Schema::create('mutasi_saldo', function (Blueprint $table) {
            $table->integer('id_mutasi', true);
            $table->integer('id_warga')->index('fk_mutasi_warga');
            $table->integer('id_setoran')->nullable()->index('fk_mutasi_setoran');
            $table->string('jenis_mutasi', 20);
            $table->decimal('jumlah', 15);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldo');
    }
};
