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
        Schema::create('kas', function (Blueprint $table) {
            $table->integer('id_kas', true);
            $table->integer('id_admin')->index('fk_kas_admin');
            $table->date('tanggal');
            $table->string('jenis', 20);
            $table->string('kategori_transaksi', 100);
            $table->decimal('jumlah', 15);
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
