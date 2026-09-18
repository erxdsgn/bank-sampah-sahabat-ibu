<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdukDaurUlang extends Model
{
    protected $table = 'produk_daur_ulang';

    protected $primaryKey = 'id_produk';

    public $timestamps = false;

    protected $fillable = [
        'id_admin',
        'nama_produk',
        'deskripsi',
        'harga',
        'foto',
        'nomor_wa',
        'tanggal_upload',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'tanggal_upload' => 'date',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(
            Admin::class,
            'id_admin',
            'id_admin'
        );
    }
}
