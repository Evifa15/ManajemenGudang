document.addEventListener('DOMContentLoaded', function() {

    // 1. Notifikasi Flash
    const alertBox = document.getElementById('loginAlert') || document.querySelector('.flash-message');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => { alertBox.remove(); }, 500);
        }, 3000);
    }

    // 2. Fitur Rekap Absensi
    const searchInputAbsensi = document.getElementById('searchAbsensi');

    if (searchInputAbsensi) {
        const filterStatus = document.getElementById('filterStatus');
        const filterMonth = document.getElementById('filterMonth');
        const filterYear = document.getElementById('filterYear');
        const tableBody = document.getElementById('absensiTableBody');
        const paginationContainer = document.querySelector('.pagination-container');
        const baseUrl = searchInputAbsensi.dataset.baseUrl;

        // Render Pagination
        function renderPagination(totalPages, currentPage) {
            if (!paginationContainer) return;
            if (totalPages === 0) {
                paginationContainer.innerHTML = '';
                return;
            }
            
            let html = '<nav><ul class="pagination">';
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;

            const safeTotalPages = totalPages < 1 ? 1 : totalPages;
            let start = Math.max(1, currentPage - 2);
            let end = Math.min(safeTotalPages, currentPage + 2);

            if (safeTotalPages > 5) {
                if (currentPage <= 3) { end = 5; }
                if (currentPage >= safeTotalPages - 2) { start = safeTotalPages - 4; }
            } else { start = 1; end = safeTotalPages; }

            for (let i = start; i <= end; i++) {
                const active = i === currentPage ? 'active' : '';
                html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }

            const nextDisabled = currentPage === safeTotalPages ? 'disabled' : '';
            html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
            html += '</ul></nav>';
            paginationContainer.innerHTML = html;
        }

        // Load Data
        function loadAbsensi(page = 1) {
            if (typeof page !== 'number' || isNaN(page) || page < 1) page = 1;
            const params = new URLSearchParams({
                ajax: 1, page: page, search: searchInputAbsensi.value,
                status: filterStatus.value, month: filterMonth.value, year: filterYear.value
            });

            tableBody.style.opacity = '0.5';

            fetch(`${baseUrl}admin/rekapAbsensi?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    if (data.absensi.length === 0) {
                        html = '<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>';
                        renderPagination(0, 1);
                    } else {
                        data.absensi.forEach(absen => {
                            let statusClass = 'status-gray';
                            // Normalisasi status untuk logika warna
                            let displayStatus = absen.status;
                            if(absen.status === 'Hadir' || absen.status === 'Masih Bekerja') statusClass = 'status-green';
                            else if(absen.status === 'Sakit') statusClass = 'status-red';
                            else if(absen.status === 'Izin') statusClass = 'status-orange';

                            // Siapkan data untuk tombol edit (escape string jika perlu)
                            // Kita kirim status dan keterangan juga sekarang
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
                                <td><span class="${statusClass}">${displayStatus}</span>${buktiHtml}</td>
                                <td class="no-print">${btnEdit}</td>
                            </tr>`;
                        });
                        renderPagination(data.totalPages, data.currentPage);
                    }
                    tableBody.innerHTML = html;
                    tableBody.style.opacity = '1';
                });
        }

        // Event Listeners
        searchInputAbsensi.addEventListener('input', () => loadAbsensi(1));
        filterStatus.addEventListener('change', () => loadAbsensi(1));
        filterMonth.addEventListener('change', () => loadAbsensi(1));
        filterYear.addEventListener('change', () => loadAbsensi(1));
        if(paginationContainer){
            paginationContainer.addEventListener('click', (e) => {
                if(e.target.classList.contains('page-link')){
                    e.preventDefault();
                    const p = e.target.parentElement;
                    if(!p.classList.contains('disabled') && !p.classList.contains('active')) loadAbsensi(parseInt(e.target.dataset.page));
                }
            });
        }
        loadAbsensi(1);
    }

    // 3. Fitur PDF (Sama seperti sebelumnya)
    const btnExport = document.getElementById('btnExportPdf');
    if (btnExport) {
        btnExport.addEventListener('click', function() {
            const element = document.getElementById('areaPrintAbsensi');
            element.classList.add('printing-mode');
            const opt = { margin: 10, filename: 'Laporan_Absensi.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' } };
            html2pdf().set(opt).from(element).save().then(() => element.classList.remove('printing-mode')).catch(() => element.classList.remove('printing-mode'));
        });
    }
});

/* =========================================
   FUNGSI GLOBAL
   ========================================= */

// Edit Absen Admin (REVISI DINAMIS)
function editAbsenPopup(id, nama, masuk, pulang, status, keterangan) {
    const template = document.getElementById('templateEditAbsenAdmin');
    if(!template) return;

    // Ambil Base URL dari elemen search
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

            // Isi Data Awal
            popup.querySelector('#edit_absen_id').value = id;
            popup.querySelector('#edit_nama').value = nama;
            popup.querySelector('#edit_status').value = (status === 'Masih Bekerja') ? 'Hadir' : status;
            
            if(masuk !== '-' && masuk !== 'null') popup.querySelector('#edit_masuk').value = masuk;
            if(pulang !== '-' && pulang !== 'null') popup.querySelector('#edit_pulang').value = pulang;
            if(keterangan && keterangan !== 'null') popup.querySelector('#edit_keterangan').value = keterangan;

            // LOGIKA TOGGLE TAMPILAN BERDASARKAN STATUS
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

            // Jalankan toggle saat pertama buka
            toggleForm();
            // Jalankan toggle saat status berubah
            selectStatus.addEventListener('change', toggleForm);
        },
        preConfirm: () => {
            document.getElementById('formEditAbsen').submit();
        }
    });
}

// Modal Izin Staff (Tetap)
function showIzinModal(actionUrl) {
    // ... (Sama seperti kode sebelumnya, tidak berubah)
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