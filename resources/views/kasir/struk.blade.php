@extends('layouts.app')
@section('title', 'Struk')

@section('content')
  <div class="struk-wrap">
    <div class="struk">
      <div class="check-wrap">
        <svg class="check-circle" viewBox="0 0 52 52">
          <circle cx="26" cy="26" r="24"></circle>
          <path d="M15 27l7 7 15-15"></path>
        </svg>
      </div>
      <h3>ROTI BAKAR ROMANSA</h3>
      <div class="sub-center">Hangat di Setiap Cerita</div>

      <div class="meta">Kode: {{ $transaksi->kode }}</div>
      <div class="meta" style="margin-bottom:10px;">{{ $transaksi->created_at->translatedFormat('d M Y, H:i') }}</div>

      <div class="divider-dash"></div>

      @foreach ($transaksi->items as $it)
        <div class="struk-item">
          <span>{{ $it->nama_produk }} x{{ $it->qty }}</span>
          <span>Rp{{ number_format($it->harga * $it->qty, 0, ',', '.') }}</span>
        </div>
      @endforeach

      <div class="divider-dash"></div>

      <div class="struk-item" style="font-weight:800; font-size:14px;">
        <span>Total</span>
        <span>Rp{{ number_format($transaksi->total, 0, ',', '.') }}</span>
      </div>
      <div class="struk-item">
        <span>Metode</span>
        <span style="text-transform:uppercase;">{{ $transaksi->metode_bayar }}</span>
      </div>
      <div class="struk-item">
        <span>Bayar</span>
        <span>Rp{{ number_format($transaksi->bayar, 0, ',', '.') }}</span>
      </div>
      <div class="struk-item">
        <span>Kembalian</span>
        <span>Rp{{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
      </div>
    </div>
  </div>

  <div style="max-width:340px;margin:14px auto 0;">
    <button type="button" class="btn-primary" onclick="window.print()">Cetak</button>
    <a href="{{ route('kasir.index') }}"><button type="button" class="btn-outline">Transaksi Baru</button></a>
  </div>
@endsection
