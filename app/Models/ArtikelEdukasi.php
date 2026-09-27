<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtikelEdukasi extends Model
{
    protected $table = 'artikel_edukasi';

    protected $primaryKey = 'id_artikel';

    protected $fillable = [
        'id_admin',
        'judul',
        'jenis',
        'konten',
        'gambar',
        'tanggal_publish',
    ];

    protected $casts = [
        'tanggal_publish' => 'date',
    ];
}
