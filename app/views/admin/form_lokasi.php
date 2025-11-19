<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $isEditMode = isset($data['lokasi']) && $data['lokasi'] != null;
    $lok = $data['lokasi']; 
?>

<main class="app-content">
    
    <div class="content-header">
        <h1><?php echo $isEditMode ? 'Edit Lokasi' : 'Tambah Lokasi Baru'; ?></h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processLokasi" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="lokasi_id" value="<?php echo $lok['lokasi_id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="kode_lokasi">Kode Lokasi (Unik)</label>
                <input type="text" id="kode_lokasi" name="kode_lokasi" 
                       value="<?php echo $isEditMode ? htmlspecialchars($lok['kode_lokasi']) : ''; ?>" 
                       placeholder="Contoh: A1-01" required>
            </div>

            <div class="form-group">
                <label for="nama_rak">Nama Rak / Area</label>
                <input type="text" id="nama_rak" name="nama_rak" 
                       value="<?php echo $isEditMode ? htmlspecialchars($lok['nama_rak']) : ''; ?>" 
                       placeholder="Contoh: Rak Besi A-1 Atas" required>
            </div>

            <div class="form-group">
                <label for="zona">Zona</label>
                <input type="text" id="zona" name="zona" 
                       value="<?php echo $isEditMode ? htmlspecialchars($lok['zona']) : ''; ?>"
                       placeholder="Contoh: Gudang Kering, Area Karantina">
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4"
                          placeholder="Deskripsi tambahan tentang lokasi (Opsional)"><?php echo $isEditMode ? htmlspecialchars($lok['deskripsi']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $isEditMode ? 'Update' : 'Simpan'; ?> Lokasi</button>
                <a href="<?php echo BASE_URL; ?>admin/lokasi" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>