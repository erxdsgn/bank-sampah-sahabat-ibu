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
        Schema::table('harga_sampah', function (Blueprint $table) {
            $table->renameColumn('harga_per_gram', 'harga_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harga_sampah', function (Blueprint $table) {
            $table->renameColumn('harga_satuan', 'harga_per_gram');
        });
    }
};
