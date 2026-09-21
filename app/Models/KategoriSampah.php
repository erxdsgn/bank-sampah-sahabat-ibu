<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KategoriSampah extends Model
{
    use HasFactory;

    protected $table = 'kategori_sampah';

    // Primary key custom, bukan default 'id'
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'id_induk',
        'nama_kategori',
        'satuan',
    ];

    /**
     * Kategori induk dari baris ini (null kalau ini kategori induk paling atas).
     */
    public function induk(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'id_induk', 'id_kategori');
    }

    /**
     * Daftar sub-kategori (anak) dari kategori ini.
     */
    public function anak(): HasMany
    {
        return $this->hasMany(KategoriSampah::class, 'id_induk', 'id_kategori');
    }

    /**
     * Relasi ke semua riwayat harga sampah.
     */
    public function hargaSampah(): HasMany
    {
        return $this->hasMany(HargaSampah::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Relasi untuk mengambil 1 harga sampah terbaru.
     */
    public function hargaTerbaru(): HasOne
    {
        return $this->hasOne(HargaSampah::class, 'id_kategori', 'id_kategori')
                    ->latestOfMany('updated_at');
    }

    /**
     * Scope: hanya kategori anak (yang punya id_induk terisi).
     * Digunakan untuk dropdown form setoran/verifikasi.
     */
    public function scopeHanyaAnak($query)
    {
        return $query->whereNotNull('id_induk');
    }

    /**
     * Nama lengkap untuk ditampilkan, misalnya "Plastik - Botol Plastik Bening (PET)".
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->induk
            ? "{$this->induk->nama_kategori} - {$this->nama_kategori}"
            : $this->nama_kategori;
    }

    /**
     * Helper: Cari kategori berdasarkan teks/nama yang dikirim dari form
     * (Mendukung nama lengkap "Plastik - Botol" maupun nama tunggal "Botol")
     */
    public static function cariBerdasarkanNama(string $nama): ?self
    {
        $namaClean = mb_strtolower(trim($nama));
        $semua = self::with(['induk', 'hargaTerbaru'])->get();

        return $semua->first(fn ($k) => mb_strtolower($k->nama_lengkap) === $namaClean)
            ?? $semua->first(fn ($k) => mb_strtolower($k->nama_kategori) === $namaClean);
    }
}
