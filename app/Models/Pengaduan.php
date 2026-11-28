<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    use HasFactory;

    protected $table = 'pengaduans';
    protected $primaryKey = 'id_pengaduan';

    protected $fillable = [
        'user_id',
        'id_warga',
        'kategori',
        'judul',
        'deskripsi',
        'foto_bukti',
        'status',
        'tanggapan_admin',
        'id_admin',
        'ditanggapi_at',
    ];

    protected $casts = [
        'ditanggapi_at' => 'datetime',
    ];

    public const KATEGORI_LABEL = [
        'layanan_petugas'  => 'Layanan Petugas',
        'jadwal_lokasi'    => 'Jadwal & Lokasi',
        'jenis_harga'      => 'Jenis & Harga',
        'kendala_aplikasi' => 'Kendala Aplikasi',
        'saran_masukan'    => 'Saran & Masukan',
        'lainnya'          => 'Lainnya',
    ];

    public const STATUS_LABEL = [
        'menunggu' => 'Menunggu',
        'diproses' => 'Diproses',
        'selesai'  => 'Selesai',
        'ditolak'  => 'Ditolak',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga', 'id_warga');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin', 'id');
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI_LABEL[$this->kategori] ?? 'Lainnya';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status] ?? 'Menunggu';
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto_bukti) {
            return null;
        }

        return asset('storage/' . ltrim($this->foto_bukti, '/'));
    }
}
