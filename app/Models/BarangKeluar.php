<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';

    protected $primaryKey = 'id_barang_keluar';

    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'id_admin',
        'tanggal',
        'berat_kg',
        'harga_jual_per_kg',
        'total',
        'pembeli',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'berat_kg' => 'decimal:2',
        'harga_jual_per_kg' => 'decimal:2',
        'total' => 'decimal:2',
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
