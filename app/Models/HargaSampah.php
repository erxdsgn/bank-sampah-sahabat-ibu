<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaSampah extends Model
{
    protected $table = 'harga_sampah';

    protected $primaryKey = 'id_harga';

    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'id_admin',
        'harga_per_kg',
        'tanggal_berlaku',
    ];

    protected $casts = [
        'harga_per_kg' => 'decimal:2',
        'tanggal_berlaku' => 'date',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(
            KategoriSampah::class,
            'id_kategori',
            'id_kategori'
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
}
