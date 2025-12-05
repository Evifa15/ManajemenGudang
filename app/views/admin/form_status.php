<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['status']) && $data['status'] != null;
    $sts = $data['status']; 
?>

<main class="app-content">
    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 800px; margin: 0 auto; padding: 25px;">
        
        <form action="<?php echo BASE_URL; ?>admin/processStatus" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="status_id" value="<?php echo $sts['status_id']; ?>">
            <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <h4 style="color: #152e4d; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 8px;">
                    <i class="ph ph-info" style="color: #f8c21a; margin-right: 5px;"></i> Informasi Status
                </h4>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0; line-height: 1.4;">
                    Label status untuk menandai kondisi barang (Contoh: Baik, Rusak, Kadaluwarsa).
                </p>
            </div>

            <div style="padding: 0 5px;">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nama_status" style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        Nama Status <span style="color: red;">*</span>
                    </label>
                    <input type="text" id="nama_status" name="nama_status" 
                           class="form-control"
                           value="<?php echo $isEditMode ? htmlspecialchars($sts['nama_status']) : ''; ?>" 
                           placeholder="Contoh: Baik, Rusak Ringan, Expired" 
                           required
                           style="font-size: 1rem; padding: 10px 15px; font-weight: 600; color: #152e4d; background-color: #f8fafc;">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="deskripsi" style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        Keterangan <span style="color: #94a3b8; font-weight: 400;">(Opsional)</span>
                    </label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" 
                              class="form-control"
                              style="line-height: 1.5; padding: 10px 15px;"
                              placeholder="Deskripsi singkat mengenai kondisi ini..."><?php echo $isEditMode ? htmlspecialchars($sts['deskripsi']) : ''; ?></textarea>
                </div>

            </div>
            
            <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: flex-end; gap: 15px;">
                <a href="<?php echo BASE_URL; ?>admin/masterDataConfig#tab-status" class="btn btn-batal-custom" style="padding: 10px 20px;">
                    Batal
                </a>
                <button type="submit" class="btn btn-simpan-custom" style="padding: 10px 25px;">
                    <i class="ph ph-floppy-disk" style="font-size: 1.2rem;"></i> 
                    <?php echo $isEditMode ? 'Simpan Perubahan' : 'Simpan Status'; ?>
                </button>
            </div>

        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>