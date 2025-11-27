<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
    
    $p = $data['period'];
?>

<main class="app-content">
    
    <div class="content-header no-print">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>admin/riwayatOpname" class="btn" style="background: #6c757d; color: white;">&larr; Kembali</a>
            <h1>Detail Laporan Hasil Opname</h1>
        </div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Dokumen</button>
    </div>

    <div style="background: white; padding: 40px; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.05); margin-top: 20px;">
        
        <div style="border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; display: flex; justify-content: space-between;">
            <div>
                <h2 style="margin: 0; text-transform: uppercase;">Laporan Hasil Stock Opname</h2>
                <p style="margin: 5px 0 0 0; color: #666;">Nomor Dokumen: <strong><?php echo htmlspecialchars($p['nomor_sp']); ?></strong></p>
            </div>
            <div style="text-align: right;">
                <p style="margin:0;">Status: <strong style="color:green; border:1px solid green; padding:2px 5px;">FINAL / SELESAI</strong></p>
                <p style="margin:5px 0 0 0; font-size: 0.9em;">Dicetak pada: <?php echo date('d-M-Y H:i'); ?></p>
            </div>
        </div>

        <div style="display: flex; gap: 40px; margin-bottom: 30px;">
            <div style="flex: 1;">
                <h4 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px;">A. Informasi Pelaksanaan</h4>
                <table style="width: 100%; font-size: 0.95em;">
                    <tr>
                        <td style="width: 140px; color: #666;">Dimulai Oleh:</td>
                        <td><strong><?php echo htmlspecialchars($p['creator_name']); ?></strong></td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Waktu Mulai:</td>
                        <td><?php echo date('d F Y, H:i', strtotime($p['start_date'])); ?></td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Difinalisasi Oleh:</td>
                        <td><strong><?php echo htmlspecialchars($p['finalizer_name']); ?></strong></td>
                    </tr>
                    <tr>
                        <td style="color: #666;">Waktu Selesai:</td>
                        <td><?php echo date('d F Y, H:i', strtotime($p['end_date'])); ?></td>
                    </tr>
                </table>
                
                <?php if($p['catatan_admin']): ?>
                    <div style="margin-top: 10px; background: #f9f9f9; padding: 10px; font-style: italic; font-size: 0.9em;">
                        "<?php echo htmlspecialchars($p['catatan_admin']); ?>"
                    </div>
                <?php endif; ?>
            </div>

            <div style="flex: 1;">
                <h4 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px;">B. Tim Pemeriksa (Staff)</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                    <tr style="background: #f8f9fa; text-align: left;">
                        <th style="padding: 5px; border-bottom: 1px solid #ddd;">Kategori</th>
                        <th style="padding: 5px; border-bottom: 1px solid #ddd;">Pemeriksa</th>
                        <th style="padding: 5px; border-bottom: 1px solid #ddd;">Status</th>
                    </tr>
                    <?php foreach($data['participants'] as $task): ?>
                    <tr>
                        <td style="padding: 5px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($task['nama_kategori']); ?></td>
                        <td style="padding: 5px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($task['staff_name'] ?? '-'); ?></td>
                        <td style="padding: 5px; border-bottom: 1px solid #eee;">
                            <?php if($task['status_task']=='Submitted'): ?>
                                <span style="color:green;">✔ Selesai</span>
                                <br><small style="color:#999;"><?php echo date('H:i', strtotime($task['waktu_selesai'])); ?></small>
                            <?php else: ?>
                                <span style="color:orange;"><?php echo $task['status_task']; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <h4 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px;">C. Rincian Hasil Perhitungan Fisik</h4>
        <div class="content-table" style="box-shadow: none; border: 1px solid #ccc;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #333; color: white;">
                        <th style="padding: 10px;">No</th>
                        <th style="padding: 10px;">Barang</th>
                        <th style="padding: 10px;">Dihitung Oleh</th>
                        <th style="padding: 10px;">Lot / Batch</th>
                        <th style="padding: 10px; text-align: center;">Hasil Fisik</th>
                        <th style="padding: 10px;">Catatan Lapangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($data['logs'] as $log): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 8px; text-align: center;"><?php echo $no++; ?></td>
                        <td style="padding: 8px;">
                            <strong><?php echo htmlspecialchars($log['nama_barang']); ?></strong>
                            <br><small style="color: #666;"><?php echo htmlspecialchars($log['kode_barang']); ?></small>
                        </td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($log['counter_name']); ?></td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($log['lot_number'] ?? '-'); ?></td>
                        <td style="padding: 8px; text-align: center; font-size: 1.1em; font-weight: bold; background: #f9f9f9;">
                            <?php echo $log['stok_fisik']; ?> <?php echo $log['nama_satuan']; ?>
                        </td>
                        <td style="padding: 8px; font-style: italic; color: #555;">
                            <?php echo htmlspecialchars($log['catatan_staff'] ?? '-'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 60px; display: flex; justify-content: space-between; page-break-inside: avoid;">
            <div style="text-align: center; width: 200px;">
                <p>Dibuat Oleh,</p>
                <br><br><br>
                <p><strong>( <?php echo htmlspecialchars($p['creator_name']); ?> )</strong></p>
                <hr style="margin: 5px 0;">
                <p>Admin Gudang</p>
            </div>
            <div style="text-align: center; width: 200px;">
                <p>Disetujui Oleh,</p>
                <br><br><br>
                <p><strong>( <?php echo htmlspecialchars($p['finalizer_name']); ?> )</strong></p>
                <hr style="margin: 5px 0;">
                <p>Kepala Gudang / Supervisor</p>
            </div>
        </div>

    </div>
</main>

<style>
    @media print {
        body * { visibility: hidden; }
        .app-sidebar, .app-header, .no-print { display: none !important; }
        .app-content { margin: 0; padding: 0; overflow: visible; }
        .app-content > div { visibility: visible; border: none; box-shadow: none; }
        .app-content > div * { visibility: visible; }
        table { width: 100% !important; }
    }
</style>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>