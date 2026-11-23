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
        'jumlah',
        'satuan',
        'berat',
        'harga_satuan',
        'subtotal',
        'poin', // <-- DITAMBAHKAN: Agar poin bisa disimpan
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'berat' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'poin' => 'integer', // <-- DITAMBAHKAN: Casting poin ke integer
    ];

    /**
     * Kategori sampah.
     */
    public function kategori()
    {
        return $this->belongsTo(
            KategoriSampah::class,
            'id_kategori',
            'id_kategori'
        );
    }

    /**
     * Setoran.
     */
    public function setoran()
    {
        return $this->belongsTo(
            Setoran::class,
            'id_setoran',
            'id_setoran'
        );
    }

    /**
     * Helper untuk mendapatkan jumlah.
     *
     * Data baru menggunakan jumlah.
     */
    public function getJumlahAktualAttribute()
    {
        if ($this->jumlah !== null) {
            return (float) $this->jumlah;
        }

        return (float) (
            $this->berat ?? 0
        );
    }

    /**
     * Helper untuk mendapatkan satuan.
     *
     * Data baru menggunakan satuan.
     */
    public function getSatuanAktualAttribute(): string
    {
        if (! empty($this->satuan)) {
            return strtolower(
                trim($this->satuan)
            );
        }

        return 'gram';
    }
}
