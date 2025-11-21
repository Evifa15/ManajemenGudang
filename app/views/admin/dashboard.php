<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <div class="widget widget-absensi" style="margin-bottom: 20px;">
        <h3>Absensi Hari Ini</h3>
        <p>Halo, <?php echo $_SESSION['nama_lengkap']; ?>!</p>
        
        <?php $today = $data['today_attendance']; ?>

        <?php if (!$today): // Belum Absen ?>
            <p>Anda belum melakukan presensi hari ini.</p>
            
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <form action="<?php echo BASE_URL; ?>admin/processCheckIn" method="POST" style="flex: 1; margin: 0;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1em;">
                        ✅ HADIR (CHECK-IN)
                    </button>
                </form>

                <button type="button" 
                        onclick="showIzinModal('<?php echo BASE_URL; ?>admin/processAbsenTidakHadir')" 
                        class="btn btn-warning" 
                        style="flex: 1; padding: 15px; font-size: 1.1em;">
                    📩 IZIN / SAKIT
                </button>
            </div>

        <?php elseif ($today['status'] == 'Hadir'): ?>
            
            <?php if ($today['waktu_pulang'] == null): ?>
                <p>Anda Check-in pada: <strong><?php echo date('H:i:s', strtotime($today['waktu_masuk'])); ?></strong></p>
                <form action="<?php echo BASE_URL; ?>admin/processCheckOut" method="POST" style="margin: 0;">
                    <button type="submit" class="btn btn-danger" style="width: 100%; padding: 15px; font-size: 1.2em;">CHECK-OUT</button>
                </form>
            <?php else: ?>
                <p style="color: green; font-weight: bold;">Anda sudah selesai bekerja hari ini.</p>
                <p>Total Jam Kerja: <?php echo $today['waktu_masuk']; ?> - <?php echo $today['waktu_pulang']; ?></p>
            <?php endif; ?>

        <?php else: // Izin/Sakit ?>
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

    <div class="content-header">
        <h1>Dashboard Admin</h1>
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
    require_once APPROOT . '/views/templates/footer.php';
?>