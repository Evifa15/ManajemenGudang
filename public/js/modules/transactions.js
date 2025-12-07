// manajemengudang/public/js/modules/transactions.js

/**
 * --------------------------------------------------------------------------
 * Transactions Module
 * Menangani logika Barang Masuk, Keluar, Retur, dan Riwayat Transaksi.
 * --------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Inisialisasi Form Barang Masuk
    if (document.getElementById('formBarangMasukPage')) {
        initFormBarangMasuk();
    }

    // 2. Inisialisasi Form Barang Keluar
    if (document.getElementById('formBarangKeluarPage')) {
        initFormBarangKeluar();
    }

    // 3. Inisialisasi Form Retur
    if (document.getElementById('formReturRusakPage')) {
        initFormRetur();
    }

    // 4. Inisialisasi Halaman Riwayat (Masuk/Keluar/Retur)
    if (document.querySelector('.history-top-bar')) {
        initHistoryPages();
    }
});

/**
 * [HELPER] Logika Multi-File Upload yang dapat digunakan kembali.
 */
function handleFileMultiUpload(btnAddId, inputTempId, inputFinalId, previewContainerId, resetBtnId) {
    const btnAddFile = document.getElementById(btnAddId);
    const inputTemp = document.getElementById(inputTempId);
    const inputFinal = document.getElementById(inputFinalId);
    const previewContainer = document.getElementById(previewContainerId);
    const btnReset = document.getElementById(resetBtnId);

    if (!btnAddFile || !inputTemp || !inputFinal || !previewContainer) return;

    const dt = new DataTransfer(); 

    // Internal function to create preview element
    function createPreviewElement(file) {
        const div = document.createElement('div');
        div.className = 'file-preview-item';
        div.style.cssText = 'position: relative; width: 80px; height: 80px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);';

        const btnRemove = document.createElement('button');
        btnRemove.innerHTML = '&times;';
        btnRemove.style.cssText = 'position: absolute; top: 2px; right: 2px; background: rgba(239, 68, 68, 0.9); color: white; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 14px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 10;';
        
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
            div.appendChild(img);
        } else {
            div.innerHTML += '<i class="ph ph-file-pdf" style="font-size: 30px; color: #ef4444;"></i>';
        }

        btnRemove.addEventListener('click', () => {
            div.remove();
            const newDt = new DataTransfer();
            for (let j = 0; j < dt.files.length; j++) {
                if (dt.files[j].uniqueId !== file.uniqueId) {
                    newDt.items.add(dt.files[j]);
                }
            }
            dt.items.clear();
            for (let k = 0; k < newDt.files.length; k++) dt.items.add(newDt.files[k]);
            inputFinal.files = newDt.files; // FIX: Gunakan newDt.files
        });

        div.appendChild(btnRemove);
        const placeholder = previewContainer.querySelector('.upload-area-dashed');
        if(placeholder) placeholder.style.display = 'none';
        previewContainer.appendChild(div);
    }
    
    // Internal function to handle files
    function handleFiles(files) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.size > 5 * 1024 * 1024) { 
                Swal.fire('Gagal', `File ${file.name} terlalu besar (Max 5MB)`, 'warning');
                continue;
            }
            file.uniqueId = Date.now() + i; 
            dt.items.add(file);
            createPreviewElement(file);
        }
        inputFinal.files = dt.files;
        inputTemp.value = ''; 
    }

    // Set Listeners
    btnAddFile.addEventListener('click', () => inputTemp.click());
    inputTemp.addEventListener('change', function() { handleFiles(this.files); });

    // Reset Logic
    function resetFiles() {
        dt.items.clear();
        inputFinal.value = '';
        const placeholder = previewContainer.querySelector('.upload-area-dashed');
        if(!placeholder) {
            previewContainer.innerHTML = '<div class="upload-area-dashed">Belum ada file dipilih</div>';
        }
    }
    
    if (btnReset) {
        btnReset.addEventListener('click', resetFiles);
    }

    return { dt: dt, resetFiles: resetFiles };
}

/**
 * ============================================================================
 * A. FORM BARANG MASUK
 * ============================================================================
 */
function initFormBarangMasuk() {
    if (window.WMSScanner) {
        window.WMSScanner.autoInitProductSearch();
    }

    // Menggunakan helper umum untuk multi file upload
    const fileUploader = handleFileMultiUpload(
        'btn-add-file', 
        'bukti_foto_input', 
        'bukti_foto_final', 
        'preview-container', 
        'btnResetBarangMasuk'
    );
    
    // Auto Batch Logic (dari Step 2)
    const btnAutoBatch = document.getElementById('btnAutoBatch');
    const lotNumberInput = document.getElementById('lot_number');
    const baseUrl = document.querySelector('main').dataset.baseUrl;
    
    if (btnAutoBatch && lotNumberInput) {
        btnAutoBatch.addEventListener('click', () => {
            lotNumberInput.value = 'Generating...';
            lotNumberInput.setAttribute('readonly', true);

            fetch(`${baseUrl}staff/getAutoBatchCode`)
                .then(res => res.json())
                .then(data => {
                    if (data.code) {
                        lotNumberInput.value = data.code;
                        lotNumberInput.style.backgroundColor = '#fffbeb';
                        setTimeout(() => lotNumberInput.style.backgroundColor = '', 500);
                    } else {
                         lotNumberInput.value = 'ERROR';
                    }
                })
                .catch(err => {
                    console.error("Gagal generate kode batch:", err);
                    lotNumberInput.value = 'ERROR';
                })
                .finally(() => lotNumberInput.removeAttribute('readonly'));
        });
    }
}

/**
 * ============================================================================
 * B. FORM BARANG KELUAR
 * ============================================================================
 */
function initFormBarangKeluar() {
    initStockSelectionLogic('formBarangKeluar');
    
    // 🔥 [BARU] Inisialisasi Multi File Upload untuk Barang Keluar
    const fileUploader = handleFileMultiUpload(
        'btn-add-file-keluar', 
        'bukti_foto_input_keluar', 
        'bukti_foto_final_keluar', 
        'preview-container-keluar', 
        'btnResetBarangKeluar'
    );
}

/**
 * ============================================================================
 * C. FORM RETUR
 * ============================================================================
 */
function initFormRetur() {
    initStockSelectionLogic('formReturRusak');

    const btnReset = document.getElementById('btnResetRetur');
    if (btnReset) {
        // Karena di helper sudah ada logic reset files, kita bisa pakai itu juga.
        // Asumsi form retur menggunakan ID yang sudah ada untuk upload file.
        // Jika tidak, Anda perlu memanggil handleFileMultiUpload di sini juga.
        
        // --- LOGIKA RESET MANUAL (JIKA TIDAK MENGGUNAKAN HELPER BARU) ---
        // btnReset.addEventListener('click', () => {
        //     const previewContainer = document.getElementById('preview-container');
        //     const placeholder = previewContainer.querySelector('.upload-area-dashed');
        //     if(!placeholder) {
        //          previewContainer.innerHTML = '<div class="upload-area-dashed">Belum ada foto dipilih</div>';
        //     }
        // });
        // --- AKHIR LOGIKA RESET MANUAL ---
    }
    
    // Jika form retur juga menggunakan multi-upload (asumsi: form_retur_rusak.php)
    handleFileMultiUpload(
        'btn-add-file', // Asumsi ID ini dipakai juga di Retur
        'bukti_foto_input', 
        'bukti_foto_final', 
        'preview-container', 
        'btnResetRetur'
    );
}

/**
 * [SHARED] Logic Dropdown Stok & FEFO (First Expired First Out)
 */
function initStockSelectionLogic(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const baseUrl = form.dataset.baseUrl;
    const productSelect = form.querySelector('select[name="product_id"]');
    const stockSelect = form.querySelector('select[name="stock_id"]');
    const jumlahInput = form.querySelector('input[name="jumlah"]');
    const stockWidget = document.getElementById('stock_info_widget');
    const jumlahMaxInfo = document.getElementById('jumlah_max_info');
    
    // Field Readonly untuk Info Stok
    const viewQty = document.getElementById('view_qty');
    const viewProd = document.getElementById('view_prod');
    const viewExp = document.getElementById('view_exp');
    let currentStockData = [];

    if (window.WMSScanner) window.WMSScanner.autoInitProductSearch();

    // 1. Saat Barang Dipilih -> Ambil Data Stok
    productSelect.addEventListener('change', async function() {
        const productId = this.value;
        resetStockFields();
        if (!productId) return;

        try {
            stockSelect.innerHTML = '<option>Memuat data stok...</option>';
            
            // 🔥 [FIX UTAMA] Tambahkan header AJAX agar Controller tidak redirect
            const response = await fetch(`${baseUrl}staff/getStockInfo/${productId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            // 🔥 [FIX Sesi] Handle response 401 (Unauthorized) dari constructor
            if (response.status === 401) {
                const errorData = await response.json();
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Habis', 
                    text: 'Sesi Anda telah berakhir. Harap login kembali.',
                }).then(() => {
                    window.location.href = errorData.redirect || `${baseUrl}auth/index`;
                });
                return; 
            }
            
            // Cek jika ada Error 500 (dari output buffer di Controller)
            if (!response.ok) {
                 const errorData = await response.json();
                 console.error('AJAX Server Error:', errorData);
                 Swal.fire('Gagal Ambil Data', 'Terjadi error di server. Cek konsol browser.', 'error');
                 return;
            }

            // 🔥 Ini adalah titik kegagalan sebelumnya (SyntaxError)
            const data = await response.json(); 
            
            currentStockData = data;
            stockSelect.innerHTML = '';

            if (data.length > 0) {
                stockSelect.disabled = false;
                if(stockWidget) stockWidget.style.display = 'block';

                data.forEach((item, index) => {
                    const labelBatch = item.lot_number || 'Tanpa Batch';
                    const option = document.createElement('option');
                    option.value = item.stock_id;
                    option.textContent = labelBatch; 
                    
                    if (index === 0) {
                        option.textContent = `★ ${labelBatch} (FEFO)`; 
                        option.style.fontWeight = 'bold';
                    }
                    
                    stockSelect.appendChild(option);
                });
                stockSelect.selectedIndex = 0;
                stockSelect.dispatchEvent(new Event('change'));
            } else {
                stockSelect.innerHTML = '<option value="">❌ Stok Kosong / Tidak Tersedia</option>';
                stockSelect.disabled = true;
                if(stockWidget) stockWidget.style.display = 'none';
                Swal.fire('Stok Kosong', 'Barang ini tidak memiliki stok tersedia.', 'warning');
            }
        } catch (error) {
            // 🔥 Catch ini hanya menangkap error jaringan (fetch/json parsing murni)
            console.error('Gagal ambil stok (Network/JSON Parse Error):', error);
             Swal.fire('Error Koneksi', 'Gagal menghubungi server.', 'error');
        }
    });

    // 2. Saat Batch Dipilih -> Tampilkan Detail
    stockSelect.addEventListener('change', function() {
        const selectedId = this.value;
        const stockItem = currentStockData.find(s => s.stock_id == selectedId);

        if (stockItem) {
            if(viewQty) viewQty.value = stockItem.quantity;
            
            if (window.WMSFormatting) {
                if(viewProd) viewProd.value = window.WMSFormatting.formatDateIndo(stockItem.production_date);
                if(viewExp) viewExp.value = window.WMSFormatting.formatDateIndo(stockItem.exp_date);
            } else {
                if(viewProd) viewProd.value = stockItem.production_date || '-'; 
                if(viewExp) viewExp.value = stockItem.exp_date || '-';
            }
            
            const unitName = stockItem.nama_satuan || 'Pcs'; // Menggunakan nama_satuan dari model
            const maxQuantity = parseInt(stockItem.quantity) || 0;

            if(jumlahInput) {
                jumlahInput.max = maxQuantity; 
                jumlahInput.value = 1;
            }
            if(jumlahMaxInfo) {
                jumlahMaxInfo.textContent = `Maksimal pengeluaran: ${maxQuantity} ${unitName}`;
                jumlahMaxInfo.style.color = '#64748b';
                jumlahMaxInfo.dataset.unit = unitName;
            }
        }
    });

    // 3. Validasi Input Jumlah (Realtime)
    if(jumlahInput) {
        jumlahInput.addEventListener('input', function() {
            const max = parseInt(this.max);
            const val = parseInt(this.value);
            const unit = jumlahMaxInfo ? (jumlahMaxInfo.dataset.unit || 'Pcs') : 'Pcs'; 

            if (val > max) {
                this.value = max;
                if(jumlahMaxInfo) {
                    jumlahMaxInfo.textContent = `❌ Jumlah melebihi stok tersedia! Maksimal ${max} ${unit}.`; 
                    jumlahMaxInfo.style.color = 'red';
                }
            } else if (val < 1 && max > 0) {
                 this.value = 1;
            } else if(jumlahMaxInfo) {
                jumlahMaxInfo.textContent = `Maksimal pengeluaran: ${max} ${unit}`;
                jumlahMaxInfo.style.color = '#64748b';
            }
        });
    }


    // Helper Reset
    function resetStockFields() {
        if(stockSelect) {
            stockSelect.innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
            stockSelect.disabled = true;
        }
        
        if(viewQty) viewQty.value = '';
        if(viewProd) viewProd.value = '';
        if(viewExp) viewExp.value = '';
        
        if(jumlahInput) {
            jumlahInput.value = 1;
            jumlahInput.removeAttribute('max');
        }
        
        if(jumlahMaxInfo) {
            jumlahMaxInfo.textContent = '';
            delete jumlahMaxInfo.dataset.unit;
        }
        
        // Reset file input (asumsi ID ini dipakai juga di form retur)
        const inputFinal = document.getElementById('bukti_foto_final');
        const previewContainer = document.getElementById('preview-container');

        if(inputFinal) inputFinal.value = '';
        if(previewContainer && previewContainer.querySelector('.upload-area-dashed') === null) {
            previewContainer.innerHTML = '<div class="upload-area-dashed">Belum ada foto dipilih</div>';
        }
    }
}

/**
 * ============================================================================
 * D. HALAMAN RIWAYAT (Search, Filter & EXPORT)
 * ============================================================================
 */
function initHistoryPages() {
    let config = {};

    // 1. CONFIG BARANG MASUK
    if (document.getElementById('liveSearchMasuk')) {
        config = {
            searchId: 'liveSearchMasuk',
            tableId: 'tableBodyMasuk',
            startId: 'startDateMasuk',
            endId: 'endDateMasuk',
            pagId: 'paginationContainerMasuk',
            urlEndpoint: 'admin/riwayatBarangMasuk',
            
            toggleId: 'btnToggleFilterMasuk',
            panelId: 'filterPanelMasuk',
            resetId: 'btnResetMasuk',
            
            exportToggleId: 'btnToggleExportMasuk',
            exportMenuId: 'exportMenuMasuk',
            exportBtnClass: '.btn-export-masuk-action',
            exportEndpoint: 'admin/exportRiwayatMasuk'
        };
    
    // 2. CONFIG BARANG KELUAR 
    } else if (document.getElementById('liveSearchKeluar')) {
        config = {
            searchId: 'liveSearchKeluar',
            tableId: 'tableBodyKeluar',
            startId: 'startDateKeluar',
            endId: 'endDateKeluar',
            pagId: 'paginationContainerKeluar',
            urlEndpoint: 'admin/riwayatBarangKeluar',
            
            
            toggleId: 'btnToggleFilter', 
            panelId: 'filterPanel',      
            resetId: 'btnResetKeluar',
            
            exportToggleId: 'btnToggleExportKeluar',      
            exportMenuId: 'exportMenuKeluar',             
            exportBtnClass: '.btn-export-keluar-action',  
            exportEndpoint: 'admin/exportRiwayatKeluar'
        };

    // 3. CONFIG RETUR
    } else if (document.getElementById('liveSearchRetur')) {
        config = {
            searchId: 'liveSearchRetur',
            tableId: 'tableBodyRetur',
            startId: 'startDateRetur',
            endId: 'endDateRetur',
            pagId: 'paginationContainerRetur',
            urlEndpoint: 'admin/riwayatReturRusak',
            
            toggleId: 'btnToggleFilter', 
            panelId: 'filterPanel',
            resetId: 'btnResetRetur',
            
            exportToggleId: 'btnToggleExportRetur',
            exportMenuId: 'exportMenuRetur',
            exportBtnClass: '.btn-export-retur-action',
            exportEndpoint: 'admin/exportRiwayatReturRusak'
        };
    } else {
        return;
    }

    // --- LOGIKA UTAMA (Export logic tidak diubah, hanya memastikan ID-nya terambil) ---
    const searchInput = document.getElementById(config.searchId);
    const tableBody = document.getElementById(config.tableId);
    const startDate = document.getElementById(config.startId);
    const endDate = document.getElementById(config.endId);
    const pagContainer = document.getElementById(config.pagId);
    const baseUrl = document.querySelector('main').dataset.baseUrl;

    function loadData(page = 1) {
        const params = new URLSearchParams({
            ajax: 1,
            page: page,
            search: searchInput.value,
            start_date: startDate ? startDate.value : '',
            end_date: endDate ? endDate.value : ''
        });

        tableBody.style.opacity = '0.5';

        fetch(`${baseUrl}${config.urlEndpoint}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = data.html;
                tableBody.style.opacity = '1';
                if (window.WMSUI) {
                    window.WMSUI.renderPagination(pagContainer, data.totalPages, data.currentPage, loadData);
                }
            })
            .catch(err => {
                console.error(err);
                tableBody.style.opacity = '1';
            });
    }

    searchInput.addEventListener('input', () => loadData(1));
    if(startDate) startDate.addEventListener('change', () => loadData(1));
    if(endDate) endDate.addEventListener('change', () => loadData(1));

    if (pagContainer && pagContainer.dataset.totalPages) {
        if (window.WMSUI) {
            window.WMSUI.renderPagination(pagContainer, pagContainer.dataset.totalPages, pagContainer.dataset.currentPage, loadData);
        }
    }

    // Toggle Filter Panel
    const btnToggle = document.getElementById(config.toggleId);
    const panel = document.getElementById(config.panelId);
    if (btnToggle && panel) {
        btnToggle.addEventListener('click', () => {
            if (panel.style.display === 'none' || panel.style.display === '') {
                panel.style.display = 'block';
                btnToggle.style.backgroundColor = '#e2e8f0';
                btnToggle.style.color = '#152e4d';
                btnToggle.style.borderColor = '#152e4d';
            } else {
                panel.style.display = 'none';
                btnToggle.style.backgroundColor = '#fff';
                btnToggle.style.color = '#64748b';
                btnToggle.style.borderColor = '#cbd5e1';
            }
        });
    }

    // Reset Filter
    const btnReset = document.getElementById(config.resetId);
    if (btnReset) {
        btnReset.addEventListener('click', () => {
            searchInput.value = '';
            if(startDate) startDate.value = '';
            if(endDate) endDate.value = '';
            loadData(1);
        });
    }

    // Logic Export (Tombol ini yang seharusnya bekerja sekarang)
    const btnExport = document.getElementById(config.exportToggleId);
    const menuExport = document.getElementById(config.exportMenuId);

    if (btnExport && menuExport) {
        btnExport.addEventListener('click', (e) => {
            e.stopPropagation();
            menuExport.classList.toggle('show');
        });
        window.addEventListener('click', (e) => {
            if (!btnExport.contains(e.target) && !menuExport.contains(e.target)) {
                menuExport.classList.remove('show');
            }
        });
        const exportLinks = document.querySelectorAll(config.exportBtnClass);
        exportLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const type = link.dataset.type;
                const params = new URLSearchParams({
                    search: searchInput.value,
                    start_date: startDate ? startDate.value : '',
                    end_date: endDate ? endDate.value : ''
                });
                menuExport.classList.remove('show');

                if (type === 'pdf') {
                    // 🔥 LOGIKA KHUSUS PDF (Client-side HTML2PDF) 🔥
                    if (window.WMSUI && typeof html2pdf !== 'undefined') {
                        window.WMSUI.showLoading('Memproses PDF...', 'Mengambil data dan merender dokumen...');
                    } else {
                        Swal.fire('Error', 'Library PDF Generator (html2pdf) belum dimuat/tidak tersedia.', 'error');
                        return; 
                    }

                    // 1. Fetch HTML dari Controller (Controller hanya output HTML polos)
                    fetch(`${baseUrl}${config.exportEndpoint}/${type}?${params.toString()}`)
                        .then(res => res.text())
                        .then(html => {
                            // 2. Buat elemen container dari HTML yang di-fetch
                            const container = document.createElement('div');
                            container.innerHTML = html;
                            
                            const opt = {
                                margin: 10,
                                filename: `${config.urlEndpoint.split('/')[1]}_${new Date().toISOString().slice(0,10)}.pdf`,
                                image: { type: 'jpeg', quality: 0.98 },
                                html2canvas: { scale: 2 },
                                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                            };
                            
                            // 3. Render dan Simpan PDF
                            html2pdf().set(opt).from(container).save().then(() => {
                                if (window.WMSUI) Swal.close();
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Gagal', 'Gagal memuat data untuk PDF.', 'error');
                        });

                } else {
                    // LOGIKA CSV/EXCEL (Direct Download)
                    if (window.WMSUI) window.WMSUI.showLoading('Memproses Export...', type.toUpperCase());
                    setTimeout(() => {
                        window.location.href = `${baseUrl}${config.exportEndpoint}/${type}?${params.toString()}`;
                        setTimeout(() => { if (Swal.isVisible()) Swal.close(); }, 3000);
                    }, 500);
                }
            });
        });
    }
}