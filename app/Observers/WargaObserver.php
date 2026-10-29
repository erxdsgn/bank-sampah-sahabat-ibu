<?php

namespace App\Observers;

use App\Models\Warga;

class WargaObserver
{
    public function creating(Warga $warga): void
    {
        if (empty($warga->id_warga)) {
            // Ambil data warga terakhir
            $lastWarga = Warga::orderBy('id_warga', 'desc')->first();

            if ($lastWarga) {
                // Ambil angka setelah awalan 'BSSI' (4 karakter)
                $lastNumber = (int) substr($lastWarga->id_warga, 4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            // Hasil: BSSI001, BSSI002, dst.
            $warga->id_warga = 'BSSI' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
    }
}
