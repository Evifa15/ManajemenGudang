/**
 * --------------------------------------------------------------------------
 * Master Data Module
 * Menangani CRUD Barang, User, Supplier, Kategori, Lokasi, dll.
 * Termasuk logika Tabulasi untuk halaman Konfigurasi Master.
 * --------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Inisialisasi Halaman Manajemen Barang (List View)
    if (document.getElementById('liveSearchBarang')) {
        initBarangPage();
    }

    // 2. Inisialisasi Halaman Manajemen User
    if (document.getElementById('liveSearchInput')) {
        initUserPage();
    }

    // 3. Inisialisasi Halaman Konfigurasi Master
    if (document.getElementById('masterDataPage')) {
        initMasterConfigPage();
    }

    // 4. Global: Auto Generate Kode
    initAutoCodeGenerator();

    // 5. Inisialisasi Form Barang (Tambah/Edit)
    if (document.getElementById('formBarang')) {
        initBarangForm(); 
    }
});

/**
 * ============================================================================
 * A. LOGIKA HALAMAN BARANG (admin/barang)
 * ============================================================================
 */
function initBarangPage() {
    const searchInput = document.getElementById('liveSearchBarang');
    const tableBody = document.getElementById('barangTableBody');
    const paginationContainer = document.getElementById('paginationContainerBarang');
    const baseUrl = searchInput ? searchInput.dataset.baseUrl : '';
    
    // Filters
    const filters = {
        kategori: document.getElementById('filterKategori'),
        merek: document.getElementById('filterMerek'),
        status: document.getElementById('filterStatus'),
        lokasi: document.getElementById('filterLokasi')
    };

    // Bulk Actions
    const btnBulkDelete = document.getElementById('btnBulkDeleteBarang');
    const selectAll = document.getElementById('selectAllBarang');
    const countSpan = document.getElementById('selectedCountBarang');

    // --- Logic Tombol Filter (Toggle Show/Hide) ---
    const btnToggleFilter = document.getElementById('btnToggleFilter');
    const filterPanel = document.getElementById('filterPanel');

    if (btnToggleFilter && filterPanel) {
        btnToggleFilter.addEventListener('click', () => {
            const isHidden = filterPanel.style.display === 'none' || filterPanel.style.display === '';
            
            if (isHidden) {
                filterPanel.style.display = 'block';
                btnToggleFilter.style.backgroundColor = '#e2e8f0';
                btnToggleFilter.style.color = '#152e4d';
                btnToggleFilter.style.borderColor = '#152e4d';
            } else {
                filterPanel.style.display = 'none';
                btnToggleFilter.style.backgroundColor = '#fff';
                btnToggleFilter.style.color = '#64748b';
                btnToggleFilter.style.borderColor = '#cbd5e1';
            }
        });
    }

    // --- 1. Load Data (AJAX) ---
    function loadBarang(page = 1) {
        const params = new URLSearchParams({
            ajax: 1,
            page: page,
            search: searchInput.value,
            kategori: filters.kategori?.value || '',
            merek: filters.merek?.value || '',
            status: filters.status?.value || '',
            lokasi: filters.lokasi?.value || ''
        });

        tableBody.style.opacity = '0.5';

        fetch(`${baseUrl}admin/barang?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = data.html;
                tableBody.style.opacity = '1';
                
                // Gunakan helper pagination dari ui-helpers.js
                if (window.WMSUI) {
                    window.WMSUI.renderPagination(paginationContainer, data.totalPages, data.currentPage, (p) => loadBarang(p));
                }

                // Reset Checkbox
                if (selectAll) selectAll.checked = false;
                updateBulkButton();
            })
            .catch(err => {
                console.error(err);
                tableBody.style.opacity = '1';
            });
    }

    // --- 2. Event Listeners ---
    if (searchInput) {
        searchInput.addEventListener('input', () => loadBarang(1));
    }
    
    Object.values(filters).forEach(el => {
        if(el) el.addEventListener('change', () => loadBarang(1));
    });

   
    const btnReset = document.getElementById('btnResetFilter');
    if (btnReset) {
        btnReset.addEventListener('click', () => {
            searchInput.value = '';
            Object.values(filters).forEach(el => { if(el) el.value = ''; });
            loadBarang(1);
        });
    }

    // --- 3. Bulk Delete Logic ---
    function updateBulkButton() {
        const checked = document.querySelectorAll('.barang-checkbox:checked').length;
        if (countSpan) countSpan.textContent = checked;
        if (btnBulkDelete) btnBulkDelete.style.display = checked > 0 ? 'inline-flex' : 'none';
    }

    if (tableBody) {
        tableBody.addEventListener('change', (e) => {
            if (e.target.classList.contains('barang-checkbox')) updateBulkButton();
        });
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.barang-checkbox').forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }

    if (btnBulkDelete) {
        btnBulkDelete.addEventListener('click', function() {
            const ids = Array.from(document.querySelectorAll('.barang-checkbox:checked')).map(cb => cb.value);
            if (ids.length === 0) return;
            if (window.WMSUI) {
                WMSUI.showConfirm(
                    `Hapus ${ids.length} Barang?`, 
                    "Stok dan riwayat terkait barang ini juga akan dihapus permanen!"
                ).then((result) => {
                    if (result.isConfirmed) {
                        WMSUI.showLoading('Menghapus Data...'); 
                        
                        fetch(`${baseUrl}admin/deleteBulkBarang`, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ ids: ids })
                        }).then(() => {
                            Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                            loadBarang(1); 
                        }).catch(err => {
                            Swal.fire('Error', 'Gagal menghapus data.', 'error');
                        });
                    }
                });
            }
            
        });
    }
    // --- 4. LOGIKA EXPORT DATA BARANG ---
    const btnExport = document.getElementById('btnToggleExportBarang');
    const menuExport = document.getElementById('exportMenuBarang');

    if (btnExport && menuExport) {
        // Toggle Menu
        btnExport.addEventListener('click', function(e) {
            e.stopPropagation();
            menuExport.classList.toggle('show');
        });

        // Tutup Menu jika klik luar
        window.addEventListener('click', function(e) {
            if (!btnExport.contains(e.target) && !menuExport.contains(e.target)) {
                menuExport.classList.remove('show');
            }
        });

        // Handle Klik Item Export
        const exportActions = menuExport.querySelectorAll('.btn-export-action');
        
        exportActions.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const type = this.getAttribute('data-type');
                
                // Ambil nilai filter saat ini
                const searchVal = searchInput.value;
                const katVal = filters.kategori ? filters.kategori.value : '';
                const merkVal = filters.merek ? filters.merek.value : '';
                
                menuExport.classList.remove('show'); // Tutup menu

                const params = new URLSearchParams({
                    search: searchVal,
                    kategori: katVal,
                    merek: merkVal
                });

                if (type === 'pdf') {
                    // Logika PDF
                    if (window.WMSUI) window.WMSUI.showLoading('Menyiapkan PDF...');
                    else Swal.fire('Loading', 'Menyiapkan PDF...', 'info');

                    fetch(`${baseUrl}admin/exportBarang/${type}?${params.toString()}`)
                        .then(res => res.text())
                        .then(htmlContent => {
                            const container = document.createElement('div');
                            container.innerHTML = htmlContent;
                            const opt = {
                                margin: 10,
                                filename: `Laporan_Barang_${new Date().toISOString().slice(0,10)}.pdf`,
                                image: { type: 'jpeg', quality: 0.98 },
                                html2canvas: { scale: 2 },
                                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                            };
                            html2pdf().set(opt).from(container).save().then(() => Swal.close());
                        });
                } else {
                    // Logika Excel/CSV
                    if (window.WMSUI) window.WMSUI.showLoading('Mengunduh...');
                    setTimeout(() => {
                        window.location.href = `${baseUrl}admin/exportBarang/${type}?${params.toString()}`;
                        setTimeout(() => Swal.close(), 2000);
                    }, 500);
                }
            });
        });
    }

    // --- 5. Import CSV Popup (Barang) - NEW STYLE ---
    const btnImport = document.getElementById('btnImportCsv');
    if (btnImport) {
        btnImport.addEventListener('click', () => {
            Swal.fire({
                title: `<span class="import-modal-title">
                            <i class="ph ph-package"></i> Import Data Barang
                        </span>`,
                html: `
                    <form id="formImportBarang" action="${baseUrl}admin/processImportBarang" method="POST" enctype="multipart/form-data">
                        <div class="import-instruction-box">
                            <strong><i class="ph ph-info"></i> Petunjuk:</strong>
                            <ul>
                                <li>Gunakan file format <strong>.CSV</strong></li>
                                <li>Urutan Kolom: <em>Kode, Nama, Kategori, Merek, Satuan, Stok Minimum, Lokasi</em>.</li>
                            </ul>
                        </div>
                        <div class="file-upload-wrapper" id="dropZoneBarang">
                            <input type="file" id="csv_file_input" name="csv_file" accept=".csv" required 
                                   style="position: absolute; width: 100%; height: 100%; top:0; left:0; opacity: 0; cursor: pointer;">
                            <i class="ph ph-file-csv file-upload-icon"></i>
                            <span id="fileNameDisplay" class="file-upload-text">Klik atau Drag file CSV ke sini</span>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Upload',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                didOpen: () => {
                    const input = document.getElementById('csv_file_input');
                    const display = document.getElementById('fileNameDisplay');
                    const wrapper = document.getElementById('dropZoneBarang');
                    const icon = wrapper.querySelector('.file-upload-icon');

                    input.addEventListener('change', () => {
                        if (input.files.length > 0) {
                            display.textContent = input.files[0].name;
                            wrapper.classList.add('has-file');
                            icon.classList.replace('ph-file-csv', 'ph-check-circle');
                        } else {
                            display.textContent = 'Klik atau Drag file CSV ke sini';
                            wrapper.classList.remove('has-file');
                            icon.classList.replace('ph-check-circle', 'ph-file-csv');
                        }
                    });
                },
                preConfirm: () => {
                    if (!document.getElementById('csv_file_input').files.length) {
                        Swal.showValidationMessage('Pilih file dulu!'); return false;
                    }
                    document.getElementById('formImportBarang').submit();
                }
            });
        });
    }
}

/**
 * ============================================================================
 * B. LOGIKA HALAMAN USER (admin/users)
 * ============================================================================
 */
function initUserPage() {
    // 1. Inisialisasi Elemen Baru
    const searchInput = document.getElementById('liveSearchUser'); // ID Baru
    const tableBody = document.getElementById('userTableBody');
    const paginationContainer = document.getElementById('paginationContainerUsers');
    const filterRole = document.getElementById('filterRoleUser'); // ID Baru
    const baseUrl = searchInput ? searchInput.dataset.baseUrl : '';

    // Elemen Filter Toggle
    const btnToggleFilter = document.getElementById('btnToggleFilterUser');
    const filterPanel = document.getElementById('filterPanelUser');
    const btnResetFilter = document.getElementById('btnResetFilterUser');

    // Elemen Bulk Delete
    const btnBulkDelete = document.getElementById('btnBulkDeleteUser');
    const selectAll = document.getElementById('selectAllUser');
    const countSpan = document.getElementById('selectedCountUser');

    // --- 2. Logic Toggle Filter Panel ---
    if (btnToggleFilter && filterPanel) {
        btnToggleFilter.addEventListener('click', () => {
            const isHidden = filterPanel.style.display === 'none' || filterPanel.style.display === '';
            if (isHidden) {
                filterPanel.style.display = 'block';
                btnToggleFilter.style.backgroundColor = '#e2e8f0';
                btnToggleFilter.style.color = '#152e4d';
                btnToggleFilter.style.borderColor = '#152e4d';
            } else {
                filterPanel.style.display = 'none';
                btnToggleFilter.style.backgroundColor = '#fff';
                btnToggleFilter.style.color = '#64748b';
                btnToggleFilter.style.borderColor = '#cbd5e1';
            }
        });
    }

    // --- 3. Logic Reset Filter ---
    if (btnResetFilter) {
        btnResetFilter.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (filterRole) filterRole.value = '';
            loadUsers(1);
        });
    }

    // --- 4. Fungsi Load Data (AJAX) ---
    function loadUsers(page = 1) {
        const params = new URLSearchParams({
            ajax: 1,
            page: page,
            search: searchInput ? searchInput.value : '',
            role: filterRole ? filterRole.value : ''
        });

        tableBody.style.opacity = '0.5';

        // Fetch ke Controller (Response JSON)
        fetch(`${baseUrl}admin/users?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                // Render Ulang Tabel (Client-side Rendering dari JSON)
                renderUserTable(data.users);
                
                // Render Pagination
                if (window.WMSUI) {
                    window.WMSUI.renderPagination(paginationContainer, data.totalPages, data.currentPage, (p) => loadUsers(p));
                }

                // Reset Tampilan
                tableBody.style.opacity = '1';
                if (selectAll) selectAll.checked = false;
                updateBulkButton();
            })
            .catch(err => {
                console.error("Error loading users:", err);
                tableBody.style.opacity = '1';
            });
    }

    // --- 5. Helper Render HTML Tabel User ---
    function renderUserTable(users) {
        let html = '';
        if (!users || users.length === 0) {
            html = '<tr><td colspan="5" style="text-align:center; padding: 20px; color: #666;">Data tidak ditemukan.</td></tr>';
        } else {
            users.forEach(user => {
                let roleClass = 'color: #4b5563; background: #f3f4f6; border: 1px solid #d1d5db;';
                if(user.role === 'admin') roleClass = 'color: #7c3aed; background: #f3e8ff; border: 1px solid #d8b4fe;';
                else if(user.role === 'staff') roleClass = 'color: #059669; background: #ecfdf5; border: 1px solid #6ee7b7;';
                else if(user.role === 'pemilik') roleClass = 'color: #d97706; background: #fffbeb; border: 1px solid #fde68a;';
                const initial = user.nama_lengkap.charAt(0).toUpperCase();             
                const currentUserId = document.querySelector('meta[name="user_id"]')?.content || ''; 
 
                
                html += `
                <tr>
                    <td style="text-align: center;">
                         <input type="checkbox" class="user-checkbox" value="${user.user_id}" style="transform: scale(1.2); cursor: pointer;">
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; background: #e0f2fe; color: #0369a1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                ${initial}
                            </div>
                            <strong>${escapeHtml(user.nama_lengkap)}</strong>
                        </div>
                    </td>
                    <td style="color: #64748b; font-family: sans-serif;">${escapeHtml(user.email)}</td>
                    <td>
                        <span style="text-transform: capitalize; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; ${roleClass}">
                            ${escapeHtml(user.role)}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="${baseUrl}admin/editUser/${user.user_id}" class="btn-icon edit" title="Edit">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <button type="button" class="btn-icon delete btn-delete" 
                                    data-url="${baseUrl}admin/deleteUser/${user.user_id}" title="Hapus">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
        }
        tableBody.innerHTML = html;
    }

    function escapeHtml(text) {
        if (!text) return "";
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // --- 6. Event Listeners (Input & Change) ---
    if (searchInput) searchInput.addEventListener('input', () => loadUsers(1));
    if (filterRole) filterRole.addEventListener('change', () => loadUsers(1));

    // --- 7. Bulk Delete Logic (Disesuaikan ID baru) ---
    function updateBulkButton() {
        const checked = document.querySelectorAll('.user-checkbox:checked').length;
        if (countSpan) countSpan.textContent = checked;
        if (btnBulkDelete) btnBulkDelete.style.display = checked > 0 ? 'inline-flex' : 'none';
    }

    if (tableBody) {
        tableBody.addEventListener('change', (e) => {
            if (e.target.classList.contains('user-checkbox')) updateBulkButton();
        });
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }

    if (btnBulkDelete) {
        btnBulkDelete.addEventListener('click', function() {
            const ids = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
            if (ids.length === 0) return;

            if (window.WMSUI) {
                WMSUI.showConfirm(
                    `Hapus ${ids.length} Pengguna?`, 
                    "Akun yang dihapus tidak dapat dikembalikan."
                ).then((result) => {
                    if (result.isConfirmed) {
                        WMSUI.showLoading('Menghapus Data...');
                        fetch(`${baseUrl}admin/deleteBulkUsers`, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ ids: ids })
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                Swal.fire('Sukses', d.message, 'success');
                                loadUsers(1);
                            } else {
                                Swal.fire('Gagal', d.message, 'error');
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Gagal menghapus.', 'error'));
                    }
                });
            }
        });
    }

    const btnImportUser = document.querySelector('.btn-import-users');

}

/**
 * ============================================================================
 * C. LOGIKA KONFIGURASI MASTER (Tabulasi)
 * ============================================================================
 */
function initMasterConfigPage() {
    const tabLinks = document.querySelectorAll('.tab-nav-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const searchInput = document.getElementById('universalSearchInput');
    
    // Elements Tombol Aksi Dinamis
    const btnAdd = document.getElementById('btnMasterAdd');
    const btnDelete = document.getElementById('btnBulkDeleteTab');

    // 1. Tab Switching
    function activateTab(hash) {
        tabLinks.forEach(l => l.classList.remove('active'));
        tabPanes.forEach(p => p.classList.remove('active'));

        let targetLink = document.querySelector(`.tab-nav-link[href="${hash}"]`);
        if (!targetLink && tabLinks.length > 0) targetLink = tabLinks[0]; 

        if (targetLink) {
            targetLink.classList.add('active');
            const targetPaneId = targetLink.getAttribute('href').replace('#tab-', '#view-');
            const targetPane = document.querySelector(targetPaneId);
            if (targetPane) {
                targetPane.classList.add('active');
                
                if (btnAdd) {
                    btnAdd.href = targetLink.dataset.addUrl;
                    btnAdd.innerHTML = `<i class="ph ph-plus"></i> Tambah ${targetLink.innerText}`;
                }
                if (btnDelete) {
                    btnDelete.dataset.url = targetLink.dataset.deleteUrl;
                    btnDelete.style.display = 'none'; 
                }
                
                if (searchInput) {
                    searchInput.value = '';
                    filterTable(targetPane, '');
                }
            }
        }
    }

    tabLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const hash = link.getAttribute('href');
            history.replaceState(null, null, hash); 
            activateTab(hash);
        });
    });

    activateTab(window.location.hash || '#tab-kategori');

    // 2. Universal Search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const activePane = document.querySelector('.tab-pane.active');
            filterTable(activePane, this.value.toLowerCase());
        });
    }

    function filterTable(pane, keyword) {
        if (!pane) return;
        const rows = pane.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const texts = Array.from(row.querySelectorAll('.searchable'))
                               .map(td => td.textContent.toLowerCase())
                               .join(' ');
            
            if (texts.includes(keyword)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // 3. Universal Bulk Delete
    document.querySelectorAll('.tab-pane').forEach(pane => {
        const checkAll = pane.querySelector('.select-all-tab');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                const visibleRows = Array.from(pane.querySelectorAll('tbody tr')).filter(r => r.style.display !== 'none');
                visibleRows.forEach(row => {
                    const cb = row.querySelector('.row-checkbox-tab');
                    if (cb) cb.checked = this.checked;
                });
                updateTabDeleteButton(pane);
            });
        }

        pane.addEventListener('change', (e) => {
            if (e.target.classList.contains('row-checkbox-tab')) {
                updateTabDeleteButton(pane);
            }
        });
    });

    function updateTabDeleteButton(pane) {
        const checkedCount = pane.querySelectorAll('.row-checkbox-tab:checked').length;
        if (btnDelete) {
            if (checkedCount > 0) {
                btnDelete.style.display = 'inline-flex';
                btnDelete.innerHTML = `<i class="ph ph-trash"></i> Hapus (${checkedCount})`;
            } else {
                btnDelete.style.display = 'none';
            }
        }
    }

    if (btnDelete) {
        btnDelete.addEventListener('click', function() {
            const activePane = document.querySelector('.tab-pane.active');
            const ids = Array.from(activePane.querySelectorAll('.row-checkbox-tab:checked')).map(cb => cb.value);
            const url = this.dataset.url;

            if (ids.length > 0 && url) {
                
                // --- UPDATED: Gunakan Helper Standar ---
                if (window.WMSUI) {
                    WMSUI.showConfirm(
                        `Yakin Hapus ${ids.length} Data?`, 
                        "Data yang dihapus tidak bisa dikembalikan."
                    ).then((res) => {
                        if (res.isConfirmed) {
                            WMSUI.showLoading('Menghapus...');
                            
                            fetch(url, {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({ ids: ids })
                            })
                            .then(r => r.json())
                            .then(d => {
                                if(d.success) {
                                    Swal.fire('Sukses', d.message, 'success').then(() => location.reload());
                                } else {
                                    Swal.fire('Gagal', d.message, 'error');
                                }
                            });
                        }
                    });
                }
            }
        });
    }
}

/**
 * ============================================================================
 * D. UTILITY GLOBAL: AUTO GENERATE CODE
 * ============================================================================
 */
function initAutoCodeGenerator() {
    const btnAuto = document.getElementById('btnAutoCode');
    const inputKode = document.getElementById('kode_barang') || document.getElementById('kode_lokasi');
    
    if (btnAuto && inputKode) {
        const form = btnAuto.closest('form');
        const actionUrl = form.getAttribute('action'); 
        // Ambil root url (asumsi /admin/)
        const baseUrl = actionUrl.substring(0, actionUrl.indexOf('admin/'));

        btnAuto.addEventListener('click', () => {
            const prefix = inputKode.id === 'kode_lokasi' ? 'RAK' : 'BRG';
            
            inputKode.value = 'Generating...';
            inputKode.setAttribute('readonly', true);

            fetch(`${baseUrl}admin/getAutoCode/${prefix}`)
                .then(res => res.json())
                .then(data => {
                    if (data.code) {
                        inputKode.value = data.code;
                        inputKode.style.backgroundColor = '#fff3cd';
                        setTimeout(() => inputKode.style.backgroundColor = '', 500);
                    }
                })
                .catch(console.error)
                .finally(() => inputKode.removeAttribute('readonly'));
        });
    }
}

/**
 * ============================================================================
 * E. LOGIKA FORM BARANG (Tambah & Edit) - UTAMA
 * ============================================================================
 */
function initBarangForm() {
    const form = document.getElementById('formBarang');
    const baseUrl = document.querySelector('main')?.dataset.baseUrl || ''; 
    
    console.log('Form Barang Logic Loaded!'); // Cek di Console Browser

    // --- 1. Logika Perubahan Jenis Barang ---
    const jenisSelect = document.getElementById('jenis_barang');
    const fieldStokMin = document.getElementById('fieldStokMin');
    const checkBisaDipinjam = document.getElementById('check_bisa_dipinjam');

    function handleJenisChange() {
        if (!jenisSelect) return;
        const val = jenisSelect.value;
        const isAsset = val === 'asset';

        if (fieldStokMin) {
            fieldStokMin.style.display = isAsset ? 'none' : 'block';
        }

        if (checkBisaDipinjam) {
            if (isAsset) {
                checkBisaDipinjam.checked = true;
                checkBisaDipinjam.disabled = true;
                // Buat hidden input agar value tetap terkirim
                if (!document.getElementById('hidden_bisa_dipinjam')) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'bisa_dipinjam';
                    hidden.value = '1';
                    hidden.id = 'hidden_bisa_dipinjam';
                    checkBisaDipinjam.parentNode.appendChild(hidden);
                }
            } else {
                checkBisaDipinjam.disabled = false;
                const hidden = document.getElementById('hidden_bisa_dipinjam');
                if (hidden) hidden.remove();
            }
        }
    }

    if (jenisSelect) {
        jenisSelect.addEventListener('change', handleJenisChange);
        handleJenisChange(); // Jalankan sekali saat load
    }

    // --- 2. Logika Update Label Satuan ---
    const satuanSelect = document.getElementById('satuan_id');
    const labelSatuan = document.getElementById('labelSatuanMin');

    function updateSatuanLabel() {
        if (satuanSelect && labelSatuan) {
            const opt = satuanSelect.options[satuanSelect.selectedIndex];
            const text = opt ? (opt.getAttribute('data-nama') || opt.text) : 'Unit';
            labelSatuan.textContent = text.includes('Pilih') ? 'Unit' : text;
        }
    }

    if (satuanSelect) {
        satuanSelect.addEventListener('change', updateSatuanLabel);
    }

    // --- 3. Tombol Reset / Restart (LOGIKA PERBAIKAN) ---
    const btnReset = document.getElementById('btnResetForm');
    
    if (btnReset && form) {
        btnReset.addEventListener('click', function(e) {
            e.preventDefault(); // Mencegah submit form tidak sengaja
            console.log('Tombol Reset Diklik!');

            // A. Reset Form Native
            form.reset();

            // B. Kembalikan Select ke Default & Trigger Logic UI
            if (jenisSelect) {
                jenisSelect.selectedIndex = 0;
                handleJenisChange();
            }
            if (satuanSelect) {
                satuanSelect.selectedIndex = 0;
                updateSatuanLabel();
            }

            // C. Reset Kode Barang
            const inputKode = document.getElementById('kode_barang');
            if (inputKode) {
                inputKode.value = '';
                inputKode.removeAttribute('readonly');
                inputKode.style.backgroundColor = ''; 
            }

            // D. Reset Preview Foto
            const fileInput = document.querySelector('input[name="foto_barang"]');
            const previewImage = document.getElementById('preview-image');
            const previewContainer = document.getElementById('preview-image-container');
            const previewOverlay = document.querySelector('.file-preview-overlay');
            const fileWrapper = document.querySelector('.file-upload-wrapper');

            if(fileInput) fileInput.value = ''; 
            if(previewImage) previewImage.src = '';
            
            // Sembunyikan gambar, Tampilkan overlay
            if(previewContainer) previewContainer.style.display = 'none';
            if(previewOverlay) previewOverlay.style.display = 'flex';

            if(fileWrapper) {
                fileWrapper.classList.remove('has-preview');
                fileWrapper.classList.remove('is-dragover');
                fileWrapper.style.cssText = ''; 
            }

            // Scroll ke atas dengan halus
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // --- 4. Tombol Auto Generate Kode ---
    if (initAutoCodeGenerator && typeof initAutoCodeGenerator !== 'function') {
        // Fallback jika global initAutoCodeGenerator tidak bekerja di scope ini
        const btnAuto = document.getElementById('btnAutoCode');
        const inputKode = document.getElementById('kode_barang');
        // ... (kode sama dengan global, opsional) ...
    }

    // --- 5. Preview Foto Upload & DRAG AND DROP ---
    const fileInput = document.querySelector('input[name="foto_barang"]');
    const fileWrapper = document.querySelector('.file-upload-wrapper'); 
    const previewImage = document.getElementById('preview-image');
    const previewContainer = document.getElementById('preview-image-container');
    const previewOverlay = document.querySelector('.file-preview-overlay');

    if (fileInput && fileWrapper && previewImage && previewContainer && previewOverlay) { 
        
        function updateVisualStatus(fileObject) {
            if (fileObject instanceof File) {
                if (!fileObject.type.startsWith('image/')) {
                    alert('Harap upload file gambar (JPG/PNG).');
                    return;
                }

                const url = URL.createObjectURL(fileObject);
                previewImage.src = url;
                previewContainer.style.display = 'flex'; // Flex agar center
                previewOverlay.style.display = 'none';
                fileWrapper.classList.add('has-preview');
            } else {
                // Logic untuk reset atau init data lama
                const isExistingImage = previewImage.getAttribute('data-initial-file') === '1';
                if (!isExistingImage) {
                    previewImage.src = '';
                    previewContainer.style.display = 'none';
                    previewOverlay.style.display = 'flex'; 
                    fileWrapper.classList.remove('has-preview');
                }
            }
        }

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                updateVisualStatus(this.files[0]); 
            }
        });

        // Drag & Drop
        const dropZone = fileWrapper;
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        dropZone.addEventListener('dragover', () => dropZone.classList.add('is-dragover'), false);
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('is-dragover'), false);

        dropZone.addEventListener('drop', e => {
            dropZone.classList.remove('is-dragover'); 
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        }, false);
        
        // Initial check (Edit Mode)
        const hasInitial = previewImage.getAttribute('data-initial-file') === '1';
        if(hasInitial) {
            previewContainer.style.display = 'flex';
            previewOverlay.style.display = 'none';
            fileWrapper.classList.add('has-preview');
        }
    }
}