<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $activePeriod = $data['activePeriod'];
    $report = $data['reconciliationReport'];
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Stock Opname / Penyesuaian Stok</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <?php if (!$activePeriod): ?>
        <div class="flash-message error" style="text-align: center; font-size: 1.2em; padding: 40px;">
            ❌ **Status: Tidak Ada Opname Aktif.** <br>
            Anda harus memulai periode baru untuk audit stok.
        </div>
        
        <div class="form-actions" style="border-top: none;">
            <a href="<?php echo BASE_URL; ?>admin/startNewOpname" 
               class="btn btn-primary" style="padding: 15px 30px; font-size: 1.1em;"
               onclick="return confirm('Anda yakin ingin memulai periode Opname baru? Ini akan menghapus semua log hitungan sebelumnya.');">
                Mulai Periode Stock Opname Baru
            </a>
        </div>

    <?php elseif ($activePeriod && $report == null): ?>
        <div class="flash-message success" style="text-align: center;">
            ✅ **Status: Opname Sedang Berlangsung.** <br>
            Dimulai pada: <?php echo date('d-m-Y H:i', strtotime($activePeriod['start_date'])); ?>
        </div>

        <div class="widget">
            <h3>Monitor Kemajuan Hitungan</h3>
            <p>Staff dapat mulai menginput hasil hitungan fisik. Klik tombol di bawah ini jika semua Staff sudah selesai.</p>
            
            <div class="form-actions" style="border-top: none;">
                <a href="<?php echo BASE_URL; ?>admin/stockOpname?view_report=1" 
                   class="btn btn-warning" style="padding: 15px 30px; font-size: 1.1em;">
                    Lihat Laporan Rekonsiliasi
                </a>
                <small style="display: block; margin-top: 10px; color: #555;">(Aksi ini akan menghitung selisih stok sistem vs. fisik)</small>
            </div>
        </div>

    <?php elseif ($activePeriod && $report != null): ?>
        <div class="flash-message error" style="text-align: center;">
            ⚠️ **Status: Menunggu Persetujuan.** <br>
            Data hitungan fisik telah dikunci. Harap tinjau laporan selisih di bawah ini.
        </div>
        
        <form action="<?php echo BASE_URL; ?>admin/finalizeOpname" method="POST">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Stok Sistem</th>
                            <th>Stok Fisik (Hitungan)</th>
                            <th style="color: red;">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hasSelisih = false;
                        foreach ($report as $item) : 
                            $selisih = $item['selisih'];
                            if ($selisih != 0) $hasSelisih = true;
                        ?>
                        <tr style="<?php if($selisih != 0) echo 'background-color: #f8d7da;'; ?>">
                            <td><?php echo htmlspecialchars($item['kode_barang']); ?></td>
                            <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                            <td><?php echo $item['stok_sistem']; ?></td>
                            <td><?php echo $item['stok_fisik']; ?></td>
                            <td style="font-weight: bold; color: <?php echo ($selisih != 0) ? 'red' : 'green'; ?>;">
                                <?php echo $selisih; ?>
                                <input type="hidden" name="adjustment[<?php echo $item['product_id']; ?>]" value="<?php echo $selisih; ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn btn-danger" style="padding: 15px 30px; font-size: 1.1em;"
                        onclick="return confirm('ANDA YAKIN? Aksi ini akan MENYESUAIKAN stok di sistem agar sama dengan hitungan fisik. TIDAK BISA DIBATALKAN.');">
                    Setujui Penyesuaian & Tutup Periode
                </button>
                <a href="<?php echo BASE_URL; ?>admin/stockOpname" class="btn" style="background-color: #6c757d; color: white;">Batal</a>
            </div>
        </form>
    <?php endif; ?>

    <h2 style="margin-top: 40px;">Riwayat Periode Stock Opname Sebelumnya</h2>
    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>ID Periode</th>
                    <th>Dimulai</th>
                    <th>Selesai</th>
                    <th>Status</th>
                    Difinalisasi oleh</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['completedPeriods'] as $period): ?>
                <tr>
                    <td><?php echo $period['period_id']; ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($period['start_date'])); ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($period['end_date'])); ?></td>
                    <td><?php echo $period['status']; ?></td>
                    <td><?php echo htmlspecialchars($period['finalized_by'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>