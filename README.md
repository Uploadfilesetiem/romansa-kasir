# Kasir Roti Bakar Romansa — versi Laravel (jalan langsung di HP)

Aplikasi ini pakai **Laravel + SQLite**. Tidak butuh internet, tidak butuh
hosting, tidak butuh database server — semua jalan lokal di HP kamu lewat
**Termux** (Android).

## Cara pasang di HP (Termux)

1. Install Termux dari F-Droid (disarankan, bukan dari Play Store versi lama).
2. Buka Termux, jalankan:
   ```
   pkg update && pkg upgrade
   pkg install php composer unzip -y
   ```
3. Pindahkan file zip ini ke penyimpanan HP, lalu di Termux:
   ```
   termux-setup-storage
   cd ~
   unzip /sdcard/Download/kasir-laravel.zip
   cd kasir-laravel
   ```
4. Install dependency Laravel (butuh internet SEKALI SAJA di langkah ini):
   ```
   composer install --no-dev --optimize-autoloader
   ```
5. Siapkan file konfigurasi & database:
   ```
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```
6. Jalankan servernya:
   ```
   php artisan serve --host=127.0.0.1 --port=8000
   ```
7. Buka browser HP (Chrome/dll), akses:
   ```
   http://127.0.0.1:8000
   ```

Setelah langkah 4 selesai, kamu **tidak butuh internet lagi** — semua
data tersimpan di file `database/database.sqlite` di HP kamu sendiri.

### Supaya server tidak mati saat layar HP terkunci
```
termux-wake-lock
```
Jalankan sebelum `php artisan serve`. Untuk melepas: `termux-wake-unlock`.

### Menjalankan lagi di lain waktu
Cukup buka Termux lalu:
```
cd ~/kasir-laravel && php artisan serve --host=127.0.0.1 --port=8000
```

## Fitur

- **Stok bersama (stok roti tawar)** — semua menu memakai satu stok yang
  sama (default 20), bisa diatur/di-setting kapan saja di halaman Stok
  ("+ Tambah Stok" untuk menambah, "Atur / Setting" untuk mengganti langsung).
- **Catatan selai/menu keluar** — halaman Laporan menampilkan rekap tiap
  menu yang laku beserta jumlah & omzetnya pada periode yang dipilih.
- **Uang cepat** — tombol +Rp20.000 / +Rp50.000 / +Rp100.000 / Uang Pas
  di form pembayaran tunai.
- **Tampilan & animasi** — desain kertas struk hangat (navy + gold + krem),
  bottom sheet ala aplikasi mobile, animasi masuk untuk kartu menu, tombol,
  toast notifikasi, dan centang animasi saat transaksi berhasil.
- Semua CSS & JS dibuat manual tanpa library eksternal (tidak ada CDN),
  jadi dijamin tetap jalan walau HP dalam mode pesawat / tanpa internet.

## Struktur penting

- `app/Http/Controllers/KasirController.php` — logika kasir & bayar
- `app/Http/Controllers/StokController.php` — kelola menu & stok bersama
- `app/Http/Controllers/LaporanController.php` — laporan & rekap selai
- `database/migrations/` — struktur tabel (produk, stok_master, transaksi, dst)
- `database/seeders/ProdukSeeder.php` — daftar menu awal (30 menu)
- `resources/views/` — semua tampilan (Blade)
- `public/css/app.css`, `public/js/*.js` — tampilan & animasi custom

## Reset data
Kalau ingin mulai dari nol lagi:
```
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate --seed
```
