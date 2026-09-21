<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kas', function (Blueprint $table) {
            // Jejak sumber transaksi otomatis (null = input manual admin)
            $table->unsignedBigInteger('id_referensi')->nullable()->after('kategori_transaksi');
            $table->string('tipe_referensi', 50)->nullable()->after('id_referensi');
            // 'tipe_referensi' contoh isi: 'setoran', 'pencairan_saldo', 'barang_keluar'

            // Bukti transaksi (opsional, untuk pengeluaran operasional)
            $table->string('bukti_transaksi')->nullable()->after('keterangan');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('kas', function (Blueprint $table) {
            $table->dropColumn(['id_referensi', 'tipe_referensi', 'bukti_transaksi']);
            $table->dropTimestamps();
        });
    }
};
