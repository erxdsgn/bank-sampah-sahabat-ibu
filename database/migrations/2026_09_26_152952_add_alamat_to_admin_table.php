<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            // Gunakan text() untuk alamat panjang, atau string('alamat', 255) untuk alamat pendek
            // nullable() membenarkan ruangan ini dibiarkan kosong jika tidak wajib
            $table->text('alamat')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn('alamat');
        });
    }
};
