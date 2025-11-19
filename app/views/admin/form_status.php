<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['status']) && $data['status'] != null;
    $stat = $data['status']; // Akan jadi null jika mode 'Tambah'
?>

<main class="app-content">
    
    <div class="content-header">
        <h1><?php echo $isEditMode ? 'Edit Status' : 'Tambah Status Baru'; ?></h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processStatus" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="status_id" value="<?php echo $stat['status_id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nama_status">Nama Status (Unik)</label>
                <input type="text" id="nama_status" name="nama_status" 
                       value="<?php echo $isEditMode ? htmlspecialchars($stat['nama_status']) : ''; ?>"
                       placeholder="Misal: Tersedia, Rusak, Karantina" required>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4"
                          placeholder="Deskripsi singkat mengenai status ini (Opsional)"><?php echo $isEditMode ? htmlspecialchars($stat['deskripsi']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $isEditMode ? 'Update' : 'Simpan'; ?> Status</button>
                <a href="<?php echo BASE_URL; ?>admin/status" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>

</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>