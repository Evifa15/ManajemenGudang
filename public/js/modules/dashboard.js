/**
 * --------------------------------------------------------------------------
 * Dashboard Module
 * Menangani visualisasi grafik (Chart.js) dan widget dashboard.
 * --------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
    initDashboardCharts();
});

function initDashboardCharts() {
    // 1. Grafik Dashboard Utama (Pemilik & Admin)
    // ID: grafikTransaksi
    const ctxDashboard = document.getElementById('grafikTransaksi');
    if (ctxDashboard) {
        renderChart(ctxDashboard);
    }

    // 2. Grafik Laporan Analitik (Admin - Laporan Transaksi)
    // ID: grafikAnalitik
    const ctxAnalitik = document.getElementById('grafikAnalitik');
    if (ctxAnalitik) {
        renderChart(ctxAnalitik);
    }
}

/**
 * Fungsi Render Chart Generic
 * Membaca data JSON dari atribut data-* di elemen <canvas> HTML.
 * @param {HTMLCanvasElement} canvasElement 
 */
function renderChart(canvasElement) {
    // Pastikan library Chart.js sudah dimuat di footer
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js belum dimuat. Grafik tidak dapat ditampilkan.');
        return;
    }

    try {
        // Ambil data dari atribut HTML (data-labels, data-masuk, data-keluar)
        // Gunakan fallback '[]' jika data kosong agar tidak error
        const rawLabels = canvasElement.dataset.labels;
        const rawMasuk = canvasElement.dataset.masuk;
        const rawKeluar = canvasElement.dataset.keluar;

        if (!rawLabels || !rawMasuk || !rawKeluar) {
            console.warn('Data grafik tidak lengkap pada elemen:', canvasElement.id);
            return;
        }

        const labels = JSON.parse(rawLabels);
        const dataMasuk = JSON.parse(rawMasuk);
        const dataKeluar = JSON.parse(rawKeluar);

        // Inisialisasi Chart
        new Chart(canvasElement, {
            type: 'bar', // Ubah ke 'line' jika ingin grafik garis
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Barang Masuk',
                        data: dataMasuk,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)', // Biru Transparan
                        borderColor: 'rgba(54, 162, 235, 1)',       // Biru Solid
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Barang Keluar',
                        data: dataKeluar,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)', // Merah Transparan
                        borderColor: 'rgba(255, 99, 132, 1)',       // Merah Solid
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Agar grafik menyesuaikan tinggi container
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            font: { family: "'Poppins', sans-serif", size: 12 }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(21, 46, 77, 0.9)', // Warna Brand Dark Blue
                        titleFont: { family: "'Poppins', sans-serif", size: 13 },
                        bodyFont: { family: "'Poppins', sans-serif", size: 12 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9', // Garis grid halus
                            borderDash: [5, 5]
                        },
                        ticks: {
                            font: { family: "'Poppins', sans-serif" },
                            precision: 0 // Agar tidak ada angka desimal (0.5 item)
                        }
                    },
                    x: {
                        grid: {
                            display: false // Hilangkan grid vertikal
                        },
                        ticks: {
                            font: { family: "'Poppins', sans-serif" }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });

    } catch (error) {
        console.error("Gagal merender grafik:", error);
    }
}