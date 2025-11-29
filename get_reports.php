<?php
// FILE: get_reports.php (Koreksi Logika Final)

session_start();

// Cek Autentikasi
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Akses ditolak. Silakan login."]);
    exit;
}

require_once 'config.php';
header('Content-Type: application/json');

// --- PENGATURAN PAGINATION DAN FILTER ---
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$date_start = $_GET['start'] ?? date('Y-m-d', strtotime('-7 days'));
$date_end = $_GET['end'] ?? date('Y-m-d');
$summary_only = isset($_GET['summary_only']) && $_GET['summary_only'] === 'true';

$response = [
    'success' => true,
    'total_income' => 0,
    'total_transactions' => 0,
    'current_page' => $page,
    'total_pages' => 1,
    'transactions' => []
];

try {
    // 1. QUERY TOTAL PENDAPATAN (Wajib untuk semua)
    $sql_income = "SELECT SUM(total_biaya) AS total FROM transaksi WHERE tanggal BETWEEN ? AND ?";
    $stmt_income = $conn->prepare($sql_income);
    $stmt_income->bind_param("ss", $date_start, $date_end);
    $stmt_income->execute();
    $result_income = $stmt_income->get_result()->fetch_assoc();
    $response['total_income'] = (float)($result_income['total'] ?? 0);
    $stmt_income->close();

    
    // 2. QUERY TOTAL TRANSAKSI (Wajib untuk semua, juga dipakai untuk summary/pagination)
    $sql_count = "SELECT COUNT(id_transaksi) AS total FROM transaksi WHERE tanggal BETWEEN ? AND ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("ss", $date_start, $date_end);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result()->fetch_assoc();
    $response['total_transactions'] = (int)($result_count['total'] ?? 0);
    $stmt_count->close();

    // --- KONTROL LOGIKA: EXIT jika hanya butuh SUMMARY (Report.php) ---
    if ($summary_only) {
        $total_income = $response['total_income'];
        $total_trans = $response['total_transactions'];
        
        // Hitung rata-rata
        $response['avg_transaction'] = ($total_trans > 0) ? round($total_income / $total_trans) : 0;
        
        // Kirim response dan keluar
        echo json_encode($response);
        $conn->close();
        exit;
    }
    // -----------------------------------------------------------------

    // --- LOGIKA HISTORY.PHP (Hanya dijalankan jika summary_only=false) ---
    
    // Hitung total halaman untuk pagination
    $response['total_pages'] = ceil($response['total_transactions'] / $limit);

    // 3. QUERY RIWAYAT TRANSAKSI (dengan JOIN dan Pagination)
    $sql_transactions = "
        SELECT
            t.id_transaksi,
            t.tanggal,
            t.jam,
            t.total_biaya,
            u.nama_lengkap AS kasir,
            COUNT(dt.id_detail) AS jumlah_item
        FROM
            transaksi t
        JOIN
            user u ON t.id_user = u.id_user
        JOIN
            detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
        WHERE
            t.tanggal BETWEEN ? AND ?
        GROUP BY
            t.id_transaksi
        ORDER BY
            t.tanggal DESC, t.jam DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt_trans = $conn->prepare($sql_transactions);
    $stmt_trans->bind_param("ssii", $date_start, $date_end, $limit, $offset);
    $stmt_trans->execute();
    $result_trans = $stmt_trans->get_result();

    while ($row = $result_trans->fetch_assoc()) {
        $row['total_biaya'] = (float)$row['total_biaya'];
        $row['waktu'] = $row['tanggal'] . ' ' . $row['jam'];
        $response['transactions'][] = $row;
    }
    $stmt_trans->close();

} catch (Exception $e) {
    http_response_code(500);
    $response['success'] = false;
    $response['message'] = "Database Error: " . $e->getMessage();
}

$conn->close();
echo json_encode($response);