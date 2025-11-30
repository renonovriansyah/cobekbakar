<?php
// FILE: report.php

session_start();

// Proteksi akses
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Sertakan config, walaupun data diolah oleh AJAX, ini memastikan koneksi dasar tersedia
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Cobek Bakar | Laporan Penjualan</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* CONTAINER UNTUK METRIK KPI */
        .kpi-cards-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap; 
        }

        .report-summary-card {
            flex: 1;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            min-width: 250px;
        }

        .report-summary-card p {
            font-size: 0.9em;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .report-summary-card h3 {
            font-size: 1.8em;
            font-weight: 700;
        }

        .primary-kpi {
            background-color: var(--primary-color);
            color: white;
        }

        .secondary-kpi {
            background-color: #f7f7f7;
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .report-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--box-shadow);
        }
        .report-controls label, .report-controls input, .report-controls select {
            font-size: 1em;
        }
    </style>
</head>
<body>
    <div id="app-wrapper">
        
        <aside id="sidebar">
            <h2 class="logo">COBEK BAKAR</h2>
            <nav class="main-nav">
                <a href="index.php" class="nav-item"><i class="fas fa-cash-register"></i> Input Pesanan</a>
                <a href="product.php" class="nav-item"><i class="fas fa-box"></i> Kelola Produk</a>
                <a href="history.php" class="nav-item"><i class="fas fa-history"></i> Riwayat Transaksi</a>
                <a href="report.php" class="nav-item active"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a>
            </nav>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION["nama_lengkap"]); ?></span>
                <button class="logout-btn" onclick="window.location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </aside>

        <main id="main-content">
            <header>
                <h1>Laporan Penjualan</h1>
            </header>
            
            <section class="report-area">
                
                <div class="report-controls">
                <label for="period-filter">Periode:</label>
                <select id="period-filter" onchange="generateReport()">
                    <option value="today">Harian</option>
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="custom">Rentang Kustom</option>
                </select>

                <label for="date-start"></label>
                <input type="date" id="date-start" onchange="generateReport()">

                <label for="date-end"></label>
                <input type="date" id="date-end" onchange="generateReport()">

                <button class="export-btn" onclick="printReport()">
                        <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>

                <h2>Ringkasan Kinerja Periode</h2>
                
                <div class="kpi-cards-container">
                    
                    <div class="report-summary-card primary-kpi">
                        <p>Total Pendapatan</p>
                        <h3 id="total-income-display">Rp 0</h3>
                    </div>

                    <div class="report-summary-card secondary-kpi">
                        <p>Jumlah Transaksi</p>
                        <h3 id="total-trans-display">0</h3>
                    </div>

                    <div class="report-summary-card secondary-kpi">
                        <p>Rata-rata Nilai Transaksi</p>
                        <h3 id="avg-trans-display">Rp 0</h3>
                    </div>
                </div>
                </div>
                </section>
        </main>
    </div>

    <script src="script-report.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // Inisiasi fungsi laporan penjualan saat halaman dimuat
             generateReport(); 
        });
    </script>
</body>
</html>