<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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