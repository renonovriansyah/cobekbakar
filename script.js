// FILE: script.js (KODE LENGKAP)

let cart = {}; // Objek untuk menyimpan item di keranjang {product_id: {name, price, qty}}
let PRODUCTS = []; // Array akan diisi dari database via AJAX

// Fungsi bantuan untuk memformat angka menjadi Rupiah
const formatRupiah = (number) => {
    // Memastikan input adalah number sebelum format
    if (isNaN(number)) return 'Rp 0'; 
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
        // Menggunakan id_produk yang dikembalikan dari PHP
        card.setAttribute('data-id', product.id_produk); 
        card.setAttribute('data-price', product.harga);
        card.setAttribute('onclick', `addItemToCart('${product.id_produk}')`);
        
        card.innerHTML = `
            <h4>${product.nama_produk}</h4>
            <p>${formatRupiah(product.harga)}</p>
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
    // Cari produk dari array PRODUCTS yang dimuat dari database
    const product = PRODUCTS.find(p => p.id_produk == productId);
    if (!product) return;

    if (cart[productId]) {
        cart[productId].qty += 1;
    } else {
        cart[productId] = { 
            id: product.id_produk,
            name: product.nama_produk, 
            price: product.harga, 
            qty: 1 
        };
    }
    renderCart();
}

// 2. Mengubah kuantitas
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

// 3. Menghapus item dari keranjang
function removeItem(productId) {
    delete cart[productId];
    renderCart();
}

// 4. Menghitung total biaya keranjang
function calculateTotal() {
    let subtotal = 0;
    for (const id in cart) {
        subtotal += cart[id].price * cart[id].qty;
    }
    return subtotal;
}


// 5. Merender (menampilkan) isi keranjang ke tabel
function renderCart() {
    const cartBody = document.getElementById('cart-items-body');
    let subtotal = calculateTotal();
    cartBody.innerHTML = ''; // Kosongkan isi tabel

    for (const id in cart) {
        const item = cart[id];
        const itemSubtotal = item.price * item.qty;

        const row = cartBody.insertRow();
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${formatRupiah(item.price)}</td>
            <td style="text-align:center;">
                <button class="qty-btn" onclick="updateQuantity('${id}', 'minus')">-</button>
                ${item.qty}
                <button class="qty-btn" onclick="updateQuantity('${id}', 'plus')">+</button>
            </td>
            <td>${formatRupiah(itemSubtotal)}</td>
            <td><button class="remove-btn" onclick="removeItem('${id}')"><i class="fas fa-times"></i></button></td>
        `;
    }

    // Update display total
    document.getElementById('subtotal-display').textContent = formatRupiah(subtotal);
    document.getElementById('grand-total-display').textContent = formatRupiah(subtotal);

    // Hitung kembalian otomatis
    calculateChange();
}


// --------------------------------------------------------
// C. FUNGSI PEMBAYARAN DAN PROSES
// --------------------------------------------------------

// 1. Menghitung kembalian (dipanggil saat input uang diterima berubah)
function calculateChange() {
    let total = calculateTotal();

    const cashInput = document.getElementById('cash-input').value;
    const cashReceived = parseInt(cashInput) || 0;
    const change = cashReceived - total;

    document.getElementById('change-display').textContent = formatRupiah(change);

    // Aktifkan/Nonaktifkan tombol Proses Pembayaran
    const processBtn = document.getElementById('process-payment-btn');
    if (total > 0 && change >= 0) {
        processBtn.disabled = false;
    } else {
        processBtn.disabled = true;
    }
}

// 2. Memproses Pembayaran (Kirim data ke PHP)
async function processPayment() {
    const total = calculateTotal();
    const cashInput = document.getElementById('cash-input').value;
    const cashReceived = parseInt(cashInput) || 0;
    
    if (Object.keys(cart).length === 0) {
        alert('Keranjang masih kosong!');
        return;
    }

    // Ubah objek cart menjadi array untuk pengiriman data
    const cartArray = Object.values(cart);

    const paymentData = {
        total_biaya: total,
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
            alert(`Transaksi Berhasil!\nTotal: ${formatRupiah(total)}\nKembalian: ${formatRupiah(cashReceived - total)}`);
            
            // Panggil fungsi cetak struk (simulasi)
            printStruk(result.id_transaksi); 

            clearCart();
        } else {
            alert(`Transaksi GAGAL: ${result.error}`);
            console.error("Detail Error Backend:", result.error);
        }

    } catch (error) {
        alert("Koneksi ke server gagal saat memproses pembayaran.");
        console.error("Koneksi Error:", error);
    }
}

// 3. Mengosongkan Keranjang
function clearCart() {
    cart = {};
    document.getElementById('cash-input').value = '';
    renderCart();
}

// 4. Fungsi Cetak Struk (Simulasi Aksi)
function printStruk(transactionId) {
    // Berdasarkan SRS, ini harus terintegrasi dengan printer struk.
    // Di sini kita hanya menampilkan notifikasi.
    console.log(`Mencetak Struk untuk Transaksi ID: ${transactionId}`);
    // Logika cetak ke perangkat keras/driver akan diimplementasikan di backend.
    alert(`Struk Transaksi #${transactionId} telah dikirim ke printer.`);
}


// --------------------------------------------------------
// D. INISIALISASI
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