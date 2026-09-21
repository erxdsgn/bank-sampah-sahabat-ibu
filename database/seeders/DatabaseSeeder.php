<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat akun Admin default untuk login
        Admin::create([
            'nama'     => 'Admin Bank Sampah Sahabat Ibu',
            'username' => 'adminBSSI',
            'password' => bcrypt('121212'), // Password untuk login
        ]);
    }
}

