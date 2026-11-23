<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_setoran', function (Blueprint $table) {
            $table->renameColumn('berat_gram', 'berat');
            $table->renameColumn('harga_per_gram', 'harga_satuan');
        });
    }

    public function down(): void
    {
        Schema::table('detail_setoran', function (Blueprint $table) {
            $table->renameColumn('berat', 'berat_gram');
            $table->renameColumn('harga_satuan', 'harga_per_gram');
        });
    }
};
