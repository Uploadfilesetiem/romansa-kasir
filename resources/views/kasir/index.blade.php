@extends('layouts.app')
@section('title', 'Kasir')

@section('content')
  <div id="stok-banner" class="stok-banner">
    <span>Stok Roti Tawar</span>
    <span class="angka">
      <span id="stok-sisa">{{ is_object($stok) ? $stok->stok_sisa : $stok }} tersisa</span>
    </span>
  </div>

  <div class="kategori-scroll">
    <button type="button" class="pill active" data-kategori="Semua">Semua</button>
    @foreach ($grouped as $g)
      <button type="button" class="pill" data-kategori="{{ $g['kategori'] }}">{{ $g['kategori'] }}</button>
    @endforeach
  </div>

  @forelse ($grouped as $g)
    <div class="kategori-block" data-kategori="{{ $g['kategori'] }}">
      <div class="kategori-heading">{{ $g['kategori'] }}</div>
      <div class="menu-grid">
        @foreach ($g['items'] as $i => $p)
          <button
            type="button"
            class="menu-card"
            style="animation-delay: {{ $i * 0.03 }}s"
            data-id="{{ $p->id }}"
            data-nama="{{ $p->nama }}"
            data-harga="{{ $p->harga }}"
            data-kategori="{{ $p->kategori }}"
          >
            <div class="nama">{{ $p->nama }}</div>
            <div class="harga">Rp{{ number_format($p->harga, 0, ',', '.') }}</div>
          </button>
        @endforeach
      </div>
    </div>
  @empty
    <p class="text-muted text-center py-4">Belum ada menu.</p>
  @endforelse
@endsection
