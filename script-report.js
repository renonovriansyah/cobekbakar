// FILE: script-report.js (KODE LENGKAP)

const REPORT_ENDPOINT = 'get_reports.php';

// Fungsi bantuan untuk memformat angka menjadi Rupiah
const formatRupiah = (number) => {
    if (isNaN(number)) return 'Rp 0';
    return 'Rp ' + Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

// --------------------------------------------------------
// A. FUNGSI UTAMA: MENGAMBIL DAN MERENDER LAPORAN
// --------------------------------------------------------

async function generateReport(page = 1) {
    const periodFilter = document.getElementById('period-filter').value;
    let dateStart = document.getElementById('date-start').value;
    let dateEnd = document.getElementById('date-end').value;
    
    // Auto-set tanggal berdasarkan filter cepat
    if (periodFilter !== 'custom') {
        const today = new Date();
        dateEnd = today.toISOString().split('T')[0];
        if (periodFilter === 'today') {
            dateStart = dateEnd;
        } else if (periodFilter === 'weekly') {
            const lastWeek = new Date(today.setDate(today.getDate() - 7));
            dateStart = lastWeek.toISOString().split('T')[0];
        } else if (periodFilter === 'monthly') {
            const lastMonth = new Date(today.setMonth(today.getMonth() - 1));
            dateStart = lastMonth.toISOString().split('T')[0];
        }
        document.getElementById('date-start').value = dateStart;
        document.getElementById('date-end').value = dateEnd;
    }
    
    // Buat URL query
    const url = `${REPORT_ENDPOINT}?start=${dateStart}&end=${dateEnd}&page=${page}`;
    
    try {
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            renderSummary(result.total_income);
            renderTransactionTable(result.transactions);
            renderPagination(result.current_page, result.total_pages);
        } else {
            alert("Gagal memuat laporan: " + result.message);
            console.error("Error Laporan:", result.message);
        }
    } catch (error) {
        alert("Koneksi ke server gagal saat memuat laporan.");
        console.error("Koneksi Error:", error);
    }
}

// --------------------------------------------------------
// B. MERENDER KOMPONEN TAMPILAN
// --------------------------------------------------------

function renderSummary(totalIncome) {
    document.getElementById('total-income-display').textContent = formatRupiah(totalIncome);
}

function renderTransactionTable(transactions) {
    const tableBody = document.getElementById('transaction-table-body');
    tableBody.innerHTML = '';
    
    if (transactions.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Tidak ada data transaksi dalam periode ini.</td></tr>';
        return;
    }

    transactions.forEach(t => {
        const row = tableBody.insertRow();
        row.innerHTML = `
            <td>TRX${t.id_transaksi.toString().padStart(5, '0')}</td>
            <td>${t.waktu}</td>
            <td>${t.jumlah_item} Item</td>
            <td>${t.kasir}</td>
            <td>${formatRupiah(t.total_biaya)}</td>
            <td><span style="color:var(--secondary-color);">Cetak</span></td>
        `;
    });
}

function renderPagination(currentPage, totalPages) {
    const paginationDiv = document.querySelector('.pagination');
    paginationDiv.innerHTML = '';

    // Tombol Sebelumnya
    const prevBtn = document.createElement('button');
    prevBtn.innerHTML = '&laquo; Sebelumnya';
    prevBtn.disabled = currentPage === 1;
    if (currentPage > 1) {
        prevBtn.onclick = () => generateReport(currentPage - 1);
    }
    paginationDiv.appendChild(prevBtn);

    // Tampilkan tombol halaman
    for (let i = 1; i <= totalPages; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        if (i === currentPage) {
            pageBtn.className = 'active';
        }
        pageBtn.onclick = () => generateReport(i);
        paginationDiv.appendChild(pageBtn);
    }
    
    // Tombol Berikutnya
    const nextBtn = document.createElement('button');
    nextBtn.innerHTML = 'Berikutnya &raquo;';
    nextBtn.disabled = currentPage === totalPages;
    if (currentPage < totalPages) {
        nextBtn.onclick = () => generateReport(currentPage + 1);
    }
    paginationDiv.appendChild(nextBtn);
}


function printReport() {
    alert('Fungsi Mencetak Laporan sedang disiapkan...');
    // Logika cetak ke PDF/Printer A4
}

// --------------------------------------------------------
// C. INISIALISASI
// --------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
    // Event listener untuk filter periode
    document.getElementById('period-filter').addEventListener('change', () => generateReport(1));
    document.getElementById('date-start').addEventListener('change', () => generateReport(1));
    document.getElementById('date-end').addEventListener('change', () => generateReport(1));
    
    // Muat laporan awal (default)
    generateReport();
});