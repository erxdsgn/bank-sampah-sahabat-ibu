<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaduans', function (Blueprint $table) {
            $table->id('id_pengaduan');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('id_warga', 30)->nullable()->index();
            $table->string('kategori', 40);
            $table->string('judul', 120);
            $table->text('deskripsi');
            $table->string('foto_bukti')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu, diproses, selesai, ditolak
            $table->text('tanggapan_admin')->nullable();
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->timestamp('ditanggapi_at')->nullable();
            $table->timestamps();

            $table->foreign('id_warga')
                ->references('id_warga')
                ->on('warga')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
    }
};
