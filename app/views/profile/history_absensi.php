<?php
    require_once APPROOT . '/views/templates/header.php';
    
    // Load Sidebar Sesuai Role
    if ($_SESSION['role'] == 'admin') {
        require_once APPROOT . '/views/templates/sidebar_admin.php';
    } else if ($_SESSION['role'] == 'staff') {
        require_once APPROOT . '/views/templates/sidebar_staff.php';
    } else if ($_SESSION['role'] == 'pemilik') {
        require_once APPROOT . '/views/templates/sidebar_pemilik.php';
    } else {
        require_once APPROOT . '/views/templates/sidebar_peminjam.php';
    }
?>

<main class="app-content">
    
    <div class="content-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>profile/index" class="btn" style="background-color: #6c757d; color: white;">
                &larr; Kembali ke Profil
            </a>
            <h1>Riwayat Absensi Saya</h1>
        </div>
    </div>

    <div class="search-container">
        <form id="formHistory" data-base-url="<?php echo BASE_URL; ?>" style="display: flex; gap: 10px;">
            
            <select id="filterMonthProfile" class="filter-select">
                <?php for($m=1; $m<=12; $m++): 
                    $monthName = date('F', mktime(0, 0, 0, $m, 10));
                ?>
                    <option value="<?php echo $m; ?>" <?php if($data['filters']['month'] == $m) echo 'selected'; ?>>
                        <?php echo $monthName; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <select id="filterYearProfile" class="filter-select">
                <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php if($data['filters']['year'] == $y) echo 'selected'; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>

            </form>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Total Jam</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody id="historyTableBody">
                <?php if(empty($data['absensi'])): ?>
                    <tr><td colspan="6" style="text-align:center;">Belum ada data absensi bulan ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['absensi'] as $absen) : 
                        // Logika PHP ini nanti akan kita duplikasi di JavaScript juga
                        $totalJam = '-';
                        $statusClass = 'status-gray';
                        $displayStatus = $absen['status'];
                        
                        if ($absen['status'] == 'Hadir') {
                            $statusClass = 'status-green';
                            if ($absen['waktu_masuk'] && $absen['waktu_pulang']) {
                                $checkin = new DateTime($absen['waktu_masuk']);
                                $checkout = new DateTime($absen['waktu_pulang']);
                                $interval = $checkin->diff($checkout);
                                $totalJam = $interval->format('%h jam %i mnt');
                            } elseif ($absen['waktu_masuk'] && !$absen['waktu_pulang']) {
                                $displayStatus = 'Masih Bekerja';
                                $statusClass = 'status-green';
                            }
                        } elseif ($absen['status'] == 'Sakit') {
                            $statusClass = 'status-red';
                        } elseif ($absen['status'] == 'Izin') {
                            $statusClass = 'status-orange';
                        }
                    ?>
                    <tr>
                        <td><?php echo date('d-m-Y', strtotime($absen['tanggal'])); ?></td>
                        <td><?php echo $absen['waktu_masuk'] ? date('H:i', strtotime($absen['waktu_masuk'])) : '-'; ?></td>
                        <td><?php echo $absen['waktu_pulang'] ? date('H:i', strtotime($absen['waktu_pulang'])) : '-'; ?></td>
                        <td><?php echo $totalJam; ?></td>
                        <td>
                            <span style="font-weight:bold; color:<?php 
                                echo ($statusClass == 'status-green') ? 'green' : 
                                     (($statusClass == 'status-red') ? 'red' : 
                                     (($statusClass == 'status-orange') ? 'orange' : 'gray')); 
                            ?>">
                                <?php echo $displayStatus; ?>
                            </span>
                        </td>
                        <td>
                            <?php if($absen['bukti_foto']): ?>
                                <a href="<?php echo BASE_URL . 'uploads/bukti_absen/' . $absen['bukti_foto']; ?>" target="_blank" style="text-decoration: underline; color: blue;">
                                    Lihat Bukti
                                </a>
                            <?php else: ?>
                                <?php echo htmlspecialchars($absen['keterangan'] ?? '-'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container"></div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>