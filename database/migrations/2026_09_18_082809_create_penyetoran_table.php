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
        Schema::create('penyetoran', function (Blueprint $table) {
            $table->integer('id_setoran', true);
            $table->string('kode_transaksi', 50)->nullable()->unique();
            $table->integer('id_warga')->index('fk_setoran_warga');
            $table->integer('id_admin')->nullable()->index('fk_setoran_admin');
            $table->date('tanggal_setoran')->useCurrent();
            $table->string('status', 50);
            $table->decimal('total_berat', 10)->nullable()->default(0); // Dihitung dalam gram
            $table->decimal('total_nilai', 15)->nullable()->default(0);
            $table->string('foto_sampah')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyetoran');
    }
};
