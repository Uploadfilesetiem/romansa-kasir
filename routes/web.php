<?php

use App\Http\Controllers\KasirController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\StokController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KasirController::class, 'index'])->name('kasir.index');
Route::post('/bayar', [KasirController::class, 'bayar'])->name('kasir.bayar');
Route::get('/struk/{transaksi}', [KasirController::class, 'struk'])->name('kasir.struk');

Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
Route::post('/stok/menu', [StokController::class, 'storeProduk'])->name('stok.menu.store');
Route::put('/stok/menu/{produk}', [StokController::class, 'updateProduk'])->name('stok.menu.update');
Route::delete('/stok/menu/{produk}', [StokController::class, 'destroyProduk'])->name('stok.menu.destroy');
Route::post('/stok/atur', [StokController::class, 'aturStok'])->name('stok.atur');
Route::post('/stok/tambah', [StokController::class, 'tambahStok'])->name('stok.tambah');

Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
Route::delete('/laporan/{transaksi}', [LaporanController::class, 'batalkan'])->name('laporan.batalkan');
