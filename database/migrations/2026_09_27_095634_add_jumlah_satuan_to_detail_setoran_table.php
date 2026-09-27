<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_setoran', function (Blueprint $table) {
            $table->decimal('jumlah', 15, 2)
                ->nullable()
                ->after('id_kategori');

            $table->string('satuan', 20)
                ->nullable()
                ->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('detail_setoran', function (Blueprint $table) {
            $table->dropColumn(['jumlah', 'satuan']);
        });
    }
};
