<?php
// FILE: cetak_laporan.php (FINAL DENGAN DETAIL TRANSAKSI)

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

// --- FUNGSI FORMAT RUPIAH ---
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// --- LOGIKA MENGAMBIL DATA RINGKASAN ---
$conn->begin_transaction(); // Mulai transaksi untuk query ganda

try {
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

    $avg_transaction = ($total_trans > 0) ? round($total_income / $total_trans) : 0;
    
    // 3. Ambil Semua Detail Transaksi (Sama seperti di get_reports.php)
    $sql_detail_trans = "
        SELECT
            t.id_transaksi,
            t.tanggal,
            t.jam,
            t.total_biaya,
            u.nama_lengkap AS kasir
        FROM
            transaksi t
        JOIN
            user u ON t.id_user = u.id_user
        WHERE
            t.tanggal BETWEEN ? AND ?
        ORDER BY
            t.tanggal ASC, t.jam ASC
    ";
    
    $stmt_detail = $conn->prepare($sql_detail_trans);
    $stmt_detail->bind_param("ss", $date_start, $date_end);
    $stmt_detail->execute();
    $transactions = $stmt_detail->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_detail->close();
    
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    die("Gagal memuat data transaksi rinci: " . $e->getMessage());
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan <?php echo $date_start; ?> - <?php echo $date_end; ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; padding: 30px; }
        h1, h2, h3 { text-align: center; margin-bottom: 5px; }
        .periode { text-align: center; margin-bottom: 20px; font-style: italic; }
        
        /* RINGKASAN */
        .summary-box { border: 1px solid #000; padding: 15px; margin-bottom: 30px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .row.total { font-weight: bold; font-size: 1.1em; border-top: 1px solid #000; padding-top: 10px; }
        
        /* DETAIL TRANSAKSI */
        .detail-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .detail-table th, .detail-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .detail-table th { background-color: #f0f0f0; }
        .detail-table td:nth-child(4), .detail-table td:nth-child(5) { text-align: right; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <h1>LAPORAN PENJUALAN COBEK BAKAR</h1>
    <p class="periode">Periode: <?php echo date('d M Y', strtotime($date_start)); ?> s/d <?php echo date('d M Y', strtotime($date_end)); ?></p>
    
    <h3>RINGKASAN KINERJA</h3>
    <div class="summary-box">
        <div class="row total">
            <span>TOTAL PENDAPATAN</span>
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
    
    <h3>RINCIAN TRANSAKSI</h3>
    
    <?php if (empty($transactions)): ?>
        <p>Tidak ada transaksi dalam periode ini.</p>
    <?php else: ?>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>No. TRX</th>
                    <th>Tanggal & Waktu</th>
                    <th>Kasir</th>
                    <th>Total Biaya (Net)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td>TRX<?php echo str_pad($t['id_transaksi'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('d M Y', strtotime($t['tanggal'])) . ' ' . date('H:i', strtotime($t['jam'])); ?></td>
                        <td><?php echo htmlspecialchars($t['kasir']); ?></td>
                        <td><?php echo formatRupiah($t['total_biaya']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p style="margin-top: 40px;">Laporan dicetak oleh: <?php echo htmlspecialchars($_SESSION["nama_lengkap"]); ?></p>
    <p>Tanggal Cetak: <?php echo date('d M Y H:i:s'); ?></p>

    <div style="text-align: center;" class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Ulang Laporan</button>
    </div>
</body>
</html>