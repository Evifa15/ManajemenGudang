<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
    $p = $data['product'];
?>

<main class="app-content">
    <div class="content-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>admin/barang" class="btn" style="background: #6c757d; color: white;">&larr; Kembali</a>
            <h1>Detail Barang: <?php echo htmlspecialchars($p['nama_barang']); ?></h1>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>admin/editBarang/<?php echo $p['product_id']; ?>" class="btn btn-warning">✏️ Edit Barang</a>
        </div>
    </div>

    <div style="display: flex; gap: 30px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        
        <div style="flex: 1; max-width: 300px;">
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 8px; text-align: center;">
                <?php 
                    $foto = !empty($p['foto_barang']) 
                            ? BASE_URL . 'uploads/barang/' . $p['foto_barang'] 
                            : 'https://via.placeholder.com/300?text=No+Image';
                ?>
                <img src="<?php echo $foto; ?>" alt="Foto Barang" style="width: 100%; height: auto; border-radius: 5px;">
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="<?php echo BASE_URL; ?>admin/cetakLabel/<?php echo $p['product_id']; ?>" class="btn btn-info" style="width: 100%; display: block;">
                    🖨️ Cetak Label Barcode
                </a>
            </div>
        </div>

        <div style="flex: 2;">
            <h3 style="border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; color: #007bff;">Informasi Produk</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold; width: 180px;">Kode Barang</td>
                    <td style="padding: 10px; font-size: 1.2em; letter-spacing: 1px;"><?php echo $p['kode_barang']; ?></td>
                </tr>
                
                <tr style="border-bottom: 1px solid #eee; background-color: #f8f9fa;">
                    <td style="padding: 10px; font-weight: bold;">📍 Lokasi Rak</td>
                    <td style="padding: 10px; font-weight: bold; color: #333;">
                        <?php echo htmlspecialchars($p['kode_lokasi'] ?? 'Belum diatur'); ?>
                    </td>
                </tr>

                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">Kategori</td>
                    <td style="padding: 10px;"><?php echo $p['nama_kategori'] ?? '-'; ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">Merek</td>
                    <td style="padding: 10px;"><?php echo $p['nama_merek'] ?? '-'; ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">Satuan</td>
                    <td style="padding: 10px;"><?php echo $p['nama_satuan'] ?? '-'; ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">Stok Saat Ini</td>
                    <td style="padding: 10px;">
                        <strong style="font-size: 1.5em; color: <?php echo ($p['stok_saat_ini'] <= $p['stok_minimum']) ? '#dc3545' : '#198754'; ?>;">
                            <?php echo (int)$p['stok_saat_ini']; ?>
                        </strong>
                    </td>
                </tr>

                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold;">⚠️ Stok Minimum</td>
                    <td style="padding: 10px;">
                        <?php echo (int)$p['stok_minimum']; ?> 
                        <small style="color:#666; margin-left: 5px;">(Batas peringatan restock)</small>
                    </td>
                </tr>

                 <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px; font-weight: bold; vertical-align: top;">Deskripsi</td>
                    <td style="padding: 10px; color: #555; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($p['deskripsi'])); ?></td>
                </tr>
            </table>

            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border: 1px solid #e9ecef;">
                <strong>Fitur Aktif:</strong>
                <ul style="margin-top: 5px; margin-left: 20px; margin-bottom: 0;">
                    <li><?php echo ($p['bisa_dipinjam']) ? '✅ Bisa Dipinjam (Aset)' : '❌ Tidak Bisa Dipinjam (Habis Pakai)'; ?></li>
                    <li><?php echo ($p['lacak_lot_serial']) ? '✅ Wajib Input Lot/Batch' : '❌ Tidak Lacak Lot'; ?></li>
                </ul>
            </div>
        </div>

    </div>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>