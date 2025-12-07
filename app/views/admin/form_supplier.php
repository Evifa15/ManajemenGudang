<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    // Cek Mode Edit atau Tambah
    $isEditMode = isset($data['supplier']) && $data['supplier'] != null;
    $sup = $data['supplier']; 
?>

<main class="app-content">
    
    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); max-width: 800px; margin: 0 auto; padding: 30px; border-radius: 12px;">
        
        <form action="<?php echo BASE_URL; ?>admin/processSupplier" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="supplier_id" value="<?php echo $sup['supplier_id']; ?>">
            <?php endif; ?>

            <div style="margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
                <h3 style="color: #152e4d; font-weight: 800; margin-bottom: 5px; display: flex; align-items: center; gap: 10px;">
                    <i class="ph ph-truck" style="color: #f8c21a; font-size: 1.8rem;"></i> 
                    <?php echo $isEditMode ? 'Edit Supplier' : 'Tambah Supplier Baru'; ?>
                </h3>
                <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                    Lengkapi data pemasok barang untuk memudahkan proses restock.
                </p>
            </div>

            <div style="padding: 0 5px;">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nama_supplier" style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;">
                        Nama Perusahaan / Supplier <span style="color: red;">*</span>
                    </label>
                    <input type="text" id="nama_supplier" name="nama_supplier" 
                           class="form-control"
                           value="<?php echo $isEditMode ? htmlspecialchars($sup['nama_supplier']) : ''; ?>" 
                           placeholder="Contoh: PT. Distribusi Sabun Jaya" 
                           required
                           style="font-size: 1.1rem; padding: 12px 15px; font-weight: 600; color: #152e4d; background-color: #f8fafc; border: 1px solid #cbd5e1;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    
                    <div class="form-group">
                        <label for="kontak_person" style="font-weight: 600; color: #334155; margin-bottom: 8px;">
                            <i class="ph ph-user"></i> Kontak Person (PIC)
                        </label>
                        <input type="text" id="kontak_person" name="kontak_person" class="form-control"
                               placeholder="Contoh: Bpk. Budi"
                               style="padding: 10px;"
                               value="<?php echo $isEditMode ? htmlspecialchars($sup['kontak_person'] ?? '') : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="telepon" style="font-weight: 600; color: #334155; margin-bottom: 8px;">
                            <i class="ph ph-phone"></i> No. Telepon / WA
                        </label>
                        <input type="text" id="telepon" name="telepon" class="form-control"
                               placeholder="0812xxxxxxx"
                               style="padding: 10px;"
                               value="<?php echo $isEditMode ? htmlspecialchars($sup['telepon'] ?? '') : ''; ?>">
                    </div>

                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="email" style="font-weight: 600; color: #334155; margin-bottom: 8px;">
                        <i class="ph ph-envelope"></i> Email (Opsional)
                    </label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="admin@supplier.com"
                           style="padding: 10px;"
                           value="<?php echo $isEditMode ? htmlspecialchars($sup['email'] ?? '') : ''; ?>">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="alamat" style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;">
                        <i class="ph ph-map-pin"></i> Alamat Lengkap
                    </label>
                    <textarea id="alamat" name="alamat" rows="3" 
                              class="form-control"
                              style="line-height: 1.5; padding: 10px 15px;"
                              placeholder="Alamat kantor atau gudang supplier..."><?php echo $isEditMode ? htmlspecialchars($sup['alamat'] ?? '') : ''; ?></textarea>
                </div>

            </div>
            
            <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: flex-end; gap: 15px;">
                
                <a href="<?php echo BASE_URL; ?>admin/masterDataConfig#tab-supplier" class="btn-batal-custom">
                    Batal
                </a>
                
                <button type="submit" class="btn-simpan-custom">
                    <i class="ph ph-floppy-disk" style="font-size: 1.2rem;"></i> 
                    <?php echo $isEditMode ? 'Simpan Perubahan' : 'Simpan Kategori'; ?>
                </button>

            </div>

        </form>
    </div>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>