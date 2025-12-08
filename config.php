<?php
// FILE: config.php

// Kebutuhan Database: MySQL dan localhost 

define('DB_SERVER', 'sql100.infinityfree.com'); 
define('DB_USERNAME', 'if0_40595163');  
define('DB_PASSWORD', 'GzfIjq0np20K'); 
define('DB_NAME', 'if0_40595163_db_kasir_cobekbakar');

// 1. Buat Koneksi
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// 2. Cek Koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set timezone default ke Indonesia/Jakarta
date_default_timezone_set('Asia/Jakarta');
?>
