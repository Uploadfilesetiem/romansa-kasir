<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Stok bersama (mis. stok roti tawar). SEMUA menu memakai satu baris stok ini,
// karena setiap menu pada dasarnya memakai 1 roti tawar. Nilainya bisa
// diubah/di-setting kapan saja lewat halaman Stok.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_master', function (Blueprint $table) {
            $table->id();
            $table->integer('stok')->default(20);
            $table->timestamp('updated_at')->nullable();
        });

        DB::table('stok_master')->insert([
            'id' => 1,
            'stok' => 20,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_master');
    }
};
