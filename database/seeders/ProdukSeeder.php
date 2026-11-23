<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Admin;
use Faker\Factory as Faker;

class ProdukSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // Ambil semua ID admin yang ada
        $adminIds = Admin::pluck('id_admin')->toArray();

        if (empty($adminIds)) {
            $this->command->error('Tabel admin kosong! Jalankan AdminSeeder dulu.');
            return;
        }

        $namaProduk = [
            'Tas Daur Ulang', 'Dompet Kertas', 'Pot Bunga Kaleng', 'Hiasan Dinding Sedotan',
            'Vas Bunga Botol', 'Tempat Pensil Plastik', 'Brosur Daur Ulang', 'Kotak Tisu Kardus',
            'Mainan Edukasi Kaleng', 'Lampu Hias Sendok', 'Gantungan Kunci Kain', 'Topi Anyaman',
            'Sandal Kulit', 'Tas Belanja Kain', 'Celengan Botol', 'Bingkai Foto Koran',
            'Kipas Tangan Daur Ulang', 'Tempat Sampah Mini', 'Pouch Serut', 'Aksesoris Manik'
        ];

        for ($i = 1; $i <= 50; $i++) {
            Product::create([
                'id_admin' => $faker->randomElement($adminIds),
                'nama_produk' => $faker->randomElement($namaProduk) . ' ' . $faker->randomElement(['Keren', 'Unik', 'Eksklusif']),
                'deskripsi' => $faker->sentence(10) . ' Cocok untuk hadiah maupun koleksi.',
                'harga' => $faker->numberBetween(5000, 150000),
                'stok' => $faker->numberBetween(0, 100),
                'foto' => 'https://placehold.co/600x400/png?text=Produk+' . $i,
                'tanggal_upload' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'), // <-- TAMBAHKAN INI
            ]);
        }
    }
}
