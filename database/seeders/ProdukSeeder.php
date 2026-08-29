<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            // CAMPUR
            ['Selai Buah Ekonomis 2 Rasa', 'Campur', 13000],
            ['Double Cokelat', 'Campur', 16000],
            ['Cokelat - Selai Buah', 'Campur', 17000],
            ['Kacang - Selai Buah', 'Campur', 17000],
            ['Keju - Selai Buah', 'Campur', 17000],
            ['Cokelat - Keju', 'Campur', 18000],
            ['Strawberry Blueberry Premium Fill', 'Campur', 18000],
            ['Cokelat - Kacang', 'Campur', 18000],
            ['Kacang - Keju', 'Campur', 18000],
            // ISTIMEWA
            ['Choco Crunchy - Selai Buah', 'Istimewa', 18000],
            ['Oreo - Cokelat', 'Istimewa', 18000],
            ['Tiramisu Crunchy - Cokelat', 'Istimewa', 18000],
            ['Tiramisu Crunchy - Selai Buah', 'Istimewa', 18000],
            ['Choco Crunchy - Keju', 'Istimewa', 20000],
            ['Oreo - Keju', 'Istimewa', 20000],
            ['Double Keju', 'Istimewa', 20000],
            ['Tiramisu Crunchy - Keju', 'Istimewa', 20000],
            ['Double Choco Crunchy', 'Istimewa', 22000],
            // KOMBINASI
            ['Selai Buah 4 Rasa', 'Kombinasi', 15000],
            ['Selai Buah Premium Fill - Selai Buah Ekonomis', 'Kombinasi', 16000],
            ['Cokelat - Strawberry Mix Blueberry Premium Fill', 'Kombinasi', 18000],
            ['Cokelat Mix Keju + Selai Buah', 'Kombinasi', 20000],
            ['Double Cokelat Mix Double Kacang', 'Kombinasi', 22000],
            ['Double Cokelat Mix Double Keju', 'Kombinasi', 22000],
            ['Cokelat Mix Keju - Oreo', 'Kombinasi', 23000],
            ['Double Cokelat Mix Keju + Kacang', 'Kombinasi', 25000],
            // GURIH
            ['Salt Egg', 'Gurih', 24000],
            ['Salt Chicken Patties', 'Gurih', 32000],
            ['Salt Beef Patties', 'Gurih', 36000],
            ['Topping Tambahan', 'Gurih', 3000],
        ];

        foreach ($menu as [$nama, $kategori, $harga]) {
            Produk::firstOrCreate(
                ['nama' => $nama],
                ['kategori' => $kategori, 'harga' => $harga]
            );
        }
    }
}
