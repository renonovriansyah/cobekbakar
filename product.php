<?php
// FILE: product.php

session_start();

// Proteksi akses
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Sertakan config untuk koneksi database (walaupun hanya digunakan oleh proses_produk.php, ini untuk konsistensi)
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Cobek Bakar | Kelola Produk</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="app-wrapper">
        
        <aside id="sidebar">
            <h2 class="logo">COBEK BAKAR</h2>
            <nav class="main-nav">
                <a href="index.php" class="nav-item"><i class="fas fa-cash-register"></i> Input Pesanan</a>
                <a href="product.php" class="nav-item active"><i class="fas fa-box"></i> Kelola Produk</a>
                <a href="history.php" class="nav-item"><i class="fas fa-history"></i> Riwayat Transaksi</a>
                <a href="report.php" class="nav-item"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a>
            </nav>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION["nama_lengkap"]); ?></span>
                <button class="logout-btn" onclick="window.location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </aside>

        <main id="main-content">
            <header>
                <h1>Kelola Data Produk</h1>
            </header>
            
            <section class="management-area">
                
                <div class="controls-bar">
                    <button class="primary-btn" onclick="openProductModal('add')">
                        <i class="fas fa-plus"></i> Tambah Produk Baru
                    </button>
                    <input type="text" id="search-product-input" placeholder="Cari Nama Produk..." class="search-input">
                </div>

                <table id="product-list-table" class="data-table">
                    <thead>
                        <tr>
                            <th>ID Produk</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th> 
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Diskon</th> 
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        </tbody>
                </table>
            </section>
        </main>
    </div>

    <div id="product-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeProductModal()">&times;</span>
            <h2 id="modal-title">Tambah/Ubah Produk</h2>
            <form id="product-form">
                <input type="hidden" id="product-id" name="id_produk"> <div class="input-group">
                    <label for="product-name">Nama Produk</label>
                    <input type="text" id="product-name" name="nama_produk" required>
                </div>
                <div class="input-group">
                <label for="product-category">Kategori Produk</label>
                    <select id="product-category" name="kategori" required>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Tambahan">Tambahan</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="product-price">Harga Jual (Rp)</label>
                    <input type="number" id="product-price" name="harga" required min="100">
                </div>
                <div class="input-group">
                    <label for="product-discount">Diskon Jual (%)</label>
                    <input type="number" id="product-discount" name="diskon_jual" required min="0" max="100" value="0">
                </div>
                <div class="input-group">
                    <label for="product-stock">Stok</label>
                    <input type="number" id="product-stock" name="stok" required min="0">
                </div>
                <button type="submit" class="primary-btn" id="modal-submit-btn">Simpan Data</button>
            </form>
        </div>
    </div>

    <script src="script-product.js"></script>
</body>
</html>