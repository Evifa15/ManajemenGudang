/**
 * --------------------------------------------------------------------------
 * Stock Opname Module
 * Menangani logika halaman Perintah Opname (Admin) dan Input Opname (Staff).
 * --------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Inisialisasi Halaman Perintah Opname (Admin)
    if (document.getElementById('opnamePerintahPage')) {
        initAdminOpnamePage();
    }

    // 2. Inisialisasi Halaman Input Opname (Staff)
    // Kita deteksi keberadaan form input opname
    const inputForm = document.querySelector('form[action*="processInputOpname"]');
    if (inputForm) {
        initStaffOpnamePage(inputForm);
    }
});

/**
 * ============================================================================
 * A. LOGIKA ADMIN (Perintah Opname)
 * ============================================================================
 */
function initAdminOpnamePage() {
    const page = document.getElementById('opnamePerintahPage');
    const baseUrl = page.dataset.baseUrl;

    // 1. Fitur Check All Kategori
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.cat-check');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    // 2. Fitur Popup Detail Kategori
    // (Admin bisa melihat daftar barang apa saja di dalam kategori sebelum memilih)
    const detailButtons = document.querySelectorAll('.btn-detail-cat');
    
    detailButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const catId = this.dataset.id;
            const catName = this.dataset.nama;
            const url = `${baseUrl}admin/getCategoryDetails/${catId}`;

            // Tampilkan Loading
            if (window.WMSUI) window.WMSUI.showLoading('Memuat Data...', 'Mengambil daftar barang...');

            // Fetch Data
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil data');
                    return response.json();
                })
                .then(data => {
                    // Tutup Loading
                    Swal.close();

                    let htmlContent = '';
                    if (data.length === 0) {
                        htmlContent = '<div style="padding:20px; text-align:center; color:#666;">Tidak ada barang dalam kategori ini.</div>';
                    } else {
                        // Buat Tabel Sederhana
                        htmlContent = `
                            <div style="text-align: left; max-height: 300px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                                    <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 1;">
                                        <tr>
                                            <th style="padding: 10px; border-bottom: 2px solid #e2e8f0; color: #152e4d;">Kode</th>
                                            <th style="padding: 10px; border-bottom: 2px solid #e2e8f0; color: #152e4d;">Nama Barang</th>
                                            <th style="padding: 10px; border-bottom: 2px solid #e2e8f0; color: #152e4d;">Merek</th>
                                            <th style="padding: 10px; border-bottom: 2px solid #e2e8f0; color: #152e4d;">Lokasi Utama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.forEach((item, index) => {
                            const bg = index % 2 === 0 ? '#fff' : '#f9f9f9';
                            htmlContent += `
                                <tr style="background-color: ${bg};">
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                        <span style="font-family:monospace; font-weight:600;">${item.kode_barang}</span>
                                    </td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${item.nama_barang}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${item.nama_merek || '-'}</td>
                                    <td style="padding: 8px; border-bottom: 1px solid #eee;">${item.lokasi_utama || '-'}</td>
                                </tr>
                            `;
                        });

                        htmlContent += `</tbody></table></div>`;
                    }

                    // Tampilkan Popup
                    Swal.fire({
                        title: `Detail: ${catName}`,
                        html: htmlContent,
                        width: '700px',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#152e4d'
                    });
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Gagal memuat data barang.', 'error');
                });
        });
    });
}

/**
 * ============================================================================
 * B. LOGIKA STAFF (Input Opname)
 * ============================================================================
 */
function initStaffOpnamePage(form) {
    const productSelect = document.getElementById('product_id');
    const lotFields = document.getElementById('lot_tracking_fields');
    const lotNumberInput = document.getElementById('lot_number');
    
    // Kita butuh data JSON produk (biasanya di-embed di view PHP)
    // Tapi kita bisa akali dengan membaca atribut data-lacak_lot di <option>
    
    // Listener saat barang dipilih
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const lacakLot = selectedOption.getAttribute('data-lacak_lot');

        // Logic Toggle Field Lot
        if (lacakLot == "1") {
            // Jika barang ini wajib tracking lot
            if (lotFields) {
                lotFields.style.display = 'block';
                // Tambahkan animasi fade in
                lotFields.style.animation = 'fadeIn 0.3s ease-out';
            }
            if (lotNumberInput) {
                lotNumberInput.required = true;
                lotNumberInput.placeholder = "Masukkan Nomor Batch / Lot (Wajib)";
                lotNumberInput.focus();
            }
        } else {
            // Barang biasa
            if (lotFields) lotFields.style.display = 'none';
            if (lotNumberInput) {
                lotNumberInput.required = false;
                lotNumberInput.value = ''; // Reset nilai
            }
        }
    });

    // Validasi Submit
    form.addEventListener('submit', function(e) {
        const stokInput = document.getElementById('stok_fisik');
        
        if (stokInput.value < 0) {
            e.preventDefault();
            Swal.fire('Input Salah', 'Jumlah fisik tidak boleh negatif.', 'warning');
            return;
        }

        // Tampilkan Loading saat submit
        const btnSubmit = this.querySelector('button[type="submit"]');
        if(btnSubmit) {
            btnSubmit.innerHTML = '<i class="ph ph-spinner" style="animation: spin 1s linear infinite;"></i> Menyimpan...';
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.7';
        }
    });
}