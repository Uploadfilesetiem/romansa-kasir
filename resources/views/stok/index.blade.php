@extends('layouts.app')
@section('title', 'Stok & Menu')

@section('content')
  <div class="page-title">Stok & Menu</div>

  <div class="card card-pad" style="margin-bottom:18px;">
    <div class="flex-between">
      <div>
        <div class="section-title">Stok Roti Tawar (Stok Bersama)</div>
        <div class="section-sub">Semua menu ikut mengurangi stok ini, karena setiap menu memakai 1 roti tawar.</div>
        <div class="badge-stok {{ $stok <= 5 ? 'low' : '' }}">{{ $stok }} <small>lembar/porsi</small></div>
      </div>
      <div style="display:flex; flex-direction:column; gap:6px;">
        <button type="button" class="btn-small gold" data-open-sheet="tambah-stok">+ Tambah Stok</button>
        <button type="button" class="btn-small ghost" data-open-sheet="atur-stok">Atur / Setting</button>
      </div>
    </div>
  </div>

  <div class="flex-between" style="margin-bottom:10px;">
    <div class="section-title">Daftar Menu</div>
    <button type="button" class="btn-small navy" data-open-sheet="tambah-menu">+ Tambah Menu</button>
  </div>

  <div class="card table-wrap">
    <table class="list">
      <thead>
        <tr><th>Nama</th><th>Kategori</th><th class="text-right">Harga</th><th class="text-center">Aksi</th></tr>
      </thead>
      <tbody>
        @forelse ($produk as $p)
          <tr>
            <td>{{ $p->nama }}</td>
            <td>{{ $p->kategori }}</td>
            <td class="text-right">Rp{{ number_format($p->harga, 0, ',', '.') }}</td>
            <td class="text-center">
              <button type="button" class="btn-mini edit" data-open-sheet="edit-{{ $p->id }}">Edit</button>
              <form method="POST" action="{{ route('stok.menu.destroy', $p) }}" style="display:inline" data-confirm="Hapus menu ini?">
                @csrf @method('DELETE')
                <button type="submit" class="btn-mini hapus">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center" style="padding:20px;color:#8a8071;">Belum ada menu.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Sheet: Tambah Stok --}}
  <div id="sheet-tambah-stok" class="sheet-root">
    <div class="sheet-backdrop" data-close-sheet></div>
    <div class="sheet">
      <div class="sheet-handle"></div>
      <h3>Tambah Stok Roti Tawar</h3>
      <div class="sub">Stok saat ini: {{ $stok }}</div>
      <form method="POST" action="{{ route('stok.tambah') }}">
        @csrf
        <label class="field-label">Jumlah Tambahan</label>
        <input type="number" name="jumlah" class="input" required min="1">
        <label class="field-label">Keterangan</label>
        <input type="text" name="keterangan" class="input" value="Pembelian roti tawar">
        <button type="submit" class="btn-primary">Simpan</button>
      </form>
    </div>
  </div>

  {{-- Sheet: Atur Stok --}}
  <div id="sheet-atur-stok" class="sheet-root">
    <div class="sheet-backdrop" data-close-sheet></div>
    <div class="sheet">
      <div class="sheet-handle"></div>
      <h3>Atur / Setting Stok</h3>
      <div class="sub">Gunakan untuk mengatur ulang nilai stok, mis. saat buka toko (contoh: 20), atau setelah stock opname.</div>
      <form method="POST" action="{{ route('stok.atur') }}">
        @csrf
        <label class="field-label">Jumlah Stok</label>
        <input type="number" name="stok" class="input" required min="0" value="{{ $stok }}">
        <button type="submit" class="btn-primary">Simpan</button>
      </form>
    </div>
  </div>

  {{-- Sheet: Tambah Menu --}}
  <div id="sheet-tambah-menu" class="sheet-root">
    <div class="sheet-backdrop" data-close-sheet></div>
    <div class="sheet">
      <div class="sheet-handle"></div>
      <h3>Tambah Menu Baru</h3>
      <form method="POST" action="{{ route('stok.menu.store') }}">
        @csrf
        <label class="field-label">Nama Menu</label>
        <input type="text" name="nama" class="input" required>
        <label class="field-label">Kategori</label>
        <select name="kategori" class="input">
          <option>Campur</option><option>Istimewa</option><option>Kombinasi</option><option>Gurih</option><option>Lainnya</option>
        </select>
        <label class="field-label">Harga</label>
        <input type="number" name="harga" class="input" required min="0">
        <div class="section-sub" style="margin-top:8px;">Menu baru otomatis memakai stok bersama di atas.</div>
        <button type="submit" class="btn-primary">Tambah Menu</button>
      </form>
    </div>
  </div>

  {{-- Sheet: Edit Menu (per produk) --}}
  @foreach ($produk as $p)
    <div id="sheet-edit-{{ $p->id }}" class="sheet-root">
      <div class="sheet-backdrop" data-close-sheet></div>
      <div class="sheet">
        <div class="sheet-handle"></div>
        <h3>Edit Menu</h3>
        <form method="POST" action="{{ route('stok.menu.update', $p) }}">
          @csrf @method('PUT')
          <label class="field-label">Nama Menu</label>
          <input type="text" name="nama" class="input" value="{{ $p->nama }}" required>
          <label class="field-label">Kategori</label>
          <select name="kategori" class="input">
            @foreach (['Campur','Istimewa','Kombinasi','Gurih','Lainnya'] as $k)
              <option value="{{ $k }}" @selected($p->kategori === $k)>{{ $k }}</option>
            @endforeach
          </select>
          <label class="field-label">Harga</label>
          <input type="number" name="harga" class="input" value="{{ $p->harga }}" required min="0">
          <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </form>
      </div>
    </div>
  @endforeach
@endsection

@section('scripts')
  <style>
    .sheet-root .sheet-backdrop, .sheet-root .sheet { display: none; }
    .sheet-root.open .sheet-backdrop, .sheet-root.open .sheet { display: block; }
  </style>
@endsection
