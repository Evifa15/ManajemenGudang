<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    // Cek apakah ini mode 'Edit' (jika $data['barang'] ada isinya)
    $isEditMode = isset($data['barang']) && $data['barang'] != null;
    $brg = $data['barang']; // Akan jadi null jika mode 'Tambah'
?>

<main class="app-content">
    
    <div class="content-header">
        <h1><?php echo $isEditMode ? 'Edit Barang' : 'Tambah Barang Baru'; ?></h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processBarang" method="POST">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="product_id" value="<?php echo $brg['product_id']; ?>">
            <?php endif; ?>

            <fieldset>
                <legend>Informasi Dasar Barang</legend>
                <div class="form-group">
                    <label for="kode_barang">Kode Barang (Unik)</label>
                    <input type="text" id="kode_barang" name="kode_barang" 
                           value="<?php echo $isEditMode ? htmlspecialchars($brg['kode_barang']) : ''; ?>" 
                           placeholder="Misal: SB-001 (Bisa auto-generate)" required>
                </div>
                <div class="form-group">
                    <label for="nama_barang">Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang" 
                           value="<?php echo $isEditMode ? htmlspecialchars($brg['nama_barang']) : ''; ?>" 
                           placeholder="Misal: Sabun Mandi Lifebuoy 100gr" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" 
                              placeholder="Deskripsi tambahan (Opsional)"><?php echo $isEditMode ? htmlspecialchars($brg['deskripsi']) : ''; ?></textarea>
                </div>
            </fieldset>

            <fieldset>
                <legend>Atribut Barang</legend>
                <div class="form-group">
                    <label for="kategori_id">Kategori</label>
                    <select id="kategori_id" name="kategori_id" required>
                        <option value="">-- Pilih Kategori --</option>
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
                        <option value="">-- Pilih Merek --</option>
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
                        <option value="">-- Pilih Satuan --</option>
                        <?php foreach($data['satuan'] as $sat): ?>
                            <option value="<?php echo $sat['satuan_id']; ?>" <?php if($isEditMode && $brg['satuan_id'] == $sat['satuan_id']) echo 'selected'; ?>>
                                <?php echo $sat['nama_satuan'] . ' (' . $sat['singkatan'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <?php if (!$isEditMode): ?>
            <fieldset>
                <legend>Stok Awal (Opsional)</legend>
                <div class="form-group">
                    <label for="stok_awal">Stok Awal</label>
                    <input type="number" id="stok_awal" name="stok_awal" value="0" min="0">
                </div>
                <div class="form-group">
                    <label for="lokasi_id">Lokasi Penyimpanan</label>
                    <select id="lokasi_id" name="lokasi_id">
                        <option value="">-- Pilih Lokasi --</option>
                        <?php foreach($data['lokasi'] as $lok): ?>
                            <option value="<?php echo $lok['lokasi_id']; ?>"><?php echo $lok['kode_lokasi'] . ' - ' . $lok['nama_rak']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status_id">Status Stok</label>
                    <select id="status_id" name="status_id">
                        <option value="">-- Pilih Status --</option>
                        <?php foreach($data['status'] as $stat): ?>
                            <option value="<?php echo $stat['status_id']; ?>"><?php echo $stat['nama_status']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>
            <?php endif; ?>

            <fieldset>
                <legend>Pengaturan & Pelacakan</legend>
                <div class="form-group">
                    <label for="stok_minimum">Stok Minimum</label>
                    <input type="number" id="stok_minimum" name="stok_minimum" 
                           value="<?php echo $isEditMode ? htmlspecialchars($brg['stok_minimum']) : '0'; ?>" min="0">
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" id="bisa_dipinjam" name="bisa_dipinjam" value="1" <?php if($isEditMode && $brg['bisa_dipinjam'] == 1) echo 'checked'; ?>>
                    <label for="bisa_dipinjam">Barang ini bisa dipinjam (Aset)</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" id="lacak_lot_serial" name="lacak_lot_serial" value="1" <?php if($isEditMode && $brg['lacak_lot_serial'] == 1) echo 'checked'; ?>>
                    <label for="lacak_lot_serial">Lacak barang ini via Lot/Serial Number</label>
                </div>
            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $isEditMode ? 'Update' : 'Simpan'; ?> Barang</button>
                <a href="<?php echo BASE_URL; ?>admin/barang" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>