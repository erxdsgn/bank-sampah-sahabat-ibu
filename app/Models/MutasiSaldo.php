<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiSaldo extends Model
{
    protected $table = 'mutasi_saldo';

    protected $primaryKey = 'id_mutasi';

    public $timestamps = false;

    protected $fillable = [
        'id_warga',
        'id_setoran',
        'jenis_mutasi',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function warga(): BelongsTo
    {
        return $this->belongsTo(
            Warga::class,
            'id_warga',
            'id_warga'
        );
    }

    public function penyetoran(): BelongsTo
    {
        return $this->belongsTo(
            Penyetoran::class,
            'id_setoran',
            'id_setoran'
        );
    }
}
