<?php
// FILE: get_products.php

session_start();

// Cek autentikasi (sesuai SRS, hanya kasir yang berhak mengakses)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Jika tidak terautentikasi, kirim respons 401 Unauthorized
    http_response_code(401);
    echo json_encode(["error" => "Akses tidak diizinkan. Silakan login."]);
    exit;
}

require_once 'config.php'; // Sertakan koneksi database

// Set header agar browser tahu bahwa responsnya adalah JSON
header('Content-Type: application/json');

// Query untuk mengambil semua produk yang tersedia
$sql = "SELECT id_produk, nama_produk, harga, stok, diskon_jual FROM produk WHERE stok > 0 ORDER BY nama_produk ASC";

$result = $conn->query($sql);
$products = [];

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['harga'] = (float)$row['harga'];
            $row['diskon_jual'] = (int)$row['diskon_jual'];
            $products[] = $row;
        }
    }
    // Mengembalikan data sebagai array JSON
    echo json_encode($products);
} else {
    // Jika ada kesalahan query
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Gagal mengambil data produk: " . $conn->error]);
}

$conn->close();
?>