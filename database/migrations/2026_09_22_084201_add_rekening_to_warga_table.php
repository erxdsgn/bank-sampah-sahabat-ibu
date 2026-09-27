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
        Schema::table('warga', function (Blueprint $table) {
            $table->string('nama_bank_ewallet', 50)->nullable()->after('saldo');
            // Contoh isi: 'BCA', 'BNI', 'GoPay', 'DANA'

            $table->string('nomor_rekening', 50)->nullable()->after('nama_bank_ewallet');
            // Nomor rekening atau nomor HP e-wallet

            $table->string('nama_pemilik_rekening', 100)->nullable()->after('nomor_rekening');
            // Nama yang tertera di rekening/e-wallet

            $table->enum('status_rekening', ['unverified', 'verified'])
                ->default('unverified')
                ->after('nama_pemilik_rekening');
        });
    }
};
