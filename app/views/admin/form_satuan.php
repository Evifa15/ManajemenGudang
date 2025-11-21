<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['satuan']) && $data['satuan'] != null;
    $sat = $data['satuan']; // Akan jadi null jika mode 'Tambah'
?>

<main class="app-content">
    
    <div class="content-header">
        
        <h1><?php echo $isEditMode ? 'Edit Satuan' : 'Tambah Satuan Baru'; ?></h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processSatuan" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="satuan_id" value="<?php echo $sat['satuan_id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nama_satuan">Nama Satuan (Unik)</label>
                <input type="text" id="nama_satuan" name="nama_satuan" 
                       value="<?php echo $isEditMode ? htmlspecialchars($sat['nama_satuan']) : ''; ?>"
                       placeholder="Misal: Pieces, Box, Karton, Liter" required>
            </div>
            
            <div class="form-group">
                <label for="singkatan">Singkatan</label>
                <input type="text" id="singkatan" name="singkatan" 
                       value="<?php echo $isEditMode ? htmlspecialchars($sat['singkatan']) : ''; ?>"
                       placeholder="Misal: pcs, box, krt, L (Opsional)">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $isEditMode ? 'Update' : 'Simpan'; ?> Satuan</button>
                <a href="<?php echo BASE_URL; ?>admin/masterDataConfig#tab-satuan" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>

</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>