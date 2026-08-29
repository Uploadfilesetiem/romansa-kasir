<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = ['kode', 'total', 'metode_bayar', 'bayar', 'kembalian'];

    protected $casts = [
        'total' => 'integer',
        'bayar' => 'integer',
        'kembalian' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(TransaksiItem::class, 'transaksi_id');
    }
}
