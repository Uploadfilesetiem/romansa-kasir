<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\StokMaster;
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

        // Ambil nilai sisa stok langsung dari query DB agar pasti dapat angkanya
        $stokMaster = DB::table('stok_master')->first();
        $stok = $stokMaster ? $stokMaster->stok_sisa : 0;

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

        // Kurangi stok roti tawar
        $totalQty = array_sum(array_column($request->items, 'qty'));
        DB::table('stok_master')->decrement('stok_sisa', $totalQty);

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
