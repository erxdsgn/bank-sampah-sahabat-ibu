<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warga extends Model
{
    protected $table = 'warga';
    protected $primaryKey = 'id_warga';
    public $timestamps = false;
    protected $fillable = [
        'nik',
        'nama',
        'no_hp',
        'alamat',
        'jumlah_anggota_keluarga',
        'saldo',
        'tanggal_daftar',
    ];

    public function pencairan()
    {
        return $this->hasMany(Pencairan::class, 'id_warga', 'id_warga');
    }

    public function mutasiSaldo()
    {
        return $this->hasMany(MutasiSaldo::class, 'id_warga', 'id_warga');
    }
}
