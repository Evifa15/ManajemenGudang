<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['supplier']) && $data['supplier'] != null;
    $sup = $data['supplier']; 
?>

<main class="app-content">
    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 800px; margin: 0 auto; padding: 25px;">
        
        <form action="<?php echo BASE_URL; ?>admin/processSupplier" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="supplier_id" value="<?php echo $sup['supplier_id']; ?>">
            <?php endif; ?>

            <div style="margin-bottom: 20px;">
                <h4 style="color: #152e4d; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 8px;">
                    <i class="ph ph-truck" style="color: #f8c21a; margin-right: 5px;"></i> Informasi Supplier
                </h4>
                <p style="color: #64748b; font-size: 0.9rem; margin: 0; line-height: 1.4;">
                    Data pemasok barang untuk memudahkan pemesanan ulang (Restock).
                </p>
            </div>

            <div style="padding: 0 5px;">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nama_supplier" style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        Nama Perusahaan / Supplier <span style="color: red;">*</span>
                    </label>
                    <input type="text" id="nama_supplier" name="nama_supplier" 
                           class="form-control"
                           value="<?php echo $isEditMode ? htmlspecialchars($sup['nama_supplier']) : ''; ?>" 
                           placeholder="Contoh: PT. Distribusi Sabun Jaya" 
                           required
                           style="font-size: 1rem; padding: 10px 15px; font-weight: 600; color: #152e4d; background-color: #f8fafc;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="kontak_person" style="font-weight: 600; color: #334155; margin-bottom: 6px;">Kontak Person</label>
                        <input type="text" id="kontak_person" name="kontak_person" class="form-control"
                               placeholder="Nama Sales / PIC"
                               value="<?php echo $isEditMode ? htmlspecialchars($sup['kontak_person'] ?? '') : ''; ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="telepon" style="font-weight: 600; color: #334155; margin-bottom: 6px;">No. Telepon / HP</label>
                        <input type="text" id="telepon" name="telepon" class="form-control"
                               placeholder="0812..."
                               value="<?php echo $isEditMode ? htmlspecialchars($sup['telepon'] ?? '') : ''; ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="email" style="font-weight: 600; color: #334155; margin-bottom: 6px;">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="email@supplier.com"
                               value="<?php echo $isEditMode ? htmlspecialchars($sup['email'] ?? '') : ''; ?>">
                    </div>

                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="alamat" style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        Alamat Lengkap
                    </label>
                    <textarea id="alamat" name="alamat" rows="3" 
                              class="form-control"
                              style="line-height: 1.5; padding: 10px 15px;"
                              placeholder="Alamat kantor atau gudang supplier..."><?php echo $isEditMode ? htmlspecialchars($sup['alamat'] ?? '') : ''; ?></textarea>
                </div>

            </div>
            
            <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: flex-end; gap: 15px;">
                <a href="<?php echo BASE_URL; ?>admin/masterDataConfig#tab-suppliers" class="btn btn-batal-custom" style="padding: 10px 20px;">
                    Batal
                </a>
                <button type="submit" class="btn btn-simpan-custom" style="padding: 10px 25px;">
                    <i class="ph ph-floppy-disk" style="font-size: 1.2rem;"></i> 
                    <?php echo $isEditMode ? 'Simpan Perubahan' : 'Simpan Supplier'; ?>
                </button>
            </div>

        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>