<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Kasir') — Roti Bakar Romansa</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

  <div class="toast-wrap">
    @if (session('sukses'))
      <div class="toast">✓ {{ session('sukses') }}</div>
    @endif
    @if ($errors->any())
      <div class="toast err">{{ $errors->first() }}</div>
    @endif
  </div>

  <div class="app-shell">
    <div class="app-header">
      <div>
        <div class="brand">Roti Bakar Romansa</div>
        <div class="tagline">Hangat di Setiap Cerita</div>
      </div>
    </div>

    @yield('content')
  </div>

  <nav class="bottom-nav">
    <a href="{{ route('kasir.index') }}" class="{{ request()->routeIs('kasir.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-6 9 6v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/></svg>
      Kasir
    </a>
    <a href="{{ route('stok.index') }}" class="{{ request()->routeIs('stok.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
      Stok
    </a>
    <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18M7 15l4-6 3 4 5-8"/></svg>
      Laporan
    </a>
  </nav>

  <script src="{{ asset('js/app.js') }}"></script>
  @yield('scripts')
</body>
</html>
