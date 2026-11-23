<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        for ($i = 1; $i <= 50; $i++) {
            User::create([
                'username' => 'warga' . $i,
                'email' => 'warga' . $i . '@sahabatibu.com',
                'password' => Hash::make('password'),
                'role' => 'warga',
                'notifikasi_aktif' => true, 
            ]);
        }
    }
}
