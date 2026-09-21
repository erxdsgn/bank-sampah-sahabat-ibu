<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class Pencairan extends Model
{
    protected $table = 'pencairan_saldo';
    protected $primaryKey = 'id_pencairan';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'id_warga',
        'id_admin',
        'tanggal_pencairan',
        'jumlah',
        'metode_transfer',
        'status',
    ];

    protected $casts = [
        'tanggal_pencairan' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'id_warga', 'id_warga');
    }

    public function admin()
    {
        // Sesuaikan dengan model Admin/User yang kamu pakai
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
