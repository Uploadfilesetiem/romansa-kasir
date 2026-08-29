<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function index()
    {
        $produk = Produk::orderBy('kategori')->orderBy('nama')->get();
        $stok = (int) (DB::table('stok_master')->where('id', 1)->value('stok') ?? 0);

        return view('stok.index', compact('produk', 'stok'));
    }

    public function storeProduk(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:produk,nama',
            'kategori' => 'required|string|in:Campur,Istimewa,Kombinasi,Gurih,Lainnya',
            'harga' => 'required|integer|min:0',
        ]);

        Produk::create($data);

        return back()->with('sukses', 'Menu baru berhasil ditambahkan.');
    }

    public function updateProduk(Request $request, Produk $produk)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:produk,nama,'.$produk->id,
            'kategori' => 'required|string|in:Campur,Istimewa,Kombinasi,Gurih,Lainnya',
            'harga' => 'required|integer|min:0',
        ]);

        $produk->update($data);

        return back()->with('sukses', 'Menu berhasil diperbarui.');
    }

    public function destroyProduk(Produk $produk)
    {
        $produk->delete();

        return back()->with('sukses', 'Menu dihapus.');
    }

    public function aturStok(Request $request)
    {
        $data = $request->validate(['stok' => 'required|integer|min:0']);

        DB::table('stok_master')->where('id', 1)->update([
            'stok' => $data['stok'],
            'updated_at' => now(),
        ]);

        DB::table('stok_log')->insert([
            'produk_id' => null,
            'jenis' => 'atur',
            'jumlah' => $data['stok'],
            'keterangan' => 'Atur ulang stok roti tawar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('sukses', 'Stok berhasil diperbarui.');
    }

    public function tambahStok(Request $request)
    {
        $data = $request->validate([
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::table('stok_master')->where('id', 1)->increment('stok', $data['jumlah'], [
            'updated_at' => now(),
        ]);

        DB::table('stok_log')->insert([
            'produk_id' => null,
            'jenis' => 'tambah',
            'jumlah' => $data['jumlah'],
            'keterangan' => $data['keterangan'] ?: 'Tambah stok roti tawar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('sukses', 'Stok berhasil ditambahkan.');
    }
}
