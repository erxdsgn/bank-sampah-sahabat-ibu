<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ArtikelEdukasi;
use App\Models\Admin;
use Faker\Factory as Faker;

class ArtikelSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        $adminIds = Admin::pluck('id_admin')->toArray();
        $idAdminAcak = empty($adminIds) ? 1 : $faker->randomElement($adminIds);

       
        $jenisValid = ['edukasi', 'artikel', 'acara'];

        $judulArtikel = [
            'Pentingnya Memilah Sampah dari Rumah', 'Cara Mengolah Minyak Jelantah Menjadi Sabun',
            'Laporan Keuangan Bank Sampah Bulan Ini', 'Panduan Membuat Kompos dari Sampah Organik',
            'Jadwal Pengambilan Sampah Terbaru', 'Kisah Sukses Warga Mendapat Cuan dari Sampah',
            'Mengenal Jenis-Jenis Plastik dan Bahayanya', 'Kreativitas Daur Ulang Kardus Bekas',
            'Lomba Daur Ulang Antar RT', 'Pentingnya Mengurangi Penggunaan Kantong Plastik',
            'Cara Membuat Sabun dari Minyak Jelantah', 'Edukasi Pemilahan Sampah untuk Anak',
            'Workshop Kompos Skala Rumah Tangga', 'Pelatihan Daur Ulang Plastik Menjadi Kerajinan',
            'Pentingnya Bank Sampah untuk Lingkungan',
        ];

        for ($i = 1; $i <= 50; $i++) {
            $isDraft = $faker->boolean(30);

            ArtikelEdukasi::create([
                'id_admin' => $idAdminAcak,
                'judul' => $faker->randomElement($judulArtikel) . ' (Bagian ' . $i . ')',
                'jenis' => $faker->randomElement($jenisValid), // <-- nilai valid
                'konten' => $faker->paragraph(5),
                'gambar' => 'https://via.placeholder.com/800x400.png?text=Artikel+' . $i,
                'tanggal_publish' => $isDraft ? null : $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            ]);
        }

        $this->command->info('✅ 50 Data Artikel berhasil dibuat!');
    }
}
