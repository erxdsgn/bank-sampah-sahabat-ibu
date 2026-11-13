<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warga extends Model
{
    use HasFactory;

    protected $table = 'warga';
    protected $primaryKey = 'id_warga';

    // Properti wajib jika id_warga bertipe string (misal: BSSI001)
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_warga',
        'id_user',
        'nik',
        'nama',
        'no_hp',
        'alamat',
        'jumlah_anggota_keluarga',
        'saldo',
        'tanggal_daftar',
        'nama_bank_ewallet',
        'nomor_rekening',
        'nama_pemilik_rekening',
        'status_rekening',
        'kelompok',
        'kelompok_updated_at',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'tanggal_daftar' => 'datetime',
        'jumlah_anggota_keluarga' => 'integer',
        'kelompok_updated_at' => 'datetime',
    ];

    /**
     * Relasi balik ke akun pusat (User)
     */
    public function user()
    {
        // Disesuaikan dari 'user_id' menjadi 'id_user'
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function pencairan()
    {
        return $this->hasMany(Pencairan::class, 'id_warga', 'id_warga');
    }

    public function mutasiSaldo()
    {
        return $this->hasMany(MutasiSaldo::class, 'id_warga', 'id_warga');
    }
}
