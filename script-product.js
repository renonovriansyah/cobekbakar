// FILE: script-product.js (KODE LENGKAP)

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

function renderProductTable(products) {
    const tableBody = document.getElementById('product-table-body');
    tableBody.innerHTML = '';

    products.forEach(product => {
        const row = tableBody.insertRow();
        row.innerHTML = `
            <td>P${product.id_produk.toString().padStart(3, '0')}</td>
            <td>${product.nama_produk}</td>
            <td>${formatRupiah(product.harga)}</td>
            <td>${product.stok}</td>
            <td>
                <button class="action-btn edit" onclick="openProductModal('edit', ${product.id_produk})"><i class="fas fa-edit"></i> Ubah</button>
                <button class="action-btn delete" onclick="deleteProduct(${product.id_produk}, '${product.nama_produk}')"><i class="fas fa-trash"></i> Hapus</button>
            </td>
        `;
    });
}

// --------------------------------------------------------
// B. CREATE & UPDATE: Modal dan Proses Form
// --------------------------------------------------------

async function openProductModal(mode, productId = null) {
    const modal = document.getElementById('product-modal');
    currentMode = mode;
    
    // Reset Form
    document.getElementById('product-form').reset();
    document.getElementById('product-id').value = '';

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

document.getElementById('product-form').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.harga = parseFloat(data.harga);
    data.stok = parseInt(data.stok);

    let method = currentMode === 'add' ? 'POST' : 'PUT';
    let url = PRODUCT_ENDPOINT;

    if (currentMode === 'edit') {
        url += `?id=${data.id_produk}`; // Menggunakan query string untuk ID pada PUT/DELETE
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
    // KOREKSI PESAN KONFIRMASI: Mengganti ID dengan Nama Produk
    if (!confirm(`Anda yakin ingin menghapus produk "${productName}"? Aksi ini tidak dapat dibatalkan.`)) {
        return;
    }

    try {
        const response = await fetch(`${PRODUCT_ENDPOINT}?id=${productId}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            loadProducts(); // Muat ulang tabel
        } else {
            alert("Penghapusan GAGAL: " + result.message);
        }

    } catch (error) {
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
        // Memuat ulang produk untuk filtering (jika terlalu banyak data, ini harus di backend)
        // Untuk saat ini, kita filter di frontend saja (efisiensi pada data kecil)
        loadProducts().then(() => {
            const tableBody = document.getElementById('product-table-body');
            const rows = tableBody.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const productName = rows[i].cells[1].textContent.toLowerCase(); // Kolom Nama Produk
                if (productName.includes(query)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    });
});