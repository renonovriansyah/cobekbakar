<?php
// FILE: proses_produk.php (VERSI FINAL DENGAN DISKON DAN KATEGORI)

session_start();

// Cek Autentikasi
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Akses ditolak. Silakan login."]);
    exit;
}

require_once 'config.php';
header('Content-Type: application/json');

// Mendapatkan metode HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Mendapatkan data input dari body (untuk POST/PUT)
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // --- READ: Mengambil semua produk ---
        $id_produk = $_GET['id'] ?? null;
        
        // SQL HARUS MENYERTAKAN KOLOM BARU (kategori, diskon_jual)
        $select_fields = "id_produk, nama_produk, harga, stok, kategori, diskon_jual";
        
        if ($id_produk) {
            // Ambil detail satu produk
            $sql = "SELECT {$select_fields} FROM produk WHERE id_produk = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_produk);
            $stmt->execute();
            $result = $stmt->get_result();
            $produk = $result->fetch_assoc();
            
            if ($produk) {
                // Konversi tipe data untuk JS
                $produk['harga'] = (float)$produk['harga'];
                $produk['diskon_jual'] = (int)$produk['diskon_jual'];
                echo json_encode(["success" => true, "data" => $produk]);
            } else {
                echo json_encode(["success" => false, "message" => "Produk tidak ditemukan."]);
            }
            $stmt->close();
        } else {
            // Ambil semua produk
            $sql = "SELECT {$select_fields} FROM produk ORDER BY id_produk DESC";
            $result = $conn->query($sql);
            $produk_list = [];
            while ($row = $result->fetch_assoc()) {
                // Konversi tipe data untuk JS
                $row['harga'] = (float)$row['harga'];
                $row['diskon_jual'] = (int)$row['diskon_jual'];
                $produk_list[] = $row;
            }
            echo json_encode(["success" => true, "data" => $produk_list]);
        }
        break;

    case 'POST':
        // --- CREATE: Menambah produk baru ---
        $nama = $input['nama_produk'] ?? '';
        $harga = $input['harga'] ?? 0;
        $stok = $input['stok'] ?? 0;
        $kategori = $input['kategori'] ?? 'Makanan';
        $diskon = $input['diskon_jual'] ?? 0;

        if (empty($nama) || $harga <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Nama dan Harga harus diisi."]);
            break;
        }

        $sql = "INSERT INTO produk (nama_produk, harga, stok, kategori, diskon_jual) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // BIND PARAM: "sdisi" (string, double, integer, string, integer)
        $stmt->bind_param("sdisi", $nama, $harga, $stok, $kategori, $diskon);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Produk baru berhasil ditambahkan.", "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal menambahkan produk: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'PUT':
        // --- UPDATE: Mengubah data produk ---
        $id_produk = $_GET['id'] ?? $input['id_produk'] ?? null;
        $nama = $input['nama_produk'] ?? '';
        $harga = $input['harga'] ?? 0;
        $stok = $input['stok'] ?? 0;
        
        if (empty($id_produk) || empty($nama) || $harga <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Data tidak lengkap untuk diperbarui."]);
            break;
        }

        $kategori = $input['kategori'] ?? 'Makanan';
        $diskon = $input['diskon_jual'] ?? 0;

        $sql = "UPDATE produk SET nama_produk = ?, harga = ?, stok = ?, kategori = ?, diskon_jual = ? WHERE id_produk = ?";
        
        $stmt = $conn->prepare($sql);
        
        // BIND PARAM: "sdisii" (string, double, integer, string, integer, integer)
        if (!$stmt->bind_param("sdisii", $nama, $harga, $stok, $kategori, $diskon, $id_produk)) {
             http_response_code(500);
             echo json_encode(["success" => false, "message" => "Gagal mengikat parameter: " . $stmt->error]);
             $stmt->close();
             break;
        }

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Produk berhasil diperbarui."]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal memperbarui produk: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'DELETE':
        // --- DELETE: Menghapus produk ---
        $id_produk = $_GET['id'] ?? null;

        if (empty($id_produk)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID Produk harus ditentukan."]);
            break;
        }
        
        // --- LOGIKA UTAMA: VERIFIKASI RIWAYAT TRANSAKSI ---
        $sql_check = "SELECT COUNT(*) FROM detail_transaksi WHERE id_produk = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $id_produk);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result()->fetch_row();
        $transaction_count = $result_check[0];
        $stmt_check->close();

        if ($transaction_count > 0) {
            // FIX UX: Berikan pesan informatif ke frontend
            http_response_code(409); // Conflict
            echo json_encode([
                "success" => false, 
                "can_delete" => false, // Marker untuk JS
                "message" => "GAGAL HAPUS: Produk ini sudah digunakan dalam $transaction_count transaksi dan tidak dapat dihapus. Silakan hubungi admin."
            ]);
            break;
        }

        // --- LANJUTKAN PROSES DELETE (Jika aman) ---
        $sql = "DELETE FROM produk WHERE id_produk = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_produk);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Produk berhasil dihapus."]);
        } else {
            // Error SQL umum
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal menghapus produk: " . $stmt->error]);
        }
        $stmt->close();
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Metode tidak diizinkan."]);
        break;
}

$conn->close();
?>