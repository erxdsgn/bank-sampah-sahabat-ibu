<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JadwalSetoran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_setoran';
    protected $primaryKey = 'id_jadwal';

    // Karena ID berupa string acak, matikan auto increment
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_jadwal',
        'nama_agenda',
        'tanggal_setoran',
        'jam_mulai',
        'jam_selesai',
        'wilayah',
        'kuota_maksimal',
        'status',
        'keterangan',
    ];

    // Otomatis men-generate ID acak (contoh: js_9f8d7c6b5a) saat data baru dibuat
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_jadwal)) {
                $model->id_jadwal = 'js_' . Str::random(10);
            }
        });
    }
}
