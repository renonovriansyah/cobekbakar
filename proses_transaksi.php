<?php
// FILE: proses_transaksi.php (FINAL DENGAN PENYIMPANAN UANG TUNAI DAN DISKON PER ITEM)

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
// total_biaya dari JS sekarang adalah GRAND TOTAL (setelah diskon)
$total_biaya = $data['total_biaya'] ?? 0;
$uang_diterima = $data['uang_diterima'] ?? 0;
$kembalian = $uang_diterima - $total_biaya;
$detail_pesanan = $data['detail_pesanan'] ?? [];
$id_user = $_SESSION['id_user'];

// Catatan: Karena total_biaya yang dikirim dari JS adalah GRAND TOTAL, 
// validasi ini memastikan keranjang tidak kosong dan uang pas/lebih.
if (empty($detail_pesanan) || $total_biaya <= 0 || $kembalian < 0) {
    echo json_encode(["success" => false, "error" => "Data transaksi tidak valid atau uang diterima kurang."]);
    exit;
}

// ------------------------------------------
// 1. MULAI TRANSAKSI DATABASE (PENTING UNTUK INTEGRITAS DATA)
// ------------------------------------------
$conn->begin_transaction();
$transaksi_berhasil = false;
$id_transaksi = null;

try {
    // A. Masukkan data ke tabel TRANSAKSI
    // total_biaya: GRAND TOTAL bersih
    $sql_transaksi = "INSERT INTO transaksi (tanggal, jam, total_biaya, uang_diterima, kembalian, id_user) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_transaksi = $conn->prepare($sql_transaksi);
    
    $tanggal = date("Y-m-d");
    $jam = date("H:i:s");
    
    // BIND PARAM: ssdddi (string, string, double, double, double, integer)
    $stmt_transaksi->bind_param("ssdddi", $tanggal, $jam, $total_biaya, $uang_diterima, $kembalian, $id_user);
    $stmt_transaksi->execute();
    $id_transaksi = $conn->insert_id;
    $stmt_transaksi->close();

    if (!$id_transaksi) {
        throw new Exception("Gagal mendapatkan ID Transaksi baru.");
    }
    
    // B. Masukkan data ke tabel DETAIL_TRANSAKSI dan Kurangi Stok 
    
    // MODIFIKASI KRUSIAL: Tambah harga_satuan_diskon, diskon_persen
    // Anda harus memastikan tabel detail_transaksi memiliki kolom ini
    $sql_detail = "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan_diskon, diskon_persen, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_detail = $conn->prepare($sql_detail);

    $sql_update_stok = "UPDATE produk SET stok = stok - ? WHERE id_produk = ?";
    $stmt_update_stok = $conn->prepare($sql_update_stok);

    foreach ($detail_pesanan as $item) {
        // 1. Ambil dan Konversi Data untuk Binding
        $id_produk = (int)$item['id'];
        $jumlah = (int)$item['qty']; // Pastikan ini INTEGER
        $diskon_persen = (int)$item['discount']; 
        $subtotal_bersih = (float)$item['subtotal']; // Subtotal setelah diskon
        $harga_satuan_diskon = (float)$item['price_final']; // Harga satuan bersih setelah diskon (dari JS)

        // 2. Masukkan Detail Transaksi
        // BIND PARAM LEBIH AMAN: "iididi"
        // (i:id_transaksi, i:id_produk, i:jumlah, d:harga_satuan_diskon, i:diskon_persen, d:subtotal_bersih)
        $stmt_detail->bind_param("iididi", $id_transaksi, $id_produk, $jumlah, $harga_satuan_diskon, $diskon_persen, $subtotal_bersih);
        if (!$stmt_detail->execute()) {
            throw new Exception("Gagal menyimpan detail transaksi produk ID: " . $id_produk . " DB Error: " . $stmt_detail->error);
        }

        // 3. Kurangi Stok
        $stmt_update_stok->bind_param("ii", $jumlah, $id_produk);
        if (!$stmt_update_stok->execute()) {
            throw new Exception("Gagal mengurangi stok produk ID: " . $id_produk . " DB Error: " . $stmt_update_stok->error);
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
    
    // PENINGKATAN USABILITY (Sesuai SRS): Tampilkan pesan error yang lebih umum ke user
    $user_error_message = "Transaksi GAGAL. Terjadi kesalahan pada penyimpanan data. Silakan coba lagi atau hubungi administrator.";
    error_log("Kesalahan Transaksi DB: " . $e->getMessage()); // Log error teknis
    
    echo json_encode(["success" => false, "error" => $user_error_message]);
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