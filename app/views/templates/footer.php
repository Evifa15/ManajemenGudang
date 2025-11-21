</div> <template id="templateModalIzin">
        <form id="formIzin" method="POST" enctype="multipart/form-data">
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight:bold;">Pilih Status:</label>
                <select name="status" class="swal2-input" style="width: 100%; margin: 5px 0;">
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                </select>
            </div>
            <div style="text-align: left; margin-bottom: 10px;">
                <label style="font-weight:bold;">Keterangan:</label>
                <textarea name="keterangan" class="swal2-textarea" style="width: 100%; margin: 5px 0;" required placeholder="Jelaskan alasan Anda..."></textarea>
            </div>
            <div style="text-align: left;">
                <label style="font-weight:bold;">Bukti (Opsional):</label>
                <input type="file" name="bukti_foto" class="swal2-file" style="width: 100%; margin-top:5px;">
            </div>
        </form>
    </template>
    <template id="templateEditAbsenAdmin">
        <form id="formEditAbsen" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="absen_id" id="edit_absen_id">
            
            <div style="text-align: left; margin-bottom: 15px;">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Nama Karyawan:</label>
                <input type="text" id="edit_nama" class="swal2-input" style="margin:0; width:100%; background:#eee;" readonly>
            </div>

            <div style="text-align: left; margin-bottom: 15px;">
                <label style="font-weight:bold; display:block; margin-bottom:5px;">Status Kehadiran:</label>
                <select name="status" id="edit_status" class="swal2-select" style="margin:0; width:100%; display:block;">
                    <option value="Hadir">Hadir / Masih Bekerja</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Alpa">Alpa</option>
                </select>
            </div>

            <div id="row_jam" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1; text-align: left;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Jam Masuk:</label>
                    <input type="time" name="waktu_masuk" id="edit_masuk" class="swal2-input" style="margin:0; width:100%;">
                </div>
                <div style="flex: 1; text-align: left;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Jam Pulang:</label>
                    <input type="time" name="waktu_pulang" id="edit_pulang" class="swal2-input" style="margin:0; width:100%;">
                </div>
            </div>

            <div id="row_keterangan" style="text-align: left; margin-bottom: 15px; display: none;">
                <div style="margin-bottom: 10px;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Keterangan / Alasan:</label>
                    <textarea name="keterangan" id="edit_keterangan" class="swal2-textarea" style="margin:0; width:100%; height: 80px;"></textarea>
                </div>
                
                <div>
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Upload Bukti (Opsional):</label>
                    <input type="file" name="bukti_foto" id="edit_bukti" class="swal2-file" style="width: 100%; font-size: 0.9em;">
                    <small style="color: #666; display:block; margin-top:5px;">Format: JPG/PNG/PDF (Max 2MB)</small>
                </div>
            </div>

        </form>
    </template>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script src="<?php echo BASE_URL; ?>js/main.js"></script>

    <?php 
    // Opsional: Popup Selamat Datang jika baru login
    if (isset($_SESSION['welcome_popup'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Selamat Datang!',
                    text: 'Halo, <?php echo htmlspecialchars($_SESSION['welcome_popup']); ?>!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    position: 'center'
                });
            });
        </script>
        <?php unset($_SESSION['welcome_popup']); ?>
    <?php endif; ?>

</body>
</html>