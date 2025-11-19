<?php
    // 1. Memuat "Kepala" Halaman
    require_once APPROOT . '/views/templates/header.php';
?>

<?php
    // 2. Memuat "Sidebar" KHUSUS STAFF
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>

<!-- 3. KONTEN UTAMA Halaman Dashboard Staff -->
<main class="app-content">
    
    <div class="content-header">
        <h1>Dashboard Staff</h1>
    </div>

    <!-- Sesuai Rancangan UI-POV Anda [cite: prompt] -->
    <div class="dashboard-widgets">
                <!-- Widget 1: Absensi (Modul N) -->
                <div class="widget widget-absensi">
            <h3>Absensi Hari Ini</h3>
            <p>Halo, <?php echo $_SESSION['nama_lengkap']; ?>!</p>

            <?php $today = $data['today_attendance']; ?>

            <?php if (!$today): // 1. Belum Check-in ?>
                <p>Anda belum melakukan check-in hari ini.</p>
                <form action="<?php echo BASE_URL; ?>staff/processCheckIn" method="POST" style="margin: 0;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.2em;">CHECK-IN SEKARANG</button>
                </form>

            <?php elseif ($today && $today['waktu_pulang'] == null): // 2. Sudah Check-in, Belum Check-out ?>
                <p>Anda Check-in pada: <strong><?php echo date('H:i:s', strtotime($today['waktu_masuk'])); ?></strong></p>
                <form action="<?php echo BASE_URL; ?>staff/processCheckOut" method="POST" style="margin: 0;">
                    <button type="submit" class="btn btn-danger" style="width: 100%; padding: 15px; font-size: 1.2em;">CHECK-OUT</button>
                </form>

            <?php else: // 3. Sudah Check-out (Selesai Kerja) ?>
                <p style="color: green; font-weight: bold;">Anda sudah selesai absen hari ini.</p>
                <p>Check-in: <?php echo date('H:i:s', strtotime($today['waktu_masuk'])); ?> | Check-out: <?php echo date('H:i:s', strtotime($today['waktu_pulang'])); ?></p>
            <?php endif; ?>
        </div>

        <!-- Widget 2: Shortcut Transaksi Utama -->
        <div class="widget widget-shortcut">
            <h3>Akses Cepat Transaksi</h3>
            <div class="shortcut-buttons">
                <a href="<?php echo BASE_URL; ?>staff/barangMasuk" class="btn btn-primary">Input Barang Masuk</a>
                <a href="#" class="btn btn-primary">Input Barang Keluar</a>
                <a href="#" class="btn btn-warning">Lapor Barang Rusak</a>
            </div>
        </div>

        <!-- Widget 3: Tugas Operasional (Notifikasi) -->
        <div class="widget widget-tugas">
            <h3>Tugas Anda Hari Ini</h3>
            <ul>
                <li><a href="#">(Contoh) 2 Permintaan Peminjaman baru.</a></li>
                <li><a href="#">(Contoh) PERIODE STOCK OPNAME SEDANG AKTIF.</a></li>
            </ul>
        </div>
    </div>
</main>
<!-- AKHIR KONTEN UTAMA -->

<?php
    // 4. Memuat "Kaki" Halaman
    require_once APPROOT . '/views/templates/footer.php';
?>