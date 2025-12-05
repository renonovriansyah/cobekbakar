<?php
// FILE: config.php

// Kebutuhan Database: MySQL dan localhost 

define('DB_SERVER', 'localhost'); // Host server database (localhost sesuai SRS)
define('DB_USERNAME', 'root');   // Ganti dengan username database Anda
define('DB_PASSWORD', '');       // Ganti dengan password database Anda
define('DB_NAME', 'db_kasir_cobekbakar'); // Nama database yang dibuat di atas

// 1. Buat Koneksi
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// 2. Cek Koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set timezone default ke Indonesia/Jakarta
date_default_timezone_set('Asia/Jakarta');
?>