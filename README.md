# Cobek Bakar — Aplikasi Kasir Restoran

Versi dokumentasi: 2025-12-06

---

## Ringkasan Singkat
Cobek Bakar adalah aplikasi kasir berbasis web untuk restoran/warung yang memudahkan proses pemesanan, pembayaran, dan pencetakan struk serta laporan penjualan. Aplikasi dibuat agar mudah dijalankan secara lokal maupun dideploy ke server.

---

## Tujuan & Masalah yang Diselesaikan
- Menyediakan sistem kasir sederhana untuk usaha makanan (restoran, warung, katering).
- Menyederhanakan proses pencatatan pesanan dan pembayaran.
- Mempercepat pembuatan struk transaksi untuk pelanggan.
- Menghasilkan laporan penjualan harian/bulanan untuk keperluan pembukuan.
- Mengurangi kesalahan perhitungan manual dan mempermudah arsip data transaksi.

---

## Fitur Utama
- Manajemen menu (tambah, edit, hapus).
- Pemrosesan pesanan (tambah item, hitung total, pangkas diskon).
- Manajemen meja / tipe transaksi (opsional).
- Cetak struk transaksi (thermal printer / print via browser).
- Cetak laporan penjualan (rentang tanggal, export PDF/print).
- Login multi-peran (contoh: Kasir, Admin).

---

## Teknologi yang Digunakan
- Bahasa pemrograman: PHP (file entry seperti `login.php` pada deploy).
- Basis data: MySQL / MariaDB.
- Frontend: HTML, CSS, JavaScript (library ringan bila ada).
- Kemungkinan library cetak/ export: FPDF/TCPDF untuk PDF, atau mekanisme print HTML untuk printing ke printer lokal.
- Web server: Apache / Nginx atau PHP built-in server untuk pengujian lokal.

Catatan: Sesuaikan detail teknologi menurut isi repository (lihat file konfigurasi seperti `config.php`, `db/` atau `vendor/` jika tersedia).

---

## Struktur Umum Proyek (Contoh)
- / — root aplikasi (mungkin berisi `index.php`, `login.php`)
- /assets — CSS, JS, gambar
- /pages atau /app — halaman aplikasi
- /db atau /sql — file dump database (.sql)
- /config — file konfigurasi koneksi database (mis: `config.php`)
- /vendor — dependensi (jika menggunakan composer)

Periksa struktur aktual di repo untuk penamaan dan lokasi file yang tepat.

---

## Persiapan & Prasyarat (Local)
- PHP 7.4 atau lebih baru (disarankan PHP 8+)
- MySQL / MariaDB
- Web server (XAMPP, MAMP, LAMP) atau PHP built-in server
- (Opsional) Composer jika proyek memakai dependensi PHP

---

## Cara Menjalankan Aplikasi di Localhost

Langkah umum (cara ini aman digunakan walau struktur repo sedikit berbeda — sesuaikan path jika perlu):

1. Clone repository
   ```
   git clone https://github.com/renonovriansyah/cobekbakar.git
   cd cobekbakar
   ```

2. Letakkan di web root (jika menggunakan XAMPP):
   - Salin/move folder `cobekbakar` ke `C:\xampp\htdocs\` (Windows) atau `/var/www/html/` (Linux).
   - Atau jalankan PHP built-in server dari root proyek:
     ```
     php -S localhost:8000
     ```
     lalu buka http://localhost:8000/login.php (atau `index.php` sesuai file entry).

3. Buat database di MySQL
   - Login ke MySQL:
     ```
     mysql -u root -p
     ```
   - Buat database, contoh:
     ```
     CREATE DATABASE cobekbakar;
     ```

4. Import struktur dan data database
   - Cari file SQL di repo (periksa folder `db/`, `sql/`, atau file bernama `cobekbakar.sql`, `database.sql`, dll).
   - Jika ada file SQL, import:
     ```
     mysql -u root -p cobekbakar < path/to/cobekbakar.sql
     ```
   - Jika tidak ada file SQL, buat tabel sesuai dokumentasi internal atau minta file dump dari pengembang.

5. Konfigurasi koneksi database
   - Buka file konfigurasi (cari `config.php`, `db_config.php`, atau file serupa).
   - Sesuaikan parameter:
     - DB_HOST (mis: localhost)
     - DB_NAME (mis: cobekbakar)
     - DB_USER (mis: root)
     - DB_PASS (mis: [kosong atau kata sandi])

6. Buka aplikasi di browser
   - Jika menggunakan XAMPP: http://localhost/cobekbakar/login.php
   - Jika menggunakan PHP built-in server: http://localhost:8000/login.php

Catatan: Nama file entry dan path dapat berbeda. Periksa root repo untuk file seperti `login.php` atau `index.php`.

---

## Akun Demo
Untuk pengujian cepat, gunakan akun demo berikut:
- Peran: Kasir
- Username: `admin`
- Password: `admincobekbakar`

Catatan: Jika akun demo tidak tersedia setelah import DB, periksa tabel pengguna (`users`, `kasir`, atau nama serupa) dan tambahkan akun secara manual atau lakukan reset password sesuai skema aplikasi.

---

## Link Deployment (Demo Online)
Aplikasi sudah dideploy di:
https://websitecobekbakar.wuaze.com/login.php

Gunakan akun demo di atas untuk mengakses area kasir.

---

## Catatan Tambahan — Cetak Struk & Cetak Laporan

1. Cetak Struk (Receipt Printing)
   - Metode sederhana: gunakan cetak HTML via `window.print()` dari halaman struk yang diformat khusus (cocok untuk printer biasa atau virtual PDF).
   - Untuk thermal printer (ESC/POS):
     - Gunakan library di server yang menghasilkan perintah ESC/POS (mis: PHP-Escpos) atau gunakan aplikasi perantara (middleware) untuk mengirim perintah ke printer lokal.
     - Pastikan printer terpasang pada mesin kasir dan driver sudah benar.
   - Format struk:
     - Sertakan nama usaha, alamat, waktu transaksi, nomor transaksi, daftar item (qty, harga, subtotal), total, pembayaran, dan terima kasih.
     - Sesuaikan lebar struk (biasanya 58mm/80mm) pada CSS/HTML.

2. Cetak Laporan
   - Laporan harian/bulanan: buat halaman yang menerima filter tanggal (dari — sampai).
   - Export/Print:
     - Opsi 1: Tampilkan laporan dalam HTML tabel dan gunakan `window.print()` untuk mencetak.
     - Opsi 2: Generate PDF menggunakan library seperti FPDF, TCPDF atau Dompdf, lalu beri tombol "Download PDF" / "Print".
   - Pastikan paginasi dan ringkasan (total penjualan, jumlah transaksi, rata-rata transaksi) tersedia.

3. Tips & Troubleshooting
   - Jika hasil cetak terpotong, sesuaikan CSS untuk media print (@media print) dan margin.
   - Untuk thermal printer yang terhubung melalui USB pada mesin Windows, coba gunakan driver yang menyediakan port virtual atau software print server.
   - Jika menggunakan sistem POS hybrid (browser + native print), pertimbangkan middleware kecil (node.js) yang menerima perintah HTTP dari aplikasi web dan meneruskan ke driver printer lokal.

---

## Keamanan & Backup
- Ubah password default setelah instalasi.
- Batasi akses ke panel admin (IP whitelist / autentikasi 2 faktor bila diperlukan).
- Buat backup rutin database (mysqldump) dan simpan di lokasi terpisah.
  ```
  mysqldump -u root -p cobekbakar > cobekbakar_backup_YYYYMMDD.sql
  ```
- Pastikan file konfigurasi tidak dapat diakses publik (sesuaikan permission).

---

## Pengembangan & Kontribusi
- Untuk menambah fitur baru, buat branch terpisah dan ajukan pull request.
- Sertakan deskripsi fitur, langkah testing, dan migrasi DB (jika ada).
- Dokumentasikan perubahan di README atau CHANGELOG.

---

## Kontak / Bantuan
Jika perlu bantuan lebih lanjut (konfigurasi DB, mencari file SQL, atau menyesuaikan skrip cetak), beri tahu detail masalahnya (error log, struktur folder, nama file konfigurasi) dan saya akan bantu langkah demi langkah.

---

Terima kasih telah menggunakan Cobek Bakar — semoga dokumentasi ini membantu Anda menjalankan dan memahami aplikasi dengan cepat.
