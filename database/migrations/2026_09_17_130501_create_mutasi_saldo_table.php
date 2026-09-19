<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_saldo', function (Blueprint $table) {
            $table->increments('id_mutasi');
            $table->unsignedInteger('id_warga');
            $table->unsignedInteger('id_setoran')->nullable();
            $table->string('jenis_mutasi', 20);
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();

            $table->foreign('id_warga')
                ->references('id_warga')
                ->on('warga')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_setoran')
                ->references('id_setoran')
                ->on('penyetoran')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_saldo');
    }
};
