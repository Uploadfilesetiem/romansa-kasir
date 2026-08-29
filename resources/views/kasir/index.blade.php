@extends('layouts.app')
@section('title', 'Kasir')

@section('content')
  <div id="stok-banner" class="stok-banner">
    <span>Stok Roti Tawar</span>
    <span class="angka"><span id="stok-sisa">{{ $stok }} tersisa (dari {{ $stok }})</span></span>
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
          >
            <span class="nama">{{ $p->nama }}</span>
            <span class="harga">Rp{{ number_format($p->harga, 0, ',', '.') }}</span>
          </button>
        @endforeach
      </div>
    </div>
  @empty
    <p class="rekap-empty">Belum ada menu. Jalankan <code>php artisan migrate --seed</code> dahulu.</p>
  @endforelse

  <button type="button" id="cart-fab" class="cart-fab" style="display:none">
    🛒 Keranjang
    <span class="badge" id="cart-fab-count">0</span>
  </button>

  <!-- Bottom sheet Keranjang -->
  <div id="sheet-cart" class="sheet-root">
    <div class="sheet-backdrop" data-close-sheet onclick="document.getElementById('sheet-cart').classList.remove('open')"></div>
    <div class="sheet">
      <div class="sheet-handle"></div>
      <h3>Keranjang</h3>
      <div class="sub">Ketuk menu di halaman utama untuk menambah item.</div>

      <div id="cart-empty" style="text-align:center;color:#8a8071;font-size:13px;padding:14px 0;">Belum ada item.</div>
      <div id="cart-items"></div>

      <div class="divider-dash"></div>
      <div class="total-row">
        <span>Total</span>
        <span id="cart-total">Rp0</span>
      </div>

      <label class="field-label">Metode Bayar</label>
      <div class="metode-toggle">
        <button type="button" class="active" data-metode="tunai">Tunai</button>
        <button type="button" data-metode="qris">QRIS</button>
      </div>

      <div id="bayar-wrap">
        <label class="field-label">Uang Diterima</label>
        <input type="number" id="bayar_input" class="input" placeholder="0">
        <div class="uang-cepat">
          <button type="button" class="chip" data-nominal="20000">+Rp20.000</button>
          <button type="button" class="chip" data-nominal="50000">+Rp50.000</button>
          <button type="button" class="chip" data-nominal="100000">+Rp100.000</button>
          <button type="button" class="chip pas" id="chip-pas">Uang Pas</button>
          <button type="button" class="chip reset" id="chip-reset">Reset</button>
        </div>
        <div class="flex-between" style="margin-top:10px;">
          <span style="font-size:13px;">Kembalian</span>
          <strong id="cart-kembalian">Rp0</strong>
        </div>
      </div>

      <div id="form-error" class="error-text"></div>

      <form id="kasir-form" method="POST" action="{{ route('kasir.bayar') }}">
        @csrf
        <input type="hidden" name="items_json" id="items_json">
        <input type="hidden" name="metode" id="metode_input" value="tunai">
        <input type="hidden" name="bayar" id="bayar_hidden">
        <button type="submit" id="btn-bayar" class="btn-primary" disabled>Bayar</button>
      </form>
    </div>
  </div>
@endsection

@section('scripts')
  <script>window.STOK_ROTI = {{ $stok }};</script>
  <script src="{{ asset('js/kasir.js') }}"></script>
  <script>
    // sinkronkan input bayar visible -> hidden field sebelum submit
    document.getElementById('kasir-form').addEventListener('submit', () => {
      document.getElementById('bayar_hidden').value = document.getElementById('bayar_input').value || 0;
    });
    document.querySelectorAll('.sheet-root').forEach(s => {
      s.style.display = 'contents';
    });
  </script>
  <style>
    .sheet-root .sheet-backdrop, .sheet-root .sheet { display: none; }
    .sheet-root.open .sheet-backdrop, .sheet-root.open .sheet { display: block; }
  </style>
@endsection
