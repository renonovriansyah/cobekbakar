<?php
// FILE: proses_transaksi.php

session_start();

// Cek Autentikasi
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Akses tidak diizinkan."]);
    exit;
}

require_once 'config.php';
header('Content-Type: application/json');

// Ambil data JSON dari body request
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

// Validasi data
$total_biaya = $data['total_biaya'] ?? 0;
$uang_diterima = $data['uang_diterima'] ?? 0;
$detail_pesanan = $data['detail_pesanan'] ?? [];
$id_user = $_SESSION['id_user'];

if (empty($detail_pesanan) || $total_biaya <= 0) {
    echo json_encode(["success" => false, "error" => "Data transaksi tidak valid atau kosong."]);
    exit;
}

// ------------------------------------------
// 1. MULAI TRANSAKSI DATABASE (ACID)
// ------------------------------------------
$conn->begin_transaction();
$transaksi_berhasil = false;
$id_transaksi = null;

try {
    // A. Masukkan data ke tabel TRANSAKSI
    $sql_transaksi = "INSERT INTO transaksi (tanggal, jam, total_biaya, id_user) VALUES (?, ?, ?, ?)";
    $stmt_transaksi = $conn->prepare($sql_transaksi);
    
    $tanggal = date("Y-m-d");
    $jam = date("H:i:s");
    
    $stmt_transaksi->bind_param("ssdi", $tanggal, $jam, $total_biaya, $id_user);
    $stmt_transaksi->execute();
    $id_transaksi = $conn->insert_id; // Ambil ID transaksi yang baru dibuat
    $stmt_transaksi->close();

    if (!$id_transaksi) {
        throw new Exception("Gagal mendapatkan ID Transaksi baru.");
    }
    
    // B. Masukkan data ke tabel DETAIL_TRANSAKSI dan Kurangi Stok
    $sql_detail = "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)";
    $stmt_detail = $conn->prepare($sql_detail);
    
    $sql_update_stok = "UPDATE produk SET stok = stok - ? WHERE id_produk = ?";
    $stmt_update_stok = $conn->prepare($sql_update_stok);

    foreach ($detail_pesanan as $item) {
        $id_produk = $item['id'];
        $jumlah = $item['qty'];
        $subtotal = $item['price'] * $jumlah;

        // 1. Masukkan Detail Transaksi
        $stmt_detail->bind_param("iidi", $id_transaksi, $id_produk, $jumlah, $subtotal);
        if (!$stmt_detail->execute()) {
            throw new Exception("Gagal menyimpan detail transaksi produk ID: " . $id_produk);
        }

        // 2. Kurangi Stok
        $stmt_update_stok->bind_param("ii", $jumlah, $id_produk);
        if (!$stmt_update_stok->execute()) {
            throw new Exception("Gagal mengurangi stok produk ID: " . $id_produk);
        }
    }

    $stmt_detail->close();
    $stmt_update_stok->close();

    // C. Commit transaksi jika semua berhasil
    $conn->commit();
    $transaksi_berhasil = true;

} catch (Exception $e) {
    // D. Rollback jika ada kegagalan
    $conn->rollback();
    error_log("Kesalahan Transaksi: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
    exit;
}

$conn->close();

if ($transaksi_berhasil) {
    // Kirim respons sukses ke JavaScript
    echo json_encode([
        "success" => true,
        "message" => "Transaksi berhasil disimpan.",
        "id_transaksi" => $id_transaksi
    ]);
}
?>