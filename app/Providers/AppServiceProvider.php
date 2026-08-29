<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Cek jika tabel produk belum ada, otomatis jalankan migrate & seed
        try {
            if (!Schema::hasTable('produk')) {
                Artisan::call('migrate:refresh', [
                    '--seed' => true,
                    '--force' => true,
                ]);
            }
        } catch (\Throwable $e) {
            //
        }
    }
}
