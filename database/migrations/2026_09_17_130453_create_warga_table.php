<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warga', function (Blueprint $table) {
            $table->increments('id_warga');
            $table->string('nik', 20)->unique();
            $table->string('nama');
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->integer('jumlah_anggota_keluarga')->default(0);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->date('tanggal_daftar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warga');
    }
};
