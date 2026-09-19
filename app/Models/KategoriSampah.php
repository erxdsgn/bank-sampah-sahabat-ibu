<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function induk(): BelongsTo
    {
        return $this->belongsTo(
            KategoriSampah::class,
            'id_induk',
            'id_kategori'
        );
    }

    public function subKategori(): HasMany
    {
        return $this->hasMany(
            KategoriSampah::class,
            'id_induk',
            'id_kategori'
        );
    }

    public function hargaSampah(): HasMany
    {
        return $this->hasMany(
            HargaSampah::class,
            'id_kategori',
            'id_kategori'
        );
    }

    public function detailSetoran(): HasMany
    {
        return $this->hasMany(
            DetailSetoran::class,
            'id_kategori',
            'id_kategori'
        );
    }

    public function barangKeluar(): HasMany
    {
        return $this->hasMany(
            BarangKeluar::class,
            'id_kategori',
            'id_kategori'
        );
    }
}
