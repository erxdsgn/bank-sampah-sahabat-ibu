<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_setoran', function (Blueprint $table) {
            $table->integer('poin')->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('detail_setoran', function (Blueprint $table) {
            $table->dropColumn('poin');
        });
    }
};
