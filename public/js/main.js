document.addEventListener('DOMContentLoaded', function() {

    /* =========================================
       1. FITUR GLOBAL (Notifikasi)
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
       2. FITUR REKAP ABSENSI (ADMIN) - PAGINATION & SEARCH
       ========================================= */
    const searchInputAbsensi = document.getElementById('searchAbsensi');

    if (searchInputAbsensi) {
        const filterStatus = document.getElementById('filterStatus');
        const filterMonth = document.getElementById('filterMonth');
        const filterYear = document.getElementById('filterYear');
        const tableBody = document.getElementById('absensiTableBody');
        const paginationContainer = document.querySelector('.pagination-container');
        const baseUrl = searchInputAbsensi.dataset.baseUrl;

        // --- A. FUNGSI RENDER TOMBOL PAGINASI ---
        function renderPagination(totalPages, currentPage) {
            if (!paginationContainer) return;
            
            if (totalPages === 0) {
                paginationContainer.innerHTML = '';
                return;
            }

            let html = '<nav><ul class="pagination">';

            // Tombol Previous
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            html += `<li class="page-item ${prevDisabled}">
                        <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                     </li>`;

            // Logika Angka
            const safeTotalPages = totalPages < 1 ? 1 : totalPages;
            
            let start = Math.max(1, currentPage - 2);
            let end = Math.min(safeTotalPages, currentPage + 2);

            if (safeTotalPages > 5) {
                if (currentPage <= 3) { end = 5; }
                if (currentPage >= safeTotalPages - 2) { start = safeTotalPages - 4; }
            } else {
                start = 1; end = safeTotalPages;
            }

            for (let i = start; i <= end; i++) {
                const active = i === currentPage ? 'active' : '';
                html += `<li class="page-item ${active}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                         </li>`;
            }

            // Tombol Next
            const nextDisabled = currentPage === safeTotalPages ? 'disabled' : '';
            html += `<li class="page-item ${nextDisabled}">
                        <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                     </li>`;

            html += '</ul></nav>';
            paginationContainer.innerHTML = html;
        }

        // --- B. FUNGSI LOAD DATA UTAMA ---
        function loadAbsensi(page = 1) {
            if (typeof page !== 'number' || isNaN(page) || page < 1) page = 1;

            const params = new URLSearchParams({
                ajax: 1,
                page: page,
                search: searchInputAbsensi.value,
                status: filterStatus.value,
                month: filterMonth.value,
                year: filterYear.value
            });

            tableBody.style.opacity = '0.5'; // Efek loading

            fetch(`${baseUrl}admin/rekapAbsensi?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    
                    if (data.absensi.length === 0) {
                        html = '<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>';
                        renderPagination(0, 1); 
                    } else {
                        data.absensi.forEach(absen => {
                            let statusClass = 'status-gray';
                            if (absen.status === 'Hadir') statusClass = 'status-green';
                            else if (absen.status === 'Masih Bekerja') statusClass = 'status-green';
                            else if (absen.status === 'Sakit') statusClass = 'status-red';
                            else if (absen.status === 'Izin') statusClass = 'status-orange';

                            // Siapkan data untuk tombol edit
                            const safeKet = absen.keterangan ? absen.keterangan.replace(/'/g, "&#39;") : '';
                            
                            const btnEdit = `<button class="btn btn-warning btn-sm" 
                                onclick="editAbsenPopup('${absen.absen_id}', '${absen.nama_lengkap}', '${absen.waktu_masuk}', '${absen.waktu_pulang}', '${absen.status}', '${safeKet}')">
                                Edit
                            </button>`;
                            
                            let buktiHtml = absen.bukti_foto ? `<br><a href="${baseUrl}../public/uploads/bukti_absen/${absen.bukti_foto}" target="_blank" class="link-bukti">(Lihat Bukti)</a>` : '';

                            html += `<tr>
                                <td>${absen.tanggal}</td>
                                <td>${absen.nama_lengkap}</td>
                                <td>${absen.waktu_masuk}</td>
                                <td>${absen.waktu_pulang}</td>
                                <td>${absen.total_jam}</td>
                                <td><span class="${statusClass}">${absen.status}</span>${buktiHtml}</td>
                                <td class="no-print">${btnEdit}</td>
                            </tr>`;
                        });
                        renderPagination(data.totalPages, data.currentPage);
                    }
                    tableBody.innerHTML = html;
                    tableBody.style.opacity = '1';
                })
                .catch(err => {
                    console.error('Error:', err);
                    tableBody.style.opacity = '1';
                });
        }

        // --- EVENT LISTENERS ---
        searchInputAbsensi.addEventListener('input', () => loadAbsensi(1));
        filterStatus.addEventListener('change', () => loadAbsensi(1));
        filterMonth.addEventListener('change', () => loadAbsensi(1));
        filterYear.addEventListener('change', () => loadAbsensi(1));

        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('page-link')) {
                    e.preventDefault();
                    const parentLi = e.target.parentElement;
                    if (!parentLi.classList.contains('disabled') && !parentLi.classList.contains('active')) {
                        const targetPage = parseInt(e.target.getAttribute('data-page'));
                        loadAbsensi(targetPage);
                    }
                }
            });
        }

        loadAbsensi(1); 
    }

    /* =========================================
       3. FITUR RIWAYAT ABSENSI SAYA (PROFIL) - REALTIME
       ========================================= */
    const filterMonthProfile = document.getElementById('filterMonthProfile');
    
    if (filterMonthProfile) {
        const filterYearProfile = document.getElementById('filterYearProfile');
        const historyBody = document.getElementById('historyTableBody');
        const formHistory = document.getElementById('formHistory');
        const baseUrl = formHistory.dataset.baseUrl;
        const paginationContainer = document.querySelector('.pagination-container');

        // Fungsi Load Data Profil (Mirip Admin tapi lebih simpel)
        function loadMyHistory(page = 1) {
            const params = new URLSearchParams({
                ajax: 1,
                page: page,
                month: filterMonthProfile.value,
                year: filterYearProfile.value
            });

            historyBody.style.opacity = '0.5';

            fetch(`${baseUrl}profile/absensi?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    
                    if (data.absensi.length === 0) {
                        html = '<tr><td colspan="6" style="text-align:center;">Belum ada data absensi bulan ini.</td></tr>';
                    } else {
                        data.absensi.forEach(absen => {
                            let color = 'gray';
                            if(absen.display_status === 'Hadir' || absen.display_status === 'Masih Bekerja') color = 'green';
                            else if(absen.status_raw === 'Sakit') color = 'red';
                            else if(absen.status_raw === 'Izin') color = 'orange';

                            let colKet = absen.keterangan;
                            if (absen.bukti_foto) {
                                colKet = `<a href="${baseUrl}../public/uploads/bukti_absen/${absen.bukti_foto}" target="_blank" style="text-decoration: underline; color: blue;">Lihat Bukti</a>`;
                            }

                            html += `
                            <tr>
                                <td>${absen.tanggal}</td>
                                <td>${absen.waktu_masuk}</td>
                                <td>${absen.waktu_pulang}</td>
                                <td>${absen.total_jam}</td>
                                <td><span style="font-weight:bold; color:${color}">${absen.display_status}</span></td>
                                <td>${colKet}</td>
                            </tr>`;
                        });
                    }
                    historyBody.innerHTML = html;
                    historyBody.style.opacity = '1';

                    // Untuk pagination profil, kita bisa reuse fungsi renderPagination 
                    // TAPI karena fungsi itu ada di dalam blok IF admin, kita harus duplikat atau pindah.
                    // Untuk amannya, kita update DOM pagination manual saja disini.
                    updateProfilePagination(data.totalPages, data.currentPage);
                });
        }
        
        function updateProfilePagination(totalPages, currentPage) {
            if (!paginationContainer) return;
            if (totalPages === 0) { paginationContainer.innerHTML = ''; return; }

            let html = '<nav><ul class="pagination">';
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
            
            const safeTotalPages = totalPages < 1 ? 1 : totalPages;
            for (let i = 1; i <= safeTotalPages; i++) {
                const active = i === currentPage ? 'active' : '';
                html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            
            const nextDisabled = currentPage === safeTotalPages ? 'disabled' : '';
            html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
            html += '</ul></nav>';
            paginationContainer.innerHTML = html;
        }

        // Event Listeners Profil
        filterMonthProfile.addEventListener('change', () => loadMyHistory(1));
        filterYearProfile.addEventListener('change', () => loadMyHistory(1));
        
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('page-link')) {
                    e.preventDefault();
                    const p = e.target.parentElement;
                    if (!p.classList.contains('disabled') && !p.classList.contains('active')) {
                        loadMyHistory(parseInt(e.target.dataset.page));
                    }
                }
            });
        }
    }

    /* =========================================
       4. FITUR DOWNLOAD PDF
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

}); // <--- END DOM CONTENT LOADED

/* =========================================
   5. FUNGSI GLOBAL (MODAL & EDIT)
   ========================================= */

// A. Edit Absen Admin (REVISI DINAMIS)
function editAbsenPopup(id, nama, masuk, pulang, status, keterangan) {
    const template = document.getElementById('templateEditAbsenAdmin');
    if(!template) return;

    const searchElem = document.getElementById('searchAbsensi');
    const baseUrl = searchElem ? searchElem.dataset.baseUrl : '';

    Swal.fire({
        title: 'Edit Data Absensi',
        html: template.innerHTML,
        showCancelButton: true,
        confirmButtonText: 'Simpan Perubahan',
        didOpen: () => {
            const popup = Swal.getPopup();
            const form = popup.querySelector('#formEditAbsen');
            form.action = baseUrl + 'admin/updateAbsensiManual';

            popup.querySelector('#edit_absen_id').value = id;
            popup.querySelector('#edit_nama').value = nama;
            popup.querySelector('#edit_status').value = (status === 'Masih Bekerja') ? 'Hadir' : status;
            
            if(masuk !== '-' && masuk !== 'null') popup.querySelector('#edit_masuk').value = masuk;
            if(pulang !== '-' && pulang !== 'null') popup.querySelector('#edit_pulang').value = pulang;
            if(keterangan && keterangan !== 'null') popup.querySelector('#edit_keterangan').value = keterangan;

            // LOGIKA TOGGLE
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
            document.getElementById('formEditAbsen').submit();
        }
    });
}

// B. Modal Izin Staff (Tetap)
function showIzinModal(actionUrl) {
    const template = document.getElementById('templateModalIzin');
    if(!template) return;
    Swal.fire({
        title: 'Form Ketidakhadiran',
        html: template.innerHTML,
        showCancelButton: true,
        confirmButtonText: 'Kirim',
        didOpen: () => { Swal.getPopup().querySelector('#formIzin').action = actionUrl; },
        preConfirm: () => { document.getElementById('formIzin').submit(); }
    });
}