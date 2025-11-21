<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['kategori']) && $data['kategori'] != null;
    $kat = $data['kategori']; 
?>

<main class="app-content">
    
    <div class="content-header">
        
        <h1><?php echo $isEditMode ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?></h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processKategori" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="kategori_id" value="<?php echo $kat['kategori_id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nama_kategori">Nama Kategori (Unik)</label>
                <input type="text" id="nama_kategori" name="nama_kategori" 
                       value="<?php echo $isEditMode ? htmlspecialchars($kat['nama_kategori']) : ''; ?>" 
                       placeholder="Misal: Sabun Mandi, Minuman, Makanan Ringan" required>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" 
                          placeholder="Deskripsi singkat mengenai kategori ini (Opsional)"><?php echo $isEditMode ? htmlspecialchars($kat['deskripsi']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $isEditMode ? 'Update' : 'Simpan'; ?> Kategori</button>
                <a href="<?php echo BASE_URL; ?>admin/masterDataConfig#tab-kategori" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>