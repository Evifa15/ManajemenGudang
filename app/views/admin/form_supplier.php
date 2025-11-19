<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['supplier']) && $data['supplier'] != null;
    $supplier = $data['supplier']; 
?>

<main class="app-content">
    
    <div class="content-header">
        <h1><?php echo $isEditMode ? 'Edit Supplier' : 'Tambah Supplier Baru'; ?></h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processSupplier" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="supplier_id" value="<?php echo $supplier['supplier_id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nama_supplier">Nama Supplier</label>
                <input type="text" id="nama_supplier" name="nama_supplier" 
                       value="<?php echo $isEditMode ? htmlspecialchars($supplier['nama_supplier']) : ''; ?>" 
                       placeholder="Misal: PT. Sinar Jaya Abadi" required>
            </div>

            <div class="form-group">
                <label for="kontak_person">Kontak Person</label>
                <input type="text" id="kontak_person" name="kontak_person" 
                       value="<?php echo $isEditMode ? htmlspecialchars($supplier['kontak_person']) : ''; ?>"
                       placeholder="Misal: Bp. Budi">
            </div>

            <div class="form-group">
                <label for="telepon">Telepon</label>
                <input type="text" id="telepon" name="telepon" 
                       value="<?php echo $isEditMode ? htmlspecialchars($supplier['telepon']) : ''; ?>"
                       placeholder="Misal: 08123456789">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo $isEditMode ? htmlspecialchars($supplier['email']) : ''; ?>"
                       placeholder="Misal: info@sinarjaya.com">
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" rows="4"
                          placeholder="Misal: Jl. Industri No. 10, Jakarta"><?php echo $isEditMode ? htmlspecialchars($supplier['alamat']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $isEditMode ? 'Update' : 'Simpan'; ?> Supplier</button>
                <a href="<?php echo BASE_URL; ?>admin/suppliers" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>