@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
  <div class="page-title">Laporan</div>

  <form method="GET" action="{{ route('laporan.index') }}" class="filter-row">
    <input type="date" name="start" class="input" value="{{ $start }}">
    <input type="date" name="end" class="input" value="{{ $end }}">
    <select name="metode" class="input" style="flex:0 0 110px;">
      <option value="">Semua</option>
      <option value="tunai" @selected($metode === 'tunai')>Tunai</option>
      <option value="qris" @selected($metode === 'qris')>QRIS</option>
    </select>
    <button type="submit" class="btn-small navy">Filter</button>
  </form>

  <div class="menu-grid" style="grid-template-columns: 1fr 1fr; margin-bottom:16px;">
    <div class="card card-pad fade-item">
      <div class="section-sub">Total Tunai</div>
      <div style="font-weight:800;font-size:17px;color:var(--navy);">Rp{{ number_format($totalTunai,0,',','.') }}</div>
    </div>
    <div class="card card-pad fade-item" style="animation-delay:.05s">
      <div class="section-sub">Total QRIS</div>
      <div style="font-weight:800;font-size:17px;color:var(--navy);">Rp{{ number_format($totalQris,0,',','.') }}</div>
    </div>
  </div>

  <div class="card table-wrap" style="margin-bottom:16px;">
    <div style="padding:12px 14px 0;">
      <div class="section-title">Catatan Selai/Menu Keluar</div>
      <div class="section-sub">Rekap jumlah tiap menu (selai) yang terjual pada periode ini, dari yang paling laris.</div>
    </div>
    <table class="list" style="margin-top:8px;">
      <thead><tr><th>Menu / Selai</th><th class="text-right">Qty</th><th class="text-right">Omzet</th></tr></thead>
      <tbody>
        @forelse ($rekapMenu as $r)
          <tr>
            <td>{{ $r['nama'] }}</td>
            <td class="text-right" style="font-weight:700;">{{ $r['qty'] }}</td>
            <td class="text-right">Rp{{ number_format($r['total'],0,',','.') }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="rekap-empty">Belum ada menu yang terjual pada periode ini.</td></tr>
        @endforelse
      </tbody>
      @if (count($rekapMenu))
        <tfoot>
          <tr>
            <td>Total</td>
            <td class="text-right">{{ array_sum(array_column($rekapMenu, 'qty')) }}</td>
            <td class="text-right">Rp{{ number_format($totalTunai + $totalQris,0,',','.') }}</td>
          </tr>
        </tfoot>
      @endif
    </table>
  </div>

  <div class="flex-between" style="margin-bottom:10px;">
    <div class="section-title">Riwayat Transaksi</div>
    <a href="{{ route('laporan.cetak', request()->query()) }}" target="_blank">
      <button type="button" class="btn-small ghost">🖨️ Cetak</button>
    </a>
  </div>

  <div class="card table-wrap">
    <table class="list">
      <thead><tr><th>Kode</th><th>Waktu</th><th>Metode</th><th class="text-right">Total</th><th class="text-center">Aksi</th></tr></thead>
      <tbody>
        @forelse ($data as $t)
          <tr>
            <td>{{ $t->kode }}</td>
            <td>{{ $t->created_at->translatedFormat('d M, H:i') }}</td>
            <td style="text-transform:uppercase;">{{ $t->metode_bayar }}</td>
            <td class="text-right">Rp{{ number_format($t->total,0,',','.') }}</td>
            <td class="text-center">
              <form method="POST" action="{{ route('laporan.batalkan', $t) }}" data-confirm="Batalkan transaksi {{ $t->kode }}? Stok roti akan dikembalikan.">
                @csrf @method('DELETE')
                <button type="submit" class="btn-mini hapus">Batalkan</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="rekap-empty">Belum ada transaksi pada periode ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
