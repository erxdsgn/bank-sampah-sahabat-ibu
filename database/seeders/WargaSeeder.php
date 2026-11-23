<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warga;
use Faker\Factory as Faker;

class WargaSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        $kelompokList = ['RT 01 / RW 05', 'RT 02 / RW 05', 'RT 03 / RW 06', 'RT 04 / RW 06', 'RT 05 / RW 07'];
        $bankList = ['BCA', 'BRI', 'Mandiri', 'BNI', 'DANA', 'OVO', 'GoPay', 'ShopeePay'];

        for ($i = 1; $i <= 50; $i++) {
            Warga::create([
                'id_warga' => 'BSSI' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'id_user' => $i, // Menghubungkan ke ID user yang baru dibuat (1-50)
                'nik' => $faker->nik(),
                'nama' => $faker->name(),
                'no_hp' => $faker->phoneNumber(),
                'alamat' => $faker->address(),
                'kelompok' => $faker->randomElement($kelompokList),
                'kelompok_updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'jumlah_anggota_keluarga' => $faker->numberBetween(1, 6),
                'saldo' => $faker->randomFloat(2, 0, 500000),
                'nama_bank_ewallet' => $faker->randomElement($bankList),
                'nomor_rekening' => $faker->bankAccountNumber(),
                'nama_pemilik_rekening' => $faker->name(),
                'tanggal_daftar' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            ]);
        }
    }
}
