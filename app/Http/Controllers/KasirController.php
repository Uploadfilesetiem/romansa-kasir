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

            // 1. Simpan Transaksi Utama
            $transaksiId = DB::table('transaksis')->insertGetId([
                'kode_transaksi'    => 'TRX-' . time(),
                'total'             => $total,
                'bayar'             => $request->bayar,
                'kembalian'         => max(0, $request->bayar - $total),
                'metode_pembayaran' => $request->metode_pembayaran,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // 2. Deteksi otomatis kolom yang ada di database agar TIDAK ADA ERROR 'Column Not Found'
            $columns = Schema::getColumnListing('transaksi_items');
            
            $hasNamaProduk = in_array('nama_produk', $columns);
            $hasNama       = in_array('nama', $columns);
            $hasCatatan    = in_array('catatan', $columns);
            $hasProdukId   = in_array('produk_id', $columns);
            $hasHarga      = in_array('harga', $columns);
            $hasQty        = in_array('qty', $columns);
            $hasSubtotal   = in_array('subtotal', $columns);

            foreach ($request->items as $item) {
                // Tentukan nama item
                $namaFix = $item['nama_produk'] ?? $item['nama'] ?? null;
                if (empty($namaFix) && !empty($item['id'])) {
                    $p = DB::table('produks')->where('id', $item['id'])->first();
                    if ($p) {
                        $namaFix = $p->nama ?? $p->nama_produk ?? null;
                    }
                }
                if (empty($namaFix)) {
                    $namaFix = 'Roti Bakar Romansa';
                }

                $itemData = [
                    'transaksi_id' => $transaksiId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                // Hanya masukkan kolom yang BENAR-BENAR ADA di tabel database kamu
                if ($hasProdukId) {
                    $itemData['produk_id'] = $item['id'] ?? null;
                }
                if ($hasNamaProduk) {
                    $itemData['nama_produk'] = $namaFix;
                }
                if ($hasNama) {
                    $itemData['nama'] = $namaFix;
                }
                if ($hasHarga) {
                    $itemData['harga'] = $item['harga'];
                }
                if ($hasQty) {
                    $itemData['qty'] = $item['qty'];
                }
                if ($hasSubtotal) {
                    $itemData['subtotal'] = $item['harga'] * $item['qty'];
                }
                if ($hasCatatan && isset($item['catatan'])) {
                    $itemData['catatan'] = $item['catatan'];
                }

                DB::table('transaksi_items')->insert($itemData);
            }

            // 3. Potong stok
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

        foreach ($items as $it) {
            $it->nama_produk = $it->nama_produk ?? $it->nama ?? 'Roti Bakar Romansa';
        }

        $transaksi->items = $items;

        return view('kasir.struk', compact('transaksi'));
    }
}
