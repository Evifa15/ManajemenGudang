<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Form Input Barang Masuk</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>staff/processBarangMasuk" method="POST" enctype="multipart/form-data">
            
            <fieldset>
                <legend>Detail Penerimaan</legend>
                <div class="form-group">
                    <label for="tanggal_penerimaan">Tanggal Penerimaan</label>
                    <input type="date" id="tanggal_penerimaan" name="tanggal_penerimaan" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="supplier_id">Pilih Supplier</label>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="">-- Pilih Supplier --</option>
                        <?php foreach($data['suppliers'] as $sup): ?>
                            <option value="<?php echo $sup['supplier_id']; ?>"><?php echo $sup['nama_supplier']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <fieldset>
                <legend>Detail Barang & Batch</legend>
                
                <div class="form-group">
                    <label for="product_id">Pilih Barang</label>
                    
                    <div id="reader" style="width: 100%; display:none; margin-bottom:15px; border:2px solid #ccc; border-radius:5px;"></div>

                    <div style="display: flex; gap: 10px;">
                        <select id="product_id" name="product_id" required class="form-control" style="flex: 1;">
                            <option value="">-- Pilih Barang atau Scan --</option>
                            <?php foreach($data['products'] as $prod): ?>
                                <option value="<?php echo $prod['product_id']; ?>" 
                                        data-kode="<?php echo $prod['kode_barang']; ?>"
                                        data-lacak_lot="<?php echo $prod['lacak_lot_serial']; ?>">
                                    <?php echo $prod['kode_barang'] . ' - ' . $prod['nama_barang']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="button" id="btnScanBarcode" class="btn btn-info" style="background:#17a2b8; color:white; border:none;">
                            📷 Scan
                        </button>
                    </div>
                </div>

                <div style="background: #e8f4fd; padding: 15px; border-radius: 5px; border: 1px solid #b8daff; margin-bottom: 15px;">
                    <div class="form-group">
                        <label for="lot_number">Nomor Lot / Batch <span style="color:red">*</span></label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="lot_number" name="lot_number" 
                                   placeholder="Ketik manual atau klik Auto (BATCH-YYMMDD-XXX)" 
                                   required style="flex: 1;">
                            
                            <button type="button" id="btnAutoBatch" class="btn btn-info" 
                                    style="background-color: #17a2b8; border: none; color: white; padding: 0 15px;"
                                    title="Buat Nomor Batch Otomatis">
                                ⚡ Auto
                            </button>
                        </div>
                        <small style="color:#666;">Wajib diisi. Gunakan tombol Auto untuk Batch Internal.</small>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="production_date">Tanggal Produksi <span style="color:red">*</span></label>
                            <input type="date" id="production_date" name="production_date" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="exp_date">Tanggal Kedaluwarsa (Expired) <span style="color:red">*</span></label>
                            <input type="date" id="exp_date" name="exp_date" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah Masuk</label>
                    <input type="number" id="jumlah" name="jumlah" value="1" min="1" required>
                </div>
            </fieldset>

            <fieldset>
                <legend>Penyimpanan & Dokumen</legend>
                <div class="form-group">
                    <label for="lokasi_id">Simpan ke Lokasi</label>
                    <select id="lokasi_id" name="lokasi_id" required>
                        <option value="">-- Pilih Lokasi Penyimpanan --</option>
                        <?php foreach($data['lokasi'] as $lok): ?>
                            <option value="<?php echo $lok['lokasi_id']; ?>"><?php echo $lok['kode_lokasi'] . ' - ' . $lok['nama_rak']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status_id">Status Barang</label>
                    <select id="status_id" name="status_id" required>
                        <option value="">-- Pilih Status --</option>
                        <?php foreach($data['status'] as $stat): ?>
                            <option value="<?php echo $stat['status_id']; ?>" <?php if(strtolower($stat['nama_status']) == 'tersedia') echo 'selected'; ?>>
                                <?php echo $stat['nama_status']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan (Opsional)</label>
                    <textarea id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Dus sedikit basah"></textarea>
                </div>

                <div class="form-group">
                    <label>Upload Bukti Nota / Surat Jalan (Bisa Lebih dari 1)</label>
                    
                    <div id="preview-container" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                        </div>

                    <button type="button" id="btn-add-file" class="btn btn-info btn-sm" style="margin-bottom: 5px;">
                        + Tambah File
                    </button>
                    <small style="color: #666; display: inline-block; margin-left: 10px;">Format: JPG, PNG, PDF</small>

                    <input type="file" id="bukti_foto_input" accept="image/*, application/pdf" style="display: none;">
                    
                    <input type="file" id="bukti_foto_final" name="bukti_foto[]" multiple style="display: none;">
                </div>
            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Transaksi Masuk</button>
            </div>
        </form>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>