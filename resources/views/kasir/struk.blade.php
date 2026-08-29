@extends('layouts.app')
@section('title', 'Struk Pembayaran')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4" style="max-width: 400px;">
  <div class="card border-0 shadow-sm p-3 bg-white text-center">
    <h4 class="fw-bold mb-0" style="color: #b45309;">ROTI BAKAR ROMANSA</h4>
    <p class="small text-muted mb-2">Struk Pembayaran Kasir</p>
    <hr class="my-2" style="border-top: 2px dashed #ccc;">

    <div class="text-start small mb-2">
      <div><strong>Kode:</strong> {{ $transaksi->kode_transaksi }}</div>
      <div><strong>Tanggal:</strong> {{ date('d-m-Y H:i', strtotime($transaksi->created_at ?? now())) }}</div>
      <div><strong>Metode:</strong> {{ $transaksi->metode_pembayaran }}</div>
    </div>

    <hr class="my-2" style="border-top: 2px dashed #ccc;">

    <div class="text-start small mb-2">
      @foreach($transaksi->items as $item)
        <div class="d-flex justify-content-between">
          <span class="fw-bold">{{ $item->nama_produk }} x{{ $item->qty }}</span>
          <span>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
        @if(!empty($item->catatan))
          <div class="text-muted ms-2" style="font-size: 0.75rem;">Catatan: {{ $item->catatan }}</div>
        @endif
      @endforeach
    </div>

    <hr class="my-2" style="border-top: 2px dashed #ccc;">

    <div class="text-start small mb-3">
      <div class="d-flex justify-content-between">
        <span>Total:</span>
        <span class="fw-bold">Rp{{ number_format($transaksi->total, 0, ',', '.') }}</span>
      </div>
      <div class="d-flex justify-content-between">
        <span>Bayar:</span>
        <span>Rp{{ number_format($transaksi->bayar, 0, ',', '.') }}</span>
      </div>
      <div class="d-flex justify-content-between fw-bold text-success fs-6 mt-1">
        <span>Kembali:</span>
        <span>Rp{{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
      </div>
    </div>

    <div class="d-grid gap-2">
      <button onclick="window.print()" class="btn btn-warning fw-bold text-dark">Cetak Struk</button>
      <a href="{{ route('kasir.index') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Kasir</a>
    </div>
  </div>
</div>
