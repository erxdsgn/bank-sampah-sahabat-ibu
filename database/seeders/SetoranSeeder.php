<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setoran;
use App\Models\DetailSetoran;
use App\Models\Warga;
use App\Models\Admin;
use App\Models\HargaSampah;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SetoranSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        $wargaIds = Warga::pluck('id_warga')->toArray();
        $adminIds = Admin::pluck('id_admin')->toArray();

        // Ambil harga terbaru per kategori
        $hargaList = HargaSampah::with('kategori')
            ->orderBy('tanggal_berlaku', 'desc')
            ->get()
            ->unique('id_kategori')
            ->values();

        if (empty($wargaIds) || empty($adminIds) || $hargaList->isEmpty()) {
            $this->command->error('Data Warga, Admin, atau Harga Sampah kosong!');
            return;
        }

        DB::transaction(function () use ($faker, $wargaIds, $adminIds, $hargaList) {
            for ($i = 1; $i <= 50; $i++) {
                $tanggalSetoran = $faker->dateTimeBetween('-6 months', 'now');
                $idWarga = $faker->randomElement($wargaIds);
                $idAdmin = $faker->randomElement($adminIds);

                // Buat Setoran Induk
                $setoran = Setoran::create([
                    'kode_transaksi'  => 'BS-' . $tanggalSetoran->format('Ymd') . '-' . strtoupper(Str::random(4)),
                    'id_warga'        => $idWarga,
                    'id_admin'        => $idAdmin,
                    'tanggal_setoran' => $tanggalSetoran->format('Y-m-d'),
                    'status'          => $faker->randomElement(['pending', 'diproses', 'selesai', 'selesai', 'selesai']),
                    'total_berat'     => 0,
                    'total_nilai'     => 0,
                    'foto_sampah'     => null,
                    'catatan_admin'   => $faker->optional()->sentence(),
                ]);

                $jumlahDetail = $faker->numberBetween(1, 3);
                $totalBeratGram = 0;
                $totalNilai = 0;

                for ($j = 0; $j < $jumlahDetail; $j++) {
                    $harga = $hargaList->random();
                    $satuan = strtolower($harga->kategori->satuan ?? 'kg');

                    // Tentukan jumlah berdasarkan satuan
                    if (in_array($satuan, ['kg', 'kilogram'])) {
                        $jumlah = $faker->randomFloat(2, 0.5, 10); // 0.5 - 10 kg
                        $beratGram = $jumlah * 1000;
                    } elseif (in_array($satuan, ['gram', 'gr', 'g'])) {
                        $jumlah = $faker->numberBetween(200, 5000); // 200 - 5000 gram
                        $beratGram = $jumlah;
                    } else {
                        // pcs, liter, dll
                        $jumlah = $faker->numberBetween(1, 20);
                        $beratGram = $jumlah * 100; // asumsi 100 gram per item
                    }

                    $hargaSatuan = (float) $harga->harga_satuan;
                    // harga_satuan biasanya per kg. Kalau satuan gram, harga per gram = harga/1000
                    $hargaPerGram = $hargaSatuan / 1000;

                    // Hitung subtotal
                    if (in_array($satuan, ['gram', 'gr', 'g'])) {
                        $subtotal = $jumlah * $hargaPerGram;
                    } else {
                        $subtotal = $jumlah * $hargaSatuan;
                    }

                    DetailSetoran::create([
                        // Sistem baru
                        'id_setoran'    => $setoran->id_setoran,
                        'id_kategori'   => $harga->id_kategori,
                        'jumlah'        => $jumlah,
                        'satuan'        => $satuan,
                        'subtotal'      => $subtotal,
                        // Kolom lama (kompatibilitas)
                        'berat_gram'    => $beratGram,
                        'harga_per_gram'=> $hargaPerGram,
                    ]);

                    $totalBeratGram += $beratGram;
                    $totalNilai += $subtotal;
                }

                // Update total setoran
                $setoran->update([
                    'total_berat' => $totalBeratGram, // dalam gram
                    'total_nilai' => $totalNilai,
                ]);

                // Jika status selesai, tambahkan saldo warga
                if ($setoran->status === 'selesai') {
                    Warga::where('id_warga', $idWarga)->increment('saldo', $totalNilai);
                }
            }
        });

        $this->command->info('✅ 50 Data Setoran & Detail Setoran berhasil dibuat!');
    }
}
