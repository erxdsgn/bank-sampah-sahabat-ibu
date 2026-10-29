<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. Ditambahkan

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // 2. Ditambahkan HasApiTokens

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'role', // 'admin' atau 'warga'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Relasi ke profil Admin
     */
    public function admin()
    {
        // PERBAIKAN: Diubah dari 'id_user' menjadi 'user_id' agar sesuai dengan Admin.php
        return $this->hasOne(Admin::class, 'user_id', 'id');
    }

    /**
     * Relasi ke profil Warga
     */
    public function warga()
    {
        return $this->hasOne(Warga::class, 'id_user', 'id');
    }
}
