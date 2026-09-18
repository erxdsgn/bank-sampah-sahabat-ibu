<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailSetoran extends Model
{
    protected $table = 'detail_setoran';

    protected $primaryKey = 'id_detail';

    public $timestamps = false;

    protected $fillable = [
        'id_setoran',
        'id_kategori',
        'berat_kg',
        'harga_per_kg',
        'subtotal',
    ];

    protected $casts = [
        'berat_kg' => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function penyetoran(): BelongsTo
    {
        return $this->belongsTo(
            Penyetoran::class,
            'id_setoran',
            'id_setoran'
        );
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(
            KategoriSampah::class,
            'id_kategori',
            'id_kategori'
        );
    }
}
