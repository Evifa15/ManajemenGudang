<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_pemilik.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Rekap Absensi Karyawan (Read-Only)</h1>
        <div>
            <button class="btn btn-primary" onclick="window.print();">Cetak Laporan</button>
        </div>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>pemilik/rekapAbsensi" method="GET">
            
            <select name="user_id" class="filter-select">
                <option value="">Semua Karyawan</option>
                <?php foreach($data['allKaryawan'] as $k): ?>
                    <option value="<?php echo $k['user_id']; ?>" <?php if($data['filters']['user_id'] == $k['user_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($k['nama_lengkap']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="month" class="filter-select">
                <?php for($m=1; $m<=12; $m++): 
                    $monthName = date('F', mktime(0, 0, 0, $m, 10));
                ?>
                    <option value="<?php echo $m; ?>" <?php if($data['filters']['month'] == $m) echo 'selected'; ?>>
                        <?php echo $monthName; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <select name="year" class="filter-select">
                <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php if($data['filters']['year'] == $y) echo 'selected'; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
            
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Karyawan</th>
                    <th>Waktu Check-in</th>
                    <th>Waktu Check-out</th>
                    <th>Total Jam Kerja</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['absensi'] as $absen) : 
                    $totalJam = '-';
                    $status = 'Alpa'; // Asumsi default
                    if ($absen['waktu_masuk']) {
                        $status = 'Hadir';
                        if ($absen['waktu_pulang']) {
                            $checkin = new DateTime($absen['waktu_masuk']);
                            $checkout = new DateTime($absen['waktu_pulang']);
                            $interval = $checkin->diff($checkout);
                            $totalJam = $interval->format('%h jam %i mnt');
                        } else {
                            $status = 'Masih Bekerja (Belum Check-out)';
                            $totalJam = '-';
                        }
                    }
                ?>
                <tr>
                    <td><?php echo date('d-m-Y', strtotime($absen['tanggal'])); ?></td>
                    <td><?php echo htmlspecialchars($absen['nama_lengkap']); ?></td>
                    <td><?php echo $absen['waktu_masuk'] ? date('H:i:s', strtotime($absen['waktu_masuk'])) : '-'; ?></td>
                    <td><?php echo $absen['waktu_pulang'] ? date('H:i:s', strtotime($absen['waktu_pulang'])) : '-'; ?></td>
                    <td><?php echo $totalJam; ?></td>
                    <td><?php echo $status; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>