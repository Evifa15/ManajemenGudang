<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    // Cek apakah ini mode 'Edit' (jika $data['barang'] ada isinya)
    $isEditMode = isset($data['barang']) && $data['barang'] != null;
    $brg = $data['barang']; 
?>

<main class="app-content">
    
    <div class="content-header">
        <h1><?php echo $isEditMode ? 'Edit Barang' : 'Tambah Barang Baru'; ?></h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processBarang" method="POST" enctype="multipart/form-data">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="product_id" value="<?php echo $brg['product_id']; ?>">
                <input type="hidden" name="foto_lama" value="<?php echo $brg['foto_barang']; ?>">
            <?php endif; ?>

            <div style="display: flex; gap: 30px; align-items: flex-start;">
                
                <div style="flex: 2;">
                    <fieldset>
                        <legend>Informasi Dasar</legend>
                        
                        <div class="form-group">
                            <label for="kode_barang">Kode Barang (Unik)</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="kode_barang" name="kode_barang" 
                                    value="<?php echo $isEditMode ? htmlspecialchars($brg['kode_barang']) : ''; ?>" 
                                    placeholder="Ketik manual atau klik Auto" 
                                    style="flex: 1;" required>
                                
                                <button type="button" id="btnAutoCode" class="btn btn-info" 
                                        style="background-color: #17a2b8; border: none; color: white;"
                                        title="Buat Kode Otomatis">
                                    ⚡ Auto
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" id="nama_barang" name="nama_barang" 
                                value="<?php echo $isEditMode ? htmlspecialchars($brg['nama_barang']) : ''; ?>" 
                                placeholder="Contoh: Sabun Mandi Lifebuoy 100gr" required>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Lengkap</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3" 
                                    placeholder="Deskripsi, spesifikasi, atau catatan khusus"><?php echo $isEditMode ? htmlspecialchars($brg['deskripsi']) : ''; ?></textarea>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Atribut Barang</legend>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="kategori_id">Kategori</label>
                                <select id="kategori_id" name="kategori_id" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach($data['kategori'] as $kat): ?>
                                        <option value="<?php echo $kat['kategori_id']; ?>" <?php if($isEditMode && $brg['kategori_id'] == $kat['kategori_id']) echo 'selected'; ?>>
                                            <?php echo $kat['nama_kategori']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="merek_id">Merek</label>
                                <select id="merek_id" name="merek_id" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach($data['merek'] as $mrk): ?>
                                        <option value="<?php echo $mrk['merek_id']; ?>" <?php if($isEditMode && $brg['merek_id'] == $mrk['merek_id']) echo 'selected'; ?>>
                                            <?php echo $mrk['nama_merek']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="satuan_id">Satuan</label>
                                <select id="satuan_id" name="satuan_id" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach($data['satuan'] as $sat): ?>
                                        <option value="<?php echo $sat['satuan_id']; ?>" <?php if($isEditMode && $brg['satuan_id'] == $sat['satuan_id']) echo 'selected'; ?>>
                                            <?php echo $sat['nama_satuan']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="stok_minimum">Stok Minimum</label>
                                <input type="number" id="stok_minimum" name="stok_minimum" 
                                    value="<?php echo $isEditMode ? htmlspecialchars($brg['stok_minimum']) : '5'; ?>" min="0">
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div style="flex: 1;">
                    <fieldset>
                        <legend>Foto Produk (Kemasan)</legend>
                        
                        <div style="text-align: center; margin-bottom: 15px; background: #f9f9f9; padding: 10px; border-radius: 5px; border: 1px dashed #ccc;">
                            <?php 
                                $foto = ($isEditMode && !empty($brg['foto_barang'])) 
                                        ? BASE_URL . 'uploads/barang/' . $brg['foto_barang'] 
                                        : 'https://via.placeholder.com/150?text=No+Image';
                            ?>
                            <img id="previewFoto" src="<?php echo $foto; ?>" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 5px;">
                        </div>

                        <div class="form-group">
                            <label for="foto_barang">Upload Foto Baru</label>
                            <input type="file" id="foto_barang" name="foto_barang" accept="image/*" onchange="previewImage(this)">
                            <small style="color: #666;">Format: JPG/PNG. Max 2MB.</small>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Pengaturan</legend>
                        <div class="form-group form-check">
                            <input type="checkbox" id="bisa_dipinjam" name="bisa_dipinjam" value="1" <?php if($isEditMode && $brg['bisa_dipinjam'] == 1) echo 'checked'; ?>>
                            <label for="bisa_dipinjam">Bisa Dipinjam?</label>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" id="lacak_lot_serial" name="lacak_lot_serial" value="1" <?php if($isEditMode && $brg['lacak_lot_serial'] == 1) echo 'checked'; ?>>
                            <label for="lacak_lot_serial">Wajib Lot/Batch?</label>
                        </div>
                    </fieldset>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 1.1em;">
                    💾 <?php echo $isEditMode ? 'Update Barang' : 'Simpan Barang'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>admin/barang" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>
</main>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewFoto').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
<script src="<?php echo BASE_URL; ?>js/main.js"></script>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>