<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KasirController;

Route::get('/', [KasirController::class, 'index'])->name('kasir.index');
Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
Route::post('/kasir/store', [KasirController::class, 'store'])->name('kasir.store');
Route::get('/kasir/struk/{id}', [KasirController::class, 'struk'])->name('kasir.struk');

