// FILE: script-product.js (KODE LENGKAP DENGAN DISKON DAN KATEGORI)

let currentMode = 'add'; // State untuk menentukan mode form (add atau edit)
const PRODUCT_ENDPOINT = 'proses_produk.php';

// Fungsi bantuan untuk memformat angka menjadi Rupiah
const formatRupiah = (number) => {
    if (isNaN(number)) return 'Rp 0';
    return 'Rp ' + Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

// --------------------------------------------------------
// A. READ: Memuat dan Merender Data Produk
// --------------------------------------------------------

async function loadProducts() {
    try {
        const response = await fetch(PRODUCT_ENDPOINT, { method: 'GET' });
        const result = await response.json();

        if (result.success) {
            renderProductTable(result.data);
        } else {
            console.error("Gagal memuat produk:", result.message);
            alert("Gagal memuat data produk: " + result.message);
        }
    } catch (error) {
        console.error("Error Koneksi:", error);
        alert("Koneksi ke server gagal saat memuat data produk.");
    }
}

// MODIFIKASI KRUSIAL: Menambahkan kolom Kategori dan Diskon ke tabel
function renderProductTable(products) {
    const tableBody = document.getElementById('product-table-body');
    tableBody.innerHTML = '';

    products.forEach(product => {
        const row = tableBody.insertRow();
        row.innerHTML = `
            <td>P${product.id_produk.toString().padStart(3, '0')}</td>
            <td>${product.nama_produk}</td>
            <td>${product.kategori || '-'}</td> <td>${formatRupiah(product.harga)}</td>
            <td>${product.stok}</td>
            <td>${product.diskon_jual || 0}%</td> <td>
                <button class="action-btn edit" onclick="openProductModal('edit', ${product.id_produk})"><i class="fas fa-edit"></i> Ubah</button>
                <button class="action-btn delete" onclick="deleteProduct(${product.id_produk}, '${product.nama_produk}')"><i class="fas fa-trash"></i> Hapus</button>
            </td>
        `;
    });
}

// --------------------------------------------------------
// B. CREATE & UPDATE: Modal dan Proses Form
// --------------------------------------------------------

// MODIFIKASI KRUSIAL: Mengisi nilai Kategori dan Diskon saat mode Edit
async function openProductModal(mode, productId = null) {
    const modal = document.getElementById('product-modal');
    currentMode = mode;
    
    // Reset Form
    document.getElementById('product-form').reset();
    document.getElementById('product-id').value = '';
    
    // Pastikan input Diskon dan Kategori ada di product.php
    const categoryInput = document.getElementById('product-category');
    const discountInput = document.getElementById('product-discount');
    
    if (mode === 'add') {
        document.getElementById('modal-title').textContent = 'Tambah Produk Baru';
        document.getElementById('modal-submit-btn').textContent = 'Simpan Data';
    } else if (mode === 'edit' && productId) {
        document.getElementById('modal-title').textContent = `Ubah Produk (ID: ${productId})`;
        document.getElementById('modal-submit-btn').textContent = 'Perbarui Data';
        document.getElementById('product-id').value = productId;
        
        // Ambil data produk untuk diisi ke form
        try {
            const response = await fetch(`${PRODUCT_ENDPOINT}?id=${productId}`, { method: 'GET' });
            const result = await response.json();

            if (result.success && result.data) {
                const product = result.data;
                document.getElementById('product-name').value = product.nama_produk;
                document.getElementById('product-price').value = product.harga;
                document.getElementById('product-stock').value = product.stok;
                
                // BARU: Isi nilai Kategori dan Diskon
                if (categoryInput) categoryInput.value = product.kategori || 'Makanan';
                if (discountInput) discountInput.value = product.diskon_jual || 0;
                
            } else {
                alert("Gagal mengambil data produk: " + result.message);
                return;
            }
        } catch (error) {
            alert("Koneksi gagal saat mengambil data produk.");
            return;
        }
    }
    
    modal.style.display = 'block';
}

function closeProductModal() {
    document.getElementById('product-modal').style.display = 'none';
}

// MODIFIKASI KRUSIAL: Mengambil data Kategori dan Diskon dari form
document.getElementById('product-form').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Konversi tipe data untuk dikirim ke PHP
    data.harga = parseFloat(data.harga);
    data.stok = parseInt(data.stok);
    data.diskon_jual = parseInt(data.diskon_jual); // PASTIKAN DIKONVERSI KE INTEGER

    let method = currentMode === 'add' ? 'POST' : 'PUT';
    let url = PRODUCT_ENDPOINT;

    if (currentMode === 'edit') {
        url += `?id=${data.id_produk}`;
    }

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            closeProductModal();
            loadProducts(); // Muat ulang tabel
        } else {
            alert("Operasi GAGAL: " + result.message);
        }

    } catch (error) {
        alert(`Koneksi gagal saat ${currentMode === 'add' ? 'menambah' : 'mengubah'} produk.`);
    }
});

// --------------------------------------------------------
// C. DELETE: Menghapus Produk
// --------------------------------------------------------

async function deleteProduct(productId, productName) {
    if (!confirm(`Anda yakin ingin menghapus produk "${productName}"? Aksi ini tidak dapat dibatalkan.`)) {
        return;
    }

    try {
        const response = await fetch(`${PRODUCT_ENDPOINT}?id=${productId}`, {
            method: 'DELETE'
        });

        // Selalu coba baca JSON, bahkan jika status code 409
        const result = await response.json(); 

        if (result.success) {
            alert(result.message);
            loadProducts(); 
        } else if (result.can_delete === false) {
            // FIX UTAMA: Menampilkan pesan informatif dari backend
            alert(result.message); 
            
        } else {
            // Menangkap error umum (misalnya 500, SQL error)
            alert("Penghapusan GAGAL: " + (result.message || "Terjadi kesalahan server yang tidak terduga."));
        }

    } catch (error) {
        // Ini adalah error "Koneksi gagal" yang TIDAK informatif (hanya jika AJAX gagal terhubung sama sekali)
        alert("Koneksi gagal saat menghapus produk.");
    }
}

// --------------------------------------------------------
// D. INISIALISASI
// --------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    
    // Filter/Pencarian di Tabel Produk (Frontend Search)
    document.getElementById('search-product-input').addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        
        // Perlu memuat ulang data atau menyaring data yang sudah ada (kita gunakan loadProducts lalu filter)
        // Jika data sedikit, ini aman.
        loadProducts().then(() => {
            const tableBody = document.getElementById('product-table-body');
            // Cek jika tabel kosong karena loadProducts gagal atau tidak ada data
            if (!tableBody || tableBody.rows.length === 0) return;
            
            const rows = tableBody.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                // Kolom Nama Produk berada di indeks 1
                const productName = rows[i].cells[1].textContent.toLowerCase(); 
                if (productName.includes(query)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    });
});