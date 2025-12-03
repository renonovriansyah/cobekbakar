<?php
// FILE: struk.php (FINAL UNTUK CETAK STRUK DENGAN DISKON TRANSPARAN)

session_start();
require_once 'config.php';

$id_transaksi = $_GET['id'] ?? null;
if (!$id_transaksi || !is_numeric($id_transaksi)) {
    die("ID Transaksi tidak valid.");
}

// 1. Query Data Transaksi Utama (TERMASUK UANG DITERIMA & KEMBALIAN)
$sql_trans = "
    SELECT 
        t.tanggal, t.jam, t.total_biaya, t.uang_diterima, t.kembalian,
        u.nama_lengkap AS kasir 
    FROM 
        transaksi t
    JOIN 
        user u ON t.id_user = u.id_user
    WHERE 
        t.id_transaksi = ?
";
$stmt_trans = $conn->prepare($sql_trans);
$stmt_trans->bind_param("i", $id_transaksi);
$stmt_trans->execute();
$transaksi = $stmt_trans->get_result()->fetch_assoc();
$stmt_trans->close();

if (!$transaksi) {
    die("Transaksi tidak ditemukan.");
}

// 2. Query Detail Produk (Detail Transaksi) - Mengambil DISKON dari tabel produk
$sql_detail = "
    SELECT 
        dt.jumlah, dt.subtotal, 
        p.nama_produk, p.harga AS harga_satuan_normal, 
        p.diskon_jual 
    FROM 
        detail_transaksi dt
    JOIN 
        produk p ON dt.id_produk = p.id_produk
    WHERE 
        dt.id_transaksi = ?
";
$stmt_detail = $conn->prepare($sql_detail);
$stmt_detail->bind_param("i", $id_transaksi);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();
$detail_pesanan = $result_detail->fetch_all(MYSQLI_ASSOC);
$stmt_detail->close();

$conn->close();

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// INISIASI TOTAL AKHIR
$total_diskon_transaksi = 0;
$total_gross = 0;

foreach ($detail_pesanan as &$item) {
    // Menghitung harga normal dan potongan untuk setiap item
    $harga_normal = $item['harga_satuan_normal'];
    $qty = $item['jumlah'];
    $persen_diskon = (float)$item['diskon_jual'];

    $subtotal_gross_item = $harga_normal * $qty;
    $potongan_diskon_item = ($subtotal_gross_item * $persen_diskon) / 100;
    
    // Menyimpan hasil perhitungan ke array item
    $item['potongan_diskon'] = $potongan_diskon_item;

    // Akumulasi total
    $total_diskon_transaksi += $potongan_diskon_item;
    $total_gross += $subtotal_gross_item;
}
unset($item); // Putus referensi

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #<?php echo $id_transaksi; ?></title>
    
    <style>
        /* Tampilan yang disukai: minimalis, meniru POS thermal */
        body {
            font-family: 'Consolas', monospace; 
            font-size: 11px; 
            margin: 0;
            padding: 10px;
            color: #000;
            background-color: #fff;
            max-width: 300px; 
        }
        .receipt-container { width: 100%; margin: 0 auto; }
        .header, .footer, .separator { text-align: center; margin: 5px 0; }
        .separator { border-top: 1px dashed #000; }
        
        .item-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .item-name { width: 50%; text-align: left; }
        .item-details { width: 30%; text-align: right; }
        .item-subtotal { width: 20%; text-align: right; }
        
        .summary-row { display: flex; justify-content: space-between; margin-top: 5px; }
        .summary-row.total { font-size: 1.1em; font-weight: bold; padding-top: 5px; border-top: 1px dashed #000; margin-top: 5px;}
        .detail { margin-bottom: 10px; }

        .discount-detail {
            margin-top: -3px; 
            padding-left: 20px; /* Indentasi agar terlihat sebagai sub-item */
            font-size: 0.9em;
            color: #444; /* Sedikit lebih gelap dari teks utama */
        }
        
        /* Media query memastikan elemen no-print hanya hilang saat mencetak */
        @media print {
            .no-print { display: none; }
            body { 
                font-size: 10px; 
                padding: 0; 
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h3>COBEK BAKAR</h3>
            <p>Jl. Mulu Jadian Enggak No. 01 Di Hatiku, Jambi</p>
            <p>Telp: 0812-3456-7890</p>
        </div>
        
        <div class="separator"></div>
        
        <div class="detail">
            <p><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($transaksi['tanggal'])); ?></p>
            <p><strong>Waktu:</strong> <?php echo date('H:i', strtotime($transaksi['jam'])); ?></p>
            <p><strong>Kasir:</strong> <?php echo htmlspecialchars($transaksi['kasir']); ?></p>
            <p><strong>No. Struk:</strong> TRX<?php echo str_pad($id_transaksi, 5, '0', STR_PAD_LEFT); ?></p>
        </div>
        
        <div class="item-list">
        <?php foreach ($detail_pesanan as $item): ?>
            
            <div class="item-row">
                <span class="item-name">
                    <?php echo htmlspecialchars($item['nama_produk']); ?>
                </span>
                <span class="item-details"><?php echo $item['jumlah']; ?> x <?php echo number_format($item['harga_satuan_normal'], 0, ',', '.'); ?></span>
                <span class="item-subtotal">
                    <?php echo formatRupiah($item['subtotal']); ?>
                </span>
            </div>
            
            <?php if ($item['potongan_diskon'] > 0): ?>
                <div class="summary-row discount-detail">
                    <span style="font-style: italic;">Potongan Diskon (<?php echo (int)$item['diskon_jual']; ?>%)</span>
                    <span style="text-align: right; color: #E76F51;">- <?php echo formatRupiah($item['potongan_diskon']); ?></span>
                </div>
            <?php endif; ?>
            
        <?php endforeach; ?>
    </div>

    <div class="separator"></div>

        <div class="summary">
            <?php if ($total_diskon_transaksi > 0): ?>
            <div class="summary-row">
                <span>Subtotal (Normal)</span>
                <span><?php echo formatRupiah($total_gross); ?></span>
            </div>
            <div class="summary-row" style="color: #E76F51; font-weight: bold;">
                <span>Total Diskon</span>
                <span>- <?php echo formatRupiah($total_diskon_transaksi); ?></span>
            </div>
            <div class="separator"></div>
            <?php endif; ?>
            
            <div class="summary-row total">
                <span>TOTAL BERSIH</span>
                <span><?php echo formatRupiah($transaksi['total_biaya']); ?></span>
            </div>
            
            <div class="summary-row">
                <span>TUNAI DITERIMA</span>
                <span><?php echo formatRupiah($transaksi['uang_diterima']); ?></span>
            </div>
            
            <div class="summary-row">
                <span>KEMBALIAN</span>
                <span><?php echo formatRupiah($transaksi['kembalian']); ?></span>
            </div>
        </div>

        <div class="separator"></div>

        <div class="footer">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p class="no-print">--- Akhir Struk ---</p>
            <button class="no-print" onclick="window.print()">Cetak Ulang Struk</button>
        </div>
    </div>
</body>
</html>