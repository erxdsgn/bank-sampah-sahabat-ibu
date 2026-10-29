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
        Schema::create('pencairan_saldo', function (Blueprint $table) {
            $table->id('id_pencairan');
            $table->unsignedBigInteger('id_warga')->index('fk_pencairan_warga');
            $table->unsignedBigInteger('id_admin')->index('fk_pencairan_admin');
            $table->date('tanggal_pencairan');
            $table->decimal('jumlah', 15, 2);
            $table->string('metode_transfer', 50);
            $table->string('status', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pencairan_saldo');
    }
};
