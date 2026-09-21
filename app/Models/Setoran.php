<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setoran extends Model
{
    protected $table = 'penyetoran';
    protected $primaryKey = 'id_setoran';

    protected $fillable = [
        'kode_transaksi',
        'id_warga',
        'id_admin',
        'tanggal_setoran',
        'status',
        'total_berat',
        'total_nilai',
        'foto_sampah',
        'catatan_admin',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga', 'id_warga');
    }

    public function details()
    {
        return $this->hasMany(DetailSetoran::class, 'id_setoran', 'id_setoran');
    }
}
