<?php
// FILE: cetak_laporan.php (FILE BARU)

session_start();
require_once 'config.php';

// Pastikan autentikasi
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die("Akses ditolak.");
}

// Ambil filter tanggal dari URL
$date_start = $_GET['start'] ?? null;
$date_end = $_GET['end'] ?? null;

if (!$date_start || !$date_end) {
    die("Rentang tanggal tidak valid.");
}

// --- LOGIKA MENGAMBIL DATA RINGKASAN (SAMA SEPERTI get_reports.php?summary_only=true) ---

// 1. Total Pendapatan
$sql_income = "SELECT SUM(total_biaya) AS total FROM transaksi WHERE tanggal BETWEEN ? AND ?";
$stmt_income = $conn->prepare($sql_income);
$stmt_income->bind_param("ss", $date_start, $date_end);
$stmt_income->execute();
$result_income = $stmt_income->get_result()->fetch_assoc();
$total_income = (float)($result_income['total'] ?? 0);
$stmt_income->close();

// 2. Jumlah Transaksi
$sql_count = "SELECT COUNT(id_transaksi) AS total FROM transaksi WHERE tanggal BETWEEN ? AND ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("ss", $date_start, $date_end);
$stmt_count->execute();
$result_count = $stmt_count->get_result()->fetch_assoc();
$total_trans = (int)($result_count['total'] ?? 0);
$stmt_count->close();

$conn->close();

$avg_transaction = ($total_trans > 0) ? round($total_income / $total_trans) : 0;

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Penjualan</title>
    <style>
        /* Desain untuk cetak A4 */
        body { font-family: Arial, sans-serif; font-size: 12pt; padding: 30px; }
        h1, h2 { text-align: center; margin-bottom: 20px; }
        .summary-box { border: 1px solid #000; padding: 15px; margin-bottom: 30px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .row.total { font-weight: bold; font-size: 1.1em; border-top: 1px solid #000; padding-top: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <h1>LAPORAN PENJUALAN COBEK BAKAR</h1>
    <h2>Periode: <?php echo date('d M Y', strtotime($date_start)); ?> s/d <?php echo date('d M Y', strtotime($date_end)); ?></h2>
    
    <div class="summary-box">
        <div class="row total">
            <span>TOTAL PENDAPATAN BERSIH</span>
            <span><?php echo formatRupiah($total_income); ?></span>
        </div>
        <div class="row">
            <span>Jumlah Transaksi</span>
            <span><?php echo $total_trans; ?> Transaksi</span>
        </div>
        <div class="row">
            <span>Rata-rata Nilai Transaksi</span>
            <span><?php echo formatRupiah($avg_transaction); ?></span>
        </div>
    </div>
    
    <p style="margin-top: 40px;">*Laporan ini hanya mencakup ringkasan penjualan, detail transaksi tersedia di sistem.</p>

    <div style="text-align: center;" class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Laporan Ini</button>
    </div>
</body>
</html>