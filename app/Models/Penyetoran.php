<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penyetoran extends Model
{
    protected $table = 'penyetoran';

    protected $primaryKey = 'id_setoran';

    public $timestamps = false;

    protected $fillable = [
        'id_warga',
        'id_admin',
        'tanggal_setoran',
        'status',
        'total_berat',
        'total_nilai',
    ];

    protected $casts = [
        'tanggal_setoran' => 'date',
        'total_berat' => 'decimal:2',
        'total_nilai' => 'decimal:2',
    ];

    public function warga(): BelongsTo
    {
        return $this->belongsTo(
            Warga::class,
            'id_warga',
            'id_warga'
        );
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(
            Admin::class,
            'id_admin',
            'id_admin'
        );
    }

    public function detailSetoran(): HasMany
    {
        return $this->hasMany(
            DetailSetoran::class,
            'id_setoran',
            'id_setoran'
        );
    }

    public function mutasiSaldo(): HasMany
    {
        return $this->hasMany(
            MutasiSaldo::class,
            'id_setoran',
            'id_setoran'
        );
    }
}
