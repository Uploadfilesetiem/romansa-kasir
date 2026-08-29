<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private function ambilData(Request $request): array
    {
        $start = $request->query('start', now()->toDateString());
        $end = $request->query('end', now()->toDateString());
        $metode = $request->query('metode');

        $q = Transaksi::with('items')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);

        if ($metode) {
            $q->where('metode_bayar', $metode);
        }

        $data = $q->orderByDesc('created_at')->get();

        return [$data, $start, $end, $metode];
    }

    private function rekapMenu($data): array
    {
        $rekap = [];
        foreach ($data as $t) {
            foreach ($t->items as $it) {
                $key = $it->nama_produk;
                if (!isset($rekap[$key])) {
                    $rekap[$key] = ['nama' => $key, 'qty' => 0, 'total' => 0];
                }
                $rekap[$key]['qty'] += $it->qty;
                $rekap[$key]['total'] += $it->subtotal;
            }
        }
        usort($rekap, fn ($a, $b) => $b['qty'] <=> $a['qty']);

        return array_values($rekap);
    }

    private function ringkasan($data): array
    {
        return [
            'totalTunai' => $data->where('metode_bayar', 'tunai')->sum('total'),
            'totalQris' => $data->where('metode_bayar', 'qris')->sum('total'),
        ];
    }

    public function index(Request $request)
    {
        [$data, $start, $end, $metode] = $this->ambilData($request);
        $ringkasan = $this->ringkasan($data);
        $rekapMenu = $this->rekapMenu($data);

        return view('laporan.index', [
            'data' => $data,
            'start' => $start,
            'end' => $end,
            'metode' => $metode,
            'totalTunai' => $ringkasan['totalTunai'],
            'totalQris' => $ringkasan['totalQris'],
            'rekapMenu' => $rekapMenu,
        ]);
    }

    public function cetak(Request $request)
    {
        [$data, $start, $end, $metode] = $this->ambilData($request);
        $ringkasan = $this->ringkasan($data);
        $rekapMenu = $this->rekapMenu($data);

        return view('laporan.cetak', [
            'data' => $data,
            'start' => $start,
            'end' => $end,
            'totalTunai' => $ringkasan['totalTunai'],
            'totalQris' => $ringkasan['totalQris'],
            'rekapMenu' => $rekapMenu,
        ]);
    }

    public function batalkan(Transaksi $transaksi)
    {
        $transaksi->load('items');
        $totalQty = $transaksi->items->sum('qty');

        DB::transaction(function () use ($transaksi, $totalQty) {
            if ($totalQty > 0) {
                DB::table('stok_master')->where('id', 1)->increment('stok', $totalQty, [
                    'updated_at' => now(),
                ]);
                DB::table('stok_log')->insert([
                    'produk_id' => null,
                    'jenis' => 'pembatalan',
                    'jumlah' => $totalQty,
                    'keterangan' => "Pembatalan transaksi #{$transaksi->id}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $transaksi->delete();
        });

        return back()->with('sukses', 'Transaksi dibatalkan, stok roti sudah dikembalikan.');
    }
}
