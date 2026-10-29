<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'user_id', // FK ke tabel users
        'nama',
        'no_hp',
        'alamat',
    ];

    /**
     * Relasi balik ke akun pusat (User)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function hargaSampah()
    {
        return $this->hasMany(HargaSampah::class, 'id_admin', 'id_admin');
    }
}
