<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Transaksi extends Model
{
    /**
     * Kandidat nama kolom "nama warga". Kolom yang dipakai dideteksi otomatis
     * dari tabel warga, jadi tidak perlu diatur manual.
     */
    private const KANDIDAT_KOLOM_NAMA = ['nama', 'nama_lengkap', 'nama_warga', 'name'];

    private static ?string $kolomNamaCache = null;

    public const JENIS = [
        'setoran'   => 'Setoran sampah',
        'pencairan' => 'Pencairan saldo',
        'penjualan' => 'Penjualan ke pengepul',
    ];

    public const STATUS = [
        'pending'    => 'Pending',
        'selesai'    => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    protected $fillable = [
        'kode', 'warga_id', 'jenis', 'tanggal', 'total', 'status', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total'   => 'float',
    ];

    /* ------------------------------------------------------------------ */
    /* Relasi                                                             */
    /* ------------------------------------------------------------------ */

    public function warga(): BelongsTo
    {
        // Kolom kunci ditulis eksplisit karena primary key Warga bukan "id" (yaitu "id_warga").
        return $this->belongsTo(Warga::class, 'warga_id', (new Warga())->getKeyName());
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransaksiItem::class);
    }

    /* ------------------------------------------------------------------ */
    /* Deteksi kolom nama warga                                           */
    /* ------------------------------------------------------------------ */

    public static function kolomNamaWarga(): string
    {
        if (self::$kolomNamaCache !== null) {
            return self::$kolomNamaCache;
        }

        $tabel = (new Warga())->getTable();

        foreach (self::KANDIDAT_KOLOM_NAMA as $kolom) {
            if (Schema::hasColumn($tabel, $kolom)) {
                return self::$kolomNamaCache = $kolom;
            }
        }

        return self::$kolomNamaCache = self::KANDIDAT_KOLOM_NAMA[0];
    }

    /**
     * Nama yang ditampilkan untuk satu baris warga (dipakai di dropdown form).
     */
    public static function namaDariWarga(Warga $warga): string
    {
        return (string) ($warga->{self::kolomNamaWarga()} ?? ('Warga #' . $warga->getKey()));
    }

    /* ------------------------------------------------------------------ */
    /* Pencarian & filter                                                 */
    /* ------------------------------------------------------------------ */

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('kode', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%")
                        ->orWhereHas('warga', fn (Builder $w) => $w->where(self::kolomNamaWarga(), 'like', "%{$search}%"))
                        ->orWhereHas('items', fn (Builder $i) => $i->where('nama_item', 'like', "%{$search}%"));
                });
            })
            ->when($filters['jenis'] ?? null, fn (Builder $q, $v) => $q->where('jenis', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($filters['dari'] ?? null, fn (Builder $q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($filters['sampai'] ?? null, fn (Builder $q, $v) => $q->whereDate('tanggal', '<=', $v));
    }

    /* ------------------------------------------------------------------ */
    /* Helper tampilan                                                    */
    /* ------------------------------------------------------------------ */

    public function getNamaWargaAttribute(): string
    {
        return $this->warga?->{self::kolomNamaWarga()} ?? '-';
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst((string) $this->jenis);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getJenisBadgeAttribute(): string
    {
        return match ($this->jenis) {
            'setoran'   => 'lp-badge--green',
            'pencairan' => 'lp-badge--amber',
            'penjualan' => 'lp-badge--blue',
            default     => 'lp-badge--gray',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'selesai'    => 'lp-badge--green',
            'pending'    => 'lp-badge--amber',
            'dibatalkan' => 'lp-badge--red',
            default      => 'lp-badge--gray',
        };
    }

    /* ------------------------------------------------------------------ */
    /* Kode transaksi                                                     */
    /* ------------------------------------------------------------------ */

    public static function generateKode(): string
    {
        do {
            $kode = 'TRX-' . now()->format('ymd') . '-' . Str::upper(Str::random(4));
        } while (self::where('kode', $kode)->exists());

        return $kode;
    }
}