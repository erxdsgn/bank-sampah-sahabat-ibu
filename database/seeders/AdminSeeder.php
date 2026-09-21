<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Atau App\Models\Admin
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'nama'     => 'BankSampahSI',
            'username' => 'adminBSSI',
            'password' => Hash::make('121212'), // Ubah password sesuai keinginan
        ]);
    }
}
