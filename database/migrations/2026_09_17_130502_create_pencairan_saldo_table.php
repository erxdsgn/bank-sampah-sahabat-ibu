<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pencairan_saldo', function (Blueprint $table) {
            $table->increments('id_pencairan');
            $table->unsignedInteger('id_warga');
            $table->unsignedInteger('id_admin');
            $table->date('tanggal_pencairan');
            $table->decimal('jumlah', 15, 2);
            $table->string('metode_transfer', 50);
            $table->string('status', 50);

            $table->foreign('id_warga')
                ->references('id_warga')
                ->on('warga')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_admin')
                ->references('id_admin')
                ->on('admin')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencairan_saldo');
    }
};
