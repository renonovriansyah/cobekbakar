# CobekBakar — Dokumentasi Proyek

Dokumentasi ini dibuat untuk memberi gambaran lengkap dan terstruktur tentang proyek "cobekbakar" (repo: renonovriansyah/cobekbakar). Tujuannya agar setiap modul, fitur, tampilan, dan alur logika dapat dipahami dengan mudah oleh pengembang baru maupun yang ingin mengembangkan proyek ini.

Ringkasan singkat saya lakukan: saya mengambil daftar file dari repository dan menyusun dokumentasi per modul/fitur, menjelaskan peran tiap berkas, alur data dan saran skema database serta langkah setup. Jika Anda mau, saya bisa melanjutkan dengan membuat file README/CONTRIBUTING yang siap di-commit atau menambahkan diagram ERD.

Daftar isi
- Gambaran Umum
- Teknologi & Komposisi Bahasa
- Persyaratan & Quick Start
- Struktur File & Penjelasan Per-File
- Skema Database yang Direkomendasikan
- Modul & Alur Logika
  - Autentikasi
  - Manajemen Produk
  - Transaksi / POS
  - Laporan & Cetak
  - Riwayat
- Endpoint / AJAX (format input/response)
- Tampilan (halaman utama)
- Keamanan & Best Practices
- Pengujian & Debugging
- Ide Pengembangan / Ekstensi
- Panduan Kontribusi singkat

---

GAMBARAN UMUM
Proyek ini tampak sebagai aplikasi kasir/penjualan sederhana berbasis PHP (server-side) dengan JavaScript di sisi klien dan styling CSS. Fitur inti meliputi: autentikasi, manajemen produk, transaksi/struk, laporan dan cetak laporan.

TEKNOLOGI & KOMPOSISI BAHASA
Berdasarkan analisis repository:
- PHP — mayoritas kode backend (sekitar 57% menurut repo metadata)
- JavaScript — logika interaktif / AJAX pada client (sekitar 27%)
- CSS — styling tampilan (sekitar 12%)

PERSYARATAN & QUICK START
Persyaratan minimal:
- Webserver dengan PHP (mis. Apache / Nginx + PHP 7.4+)
- MySQL / MariaDB
- Akses untuk men-deploy file PHP ke host
- Browser modern untuk UI (JS)

Langkah singkat setup:
1. Clone repo ke webroot.
2. Buat database MySQL baru (contoh: cobekbakar_db).
3. Update konfigurasi koneksi database di `config.php`.
4. Impor tabel yang direkomendasikan (lihat bagian Skema Database).
5. Akses `login.php` untuk masuk.

Catatan konfigurasi:
- `config.php` biasanya menyimpan koneksi database (host, user, pass, dbname). Pastikan file ini aman (jangan commit kredensial sensitif ke repo publik).

STRUKTUR FILE (LISTING UTAMA)
Berikut berkas-berkas yang terdapat di root repo beserta fungsi ringkasnya:

- cetak_laporan.php  
  - Fungsi: Kemungkinan menangani tampilan/aksi untuk mencetak laporan (mungkin menghasilkan halaman khusus/versi cetak laporan).
- config.php  
  - Fungsi: Konfigurasi umum aplikasi seperti koneksi database, inisialisasi session, konstanta aplikasi.
- get_products.php  
  - Fungsi: Endpoint AJAX untuk mengambil daftar produk (mungkin JSON). Digunakan oleh UI produk atau POS.
- get_reports.php  
  - Fungsi: Endpoint AJAX untuk mengambil data laporan (rentang tanggal, ringkasan penjualan).
- git/  
  - Fungsi: Folder bernama `git` (periksa isinya). Mungkin berisi artefak atau backup; sebaiknya di-review karena tidak umum untuk aplikasi produksi.
- history.php  
  - Fungsi: Menampilkan riwayat transaksi / log (bukti penjualan sebelumnya).
- index.php  
  - Fungsi: Halaman utama aplikasi — biasanya area POS/checkout.
- login.php  
  - Fungsi: Form login pengguna/admin.
- logout.php  
  - Fungsi: Proses logout, menghancurkan session dan redirect ke login.
- product.php  
  - Fungsi: Halaman manajemen produk (form tambah/edit, daftar produk).
- proses_login.php  
  - Fungsi: Handler server-side untuk memproses kredensial login (validasi, session).
- proses_produk.php  
  - Fungsi: Handler CRUD produk (tambah/edit/hapus) dari form `product.php`.
- proses_transaksi.php  
  - Fungsi: Handler pembuatan transaksi/penjualan — menyimpan transaksi dan itemnya.
- report.php  
  - Fungsi: Halaman laporan (UI untuk memilih rentang tanggal, menampilkan ringkasan).
- script-product.js  
  - Fungsi: Script client untuk logika produk: AJAX ke `get_products.php`, validasi form, dinamis UI.
- script-report.js  
  - Fungsi: Script client untuk memanggil `get_reports.php`, menghasilkan tabel/visualisasi laporan.
- script.js  
  - Fungsi: Script client umum (komponen UI, helper, event global).
- struk.php  
  - Fungsi: Mencetak struk/nota (layout struk, dipanggil setelah transaksi).
- style.css  
  - Fungsi: Styling global aplikasi.

CATATAN: Nama file memberi indikasi fungsi umum; sebaiknya buka masing-masing file untuk konfirmasi alur dan variabel yang digunakan.

SKEMA DATABASE YANG DIREKOMENDASIKAN
Berikut skema minimal yang cocok dengan fitur di repo. Sesuaikan sesuai implementasi file aktual.

1) users
- id INT PK AUTO_INCREMENT
- username VARCHAR(100) UNIQUE
- password VARCHAR(255)  (hash bcrypt)
- name VARCHAR(150)
- role ENUM('admin','kasir') DEFAULT 'kasir'
- created_at DATETIME
- last_login DATETIME

2) products
- id INT PK AUTO_INCREMENT
- code VARCHAR(50) UNIQUE
- name VARCHAR(255)
- price DECIMAL(12,2)
- stock INT
- unit VARCHAR(50) (opsional)
- created_at DATETIME
- updated_at DATETIME

3) transactions
- id INT PK AUTO_INCREMENT
- invoice VARCHAR(50) UNIQUE
- user_id INT FK -> users.id
- total DECIMAL(12,2)
- payment DECIMAL(12,2)
- change DECIMAL(12,2)
- created_at DATETIME

4) transaction_items
- id INT PK AUTO_INCREMENT
- transaction_id INT FK -> transactions.id
- product_id INT FK -> products.id
- qty INT
- price DECIMAL(12,2)
- subtotal DECIMAL(12,2)

5) reports (opsional / materialized view)
- id INT PK
- report_date DATE
- total_transactions INT
- total_revenue DECIMAL(12,2)
- ... atau cukup gunakan query agregasi pada transactions

6) history (opsional)
- bisa jadi alias `transactions` atau log aktivitas pengguna

MODUL & ALUR LOGIKA (DETAIL)

1) Autentikasi
- File terkait: login.php, proses_login.php, logout.php, config.php
- Alur:
  1. Pengguna buka `login.php` → masukkan username & password.
  2. Form dikirim ke `proses_login.php` → validasi input, query ke tabel `users`.
  3. Jika cocok, buat session (mis. $_SESSION['user_id'], $_SESSION['username']), redirect ke `index.php`.
  4. `logout.php` menghancurkan session dan redirect ke login.
- Keamanan:
  - Pastikan password disimpan sebagai hash (password_hash/password_verify).
  - Terapkan rate-limiting atau lockout pada percobaan login berulang.
  - Gunakan session_regenerate_id() setelah login sukses.

2) Manajemen Produk
- File terkait: product.php, proses_produk.php, get_products.php, script-product.js
- Fungsi:
  - product.php: UI daftar produk + form tambah/edit.
  - proses_produk.php: menerima data POST untuk create/update/delete, melakukan validasi dan query DB.
  - get_products.php: endpoint yang mengembalikan daftar produk (JSON) untuk mengisi UI (mis. select, autocomplete).
  - script-product.js: mengatur AJAX, event form, validasi client.
- Saran logika:
  - Saat tambah produk: cek duplikasi kode, validasi harga & stok.
  - Gunakan prepared statements untuk menghindari SQL injection.
  - Untuk operasi delete, pertimbangkan soft-delete (flag `is_deleted`) agar histori transaksi tetap utuh.

3) Transaksi / POS
- File terkait: index.php, proses_transaksi.php, struk.php, script.js
- Alur:
  1. Operator memilih produk (dari daftar atau search via `get_products.php`).
  2. Menentukan qty, menambahkan ke keranjang pada client-side.
  3. Saat pembayaran, data dikirim ke `proses_transaksi.php` (biasanya via POST) berisi daftar item, total, payment.
  4. `proses_transaksi.php` menyimpan transaksi di tabel `transactions` dan item di `transaction_items`, mengurangi `products.stock` jika ada manajemen stok.
  5. Setelah sukses, redirect atau tampilkan `struk.php` untuk printing.
- Pertimbangan:
  - Gunakan transaksi DB (BEGIN/COMMIT/ROLLBACK) untuk menjamin konsistensi.
  - Validasi stok sebelum menyimpan transaksi.
  - Buat nomor invoice unik (timestamp + random / increment per hari).

4) Laporan & Cetak
- File terkait: report.php, get_reports.php, script-report.js, cetak_laporan.php
- Fungsi:
  - report.php: UI untuk memilih rentang tanggal dan melihat ringkasan/daftar transaksi.
  - get_reports.php: endpoint untuk mengembalikan data laporan (JSON) berdasarkan parameter (start_date, end_date, dll).
  - cetak_laporan.php: menghasilkan format siap-cetak (HTML) dari data laporan.
  - script-report.js: men-trigger AJAX untuk mengambil data dan menampilkan tabel atau grafik.
- Saran:
  - Gunakan query agregasi untuk total, jumlah transaksi, produk terlaris.
  - Pastikan paginasi untuk dataset besar.

5) Riwayat
- File terkait: history.php
- Fungsi:
  - Menampilkan daftar transaksi sebelumnya (filter tanggal, invoice, user).
  - Bisa menghubungkan ke detail transaksi untuk melihat item dan mencetak ulang struk.

ENDPOINT / AJAX (FORMAT UMUM)
Berikut contoh format request/response yang umum:

- get_products.php
  - Request: GET?search=ayam&limit=10
  - Response (JSON):
    {
      "success": true,
      "data": [
        {"id":1, "code":"P001", "name":"Ayam Goreng", "price":"20000", "stock":10},
        ...
      ]
    }

- proses_produk.php (CRUD)
  - Create (POST): {action:"create", code, name, price, stock}
  - Update (POST): {action:"update", id, ...}
  - Response: {success: true, message: "Produk tersimpan"}

- proses_transaksi.php
  - Request (POST): {invoice, user_id, items: [{product_id, qty, price}], total, payment}
  - Response: {success:true, invoice: "INV-20251205-001", transaction_id: 123}

- get_reports.php
  - Request: GET?start=2025-01-01&end=2025-01-31
  - Response: {success:true, report: { total_transactions: 10, total_revenue: "500000" }, data: [...]}

TAMPILAN (HALAMAN UTAMA)
- login.php — halaman login sederhana
- index.php — kemungkinan halaman POS: area daftar produk, keranjang, total, tombol bayar
- product.php — manajemen produk (form & tabel)
- report.php — UI untuk melihat laporan & cetak
- struk.php / cetak_laporan.php — halaman yang dioptimalkan untuk printing

KEAMANAN & BEST PRACTICES
- Gunakan prepared statements / parameterized queries (PDO atau mysqli prepared statements).
- Jangan menyimpan password dalam plaintext — gunakan password_hash / password_verify.
- Amankan `config.php`: jangan commit kredensial; gunakan environment variables jika memungkinkan.
- Validasi dan sanitasi input di server-side meskipun ada validasi client-side JS.
- Proteksi terhadap CSRF untuk form yang mengubah data (token).
- Batasi hak akses: hanya admin dapat mengelola produk, kasir hanya transaksi.
- Periksa file/folder `git/` — hindari menyimpan info sensitif di direktori proyek publik.

PENGUJIAN & DEBUGGING
- Periksa log PHP / webserver untuk stack trace bila error.
- Aktifkan display_errors = Off di produksi, catat error ke file log.
- Gunakan sesi debug (var_dump / console.log) lokal untuk mengecek format AJAX.
- Buat data sample untuk pengujian produk & transaksi.

IDE PENGEMBANGAN / EKSTENSI
- Tambah manajemen stok lebih lengkap (pemasukan / pengeluaran).
- Tambah fitur multi-user & permissions (role-based access control).
- Tambah fitur laporan grafis (chart.js) pada report.php.
- Integrasi print via thermal printer (ESC/POS) atau export PDF.
- Tambah fitur backup database & export CSV.
- Refactor ke MVC / framework (Laravel / CodeIgniter) untuk skala besar.
- Menambahkan validasi & sanitasi input lebih lengkap, serta unit test.

PANDUAN KONTRIBUSI SINGKAT
- Fork repo & buat branch fitur: `feature/your-feature`.
- Tulis deskripsi perubahan di commit message.
- Sertakan contoh data/test case untuk fitur baru.
- Buka pull request dengan deskripsi dan langkah verifikasi.

PENUTUP (CATATAN SAYA)
Saya sudah mengambil daftar file dari repository dan menyusun dokumentasi ini yang mencakup gambaran tiap modul, alur logika, rekomendasi skema database, endpoint AJAX, dan langkah setup. Selanjutnya saya bisa:
- Membuat README.md yang diringkas dari dokumentasi ini dan/atau
- Menambahkan file DOCUMENTATION.md ke repo atau men-generate contoh SQL untuk skema database.

Jika Anda ingin saya membuatkan file README/SQL/ERD siap commit, beri tahu pilihan yang diinginkan dan saya akan melanjutkan pembuatan tersebut.
