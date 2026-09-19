<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyetoran', function (Blueprint $table) {
            $table->increments('id_setoran');
            $table->unsignedInteger('id_warga');
            $table->unsignedInteger('id_admin')->nullable();
            $table->date('tanggal_setoran');
            $table->string('status', 50);
            $table->decimal('total_berat', 10, 2)->default(0);
            $table->decimal('total_nilai', 15, 2)->default(0);

            $table->foreign('id_warga')
                ->references('id_warga')
                ->on('warga')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_admin')
                ->references('id_admin')
                ->on('admin')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyetoran');
    }
};
