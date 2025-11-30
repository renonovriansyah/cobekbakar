// FILE: script.js (KODE LENGKAP DENGAN PERHITUNGAN DISKON)

let cart = {}; // Objek untuk menyimpan item di keranjang {product_id: {name, price, qty}}
let PRODUCTS = []; // Array akan diisi dari database via AJAX

// Fungsi bantuan untuk memformat angka menjadi Rupiah
const formatRupiah = (number) => {
    // Memastikan input adalah number sebelum format
    if (isNaN(number)) return 'Rp 0'; 
    // Menggunakan Math.round untuk menghindari masalah floating point pada Rupiah (tanpa desimal)
    return 'Rp ' + Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

// --------------------------------------------------------
// A. FUNGSI UNTUK MEMUAT DATA DARI BACKEND
// --------------------------------------------------------

async function loadProducts() {
    try {
        const response = await fetch('get_products.php');
        const data = await response.json();

        if (response.ok) {
            // Data produk kini berisi diskon_jual
            PRODUCTS = data;
            renderProductGrid(PRODUCTS);
        } else {
            console.error("Gagal memuat produk:", data.error);
            alert("Gagal memuat data produk. Coba refresh halaman.");
        }
    } catch (error) {
        console.error("Koneksi gagal:", error);
        alert("Koneksi ke server gagal. Pastikan database aktif.");
    }
}

function renderProductGrid(products) {
    const grid = document.getElementById('product-grid');
    grid.innerHTML = ''; // Kosongkan grid

    products.forEach(product => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.setAttribute('data-id', product.id_produk); 
        card.setAttribute('data-price', product.harga);
        card.setAttribute('onclick', `addItemToCart('${product.id_produk}')`);
        
        let priceDisplay = formatRupiah(product.harga);
        
        // Tampilkan diskon jika ada
        if (product.diskon_jual > 0) {
            const diskon_percent = product.diskon_jual / 100;
            const price_after_discount = product.harga * (1 - diskon_percent);
            priceDisplay = `<del style="color:#888; font-size:0.8em;">${formatRupiah(product.harga)}</del> ${formatRupiah(price_after_discount)}`;
        }

        card.innerHTML = `
            <h4>${product.nama_produk}</h4>
            <p>${priceDisplay}</p>
            <button class="add-btn"><i class="fas fa-plus"></i></button>
        `;
        grid.appendChild(card);
    });
}


// --------------------------------------------------------
// B. FUNGSI INTERAKSI KERANJANG
// --------------------------------------------------------

// 1. Menambahkan item ke keranjang
function addItemToCart(productId) {
    const product = PRODUCTS.find(p => p.id_produk == productId);
    if (!product) return;

    // Ambil nilai diskon, pastikan defaultnya 0 jika tidak ada
    const diskon_jual = product.diskon_jual || 0; 
    
    // Hitung harga setelah diskon
    const diskon_percent = diskon_jual / 100;
    const price_after_discount = product.harga * (1 - diskon_percent);
    
    if (cart[productId]) {
        cart[productId].qty += 1;
    } else {
        cart[productId] = { 
            id: product.id_produk,
            name: product.nama_produk, 
            price: product.harga, 
            discount_percent: diskon_jual, // Pastikan ini aman
            price_final: price_after_discount, // Harga satuan bersih
            qty: 1 
        };
    }
    renderCart();
}

// 2. Mengubah kuantitas (TETAP SAMA)
function updateQuantity(productId, type) {
    if (cart[productId]) {
        if (type === 'plus') {
            cart[productId].qty += 1;
        } else if (type === 'minus') {
            cart[productId].qty -= 1;
            if (cart[productId].qty < 1) {
                delete cart[productId]; // Hapus jika kuantitas nol
            }
        }
        renderCart();
    }
}

// 3. Menghapus item dari keranjang (TETAP SAMA)
function removeItem(productId) {
    delete cart[productId];
    renderCart();
}

// 4. Menghitung total biaya keranjang (MODIFIKASI)
function calculateTotal() {
    let subtotal = 0; // Total harga kotor
    let total_diskon = 0; // Total diskon yang diberikan
    let grand_total = 0; // Total harga bersih (yang dibayar)

    for (const id in cart) {
        const item = cart[id];
        const item_price_gross = item.price * item.qty;
        const item_price_net = item.price_final * item.qty;
        
        subtotal += item_price_gross;
        grand_total += item_price_net;
        total_diskon += (item_price_gross - item_price_net);
    }
    
    // Kembalikan semua nilai total yang dibutuhkan
    return { subtotal, grand_total, total_diskon }; 
}

// 5. Merender (menampilkan) isi keranjang ke tabel
function renderCart() {
    const cartBody = document.getElementById('cart-items-body');
    const { subtotal, grand_total, total_diskon } = calculateTotal(); 
    cartBody.innerHTML = ''; 

    for (const id in cart) {
        const item = cart[id];
        
        const itemSubtotalGross = item.price * item.qty;
        
        let subtotalCellContent = formatRupiah(itemSubtotalGross);
        if (item.discount_percent > 0) {
            const itemSubtotalNet = item.price_final * item.qty;
            subtotalCellContent = `
                <del style="color:#888; font-size:0.8em;">${formatRupiah(itemSubtotalGross)}</del><br>
                ${formatRupiah(itemSubtotalNet)}
            `;
        }

        const row = cartBody.insertRow();
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${formatRupiah(item.price)}</td>
            <td style="text-align:center;">
                <button class="qty-btn" onclick="updateQuantity('${id}', 'minus')">-</button>
                ${item.qty}
                <button class="qty-btn" onclick="updateQuantity('${id}', 'plus')">+</button>
            </td>
            <td>${subtotalCellContent}</td>
            <td><button class="remove-btn" onclick="removeItem('${id}')"><i class="fas fa-times"></i></button></td>
        `;
    }

    // Update display Subtotal (Harga kotor)
    document.getElementById('subtotal-display').textContent = formatRupiah(subtotal);
    
    // PERBAIKAN: HANYA MENGISI KONTEN ke wadah permanen
    const diskonRowContainer = document.getElementById('total-diskon-row');

    if (diskonRowContainer) {
         diskonRowContainer.innerHTML = `
            <p>Total Diskon:</p> 
            <p style="color: var(--primary-color); font-weight: bold;">- ${formatRupiah(total_diskon)}</p>
        `;
    } else {
        // Jika container tidak ditemukan (hanya untuk debugging)
        console.error("Elemen total-diskon-row tidak ditemukan di HTML.");
    }

    // Update Grand Total (Harga bersih)
    document.getElementById('grand-total-display').textContent = formatRupiah(grand_total);

    // Hitung kembalian otomatis
    calculateChange();
}

// --------------------------------------------------------
// C. FUNGSI PEMBAYARAN DAN PROSES
// --------------------------------------------------------

// 1. Menghitung kembalian (MODIFIKASI: Gunakan grand_total)
function calculateChange() {
    // Panggil calculateTotal dan ambil grand_total
    const { grand_total } = calculateTotal(); // Pastikan ambil grand_total

    const cashInput = document.getElementById('cash-input').value;
    const cashReceived = parseInt(cashInput) || 0;
    const change = cashReceived - grand_total; // Hitung kembalian dari grand_total

    document.getElementById('change-display').textContent = formatRupiah(change);

    // Aktifkan/Nonaktifkan tombol Proses Pembayaran
    const processBtn = document.getElementById('process-payment-btn');
    if (grand_total > 0 && change >= 0) { // Gunakan grand_total untuk validasi
        processBtn.disabled = false;
    } else {
        processBtn.disabled = true;
    }
}

// 2. Memproses Pembayaran (Kirim data ke PHP) (MODIFIKASI)
async function processPayment() {
    const { grand_total } = calculateTotal();
    const cashInput = document.getElementById('cash-input').value;
    const cashReceived = parseInt(cashInput) || 0;
    
    if (Object.keys(cart).length === 0) {
        alert('Keranjang masih kosong!');
        return;
    }

    // Ubah objek cart menjadi array untuk pengiriman data
    const cartArray = Object.values(cart).map(item => ({
        // Kirim semua detail yang dibutuhkan proses_transaksi.php
        id: item.id,
        qty: item.qty,
        price_gross: item.price,
        discount: item.discount_percent,
        subtotal: item.price_final * item.qty,
        price_final: item.price_final // Harga satuan setelah diskon
    }));

    const paymentData = {
        total_biaya: grand_total, // KIRIM GRAND TOTAL
        uang_diterima: cashReceived,
        detail_pesanan: cartArray
    };

    try {
        // Kirim data transaksi ke endpoint PHP
        const response = await fetch('proses_transaksi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(paymentData)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert(`Transaksi Berhasil!\nTotal: ${formatRupiah(grand_total)}\nKembalian: ${formatRupiah(cashReceived - grand_total)}`);
            
            // Panggil fungsi cetak struk
            printStruk(result.id_transaksi); 

            clearCart();
            // Muat ulang produk untuk mengupdate stok
            loadProducts(); 
        } else {
            alert(`Transaksi GAGAL: ${result.error}`);
            console.error("Detail Error Backend:", result.error);
        }

    } catch (error) {
        alert("Koneksi ke server gagal saat memproses pembayaran.");
        console.error("Koneksi Error:", error);
    }
}

// 3. Mengosongkan Keranjang (TETAP SAMA)
function clearCart() {
    cart = {};
    document.getElementById('cash-input').value = '';
    renderCart();
}

/**
 * Membuka jendela pop-up dan memicu cetak struk (TETAP SAMA)
 * @param {number} transactionId ID transaksi yang baru dibuat
 */
function printStruk(transactionId) {
    const url = 'struk.php?id=' + transactionId;

    // Buka jendela baru dengan ukuran tipis seperti struk POS
    const receiptWindow = window.open(url, '_blank', 'width=350,height=600,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');

    if (receiptWindow) {
        // Logika cetak otomatis dihapus, pengguna cetak manual dari pop-up
    } else {
        alert("Gagal membuka jendela struk. Pastikan pop-up diizinkan.");
    }
}

// --------------------------------------------------------
// D. INISIALISASI (TETAP SAMA)
// --------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
    loadProducts(); // Memuat data produk saat halaman dimuat
    renderCart();
    
    // Tambahkan event listener untuk input pencarian (filter produk)
    document.getElementById('product-search').addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        const filtered = PRODUCTS.filter(p => p.nama_produk.toLowerCase().includes(query));
        renderProductGrid(filtered);
    });
});