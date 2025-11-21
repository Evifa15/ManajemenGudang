    /* --- MENU LOGIN / AUTHENTIFIKASI --- */
    document.addEventListener('DOMContentLoaded', function() {
        const alertBox = document.getElementById('loginAlert') || document.querySelector('.flash-message');   
        if (alertBox) {
            setTimeout(() => {
                alertBox.style.transition = "opacity 0.5s ease";
                alertBox.style.opacity = "0"; 
                setTimeout(() => {
                    alertBox.remove();
                }, 500);
            }, 3000);
        }
    });

    /* --- FITUR ABSENSI --- */
    function showIzinModal(actionUrl) {
        Swal.fire({
            title: 'Form Ketidakhadiran',
            html: `
                <form id="formIzin" action="${actionUrl}" method="POST" enctype="multipart/form-data">
                    <div style="text-align: left; margin-bottom: 10px;">
                        <label style="font-weight:bold;">Pilih Status:</label>
                        <select name="status" class="swal2-input" style="width: 100%; margin: 5px 0;">
                            <option value="Sakit">Sakit</option>
                            <option value="Izin">Izin (Keperluan Pribadi)</option>
                        </select>
                    </div>
                    
                    <div style="text-align: left; margin-bottom: 10px;">
                        <label style="font-weight:bold;">Keterangan / Alasan:</label>
                        <textarea name="keterangan" class="swal2-textarea" style="width: 100%; margin: 5px 0;" placeholder="Jelaskan alasan Anda..." required></textarea>
                    </div>

                    <div style="text-align: left;">
                        <label style="font-weight:bold;">Upload Bukti (Opsional):</label>
                        <input type="file" name="bukti_foto" class="swal2-file" style="width: 100%; margin-top:5px;" accept="image/*,.pdf">
                        <small style="color:#666; font-size: 0.85em;">Format: JPG, PNG, PDF (Max 2MB)</small>
                    </div>

                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Kirim Laporan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const form = document.getElementById('formIzin');
                const ket = form.querySelector('textarea[name="keterangan"]').value;
                const fileInput = form.querySelector('input[name="bukti_foto"]');
                const file = fileInput.files[0];
                if (!ket) {
                    Swal.showValidationMessage('Harap isi keterangan!');
                    return false;
                }
                if (file && file.size > 2 * 1024 * 1024) {
                    Swal.showValidationMessage('Ukuran file terlalu besar (Max 2MB)!');
                    return false;
                }
                form.submit();
            }
        });
    }

    /* --- HALAMAN REKAP ABSENSI (ADMIN) --- */
    if (searchInputAbsensi) {
        const filterUser = document.getElementById('filterUser');
        const filterMonth = document.getElementById('filterMonth');
        const filterYear = document.getElementById('filterYear');
        const tableBody = document.getElementById('absensiTableBody');
        const baseUrl = searchInputAbsensi.dataset.baseUrl;
        function loadAbsensi() {
            const params = new URLSearchParams({
                ajax: 1,
                search: searchInputAbsensi.value,
                user_id: filterUser.value,
                month: filterMonth.value,
                year: filterYear.value
            });
            fetch(`${baseUrl}admin/rekapAbsensi?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if(data.absensi.length === 0) {
                        html = '<tr><td colspan="7" style="text-align:center;">Data tidak ditemukan.</td></tr>';
                    } else {
                        data.absensi.forEach(absen => {
                            let statusClass = 'status-gray';
                            if(absen.status === 'Hadir' || absen.status === 'Masih Bekerja') statusClass = 'status-green';
                            else if(absen.status === 'Sakit') statusClass = 'status-red';
                            else if(absen.status === 'Izin') statusClass = 'status-orange';
                            html += `
                            <tr>
                                <td>${absen.tanggal}</td>
                                <td>${absen.nama_lengkap}</td>
                                <td>${absen.waktu_masuk}</td>
                                <td>${absen.waktu_pulang}</td>
                                <td>${absen.total_jam}</td>
                                <td>
                                    <span class="${statusClass}">${absen.status}</span>
                                </td>
                                <td class="no-print">
                                    <button class="btn btn-warning btn-sm" 
                                        onclick="editAbsenPopup('${absen.absen_id}', '${absen.nama_lengkap}', '${absen.waktu_masuk}', '${absen.waktu_pulang}')">
                                        Edit
                                    </button>
                                </td>
                            </tr>`;
                        });
                    }
                    tableBody.innerHTML = html;
                })
                .catch(err => console.error('Error fetching data:', err));
        }
        searchInputAbsensi.addEventListener('input', loadAbsensi);
        filterUser.addEventListener('change', loadAbsensi);
        filterMonth.addEventListener('change', loadAbsensi);
        filterYear.addEventListener('change', loadAbsensi);
    }
    

