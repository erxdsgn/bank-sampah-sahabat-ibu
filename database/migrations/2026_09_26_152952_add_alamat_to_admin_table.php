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
        // Cek dulu apakah kolom 'alamat' belum ada sebelum menambahkannya
        if (!Schema::hasColumn('admin', 'alamat')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->text('alamat')->nullable()->after('nama');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('admin', 'alamat')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->dropColumn('alamat');
            });
        }
    }
};
