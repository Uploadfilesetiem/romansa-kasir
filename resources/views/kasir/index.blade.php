@extends('layouts.app')
@section('title', 'Kasir')

@section('content')
<style>
  /* Styling Card & Animasi Klik */
  .menu-card {
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    background: #ffffff;
    padding: 12px;
    text-align: left;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    cursor: pointer;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
  }
  .menu-card:active {
    transform: scale(0.95);
    background-color: #fef3c7 !important;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.4);
  }
  .menu-card .nama {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
    line-height: 1.3;
  }
  .menu-card .harga {
    color: #d97706;
    font-weight: 700;
    font-size: 0.9rem;
    margin-top: 6px;
  }

  /* Sticky Cart Bar */
  .cart-bar-custom {
    position: fixed;
    bottom: 60px;
    left: 50%;
    transform: translateX(-50%);
    width: 92%;
    max-width: 480px;
    background: #1e293b;
    color: #fff;
    border-radius: 16px;
    padding: 12px 18px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
    z-index: 1030;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: slideUp 0.3s ease-out;
  }
  @keyframes slideUp {
    from { transform: translate(-50%, 100%); opacity: 0; }
    to { transform: translate(-50%, 0); opacity: 1; }
  }

  /* Badge Animasi Tambah Item */
  .badge-pop {
    animation: popScale 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
  @keyframes popScale {
    0% { transform: scale(1); }
    50% { transform: scale(1.4); }
    100% { transform: scale(1); }
  }
</style>

<div class="container pb-5 mb-5">
  <div id="stok-banner" class="stok-banner mb-3 p-3 bg-white rounded-3 shadow-sm d-flex justify-content-between align-items-center">
    <span class="fw-bold text-secondary">Stok Roti Tawar</span>
    <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill fw-bold">
      <span id="stok-sisa">{{ $stok }} tersisa</span>
    </span>
  </div>

  <div class="kategori-scroll mb-3 d-flex gap-2 overflow-auto pb-2" style="white-space: nowrap;">
    <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 active" data-kategori="Semua" onclick="filterKategori('Semua', this)">Semua</button>
    @foreach ($grouped as $g)
      <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-kategori="{{ $g['kategori'] }}" onclick="filterKategori('{{ addslashes($g['kategori']) }}', this)">{{ $g['kategori'] }}</button>
    @endforeach
  </div>

  @forelse ($grouped as $g)
    <div class="kategori-block mb-4" data-kategori="{{ $g['kategori'] }}">
      <div class="kategori-heading fw-bold mb-2 text-uppercase text-amber-700" style="color: #b45309; letter-spacing: 0.5px; font-size: 0.85rem;">{{ $g['kategori'] }}</div>
      <div class="row g-2">
        @foreach ($g['items'] as $p)
          <div class="col-6 col-md-4">
            <div
              class="menu-card h-100"
              data-id="{{ $p->id }}"
              onclick="tambahItem({{ $p->id }}, '{{ addslashes($p->nama) }}', {{ $p->harga }}, this)"
            >
              <div class="nama">{{ $p->nama }}</div>
              <div class="harga">Rp{{ number_format($p->harga, 0, ',', '.') }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @empty
    <p class="text-muted text-center py-4">Belum ada menu.</p>
  @endforelse
</div>

<!-- Floating Cart Bar Modern -->
<div id="cart-bar" class="cart-bar-custom d-none">
  <div>
    <div class="small text-light text-opacity-75">Pesanan Kasir</div>
    <div class="fw-bold fs-6">
      <span id="cart-count" class="badge bg-warning text-dark me-1">0</span> Item
      <span class="ms-2 text-warning fw-bold" id="cart-total">Rp0</span>
    </div>
  </div>
  <button class="btn btn-warning fw-bold text-dark rounded-3 px-3 py-2" onclick="bukaModalKeranjang()">
    Bayar & Struk &rarr;
  </button>
</div>

<!-- Modal Detail Pesanan -->
<div class="modal fade" id="modalKeranjang" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
    <div class="modal-content rounded-4 border-0 shadow-lg">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold text-dark">Detail Pesanan & Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <div id="cart-items-list" class="mb-3" style="max-height: 280px; overflow-y: auto;"></div>
        
        <div class="bg-light p-3 rounded-3 mb-3">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-secondary fw-bold">Total Tagihan</span>
            <span class="fw-bold text-success fs-5" id="modal-cart-total">Rp0</span>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-bold text-secondary">Metode Pembayaran</label>
            <select id="metode_pembayaran" class="form-select form-select-sm fw-bold">
              <option value="CASH">Tunai (CASH)</option>
              <option value="QRIS">QRIS</option>
            </select>
          </div>
          <div>
            <label class="form-label small fw-bold text-secondary">Uang Diterima (Rp)</label>
            <input type="number" id="bayar_input" class="form-control form-control-sm fw-bold fs-6" placeholder="Contoh: 50000">
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 p-3 pt-0">
        <button type="button" class="btn btn-light w-100 fw-bold py-2 mb-2" data-bs-dismiss="modal">Tambah Menu Lagi</button>
        <button type="button" class="btn btn-warning w-100 fw-bold py-2 text-dark fs-6" onclick="prosesBayar()">SELESAIKAN TRANSAKSI</button>
      </div>
    </div>
  </div>
</div>

<script>
  let cart = [];

  function filterKategori(kat, btn) {
    document.querySelectorAll('.kategori-scroll button').forEach(b => {
      b.classList.remove('btn-dark', 'active');
      b.classList.add('btn-outline-secondary');
    });
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-dark', 'active');
    
    document.querySelectorAll('.kategori-block').forEach(block => {
      if (kat === 'Semua' || block.getAttribute('data-kategori') === kat) {
        block.style.display = 'block';
      } else {
        block.style.display = 'none';
      }
    });
  }

  function tambahItem(id, nama, harga, el) {
    // Animasi visual klik pada card
    el.classList.add('badge-pop');
    setTimeout(() => el.classList.remove('badge-pop'), 200);

    let existing = cart.find(item => item.id === id);
    if (existing) {
      existing.qty++;
    } else {
      cart.push({ id: id, nama_produk: nama, harga: harga, qty: 1, catatan: '' });
    }
    updateCartUI();
  }

  function updateCartUI() {
    let totalCount = 0;
    let totalPrice = 0;

    cart.forEach(item => {
      totalCount += item.qty;
      totalPrice += (item.harga * item.qty);
    });

    const cartBar = document.getElementById('cart-bar');
    const cartCount = document.getElementById('cart-count');
    
    if (totalCount > 0) {
      cartBar.classList.remove('d-none');
      cartCount.innerText = totalCount;
      cartCount.classList.add('badge-pop');
      setTimeout(() => cartCount.classList.remove('badge-pop'), 200);
      document.getElementById('cart-total').innerText = 'Rp' + totalPrice.toLocaleString('id-ID');
      document.getElementById('modal-cart-total').innerText = 'Rp' + totalPrice.toLocaleString('id-ID');
    } else {
      cartBar.classList.add('d-none');
    }
  }

  function bukaModalKeranjang() {
    let html = '';
    cart.forEach((item, index) => {
      html += `
        <div class="card mb-2 border-0 bg-light p-2 rounded-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-bold text-dark">${item.nama_produk}</div>
              <div class="small text-muted">Rp${item.harga.toLocaleString('id-ID')} x ${item.qty}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-2 py-0" onclick="ubahQty(${index}, -1)">-</button>
              <span class="fw-bold px-1">${item.qty}</span>
              <button type="button" class="btn btn-sm btn-outline-success fw-bold px-2 py-0" onclick="ubahQty(${index}, 1)">+</button>
            </div>
          </div>
          <input type="text" class="form-control form-control-sm mt-2" placeholder="Catatan selai/topping (opsional)..." value="${item.catatan}" onchange="updateCatatan(${index}, this.value)">
        </div>
      `;
    });

    document.getElementById('cart-items-list').innerHTML = html;
    let modalEl = document.getElementById('modalKeranjang');
    let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }

  function ubahQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) {
      cart.splice(index, 1);
    }
    updateCartUI();
    if (cart.length > 0) {
      bukaModalKeranjang();
    } else {
      let modalEl = document.getElementById('modalKeranjang');
      let modal = bootstrap.Modal.getInstance(modalEl);
      if(modal) modal.hide();
    }
  }

  function updateCatatan(index, val) {
    cart[index].catatan = val;
  }

  function prosesBayar() {
    if (cart.length === 0) return alert('Keranjang kosong!');
    
    let total = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
    let bayarInput = parseFloat(document.getElementById('bayar_input').value);
    let metode = document.getElementById('metode_pembayaran').value;

    if (isNaN(bayarInput) || bayarInput < total) {
      return alert('Uang bayar kurang! Total belanja Rp' + total.toLocaleString('id-ID'));
    }

    fetch('{{ route("kasir.store") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        items: cart,
        metode_pembayaran: metode,
        bayar: bayarInput
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        window.location.href = data.redirect;
      } else {
        alert('Terjadi kesalahan saat memproses transaksi!');
      }
    })
    .catch(err => alert('Koneksi terputus: ' + err));
  }
</script>
