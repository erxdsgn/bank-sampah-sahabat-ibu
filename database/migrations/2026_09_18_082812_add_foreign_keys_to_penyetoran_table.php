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
        Schema::table('penyetoran', function (Blueprint $table) {
            $table->foreign(['id_admin'], 'fk_setoran_admin')->references(['id_admin'])->on('admin')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['id_warga'], 'fk_setoran_warga')->references(['id_warga'])->on('warga')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyetoran', function (Blueprint $table) {
            $table->dropForeign('fk_setoran_admin');
            $table->dropForeign('fk_setoran_warga');
        });
    }
};
