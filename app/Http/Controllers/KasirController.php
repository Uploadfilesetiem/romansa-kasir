<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function index()
    {
        $produk = Produk::all();
        
        $groupedRaw = $produk->groupBy('kategori');
        $grouped = [];

        foreach ($groupedRaw as $kategoriName => $items) {
            $grouped[] = [
                'kategori' => $kategoriName ?: 'Lainnya',
                'items'    => $items
            ];
        }

        // Cari nilai stok secara fleksibel dari objek/array tabel stok_master
        $stokMaster = DB::table('stok_master')->first();
        $stok = 0;

        if ($stokMaster) {
            $data = (array) $stokMaster;
            // Ambil kolom yang mengandung nilai stok (stok_sisa, stok, jumlah, dsb)
            $stok = $data['stok_sisa'] ?? $data['stok'] ?? $data['jumlah'] ?? reset($data) ?? 0;
        }

        return view('kasir.index', compact('produk', 'grouped', 'stok'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'metode_pembayaran' => 'required|string',
            'bayar' => 'required|numeric',
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $total += $item['harga'] * $item['qty'];
        }

        $transaksi = Transaksi::create([
            'kode_transaksi' => 'TRX-' . time(),
            'total' => $total,
            'bayar' => $request->bayar,
            'kembalian' => max(0, $request->bayar - $total),
            'metode_pembayaran' => $request->metode_pembayaran,
        ]);

        foreach ($request->items as $item) {
            TransaksiItem::create([
                'transaksi_id' => $transaksi->id,
                'produk_id'    => $item['id'] ?? null,
                'nama_produk'  => $item['nama_produk'] ?? $item['nama'] ?? 'Produk',
                'harga'        => $item['harga'],
                'qty'          => $item['qty'],
                'subtotal'     => $item['harga'] * $item['qty'],
                'catatan'      => $item['catatan'] ?? null,
            ]);
        }

        // Potong stok secara aman
        $totalQty = array_sum(array_column($request->items, 'qty'));
        $stokMaster = DB::table('stok_master')->first();
        if ($stokMaster) {
            $data = (array) $stokMaster;
            $kolomStok = isset($data['stok_sisa']) ? 'stok_sisa' : (isset($data['stok']) ? 'stok' : null);
            if ($kolomStok) {
                DB::table('stok_master')->decrement($kolomStok, $totalQty);
            }
        }

        return response()->json([
            'status' => 'success',
            'transaksi_id' => $transaksi->id,
            'redirect' => route('kasir.struk', $transaksi->id)
        ]);
    }

    public function struk($id)
    {
        $transaksi = Transaksi::with('items')->findOrFail($id);
        return view('kasir.struk', compact('transaksi'));
    }
}
