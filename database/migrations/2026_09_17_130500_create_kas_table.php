<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas', function (Blueprint $table) {
            $table->increments('id_kas');
            $table->unsignedInteger('id_admin');
            $table->date('tanggal');
            $table->string('jenis', 20);
            $table->string('kategori_transaksi', 100);
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();

            $table->foreign('id_admin')
                ->references('id_admin')
                ->on('admin')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
