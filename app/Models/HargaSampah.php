<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaSampah extends Model
{
    use HasFactory;

    protected $table = 'harga_sampah';
    protected $primaryKey = 'id_harga';

    protected $fillable = [
        'id_kategori',
        'id_admin',
        'harga_satuan', // Diubah dari harga_per_gram
        'tanggal_berlaku',
    ];

    protected $casts = [
        'tanggal_berlaku' => 'date',
        'harga_satuan'    => 'decimal:2', // Diubah dari harga_per_gram
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriSampah::class, 'id_kategori', 'id_kategori');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
