<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'produk_daur_ulang';

    protected $primaryKey = 'id_produk';

    public $timestamps = false;

    protected $guarded = [];
}
