<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>
<main class="app-content">   
    <div class="content-header">
        <h1>Dashboard Staff</h1>
    </div>
    <div class="dashboard-widgets">       
        <div class="widget widget-absensi">
            <h3>Absensi Hari Ini</h3>
            <p>Halo, <?php echo $_SESSION['nama_lengkap']; ?>!</p>
            <?php $today = $data['today_attendance']; ?>
            <?php if (!$today): ?>
                <p>Anda belum melakukan presensi hari ini.</p>              
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <form action="<?php echo BASE_URL; ?>staff/processCheckIn" method="POST" style="flex: 1; margin: 0;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1em;">
                            ✅ HADIR (CHECK-IN)
                        </button>
                    </form>
                    <button type="button" 
                            onclick="showIzinModal('<?php echo BASE_URL; ?>staff/processAbsenTidakHadir')" 
                            class="btn btn-warning" 
                            style="flex: 1; padding: 15px; font-size: 1.1em;">
                        📩 IZIN / SAKIT
                    </button>
                </div>
            <?php elseif ($today['status'] == 'Hadir'): ?>
                
                <?php if ($today['waktu_pulang'] == null): ?>
                    <p>Anda Check-in pada: <strong><?php echo date('H:i:s', strtotime($today['waktu_masuk'])); ?></strong></p>
                    <form action="<?php echo BASE_URL; ?>staff/processCheckOut" method="POST" style="margin: 0;">
                        <button type="submit" class="btn btn-danger" style="width: 100%; padding: 15px; font-size: 1.2em;">CHECK-OUT</button>
                    </form>
                <?php else: ?>
                    <p style="color: green; font-weight: bold;">Anda sudah selesai bekerja hari ini.</p>
                    <p>Total Jam Kerja: <?php echo $today['waktu_masuk']; ?> - <?php echo $today['waktu_pulang']; ?></p>
                <?php endif; ?>
            <?php else: ?>
                <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeeba;">
                    <h4 style="color: #856404; margin-bottom: 5px;">Status: <?php echo htmlspecialchars($today['status']); ?></h4>
                    <p>Keterangan: "<?php echo htmlspecialchars($today['keterangan']); ?>"</p>                   
                     <?php if (!empty($today['bukti_foto'])): ?>
                        <p style="margin-top: 5px;">
                            <a href="<?php echo BASE_URL . 'uploads/bukti_absen/' . $today['bukti_foto']; ?>" target="_blank" style="color: #856404; text-decoration: underline;">
                                📄 Lihat Bukti Lampiran
                            </a>
                        </p>
                    <?php endif; ?>

                    <small>Tidak perlu Check-in/out.</small>
                </div>
            <?php endif; ?>
        </div>
        <div class="widget widget-shortcut">
            <h3>Akses Cepat Transaksi</h3>
            <div class="shortcut-buttons">
                <a href="<?php echo BASE_URL; ?>staff/barangMasuk" class="btn btn-primary">Input Barang Masuk</a>
                <a href="<?php echo BASE_URL; ?>staff/barangKeluar" class="btn btn-primary">Input Barang Keluar</a>
                <a href="<?php echo BASE_URL; ?>staff/returBarang" class="btn btn-warning">Lapor Barang Rusak</a>
            </div>
        </div>
        <div class="widget widget-tugas">
            <h3>Tugas Anda Hari Ini</h3>
            <ul>
                <li><a href="#">(Contoh) 2 Permintaan Peminjaman baru.</a></li>
                <li><a href="#">(Contoh) PERIODE STOCK OPNAME SEDANG AKTIF.</a></li>
            </ul>
        </div>
    </div>
</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>