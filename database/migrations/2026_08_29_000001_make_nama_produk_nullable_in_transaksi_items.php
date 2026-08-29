<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaksi_items')) {
            Schema::table('transaksi_items', function (Blueprint $table) {
                $table->string('nama_produk')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
    }
};
