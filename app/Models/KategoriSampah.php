<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriSampah extends Model
{
    use HasFactory;

    protected $table = 'kategori_sampah';
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'id_induk',
        'nama_kategori',
        'satuan',
    ];

    /**
     * Relasi ke kategori induk (parent category).
     */
    public function induk()
    {
        return $this->belongsTo(KategoriSampah::class, 'id_induk', 'id_kategori');
    }

    /**
     * Relasi ke sub-kategori (child category).
     */
    public function anak()
    {
        return $this->hasMany(KategoriSampah::class, 'id_induk', 'id_kategori');
    }

    /**
     * Relasi ke seluruh riwayat harga sampah.
     */
    public function hargaSampah()
    {
        return $this->hasMany(HargaSampah::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Relasi untuk mengambil harga yang paling baru/aktif.
     */
    public function hargaTerbaru()
    {
        return $this->hasOne(HargaSampah::class, 'id_kategori', 'id_kategori')
                    ->latest('tanggal_berlaku');
    }

    /**
     * ACCESSOR: Menghitung poin secara otomatis dari harga satuan terbaru.
     * Contoh rasio: Setiap Rp100 dari harga satuan = 1 Poin.
     * (Pembagi '100' di bawah bisa Anda ubah sesuai kebutuhan bank sampah Anda).
     */
    public function getPoinPerSatuanAttribute()
    {
        $hargaTerbaru = $this->hargaTerbaru ? $this->hargaTerbaru->harga_satuan : 0;

        return floor($hargaTerbaru / 100);
    }
}
