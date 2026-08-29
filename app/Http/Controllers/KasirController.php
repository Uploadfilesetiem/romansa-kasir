<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    private const URUTAN_KATEGORI = ['Campur', 'Istimewa', 'Kombinasi', 'Gurih', 'Lainnya'];

    public function index()
    {
        $produk = Produk::orderBy('nama')->get();

        $grouped = collect(self::URUTAN_KATEGORI)
            ->map(fn ($kat) => [
                'kategori' => $kat,
                'items' => $produk->where('kategori', $kat)->values(),
            ])
            ->filter(fn ($g) => $g['items']->isNotEmpty())
            ->values();

        $stok = (int) (DB::table('stok_master')->where('id', 1)->value('stok') ?? 0);

        return view('kasir.index', [
            'grouped' => $grouped,
            'stok' => $stok,
        ]);
    }

    // Semua menu memakai satu stok bersama (stok roti tawar) di tabel
    // stok_master, karena setiap menu pada dasarnya memakai 1 roti tawar.
    // Setiap item yang terjual tetap dicatat di stok_log per menu supaya ada
    // catatan/riwayat selai (menu) apa saja yang keluar (lihat Laporan).
    public function bayar(Request $request)
    {
        $data = $request->validate([
            'items_json' => 'required|string',
            'metode' => 'required|in:tunai,qris',
            'bayar' => 'required|numeric|min:0',
        ]);

        $items = json_decode($data['items_json'], true);
        if (!is_array($items) || count($items) === 0) {
            return back()->withErrors(['items' => 'Keranjang masih kosong.']);
        }

        $total = 0;
        $totalQty = 0;
        foreach ($items as $it) {
            $total += (int) $it['harga'] * (int) $it['qty'];
            $totalQty += (int) $it['qty'];
        }

        $metode = $data['metode'];
        $bayarFinal = $metode === 'qris' ? $total : (int) $data['bayar'];
        $kembalian = $metode === 'qris' ? 0 : $bayarFinal - $total;

        if ($metode === 'tunai' && $bayarFinal < $total) {
            return back()->withErrors(['bayar' => 'Uang bayar kurang dari total belanja.'])->withInput();
        }

        try {
            $transaksiId = DB::transaction(function () use ($items, $total, $totalQty, $metode, $bayarFinal, $kembalian) {
                $stokRow = DB::table('stok_master')->where('id', 1)->lockForUpdate()->first();
                if (!$stokRow) {
                    throw new \Exception('Stok belum disiapkan. Jalankan "php artisan migrate --seed" dahulu.');
                }
                if ($stokRow->stok < $totalQty) {
                    throw new \Exception("Stok roti tidak cukup (sisa {$stokRow->stok}).");
                }

                $kode = 'RBR-'.now()->format('ymdHis').rand(100, 999);

                $transaksiId = DB::table('transaksi')->insertGetId([
                    'kode' => $kode,
                    'total' => $total,
                    'metode_bayar' => $metode,
                    'bayar' => $bayarFinal,
                    'kembalian' => $kembalian,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $itemRows = [];
                $logRows = [];
                foreach ($items as $it) {
                    $subtotal = (int) $it['harga'] * (int) $it['qty'];
                    $itemRows[] = [
                        'transaksi_id' => $transaksiId,
                        'produk_id' => $it['produkId'],
                        'nama_produk' => $it['nama'],
                        'harga' => $it['harga'],
                        'qty' => $it['qty'],
                        'subtotal' => $subtotal,
                    ];
                    $logRows[] = [
                        'produk_id' => $it['produkId'],
                        'jenis' => 'penjualan',
                        'jumlah' => $it['qty'],
                        'keterangan' => "Transaksi {$kode}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('transaksi_item')->insert($itemRows);
                DB::table('stok_log')->insert($logRows);

                DB::table('stok_master')->where('id', 1)->update([
                    'stok' => $stokRow->stok - $totalQty,
                    'updated_at' => now(),
                ]);

                return $transaksiId;
            });
        } catch (\Exception $e) {
            return back()->withErrors(['bayar' => $e->getMessage()])->withInput();
        }

        return redirect()->route('kasir.struk', $transaksiId);
    }

    public function struk(Transaksi $transaksi)
    {
        $transaksi->load('items');
        return view('kasir.struk', ['transaksi' => $transaksi]);
    }
}
