<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiItem extends Model
{
    protected $fillable = [
        'transaksi_id', 'nama_item', 'berat', 'harga_per_kg', 'subtotal',
    ];

    protected $casts = [
        'berat'        => 'float',
        'harga_per_kg' => 'float',
        'subtotal'     => 'float',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }
}