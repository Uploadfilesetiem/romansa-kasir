<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiItem extends Model
{
    protected $table = 'transaksi_item';

    public $timestamps = false;

    protected $fillable = ['transaksi_id', 'produk_id', 'nama_produk', 'harga', 'qty', 'subtotal'];

    protected $casts = [
        'harga' => 'integer',
        'qty' => 'integer',
        'subtotal' => 'integer',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}
