document.addEventListener('DOMContentLoaded', function() {

    /* =========================================
       0. MOBILE SIDEBAR TOGGLE (WAJIB)
       ========================================= */
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.app-sidebar');
    
    if (menuToggle && sidebar) {
        // Buat elemen overlay gelap background
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        // Klik Tombol Menu
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        // Klik Overlay (Background gelap) untuk tutup sidebar
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

    /* =========================================
       1. FITUR GLOBAL (Notifikasi Flash)
       ========================================= */
    const alertBox = document.getElementById('loginAlert') || document.querySelector('.flash-message');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => { alertBox.remove(); }, 500);
        }, 3000);
    }
  

    /* =========================================
       2. FITUR MANAJEMEN PENGGUNA (ADMIN) - REVISI VISUAL JS
       ========================================= */
    const liveSearchInput = document.getElementById('liveSearchInput');

    if (liveSearchInput) {
        const userTableBody = document.getElementById('userTableBody');
        const baseUrl = liveSearchInput.dataset.baseUrl;
        const currentUserId = liveSearchInput.dataset.currentUserId;
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectAllCheckbox = document.getElementById('selectAll');
        const filterRole = document.getElementById('filterRole');
        const paginationContainerUsers = document.getElementById('paginationContainerUsers');

        function loadUsers(page = 1, source = null) {
            let searchVal = liveSearchInput.value;
            let roleVal = filterRole ? filterRole.value : '';

            if (source === 'search') {
                roleVal = ''; 
                if(filterRole) filterRole.value = ''; 
            } else if (source === 'role') {
                searchVal = ''; 
                liveSearchInput.value = ''; 
            }

            const params = new URLSearchParams({
                ajax: 1, page: page, search: searchVal, role: roleVal
            });

            if(userTableBody) userTableBody.style.opacity = '0.5';

            fetch(`${baseUrl}admin/users?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.users.length === 0) {
                        html = '<tr><td colspan="5" style="text-align:center; padding: 20px;">Data pengguna tidak ditemukan.</td></tr>';
                    } else {
                        data.users.forEach(user => {
                            // 1. LOGIKA CHECKBOX
                            const checkboxHtml = (user.user_id != currentUserId) 
                                ? `<input type="checkbox" class="user-checkbox" value="${user.user_id}" style="transform: scale(1.2); cursor: pointer;">` 
                                : '';

                            // 2. LOGIKA ROLE BADGE
                            let roleStyle = '';
                            if(user.role === 'admin') roleStyle = 'color: #7c3aed; background: #f3e8ff; border: 1px solid #d8b4fe;';
                            else if(user.role === 'staff') roleStyle = 'color: #059669; background: #ecfdf5; border: 1px solid #6ee7b7;';
                            else if(user.role === 'pemilik') roleStyle = 'color: #d97706; background: #fffbeb; border: 1px solid #fde68a;';
                            else roleStyle = 'color: #4b5563; background: #f3f4f6; border: 1px solid #d1d5db;';
                            
                            const roleBadge = `<span style="text-transform: capitalize; font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px; ${roleStyle}">${user.role}</span>`;

                            // --- 3. LOGIKA TOMBOL UNLOCK (BARU) ---
                            let lockedBadge = '';
                            let unlockBtn = '';

                            if (user.is_locked == 1) {
                                lockedBadge = `<span class="badge-locked"><i class="ph ph-lock-key"></i> TERKUNCI</span>`;
                                unlockBtn = `
                                    <a href="${baseUrl}admin/unlockUser/${user.user_id}" 
                                       class="btn-icon unlock" 
                                       title="Buka Kunci Akun"
                                       onclick="return confirm('Buka kunci akun ini?');">
                                        <i class="ph ph-lock-key-open"></i>
                                    </a>
                                `;
                            }
                            // ---------------------------------------

                            const editBtn = `
                                <a href="${baseUrl}admin/editUser/${user.user_id}" class="btn-icon edit" title="Edit">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                            `;

                            let deleteBtn = '';
                            if (user.user_id != currentUserId) {
                                deleteBtn = `
                                    <button type="button" class="btn-icon delete btn-delete" data-url="${baseUrl}admin/deleteUser/${user.user_id}" title="Hapus">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                `;
                            }

                            html += `
                                <tr>
                                    <td style="text-align: center;">${checkboxHtml}</td>
                                    <td>
                                        <strong>${user.nama_lengkap}</strong>
                                        ${lockedBadge} 
                                    </td>
                                    <td style="color: #666;">${user.email}</td>
                                    <td>${roleBadge}</td>
                                    <td>
                                        <div class="action-buttons">
                                            ${unlockBtn}
                                            ${editBtn}
                                            ${deleteBtn}
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    
                    if(userTableBody) {
                        userTableBody.innerHTML = html;
                        userTableBody.style.opacity = '1';
                    }

                    renderPaginationUniversal(paginationContainerUsers, data.totalPages, data.currentPage, (p) => loadUsers(p));

                    if(selectAllCheckbox) selectAllCheckbox.checked = false;
                    updateBulkBtnUser(); 
                })
                .catch(err => {
                    console.error(err);
                    if(userTableBody) userTableBody.style.opacity = '1';
                });
        }

        liveSearchInput.addEventListener('input', () => loadUsers(1, 'search'));
        if(filterRole) filterRole.addEventListener('change', () => loadUsers(1, 'role'));

        if (paginationContainerUsers) {
            paginationContainerUsers.addEventListener('click', (e) => {
                if (e.target.classList.contains('page-link')) {
                    e.preventDefault();
                    const li = e.target.parentElement;
                    if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                        loadUsers(parseInt(e.target.dataset.page));
                    }
                }
            });
        }

        function updateBulkBtnUser() {
            const checked = document.querySelectorAll('.user-checkbox:checked').length;
            if (selectedCountSpan) selectedCountSpan.textContent = checked;
            if (btnBulkDelete) btnBulkDelete.style.display = checked > 0 ? 'inline-block' : 'none';
        }
        if (userTableBody) {
            userTableBody.addEventListener('change', (e) => {
                if (e.target.classList.contains('user-checkbox')) {
                    updateBulkBtnUser();
                    const total = document.querySelectorAll('.user-checkbox').length;
                    const checked = document.querySelectorAll('.user-checkbox:checked').length;
                    if(selectAllCheckbox) selectAllCheckbox.checked = (total === checked && total > 0);
                }
            });
        }
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.user-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBulkBtnUser();
            });
        }
        if (btnBulkDelete) {
            btnBulkDelete.addEventListener('click', function() {
                const checked = document.querySelectorAll('.user-checkbox:checked');
                const ids = Array.from(checked).map(cb => cb.value);
                handleBulkDelete(ids, `${baseUrl}admin/deleteBulkUsers`, () => loadUsers(1));
            });
        }

        // --- REVISI: MODAL IMPORT USER (STYLE BARU) ---
        const btnImport = document.querySelector('.btn-import-users');
        if (btnImport) {
            btnImport.addEventListener('click', function() {
                const url = this.dataset.url;
                
                Swal.fire({
                    title: 'Import Data Pengguna',
                    html: `
                        <form id="formImportUser" action="${url}" method="POST" enctype="multipart/form-data">
                            <div class="import-instruction-box">
                                <strong>Format Kolom CSV (Urutan Wajib):</strong>
                                <ul>
                                    <li>1. Nama Lengkap</li>
                                    <li>2. Email (Login)</li>
                                    <li>3. Tanggal Lahir (YYYY-MM-DD)</li> 
                                    <li>4. Role (admin, staff, pemilik, peminjam)</li>
                                </ul>
                                <small style="color: #d97706; display:block; margin-top:5px;">
                                    *Password otomatis dibuat dari tanggal lahir (Format: DDMMYYYY).
                                </small>
                            </div>
                            
                            <div class="custom-file-upload" id="dropZoneUser">
                                <input type="file" name="csv_file" id="csvFileInputUser" class="hidden-input-file" accept=".csv" required>
                                <i class="ph ph-file-csv"></i>
                                <span class="main-text">Klik atau Tarik File CSV ke Sini</span>
                                <span class="sub-text">Maksimal ukuran file 2MB</span>
                            </div>
                            
                            <div id="fileNameDisplayUser" class="selected-file-name"></div>
                        </form>
                    `,
                    
                    showCancelButton: true,
                    confirmButtonText: 'Upload & Import',
                    cancelButtonText: 'Batal',
                    
                    confirmButtonColor: '#152e4d', // Biru Tua (Simpan)
                    cancelButtonColor: '#f8c21a',  // Kuning (Batal)
                    
                    reverseButtons: true,
                    
                    // --- BAGIAN INI YANG MENGUBAH WARNA TEKS ---
                    didOpen: () => {
                        // 1. Ambil tombol Batal
                        const cancelBtn = Swal.getCancelButton();
                        if (cancelBtn) {
                            cancelBtn.style.color = '#152e4d'; // Ubah teks jadi Biru Tua
                            cancelBtn.style.fontWeight = 'bold'; // Biar lebih tegas terbaca
                        }

                        // 2. Logika Upload File (Seperti sebelumnya)
                        const fileInput = document.getElementById('csvFileInputUser');
                        const fileNameDisplay = document.getElementById('fileNameDisplayUser');
                        const dropZone = document.getElementById('dropZoneUser');
                        const mainText = dropZone.querySelector('.main-text');

                        fileInput.addEventListener('change', function() {
                            if (this.files && this.files.length > 0) {
                                const name = this.files[0].name;
                                fileNameDisplay.innerHTML = `📄 File terpilih: <strong>${name}</strong>`;
                                fileNameDisplay.style.display = 'block';
                                
                                dropZone.style.borderColor = '#10b981';
                                dropZone.style.backgroundColor = '#ecfdf5';
                                dropZone.querySelector('i').style.color = '#10b981';
                                mainText.innerText = "Ganti File?";
                            }
                        });
                    },

                    preConfirm: () => {
                        const form = document.getElementById('formImportUser');
                        const fileInput = document.getElementById('csvFileInputUser');
                        if (!fileInput.files.length) {
                            Swal.showValidationMessage('⚠️ Silakan pilih file CSV terlebih dahulu!');
                            return false;
                        }
                        form.submit();
                    }
                });
            });
        }
    }

    /* =========================================
       3. FITUR MANAJEMEN BARANG (ADMIN)
       ========================================= */
    const liveSearchBarang = document.getElementById('liveSearchBarang');
    if (liveSearchBarang) {
        const tableBodyBarang = document.getElementById('barangTableBody');
        const baseUrl = liveSearchBarang.dataset.baseUrl;
        
        const filterKategori = document.getElementById('filterKategori');
        const filterMerek = document.getElementById('filterMerek');
        const filterStatus = document.getElementById('filterStatus');
        const filterLokasi = document.getElementById('filterLokasi'); 
        
        const paginationContainer = document.getElementById('paginationContainerBarang');
        const btnBulkDeleteBarang = document.getElementById('btnBulkDeleteBarang');
        const selectAllBarang = document.getElementById('selectAllBarang');
        const selectedCountBarang = document.getElementById('selectedCountBarang');

        function loadBarang(page = 1) {
            const params = new URLSearchParams({
                ajax: 1, page: page, search: liveSearchBarang.value,
                kategori: filterKategori.value, merek: filterMerek.value, 
                status: filterStatus.value, lokasi: filterLokasi.value
            });

            if(tableBodyBarang) tableBodyBarang.style.opacity = '0.5';

            fetch(`${baseUrl}admin/barang?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyBarang.innerHTML = data.html;
                    tableBodyBarang.style.opacity = '1';
                    renderPaginationUniversal(paginationContainer, data.totalPages, data.currentPage, (p) => loadBarang(p));
                    if(selectAllBarang) selectAllBarang.checked = false;
                    updateBulkBtnBarang();
                })
                .catch(err => {
                    console.error(err);
                    if(tableBodyBarang) tableBodyBarang.style.opacity = '1';
                });
        }

        liveSearchBarang.addEventListener('input', () => loadBarang(1));
        document.querySelectorAll('.live-filter-barang').forEach(el => {
            el.addEventListener('change', () => loadBarang(1));
        });

        if (paginationContainer) {
            paginationContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('page-link')) {
                    e.preventDefault();
                    const p = e.target.parentElement;
                    if (!p.classList.contains('disabled') && !p.classList.contains('active')) {
                        loadBarang(parseInt(e.target.dataset.page));
                    }
                }
            });
        }

        function updateBulkBtnBarang() {
            const checked = document.querySelectorAll('.barang-checkbox:checked').length;
            if (selectedCountBarang) selectedCountBarang.textContent = checked;
            if (btnBulkDeleteBarang) btnBulkDeleteBarang.style.display = checked > 0 ? 'inline-block' : 'none';
        }
        if (tableBodyBarang) {
            tableBodyBarang.addEventListener('change', (e) => {
                if (e.target.classList.contains('barang-checkbox')) {
                    updateBulkBtnBarang();
                    const total = document.querySelectorAll('.barang-checkbox').length;
                    const checked = document.querySelectorAll('.barang-checkbox:checked').length;
                    if(selectAllBarang) selectAllBarang.checked = (total === checked && total > 0);
                }
            });
        }
        if (selectAllBarang) {
            selectAllBarang.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.barang-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBulkBtnBarang();
            });
        }
        if (btnBulkDeleteBarang) {
            btnBulkDeleteBarang.addEventListener('click', function() {
                const checked = document.querySelectorAll('.barang-checkbox:checked');
                const ids = Array.from(checked).map(cb => cb.value);
                handleBulkDelete(ids, this.dataset.url, () => loadBarang(1));
            });
        }
    }

    /* =========================================
       4. FITUR REKAP ABSENSI (ADMIN) - LIVE SEARCH & FILTER
       ========================================= */
    
    // Definisikan fungsi secara global agar bisa dipanggil oleh tombol Preset (setPeriod)
    // Definisikan fungsi secara global agar bisa dipanggil oleh tombol Preset (setPeriod)
    window.loadAbsensiGlobal = function(page = 1) {
        const searchInput = document.getElementById('searchAbsensi');
        if (!searchInput) return; // Hentikan jika bukan di halaman absensi

        const baseUrl = searchInput.dataset.baseUrl;
        const tableBody = document.getElementById('absensiTableBody');
        const paginationContainer = document.querySelector('.pagination-container');

        // 1. Deteksi Mode (Harian atau Laporan) dari URL
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode') || 'harian';

        // 2. Ambil Nilai Filter
        const filterStatus = document.getElementById('filterStatus');
        const filterRole = document.getElementById('filterRole'); 
        const filterDate = document.getElementById('datePickerNative');
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const filterRoleLaporan = document.getElementById('filterRoleLaporan'); 

        // 3. Susun Parameter
        let params = new URLSearchParams({
            ajax: 1,
            page: page,
            mode: mode,
            search: searchInput.value
        });

        if (mode === 'harian') {
            if (filterStatus) params.append('status', filterStatus.value);
            if (filterRole) params.append('role', filterRole.value);
            if (filterDate) params.append('date', filterDate.value);
        } else {
            if (startDateInput) params.append('start_date', startDateInput.value);
            if (endDateInput) params.append('end_date', endDateInput.value);
            if (filterRoleLaporan) params.append('role', filterRoleLaporan.value);
        }

        // 4. Efek Loading & Fetch Data
        if(tableBody) tableBody.style.opacity = '0.5';

        fetch(`${baseUrl}admin/rekapAbsensi?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                let html = '';

                if (data.absensi.length === 0) {
                    html = '<tr><td colspan="9" style="text-align:center; padding:30px; color:#999;">Data tidak ditemukan.</td></tr>';
                } else {
                    data.absensi.forEach(absen => {
                        
                        // --- Logic Status ---
                        let statusStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                        let statusText = absen.status; 

                        // Ambil tanggal hari ini dalam format YYYY-MM-DD (sesuai format database)
                        const todayDate = new Date().toISOString().split('T')[0];

                        if (absen.status === 'Hadir') {
                            // Default style Hadir (Hijau)
                            statusStyle = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
                            statusText = 'Hadir';

                            // Cek apakah belum pulang
                            if ((!absen.waktu_pulang || absen.waktu_pulang === '-') && absen.waktu_masuk) {
                                
                                // [LOGIKA BARU]: Hanya tampilkan "Masih Bekerja" jika tanggalnya HARI INI
                                if (absen.tanggal === todayDate) {
                                    statusText = 'Masih Bekerja';
                                    statusStyle = 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;'; // Biru
                                } 
                                // Jika tanggal lampau, biarkan tetap "Hadir" (Hijau) meskipun waktu pulang kosong
                            }
                        } else if (absen.status === 'Sakit') {
                            statusStyle = 'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;';
                        } else if (absen.status === 'Izin') {
                            statusStyle = 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;';
                            statusText = 'Izin / Cuti';
                        } else if (absen.status === 'Alpa') {
                             statusStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                             statusText = 'Tanpa Keterangan';
                        }

                        // --- Logic Role Badge ---
                        const roleRaw = (absen.role || '').toLowerCase();
                        let roleBadgeStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                        if(roleRaw == 'admin') roleBadgeStyle = 'background:#f3e8ff; color:#7c3aed; border:1px solid #d8b4fe;';
                        if(roleRaw == 'staff') roleBadgeStyle = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
                        if(roleRaw == 'pemilik') roleBadgeStyle = 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;';
                        if(roleRaw == 'peminjam') roleBadgeStyle = 'background:#f3f4f6; color:#4b5563; border:1px solid #d1d5db;';

                        // --- Helper Clean String ---
                        const escapeHtml = (str) => {
                            if (!str) return '';
                            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
                        };

                        // --- Logic Keterangan (Tombol Lihat) ---
                        let keteranganHtml = '<span style="color:#ccc;">-</span>';
                        if (absen.keterangan && absen.keterangan.trim() !== '' && absen.keterangan !== '-') {
                            const rawKet = absen.keterangan.replace(/[\r\n]+/g, " ").replace(/'/g, "&#39;").replace(/"/g, "&quot;");
                            keteranganHtml = `
                                <button type="button" class="btn btn-sm btn-info" 
                                    onclick="showDetailKeterangan('${rawKet}')"
                                    style="background-color: #e0f2fe; color: #0ea5e9; border: 1px solid #bae6fd; padding: 2px 10px; font-size: 0.75rem; border-radius: 20px; font-weight: 600;">
                                    <i class="ph ph-eye"></i> Lihat
                                </button>`;
                        }

                        // --- RENDER ROW BERDASARKAN MODE ---
                        if (mode === 'laporan') {
                            // Tampilan Tab Riwayat Laporan (Simple)
                            html += `
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 15px; color: #334155;">${absen.tanggal}</td>
                                <td style="padding: 15px; color: #152e4d;"><strong>${absen.nama_lengkap}</strong></td>
                                <td style="padding: 15px;"><span style="padding: 4px 8px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; ${roleBadgeStyle}">${absen.role}</span></td>
                                <td style="padding: 15px; color: #444;">${absen.waktu_masuk || '-'}</td>
                                <td style="padding: 15px; color: #444;">${absen.waktu_pulang || '-'}</td>
                                <td style="padding: 15px;"><span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; ${statusStyle}">${statusText}</span></td>
                                <td style="padding: 15px; text-align:center;">${keteranganHtml}</td>
                            </tr>`;
                        
                        } else {
                            // Tampilan Tab Monitoring Harian (Lengkap dengan Bukti & Edit)
                            let buktiHtml = '<span style="color: #cbd5e1;">-</span>';
                            if (absen.bukti_foto) {
                                buktiHtml = `<a href="${baseUrl}../public/uploads/bukti_absen/${absen.bukti_foto}" target="_blank" style="display: inline-flex; align-items: center; gap: 5px; text-decoration: none; color: #2563eb; font-weight: 600; font-size: 0.85rem; background: #eff6ff; padding: 4px 8px; border-radius: 6px;"><i class="ph ph-file-text"></i> Lihat File</a>`;
                            }

                            const jamMasukRaw = absen.waktu_masuk && absen.waktu_masuk !== '-' ? absen.waktu_masuk.substring(0,5) : '';
                            const jamPulangRaw = absen.waktu_pulang && absen.waktu_pulang !== '-' ? absen.waktu_pulang.substring(0,5) : '';
                            const safeNamaAttr = escapeHtml(absen.nama_lengkap);
                            const safeKetAttr = escapeHtml(absen.keterangan);

                            const btnEdit = `<button type="button" class="btn-icon edit btn-edit-manual" 
                                data-id="${absen.absen_id}" 
                                data-nama="${safeNamaAttr}" 
                                data-masuk="${jamMasukRaw}" 
                                data-pulang="${jamPulangRaw}" 
                                data-status="${absen.status}" 
                                data-ket="${safeKetAttr}">
                                <i class="ph ph-pencil-simple"></i>
                            </button>`;

                            html += `
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 15px; color: #334155;">${absen.tanggal}</td>
                                <td style="padding: 15px; color: #152e4d;"><strong>${absen.nama_lengkap}</strong></td>
                                <td style="padding: 15px;"><span style="padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; ${roleBadgeStyle}">${absen.role}</span></td>
                                <td style="padding: 15px; color: #444;">${absen.waktu_masuk || '-'}</td>
                                <td style="padding: 15px; color: #444;">${absen.waktu_pulang || '-'}</td>
                                <td style="padding: 15px; color: #444;">${absen.total_jam || '-'}</td>
                                <td style="padding: 15px;"><span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; ${statusStyle}">${statusText}</span></td>
                                <td style="padding: 15px;">${buktiHtml}</td>
                                <td class="no-print" style="text-align: center;">${btnEdit}</td>
                            </tr>`;
                        }
                    });
                }

                if(tableBody) { 
                    tableBody.innerHTML = html; 
                    tableBody.style.opacity = '1'; 
                }
                if(paginationContainer) {
                    renderPaginationUniversal(paginationContainer, data.totalPages, data.currentPage, (p) => window.loadAbsensiGlobal(p));
                }
            })
            .catch(err => {
                console.error("Error fetching absensi:", err);
                if(tableBody) {
                    tableBody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:red;">Terjadi kesalahan saat memuat data.</td></tr>';
                    tableBody.style.opacity = '1';
                }
            });
    };

    // --- Inisialisasi Event Listener (Saat Halaman Dimuat) ---
    const searchInput = document.getElementById('searchAbsensi');
    
    if (searchInput) {
        // 1. Listener Search (Ketik)
        searchInput.addEventListener('input', () => window.loadAbsensiGlobal(1));

        // 2. Listener Filter Harian
        const filterRole = document.getElementById('filterRole');
        const filterStatus = document.getElementById('filterStatus');
        if(filterRole) filterRole.addEventListener('change', () => window.loadAbsensiGlobal(1));
        if(filterStatus) filterStatus.addEventListener('change', () => window.loadAbsensiGlobal(1));

        // 3. Listener Filter Laporan (Date Picker & Role Laporan)
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        const filterRoleLaporan = document.getElementById('filterRoleLaporan');

        if(startDate) startDate.addEventListener('change', () => window.loadAbsensiGlobal(1));
        if(endDate) endDate.addEventListener('change', () => window.loadAbsensiGlobal(1));
        if(filterRoleLaporan) filterRoleLaporan.addEventListener('change', () => window.loadAbsensiGlobal(1));
        
        // 4. Listener & Render Pagination Awal
    const pagContainer = document.querySelector('.pagination-container');
    if(pagContainer) {
         // A. Event Listener untuk Klik Tombol
         pagContainer.addEventListener('click', (e) => { 
            if(e.target.classList.contains('page-link')){ 
                e.preventDefault(); 
                const p = e.target.parentElement; 
                if(!p.classList.contains('disabled') && !p.classList.contains('active')) {
                    window.loadAbsensiGlobal(parseInt(e.target.dataset.page)); 
                }
            } 
        }); 

        // B. [PERBAIKAN] Render Tombol Saat Halaman Pertama Dimuat
        // Ambil data total halaman dari atribut PHP data-total-pages
        const initTotal = parseInt(pagContainer.dataset.totalPages) || 1;
        const initCurrent = parseInt(pagContainer.dataset.currentPage) || 1;
        
        // Panggil fungsi render
        if (typeof renderPaginationUniversal === 'function') {
            renderPaginationUniversal(pagContainer, initTotal, initCurrent, (p) => window.loadAbsensiGlobal(p));
        }
    }
    }

    document.addEventListener('click', function(e) {
    // Cek apakah yang diklik adalah tombol edit (atau icon di dalamnya)
    const btn = e.target.closest('.btn-edit-manual');
    
    if (btn) {
        // Ambil data dari atribut data-*
        const d = btn.dataset;
        
        // Panggil fungsi popup dengan data yang bersih
        editAbsenPopup(d.id, d.nama, d.masuk, d.pulang, d.status, d.ket);
    }
});

    /* =========================================
       5. FITUR KONFIGURASI DATA MASTER (ADMIN) - [FIXED PAGINATION]
       ========================================= */
    const masterDataPage = document.getElementById('masterDataPage');
    if (masterDataPage) {
        const tabLinks = document.querySelectorAll('.tab-nav-link');
        const tabPanes = document.querySelectorAll('.tab-pane');
        const universalSearchInput = document.getElementById('universalSearchInput');
        const btnMasterAdd = document.getElementById('btnMasterAdd');
        const btnBulkDeleteTab = document.getElementById('btnBulkDeleteTab');
        const ROWS_PER_PAGE = 10; 

        // --- Core Logic Pagination ---
        function renderPagination(pane, rows) {
            const container = pane.querySelector('.custom-pagination');
            if (!container) return; // Safety check
            
            const totalRows = rows.length;
            // Hitung total halaman. Jika 0 data, tetap anggap 1 halaman.
            const totalPages = totalRows > 0 ? Math.ceil(totalRows / ROWS_PER_PAGE) : 1;
            
            // Ambil halaman saat ini, default 1
            let currentPage = parseInt(container.getAttribute('data-current-page')) || 1;
            if (currentPage > totalPages) currentPage = 1;

            // Tentukan baris mana yang tampil
            const start = (currentPage - 1) * ROWS_PER_PAGE;
            const end = start + ROWS_PER_PAGE;

            rows.forEach((row, index) => {
                if (index >= start && index < end) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // --- BUILD HTML PAGINATION (SELALU TAMPIL) ---
            let html = '<nav><ul class="pagination">';
            
            // Tombol Previous (Disable jika di halaman 1)
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;

            // Tombol Angka (Limit tampilan biar tidak kepanjangan)
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, currentPage + 2);
            
            if (totalPages <= 5) {
                startPage = 1;
                endPage = totalPages;
            }

            for (let i = startPage; i <= endPage; i++) {
                const active = i === currentPage ? 'active' : '';
                html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }

            // Tombol Next (Disable jika di halaman terakhir)
            const nextDisabled = currentPage === totalPages ? 'disabled' : '';
            html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
            
            html += '</ul></nav>';
            
            // Info teks (opsional, buat pemanis)
            const infoText = `<span style="margin-right: 15px; color: #64748b; font-size: 0.85rem; align-self: center;">
                                Menampilkan ${totalRows > 0 ? start + 1 : 0} - ${Math.min(end, totalRows)} dari ${totalRows} data
                              </span>`;
            
            // Render ke HTML
            container.innerHTML = infoText + html;
            container.setAttribute('data-current-page', currentPage);
            container.style.display = 'flex'; // Paksa tampil flex

            // Re-attach Event Listener (karena innerHTML me-reset elemen)
            const links = container.querySelectorAll('.page-link');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const li = e.target.parentElement;
                    if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                        const newPage = parseInt(e.target.dataset.page);
                        container.setAttribute('data-current-page', newPage);
                        renderPagination(pane, rows); // Rekursif render ulang
                    }
                });
            });
        }

        function filterAndPaginate() {
            const activePane = document.querySelector('.tab-pane.active');
            if(!activePane) return;

            const searchText = universalSearchInput.value.toLowerCase();
            const allRows = Array.from(activePane.querySelectorAll('.data-table tbody tr:not(.no-data)'));
            const activeLink = document.querySelector('.tab-nav-link.active');
            
            let visibleRows = [];
            let checkedCount = 0;

            allRows.forEach(row => {
                let textContent = "";
                row.querySelectorAll('.searchable').forEach(col => {
                    textContent += col.textContent.toLowerCase() + " ";
                });

                if (textContent.includes(searchText)) {
                    visibleRows.push(row);
                    const checkbox = row.querySelector('.row-checkbox-tab:checked');
                    if(checkbox) checkedCount++;
                } else {
                    row.style.display = 'none'; // Sembunyikan yang tidak match
                }
            });

            // Reset ke page 1 jika sedang mengetik search
            const paginationContainer = activePane.querySelector('.custom-pagination');
            if(paginationContainer && document.activeElement === universalSearchInput) {
                 paginationContainer.setAttribute('data-current-page', 1);
            }
            
            renderPagination(activePane, visibleRows);
            if(activeLink) updateActionButtons(activeLink, checkedCount);
        }

        function updateActionButtons(activeLink, checkedCount) {
            if(!activeLink) return;
            btnMasterAdd.setAttribute('href', activeLink.dataset.addUrl);
            btnMasterAdd.textContent = '+ Tambah ' + activeLink.textContent;
            btnBulkDeleteTab.setAttribute('data-url', activeLink.dataset.deleteUrl);
            
            if (checkedCount > 0) {
                btnBulkDeleteTab.style.display = 'inline-block';
                btnBulkDeleteTab.textContent = `🗑️ Hapus Terpilih (${checkedCount})`;
            } else {
                btnBulkDeleteTab.style.display = 'none';
            }
        }

        function activateTab(hash) {
            tabLinks.forEach(l => l.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));

            const activeLink = document.querySelector(`.tab-nav-link[href="${hash}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
                const targetId = hash.replace('#tab-', '#view-');
                const targetPane = document.querySelector(targetId);
                
                if (targetPane) {
                    targetPane.classList.add('active');
                    universalSearchInput.value = ''; 
                    
                    const pagContainer = targetPane.querySelector('.custom-pagination');
                    if(pagContainer) pagContainer.setAttribute('data-current-page', 1);

                    filterAndPaginate(); 
                }
            } else {
                if(tabLinks.length > 0) activateTab(tabLinks[0].getAttribute('href'));
            }
        }

        // --- Init Events ---
        if(window.location.hash) activateTab(window.location.hash);
        else activateTab('#tab-kategori');

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const hash = this.getAttribute('href');
                history.replaceState(null, null, hash);
                activateTab(hash);
            });
        });

        universalSearchInput.addEventListener('input', filterAndPaginate);

        document.querySelectorAll('.select-all-tab').forEach(selectAll => {
            selectAll.addEventListener('change', function() {
                const activePane = document.querySelector('.tab-pane.active');
                const visibleRows = Array.from(activePane.querySelectorAll('tbody tr')).filter(tr => tr.style.display !== 'none');
                visibleRows.forEach(row => {
                    const cb = row.querySelector('.row-checkbox-tab');
                    if(cb) cb.checked = this.checked;
                });
                filterAndPaginate(); 
            });
        });

        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.addEventListener('change', function(e) {
                if(e.target.classList.contains('row-checkbox-tab')) {
                    const activeLink = document.querySelector('.tab-nav-link.active');
                    const checked = this.querySelectorAll('.row-checkbox-tab:checked').length;
                    updateActionButtons(activeLink, checked);
                }
            });
        });
        
        if (btnBulkDeleteTab) {
            btnBulkDeleteTab.addEventListener('click', function() {
                const activePane = document.querySelector('.tab-pane.active');
                const checked = activePane.querySelectorAll('.row-checkbox-tab:checked');
                const ids = Array.from(checked).map(cb => cb.value);
                const url = this.getAttribute('data-url');
                handleBulkDelete(ids, url, () => location.reload());
            });
        }
    }

    /* =========================================
       6. FITUR PROFIL (HISTORY) - GLOBAL DELEGATION FIX
       ========================================= */
    const startDateProfile = document.getElementById('startDateProfile');
    
    // Kita cek apakah elemen ini ada, menandakan kita sedang di halaman Profil
    if (startDateProfile) {
        const endDateProfile = document.getElementById('endDateProfile');
        const historyBody = document.getElementById('historyTableBody');
        const formHistory = document.getElementById('formHistory');
        const baseUrl = formHistory.dataset.baseUrl;
        const pagContainerProfile = document.getElementById('paginationContainerHistory'); 
        
        // A. Fungsi Render Pagination (HTML Only)
        function renderLocalPagination(totalPages, currentPage) {
            if (!pagContainerProfile) return;
            
            totalPages = parseInt(totalPages) || 1;
            currentPage = parseInt(currentPage) || 1;
            
            let html = '<nav><ul class="pagination">';
            
            // Prev
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
            
            // Numbers
            let start = Math.max(1, currentPage - 2);
            let end = Math.min(totalPages, currentPage + 2);
            if (totalPages <= 5) { start = 1; end = totalPages; }
            
            for (let i = start; i <= end; i++) {
                const active = i === currentPage ? 'active' : '';
                html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            
            // Next
            const nextDisabled = currentPage === totalPages ? 'disabled' : '';
            html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
            
            html += '</ul></nav>';
            pagContainerProfile.innerHTML = html;
        }

        // B. Fungsi Load History (AJAX)
        window.loadMyHistory = function(page = 1) {
            const params = new URLSearchParams({ 
                ajax: 1, 
                page: page, 
                start_date: startDateProfile.value, 
                end_date: endDateProfile.value 
            });
            
            if(historyBody) historyBody.style.opacity = '0.5';

            fetch(`${baseUrl}profile/absensi?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (data.absensi.length === 0) { 
                    html = '<tr><td colspan="6" style="text-align:center; padding: 30px; color: #999;">Tidak ada data absensi pada periode ini.</td></tr>'; 
                } else {
                    data.absensi.forEach(absen => {
                        // ... (Bagian render baris tabel SAMA seperti sebelumnya, tidak diubah) ...
                        // Copy paste logika status/html baris dari kode sebelumnya di sini
                        let statusStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                        let statusText = absen.status_raw;
                        if (absen.status_raw === 'Hadir') {
                            statusStyle = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
                            statusText = 'Hadir';
                            if (absen.display_status === 'Masih Bekerja') {
                                statusText = 'Masih Bekerja';
                                statusStyle = 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;';
                            }
                        } else if (absen.status_raw === 'Sakit') {
                            statusStyle = 'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;';
                        } else if (absen.status_raw === 'Izin') {
                            statusStyle = 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;';
                            statusText = 'Izin / Cuti';
                        } else if (absen.status_raw === 'Alpa') {
                             statusStyle = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
                             statusText = 'Tanpa Keterangan';
                        }
                        let colKet = `<span style="font-size: 0.85rem; color: #64748b;">${absen.keterangan}</span>`;
                        if (absen.bukti_foto) { 
                            colKet = `<a href="${baseUrl}../public/uploads/bukti_absen/${absen.bukti_foto}" target="_blank" style="display: inline-flex; align-items: center; gap: 5px; text-decoration: none; color: #2563eb; font-weight: 600; font-size: 0.85rem; background: #eff6ff; padding: 4px 8px; border-radius: 6px;"><i class="ph ph-file-text"></i> Lihat Bukti</a>`; 
                        }
                        html += `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 15px; color: #334155;">${absen.tanggal}</td>
                            <td style="padding: 15px; color: #444;">${absen.waktu_masuk}</td>
                            <td style="padding: 15px; color: #444;">${absen.waktu_pulang}</td>
                            <td style="padding: 15px; color: #444;">${absen.total_jam}</td>
                            <td style="padding: 15px;">
                                <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; ${statusStyle}">${statusText}</span>
                            </td>
                            <td style="padding: 15px;">${colKet}</td>
                        </tr>`;
                    });
                }
                
                if(historyBody) { 
                    historyBody.innerHTML = html; 
                    historyBody.style.opacity = '1'; 
                }

                // Render tombol paginasi baru
                renderLocalPagination(data.totalPages, data.currentPage);
            })
            .catch(err => console.error(err));
        };

        // C. Init & Listeners Input Tanggal
        window.setPeriodProfile = function(type) { /* ...Logika Sama... */ 
            const today = new Date();
            let start = new Date();
            let end = new Date();
            if (type === 'today') { } 
            else if (type === 'this_week') {
                const day = today.getDay(); 
                const diff = today.getDate() - day + (day === 0 ? -6 : 1); 
                start.setDate(diff); end.setDate(diff + 6);
            } 
            else if (type === 'this_month') {
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            } 
            else if (type === 'last_month') {
                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                end = new Date(today.getFullYear(), today.getMonth(), 0);
            }
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            startDateProfile.value = formatDate(start);
            endDateProfile.value = formatDate(end);
            loadMyHistory(1);
        };

        startDateProfile.addEventListener('change', () => loadMyHistory(1));
        endDateProfile.addEventListener('change', () => loadMyHistory(1));
        
        // D. Initial Render
        if(pagContainerProfile) {
            const initTotal = parseInt(pagContainerProfile.dataset.totalPages) || 1;
            const initCurrent = parseInt(pagContainerProfile.dataset.currentPage) || 1;
            renderLocalPagination(initTotal, initCurrent);
        }
    }

    // [PENTING] GLOBAL CLICK LISTENER (DI LUAR IF)
    // Ini dipasang di 'document' agar menangkap klik di manapun, 
    // tapi hanya bereaksi jika yang diklik adalah tombol paginasi history.
    document.addEventListener('click', function(e) {
        // Cek apakah target klik ada di dalam #paginationContainerHistory DAN punya class .page-link
        const targetLink = e.target.closest('#paginationContainerHistory .page-link');
        
        if (targetLink) {
            e.preventDefault();
            const li = targetLink.parentElement;
            
            // Cek status disabled/active
            if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                const page = parseInt(targetLink.getAttribute('data-page'));
                if (page && !isNaN(page)) {
                    // Panggil fungsi global yang sudah kita definisikan di atas
                    if (typeof window.loadMyHistory === 'function') {
                        window.loadMyHistory(page);
                    }
                }
            }
        }
    });

    /* =========================================
       7. FITUR DOWNLOAD PDF
       ========================================= */
    const btnExport = document.getElementById('btnExportPdf');
    if (btnExport) {
        btnExport.addEventListener('click', function() {
            const element = document.getElementById('areaPrintAbsensi');
            element.classList.add('printing-mode');
            const opt = { margin: 10, filename: 'Laporan_Absensi.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' } };
            html2pdf().set(opt).from(element).save().then(() => element.classList.remove('printing-mode')).catch(() => element.classList.remove('printing-mode'));
        });
    }


    /* =========================================
       10. FITUR RIWAYAT MASUK (ADMIN) - [LIVE SEARCH & DATE]
       ========================================= */
    const liveSearchMasuk = document.getElementById('liveSearchMasuk');
    if (liveSearchMasuk) {
        const tableBodyMasuk = document.getElementById('tableBodyMasuk');
        const paginationContainerMasuk = document.getElementById('paginationContainerMasuk');
        const startDateInput = document.getElementById('startDateMasuk');
        const endDateInput = document.getElementById('endDateMasuk');
        const baseUrl = liveSearchMasuk.dataset.baseUrl;

        function loadRiwayatMasuk(page = 1) {
            const params = new URLSearchParams({
                ajax: 1, page: page, search: liveSearchMasuk.value,
                start_date: startDateInput ? startDateInput.value : '',
                end_date: endDateInput ? endDateInput.value : ''
            });

            if(tableBodyMasuk) tableBodyMasuk.style.opacity = '0.5';

            fetch(`${baseUrl}admin/riwayatBarangMasuk?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyMasuk.innerHTML = data.html;
                    tableBodyMasuk.style.opacity = '1';
                    renderPaginationUniversal(paginationContainerMasuk, data.totalPages, data.currentPage, loadRiwayatMasuk);
                })
                .catch(err => { console.error(err); if(tableBodyMasuk) tableBodyMasuk.style.opacity = '1'; });
        }

        liveSearchMasuk.addEventListener('input', () => loadRiwayatMasuk(1));
        if(startDateInput) startDateInput.addEventListener('change', () => loadRiwayatMasuk(1));
        if(endDateInput) endDateInput.addEventListener('change', () => loadRiwayatMasuk(1));

        if (paginationContainerMasuk) {
             const links = paginationContainerMasuk.querySelectorAll('.page-link');
             links.forEach(link => {
                 link.addEventListener('click', (e) => {
                     e.preventDefault();
                     const li = e.target.parentElement;
                     if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                         loadRiwayatMasuk(parseInt(e.target.dataset.page));
                     }
                 });
             });
        }
    }

    /* =========================================
       11. FITUR RIWAYAT KELUAR (ADMIN) - [LIVE SEARCH & DATE]
       ========================================= */
    const liveSearchKeluar = document.getElementById('liveSearchKeluar');
    if (liveSearchKeluar) {
        const tableBodyKeluar = document.getElementById('tableBodyKeluar');
        const paginationContainerKeluar = document.getElementById('paginationContainerKeluar');
        const startDateInput = document.getElementById('startDateKeluar');
        const endDateInput = document.getElementById('endDateKeluar');
        const baseUrl = liveSearchKeluar.dataset.baseUrl;

        function loadRiwayatKeluar(page = 1) {
            const params = new URLSearchParams({
                ajax: 1, page: page, search: liveSearchKeluar.value,
                start_date: startDateInput ? startDateInput.value : '',
                end_date: endDateInput ? endDateInput.value : ''
            });

            if(tableBodyKeluar) tableBodyKeluar.style.opacity = '0.5';

            fetch(`${baseUrl}admin/riwayatBarangKeluar?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyKeluar.innerHTML = data.html;
                    tableBodyKeluar.style.opacity = '1';
                    renderPaginationUniversal(paginationContainerKeluar, data.totalPages, data.currentPage, loadRiwayatKeluar);
                })
                .catch(err => { console.error(err); if(tableBodyKeluar) tableBodyKeluar.style.opacity = '1'; });
        }

        liveSearchKeluar.addEventListener('input', () => loadRiwayatKeluar(1));
        if(startDateInput) startDateInput.addEventListener('change', () => loadRiwayatKeluar(1));
        if(endDateInput) endDateInput.addEventListener('change', () => loadRiwayatKeluar(1));

        if (paginationContainerKeluar) {
             const links = paginationContainerKeluar.querySelectorAll('.page-link');
             links.forEach(link => {
                 link.addEventListener('click', (e) => {
                     e.preventDefault();
                     const li = e.target.parentElement;
                     if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                         loadRiwayatKeluar(parseInt(e.target.dataset.page));
                     }
                 });
             });
        }
    }

    /* =========================================
       12. FITUR RIWAYAT RETUR (ADMIN) - [LIVE SEARCH & DATE]
       ========================================= */
    const liveSearchRetur = document.getElementById('liveSearchRetur');
    if (liveSearchRetur) {
        const tableBodyRetur = document.getElementById('tableBodyRetur');
        const paginationContainerRetur = document.getElementById('paginationContainerRetur');
        const startDateInput = document.getElementById('startDateRetur');
        const endDateInput = document.getElementById('endDateRetur');
        const baseUrl = liveSearchRetur.dataset.baseUrl;

        function loadRiwayatRetur(page = 1) {
            const params = new URLSearchParams({
                ajax: 1, page: page, search: liveSearchRetur.value,
                start_date: startDateInput ? startDateInput.value : '',
                end_date: endDateInput ? endDateInput.value : ''
            });

            if(tableBodyRetur) tableBodyRetur.style.opacity = '0.5';

            fetch(`${baseUrl}admin/riwayatReturRusak?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyRetur.innerHTML = data.html;
                    tableBodyRetur.style.opacity = '1';
                    renderPaginationUniversal(paginationContainerRetur, data.totalPages, data.currentPage, loadRiwayatRetur);
                })
                .catch(err => { console.error(err); if(tableBodyRetur) tableBodyRetur.style.opacity = '1'; });
        }

        liveSearchRetur.addEventListener('input', () => loadRiwayatRetur(1));
        if(startDateInput) startDateInput.addEventListener('change', () => loadRiwayatRetur(1));
        if(endDateInput) endDateInput.addEventListener('change', () => loadRiwayatRetur(1));

        if (paginationContainerRetur) {
             const links = paginationContainerRetur.querySelectorAll('.page-link');
             links.forEach(link => {
                 link.addEventListener('click', (e) => {
                     e.preventDefault();
                     const li = e.target.parentElement;
                     if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                         loadRiwayatRetur(parseInt(e.target.dataset.page));
                     }
                 });
             });
        }
    }

    /* =========================================
       13. FITUR RIWAYAT PEMINJAMAN (ADMIN) - [LIVE SEARCH, STATUS & DATE]
       ========================================= */
    const liveSearchPeminjaman = document.getElementById('liveSearchPeminjaman');
    if (liveSearchPeminjaman) {
        const tableBodyPeminjaman = document.getElementById('tableBodyPeminjaman');
        const paginationContainerPeminjaman = document.getElementById('paginationContainerPeminjaman');
        const filterStatusPeminjaman = document.getElementById('filterStatusPeminjaman');
        const startDateInput = document.getElementById('startDatePeminjaman');
        const endDateInput = document.getElementById('endDatePeminjaman');
        const baseUrl = liveSearchPeminjaman.dataset.baseUrl;

        function loadRiwayatPeminjaman(page = 1) {
            const params = new URLSearchParams({
                ajax: 1, page: page, 
                search: liveSearchPeminjaman.value,
                status: filterStatusPeminjaman ? filterStatusPeminjaman.value : '',
                start_date: startDateInput ? startDateInput.value : '',
                end_date: endDateInput ? endDateInput.value : ''
            });

            if(tableBodyPeminjaman) tableBodyPeminjaman.style.opacity = '0.5';

            fetch(`${baseUrl}admin/riwayatPeminjaman?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyPeminjaman.innerHTML = data.html;
                    tableBodyPeminjaman.style.opacity = '1';
                    renderPaginationUniversal(paginationContainerPeminjaman, data.totalPages, data.currentPage, loadRiwayatPeminjaman);
                })
                .catch(err => { console.error(err); if(tableBodyPeminjaman) tableBodyPeminjaman.style.opacity = '1'; });
        }

        liveSearchPeminjaman.addEventListener('input', () => loadRiwayatPeminjaman(1));
        if(filterStatusPeminjaman) filterStatusPeminjaman.addEventListener('change', () => loadRiwayatPeminjaman(1));
        if(startDateInput) startDateInput.addEventListener('change', () => loadRiwayatPeminjaman(1));
        if(endDateInput) endDateInput.addEventListener('change', () => loadRiwayatPeminjaman(1));

        if (paginationContainerPeminjaman) {
             const links = paginationContainerPeminjaman.querySelectorAll('.page-link');
             links.forEach(link => {
                 link.addEventListener('click', (e) => {
                     e.preventDefault();
                     const li = e.target.parentElement;
                     if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                         loadRiwayatPeminjaman(parseInt(e.target.dataset.page));
                     }
                 });
             });
        }
    }

    /* =========================================
       14. FITUR PERINTAH OPNAME (ADMIN) - [Check All & Detail Kategori]
       ========================================= */
    const opnamePage = document.getElementById('opnamePerintahPage');
    
    // Pastikan kita ada di halaman yang benar
    if (opnamePage) {
        
        // [PERBAIKAN] Ambil Base URL langsung dari atribut HTML (Lebih Stabil)
        const baseUrl = opnamePage.dataset.baseUrl; 

        // 1. Logic Check All (Checkbox Pilih Semua)
        const checkAll = document.getElementById('checkAll');
        if(checkAll) {
            checkAll.addEventListener('change', function() {
                let checkboxes = document.querySelectorAll('.cat-check');
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        }

        // 2. Logic Tombol Detail (AJAX Popup)
        const detailButtons = document.querySelectorAll('.btn-detail-cat');
        
        detailButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Ambil data dari tombol
                const catId = this.getAttribute('data-id');
                const catName = this.getAttribute('data-nama');
                
                // Susun URL yang benar
                const url = `${baseUrl}admin/getCategoryDetails/${catId}`;

                // Debugging: Cek di Console browser jika tidak muncul
                console.log("Requesting URL:", url);

                // Tampilkan Loading
                Swal.fire({
                    title: 'Memuat Data...',
                    text: 'Sedang mengambil daftar barang...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // Ambil data via AJAX
                fetch(url)
                    .then(response => {
                        if (!response.ok) { throw new Error('Network response was not ok'); }
                        return response.json();
                    })
                    .then(data => {
                        let htmlContent = '';
                        
                        if (data.length === 0) {
                            htmlContent = '<div style="padding:20px; text-align:center; color:#666;">Tidak ada barang dalam kategori ini.</div>';
                        } else {
                            // Buat Tabel Sederhana dalam Popup
                            htmlContent = `
                                <div style="text-align: left; max-height: 300px; overflow-y: auto; border: 1px solid #ddd;">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                                        <thead style="position: sticky; top: 0; background: #f1f1f1;">
                                            <tr>
                                                <th style="border: 1px solid #ddd; padding: 8px;">Kode</th>
                                                <th style="border: 1px solid #ddd; padding: 8px;">Nama Barang</th>
                                                <th style="border: 1px solid #ddd; padding: 8px;">Merek</th>
                                                <th style="border: 1px solid #ddd; padding: 8px;">Lokasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            
                            data.forEach(item => {
                                htmlContent += `
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 5px;">${item.kode_barang}</td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">${item.nama_barang}</td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">${item.nama_merek || '-'}</td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">${item.lokasi_utama || '-'}</td>
                                    </tr>
                                `;
                            });

                            htmlContent += `</tbody></table></div>`;
                        }

                        // Tampilkan di SweetAlert
                        Swal.fire({
                            title: 'Detail: ' + catName,
                            html: htmlContent,
                            width: '700px',
                            confirmButtonText: 'Tutup'
                        });
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal mengambil data barang.\nCek Console untuk detail.', 'error');
                        console.error('AJAX Error:', err);
                    });
            });
        });
    }
    /* =========================================
       15. FITUR GRAFIK ANALITIK (ADMIN) - [NEW]
       ========================================= */
    const ctxGrafik = document.getElementById('grafikAnalitik');
    if (ctxGrafik) {
        // Ambil data dari atribut data-* di elemen canvas
        // JSON.parse mengubah string JSON kembali menjadi Array JS
        const labels = JSON.parse(ctxGrafik.dataset.labels);
        const dataMasuk = JSON.parse(ctxGrafik.dataset.masuk);
        const dataKeluar = JSON.parse(ctxGrafik.dataset.keluar);

        new Chart(ctxGrafik, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Barang Masuk',
                        data: dataMasuk,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)', // Biru
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Barang Keluar',
                        data: dataKeluar,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)', // Merah
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { position: 'top' }
                }
            }
        });
    }

    
    /* =========================================
       16. FITUR EXPORT SELECTOR (ADMIN) - [NEW]
       ========================================= */
    const btnExportSelector = document.getElementById('btnExportSelector');
    if (btnExportSelector) {
        // Ambil base URL dari input search atau atribut lain yang ada
        const searchInput = document.getElementById('liveSearchBarang');
        const baseUrl = searchInput ? searchInput.dataset.baseUrl : '';

        btnExportSelector.addEventListener('click', function() {
            Swal.fire({
                title: 'Pilih Format Export',
                
                // Gunakan HTML custom untuk icon dan instruksi
                html: `
                    <div style="text-align: center; margin-bottom: 20px; padding-top: 10px;">
                        <i class="ph ph-file-arrow-down" style="font-size: 3.5rem; color: var(--primer-lightblue);"></i>
                        <p style="margin-top: 10px; font-size: 0.95em;">Silakan pilih format file yang ingin diunduh:</p>
                    </div>
                `,
                
                // --- PENGATURAN TOMBOL ---
                showCancelButton: true,
                showDenyButton: true, // WAJIB TRUE karena ada 3 pilihan
                
                // Urutan default: Deny, Cancel, Confirm.
                // Kita akan override warnanya agar lebih rapi.
                
                confirmButtonText: 'Excel (.xls)',
                confirmButtonColor: '#10b981', // Warna Green (Success Color)
                
                denyButtonText: 'CSV (.csv)',
                denyButtonColor: '#152e4d',    // Warna Dark Blue (Brand Dark)
                
                cancelButtonText: 'Batal',
                cancelButtonColor: '#6c757d'   // Warna Grey (Netral)

            }).then((result) => {
                // Ambil nilai filter saat ini
                const searchVal = document.getElementById('liveSearchBarang').value;
                const startDateVal = document.getElementById('startDateMasuk')?.value || ''; // Optional chaining
                const endDateVal = document.getElementById('endDateMasuk')?.value || '';

                const params = new URLSearchParams({
                    search: searchVal,
                    start_date: startDateVal,
                    end_date: endDateVal
                });

                if (result.isConfirmed) {
                    // User pilih Excel
                    window.location.href = baseUrl + 'admin/exportBarang/excel?' + params.toString();
                } else if (result.isDenied) {
                    // User pilih CSV
                    window.location.href = baseUrl + 'admin/exportBarang/csv?' + params.toString();
                }
            });
        });
    }
    /* =========================================
       17. FITUR IMPORT CSV BARANG (ADMIN) - [REVISI UI]
       ========================================= */
    const btnImportCsv = document.getElementById('btnImportCsv');
    if (btnImportCsv) {
        const searchInput = document.getElementById('liveSearchBarang');
        const baseUrl = searchInput ? searchInput.dataset.baseUrl : '';

        btnImportCsv.addEventListener('click', function() {
            Swal.fire({
                title: 'Import Data Barang',
                // HTML Custom (Biarkan sama seperti sebelumnya)
                html: `
                    <form id="formImportBarang" action="${baseUrl}admin/processImportBarang" method="POST" enctype="multipart/form-data">
                        <div class="import-instruction-box">
                            <strong>Format Kolom CSV (Tanpa Header):</strong>
                            <ul>
                                <li>1. Kode Barang (Wajib, Unik)</li>
                                <li>2. Nama Barang (Wajib)</li>
                                <li>3. Kategori (Teks, misal: "Sabun")</li>
                                <li>4. Merek (Teks, misal: "Lifebuoy")</li>
                                <li>5. Satuan (Teks, misal: "Pcs")</li>
                                <li>6. Stok Awal (Angka)</li>
                                <li>7. Lokasi (Kode Rak, misal: "A1-01")</li>
                            </ul>
                        </div>
                        <div class="custom-file-upload" id="dropZone">
                            <input type="file" name="csv_file" id="csvFileInput" class="hidden-input-file" accept=".csv, .txt" required>
                            <i class="ph ph-file-csv"></i>
                            <span class="main-text">Klik atau Tarik File CSV ke Sini</span>
                            <span class="sub-text">Maksimal ukuran file 2MB</span>
                        </div>
                        <div id="fileNameDisplay" class="selected-file-name"></div>
                    </form>
                `,
                
                // --- PENGATURAN TOMBOL (FIXED) ---
               showCancelButton: true,     // Tampilkan tombol Batal
                showDenyButton: false,      // <--- INI KUNCI UNTUK MENGHILANGKAN TOMBOL "NO"
                showConfirmButton: true,
                confirmButtonText: 'Upload & Proses',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#152e4d', 
                cancelButtonColor: '#ef4444',  
                reverseButtons: true,       // Urutan tombol yang benar
                
                // Logic Javascript (Tetap sama)
                didOpen: () => {
                    const fileInput = document.getElementById('csvFileInput');
                    const fileNameDisplay = document.getElementById('fileNameDisplay');
                    const dropZone = document.getElementById('dropZone');
                    const mainText = dropZone.querySelector('.main-text');

                    fileInput.addEventListener('change', function() {
                        if (this.files && this.files.length > 0) {
                            const name = this.files[0].name;
                            fileNameDisplay.innerHTML = `📄 File terpilih: ${name}`;
                            fileNameDisplay.style.display = 'block';
                            
                            dropZone.style.borderColor = '#10b981';
                            dropZone.style.backgroundColor = '#ecfdf5';
                            mainText.innerText = "Ganti File?";
                        }
                    });
                },

                preConfirm: () => {
                    const form = document.getElementById('formImportBarang');
                    const fileInput = form.querySelector('input[type="file"]');
                    if (!fileInput.files.length) {
                        Swal.showValidationMessage('⚠️ Silakan pilih file CSV terlebih dahulu!');
                        return false;
                    }
                    form.submit();
                }
            });
        });
    }
    /* =========================================
       18. FITUR AUTO GENERATE KODE BARANG (ADMIN) - [NEW]
       ========================================= */
    const btnAutoCode = document.getElementById('btnAutoCode');
    if (btnAutoCode) {
        const inputKode = document.getElementById('kode_barang');
        
        // Ambil base URL (Kita bisa ambil dari form action atau elemen lain)
        // Cara paling aman: Ambil dari tombol logout atau buat elemen hidden
        // Di sini kita pakai trik ambil dari form parent
        const form = btnAutoCode.closest('form');
        const actionUrl = form.getAttribute('action'); 
        // actionUrl biasanya: .../admin/processBarang
        // Kita butuh: .../admin/
        const baseUrl = actionUrl.substring(0, actionUrl.lastIndexOf('/') + 1).replace('processBarang', ''); 

        btnAutoCode.addEventListener('click', function() {
            // Tampilkan loading di input (UX)
            inputKode.value = 'Generating...';
            inputKode.setAttribute('readonly', true);

            fetch(`${baseUrl}getAutoCode/BRG`) // Default prefix BRG
                .then(response => response.json())
                .then(data => {
                    if (data.code) {
                        inputKode.value = data.code;
                        // Efek kedip kuning tanda sukses
                        inputKode.style.backgroundColor = '#fff3cd';
                        setTimeout(() => {
                            inputKode.style.backgroundColor = '';
                        }, 500);
                    } else {
                        inputKode.value = '';
                        Swal.fire('Gagal', 'Tidak bisa generate kode.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    inputKode.value = '';
                    Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
                })
                .finally(() => {
                    inputKode.removeAttribute('readonly');
                });
        });
    }
    /* =========================================
       19. FITUR DETAIL RIWAYAT MASUK (POPUP) - [NEW]
       ========================================= */
    const tableBodyMasuk = document.getElementById('tableBodyMasuk');
    if (tableBodyMasuk) {
        // Event Delegation (karena tombol bisa muncul dari AJAX)
        tableBodyMasuk.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-detail-masuk')) {
                const data = JSON.parse(e.target.getAttribute('data-detail'));
                const baseUrl = document.querySelector('main').getAttribute('data-base-url'); // Pastikan <main> punya ini

                let buktiHtml = '<span style="color:#999;">Tidak ada bukti foto</span>';
                if (data.bukti_foto) {
                    buktiHtml = `<a href="${baseUrl}uploads/bukti_transaksi/${data.bukti_foto}" target="_blank" class="btn btn-sm btn-primary">📄 Lihat Foto Bukti</a>`;
                }

                const prodDate = data.production_date ? data.production_date : '-';
                const expDate = data.exp_date ? data.exp_date : '-';

                Swal.fire({
                    title: 'Detail Barang Masuk',
                    html: `
                        <table style="width:100%; text-align:left; font-size:0.95em; border-collapse: collapse;">
                            <tr style="border-bottom:1px solid #eee;"><td style="padding:8px; font-weight:bold;">Nama Barang:</td><td style="padding:8px;">${data.nama_barang}</td></tr>
                            <tr style="border-bottom:1px solid #eee;"><td style="padding:8px; font-weight:bold;">Supplier:</td><td style="padding:8px;">${data.nama_supplier || '-'}</td></tr>
                            <tr style="border-bottom:1px solid #eee;"><td style="padding:8px; font-weight:bold;">Jumlah:</td><td style="padding:8px;">${data.jumlah} ${data.nama_satuan}</td></tr>
                            <tr style="border-bottom:1px solid #eee; background:#f9f9f9;"><td style="padding:8px; font-weight:bold;">No. Batch/Lot:</td><td style="padding:8px;">${data.lot_number || '-'}</td></tr>
                            <tr style="border-bottom:1px solid #eee; background:#f9f9f9;"><td style="padding:8px; font-weight:bold;">Tgl. Produksi:</td><td style="padding:8px;">${prodDate}</td></tr>
                            <tr style="border-bottom:1px solid #eee; background:#f9f9f9;"><td style="padding:8px; font-weight:bold;">Tgl. Kedaluwarsa:</td><td style="padding:8px; color:red;">${expDate}</td></tr>
                            <tr style="border-bottom:1px solid #eee;"><td style="padding:8px; font-weight:bold;">Keterangan:</td><td style="padding:8px;">${data.keterangan || '-'}</td></tr>
                            <tr style="border-bottom:1px solid #eee;"><td style="padding:8px; font-weight:bold;">Bukti:</td><td style="padding:8px;">${buktiHtml}</td></tr>
                            <tr><td style="padding:8px; font-weight:bold;">Diinput Oleh:</td><td style="padding:8px;">${data.staff_nama} <br><small style="color:#666;">${data.created_at}</small></td></tr>
                        </table>
                    `,
                    width: '600px',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#6c757d'
                });
            }
        });
    }
    /* =========================================
       20. FITUR EXPORT RIWAYAT MASUK (ADMIN) - [NEW]
       ========================================= */
    const btnExportMasuk = document.getElementById('btnExportMasuk');
    if (btnExportMasuk) {
        btnExportMasuk.addEventListener('click', function() {
            // Ambil nilai filter saat ini
            const searchVal = document.getElementById('liveSearchMasuk').value;
            const startDateVal = document.getElementById('startDateMasuk').value;
            const endDateVal = document.getElementById('endDateMasuk').value;
            
            // Ambil base URL (dari atribut di main atau input search)
            const baseUrl = document.querySelector('main').getAttribute('data-base-url');

            Swal.fire({
                title: 'Pilih Format Export',
                text: 'Silakan pilih format file yang diinginkan:',
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '📄 Excel (.xls)',
                confirmButtonColor: '#217346',
                denyButtonText: '📝 CSV (.csv)',
                denyButtonColor: '#6c757d',
                cancelButtonText: 'Batal'
            }).then((result) => {
                let exportUrl = '';
                const params = new URLSearchParams({
                    search: searchVal,
                    start_date: startDateVal,
                    end_date: endDateVal
                });

                if (result.isConfirmed) {
                    // Excel
                    exportUrl = `${baseUrl}admin/exportRiwayatMasuk/excel?${params.toString()}`;
                    window.location.href = exportUrl;
                } else if (result.isDenied) {
                    // CSV
                    exportUrl = `${baseUrl}admin/exportRiwayatMasuk/csv?${params.toString()}`;
                    window.location.href = exportUrl;
                }
            });
        });
    }

    /* =========================================
       21. FITUR MULTI-UPLOAD FILE QUEUE (STAFF) - [NEW]
       ========================================= */
    const btnAddFile = document.getElementById('btn-add-file');
    
    if (btnAddFile) {
        const inputTemp = document.getElementById('bukti_foto_input'); // Input pemicu
        const inputFinal = document.getElementById('bukti_foto_final'); // Input penampung
        const previewContainer = document.getElementById('preview-container');
        
        // Kita butuh DataTransfer untuk memanipulasi file list
        const dt = new DataTransfer();

        // 1. Klik tombol "+ Tambah" -> Klik input file tersembunyi
        btnAddFile.addEventListener('click', () => {
            inputTemp.click();
        });

        // 2. Saat file dipilih
        inputTemp.addEventListener('change', function() {
            const newFiles = this.files;
            
            for (let i = 0; i < newFiles.length; i++) {
                const file = newFiles[i];
                
                // Tambahkan ke antrean DataTransfer
                dt.items.add(file);
                
                // Buat elemen Preview
                const div = document.createElement('div');
                div.className = 'file-preview-item';
                div.style.cssText = 'position: relative; width: 100px; height: 100px; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f9f9f9;';

                // Tombol Hapus (X)
                const btnRemove = document.createElement('button');
                btnRemove.innerHTML = '×';
                btnRemove.style.cssText = 'position: absolute; top: 0; right: 0; background: red; color: white; border: none; width: 20px; height: 20px; cursor: pointer; font-weight: bold; line-height: 1;';
                
                // Simpan index file di tombol hapus (untuk logika hapus nanti)
                // Note: Kita gunakan timestamp unik agar aman saat hapus acak
                const uniqueId = Date.now() + i;
                div.dataset.id = uniqueId;
                // Kita tambahkan property custom ke file object agar bisa dilacak
                file.uniqueId = uniqueId;

                // Tampilkan Gambar atau Ikon
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';
                    div.appendChild(img);
                } else {
                    // Jika PDF atau lainnya
                    div.innerHTML += '<span style="font-size: 30px;">📄</span>';
                    const name = document.createElement('span');
                    name.innerText = file.name.substring(0, 8) + '...';
                    name.style.cssText = 'position: absolute; bottom: 0; font-size: 10px; width: 100%; text-align: center; background: rgba(255,255,255,0.8);';
                    div.appendChild(name);
                }

                div.appendChild(btnRemove);
                previewContainer.appendChild(div);

                // 3. Logika Hapus File dari Antrean
                btnRemove.addEventListener('click', function() {
                    // Hapus elemen visual
                    div.remove();
                    
                    // Hapus file dari DataTransfer
                    // Kita harus regenerate DataTransfer baru tanpa file yang dihapus
                    const newDataTransfer = new DataTransfer();
                    
                    // Loop semua file yang ada di dt lama
                    for (let j = 0; j < dt.files.length; j++) {
                        // Jika ID file tidak sama dengan ID yang dihapus, masukkan ke dt baru
                        if (dt.files[j].uniqueId !== uniqueId) {
                            newDataTransfer.items.add(dt.files[j]);
                        }
                    }
                    
                    // Update dt utama
                    dt.items.clear();
                    for (let k = 0; k < newDataTransfer.files.length; k++) {
                        dt.items.add(newDataTransfer.files[k]);
                    }
                    
                    // Sinkronkan ke input final
                    inputFinal.files = dt.files;
                });
            }

            // 4. Update Input Final agar bisa disubmit
            inputFinal.files = dt.files;
            
            // Reset input temp agar bisa pilih file yang sama lagi jika mau
            inputTemp.value = '';
        });
    }

    /* =========================================
       22. FITUR AUTO BATCH NUMBER (STAFF) - [NEW]
       ========================================= */
    const btnAutoBatch = document.getElementById('btnAutoBatch');
    if (btnAutoBatch) {
        const inputLot = document.getElementById('lot_number');
        
        // Ambil base URL (Trik ambil dari sidebar logout link atau atribut main)
        const baseUrl = document.querySelector('main').getAttribute('data-base-url') || 
                        document.querySelector('.app-sidebar a').getAttribute('href').split('staff/')[0]; 

        btnAutoBatch.addEventListener('click', function() {
            // Tampilkan indikator loading
            const originalText = inputLot.value;
            inputLot.value = 'Generating...';
            inputLot.setAttribute('readonly', true);

            fetch(`${baseUrl}staff/getAutoBatchCode`)
                .then(response => response.json())
                .then(data => {
                    if (data.code) {
                        inputLot.value = data.code;
                        // Efek kedip kuning tanda sukses
                        inputLot.style.backgroundColor = '#fff3cd';
                        setTimeout(() => {
                            inputLot.style.backgroundColor = '';
                        }, 500);
                    } else {
                        inputLot.value = '';
                        Swal.fire('Gagal', 'Tidak bisa generate batch.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    inputLot.value = ''; // Reset jika error
                    Swal.fire('Error', 'Gagal koneksi ke server.', 'error');
                })
                .finally(() => {
                    inputLot.removeAttribute('readonly');
                });
        });
    }

    /* =========================================
       23. FITUR BARCODE SCANNER (WEBCAM) - [NEW]
       ========================================= */
    const btnScan = document.getElementById('btnScanBarcode');
    
    if (btnScan) {
        const selectProduct = document.getElementById('product_id');
        const readerDiv = document.getElementById('reader');
        let html5QrcodeScanner = null;

        btnScan.addEventListener('click', function() {
            // Toggle Tampilkan/Sembunyikan Kamera
            if (readerDiv.style.display === 'none') {
                readerDiv.style.display = 'block';
                startScanner();
                btnScan.textContent = '❌ Stop Scan';
                btnScan.classList.replace('btn-info', 'btn-danger');
            } else {
                stopScanner();
            }
        });

        function startScanner() {
            // Inisialisasi Scanner
            // fps: Frame per second (kecepatan baca)
            // qrbox: Ukuran kotak fokus scanning
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", { fps: 10, qrbox: {width: 250, height: 250} }
            );

            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(() => {
                    readerDiv.style.display = 'none';
                    btnScan.textContent = '📷 Scan';
                    btnScan.classList.replace('btn-danger', 'btn-info');
                    html5QrcodeScanner = null;
                }).catch(error => {
                    console.error("Failed to clear html5QrcodeScanner. ", error);
                });
            } else {
                readerDiv.style.display = 'none';
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            // 1. Hasil scan didapat (misal: "BRG-001")
            console.log(`Code matched = ${decodedText}`, decodedResult);
            
            // 2. Cari opsi di dropdown yang punya data-kode == hasil scan
            let found = false;
            for (let i = 0; i < selectProduct.options.length; i++) {
                // Bandingkan kode barang (lowercase biar aman)
                if (selectProduct.options[i].getAttribute('data-kode') === decodedText) {
                    // 3. Jika ketemu, pilih opsi tersebut
                    selectProduct.selectedIndex = i;
                    found = true;
                    
                    // 4. Matikan kamera otomatis
                    stopScanner();
                    
                    // 5. Picu event 'change' agar logika Lot/Expired muncul (jika ada)
                    selectProduct.dispatchEvent(new Event('change'));
                    
                    // 6. Beri feedback suara/alert
                    Swal.fire({
                        icon: 'success',
                        title: 'Barang Ditemukan!',
                        text: decodedText,
                        timer: 1000,
                        showConfirmButton: false
                    });
                    break;
                }
            }

            if (!found) {
                // Jika barang tidak ada di database
                // Kita pause sebentar scanningnya agar user bisa baca alert
                // (Library ini agak agresif scanningnya)
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Ditemukan',
                    text: `Kode "${decodedText}" tidak terdaftar di sistem.`,
                    timer: 2000
                });
            }
        }

        function onScanFailure(error) {
            // Biarkan kosong agar console tidak penuh spam saat sedang mencari kode
            // console.warn(`Code scan error = ${error}`);
        }
    }

    /* =========================================
       24. SIDEBAR ACCORDION (REVISI)
       ========================================= */
    const menuItems = document.querySelectorAll('.sidebar-menu > ul > li > a');
    
    if (menuItems) {
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Cek apakah link ini punya submenu (elemen <ul> dengan class .submenu setelah <a>)
                const submenu = this.nextElementSibling;
                
                if (submenu && submenu.classList.contains('submenu')) {
                    e.preventDefault(); // Mencegah link '#' pindah halaman/scroll ke atas
                    
                    // Ambil elemen <li> induknya
                    const parentLi = this.parentElement;

                    // (Opsional) Tutup menu lain jika ingin mode 'Accordion' murni (satu terbuka, yang lain tutup)
                    // document.querySelectorAll('.sidebar-menu > ul > li.active').forEach(activeItem => {
                    //     if (activeItem !== parentLi) {
                    //         activeItem.classList.remove('active');
                    //     }
                    // });

                    // Toggle class 'active' pada <li> yang diklik
                    // CSS akan menangani display: block/none secara otomatis
                    parentLi.classList.toggle('active');
                }
            });
        });
    }
    
}); // END DOMContentLoaded

/* =========================================
   9. FUNGSI GLOBAL (POPUP / MODAL / HELPER)
   ========================================= */

function handleBulkDelete(ids, url, onSuccessCallback) {
    if (ids.length === 0) return;

    Swal.fire({
        title: `Hapus ${ids.length} Data Terpilih?`, 
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning', 
        
        // --- STYLE DISAMAKAN ---
        iconColor: '#152e4d',          // Ikon (!) Biru Tua
        confirmButtonColor: '#152e4d', // Tombol Ya Biru Tua
        cancelButtonColor: '#f8c21a',  // Tombol Batal Kuning
        
        showCancelButton: true, 
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        
        // Styling teks tombol Batal
        didOpen: () => {
            const cancelBtn = Swal.getCancelButton();
            if (cancelBtn) {
                cancelBtn.style.color = '#152e4d';
                cancelBtn.style.fontWeight = 'bold';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading saat proses penghapusan berjalan
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ids: ids})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#152e4d'
                    }).then(() => onSuccessCallback());
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#152e4d'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    title: 'Error',
                    text: 'Terjadi kesalahan server.',
                    icon: 'error',
                    confirmButtonColor: '#152e4d'
                });
            });
        }
    });
}

function editAbsenPopup(id, nama, masuk, pulang, status, keterangan) {
    const template = document.getElementById('templateEditAbsenAdmin');
    if(!template) return;
    
    const searchElem = document.getElementById('searchAbsensi');
    const baseUrl = searchElem ? searchElem.dataset.baseUrl : '';
    
    Swal.fire({
        title: 'Edit Data Absensi', 
        html: template.innerHTML, 
        showCancelButton: true, 
        confirmButtonText: '<i class="ph ph-floppy-disk"></i> Simpan Perubahan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#152e4d', 
        cancelButtonColor: '#f8c21a',
        
        didOpen: () => {
            const popup = Swal.getPopup();
            const form = popup.querySelector('form');
            form.action = baseUrl + 'admin/updateAbsensiManual';
            
            // Isi data awal
            popup.querySelector('#edit_absen_id').value = id;
            popup.querySelector('#edit_nama').value = nama;
            popup.querySelector('#edit_status').value = (status === 'Masih Bekerja') ? 'Hadir' : status;
            if(masuk && masuk.trim() !== '-') popup.querySelector('#edit_masuk').value = masuk;
            if(pulang && pulang.trim() !== '-') popup.querySelector('#edit_pulang').value = pulang;
            if(keterangan && keterangan !== 'null' && keterangan !== '-') popup.querySelector('#edit_keterangan').value = keterangan;

            // Style Tombol Cancel
            const cancelBtn = Swal.getCancelButton();
            if (cancelBtn) {
                cancelBtn.style.color = '#152e4d';
                cancelBtn.style.fontWeight = 'bold';
            }

            // --- FITUR DRAG & DROP + PREVIEW NAMA FILE ---
            const dropZone = popup.querySelector('#drop_zone_area');
            const fileInput = popup.querySelector('#input_bukti_file');
            const labelFile = popup.querySelector('#label_file_name');

            // 1. Jika user KLIK dan pilih file
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    labelFile.innerHTML = `<strong>File Terpilih:</strong> ${this.files[0].name}`;
                    labelFile.style.color = '#152e4d';
                    dropZone.style.borderColor = '#152e4d';
                    dropZone.style.backgroundColor = '#e0f2fe';
                }
            });

            // 2. Efek saat file DI-SERET masuk area (Drag Over)
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = '#152e4d';
                dropZone.style.backgroundColor = '#e0f2fe'; // Biru muda
                labelFile.innerText = "Lepaskan file di sini...";
            });

            // 3. Efek saat file KELUAR area (Drag Leave)
            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = '#cbd5e1';
                dropZone.style.backgroundColor = '#f8fafc'; // Putih abu
                labelFile.innerText = "Klik atau Seret File ke Sini (JPG/PNG/PDF)";
            });

            // 4. Saat file DI-JATUHKAN (Drop)
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    // Masukkan file yang di-drop ke dalam input file
                    fileInput.files = files;
                    
                    // Update Teks Label
                    labelFile.innerHTML = `<strong>File Terpilih:</strong> ${files[0].name}`;
                    labelFile.style.color = '#152e4d';
                    
                    // Pertahankan warna background aktif
                    dropZone.style.borderColor = '#152e4d';
                    dropZone.style.backgroundColor = '#e0f2fe';
                }
            });

            // --- LOGIKA TOGGLE STATUS (Hadir vs Sakit) ---
            const selectStatus = popup.querySelector('#edit_status');
            const rowJam = popup.querySelector('#row_jam');
            const rowKet = popup.querySelector('#row_keterangan');

            function toggleForm() {
                if (selectStatus.value === 'Hadir') { 
                    rowJam.style.display = 'flex'; 
                    rowKet.style.display = 'none'; 
                } else { 
                    rowJam.style.display = 'none'; 
                    rowKet.style.display = 'block'; 
                }
            }
            
            toggleForm();
            selectStatus.addEventListener('change', toggleForm);
        },
        preConfirm: () => { 
            const form = Swal.getPopup().querySelector('form');
            if(form) form.submit();
        }
    });
}

function showIzinModal(actionUrl) {
    const template = document.getElementById('templateModalIzin');
    if(!template) return;
    
    Swal.fire({
        title: 'Form Izin / Sakit',
        html: template.innerHTML,
        showCancelButton: true,
        confirmButtonText: 'Kirim Pengajuan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#152e4d',
        cancelButtonColor: '#f8c21a',
        
        didOpen: () => {
            const popup = Swal.getPopup();
            
            // 1. Set Action URL
            const form = popup.querySelector('form'); 
            if(form) form.action = actionUrl;

            // 2. Style Tombol Cancel
            const cancelBtn = Swal.getCancelButton();
            if (cancelBtn) {
                cancelBtn.style.color = '#152e4d';
                cancelBtn.style.fontWeight = 'bold';
            }

            // --- FITUR DRAG & DROP (BARU DITAMBAHKAN) ---
            const dropZone = popup.querySelector('#drop_zone_izin');
            const fileInput = popup.querySelector('#input_bukti_izin');
            const labelArea = popup.querySelector('#label_file_izin');

            // Fungsi Update UI saat ada file
            const updateUI = (file) => {
                labelArea.innerHTML = `
                    <i class="ph ph-check-circle" style="font-size: 1.5rem; color: #152e4d; display: block; margin-bottom: 5px;"></i>
                    <span style="color: #152e4d; font-weight: bold;">${file.name}</span>
                `;
                dropZone.style.borderColor = '#152e4d';
                dropZone.style.backgroundColor = '#e0f2fe'; // Biru muda
            };

            // A. Event saat user KLIK manual
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    updateUI(this.files[0]);
                }
            });

            // B. Event Drag Over (Saat file diseret di atas kotak)
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = '#152e4d';
                dropZone.style.backgroundColor = '#e0f2fe';
                if(!fileInput.files.length) {
                    labelArea.querySelector('span').innerText = "Lepaskan file di sini...";
                }
            });

            // C. Event Drag Leave (Saat file keluar dari kotak tanpa di-drop)
            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                // Kembalikan style jika belum ada file yang dipilih
                if (!fileInput.files.length) {
                    dropZone.style.borderColor = '#cbd5e1';
                    dropZone.style.backgroundColor = '#f8fafc';
                    labelArea.querySelector('span').innerText = "Klik atau Seret File ke Sini";
                }
            });

            // D. Event DROP (Saat file dilepaskan)
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files; // Masukkan file ke input
                    updateUI(files[0]);      // Update tampilan
                }
            });
        },

        preConfirm: () => {
            const form = Swal.getPopup().querySelector('form');
            if (!form.checkValidity()) {
                form.reportValidity(); 
                return false; 
            }
            form.submit();
        }
    });
}

function renderPaginationUniversal(container, totalPages, currentPage, callbackFunction) {
     if (!container) return;
     
     currentPage = parseInt(currentPage);
     totalPages = parseInt(totalPages);
     
     // REVISI: Jangan sembunyikan pagination meski halamannya cuma 1.
     // Biarkan tetap render agar layout tidak 'lompat'.
     if (totalPages < 1) totalPages = 1; 
     
     let html = '<nav><ul class="pagination">';
     
     // 1. Tombol Previous
     const prevDisabled = currentPage === 1 ? 'disabled' : '';
     html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
     
     // 2. Tombol Angka
     let start = Math.max(1, currentPage - 2);
     let end = Math.min(totalPages, currentPage + 2);

     // Logika tambahan agar angka tetap cantik jika total page sedikit
     if (totalPages <= 5) {
         start = 1;
         end = totalPages;
     }

     for (let i = start; i <= end; i++) {
         const active = i === currentPage ? 'active' : '';
         html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
     }
     
     // 3. Tombol Next
     const nextDisabled = currentPage === totalPages ? 'disabled' : '';
     html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
     
     html += '</ul></nav>';
     
     container.innerHTML = html;

     // Re-attach event listeners
     const links = container.querySelectorAll('.page-link');
     links.forEach(link => {
         link.addEventListener('click', (e) => {
             e.preventDefault();
             // Mencegah klik jika disabled atau active
             const li = e.target.parentElement;
             if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                 callbackFunction(parseInt(e.target.dataset.page));
             }
         });
     });
}
//  FITUR UNLOCK USER (SWEETALERT)
    document.body.addEventListener('click', function(e) {
        // 1. Deteksi klik pada tombol unlock (atau ikon di dalamnya)
        const unlockBtn = e.target.closest('.btn-unlock-user');

        if (unlockBtn) {
            e.preventDefault(); 
            
            const targetUrl = unlockBtn.getAttribute('data-url');
            if (!targetUrl) return;

            Swal.fire({
                title: 'Buka Kunci Akun?', 
                text: "Pengguna ini akan dapat login kembali.",
                icon: 'question', 
                
                // --- REVISI: TEMA BIRU TUA (Sama dengan Hapus) ---
                iconColor: '#152e4d',          // Ikon (?) jadi Biru Tua
                confirmButtonColor: '#152e4d', // Tombol YA jadi Biru Tua
                cancelButtonColor: '#f8c21a',  // Tombol Batal Tetap Kuning
                
                showCancelButton: true, 
                confirmButtonText: 'Ya, Buka Kunci!', 
                cancelButtonText: 'Batal',
                
                // Styling teks tombol Batal agar kontras
                didOpen: () => {
                    const cancelBtn = Swal.getCancelButton();
                    if (cancelBtn) {
                        cancelBtn.style.color = '#152e4d';
                        cancelBtn.style.fontWeight = 'bold';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        timer: 1000,
                        showConfirmButton: false,
                        didOpen: () => { Swal.showLoading(); }
                    }).then(() => {
                        window.location.href = targetUrl;
                    });
                }
            });
        }
        });
    
/* =========================================
   27. LOGIKA REKAP ABSENSI (GLOBAL SCOPE)
   ========================================= */

function navShowPicker() {
    const picker = document.getElementById('datePickerNative');
    if (picker) {
        // Coba gunakan API modern showPicker()
        if (typeof picker.showPicker === 'function') {
            try {
                picker.showPicker();
            } catch (err) {
                // Fallback jika browser memblokir (jarang terjadi jika user action)
                console.error(err);
                picker.click(); 
            }
        } else {
            // Fallback untuk browser lama
            picker.focus();
            picker.click();
        }
    } else {
        console.error("Elemen datePickerNative tidak ditemukan!");
    }
}

function navChangeDate(days) {
    const currentInput = document.getElementById('datePickerNative');
    if (!currentInput) return;
    
    const strDate = currentInput.value;
    if (!strDate) return;

    // Trik: Tambahkan waktu T12:00:00 agar tanggal tidak bergeser karena timezone
    const currentDate = new Date(strDate + 'T12:00:00');
    
    // Tambah/Kurang hari
    currentDate.setDate(currentDate.getDate() + days);
    
    // Format kembali ke YYYY-MM-DD
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const day = String(currentDate.getDate()).padStart(2, '0');
    const newDateStr = `${year}-${month}-${day}`;
    
    navGoToDate(newDateStr);
}

function navGoToDate(dateStr) {
    // PERBAIKAN UTAMA: Gunakan selector atribut [data-base-url] 
    // agar tidak salah mengambil div pembungkus layout
    const mainElem = document.querySelector('[data-base-url]');
    
    let baseUrl = "";
    if (mainElem) {
        baseUrl = mainElem.dataset.baseUrl;
    } else {
        // Fallback darurat jika elemen tidak ketemu
        console.error("Base URL element not found!");
        return; 
    }
    
    // Redirect ke tanggal yang dipilih
    window.location.href = baseUrl + "admin/rekapAbsensi?mode=harian&date=" + dateStr;
}

function applyReportFilter() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    const user = document.getElementById('filterUser').value;
    
    const mainElem = document.querySelector('[data-base-url]'); // Perbaikan selector juga di sini
    const baseUrl = mainElem ? mainElem.dataset.baseUrl : ""; 
    
    window.location.href = baseUrl + "admin/rekapAbsensi?mode=laporan&start_date=" + start + "&end_date=" + end + "&user_id=" + user;
} 
/* =========================================
   28. LOGIKA LAPORAN ABSENSI (PRESET & EXPORT)
   ========================================= */

function setPeriod(type) {
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    
    if (!startDateInput || !endDateInput) return;

    const today = new Date();
    let start = new Date();
    let end = new Date();

    if (type === 'today') {
        // Hari Ini (Start & End = Today)
    } 
    else if (type === 'this_week') {
        const day = today.getDay(); 
        const diff = today.getDate() - day + (day === 0 ? -6 : 1); 
        start.setDate(diff);
        end.setDate(diff + 6);
    } 
    else if (type === 'this_month') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
        end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    } 
    else if (type === 'last_month') {
        start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        end = new Date(today.getFullYear(), today.getMonth(), 0);
    }

    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    startDateInput.value = formatDate(start);
    endDateInput.value = formatDate(end);

    // [PERBAIKAN] Panggil fungsi global
    if (typeof window.loadAbsensiGlobal === 'function') {
        window.loadAbsensiGlobal(1);
    } else {
        // Fallback jika error
        const form = document.getElementById('formLaporan');
        if (form) form.submit();
    }
}

function exportLaporan() {
    const form = document.getElementById('formLaporan');
    if(!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    const mainElem = document.querySelector('[data-base-url]');
    const baseUrl = mainElem ? mainElem.dataset.baseUrl : '';

    window.location.href = baseUrl + 'admin/exportAbsensi?' + params.toString();
}  
/* =========================================
   FUNGSI POPUP KETERANGAN ABSENSI
   ========================================= */
window.showDetailKeterangan = function(text) {
    // Decode HTML entities jika ada (agar karakter seperti &quot; kembali jadi tanda kutip)
    const txt = new DOMParser().parseFromString(text, "text/html").documentElement.textContent;
    
    Swal.fire({
        title: 'Keterangan Lengkap',
        text: txt,
        icon: 'info',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#152e4d',
        customClass: {
            popup: 'swal-wide' // Opsional: jika ingin popup agak lebar
        }
    });
};


