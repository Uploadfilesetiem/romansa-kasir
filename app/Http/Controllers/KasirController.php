<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

            // Simpan Transaksi Utama
            $transaksiId = DB::table('transaksis')->insertGetId([
                'kode_transaksi'    => 'TRX-' . time(),
                'total'             => $total,
                'bayar'             => $request->bayar,
                'kembalian'         => max(0, $request->bayar - $total),
                'metode_pembayaran' => $request->metode_pembayaran,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $hasCatatanColumn = Schema::hasColumn('transaksi_items', 'catatan');

            foreach ($request->items as $item) {
                // Pencarian nama produk otomatis 
                $namaProduk = $item['nama_produk'] ?? $item['nama'] ?? null;
                
                if (empty($namaProduk) && !empty($item['id'])) {
                    $p = DB::table('produks')->where('id', $item['id'])->first();
                    if ($p) {
                        $namaProduk = $p->nama ?? $p->nama_produk ?? null;
                    }
                }

                $itemData = [
                    'transaksi_id' => $transaksiId,
                    'produk_id'    => $item['id'] ?? null,
                    'nama_produk'  => $namaProduk ?: 'Roti Bakar',
                    'harga'        => $item['harga'],
                    'qty'          => $item['qty'],
                    'subtotal'     => $item['harga'] * $item['qty'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                if ($hasCatatanColumn && isset($item['catatan'])) {
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
                'transaksi_id' => $transaksiId,
                'redirect' => route('kasir.struk', $transaksiId)
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
        $transaksi = DB::table('transaksis')->where('id', $id)->first();
        $items = DB::table('transaksi_items')->where('transaksi_id', $id)->get();
        
        if (!$transaksi) {
            abort(404);
        }

        $transaksi->items = $items;

        return view('kasir.struk', compact('transaksi'));
    }
}
