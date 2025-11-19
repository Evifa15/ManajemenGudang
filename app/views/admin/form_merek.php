<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['merek']) && $data['merek'] != null;
    $mrk = $data['merek']; // Akan jadi null jika mode 'Tambah'
?>

<main class="app-content">
    
    <div class="content-header">
        <h1><?php echo $isEditMode ? 'Edit Merek' : 'Tambah Merek Baru'; ?></h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processMerek" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="merek_id" value="<?php echo $mrk['merek_id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nama_merek">Nama Merek (Unik)</label>
                <input type="text" id="nama_merek" name="nama_merek" 
                       value="<?php echo $isEditMode ? htmlspecialchars($mrk['nama_merek']) : ''; ?>"
                       placeholder="Misal: Unilever, P&G, Wings" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $isEditMode ? 'Update' : 'Simpan'; ?> Merek</button>
                <a href="<?php echo BASE_URL; ?>admin/merek" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>

</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>