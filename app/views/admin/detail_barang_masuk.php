<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
    $t = $data['transaksi'];
?>

<main class="app-content">
    
    <div class="content-header no-print">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk" class="btn" style="background: #6c757d; color: white;">&larr; Kembali</a>
            <h1>Detail Barang Masuk #<?php echo $t['transaction_id']; ?></h1>
        </div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Bukti</button>
    </div>

    <div style="display: flex; gap: 30px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-top: 20px; flex-wrap: wrap;">
        
        <div style="flex: 0 0 300px;"> <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; color: #007bff;">📦 Foto Produk (Referensi)</h4>
            
            <div style="border: 1px solid #e0e0e0; padding: 5px; border-radius: 8px; text-align: center; background: #fff; min-height: 250px; display: flex; align-items: center; justify-content: center;">
                <?php if (!empty($t['foto_barang'])): ?>
                    <img src="<?php echo BASE_URL . 'uploads/barang/' . $t['foto_barang']; ?>" 
                         alt="Foto Produk" 
                         style="max-width: 100%; height: auto; max-height: 300px; border-radius: 5px; cursor: zoom-in;"
                         onclick="window.open(this.src, '_blank')">
                <?php else: ?>
                    <div style="color: #999; font-style: italic; font-size: 0.9em;">
                        <span style="font-size: 50px; display: block; margin-bottom: 10px;">📦</span>
                        (Tidak ada foto produk di database)
                    </div>
                <?php endif; ?>
            </div>
            <div style="text-align: center; margin-top: 10px;">
                <small style="color: #666;">*Foto ini diambil dari Master Data Barang</small>
            </div>
        </div>

        <div style="flex: 1;">
            <h3 style="border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-bottom: 20px; color: #28a745;">
                Informasi Penerimaan Barang
            </h3>
            
            <table style="width: 100%; border-collapse: collapse; font-size: 1em;">
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-weight: bold; width: 180px; color: #555;">Tanggal Masuk</td>
                    <td style="padding: 12px; font-size: 1.1em;">
                        <?php echo date('d F Y, H:i', strtotime($t['created_at'])); ?> WIB
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-weight: bold; color: #555;">Barang</td>
                    <td style="padding: 12px;">
                        <strong style="font-size: 1.2em; color: #007bff;"><?php echo htmlspecialchars($t['nama_barang']); ?></strong>
                        <br><small style="color: #666;"><?php echo htmlspecialchars($t['kode_barang']); ?> | <?php echo htmlspecialchars($t['nama_merek']); ?></small>
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-weight: bold; color: #555;">Supplier</td>
                    <td style="padding: 12px;"><?php echo htmlspecialchars($t['nama_supplier'] ?? '-'); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #eee; background-color: #f0fdf4;">
                    <td style="padding: 12px; font-weight: bold; color: #155724;">Jumlah Diterima</td>
                    <td style="padding: 12px;">
                        <strong style="font-size: 1.4em; color: #155724;">
                            <?php echo number_format($t['jumlah'], 0, ',', '.'); ?> 
                        </strong>
                        <span style="color: #155724;"><?php echo htmlspecialchars($t['nama_satuan']); ?></span>
                    </td>
                </tr>
                
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-weight: bold; color: #555;">Detail Batch</td>
                    <td style="padding: 12px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px;">
                            <div>
                                <small style="display:block; color:#888;">Nomor Lot/Batch:</small>
                                <strong><?php echo htmlspecialchars($t['lot_number'] ?? '-'); ?></strong>
                            </div>
                            <div>
                                <small style="display:block; color:#888;">Tgl. Produksi:</small>
                                <span><?php echo $t['production_date'] ? date('d-m-Y', strtotime($t['production_date'])) : '-'; ?></span>
                            </div>
                            <div>
                                <small style="display:block; color:#888;">Tgl. Kedaluwarsa:</small>
                                <?php 
                                    $expStyle = 'color: #333;';
                                    if($t['exp_date']) {
                                        $days = (new DateTime($t['exp_date']))->diff(new DateTime())->days;
                                        if(new DateTime() > new DateTime($t['exp_date'])) $expStyle = 'color: red; font-weight:bold;'; // Expired
                                        elseif($days < 90) $expStyle = 'color: orange; font-weight:bold;'; // Warning
                                    }
                                ?>
                                <span style="<?php echo $expStyle; ?>">
                                    <?php echo $t['exp_date'] ? date('d-m-Y', strtotime($t['exp_date'])) : '-'; ?>
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-weight: bold; color: #555;">Penerima (Staff)</td>
                    <td style="padding: 12px;">
                        👤 <?php echo htmlspecialchars($t['staff_nama']); ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px; font-weight: bold; color: #555; vertical-align: top;">Keterangan</td>
                    <td style="padding: 12px; font-style: italic; color: #666;">
                        "<?php echo nl2br(htmlspecialchars($t['keterangan'] ?? '-')); ?>"
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-top: 20px; page-break-inside: avoid;">
        <h4 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; color: #555;">
            📎 Lampiran Bukti (Nota / Surat Jalan)
        </h4>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            
            <?php 
                // Logika untuk menangani JSON atau String tunggal
                $buktiList = [];
                if (!empty($t['bukti_foto'])) {
                    // Coba decode sebagai JSON
                    $decoded = json_decode($t['bukti_foto'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $buktiList = $decoded; // Format Baru (Array)
                    } else {
                        $buktiList[] = $t['bukti_foto']; // Format Lama (String)
                    }
                }
            ?>

            <?php if (!empty($buktiList)): ?>
                <?php foreach($buktiList as $index => $foto): 
                    $ext = pathinfo($foto, PATHINFO_EXTENSION);
                    $isPdf = strtolower($ext) == 'pdf';
                    $fileUrl = BASE_URL . 'uploads/bukti_transaksi/' . $foto;
                ?>
                    <div style="border: 1px solid #ddd; padding: 5px; border-radius: 8px; text-align: center; background: #f9f9f9;">
                        <a href="<?php echo $fileUrl; ?>" target="_blank">
                            <?php if ($isPdf): ?>
                                <div style="height: 200px; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #dc3545;">
                                    <span style="font-size: 50px;">📄</span>
                                    <span style="font-size: 0.9em; margin-top: 10px;">Dokumen PDF</span>
                                </div>
                            <?php else: ?>
                                <img src="<?php echo $fileUrl; ?>" 
                                     alt="Bukti Nota" 
                                     style="width: 100%; height: 200px; object-fit: cover; border-radius: 5px; cursor: zoom-in;">
                            <?php endif; ?>
                        </a>
                        <div style="margin-top: 10px; font-size: 0.85em; color: #666; padding-bottom: 5px;">
                            Lampiran #<?php echo $index + 1; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #f9f9f9; border: 2px dashed #ddd; border-radius: 8px; color: #999;">
                    <span style="font-size: 40px; display: block; margin-bottom: 10px;">📷</span>
                    Tidak ada bukti foto yang dilampirkan pada transaksi ini.
                </div>
            <?php endif; ?>

        </div>
    </div>

</main>

<style>
    @media print {
        .app-sidebar, .app-header, .no-print { display: none !important; }
        .app-content { margin: 0; padding: 0; }
        body { background: white; -webkit-print-color-adjust: exact; }
        .btn { display: none; }
    }
</style>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>