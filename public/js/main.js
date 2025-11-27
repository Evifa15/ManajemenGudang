document.addEventListener('DOMContentLoaded', function() {

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
       2. FITUR MANAJEMEN PENGGUNA (ADMIN)
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
        const paginationContainerUsers = document.querySelector('.pagination-container');

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
                        html = '<tr><td colspan="5" style="text-align:center;">Data tidak ditemukan.</td></tr>';
                    } else {
                        data.users.forEach(user => {
                            const checkboxHtml = (user.user_id != currentUserId) 
                                ? `<input type="checkbox" class="user-checkbox" value="${user.user_id}" style="transform: scale(1.2); cursor: pointer;">` : '';
                            const deleteBtnHtml = (user.user_id != currentUserId)
                                ? `<button type="button" class="btn btn-danger btn-sm btn-delete" data-url="${baseUrl}admin/deleteUser/${user.user_id}">Hapus</button>` : '';

                            html += `<tr><td style="text-align: center;">${checkboxHtml}</td><td>${user.nama_lengkap}</td><td>${user.email}</td><td><span style="text-transform: capitalize; font-weight: bold;">${user.role}</span></td><td><a href="${baseUrl}admin/editUser/${user.user_id}" class="btn btn-warning btn-sm">Edit</a> ${deleteBtnHtml}</td></tr>`;
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

        const btnImport = document.querySelector('.btn-import-users');
        if (btnImport) {
            btnImport.addEventListener('click', function() {
                const url = this.dataset.url;
                Swal.fire({
                    title: 'Import Data Pengguna',
                    html: `<form id="formImport" action="${url}" method="POST" enctype="multipart/form-data"><p style="font-size:0.9em; color:#666; margin-bottom:15px;">Upload file CSV dengan urutan kolom:<br><b>Nama Lengkap, Email, Password, Role</b></p><input type="file" name="csv_file" class="swal2-file" accept=".csv" required></form>`,
                    showCancelButton: true, confirmButtonText: 'Upload & Import',
                    preConfirm: () => {
                        const form = document.getElementById('formImport');
                        if (!form.querySelector('input[type="file"]').files[0]) { Swal.showValidationMessage('Silakan pilih file CSV dulu!'); return false; }
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
       4. FITUR REKAP ABSENSI (ADMIN)
       ========================================= */
    const searchInputAbsensi = document.getElementById('searchAbsensi');
    if (searchInputAbsensi) {
        const filterStatus = document.getElementById('filterStatus');
        const filterMonth = document.getElementById('filterMonth');
        const filterYear = document.getElementById('filterYear');
        const tableBody = document.getElementById('absensiTableBody');
        const paginationContainer = document.querySelector('.pagination-container');
        const baseUrl = searchInputAbsensi.dataset.baseUrl;

        function loadAbsensi(page = 1) {
            if (typeof page !== 'number' || isNaN(page) || page < 1) page = 1;
            let monthVal = filterMonth.value;
            let yearVal = filterYear.value;
            if (searchInputAbsensi.value.trim() !== '' || filterStatus.value !== '') {
                monthVal = ''; yearVal = '';
            }
            const params = new URLSearchParams({
                ajax: 1, page: page, search: searchInputAbsensi.value,
                status: filterStatus.value, month: monthVal, year: yearVal
            });

            if(tableBody) tableBody.style.opacity = '0.5';
            fetch(`${baseUrl}admin/rekapAbsensi?${params.toString()}`).then(res => res.json()).then(data => {
                let html = '';
                if (data.absensi.length === 0) {
                    html = '<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>';
                } else {
                    data.absensi.forEach(absen => {
                        let statusClass = 'status-gray';
                        if (absen.status === 'Hadir') statusClass = 'status-green';
                        else if (absen.status === 'Masih Bekerja') statusClass = 'status-green';
                        else if (absen.status === 'Sakit') statusClass = 'status-red';
                        else if (absen.status === 'Izin') statusClass = 'status-orange';
                        const safeKet = absen.keterangan ? absen.keterangan.replace(/'/g, "&#39;") : '';
                        const btnEdit = `<button class="btn btn-warning btn-sm" onclick="editAbsenPopup('${absen.absen_id}', '${absen.nama_lengkap}', '${absen.waktu_masuk}', '${absen.waktu_pulang}', '${absen.status}', '${safeKet}')">Edit</button>`;
                        let buktiHtml = absen.bukti_foto ? `<br><a href="${baseUrl}../public/uploads/bukti_absen/${absen.bukti_foto}" target="_blank" class="link-bukti">(Lihat Bukti)</a>` : '';
                        html += `<tr><td>${absen.tanggal}</td><td>${absen.nama_lengkap}</td><td>${absen.waktu_masuk}</td><td>${absen.waktu_pulang}</td><td>${absen.total_jam}</td><td><span class="${statusClass}">${absen.status}</span>${buktiHtml}</td><td class="no-print">${btnEdit}</td></tr>`;
                    });
                }
                if(tableBody) { tableBody.innerHTML = html; tableBody.style.opacity = '1'; }
                renderPaginationUniversal(paginationContainer, data.totalPages, data.currentPage, (p) => loadAbsensi(p));
            });
        }
        searchInputAbsensi.addEventListener('input', () => loadAbsensi(1));
        filterStatus.addEventListener('change', () => loadAbsensi(1));
        filterMonth.addEventListener('change', () => loadAbsensi(1));
        filterYear.addEventListener('change', () => loadAbsensi(1));
        
        if(paginationContainer){ paginationContainer.addEventListener('click', (e) => { if(e.target.classList.contains('page-link')){ e.preventDefault(); const p = e.target.parentElement; if(!p.classList.contains('disabled') && !p.classList.contains('active')) loadAbsensi(parseInt(e.target.dataset.page)); } }); }
    }

    /* =========================================
       5. FITUR KONFIGURASI DATA MASTER (ADMIN)
       ========================================= */
    const masterDataPage = document.getElementById('masterDataPage');
    if (masterDataPage) {
        const tabLinks = document.querySelectorAll('.tab-nav-link');
        const tabPanes = document.querySelectorAll('.tab-pane');

        function activateTab(hash) {
            tabLinks.forEach(l => l.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));

            const activeLink = document.querySelector(`.tab-nav-link[href="${hash}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
                const targetId = hash.replace('#tab-', '#view-');
                const targetPane = document.querySelector(targetId);
                if (targetPane) targetPane.classList.add('active');
            } else {
                if(tabLinks.length > 0) {
                    tabLinks[0].classList.add('active');
                    tabPanes[0].classList.add('active');
                }
            }
            document.querySelector('.app-content').scrollTop = 0;
        }

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

        document.querySelectorAll('.select-all-tab').forEach(selectAll => {
            selectAll.addEventListener('change', function() {
                const table = this.closest('table');
                const checkboxes = table.querySelectorAll('.row-checkbox-tab');
                checkboxes.forEach(cb => {
                    if (cb.closest('tr').style.display !== 'none') {
                        cb.checked = this.checked;
                    }
                });
                toggleBulkBtn(this);
            });
        });

        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.addEventListener('change', function(e) {
                if(e.target.classList.contains('row-checkbox-tab')) {
                    toggleBulkBtn(e.target);
                }
            });
        });

        function toggleBulkBtn(element) {
            const tabPane = element.closest('.tab-pane');
            const btn = tabPane.querySelector('.btn-bulk-tab');
            const checked = tabPane.querySelectorAll('.row-checkbox-tab:checked').length;
            if(checked > 0) {
                btn.style.display = 'inline-block';
                btn.textContent = `Hapus Terpilih (${checked})`;
            } else {
                btn.style.display = 'none';
            }
        }

        document.querySelectorAll('.btn-bulk-tab').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabPane = this.closest('.tab-pane');
                const checked = tabPane.querySelectorAll('.row-checkbox-tab:checked');
                const ids = Array.from(checked).map(cb => cb.value);
                const url = this.getAttribute('data-url');

                handleBulkDelete(ids, url, () => location.reload());
            });
        });

        document.querySelectorAll('.search-tab-input').forEach(input => {
            input.addEventListener('input', function() {
                const searchText = this.value.toLowerCase();
                const tabPane = this.closest('.tab-pane');
                const tableRows = tabPane.querySelectorAll('.data-table tbody tr');

                tableRows.forEach(row => {
                    let textContent = "";
                    row.querySelectorAll('.searchable').forEach(col => {
                        textContent += col.textContent.toLowerCase() + " ";
                    });
                    if (textContent.includes(searchText)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                        const checkbox = row.querySelector('.row-checkbox-tab');
                        if(checkbox) checkbox.checked = false; 
                    }
                });
                toggleBulkBtn(this);
            });
        });
    }

    /* =========================================
       6. FITUR PROFIL (HISTORY)
       ========================================= */
    const filterMonthProfile = document.getElementById('filterMonthProfile');
    if (filterMonthProfile) {
        const filterYearProfile = document.getElementById('filterYearProfile');
        const historyBody = document.getElementById('historyTableBody');
        const formHistory = document.getElementById('formHistory');
        const baseUrl = formHistory.dataset.baseUrl;
        function loadMyHistory(page = 1) {
            const params = new URLSearchParams({ ajax: 1, page: page, month: filterMonthProfile.value, year: filterYearProfile.value });
            if(historyBody) historyBody.style.opacity = '0.5';
            fetch(`${baseUrl}profile/absensi?${params.toString()}`).then(res => res.json()).then(data => {
                let html = '';
                if (data.absensi.length === 0) { html = '<tr><td colspan="6" style="text-align:center;">Belum ada data absensi bulan ini.</td></tr>'; } 
                else {
                    data.absensi.forEach(absen => {
                        let color = 'gray';
                        if(absen.display_status === 'Hadir' || absen.display_status === 'Masih Bekerja') color = 'green';
                        else if(absen.status_raw === 'Sakit') color = 'red';
                        else if(absen.status_raw === 'Izin') color = 'orange';
                        let colKet = absen.keterangan;
                        if (absen.bukti_foto) { colKet = `<a href="${baseUrl}../public/uploads/bukti_absen/${absen.bukti_foto}" target="_blank" style="text-decoration: underline; color: blue;">Lihat Bukti</a>`; }
                        html += `<tr><td>${absen.tanggal}</td><td>${absen.waktu_masuk}</td><td>${absen.waktu_pulang}</td><td>${absen.total_jam}</td><td><span style="font-weight:bold; color:${color}">${absen.display_status}</span></td><td>${colKet}</td></tr>`;
                    });
                }
                if(historyBody) { historyBody.innerHTML = html; historyBody.style.opacity = '1'; }
            });
        }
        filterMonthProfile.addEventListener('change', () => loadMyHistory(1));
        filterYearProfile.addEventListener('change', () => loadMyHistory(1));
    }

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
       8. FITUR GLOBAL DELETE (Universal)
       ========================================= */
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete')) {
            e.preventDefault(); 
            const deleteUrl = e.target.getAttribute('data-url');
            if (!deleteUrl) return;
            Swal.fire({
                title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = deleteUrl;
            });
        }
    });

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

}); // END DOMContentLoaded


/* =========================================
   9. FUNGSI GLOBAL (POPUP / MODAL / HELPER)
   ========================================= */

function handleBulkDelete(ids, url, onSuccessCallback) {
    if (ids.length === 0) return;
    Swal.fire({
        title: `Hapus ${ids.length} Data?`, text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({title: 'Memproses...', didOpen: () => Swal.showLoading()});
            fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ids: ids})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => onSuccessCallback());
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
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
        title: 'Edit Data Absensi', html: template.innerHTML, showCancelButton: true, confirmButtonText: 'Simpan Perubahan',
        didOpen: () => {
            const popup = Swal.getPopup();
            popup.querySelector('#formEditAbsen').action = baseUrl + 'admin/updateAbsensiManual';
            popup.querySelector('#edit_absen_id').value = id;
            popup.querySelector('#edit_nama').value = nama;
            popup.querySelector('#edit_status').value = (status === 'Masih Bekerja') ? 'Hadir' : status;
            if(masuk && masuk !== '-' && masuk !== 'null') popup.querySelector('#edit_masuk').value = masuk;
            if(pulang && pulang !== '-' && pulang !== 'null') popup.querySelector('#edit_pulang').value = pulang;
            if(keterangan && keterangan !== 'null') popup.querySelector('#edit_keterangan').value = keterangan;
            const selectStatus = popup.querySelector('#edit_status');
            const rowJam = popup.querySelector('#row_jam');
            const rowKet = popup.querySelector('#row_keterangan');
            function toggleForm() {
                if (selectStatus.value === 'Hadir') { rowJam.style.display = 'flex'; rowKet.style.display = 'none'; } 
                else { rowJam.style.display = 'none'; rowKet.style.display = 'block'; }
            }
            toggleForm();
            selectStatus.addEventListener('change', toggleForm);
        },
        preConfirm: () => { document.getElementById('formEditAbsen').submit(); }
    });
}

function showIzinModal(actionUrl) {
    const template = document.getElementById('templateModalIzin');
    if(!template) return;
    Swal.fire({
        title: 'Form Ketidakhadiran', html: template.innerHTML, showCancelButton: true, confirmButtonText: 'Kirim',
        didOpen: () => { Swal.getPopup().querySelector('#formIzin').action = actionUrl; },
        preConfirm: () => { document.getElementById('formIzin').submit(); }
    });
}

// Helper Paginasi Universal
function renderPaginationUniversal(container, totalPages, currentPage, callbackFunction) {
     if (!container) return;
     currentPage = parseInt(currentPage);
     totalPages = parseInt(totalPages);
     
     if (totalPages <= 1) { container.innerHTML = ''; return; }
     
     let html = '<nav><ul class="pagination">';
     const prevDisabled = currentPage === 1 ? 'disabled' : '';
     html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
     
     let start = Math.max(1, currentPage - 2);
     let end = Math.min(totalPages, currentPage + 2);
     for (let i = start; i <= end; i++) {
         const active = i === currentPage ? 'active' : '';
         html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
     }
     
     const nextDisabled = currentPage === totalPages ? 'disabled' : '';
     html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
     html += '</ul></nav>';
     
     container.innerHTML = html;

     const links = container.querySelectorAll('.page-link');
     links.forEach(link => {
         link.addEventListener('click', (e) => {
             e.preventDefault();
             const li = e.target.parentElement;
             if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                 callbackFunction(parseInt(e.target.dataset.page));
             }
         });
     });
}

/* =========================================
       10. FITUR RIWAYAT MASUK (ADMIN) - [LIVE SEARCH]
       ========================================= */
    const liveSearchMasuk = document.getElementById('liveSearchMasuk');
    if (liveSearchMasuk) {
        const tableBodyMasuk = document.getElementById('tableBodyMasuk');
        const paginationContainerMasuk = document.getElementById('paginationContainerMasuk');
        const baseUrl = liveSearchMasuk.dataset.baseUrl;

        function loadRiwayatMasuk(page = 1) {
            const params = new URLSearchParams({
                ajax: 1,
                page: page,
                search: liveSearchMasuk.value
            });

            if(tableBodyMasuk) tableBodyMasuk.style.opacity = '0.5';

            fetch(`${baseUrl}admin/riwayatBarangMasuk?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyMasuk.innerHTML = data.html;
                    tableBodyMasuk.style.opacity = '1';
                    renderPaginationUniversal(paginationContainerMasuk, data.totalPages, data.currentPage, loadRiwayatMasuk);
                })
                .catch(err => {
                    console.error(err);
                    if(tableBodyMasuk) tableBodyMasuk.style.opacity = '1';
                });
        }

        liveSearchMasuk.addEventListener('input', () => loadRiwayatMasuk(1));

        // Helper Paginasi Universal (Agar tidak copas kode pagination terus)
        function renderPaginationUniversal(container, totalPages, currentPage, callbackFunction) {
             if (!container) return;
             currentPage = parseInt(currentPage);
             totalPages = parseInt(totalPages);
             
             if (totalPages <= 1) { container.innerHTML = ''; return; }
             
             let html = '<nav><ul class="pagination">';
             const prevDisabled = currentPage === 1 ? 'disabled' : '';
             html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
             
             let start = Math.max(1, currentPage - 2);
             let end = Math.min(totalPages, currentPage + 2);
             for (let i = start; i <= end; i++) {
                 const active = i === currentPage ? 'active' : '';
                 html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
             }
             
             const nextDisabled = currentPage === totalPages ? 'disabled' : '';
             html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
             html += '</ul></nav>';
             
             container.innerHTML = html;

             // Re-attach event listener untuk pagination yang baru dibuat
             const links = container.querySelectorAll('.page-link');
             links.forEach(link => {
                 link.addEventListener('click', (e) => {
                     e.preventDefault();
                     const li = e.target.parentElement;
                     if (!li.classList.contains('disabled') && !li.classList.contains('active')) {
                         callbackFunction(parseInt(e.target.dataset.page));
                     }
                 });
             });
        }
        
        // Attach event listener awal untuk paginasi PHP (Server Side Rendered)
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
       11. FITUR RIWAYAT KELUAR (ADMIN) - [LIVE SEARCH]
       ========================================= */
    const liveSearchKeluar = document.getElementById('liveSearchKeluar');
    if (liveSearchKeluar) {
        const tableBodyKeluar = document.getElementById('tableBodyKeluar');
        const paginationContainerKeluar = document.getElementById('paginationContainerKeluar');
        const baseUrl = liveSearchKeluar.dataset.baseUrl;

        function loadRiwayatKeluar(page = 1) {
            const params = new URLSearchParams({
                ajax: 1,
                page: page,
                search: liveSearchKeluar.value
            });

            if(tableBodyKeluar) tableBodyKeluar.style.opacity = '0.5';

            fetch(`${baseUrl}admin/riwayatBarangKeluar?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyKeluar.innerHTML = data.html;
                    tableBodyKeluar.style.opacity = '1';
                    // Kita gunakan helper universal yang sudah kita buat di langkah sebelumnya
                    renderPaginationUniversal(paginationContainerKeluar, data.totalPages, data.currentPage, loadRiwayatKeluar);
                })
                .catch(err => {
                    console.error(err);
                    if(tableBodyKeluar) tableBodyKeluar.style.opacity = '1';
                });
        }

        liveSearchKeluar.addEventListener('input', () => loadRiwayatKeluar(1));

        // Attach listener awal untuk paginasi PHP
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
       12. FITUR RIWAYAT RETUR (ADMIN) - [LIVE SEARCH]
       ========================================= */
    const liveSearchRetur = document.getElementById('liveSearchRetur');
    if (liveSearchRetur) {
        const tableBodyRetur = document.getElementById('tableBodyRetur');
        const paginationContainerRetur = document.getElementById('paginationContainerRetur');
        const baseUrl = liveSearchRetur.dataset.baseUrl;

        function loadRiwayatRetur(page = 1) {
            const params = new URLSearchParams({
                ajax: 1,
                page: page,
                search: liveSearchRetur.value
            });

            if(tableBodyRetur) tableBodyRetur.style.opacity = '0.5';

            fetch(`${baseUrl}admin/riwayatReturRusak?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyRetur.innerHTML = data.html;
                    tableBodyRetur.style.opacity = '1';
                    // Gunakan helper universal lagi
                    renderPaginationUniversal(paginationContainerRetur, data.totalPages, data.currentPage, loadRiwayatRetur);
                })
                .catch(err => {
                    console.error(err);
                    if(tableBodyRetur) tableBodyRetur.style.opacity = '1';
                });
        }

        liveSearchRetur.addEventListener('input', () => loadRiwayatRetur(1));

        // Attach listener awal untuk paginasi PHP
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
       13. FITUR RIWAYAT PEMINJAMAN (ADMIN) - [LIVE SEARCH]
       ========================================= */
    const liveSearchPeminjaman = document.getElementById('liveSearchPeminjaman');
    if (liveSearchPeminjaman) {
        const tableBodyPeminjaman = document.getElementById('tableBodyPeminjaman');
        const paginationContainerPeminjaman = document.getElementById('paginationContainerPeminjaman');
        const filterStatusPeminjaman = document.getElementById('filterStatusPeminjaman');
        const baseUrl = liveSearchPeminjaman.dataset.baseUrl;

        function loadRiwayatPeminjaman(page = 1) {
            const params = new URLSearchParams({
                ajax: 1,
                page: page,
                search: liveSearchPeminjaman.value,
                status: filterStatusPeminjaman.value
            });

            if(tableBodyPeminjaman) tableBodyPeminjaman.style.opacity = '0.5';

            fetch(`${baseUrl}admin/riwayatPeminjaman?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    tableBodyPeminjaman.innerHTML = data.html;
                    tableBodyPeminjaman.style.opacity = '1';
                    renderPaginationUniversal(paginationContainerPeminjaman, data.totalPages, data.currentPage, loadRiwayatPeminjaman);
                })
                .catch(err => {
                    console.error(err);
                    if(tableBodyPeminjaman) tableBodyPeminjaman.style.opacity = '1';
                });
        }

        liveSearchPeminjaman.addEventListener('input', () => loadRiwayatPeminjaman(1));
        filterStatusPeminjaman.addEventListener('change', () => loadRiwayatPeminjaman(1));

        // Attach listener awal untuk paginasi PHP
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