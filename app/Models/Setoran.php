<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setoran extends Model
{
    protected $table = 'penyetoran';

    protected $primaryKey = 'id_setoran';

    protected $fillable = [
        'kode_transaksi',
        'id_warga',
        'id_admin',
        'tanggal_setoran',
        'status',
        'total_berat',
        'total_nilai',
        'foto_sampah',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_setoran' => 'date',
        'total_berat' => 'decimal:2',
        'total_nilai' => 'decimal:2',
    ];

    /**
     * Warga pemilik setoran.
     */
    public function warga()
    {
        return $this->belongsTo(
            Warga::class,
            'id_warga',
            'id_warga'
        );
    }

    /**
     * Detail setoran.
     */
    public function details()
    {
        return $this->hasMany(
            DetailSetoran::class,
            'id_setoran',
            'id_setoran'
        );
    }

    /**
     * Admin yang memproses.
     */
    public function admin()
    {
        return $this->belongsTo(
            Admin::class,
            'id_admin',
            'id_admin'
        );
    }

    /**
     * Ambil jumlah setoran pertama.
     */
    public function getJumlahAktualAttribute()
    {
        $detail = $this->details->first();

        if (! $detail) {
            return (float) (
                $this->total_berat ?? 0
            );
        }

        return (float)
            $detail->jumlah_aktual;
    }

    /**
     * Ambil satuan setoran pertama.
     */
    public function getSatuanAktualAttribute(): string
    {
        $detail = $this->details->first();

        if (! $detail) {
            return 'gram';
        }

        return $detail->satuan_aktual;
    }
}
