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

        /*
         * Sistem baru.
         */
        'jumlah',
        'satuan',

        /*
         * Kolom lama untuk kompatibilitas.
         */
        'berat_gram',
        'harga_per_gram',

        'subtotal',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'berat_gram' => 'decimal:2',
        'harga_per_gram' => 'decimal:2',
        'subtotal' => 'decimal:2',
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
     * Data lama fallback ke berat_gram.
     */
    public function getJumlahAktualAttribute()
    {
        if ($this->jumlah !== null) {
            return (float) $this->jumlah;
        }

        return (float) (
            $this->berat_gram ?? 0
        );
    }

    /**
     * Helper untuk mendapatkan satuan.
     *
     * Data baru menggunakan satuan.
     * Data lama dianggap gram.
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
