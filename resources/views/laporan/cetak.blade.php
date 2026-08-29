<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Roti Bakar Romansa {{ $start }} - {{ $end }}</title>
<style>
  body { font-family: Georgia, serif; padding: 24px; color: #23283f; }
  h1 { font-size: 20px; margin-bottom: 0; }
  .sub { font-size: 12px; color: #888; margin-top: 2px; margin-bottom: 18px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 22px; font-size: 12px; }
  th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
  th { background: #23283f; color: #fff; }
  .text-right { text-align: right; }
  .ringkasan { font-size: 13px; margin-bottom: 18px; }
  .ringkasan b { display:inline-block; width: 160px; }
</style>
</head>
<body onload="window.print()">
  <h1>Laporan Roti Bakar Romansa</h1>
  <div class="sub">Periode {{ $start }} s/d {{ $end }}</div>

  <div class="ringkasan">
    <div><b>Total Tunai</b> Rp{{ number_format($totalTunai,0,',','.') }}</div>
    <div><b>Total QRIS</b> Rp{{ number_format($totalQris,0,',','.') }}</div>
    <div><b>Grand Total</b> Rp{{ number_format($totalTunai+$totalQris,0,',','.') }}</div>
    <div><b>Jumlah Transaksi</b> {{ $data->count() }}</div>
  </div>

  <h3>Catatan Selai/Menu Keluar</h3>
  <table>
    <thead><tr><th>Menu / Selai</th><th class="text-right">Qty Terjual</th><th class="text-right">Omzet</th></tr></thead>
    <tbody>
      @forelse ($rekapMenu as $r)
        <tr><td>{{ $r['nama'] }}</td><td class="text-right">{{ $r['qty'] }}</td><td class="text-right">Rp{{ number_format($r['total'],0,',','.') }}</td></tr>
      @empty
        <tr><td colspan="3">Belum ada menu terjual.</td></tr>
      @endforelse
    </tbody>
  </table>

  <h3>Riwayat Transaksi</h3>
  <table>
    <thead><tr><th>Kode</th><th>Waktu</th><th>Metode</th><th class="text-right">Total</th></tr></thead>
    <tbody>
      @forelse ($data as $t)
        <tr><td>{{ $t->kode }}</td><td>{{ $t->created_at->format('d/m/Y H:i') }}</td><td>{{ strtoupper($t->metode_bayar) }}</td><td class="text-right">Rp{{ number_format($t->total,0,',','.') }}</td></tr>
      @empty
        <tr><td colspan="4">Belum ada transaksi.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
