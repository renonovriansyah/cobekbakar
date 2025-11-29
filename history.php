<?php
// FILE: history.php

session_start();

// Proteksi akses
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Cobek Bakar | Riwayat Transaksi</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="app-wrapper">
        
        <aside id="sidebar">
            <h2 class="logo">COBEK BAKAR</h2>
            <nav class="main-nav">
                <a href="index.php" class="nav-item"><i class="fas fa-cash-register"></i> Input Pesanan</a>
                <a href="product.php" class="nav-item"><i class="fas fa-box"></i> Kelola Produk</a>
                <a href="history.php" class="nav-item active"><i class="fas fa-history"></i> Riwayat Transaksi</a>
                <a href="report.php" class="nav-item"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a>
            </nav>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION["nama_lengkap"]); ?></span>
                <button class="logout-btn" onclick="window.location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </aside>

        <main id="main-content">
            <header>
                <h1>Riwayat Transaksi</h1>
            </header>
            
            <section class="report-area">
                
                <div class="report-controls">
                    <label for="period-filter">Filter Cepat:</label>
                    <select id="period-filter" onchange="generateHistory(1)">
                        <option value="today">Hari Ini</option>
                        <option value="weekly">7 Hari Terakhir</option>
                        <option value="monthly">30 Hari Terakhir</option>
                        <option value="custom">Rentang Kustom</option>
                    </select>

                    <label for="date-start">Mulai:</label>
                    <input type="date" id="date-start" onchange="generateHistory(1)">

                    <label for="date-end">Sampai:</label>
                    <input type="date" id="date-end" onchange="generateHistory(1)">
                </div>

                <h2>Daftar Transaksi Rinci</h2>
                
                <table class="data-table" id="transaction-history-table">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Waktu</th>
                            <th>Item</th>
                            <th>Kasir</th>
                            <th>Total Biaya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="transaction-table-body">
                        </tbody>
                </table>
                
                <div class="pagination">
                    </div>
            </section>
        </main>
    </div>
    
    <script src="script-report.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // Inisiasi fungsi riwayat transaksi saat halaman dimuat
             generateHistory(); 
        });
    </script>
</body>
</html>