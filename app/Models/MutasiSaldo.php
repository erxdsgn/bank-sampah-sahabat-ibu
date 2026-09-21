<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiSaldo extends Model
{
    protected $table = 'mutasi_saldo';
    protected $primaryKey = 'id_mutasi';
    public $incrementing = true;
    //

    protected $fillable = [
        'id_warga',
        'id_setoran',
        'jenis_mutasi',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga', 'id_warga');
    }
    public function setoran()
    {
        return $this->belongsTo(Setoran::class, 'id_setoran', 'id_setoran');
    }
}
