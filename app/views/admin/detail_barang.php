<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    
    <div class="detail-grid-3" style="margin-top: 20px;">

        <div class="detail-card">
            <h3><i class="ph ph-identification-card" style="color:#0ea5e9;"></i> Informasi Dasar</h3>
            
            <div class="info-group" style="margin-bottom:15px;">
                <label style="font-size:0.85rem; color:#64748b; display:block;">Kode Barang</label>
                <div style="font-family:monospace; font-size:1.1rem; font-weight:600; color:#1e293b;">
                    <?php echo htmlspecialchars($data['product']['kode_barang']); ?>
                </div>
            </div>

            <div class="info-group" style="margin-bottom:15px;">
                <label style="font-size:0.85rem; color:#64748b; display:block;">Nama Barang</label>
                <div style="font-size:1rem; font-weight:600; color:#1e293b;">
                    <?php echo htmlspecialchars($data['product']['nama_barang']); ?>
                </div>
            </div>

            <div class="info-group" style="margin-bottom:15px;">
                <label style="font-size:0.85rem; color:#64748b; display:block;">Kategori</label>
                <div style="font-size:1rem; color:#1e293b;">
                    <?php echo htmlspecialchars($data['product']['nama_kategori'] ?? '-'); ?>
                </div>
            </div>

            <div class="info-group" style="margin-bottom:15px;">
                <label style="font-size:0.85rem; color:#64748b; display:block;">Merek</label>
                <div style="font-size:1rem; color:#1e293b;">
                    <?php echo htmlspecialchars($data['product']['nama_merek'] ?? '-'); ?>
                </div>
            </div>

            <div class="info-group">
                <label style="font-size:0.85rem; color:#64748b; display:block;">Deskripsi</label>
                <div style="font-size:0.95rem; color:#334155; line-height:1.5; background:#f8fafc; padding:10px; border-radius:8px; border:1px solid #f1f5f9;">
                    <?php echo !empty($data['product']['deskripsi']) ? nl2br(htmlspecialchars($data['product']['deskripsi'])) : '<span style="color:#94a3b8; font-style:italic;">Tidak ada deskripsi</span>'; ?>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <h3><i class="ph ph-package" style="color:#f59e0b;"></i> Inventaris & Stok</h3>

            <div class="grid-2-col" style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="info-group">
                    <label style="font-size:0.85rem; color:#64748b;">Stok Saat Ini</label>
                    <div style="font-size:1.5rem; font-weight:700; color:#152e4d;">
                        <?php echo (int)$data['product']['stok_saat_ini']; ?>
                        <span style="font-size:0.9rem; font-weight:400; color:#64748b;"><?php echo htmlspecialchars($data['product']['nama_satuan'] ?? 'Pcs'); ?></span>
                    </div>
                </div>

                <div class="info-group">
                    <label style="font-size:0.85rem; color:#64748b;">Stok Minimum</label>
                    <div style="font-size:1.5rem; font-weight:700; color:#ef4444;">
                        <?php echo (int)$data['product']['stok_minimum']; ?>
                    </div>
                </div>
            </div>

            <hr style="border:0; border-top:1px dashed #e2e8f0; margin:15px 0;">

            <div class="info-group" style="margin-bottom:15px;">
                <label style="font-size:0.85rem; color:#64748b; display:block; margin-bottom:5px;">Status Kesehatan Stok</label>
                <?php 
                    $stok = (int)$data['product']['stok_saat_ini'];
                    $min = (int)$data['product']['stok_minimum'];
                    if($stok == 0) {
                        echo '<span style="background:#fee2e2; color:#991b1b; padding:6px 12px; border-radius:20px; font-weight:600; font-size:0.9rem; display:inline-flex; align-items:center; gap:5px;"><i class="ph ph-x-circle"></i> Stok Habis</span>';
                    } elseif($stok <= $min) {
                        echo '<span style="background:#fef3c7; color:#92400e; padding:6px 12px; border-radius:20px; font-weight:600; font-size:0.9rem; display:inline-flex; align-items:center; gap:5px;"><i class="ph ph-warning"></i> Menipis (Perlu Restock)</span>';
                    } else {
                        echo '<span style="background:#dcfce7; color:#166534; padding:6px 12px; border-radius:20px; font-weight:600; font-size:0.9rem; display:inline-flex; align-items:center; gap:5px;"><i class="ph ph-check-circle"></i> Stok Aman</span>';
                    }
                ?>
            </div>

            <div class="info-group">
                <label style="font-size:0.85rem; color:#64748b; display:block;">Lokasi Penyimpanan</label>
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="ph ph-map-pin" style="color:#64748b;"></i>
                    <span style="font-weight:600; color:#1e293b;">
                        <?php echo htmlspecialchars($data['product']['kode_lokasi'] ?? '-'); ?> 
                    </span>
                    <span style="color:#64748b;">(<?php echo htmlspecialchars($data['product']['nama_rak'] ?? 'Belum diset'); ?>)</span>
                </div>
            </div>
        </div>

        <div class="detail-card photo-section" style="display:flex; flex-direction:column; align-items:center; text-align:center;">
            <h3><i class="ph ph-image" style="color:#8b5cf6;"></i> Foto Produk</h3>
            
            <div style="width:100%; height:250px; background:#f1f5f9; border-radius:10px; overflow:hidden; display:flex; align-items:center; justify-content:center; border:1px dashed #cbd5e1; margin-bottom:15px;">
                <?php if (!empty($data['product']['foto_barang'])): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/barang/<?php echo $data['product']['foto_barang']; ?>" 
                         alt="Foto Barang" 
                         style="width:100%; height:100%; object-fit:cover; transition: transform 0.3s ease;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'">
                <?php else: ?>
                    <div style="color:#94a3b8; display:flex; flex-direction:column; align-items:center;">
                        <i class="ph ph-image-broken" style="font-size:3rem; margin-bottom:10px;"></i>
                        <span>Tidak ada foto</span>
                    </div>
                <?php endif; ?>
            </div>

            <p style="font-size:0.8rem; color:#64748b; line-height:1.4;">
                Format: JPG/PNG <br> 
                Pastikan foto terlihat jelas untuk memudahkan identifikasi.
            </p>
        </div>

    </div>

</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>