<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $casts = [
        'saldo' => 'decimal:2',
        'tanggal_daftar' => 'date',
    ];

    public function penyetoran(): HasMany
    {
        return $this->hasMany(Penyetoran::class, 'id_warga', 'id_warga');
    }

    public function mutasiSaldo(): HasMany
    {
        return $this->hasMany(MutasiSaldo::class, 'id_warga', 'id_warga');
    }

    public function pencairanSaldo(): HasMany
    {
        return $this->hasMany(PencairanSaldo::class, 'id_warga', 'id_warga');
    }
}
