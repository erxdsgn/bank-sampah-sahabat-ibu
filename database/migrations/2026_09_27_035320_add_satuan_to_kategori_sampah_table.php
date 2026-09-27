<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom satuan sudah ada di database,
        // jadi migration ini tidak perlu menambahkannya lagi.
    }

    public function down(): void
    {
        // Jangan hapus kolom satuan karena kolom tersebut
        // sudah menjadi bagian dari struktur tabel.
    }
};
