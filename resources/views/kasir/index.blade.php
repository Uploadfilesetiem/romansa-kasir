@extends('layouts.app')
@section('title', 'Kasir')

@section('content')
  <div id="stok-banner" class="stok-banner mb-3">
    <span>Stok Roti Tawar</span>
    <span class="angka">
      <span id="stok-sisa">{{ $stok }} tersisa</span>
    </span>
  </div>

  <div class="kategori-scroll mb-3">
    <button type="button" class="pill active" data-kategori="Semua" onclick="filterKategori('Semua', this)">Semua</button>
    @foreach ($grouped as $g)
      <button type="button" class="pill" data-kategori="{{ $g['kategori'] }}" onclick="filterKategori('{{ addslashes($g['kategori']) }}', this)">{{ $g['kategori'] }}</button>
    @endforeach
  </div>

  @forelse ($grouped as $g)
    <div class="kategori-block" data-kategori="{{ $g['kategori'] }}">
      <div class="kategori-heading fw-bold my-2" style="color: #b45309;">{{ strtoupper($g['kategori']) }}</div>
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
            onclick="tambahItem({{ $p->id }}, '{{ addslashes($p->nama) }}', {{ $p->harga }})"
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

  <!-- Floating Cart Bar -->
  <div id="cart-bar" class="fixed-bottom p-3 bg-white border-top shadow-lg d-none" style="z-index: 1040; max-width: 500px; margin: 0 auto;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <span class="fw-bold" id="cart-count">0</span> Item Dipilih
      </div>
      <div class="fw-bold text-success fs-5" id="cart-total">Rp0</div>
    </div>
    <button class="btn btn-warning w-100 fw-bold py-2 text-dark" onclick="bukaModalKeranjang()">Lihat Pesanan / Bayar</button>
  </div>

  <!-- Modal Keranjang & Catatan -->
  <div class="modal fade" id="modalKeranjang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Detail Pesanan Kasir</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="cart-items-list" class="mb-3"></div>
          <hr>
          <div class="mb-3">
            <label class="form-label fw-bold">Metode Pembayaran</label>
            <select id="metode_pembayaran" class="form-select">
              <option value="CASH">Tunai (CASH)</option>
              <option value="QRIS">QRIS</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Uang Bayar (Rp)</label>
            <input type="number" id="bayar_input" class="form-control" placeholder="Masukkan nominal uang">
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-success fw-bold" onclick="prosesBayar()">Proses Transaksi</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let cart = [];

    function filterKategori(kat, btn) {
      document.querySelectorAll('.kategori-scroll .pill').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      
      document.querySelectorAll('.kategori-block').forEach(block => {
        if (kat === 'Semua' || block.getAttribute('data-kategori') === kat) {
          block.style.display = 'block';
        } else {
          block.style.display = 'none';
        }
      });
    }

    function tambahItem(id, nama, harga) {
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
      if (totalCount > 0) {
        cartBar.classList.remove('d-none');
        document.getElementById('cart-count').innerText = totalCount;
        document.getElementById('cart-total').innerText = 'Rp' + totalPrice.toLocaleString('id-ID');
      } else {
        cartBar.classList.add('d-none');
      }
    }

    function bukaModalKeranjang() {
      let html = '';
      cart.forEach((item, index) => {
        html += `
          <div class="border-bottom pb-2 mb-2">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold">${item.nama_produk}</span>
              <span class="text-primary fw-bold">Rp${(item.harga * item.qty).toLocaleString('id-ID')}</span>
            </div>
            <div class="d-flex align-items-center gap-2 mt-1">
              <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="ubahQty(${index}, -1)">-</button>
              <span class="fw-bold">${item.qty}</span>
              <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" onclick="ubahQty(${index}, 1)">+</button>
            </div>
            <input type="text" class="form-control form-control-sm mt-2" placeholder="Catatan selai/topping..." value="${item.catatan}" onchange="updateCatatan(${index}, this.value)">
          </div>
        `;
      });

      document.getElementById('cart-items-list').innerHTML = html;
      let modal = new bootstrap.Modal(document.getElementById('modalKeranjang'));
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
        bootstrap.Modal.getInstance(document.getElementById('modalKeranjang')).hide();
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
        return alert('Uang bayar kurang dari total Rp' + total.toLocaleString('id-ID'));
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
          alert('Terjadi kesalahan!');
        }
      })
      .catch(err => alert('Error koneksi: ' + err));
    }
  </script>
@endsection
