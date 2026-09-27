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

    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'id_induk',
        'nama_kategori',
        'satuan',
    ];

    protected $casts = [
        'id_induk' => 'integer',
        'id_kategori' => 'integer',
    ];

    /**
     * Kategori induk.
     */
    public function induk(): BelongsTo
    {
        return $this->belongsTo(
            KategoriSampah::class,
            'id_induk',
            'id_kategori'
        );
    }

    /**
     * Sub-kategori.
     */
    public function anak(): HasMany
    {
        return $this->hasMany(
            KategoriSampah::class,
            'id_induk',
            'id_kategori'
        );
    }

    /**
     * Semua riwayat harga.
     */
    public function hargaSampah(): HasMany
    {
        return $this->hasMany(
            HargaSampah::class,
            'id_kategori',
            'id_kategori'
        );
    }

    /**
     * Harga terbaru berdasarkan tanggal berlaku.
     */
    public function hargaTerbaru(): HasOne
    {
        return $this->hasOne(
            HargaSampah::class,
            'id_kategori',
            'id_kategori'
        )->ofMany([
            'tanggal_berlaku' => 'max',
            'id_harga' => 'max',
        ]);
    }

    /**
     * Scope kategori anak.
     */
    public function scopeHanyaAnak($query)
    {
        return $query->whereNotNull('id_induk');
    }

    /**
     * Nama lengkap kategori.
     *
     * Contoh:
     * Plastik - Botol Plastik
     */
    public function getNamaLengkapAttribute(): string
    {
        if ($this->induk) {
            return $this->induk->nama_kategori
                . ' - '
                . $this->nama_kategori;
        }

        return $this->nama_kategori;
    }

    /**
     * Normalisasi satuan kategori.
     */
    public function getSatuanNormalAttribute(): string
    {
        $satuan = strtolower(
            trim(
                (string) $this->satuan
            )
        );

        return match ($satuan) {
            'g',
            'gram' =>
                'gram',

            'kg',
            'kilogram' =>
                'kg',

            'pcs',
            'piece',
            'pieces' =>
                'pcs',

            'l',
            'liter',
            'litre' =>
                'liter',

            default =>
                'gram',
        };
    }

    /**
     * Simbol satuan.
     */
    public function getSimbolSatuanAttribute(): string
    {
        return match (
            $this->satuan_normal
        ) {
            'gram' =>
                'gram',

            'kg' =>
                'kg',

            'pcs' =>
                'pcs',

            'liter' =>
                'L',

            default =>
                'gram',
        };
    }

    /**
     * Ambil harga terbaru.
     */
    public function getHargaSatuanAktualAttribute(): float
    {
        return (float) (
            $this->hargaTerbaru
                ->harga_satuan
                ?? 0
        );
    }

    /**
     * Hitung total harga.
     */
    public function hitungTotalHarga(
        float $jumlah
    ): float {
        return round(
            $jumlah *
            $this->harga_satuan_aktual,
            2
        );
    }

    /**
     * Cari kategori berdasarkan nama.
     */
    public static function cariBerdasarkanNama(
        string $nama
    ): ?self {
        $namaClean =
            mb_strtolower(
                trim($nama)
            );

        $semua = self::with([
            'induk',
            'hargaTerbaru',
        ])->get();

        return $semua->first(
            fn ($k) =>
                mb_strtolower(
                    $k->nama_lengkap
                ) === $namaClean
        )
        ??
        $semua->first(
            fn ($k) =>
                mb_strtolower(
                    $k->nama_kategori
                ) === $namaClean
        );
    }
}
