<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    protected $table = 'admin';

    protected $primaryKey = 'id_admin';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'username',
        'password',
    ];

    public function artikelEdukasi(): HasMany
    {
        return $this->hasMany(ArtikelEdukasi::class, 'id_admin', 'id_admin');
    }

    public function hargaSampah(): HasMany
    {
        return $this->hasMany(HargaSampah::class, 'id_admin', 'id_admin');
    }

    public function penyetoran(): HasMany
    {
        return $this->hasMany(Penyetoran::class, 'id_admin', 'id_admin');
    }

    public function barangKeluar(): HasMany
    {
        return $this->hasMany(BarangKeluar::class, 'id_admin', 'id_admin');
    }

    public function kas(): HasMany
    {
        return $this->hasMany(Kas::class, 'id_admin', 'id_admin');
    }

    public function pencairanSaldo(): HasMany
    {
        return $this->hasMany(PencairanSaldo::class, 'id_admin', 'id_admin');
    }

    public function produkDaurUlang(): HasMany
    {
        return $this->hasMany(ProdukDaurUlang::class, 'id_admin', 'id_admin');
    }
}
