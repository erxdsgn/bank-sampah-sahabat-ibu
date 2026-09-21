<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    protected $table = 'kas';
    protected $primaryKey = 'id_kas';
    public $timestamps = true; // sekarang kolomnya sudah ada

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected $appends = ['kategori', 'periode', 'is_otomatis'];

    public function getKategoriAttribute()
    {
        return $this->attributes['kategori_transaksi'] ?? null;
    }

    public function setKategoriAttribute($value)
    {
        $this->attributes['kategori_transaksi'] = $value;
    }

    public function getPeriodeAttribute()
    {
        return $this->tanggal ? $this->tanggal->translatedFormat('F Y') : null;
    }

    // Baris otomatis (punya referensi) tidak boleh diedit/dihapus manual
    public function getIsOtomatisAttribute(): bool
    {
        return !is_null($this->tipe_referensi);
    }

    public function scopePemasukan($query)
    {
        return $query->where('jenis', 'pemasukan');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('jenis', 'pengeluaran');
    }
}
