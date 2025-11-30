// FILE: script-report.js (Disesuaikan untuk Report dan History)

const REPORT_ENDPOINT = 'get_reports.php';

// Fungsi bantuan untuk memformat angka menjadi Rupiah
const formatRupiah = (number) => {
    if (isNaN(number)) return 'Rp 0';
    return 'Rp ' + Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

// --------------------------------------------------------
// A. LOGIKA UTAMA FILTER DAN FETCH DATA
// --------------------------------------------------------

function updateDateFilters(periodFilter) {
    const dateStartInput = document.getElementById('date-start');
    const dateEndInput = document.getElementById('date-end');
    const today = new Date(); // Objek hari ini yang TIDAK akan diubah
    let dateStart;
    
    // Set End Date
    dateEndInput.value = today.toISOString().split('T')[0];

    if (periodFilter === 'today') {
        dateStart = dateEndInput.value;
        
    } else if (periodFilter === 'weekly') {
        // KOREKSI: Buat salinan Date object
        let weekAgo = new Date(today); 
        weekAgo.setDate(today.getDate() - 7);
        dateStart = weekAgo.toISOString().split('T')[0];
        
    } else if (periodFilter === 'monthly') {
        // KOREKSI: Buat salinan Date object
        let monthAgo = new Date(today); 
        monthAgo.setMonth(today.getMonth() - 1);
        dateStart = monthAgo.toISOString().split('T')[0];
    }
    
    if (periodFilter !== 'custom') {
        dateStartInput.value = dateStart;
    }
    
    // Pastikan nilai yang dikembalikan adalah nilai dari input form
    return { start: dateStartInput.value, end: dateEndInput.value };
}


// --------------------------------------------------------
// B. FUNGSI UNTUK LAPORAN PENJUALAN (REPORT.PHP)
// --------------------------------------------------------

async function generateReport() {
    const periodFilter = document.getElementById('period-filter').value;
    const { start, end } = updateDateFilters(periodFilter);
    
    const url = `${REPORT_ENDPOINT}?start=${start}&end=${end}&summary_only=true`; 
    
    try {
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            // KIRIM SEMUA DATA BARU KE RENDERER
            renderSummary(result.total_income, result.total_transactions, result.avg_transaction); 
        } else {
            alert("Gagal memuat laporan pendapatan: " + result.message);
        }
    } catch (error) {
        alert("Koneksi ke server gagal saat memuat laporan.");
    }
}

// FUNGSI RENDER SUMMARY BARU
function renderSummary(totalIncome, totalTrans, avgTrans) {
    // Pastikan elemen ada sebelum diakses
    const incomeDisplay = document.getElementById('total-income-display');
    const transDisplay = document.getElementById('total-trans-display');
    const avgDisplay = document.getElementById('avg-trans-display');

    if (incomeDisplay) incomeDisplay.textContent = formatRupiah(totalIncome);
    if (transDisplay) transDisplay.textContent = totalTrans.toString();
    if (avgDisplay) avgDisplay.textContent = formatRupiah(avgTrans);
}

// --------------------------------------------------------
// C. FUNGSI UNTUK RIWAYAT TRANSAKSI (HISTORY.PHP)
// --------------------------------------------------------

async function generateHistory(page = 1) {
    const periodFilter = document.getElementById('period-filter').value;
    const { start, end } = updateDateFilters(periodFilter);

    const url = `${REPORT_ENDPOINT}?start=${start}&end=${end}&page=${page}&summary_only=false`; // Minta detail transaksi
    
    try {
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            renderTransactionTable(result.transactions);
            renderPagination(result.current_page, result.total_pages);
        } else {
            alert("Gagal memuat riwayat transaksi: " + result.message);
        }
    } catch (error) {
        alert("Koneksi ke server gagal saat memuat riwayat.");
    }
}

function renderTransactionTable(transactions) {
    const tableBody = document.getElementById('transaction-table-body');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (transactions.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Tidak ada data transaksi dalam periode ini.</td></tr>';
        return;
    }

    transactions.forEach(t => {
        const row = tableBody.insertRow();
        // Aksi dummy untuk detail struk
        const actionHtml = `
        <button class="action-btn print" onclick="printReceipt(${t.id_transaksi})">
            <i class="fas fa-print"></i> Struk
        </button>
        `;
        
        row.innerHTML = `
            <td>TRX${t.id_transaksi.toString().padStart(5, '0')}</td>
            <td>${t.waktu}</td>
            <td>${t.jumlah_item} Item</td>
            <td>${t.kasir}</td>
            <td>${formatRupiah(t.total_biaya)}</td>
            <td>${actionHtml}</td>
        `;
    });
}

function renderPagination(currentPage, totalPages) {
    const paginationDiv = document.querySelector('.pagination');
    if (!paginationDiv) return;
    
    paginationDiv.innerHTML = '';

    // Logika Pagination sama seperti sebelumnya, hanya ganti fungsi panggil
    // ...
    // Tombol Sebelumnya
    const prevBtn = document.createElement('button');
    prevBtn.innerHTML = '&laquo; Sebelumnya';
    prevBtn.disabled = currentPage === 1;
    if (currentPage > 1) {
        prevBtn.onclick = () => generateHistory(currentPage - 1);
    }
    paginationDiv.appendChild(prevBtn);

    // Tampilkan tombol halaman
    for (let i = 1; i <= totalPages; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        if (i === currentPage) {
            pageBtn.className = 'active';
        }
        pageBtn.onclick = () => generateHistory(i);
        paginationDiv.appendChild(pageBtn);
    }
    
    // Tombol Berikutnya
    const nextBtn = document.createElement('button');
    nextBtn.innerHTML = 'Berikutnya &raquo;';
    nextBtn.disabled = currentPage === totalPages;
    if (currentPage < totalPages) {
        nextBtn.onclick = () => generateHistory(currentPage + 1);
    }
    paginationDiv.appendChild(nextBtn);
}

function printReceipt(transactionId) {
    const url = 'struk.php?id=' + transactionId;
    
    // Buka jendela baru tanpa memicu cetak otomatis
    const receiptWindow = window.open(url, '_blank', 'width=350,height=600,toolbar=no,menubar=no,scrollbars=yes,resizable=yes');

    if (receiptWindow) {
        // HAPUS: logic onload dan window.print()
    }
}

function printReport() {
    // Ambil filter tanggal yang sedang aktif
    const periodFilter = document.getElementById('period-filter').value;
    const { start, end } = updateDateFilters(periodFilter); 

    const url = `cetak_laporan.php?start=${start}&end=${end}`; 

    // Buka jendela baru untuk menampilkan laporan
    const reportWindow = window.open(url, '_blank');

    if (reportWindow) {
        // Otomatis memicu cetak setelah laporan dimuat
        reportWindow.onload = function() {
            setTimeout(() => {
                reportWindow.print();
            }, 500); 
        };
    } else {
        alert("Gagal membuka jendela laporan. Pastikan pop-up diizinkan.");
    }
}