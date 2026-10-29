<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Lepas Foreign Key dari masing-masing tabel relasi
        $this->dropForeignKeyIfExists('penyetoran', 'fk_setoran_warga');
        $this->dropForeignKeyIfExists('mutasi_saldo', 'fk_mutasi_warga');$this->dropForeignKeyIfExists('pencairan_saldo', 'fk_pencairan_warga');

        // 2. Ubah id_warga di tabel utama (warga) menjadi VARCHAR(20)
        Schema::table('warga', function (Blueprint $table) {$table->string('id_warga', 20)->change();
        });

        // 3. Ubah id_warga di tabel penyetoran
        if (Schema::hasTable('penyetoran') && Schema::hasColumn('penyetoran', 'id_warga')) {
            Schema::table('penyetoran', function (Blueprint $table) {$table->string('id_warga', 20)->change();
            });
        }

        // 4. Ubah id_warga di tabel mutasi_saldo
        if (Schema::hasTable('mutasi_saldo') && Schema::hasColumn('mutasi_saldo', 'id_warga')) {
            Schema::table('mutasi_saldo', function (Blueprint $table) {$table->string('id_warga', 20)->change();
            });
        }

        // 5. Ubah id_warga di tabel pencairan_saldo
        if (Schema::hasTable('pencairan_saldo') && Schema::hasColumn('pencairan_saldo', 'id_warga')) {
            Schema::table('pencairan_saldo', function (Blueprint $table) {$table->string('id_warga', 20)->change();
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('warga', function (Blueprint $table) {$table->integer('id_warga')->autoIncrement()->change();
        });

        if (Schema::hasTable('penyetoran') && Schema::hasColumn('penyetoran', 'id_warga')) {
            Schema::table('penyetoran', function (Blueprint $table) {$table->integer('id_warga')->change();
            });
        }

        if (Schema::hasTable('mutasi_saldo') && Schema::hasColumn('mutasi_saldo', 'id_warga')) {
            Schema::table('mutasi_saldo', function (Blueprint $table) {$table->integer('id_warga')->change();
            });
        }

        if (Schema::hasTable('pencairan_saldo') && Schema::hasColumn('pencairan_saldo', 'id_warga')) {
            Schema::table('pencairan_saldo', function (Blueprint $table) {$table->integer('id_warga')->change();
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Helper untuk melepas Foreign Key secara aman
     */
    private function dropForeignKeyIfExists(string $tableName, string$constraintName): void
    {
        if (Schema::hasTable($tableName)) {$foreignKeys = DB::select(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND CONSTRAINT_NAME = ?",
                [$tableName,$constraintName]
            );

            if (!empty($foreignKeys)) {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`");
            }
        }
    }
};
