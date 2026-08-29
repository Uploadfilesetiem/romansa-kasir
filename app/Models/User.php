<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model ini tidak dipakai (aplikasi kasir ini tidak punya login), hanya
// disediakan supaya konfigurasi auth bawaan Laravel tidak error.
class User extends Model
{
    protected $table = 'users';
}
