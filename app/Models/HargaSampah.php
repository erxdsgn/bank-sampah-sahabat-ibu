<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'tanggal_berlaku' => 'date',
    ];

    public function kategori() {
        return $this->belongsTo(KategoriSampah::class, 'id_kategori', 'id_kategori');
    }

    public function admin() {
        return $this->belongsTo(\App\Models\User::class, 'id_admin', 'id');
    }
}
