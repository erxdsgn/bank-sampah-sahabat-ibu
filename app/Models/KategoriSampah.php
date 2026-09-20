<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSampah extends Model
{
    protected $table = 'kategori_sampah';
    protected $primaryKey = 'id_kategori';
    public $timestamps = false;

    protected $fillable = [
        'id_induk',
        'nama_kategori',
        'satuan',
    ];

    public function parent() {
        return $this->belongsTo(KategoriSampah::class, 'id_induk', 'id_kategori');
    }

    public function children() {
        return $this->hasMany(KategoriSampah::class, 'id_induk', 'id_kategori');
    }

    public function hargaSampah() {
        return $this->hasMany(HargaSampah::class, 'id_kategori', 'id_kategori');
    }

    public function hargaAktif() {
        return $this->hasOne(HargaSampah::class, 'id_kategori', 'id_kategori')->latest('tanggal_berlaku');
    }
}

