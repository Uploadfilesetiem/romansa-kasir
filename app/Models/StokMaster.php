<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMaster extends Model
{
    protected $table = 'stok_master';
    public $timestamps = false;

    protected $fillable = [
        'stok_awal',
        'stok_sisa',
    ];
}
