<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';

    protected $primaryKey = 'id_barang_keluar';

    protected $fillable = [
        'id_kategori',
        'id_admin',
        'tanggal',
        'berat_gram',
        'harga_jual_per_gram',
        'total',
        'pembeli',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'berat_gram' => 'decimal:2',
        'harga_jual_per_gram' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function kategori()
    {
        return $this->belongsTo(
            KategoriSampah::class,
            'id_kategori',
            'id_kategori'
        );
    }

    public function admin()
    {
        return $this->belongsTo(
            Admin::class,
            'id_admin',
            'id_admin'
        );
    }
}
