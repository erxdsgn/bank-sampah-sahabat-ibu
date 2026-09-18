<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtikelEdukasi extends Model
{
    protected $table = 'artikel_edukasi';

    protected $primaryKey = 'id_artikel';

    public $timestamps = false;

    protected $fillable = [
        'id_admin',
        'judul',
        'konten',
        'gambar',
        'tanggal_publish',
    ];

    protected $casts = [
        'tanggal_publish' => 'date',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(
            Admin::class,
            'id_admin',
            'id_admin'
        );
    }
}
