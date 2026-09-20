<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            // Tanpa foreign key ke tabel warga supaya aman apa pun nama tabelnya.
            $table->unsignedBigInteger('warga_id')->nullable()->index();
            $table->string('jenis', 20);            // setoran | pencairan | penjualan
            $table->date('tanggal');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('status', 20)->default('selesai'); // pending | selesai | dibatalkan
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'tanggal']);
        });

        Schema::create('transaksi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksis')->cascadeOnDelete();
            $table->string('nama_item', 100);
            $table->decimal('berat', 10, 2);          // kg
            $table->decimal('harga_per_kg', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_items');
        Schema::dropIfExists('transaksis');
    }
};