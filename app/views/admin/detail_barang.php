<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
    $p = $data['product'];
?>

<main class="app-content">
    
    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: visible; padding: 30px;">
        
        <div class="detail-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px;">
            
            <div>
                <h1 style="margin: 0; font-size: 1.8rem; color: var(--primer-darkblue); line-height: 1.2; font-weight: 800;">
                    <?php echo htmlspecialchars($p['nama_barang']); ?>
                </h1>
                <div style="margin-top: 8px; font-size: 0.95rem; color: #64748b; display: flex; align-items: center; gap: 10px;">
                    <span style="background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-family: monospace; color: var(--primer-darkblue); font-weight: 700;">
                        <?php echo htmlspecialchars($p['kode_barang']); ?>
                    </span>
                    <span style="color: #cbd5e1;">|</span>
                    <span style="display: flex; align-items: center; gap: 5px;">
                        <i class="ph ph-tag" style="color: #f8c21a; font-size: 1.1rem;"></i> 
                        <?php echo htmlspecialchars($p['nama_merek'] ?? '-'); ?>
                    </span>
                </div>
            </div>
            
        </div>

        <div class="detail-layout-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
            
            <div class="left-column">
                
                <div style="margin-bottom: 35px;">
                    <h4 style="color: #152e4d; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 20px;">
                        <i class="ph ph-info" style="color: #f8c21a; margin-right: 8px;"></i> Informasi Dasar
                    </h4>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 15px 0; width: 35%; color: #64748b; font-weight: 600; border-bottom: 1px solid #f1f5f9;">Kategori</td>
                            <td style="padding: 15px 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="background: #f8fafc; padding: 6px 15px; border-radius: 20px; border: 1px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($p['nama_kategori'] ?? '-'); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 15px 0; color: #64748b; font-weight: 600; border-bottom: 1px solid #f1f5f9;">Satuan Unit</td>
                            <td style="padding: 15px 0; color: #152e4d; font-weight: 600; border-bottom: 1px solid #f1f5f9;">
                                <?php echo htmlspecialchars($p['nama_satuan'] ?? 'Pcs'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 15px 0; color: #64748b; font-weight: 600; border-bottom: 1px solid #f1f5f9;">Fitur & Status</td>
                            <td style="padding: 15px 0; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                    <?php if($p['bisa_dipinjam']): ?>
                                        <span style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                            <i class="ph ph-check-circle" style="font-weight: bold;"></i> Bisa Dipinjam
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                            <i class="ph ph-x-circle" style="font-weight: bold;"></i> Tidak Dipinjamkan
                                        </span>
                                    <?php endif; ?>

                                    <?php if($p['lacak_lot_serial']): ?>
                                        <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                            <i class="ph ph-barcode" style="font-weight: bold;"></i> Wajib Lot/Batch
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div>
                    <h4 style="color: #152e4d; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px;">
                        <i class="ph ph-text-align-left" style="color: #f8c21a; margin-right: 8px;"></i> Deskripsi
                    </h4>
                    <div style="background: #fcfcfc; padding: 20px; border-radius: 8px; border: 1px solid #f1f5f9; color: #334155; line-height: 1.6; font-size: 0.95rem;">
                        <?php 
                            if(!empty($p['deskripsi'])) {
                                echo nl2br(htmlspecialchars($p['deskripsi'])); 
                            } else {
                                echo '<span style="font-style: italic; color: #94a3b8;">Tidak ada deskripsi tambahan.</span>';
                            }
                        ?>
                    </div>
                </div>

            </div>

            <div class="right-column">
                
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                    <div style="padding: 12px 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #152e4d; font-size: 0.9rem;">
                        Foto Produk
                    </div>
                    <div style="padding: 20px; text-align: center;">
                        <div style="background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #f1f5f9; height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 15px;">
                            <?php 
                                $foto = !empty($p['foto_barang']) 
                                        ? BASE_URL . 'uploads/barang/' . $p['foto_barang'] 
                                        : 'https://via.placeholder.com/300?text=No+Image';
                                
                                if (!empty($p['foto_barang']) && !file_exists(APPROOT . '/../public/uploads/barang/' . $p['foto_barang'])) {
                                    $foto = 'https://via.placeholder.com/300?text=File+Not+Found';
                                }
                            ?>
                            <img src="<?php echo $foto; ?>" alt="Foto Barang" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px;">
                        </div>

                        <a href="<?php echo BASE_URL; ?>admin/cetakLabel/<?php echo $p['product_id']; ?>" 
                           class="btn btn-brand-dark" 
                           style="width: 100%; justify-content: center; padding: 10px; border-radius: 8px; font-size: 0.9rem;">
                            <i class="ph ph-printer" style="font-size: 1.1rem;"></i> Cetak Barcode
                        </a>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                    <div style="padding: 12px 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #152e4d; font-size: 0.9rem;">
                        Informasi Stok
                    </div>
                    <div style="padding: 20px;">
                        
                        <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #f0f9ff; border-radius: 8px; border: 1px solid #bae6fd;">
                            <span style="font-size: 0.8rem; color: #0369a1; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Total Stok Fisik</span>
                            <div style="display: flex; align-items: baseline; justify-content: center; gap: 5px;">
                                <strong style="font-size: 2.2rem; color: #0284c7; line-height: 1;"><?php echo (int)$p['stok_saat_ini']; ?></strong>
                                <span style="font-size: 0.9rem; color: #64748b; font-weight: 600;"><?php echo htmlspecialchars($p['nama_satuan'] ?? ''); ?></span>
                            </div>
                        </div>

                        <div style="margin-bottom: 12px; display: flex; justify-content: space-between; font-size: 0.9rem; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                            <span style="color: #64748b;">Lokasi Rak</span>
                            <strong style="color: #152e4d;">
                                <?php echo htmlspecialchars($p['kode_lokasi'] ?? 'Belum Diatur'); ?>
                            </strong>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                            <span style="color: #64748b;">Stok Minimum</span>
                            <strong style="color: #d97706;"><?php echo (int)$p['stok_minimum']; ?></strong>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

<style>
    @media (max-width: 900px) {
        .detail-layout-grid {
            grid-template-columns: 1fr !important;
            gap: 25px !important;
        }
        /* Balik urutan di mobile agar Foto tampil duluan */
        .right-column {
            order: -1;
        }
        
        .detail-header {
            flex-direction: column;
            align-items: stretch !important;
            gap: 15px;
        }
    }
</style>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>