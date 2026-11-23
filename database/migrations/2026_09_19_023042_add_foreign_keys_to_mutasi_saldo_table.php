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
        Schema::table('mutasi_saldo', function (Blueprint $table) {
            $table->foreign(['id_setoran'], 'fk_mutasi_setoran')->references(['id_setoran'])->on('penyetoran')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['id_warga'], 'fk_mutasi_warga')->references(['id_warga'])->on('warga')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_saldo', function (Blueprint $table) {
            $table->dropForeign('fk_mutasi_setoran');
            $table->dropForeign('fk_mutasi_warga');
        });
    }
};
