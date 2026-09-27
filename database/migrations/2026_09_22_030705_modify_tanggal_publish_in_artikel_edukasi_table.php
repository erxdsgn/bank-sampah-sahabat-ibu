<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikel_edukasi', function (Blueprint $table) {
            $table->date('tanggal_publish')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('artikel_edukasi', function (Blueprint $table) {
            $table->date('tanggal_publish')->nullable(false)->change();
        });
    }
};
