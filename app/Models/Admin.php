<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    // Tetapkan nama jadual secara khusus
    protected $table = 'admin';

    // Tetapkan kunci utama (primary key)
    protected $primaryKey = 'id_admin';

    // Kolum yang boleh diisi secara pukal (mass assignment)
    protected $fillable = [
        'nama',
        'username',
        'password',
    ];

    // Sembunyikan ruangan sensitif semasa penukaran ke Array/JSON
    protected $hidden = [
        'password',
    ];

    public function hargaSampah()
    {
        return $this->hasMany(HargaSampah::class, 'id_admin', 'id_admin');
    }

    // Cast jenis data untuk ruangan tertentu jika perlu
    protected $casts = [
        'password' => 'hashed',
    ];
}
