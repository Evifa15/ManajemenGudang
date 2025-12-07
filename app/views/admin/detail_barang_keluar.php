<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
    
    // Ambil data transaksi dari key 'transaksi'
    $d = $data['transaksi']; 
?>

<main class="app-content">
    
    <div class="detail-main-layout">
        
        <div class="col-left" style="flex: 1; min-width: 300px;">
            <div class="card detail-card-base">
                <div class="detail-card-header">
                    <i class="ph ph-file-text" style="color: #152e4d; font-size: 1.2rem;"></i> Data Transaksi
                </div>
                <div class="detail-card-body">
                    
                    <div class="info-block">
                        <label class="info-label">ID TRANSAKSI</label>
                        <span class="info-value-mono-badge">
                            #<?php echo $d['transaction_id']; ?>
                        </span>
                    </div>

                    <div class="info-block">
                        <label class="info-label">WAKTU KELUAR</label>
                        <div class="info-value-main">
                            <?php echo date('d F Y', strtotime($d['created_at'])); ?> 
                        </div>
                        <div class="info-value-sub">
                            Pukul <?php echo date('H:i', strtotime($d['created_at'])); ?> WIB
                        </div>
                    </div>

                    <div class="info-block">
                        <label class="info-label">DIAMBIL OLEH</label>
                        <div class="staff-info-item">
                            <div class="staff-initial-badge">
                                <?php echo strtoupper(substr($d['staff_nama'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="info-value-main" style="font-size: 1rem;">
                                    <?php echo htmlspecialchars($d['staff_nama']); ?>
                                </div>
                                <div class="info-value-sub" style="margin-top: 0;">Staff Gudang</div>
                            </div>
                        </div>
                    </div>

                    <div class="info-block" style="margin-bottom: 0;">
                        <label class="info-label">KETERANGAN / TUJUAN</label>
                        <div class="keterangan-box">
                            <i class="ph ph-info" style="font-size: 1.2rem; margin-top: 2px;"></i>
                            <div>"<?php echo htmlspecialchars($d['keterangan']); ?>"</div>
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <label class="info-label">BUKTI FILE (SJP / SERAH TERIMA)</label>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <?php if (!empty($d['bukti_foto_array'])): ?>
                                <?php foreach($d['bukti_foto_array'] as $file): 
                                    $filePath = BASE_URL . 'uploads/bukti_transaksi/' . $file;
                                    $isImage = in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']);
                                ?>
                                <a href="<?php echo $filePath; ?>" target="_blank" 
                                   class="btn btn-sm" 
                                   style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-weight: 600;">
                                    <i class="ph ph-file-<?php echo $isImage ? 'image' : 'pdf'; ?>"></i> Lihat File
                                </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-style: italic;">Tidak ada bukti file diupload.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-right" style="flex: 2; min-width: 400px;">
            <div class="card detail-card-base">
                <div class="detail-card-header">
                    <i class="ph ph-package" style="color: #152e4d; font-size: 1.2rem;"></i> Detail Barang Keluar
                </div>
                <div class="detail-card-body">
                    
                    <div class="item-detail-header">
                        
                        <div class="item-img-wrapper">
                            <?php if(!empty($d['foto_barang']) && file_exists('uploads/barang/'.$d['foto_barang'])): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/barang/<?php echo $d['foto_barang']; ?>" alt="Produk" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="ph ph-cube" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-info-text">
                            <div class="kode">
                                KODE: <?php echo htmlspecialchars($d['kode_barang']); ?>
                            </div>
                            <h3>
                                <?php echo htmlspecialchars($d['nama_barang']); ?>
                            </h3>
                            <div class="item-badges-group">
                                <span class="item-badge-pill">
                                    <?php echo htmlspecialchars($d['nama_kategori']); ?>
                                </span>
                                <span class="item-badge-pill">
                                    <?php echo htmlspecialchars($d['nama_merek']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="qty-large-wrapper" style="background: #fef2f2; border-color: #fecaca;">
                            <div class="qty-label" style="color: #dc2626;">JUMLAH KELUAR</div>
                            <div class="qty-value" style="color: #ef4444;">
                                -<?php echo $d['jumlah']; ?>
                            </div>
                            <div class="info-value-sub" style="color: #ef4444; font-weight: 600; margin-top: 5px;"><?php echo htmlspecialchars($d['nama_satuan']); ?></div>
                        </div>
                    </div>

                    <div style="border-top: 1px dashed #cbd5e1; margin-bottom: 25px;"></div>

                    <div class="detail-grid-info">
                        
                        <div class="info-block" style="margin-bottom: 0;">
                            <label class="info-label">LOT / BATCH NUMBER</label>
                            <div class="batch-number-display">
                                <i class="ph ph-barcode" style="margin-right: 5px;"></i>
                                <?php echo !empty($d['lot_number']) ? htmlspecialchars($d['lot_number']) : '-'; ?>
                            </div>
                        </div>

                        <div class="info-block" style="margin-bottom: 0;">
                            <label class="info-label">TANGGAL PRODUKSI</label>
                            <div class="info-value-main">
                                <?php echo !empty($d['production_date']) ? date('d F Y', strtotime($d['production_date'])) : '-'; ?>
                            </div>
                        </div>

                        <div class="info-block" style="margin-bottom: 0;">
                            <label class="info-label">TANGGAL KEDALUWARSA</label>
                            <?php if (!empty($d['exp_date'])): ?>
                                <div class="info-value-main exp-date-warning">
                                    <i class="ph ph-warning-circle"></i>
                                    <?php echo date('d F Y', strtotime($d['exp_date'])); ?>
                                </div>
                            <?php else: ?>
                                <div class="info-value-main" style="color: #64748b; font-weight: 600;">- (Tidak Ada Exp)</div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>