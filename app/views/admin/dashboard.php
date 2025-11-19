<?php
    // 1. Memuat "Kepala" Halaman
    require_once APPROOT . '/views/templates/header.php';
?>

<?php
    // 2. Memuat "Sidebar" Halaman KHUSUS ADMIN
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <div class="widget widget-absensi" style="margin-bottom: 20px;">
    <h3>Absensi Hari Ini</h3>
    <?php $today = $data['today_attendance']; ?>

    <?php if (!$today): // 1. Belum Check-in ?>
        <p>Anda belum melakukan check-in hari ini.</p>
        <form action="<?php echo BASE_URL; ?>admin/processCheckIn" method="POST" style="margin: 0;">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.2em;">CHECK-IN SEKARANG</button>
        </form>

    <?php elseif ($today && $today['waktu_pulang'] == null): // 2. Sudah Check-in, Belum Check-out ?>
        <p>Anda Check-in pada: <strong><?php echo date('H:i:s', strtotime($today['waktu_masuk'])); ?></strong></p>
        <form action="<?php echo BASE_URL; ?>admin/processCheckOut" method="POST" style="margin: 0;">
            <button type="submit" class="btn btn-danger" style="width: 100%; padding: 15px; font-size: 1.2em;">CHECK-OUT</button>
        </form>

    <?php else: // 3. Sudah Check-out (Selesai Kerja) ?>
        <p style="color: green; font-weight: bold;">Anda sudah selesai absen hari ini.</p>
        <p>Check-in: <?php echo date('H:i:s', strtotime($today['waktu_masuk'])); ?> | Check-out: <?php echo date('H:i:s', strtotime($today['waktu_pulang'])); ?></p>
    <?php endif; ?>
</div>
    <div class="content-header">
        <h1>Dashboard Admin</h1>
    </div>

    <div class="dashboard-widgets widget-group-peringatan">
        <a href="<?php echo BASE_URL; ?>admin/laporanStok?status=menipis" class="widget widget-peringatan <?php echo ($data['widget_peringatan']['stok_menipis'] > 0) ? 'active' : ''; ?>">
            <h4>Stok Menipis</h4>
            <span><?php echo $data['widget_peringatan']['stok_menipis']; ?></span>
            <p>Item perlu di-restock</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/laporanPeminjaman?status=Jatuh+Tempo" class="widget widget-peringatan <?php echo ($data['widget_peringatan']['jatuh_tempo'] > 0) ? 'active' : ''; ?>">
            <h4>Peminjaman Jatuh Tempo</h4>
            <span><?php echo $data['widget_peringatan']['jatuh_tempo']; ?></span>
            <p>Aset belum kembali</p>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/laporanTransaksi#tab-rusak" class="widget widget-peringatan <?php echo ($data['widget_peringatan']['barang_rusak'] > 0) ? 'active' : ''; ?>">
            <h4>Barang Rusak (Bulan Ini)</h4>
            <span><?php echo $data['widget_peringatan']['barang_rusak']; ?></span>
            <p>Item dicatat rusak/retur</p>
        </a>
    </div>

    <div class="dashboard-widgets widget-group-kpi">
        <div class="widget widget-statistik">
            <h4>Item Keluar (Hari Ini)</h4>
            <span><?php echo $data['widget_kpi']['keluar_hari_ini']; ?></span>
            <p>Total transaksi keluar</p>
        </div>
        <div class="widget widget-statistik">
            <h4>Staf Hadir (Hari Ini)</h4>
            <span><?php echo $data['widget_kpi']['hadir_hari_ini']; ?></span>
            <p>Karyawan telah check-in</p>
        </div>
    </div>

    <div class="widget widget-grafik-besar">
        <h3>Tren Barang Masuk vs. Keluar (6 Bulan Terakhir)</h3>
        <canvas id="grafikTransaksi"></canvas>
    </div>

    <div class="dashboard-widgets widget-group-pengawasan">
        <div class="widget widget-log">
            <h3>Staf yang Sedang Bekerja</h3>
            <ul class="log-list">
                <?php if(empty($data['widget_pengawasan']['staf_hadir'])): ?>
                    <li>Tidak ada staf yang sedang check-in.</li>
                <?php endif; ?>
                <?php foreach($data['widget_pengawasan']['staf_hadir'] as $staf): ?>
                    <li><?php echo htmlspecialchars($staf['nama_lengkap']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="widget widget-log">
            <h3>Aktivitas Kritis Terakhir</h3>
            <ul class="log-list">
                 <?php if(empty($data['widget_pengawasan']['log_terbaru'])): ?>
                    <li>Belum ada aktivitas.</li>
                <?php endif; ?>
                <?php foreach($data['widget_pengawasan']['log_terbaru'] as $log): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($log['nama_lengkap'] ?? 'Sistem'); ?></strong> 
                        <?php echo htmlspecialchars($log['aksi']); ?>
                        <small>(<?php echo date('H:i', strtotime($log['waktu'])); ?>)</small>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="<?php echo BASE_URL; ?>admin/auditTrail" style="margin-top: 10px; display: inline-block;">Lihat Audit Trail Lengkap...</a>
        </div>
    </div>

</main>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?php echo $data['grafik']['labels']; ?>;
    const dataMasuk = <?php echo $data['grafik']['dataMasuk']; ?>;
    const dataKeluar = <?php echo $data['grafik']['dataKeluar']; ?>;

    const ctx = document.getElementById('grafikTransaksi').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Barang Masuk',
                    data: dataMasuk,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Barang Keluar',
                    data: dataKeluar,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });
</script>

<?php
    // 4. Memuat "Kaki" Halaman
    require_once APPROOT . '/views/templates/footer.php';
?>