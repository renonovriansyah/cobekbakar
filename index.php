<?php
// FILE: index.php

session_start();

// Jika pengguna tidak login, redirect ke halaman login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Sertakan config untuk menggunakan koneksi database
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Cobek Bakar | Input Pesanan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="app-wrapper">
        
        <aside id="sidebar">
            <h2 class="logo">COBEK BAKAR</h2>
            <nav class="main-nav">
                <a href="index.php" class="nav-item active"><i class="fas fa-cash-register"></i> Input Pesanan</a>
                <a href="product.php" class="nav-item"><i class="fas fa-box"></i> Kelola Produk</a>
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
                <h1>Input Pesanan Baru</h1>
            </header>
            
            <section class="transaction-area">
                
                <div class="product-selection">
                    <div class="search-bar-container">
                        <input type="text" id="product-search" placeholder="Cari Produk..." autofocus>
                        <i class="fas fa-search"></i>
                    </div>

                    <div class="product-grid" id="product-grid">
                        </div>
                </div>

                <div class="transaction-detail">
                    <h2>Keranjang Pesanan</h2>
                    <table id="cart-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items-body">
                            </tbody>
                    </table>
                    
                    <div class="summary-box">
                    <p>Subtotal:</p> <p id="subtotal-display">Rp 0</p>
    
                    <!-- WADAH PERMANEN UNTUK TOTAL DISKON -->
                        <div id="total-diskon-row">
                            <!-- Konten ini akan diisi oleh JS -->
                        </div>
                        
                        <h3 class="grand-total-label">TOTAL AKHIR:</h3> 
                        <h3 id="grand-total-display">Rp 0</h3>
                    </div>

                    <div class="payment-section">
                        <h4>Pembayaran</h4>
                        <input type="number" id="cash-input" placeholder="Uang Diterima (Rp)" oninput="calculateChange()">
                        
                        <div class="change-display">
                            <p>Kembalian:</p> <p id="change-display">Rp 0</p>
                        </div>

                        <button id="process-payment-btn" class="primary-btn" onclick="processPayment()">
                            <i class="fas fa-check-circle"></i> Proses Pembayaran
                        </button>
                        <button id="clear-cart-btn" class="secondary-btn" onclick="clearCart()">
                            <i class="fas fa-trash-alt"></i> Batalkan Semua
                        </button>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <script src="script.js"></script> 
</body>
</html>