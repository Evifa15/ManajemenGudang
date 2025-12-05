<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['merek']) && $data['merek'] != null;
    $mrk = $data['merek']; 
?>

<main class="app-content">
    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 800px; margin: 0 auto; padding: 25px;">
        
        <form action="<?php echo BASE_URL; ?>admin/processMerek" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="merek_id" value="<?php echo $mrk['merek_id']; ?>">
            <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <h4 style="color: #152e4d; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 8px;">
                    <i class="ph ph-tag" style="color: #f8c21a; margin-right: 5px;"></i> Informasi Merek
                </h4>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0; line-height: 1.4;">
                    Merek (Brand) digunakan untuk mengelompokkan produk berdasarkan pabrikan atau label dagang.
                </p>
            </div>

            <div style="padding: 0 5px;">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nama_merek" style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        Nama Merek <span style="color: red;">*</span>
                    </label>
                    <input type="text" id="nama_merek" name="nama_merek" 
                           class="form-control"
                           value="<?php echo $isEditMode ? htmlspecialchars($mrk['nama_merek']) : ''; ?>" 
                           placeholder="Contoh: Unilever, Indofood, Wings, Nestle" 
                           required
                           style="font-size: 1rem; padding: 10px 15px; font-weight: 600; color: #152e4d; background-color: #f8fafc;">
                    <small style="color: #94a3b8; font-size: 0.8rem; margin-top: 4px; display: block;">
                        *Nama merek harus unik.
                    </small>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="deskripsi" style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        Deskripsi / Keterangan <span style="color: #94a3b8; font-weight: 400;">(Opsional)</span>
                    </label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" 
          class="form-control"
          style="line-height: 1.5; padding: 10px 15px;"
          placeholder="Tambahkan catatan opsional mengenai merek ini..."><?php echo $isEditMode ? htmlspecialchars($mrk['deskripsi'] ?? '') : ''; ?></textarea>
                </div>

            </div>
            
            <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: flex-end; gap: 15px;">
                
                <a href="<?php echo BASE_URL; ?>admin/masterDataConfig#tab-merek" class="btn btn-batal-custom" style="padding: 10px 20px;">
                    Batal
                </a>
                
                <button type="submit" class="btn btn-simpan-custom" style="padding: 10px 25px;">
                    <i class="ph ph-floppy-disk" style="font-size: 1.2rem;"></i> 
                    <?php echo $isEditMode ? 'Simpan Perubahan' : 'Simpan Merek'; ?>
                </button>

            </div>

        </form>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>