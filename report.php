<?php
// FILE: report.php

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';
// ... (Kode HTML untuk report.php dari Tahap 6) ...
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Cobek Bakar | Laporan Penjualan</title>
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
                <a href="report.php" class="nav-item active"><i class="fas fa-history"></i> Riwayat Transaksi</a>
                <a href="report.php" class="nav-item active"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a>
            </nav>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION["nama_lengkap"]); ?></span>
                <button class="logout-btn" onclick="window.location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </aside>

        <main id="main-content">
            <header>
                <h1>Laporan Penjualan & Riwayat Transaksi</h1>
            </header>
            
            <section class="report-area">
                
                <div class="report-controls">
                    <label for="period-filter">Periode:</label>
                    <select id="period-filter">
                        <option value="today">Harian (Hari Ini)</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="custom">Rentang Kustom</option>
                    </select>

                    <label for="date-start">Mulai:</label>
                    <input type="date" id="date-start">

                    <label for="date-end">Sampai:</label>
                    <input type="date" id="date-end">

                    <button class="primary-btn" onclick="generateReport()" style="padding: 8px 15px;">
                        <i class="fas fa-filter"></i> Tampilkan
                    </button>
                    <button class="export-btn" onclick="printReport()">
                        <i class="fas fa-print"></i> Cetak Laporan
                    </button>
                </div>

                <div class="report-summary-card">
                    <p>Total Pendapatan (Periode Ini)</p>
                    <h3 id="total-income-display">Rp 0</h3>
                </div>

                <h2>Riwayat Transaksi Rinci</h2>
                
                <table class="data-table" id="transaction-history-table">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Waktu</th>
                            <th>Item</th>
                            <th>Kasir</th>
                            <th>Total Biaya</th>
                            <th>Status Struk</th>
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
</body>
</html>