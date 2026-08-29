<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $table->unsignedBigInteger('produk_id')->nullable();
            $table->string('nama_produk');
            $table->unsignedInteger('harga');
            $table->unsignedInteger('qty');
            $table->unsignedInteger('subtotal');
            $table->string('catatan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_item');
    }
};
