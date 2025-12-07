<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    // Logika Mode Edit/Tambah
    $isEditMode = isset($data['barang']) && !empty($data['barang']);
    
    // Logika Default Values untuk Dropdown Jenis Barang
    $jenisAwal = '';
    if ($isEditMode) {
        $kode = $data['barang']['kode_barang'];
        // Asumsi logic: Jika kode diawali 'B' atau bisa dipinjam -> Asset
        $firstChar = strtoupper(substr($kode, 0, 1));
        if ($firstChar == 'B' || $data['barang']['bisa_dipinjam'] == 1) {
            $jenisAwal = 'asset';
        } else {
            $jenisAwal = 'product';
        }
    }
?>

<main class="app-content">
    
    <?php if(isset($_SESSION['flash_message'])): ?>
        <?php 
            $flash = $_SESSION['flash_message'];
            $type = $flash['type'];
            $message = $flash['text'];
            
            // Tentukan Icon berdasarkan tipe
            $iconClass = 'ph-info';
            if (strpos($type, 'success') !== false) $iconClass = 'ph-check-circle';
            elseif (strpos($type, 'error') !== false) $iconClass = 'ph-x-circle';
            elseif (strpos($type, 'warning') !== false) $iconClass = 'ph-warning-circle';
        ?>
        
        <div class="flash-message <?php echo $type; ?>">
            <div class="flash-icon">
                <i class="ph <?php echo $iconClass; ?>"></i>
            </div>
            <div class="flash-text">
                <?php echo $message; ?>
            </div>
        </div>
        
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>admin/<?php echo $isEditMode ? 'updateBarang' : 'storeBarang'; ?>" 
          method="POST" enctype="multipart/form-data" id="formBarang">
        
        <?php if ($isEditMode): ?>
            <input type="hidden" name="product_id" value="<?php echo $data['barang']['product_id']; ?>">
            <input type="hidden" name="id" value="<?php echo $data['barang']['product_id']; ?>">
            <input type="hidden" name="foto_lama" value="<?php echo $data['barang']['foto_barang']; ?>">
        <?php endif; ?>

        <div class="form-grid-4">
            
            <div class="clean-card">
                <div class="card-title-custom">
                    <i class="ph ph-tag"></i> 1. Klasifikasi & Kode
                </div>

                <div class="form-group-compact">
                    <label>Jenis Barang <span style="color:red">*</span></label>
                    <div style="position: relative;">
                        <select class="form-control form-control-compact" id="jenis_barang" name="jenis_barang" required
                                style="background-color: #f0f9ff; border-color: #bae6fd; color: #0369a1; font-weight: 700; appearance: none;">
                            <option value="" disabled <?php echo !$isEditMode ? 'selected' : ''; ?>>-- Pilih Jenis --</option>
                            <option value="product" <?php echo ($jenisAwal == 'product') ? 'selected' : ''; ?>>Product</option>
                            <option value="asset" <?php echo ($jenisAwal == 'asset') ? 'selected' : ''; ?>>Aset / Inventaris</option>
                        </select>
                        <i class="ph ph-caret-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #0369a1; pointer-events: none;"></i>
                    </div>
                </div>

                <div class="form-group-compact">
                    <label>Kategori <span style="color:red">*</span></label>
                    <div style="position: relative;">
                        <select class="form-control form-control-compact" name="kategori_id" required style="appearance: none;">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($data['kategori'] as $kat) : ?>
                                <option value="<?php echo $kat['kategori_id']; ?>" 
                                    <?php echo ($isEditMode && $data['barang']['kategori_id'] == $kat['kategori_id']) ? 'selected' : ''; ?>>
                                    <?php echo $kat['nama_kategori']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                    </div>
                </div>

                <div class="form-group-compact" style="margin-top: auto;"> 
                    <label>Kode Barang (Unik) <span style="color:red">*</span></label>
                    <div style="display: flex; gap: 5px;">
                        <input type="text" class="form-control form-control-compact" id="kode_barang" name="kode_barang" 
                               value="<?php echo $isEditMode ? $data['barang']['kode_barang'] : ''; ?>" required 
                               placeholder="Contoh: BRG-001" 
                               <?php echo $isEditMode ? 'readonly' : ''; ?>
                               style="font-weight: 600; letter-spacing: 0.5px; <?php echo $isEditMode ? 'background-color: #eee;' : ''; ?>">
                        
                        <?php if (!$isEditMode): ?>
                            <button type="button" id="btnAutoCode" class="btn btn-secondary btn-sm" 
                                    style="border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 8px;"
                                    title="Generate Kode Otomatis">
                                <i class="ph ph-magic-wand" style="font-size: 1.1rem; color: #64748b;"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="clean-card">
                <div class="card-title-custom">
                    <i class="ph ph-info"></i> 2. Informasi Dasar
                </div>

                <div class="form-group-compact">
                    <label>Nama Barang <span style="color:red">*</span></label>
                    <input type="text" class="form-control form-control-compact" name="nama_barang" 
                           value="<?php echo $isEditMode ? $data['barang']['nama_barang'] : ''; ?>" required
                           placeholder="Masukkan nama barang...">
                </div>

                <div class="form-group-compact">
                    <label>Merek / Brand</label>
                    <div style="position: relative;">
                        <select class="form-control form-control-compact" name="merek_id" style="appearance: none;">
                            <option value="">-- Pilih Merek (Opsional) --</option>
                            <?php foreach ($data['merek'] as $merk) : ?>
                                <option value="<?php echo $merk['merek_id']; ?>"
                                    <?php echo ($isEditMode && $data['barang']['merek_id'] == $merk['merek_id']) ? 'selected' : ''; ?>>
                                    <?php echo $merk['nama_merek']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                    </div>
                </div>

                <div class="form-group-compact">
                    <label>Satuan Unit <span style="color:red">*</span></label>
                    <div style="position: relative;">
                        <select class="form-control form-control-compact" id="satuan_id" name="satuan_id" required style="appearance: none;">
                            <option value="" data-nama="Unit">-- Pilih Satuan --</option>
                            <?php foreach ($data['satuan'] as $sat) : ?>
                                <option value="<?php echo $sat['satuan_id']; ?>" data-nama="<?php echo $sat['nama_satuan']; ?>"
                                    <?php echo ($isEditMode && $data['barang']['satuan_id'] == $sat['satuan_id']) ? 'selected' : ''; ?>>
                                    <?php echo $sat['nama_satuan']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                    </div>
                </div>
            </div>

            <div class="clean-card" style="background-color: #fcfcfc;">
                <div class="card-title-custom">
                    <i class="ph ph-sliders"></i> 3. Stok & Fitur
                </div>

                <div class="form-group-compact">
                    <label>Lokasi Rak <span style="color:red">*</span></label>
                    <div style="position: relative;">
                        <select class="form-control form-control-compact" name="lokasi_id" required style="appearance: none;">
                            <option value="">-- Pilih Lokasi --</option>
                            <?php foreach ($data['lokasi'] as $loc) : ?>
                                <option value="<?php echo $loc['lokasi_id']; ?>"
                                    <?php echo ($isEditMode && $data['barang']['lokasi_id'] == $loc['lokasi_id']) ? 'selected' : ''; ?>>
                                    <?php echo $loc['kode_lokasi']; ?> - <?php echo $loc['nama_rak']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                    </div>
                </div>

                <div class="form-group-compact" id="fieldStokMin">
                    <label>Stok Minimum (Alert)</label>
                    <div style="position: relative;">
                        <input type="number" class="form-control form-control-compact" name="stok_minimum" 
                               value="<?php echo $isEditMode ? $data['barang']['stok_minimum'] : '5'; ?>" 
                               style="padding-right: 50px;">
                        <span id="labelSatuanMin" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; background: #eee; padding: 2px 5px; border-radius: 4px; color: #666;">Unit</span>
                    </div>
                </div>

                <div class="form-group-compact">
                    <label>Deskripsi / Spesifikasi</label>
                    <textarea name="deskripsi" class="form-control form-control-compact" rows="2" 
                              style="min-height: 60px; resize: vertical;"><?php echo $isEditMode ? $data['barang']['deskripsi'] : ''; ?></textarea>
                </div>

                <div style="margin-top: auto; padding-top: 10px; border-top: 1px dashed #e2e8f0;">
                    <label class="custom-control custom-checkbox mb-2" style="display: flex; gap: 8px; cursor: pointer; align-items: center;">
                        <input type="checkbox" name="bisa_dipinjam" id="check_bisa_dipinjam" value="1" 
                               class="custom-control-input" style="width: 16px; height: 16px; cursor: pointer;"
                               <?php echo ($isEditMode && $data['barang']['bisa_dipinjam'] == 1) ? 'checked' : ''; ?>>
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--primer-darkblue);">Bisa Dipinjam?</span>
                    </label>
                    <label class="custom-control custom-checkbox mb-0" style="display: flex; gap: 8px; cursor: pointer; align-items: center;">
                        <input type="checkbox" name="lacak_lot_serial" id="check_kadaluarsa" value="1"
                               class="custom-control-input" style="width: 16px; height: 16px; cursor: pointer;"
                               <?php echo ($isEditMode && $data['barang']['lacak_lot_serial'] == 1) ? 'checked' : ''; ?>>
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--primer-darkblue);">Lacak Lot & Expired?</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <div class="clean-card" style="height: auto; min-height: auto;">
                    <div class="card-title-custom">
                        <i class="ph ph-image"></i> 4. Foto Barang
                    </div>

                    <?php 
                        // Logic Preview (Sama seperti sebelumnya)
                        $hasInitialFile = $isEditMode && !empty($data['barang']['foto_barang']);
                        $initialFilePath = $hasInitialFile ? BASE_URL . 'uploads/barang/' . $data['barang']['foto_barang'] : '';
                        $wrapperClass = $hasInitialFile ? 'has-preview' : '';
                    ?>

                    <div class="form-group-compact" style="margin-bottom: 0;">
                        <div class="file-upload-wrapper <?php echo $wrapperClass; ?>" 
                            style="min-height: 220px; border: 2px dashed #cbd5e1; border-radius: 8px; position: relative; display: flex; align-items: center; justify-content: center; background-color: #f8fafc; overflow: hidden;">
                            
                            <input type="file" name="foto_barang" accept="image/*" id="inputFotoBarang"
                                   style="position: absolute; width: 100%; height: 100%; top:0; left:0; opacity: 0; cursor: pointer; z-index: 20;">
                            
                            <div id="preview-image-container" style="display: <?php echo $hasInitialFile ? 'flex' : 'none'; ?>; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                <img id="preview-image" 
                                     src="<?php echo $initialFilePath; ?>" 
                                     alt="Preview" 
                                     data-initial-file="<?php echo $hasInitialFile ? '1' : '0'; ?>"
                                     style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>

                            <div class="file-preview-overlay" style="display: <?php echo $hasInitialFile ? 'none' : 'flex'; ?>; flex-direction: column; align-items: center; pointer-events: none;">
                                <div style="background: #e2e8f0; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                    <i class="ph ph-camera-plus" style="font-size: 2rem; color: #94a3b8;"></i>
                                </div>
                                <span class="file-upload-text" style="font-size: 0.95rem; color: #64748b; font-weight: 600;">
                                    Upload Foto
                                </span>
                                <small style="color: #94a3b8; font-size: 0.75rem; margin-top: 2px;">(Klik atau Drop File)</small>
                            </div>
                        </div>
                    </div>
                </div> 
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    
                    <?php if (!$isEditMode): ?>
                        <button type="button" id="btnResetForm" 
                                class="btn" 
                                title="Bersihkan Formulir"
                                style="width: 100%; padding: 12px; background-color: #ffffff; color: #64748b; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;">
                            <i class="ph ph-arrow-counter-clockwise" style="font-size: 1.2rem;"></i>
                            Reset / Bersihkan
                        </button>
                    <?php endif; ?>
                    
                    <button type="submit" 
                            class="btn-simpan-custom" 
                            style="width: 100%; padding: 15px; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 10px rgba(21, 46, 77, 0.2);">
                        <i class="ph ph-floppy-disk" style="font-size: 1.3rem;"></i>
                        SIMPAN DATA
                    </button>

                </div>
                </div>
            </div> 
    </form>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>