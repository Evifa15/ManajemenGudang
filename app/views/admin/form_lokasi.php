<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['lokasi']) && $data['lokasi'] != null;
    $loc = $data['lokasi']; 
?>

<main class="app-content">
    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 800px; margin: 0 auto; padding: 25px;">
        
        <form action="<?php echo BASE_URL; ?>admin/processLokasi" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="lokasi_id" value="<?php echo $loc['lokasi_id']; ?>">
            <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <h4 style="color: #152e4d; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 8px;">
                    <i class="ph ph-map-pin" style="color: #f8c21a; margin-right: 5px;"></i> Informasi Lokasi
                </h4>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0; line-height: 1.4;">
                    Tentukan titik penyimpanan barang secara spesifik (Kode Unik, Nama Rak, dan Zona).
                </p>
            </div>

            <div style="padding: 0 5px;">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="kode_lokasi" style="font-weight: 600; color: #334155; margin-bottom: 6px;">
                            Kode Lokasi (Unik) <span style="color: red;">*</span>
                        </label>
                        <input type="text" id="kode_lokasi" name="kode_lokasi" class="form-control"
                               value="<?php echo $isEditMode ? htmlspecialchars($loc['kode_lokasi']) : ''; ?>" 
                               placeholder="Contoh: RAK-A1-01" required 
                               style="font-weight: bold; color: #152e4d; letter-spacing: 0.5px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="zona" style="font-weight: 600; color: #334155; margin-bottom: 6px;">
                            Zona / Area <span style="color: red;">*</span>
                        </label>
                        <input type="text" id="zona" name="zona" class="form-control"
                               value="<?php echo $isEditMode ? htmlspecialchars($loc['zona']) : ''; ?>" 
                               placeholder="Contoh: Gudang Depan, Area B" required>
                    </div>

                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nama_rak" style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        Nama Rak / Tempat <span style="color: red;">*</span>
                    </label>
                    <input type="text" id="nama_rak" name="nama_rak" 
                           class="form-control"
                           value="<?php echo $isEditMode ? htmlspecialchars($loc['nama_rak']) : ''; ?>" 
                           placeholder="Contoh: Rak Besi Tingkat 1, Lemari Pendingin" 
                           required>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="deskripsi" style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        Keterangan <span style="color: #94a3b8; font-weight: 400;">(Opsional)</span>
                    </label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" 
                              class="form-control"
                              style="line-height: 1.5; padding: 10px 15px;"
                              placeholder="Catatan tambahan..."><?php echo $isEditMode ? htmlspecialchars($loc['deskripsi'] ?? '') : ''; ?></textarea>
                </div>

            </div>
            
            <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: flex-end; gap: 15px;">
                <a href="<?php echo BASE_URL; ?>admin/masterDataConfig#tab-lokasi" class="btn btn-batal-custom" style="padding: 10px 20px;">
                    Batal
                </a>
                <button type="submit" class="btn btn-simpan-custom" style="padding: 10px 25px;">
                    <i class="ph ph-floppy-disk" style="font-size: 1.2rem;"></i> 
                    <?php echo $isEditMode ? 'Simpan Perubahan' : 'Simpan Lokasi'; ?>
                </button>
            </div>

        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>