<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailSetoran extends Model
{
    protected $table = 'detail_setoran';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_setoran',
        'id_kategori',
        'berat_gram',
        'harga_per_gram',
        'subtotal',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class, 'id_kategori', 'id_kategori');
    }

    public function setoran()
    {
        return $this->belongsTo(Setoran::class, 'id_setoran', 'id_setoran');
    }
}
