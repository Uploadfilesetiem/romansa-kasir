<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaksi_items') && !Schema::hasColumn('transaksi_items', 'catatan')) {
            Schema::table('transaksi_items', function (Blueprint $table) {
                $table->string('catatan')->nullable()->after('subtotal');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaksi_items', 'catatan')) {
            Schema::table('transaksi_items', function (Blueprint $table) {
                $table->dropColumn('catatan');
            });
        }
    }
};
