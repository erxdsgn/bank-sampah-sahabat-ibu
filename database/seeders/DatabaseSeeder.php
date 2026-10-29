<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Buat akun utama di tabel users
            $user = User::create([
                'username' => 'adminBSSI',
                'email'    => 'admin@banksampah.com',
                'password' => Hash::make('password123'), // Password yang di-hash
                'role'     => 'admin',
            ]);

            // 2. Buat profil admin yang terhubung ke id_user
            Admin::create([
                'user_id' => $user->id,
                'nama'    => 'Administrator Pusat',
                'no_hp'   => '081234567890',
                'alamat'  => 'Jl. Bank Sampah No. 1',
            ]);
        });
    }
}
