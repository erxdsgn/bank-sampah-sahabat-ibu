<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['username' => 'adminBSSI'],
            [
                'nama'     => 'Admin Bank Sampah Sahabat Ibu',
                'password' => '121212', // JANGAN pakai Hash::make() — cast 'hashed' yang urus
                'alamat'   => null,
            ]
        );
    }
}
