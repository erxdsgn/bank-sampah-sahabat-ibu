<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            // Menambahkan kolom kelompok (default NULL saat pendaftaran)
            $table->string('kelompok', 50)->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            $table->dropColumn('kelompok');
        });
    }
};
