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

        $stokMaster = DB::table('stok_master')->first();
        $stok = 0;

        if ($stokMaster) {
            $data = (array) $stokMaster;
            $stok = $data['stok_sisa'] ?? $data['stok'] ?? $data['jumlah'] ?? reset($data) ?? 0;
        }

        return view('kasir.index', compact('produk', 'grouped', 'stok'));
    }

    public function store(Request $request)
    {
        try {
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
                $itemData = [
                    'transaksi_id' => $transaksi->id,
                    'produk_id'    => $item['id'] ?? null,
                    'nama_produk'  => $item['nama_produk'] ?? $item['nama'] ?? 'Produk',
                    'harga'        => $item['harga'],
                    'qty'          => $item['qty'],
                    'subtotal'     => $item['harga'] * $item['qty'],
                ];

                // Hanya masukkan catatan jika kolom catatan tersedia di DB
                if (!empty($item['catatan'])) {
                    $itemData['catatan'] = $item['catatan'];
                }

                DB::table('transaksi_items')->insert($itemData);
            }

            // Potong stok
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function struk($id)
    {
        $transaksi = Transaksi::with('items')->findOrFail($id);
        return view('kasir.struk', compact('transaksi'));
    }
}
