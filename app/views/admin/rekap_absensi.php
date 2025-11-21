<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>
<main class="app-content">
    
    <?php
        // --- BLOK NOTIFIKASI (INI YANG KITA TAMBAHKAN) ---
        // Bertugas menangkap pesan dari Controller dan menampilkannya
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']); // Hapus agar tidak muncul lagi saat refresh
        }
    ?>

    <div class="content-header">
        <h1>Rekap Absensi Karyawan</h1>
        <div>
            <button id="btnExportPdf" class="btn btn-danger">
                📄 Download PDF
            </button>
        </div>
    </div>

    <div class="search-container filter-row">       
        <div class="filter-group flex-grow">
            <label for="searchAbsensi">Cari Nama Karyawan:</label>
            <input type="text" id="searchAbsensi" class="search-input" 
                   placeholder="Ketik nama..." 
                   data-base-url="<?php echo BASE_URL; ?>"> 
        </div>

        <div class="filter-group">
            <label for="filterStatus">Status Kehadiran:</label>
            <select id="filterStatus" class="filter-select full-width">
                <option value="">-- Semua Status --</option>
                <option value="Hadir">Hadir (Selesai)</option>
                <option value="Masih Bekerja">Masih Bekerja (Belum Pulang)</option>
                <option value="Sakit">Sakit</option>
                <option value="Izin">Izin</option>
                <option value="Alpa">Alpa</option>
            </select>
        </div>

        <div class="filter-group date-filters">
            <div class="date-select-wrapper">
                <label for="filterMonth">Bulan:</label>
                <select id="filterMonth" class="filter-select full-width">
                    <?php for($m=1; $m<=12; $m++): 
                        $monthName = date('F', mktime(0, 0, 0, $m, 10));
                    ?>
                        <option value="<?php echo $m; ?>" <?php if($data['filters']['month'] == $m) echo 'selected'; ?>>
                            <?php echo $monthName; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="date-select-wrapper small">
                <label for="filterYear">Tahun:</label>
                <select id="filterYear" class="filter-select full-width">
                    <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php if($data['filters']['year'] == $y) echo 'selected'; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
    </div>
    
    <div id="areaPrintAbsensi" class="content-table">
        <div class="pdf-header">
            <h2>Laporan Absensi Karyawan</h2>
            <p>Manajemen Gudang</p>
            <hr>
        </div>
        <table id="tableAbsensi">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Karyawan</th>
                    <th>Masuk</th>
                    <th>Pulang</th>
                    <th>Total Jam</th>
                    <th>Status</th>
                    <th class="no-print">Aksi</th> 
                </tr>
            </thead>
            <tbody id="absensiTableBody">
                <?php foreach ($data['absensi'] as $absen) : 
                    $totalJam = '-';
                    $waktuMasuk = '-';
                    $waktuPulang = '-';
                    $statusClass = 'status-gray'; 
                    if ($absen['status'] != 'Hadir') {
                        $status = $absen['status'];
                        if($status == 'Sakit') $statusClass = 'status-red';
                        elseif($status == 'Izin') $statusClass = 'status-orange';
                    } 
                    else {
                        $waktuMasuk = $absen['waktu_masuk'] ? date('H:i:s', strtotime($absen['waktu_masuk'])) : '-';                        
                        if ($absen['waktu_pulang']) {
                            $status = 'Hadir';
                            $statusClass = 'status-green';
                            $waktuPulang = date('H:i:s', strtotime($absen['waktu_pulang']));
                            $checkin = new DateTime($absen['waktu_masuk']);
                            $checkout = new DateTime($absen['waktu_pulang']);
                            $interval = $checkin->diff($checkout);
                            $totalJam = $interval->format('%h jam %i mnt');
                        } else {
                            $status = 'Masih Bekerja';
                            $statusClass = 'status-green';
                        }
                    }
                ?>
                <tr>
                    <td><?php echo date('d-m-Y', strtotime($absen['tanggal'])); ?></td>
                    <td><?php echo htmlspecialchars($absen['nama_lengkap']); ?></td>
                    <td><?php echo $waktuMasuk; ?></td>
                    <td><?php echo $waktuPulang; ?></td>
                    <td><?php echo $totalJam; ?></td>
                    <td>
                        <span class="<?php echo $statusClass; ?>">
                            <?php echo $status; ?>
                        </span>
                        <?php if(!empty($absen['bukti_foto'])): ?>
                            <br>
                            <a href="<?php echo BASE_URL . 'uploads/bukti_absen/' . $absen['bukti_foto']; ?>" 
                               target="_blank" 
                               class="link-bukti">
                               (Lihat Bukti)
                            </a>
                        <?php endif; ?>
                    </td>
                    <td class="no-print">
                        <button class="btn btn-warning btn-sm" 
                                onclick="editAbsenPopup('<?php echo $absen['absen_id']; ?>', '<?php echo htmlspecialchars($absen['nama_lengkap']); ?>', '<?php echo $absen['waktu_masuk']; ?>', '<?php echo $absen['waktu_pulang']; ?>', '<?php echo $absen['status']; ?>', '<?php echo htmlspecialchars($absen['keterangan'] ?? '', ENT_QUOTES); ?>')">
                            Edit
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination-container"></div>
</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>