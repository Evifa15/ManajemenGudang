/**
 * --------------------------------------------------------------------------
 * Attendance Module
 * Menangani Rekap Absensi (Admin), Riwayat (Profil), dan Input Izin (Dashboard).
 * --------------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Inisialisasi Halaman Rekap Absensi (Admin)
    if (document.getElementById('searchAbsensi')) {
        initAdminAttendance();
    }

    // 2. Inisialisasi Halaman Riwayat Saya (Profil)
    if (document.getElementById('startDateProfile')) {
        initProfileHistory();
    }
});

/**
 * ============================================================================
 * A. LOGIKA INPUT IZIN / SAKIT (DASHBOARD)
 * ============================================================================
 */
// Fungsi ini dipanggil via onclick di tombol Dashboard
window.showIzinModal = function(actionUrl) {
    const template = document.getElementById('templateModalIzin');
    if (!template) {
        console.error('Template modal izin tidak ditemukan di DOM.');
        return;
    }

    Swal.fire({
        title: 'Form Izin / Sakit',
        html: template.innerHTML, // Ambil konten dari template tersembunyi
        showCancelButton: true,
        confirmButtonText: 'Kirim Laporan',
        confirmButtonColor: '#152e4d',
        cancelButtonText: 'Batal',
        width: '600px',
        didOpen: () => {
            const popup = Swal.getPopup();
            const form = popup.querySelector('form');
            const fileInput = popup.querySelector('input[type="file"]');
            const labelFile = popup.querySelector('#label_file_izin span');
            
            // 1. Set URL Action Form
            form.action = actionUrl;

            // 2. Fitur UX: Ganti teks saat file dipilih
            if (fileInput && labelFile) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        labelFile.textContent = this.files[0].name;
                        labelFile.style.fontWeight = 'bold';
                        labelFile.style.color = '#152e4d';
                    } else {
                        labelFile.textContent = 'Klik atau Seret File ke Sini';
                    }
                });
            }
        },
        preConfirm: () => {
            const popup = Swal.getPopup();
            const form = popup.querySelector('form');
            const ket = popup.querySelector('textarea[name="keterangan"]').value;
            
            if (!ket.trim()) {
                Swal.showValidationMessage('Keterangan wajib diisi!');
                return false;
            }
            
            // Submit form secara manual
            form.submit();
        }
    });
};


/**
 * ============================================================================
 * B. LOGIKA ADMIN (admin/rekapAbsensi)
 * ============================================================================
 */
function initAdminAttendance() {
    const searchInput = document.getElementById('searchAbsensi');
    const tableBody = document.getElementById('absensiTableBody');
    const paginationContainer = document.querySelector('.pagination-container');
    const baseUrl = searchInput.dataset.baseUrl;

    // Filters
    const filterRole = document.getElementById('filterRole');
    const filterStatus = document.getElementById('filterStatus');
    
    // Date Filters (Laporan Mode)
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const filterRoleLaporan = document.getElementById('filterRoleLaporan');

    // Date Picker (Harian Mode)
    const datePickerNative = document.getElementById('datePickerNative');

    // --- Fungsi Utama Load Data ---
    window.loadAbsensiGlobal = function(page = 1) {
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode') || 'harian';

        let params = new URLSearchParams({
            ajax: 1,
            page: page,
            mode: mode,
            search: searchInput.value
        });

        if (mode === 'harian') {
            if (filterRole) params.append('role', filterRole.value);
            if (filterStatus) params.append('status', filterStatus.value);
            if (datePickerNative) params.append('date', datePickerNative.value);
        } else {
            if (startDate) params.append('start_date', startDate.value);
            if (endDate) params.append('end_date', endDate.value);
            if (filterRoleLaporan) params.append('role', filterRoleLaporan.value);
        }

        if(tableBody) tableBody.style.opacity = '0.5';

        fetch(`${baseUrl}admin/rekapAbsensi?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (data.absensi.length === 0) {
                    html = '<tr><td colspan="9" style="text-align:center; padding:30px; color:#999;">Data tidak ditemukan.</td></tr>';
                } else {
                    data.absensi.forEach(absen => {
                        html += renderRow(absen, mode, baseUrl);
                    });
                }

                if(tableBody) {
                    tableBody.innerHTML = html;
                    tableBody.style.opacity = '1';
                }

                if (window.WMSUI && paginationContainer) {
                    window.WMSUI.renderPagination(paginationContainer, data.totalPages, data.currentPage, (p) => window.loadAbsensiGlobal(p));
                }
            })
            .catch(err => {
                console.error(err);
                if(tableBody) tableBody.style.opacity = '1';
            });
    };

    function renderRow(absen, mode, baseUrl) {
        const roleStyle = getRoleBadgeStyle(absen.role);
        let statusData = getStatusStyle(absen.status, absen.waktu_pulang, absen.tanggal);

        let actionColumn = '';
        let buktiColumn = '<span style="color: #cbd5e1;">-</span>';

        if (absen.bukti_foto) {
            buktiColumn = `<a href="${baseUrl}uploads/bukti_absen/${absen.bukti_foto}" target="_blank" class="btn-link-file"><i class="ph ph-file-text"></i> Lihat File</a>`;
        }

        if (mode === 'laporan') {
            let ketHtml = '<span style="color:#ccc;">-</span>';
            if (absen.keterangan && absen.keterangan.trim() !== '-' && absen.keterangan !== 'null') {
                const rawKet = escapeHtml(absen.keterangan);
                ketHtml = `<button type="button" class="btn btn-sm btn-info" onclick="showDetailKeterangan('${rawKet}')"><i class="ph ph-eye"></i> Lihat</button>`;
            }
            actionColumn = `<td style="text-align:center;">${ketHtml}</td>`;
        } else {
            const safeNama = escapeHtml(absen.nama_lengkap);
            const safeKet = escapeHtml(absen.keterangan || '');
            const jamMasuk = absen.waktu_masuk ? absen.waktu_masuk.substring(0,5) : '';
            const jamPulang = absen.waktu_pulang ? absen.waktu_pulang.substring(0,5) : '';

            const btnEdit = `<button type="button" class="btn-icon edit" onclick="openEditModal('${absen.absen_id}', '${safeNama}', '${jamMasuk}', '${jamPulang}', '${absen.status}', '${safeKet}')">
                                <i class="ph ph-pencil-simple"></i>
                             </button>`;
            actionColumn = `<td class="no-print" style="text-align: center;">${btnEdit}</td>`;
        }

        return `
        <tr style="border-bottom: 1px solid #f1f5f9;">
            <td style="padding: 15px;">${absen.tanggal}</td>
            <td style="padding: 15px;"><strong>${absen.nama_lengkap}</strong></td>
            <td style="padding: 15px;"><span class="badge-role" style="${roleStyle}">${absen.role}</span></td>
            <td style="padding: 15px;">${absen.waktu_masuk || '-'}</td>
            <td style="padding: 15px;">${absen.waktu_pulang || '-'}</td>
            <td style="padding: 15px;">${absen.total_jam || '-'}</td>
            <td style="padding: 15px;"><span class="badge-status" style="${statusData.style}">${statusData.text}</span></td>
            ${mode === 'harian' ? `<td style="padding: 15px;">${buktiColumn}</td>` : ''}
            ${actionColumn}
        </tr>`;
    }

    searchInput.addEventListener('input', () => window.loadAbsensiGlobal(1));
    [filterRole, filterStatus, startDate, endDate, filterRoleLaporan].forEach(el => {
        if(el) el.addEventListener('change', () => window.loadAbsensiGlobal(1));
    });

    // Navigasi Tanggal
    window.navChangeDate = (days) => {
        if(!datePickerNative) return;
        const current = new Date(datePickerNative.value);
        current.setDate(current.getDate() + days);
        datePickerNative.value = current.toISOString().split('T')[0];
        datePickerNative.dispatchEvent(new Event('change'));
        window.location.href = `?mode=harian&date=${datePickerNative.value}`;
    };

    window.navGoToDate = (val) => {
        window.location.href = `?mode=harian&date=${val}`;
    };
    
    window.navShowPicker = () => {
        if(datePickerNative) datePickerNative.showPicker();
    };
    
    // --- Export Dropdown Logic (Updated Style) ---
    const btnToggleExport = document.getElementById('btnToggleExportAbsensi');
    const menuExport = document.getElementById('exportMenuAbsensi');
    
    if (btnToggleExport && menuExport) {
        // Toggle Menu
        btnToggleExport.addEventListener('click', (e) => {
            e.stopPropagation();
            // Class 'show' akan mentrigger display block pada .dropdown-menu-custom
            menuExport.classList.toggle('show');
        });

        // Tutup jika klik di luar
        window.addEventListener('click', (e) => {
            if (!btnToggleExport.contains(e.target) && !menuExport.contains(e.target)) {
                menuExport.classList.remove('show');
            }
        });

        // Handle Klik Item
        document.querySelectorAll('.btn-export-absensi-action').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                menuExport.classList.remove('show'); // Tutup menu
                
                const type = this.dataset.type;
                const params = new URLSearchParams({
                    search: searchInput.value,
                    role: filterRoleLaporan ? filterRoleLaporan.value : '',
                    start_date: startDate ? startDate.value : '',
                    end_date: endDate ? endDate.value : ''
                });

                if (type === 'pdf') {
                    // Logika PDF Client-side (HTML2PDF)
                    if (window.WMSUI && typeof html2pdf !== 'undefined') {
                        window.WMSUI.showLoading('Menyiapkan PDF...', 'Sedang merender laporan absensi...');
                    }

                    fetch(`${baseUrl}admin/exportAbsensi/${type}?${params.toString()}`)
                        .then(res => res.text())
                        .then(htmlContent => {
                            const container = document.createElement('div');
                            container.innerHTML = htmlContent;
                            
                            const opt = {
                                margin: 10,
                                filename: `Laporan_Absensi_${new Date().toISOString().slice(0,10)}.pdf`,
                                image: { type: 'jpeg', quality: 0.98 },
                                html2canvas: { scale: 2 },
                                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' } // Landscape biar muat
                            };
                            
                            html2pdf().set(opt).from(container).save().then(() => {
                                if (window.WMSUI) Swal.close();
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Gagal membuat PDF.', 'error');
                        });

                } else {
                    // Logika Excel/CSV (Direct Download)
                    if (window.WMSUI) window.WMSUI.showLoading('Memproses Export...', type.toUpperCase());
                    setTimeout(() => {
                        window.location.href = `${baseUrl}admin/exportAbsensi/${type}?${params.toString()}`;
                        // Tutup loading setelah delay singkat
                        setTimeout(() => Swal.close(), 2000);
                    }, 500);
                }
            });
        });
    }
}

/**
 * ============================================================================
 * C. LOGIKA PROFIL (Riwayat Saya)
 * ============================================================================
 */
function initProfileHistory() {
    const startDate = document.getElementById('startDateProfile');
    const endDate = document.getElementById('endDateProfile');
    const historyBody = document.getElementById('historyTableBody');
    const form = document.getElementById('formHistory');
    const pagContainer = document.getElementById('paginationContainerHistory');
    const baseUrl = form.dataset.baseUrl;

    window.loadMyHistory = function(page = 1) {
        const params = new URLSearchParams({
            ajax: 1,
            page: page,
            start_date: startDate.value,
            end_date: endDate.value
        });

        historyBody.style.opacity = '0.5';

        fetch(`${baseUrl}profile/absensi?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if (data.absensi.length === 0) {
                    html = '<tr><td colspan="6" style="text-align:center; padding: 30px; color: #999;">Tidak ada data absensi.</td></tr>';
                } else {
                    data.absensi.forEach(absen => {
                        let statusData = getStatusStyle(absen.status_raw, absen.waktu_pulang, absen.tanggal);
                        if(absen.display_status === 'Masih Bekerja') statusData.text = 'Masih Bekerja';

                        let buktiHtml = `<span style="font-size: 0.85rem; color: #64748b;">${absen.keterangan || '-'}</span>`;
                        if (absen.bukti_foto) {
                            buktiHtml = `<a href="${baseUrl}uploads/bukti_absen/${absen.bukti_foto}" target="_blank" style="color: #2563eb; font-weight:600;"><i class="ph ph-file-text"></i> Lihat Bukti</a>`;
                        }

                        html += `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 15px;">${absen.tanggal}</td>
                            <td style="padding: 15px;">${absen.waktu_masuk}</td>
                            <td style="padding: 15px;">${absen.waktu_pulang}</td>
                            <td style="padding: 15px;">${absen.total_jam}</td>
                            <td style="padding: 15px;"><span class="badge-status" style="${statusData.style}">${statusData.text}</span></td>
                            <td style="padding: 15px;">${buktiHtml}</td>
                        </tr>`;
                    });
                }
                
                historyBody.innerHTML = html;
                historyBody.style.opacity = '1';

                if (window.WMSUI) {
                    window.WMSUI.renderPagination(pagContainer, data.totalPages, data.currentPage, (p) => window.loadMyHistory(p));
                }
            });
    };

    window.setPeriodProfile = function(type) {
        const today = new Date();
        let start = new Date();
        let end = new Date();
        if (type === 'this_week') {
            const day = today.getDay();
            const diff = today.getDate() - day + (day === 0 ? -6 : 1);
            start.setDate(diff); end.setDate(diff + 6);
        } else if (type === 'this_month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        } else if (type === 'last_month') {
            start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            end = new Date(today.getFullYear(), today.getMonth(), 0);
        }
        const formatDate = (d) => d.toISOString().split('T')[0];
        startDate.value = formatDate(start);
        endDate.value = formatDate(end);
        window.loadMyHistory(1);
    };

    startDate.addEventListener('change', () => window.loadMyHistory(1));
    endDate.addEventListener('change', () => window.loadMyHistory(1));
}

/**
 * ============================================================================
 * D. HELPERS & POPUPS
 * ============================================================================
 */

// Popup Edit Manual (Admin)
window.openEditModal = function(id, nama, masuk, pulang, status, ket) {
    const template = document.getElementById('templateEditAbsenAdmin');
    if (!template) return;
    
    Swal.fire({
        title: 'Edit Absensi',
        html: template.innerHTML,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        confirmButtonColor: '#152e4d',
        didOpen: () => {
            const popup = Swal.getPopup();
            popup.querySelector('#edit_absen_id').value = id;
            popup.querySelector('#edit_nama').value = nama;
            popup.querySelector('#edit_status').value = status;
            popup.querySelector('#edit_masuk').value = masuk;
            popup.querySelector('#edit_pulang').value = pulang;
            popup.querySelector('#edit_keterangan').value = ket;

            const statusSelect = popup.querySelector('#edit_status');
            const rowJam = popup.querySelector('#row_jam');
            const rowKet = popup.querySelector('#row_keterangan');
            
            const toggle = () => {
                if (statusSelect.value === 'Hadir') {
                    rowJam.style.display = 'flex';
                    rowKet.style.display = 'none';
                } else {
                    rowJam.style.display = 'none';
                    rowKet.style.display = 'block';
                }
            };
            statusSelect.addEventListener('change', toggle);
            toggle();

            const form = popup.querySelector('form');
            const searchInput = document.getElementById('searchAbsensi');
            if(searchInput) form.action = searchInput.dataset.baseUrl + 'admin/updateAbsensiManual';
        },
        preConfirm: () => {
            const form = Swal.getPopup().querySelector('form');
            form.submit();
        }
    });
};

window.showDetailKeterangan = function(text) {
    Swal.fire({ title: 'Keterangan', text: text, confirmButtonColor: '#152e4d' });
};

function getRoleBadgeStyle(role) {
    const r = (role || '').toLowerCase();
    if (r === 'admin') return 'color: #7c3aed; background: #f3e8ff; border: 1px solid #d8b4fe;';
    if (r === 'staff') return 'color: #059669; background: #ecfdf5; border: 1px solid #6ee7b7;';
    if (r === 'pemilik') return 'color: #d97706; background: #fffbeb; border: 1px solid #fde68a;';
    return 'color: #4b5563; background: #f3f4f6; border: 1px solid #d1d5db;';
}

function getStatusStyle(status, pulang, tanggal) {
    let style = 'background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;';
    let text = status;
    const today = new Date().toISOString().split('T')[0];

    if (status === 'Hadir') {
        style = 'background:#ecfdf5; color:#059669; border:1px solid #a7f3d0;';
        if (!pulang && tanggal === today) {
            text = 'Masih Bekerja';
            style = 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;';
        }
    } else if (status === 'Sakit') {
        style = 'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;';
    } else if (status === 'Izin') {
        style = 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;';
    }
    return { style, text };
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}