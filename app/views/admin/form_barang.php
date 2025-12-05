<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    // Cek apakah ini mode 'Edit'
    $isEditMode = isset($data['barang']) && $data['barang'] != null;
    $brg = $data['barang']; 
?>

<main class="app-content">
    <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: visible;">
        
        <form action="<?php echo BASE_URL; ?>admin/processBarang" method="POST" enctype="multipart/form-data">
            
            <?php if ($isEditMode) : ?>
                <input type="hidden" name="product_id" value="<?php echo $brg['product_id']; ?>">
                <input type="hidden" name="foto_lama" value="<?php echo $brg['foto_barang']; ?>">
            <?php endif; ?>

            <div class="form-layout-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
                
                <div class="left-column">
                    
                    <div style="margin-bottom: 30px;">
                        <h4 style="color: #152e4d; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 20px;">
                            <i class="ph ph-info" style="color: #f8c21a; margin-right: 5px;"></i> Informasi Dasar
                        </h4>

                        <div class="form-group">
                            <label for="kode_barang" style="font-weight: 600; color: #334155;">Kode Barang (Unik) <span style="color: red;">*</span></label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="kode_barang" name="kode_barang" 
                                    value="<?php echo $isEditMode ? htmlspecialchars($brg['kode_barang']) : ''; ?>" 
                                    placeholder="Ketik manual atau klik Auto" 
                                    class="form-control" style="font-weight: bold; letter-spacing: 0.5px; color: #152e4d;" required>
                                
                                <button type="button" id="btnAutoCode" class="btn" 
                                        style="background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-weight: 600;"
                                        title="Generate Kode Otomatis">
                                    <i class="ph ph-lightning"></i> Auto
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nama_barang" style="font-weight: 600; color: #334155;">Nama Barang <span style="color: red;">*</span></label>
                            <input type="text" id="nama_barang" name="nama_barang" 
                                value="<?php echo $isEditMode ? htmlspecialchars($brg['nama_barang']) : ''; ?>" 
                                placeholder="Contoh: Sabun Mandi Lifebuoy 100gr" 
                                class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi" style="font-weight: 600; color: #334155;">Deskripsi / Spesifikasi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3" 
                                    class="form-control"
                                    placeholder="Jelaskan detail produk, varian, warna, dll..."><?php echo $isEditMode ? htmlspecialchars($brg['deskripsi']) : ''; ?></textarea>
                        </div>
                    </div>

                    <div>
                        <h4 style="color: #152e4d; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 20px;">
                            <i class="ph ph-tag" style="color: #f8c21a; margin-right: 5px;"></i> Kategorisasi & Stok
                        </h4>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="kategori_id" style="font-weight: 600; color: #334155;">Kategori <span style="color: red;">*</span></label>
                                <div style="position: relative;">
                                    <select id="kategori_id" name="kategori_id" class="form-control" required style="appearance: none;">
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php foreach($data['kategori'] as $kat): ?>
                                            <option value="<?php echo $kat['kategori_id']; ?>" <?php if($isEditMode && $brg['kategori_id'] == $kat['kategori_id']) echo 'selected'; ?>>
                                                <?php echo $kat['nama_kategori']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="merek_id" style="font-weight: 600; color: #334155;">Merek / Brand <span style="color: red;">*</span></label>
                                <div style="position: relative;">
                                    <select id="merek_id" name="merek_id" class="form-control" required style="appearance: none;">
                                        <option value="">-- Pilih Merek --</option>
                                        <?php foreach($data['merek'] as $mrk): ?>
                                            <option value="<?php echo $mrk['merek_id']; ?>" <?php if($isEditMode && $brg['merek_id'] == $mrk['merek_id']) echo 'selected'; ?>>
                                                <?php echo $mrk['nama_merek']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="satuan_id" style="font-weight: 600; color: #334155;">Satuan Unit <span style="color: red;">*</span></label>
                                <div style="position: relative;">
                                    <select id="satuan_id" name="satuan_id" class="form-control" required style="appearance: none;">
                                        <option value="">-- Pilih Satuan --</option>
                                        <?php foreach($data['satuan'] as $sat): ?>
                                            <option value="<?php echo $sat['satuan_id']; ?>" <?php if($isEditMode && $brg['satuan_id'] == $sat['satuan_id']) echo 'selected'; ?>>
                                                <?php echo $sat['nama_satuan']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="stok_minimum" style="font-weight: 600; color: #334155;">Stok Minimum (Alert)</label>
                                <input type="number" id="stok_minimum" name="stok_minimum" class="form-control"
                                    value="<?php echo $isEditMode ? htmlspecialchars($brg['stok_minimum']) : '5'; ?>" min="0">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="right-column">
                    
                    <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                        <h5 style="margin-bottom: 15px; color: #152e4d;">📸 Foto Produk</h5>
                        
                        <div style="text-align: center; margin-bottom: 15px; background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <?php 
                                $foto = ($isEditMode && !empty($brg['foto_barang'])) 
                                        ? BASE_URL . 'uploads/barang/' . $brg['foto_barang'] 
                                        : BASE_URL . 'img/placeholder-product.png';
                                
                                if ($isEditMode && !empty($brg['foto_barang']) && !file_exists(APPROOT . '/../public/uploads/barang/' . $brg['foto_barang'])) {
                                    $foto = 'https://via.placeholder.com/300x300?text=No+Image';
                                } elseif (!$isEditMode) {
                                     $foto = 'https://via.placeholder.com/300x300?text=Preview';
                                }
                            ?>
                            <img id="previewFoto" src="<?php echo $foto; ?>" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>

                        <div id="drop_zone_barang" style="border: 2px dashed #cbd5e1; padding: 15px; text-align: center; border-radius: 8px; background: #ffffff; position: relative; cursor: pointer; transition: all 0.2s;">
                            <input type="file" id="foto_barang" name="foto_barang" accept="image/*" 
                                   style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                            
                            <div id="label_file_barang">
                                <i class="ph ph-cloud-arrow-up" style="font-size: 1.5rem; color: #152e4d; margin-bottom: 5px;"></i>
                                <span style="color: #64748b; font-size: 0.85rem; display:block;">Klik atau Seret Foto ke Sini</span>
                                <small style="color: #94a3b8; font-size: 0.75rem;">(JPG, PNG - Max 2MB)</small>
                            </div>
                        </div>
                    </div>

                    <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <h5 style="margin-bottom: 15px; color: #152e4d;">⚙️ Konfigurasi</h5>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label class="custom-checkbox" style="display: flex; gap: 10px; align-items: center; cursor: pointer;">
                                <input type="checkbox" id="bisa_dipinjam" name="bisa_dipinjam" value="1" 
                                       style="width: 20px; height: 20px; accent-color: #152e4d;"
                                       <?php if($isEditMode && $brg['bisa_dipinjam'] == 1) echo 'checked'; ?>>
                                <div>
                                    <span style="font-weight: 600; color: #334155;">Bisa Dipinjam?</span>
                                    <small style="display: block; color: #64748b; font-size: 0.8rem;">Barang ini adalah aset yang bisa dipinjam.</small>
                                </div>
                            </label>
                        </div>

                        <input type="hidden" name="lacak_lot_serial" value="1">
                        
                    </div>

                </div>
            </div>
            
            <div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; display: flex; justify-content: flex-end; gap: 15px;">
                <a href="<?php echo BASE_URL; ?>admin/barang" class="btn btn-batal-custom">
                    Batal
                </a>
                
                <button type="submit" class="btn btn-simpan-custom">
                    <i class="ph ph-floppy-disk" style="font-size: 1.2rem;"></i> 
                    <?php echo $isEditMode ? 'Simpan Perubahan' : 'Simpan Barang Baru'; ?>
                </button>
            </div>

        </form>
    </div>
</main>

<script src="<?php echo BASE_URL; ?>js/main.js"></script>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>