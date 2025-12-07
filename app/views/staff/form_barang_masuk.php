<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>

<main class="app-content" id="formBarangMasukPage" data-base-url="<?php echo BASE_URL; ?>">
    
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_message']['type']; ?>" style="margin-bottom: 20px;">
            <?php echo $_SESSION['flash_message']['text']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <form id="formBarangMasuk" action="<?php echo BASE_URL; ?>staff/processBarangMasuk" method="POST" enctype="multipart/form-data">
        
        <div class="form-layout-grid">
            
            <div class="left-column">
                
                <div class="card">
                    
                    <h4 class="card-header-custom">
                        <i class="ph ph-package" style="color: var(--primer-yellow);"></i> Data Barang Masuk
                    </h4>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; color: var(--neutral-gray); margin-bottom: 8px; display: block;">
                            Pilih Barang <span style="color:red">*</span>
                        </label>
                        <div style="position: relative;">
                            <select id="product_id" name="product_id" required class="form-control" 
                                    style="width: 100%; font-weight: 500; appearance: none;">
                                <option value="">-- Cari Nama atau Kode Barang --</option>
                                <?php foreach($data['products'] as $prod): ?>
                                    <option value="<?php echo $prod['product_id']; ?>" 
                                            data-kode="<?php echo $prod['kode_barang']; ?>"
                                            data-lacak="<?php echo $prod['lacak_lot_serial']; ?>"
                                            data-jenis="<?php echo isset($prod['jenis_barang']) ? $prod['jenis_barang'] : 'product'; ?>">
                                        <?php echo $prod['kode_barang'] . ' - ' . $prod['nama_barang']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-weight: 600; color: var(--neutral-gray); margin-bottom: 8px; display: block;">
                                Supplier <span style="color:red">*</span>
                            </label>
                            <div style="position: relative;">
                                <select name="supplier_id" required class="form-control" style="appearance: none;">
                                    <option value="">-- Pilih Supplier --</option>
                                    <?php foreach($data['suppliers'] as $sup): ?>
                                        <option value="<?php echo $sup['supplier_id']; ?>"><?php echo $sup['nama_supplier']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-weight: 600; color: var(--neutral-gray); margin-bottom: 8px; display: block;">
                                Jumlah Masuk <span style="color:red">*</span>
                            </label>
                            
                            <div style="display: flex; gap: 10px; align-items: stretch;">
                                <input type="number" name="jumlah" min="1" value="1" required class="form-control" 
                                       style="font-weight: bold; color: var(--primer-darkblue); font-size: 1.1rem; flex: 1;">
                                
                                <div style="position: relative; width: 110px;">
                                    <select name="satuan_id" required class="form-control" 
                                            style="appearance: none; background-color: #f8fafc; font-weight: 600;">
                                        <?php if(!empty($data['satuan'])): ?>
                                            <?php foreach($data['satuan'] as $sat): ?>
                                                <option value="<?php echo $sat['satuan_id']; ?>">
                                                    <?php echo $sat['nama_satuan']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">-</option>
                                        <?php endif; ?>
                                    </select>
                                    <i class="ph ph-caret-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 0.9rem;"></i>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="separator" style="margin: 25px 0; border-top: 1px dashed #e2e8f0;"></div>

                    <div id="wrapperBatchInfo" class="widget-info-blue" style="margin-bottom: 25px;">
                        
                        <div style="margin-bottom: 10px;">
                            <label id="labelHeaderBatch" style="font-weight: 700; color: #152e4d; display: flex; align-items: center; gap: 5px;">
                                <i class="ph ph-barcode"></i> Info Batch / Lot <span style="color:red">*</span>
                            </label>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <div style="display: flex; gap: 10px; align-items: stretch;">
                                <input type="text" id="lot_number" name="lot_number" required placeholder="Nomor Batch / Serial Number"
                                       class="form-control input-readonly-highlight" style="flex: 1;">
                                
                                <button type="button" id="btnAutoBatch" class="btn btn-sm" 
                                        style="background-color: transparent; color: #2f8ba9; border: 1px solid #2f8ba9; font-weight: 600; padding: 0 20px; border-radius: 8px; white-space: nowrap;">
                                    <i class="ph ph-lightning"></i> Auto
                                </button>
                            </div>
                        </div>

                        <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #1e40af; margin-bottom: 5px; display: block;">Tgl. Produksi</label>
                                <input type="date" name="production_date" required class="form-control" 
                                       style="border-color: #93c5fd; color: #1e40af;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.85rem; font-weight: 600; color: #dc2626; margin-bottom: 5px; display: block;">Tgl. Kedaluwarsa</label>
                                <input type="date" name="exp_date" required class="form-control"
                                       style="border-color: #fca5a5; color: #dc2626; background: #fff1f2;">
                            </div>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label style="font-weight: 600; color: var(--neutral-gray);">Simpan di Lokasi</label>
                            <div style="position: relative;">
                                <select name="lokasi_id" required class="form-control" style="appearance: none;">
                                    <?php foreach($data['lokasi'] as $lok): ?>
                                        <option value="<?php echo $lok['lokasi_id']; ?>"><?php echo $lok['kode_lokasi'] . ' - ' . $lok['nama_rak']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; color: var(--neutral-gray);">Status Kondisi</label>
                            <div style="position: relative;">
                                <select name="status_id" required class="form-control" style="appearance: none;">
                                    <?php foreach($data['status'] as $stat): ?>
                                        <option value="<?php echo $stat['status_id']; ?>" <?php if(strtolower($stat['nama_status']) == 'tersedia') echo 'selected'; ?>>
                                            <?php echo $stat['nama_status']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-weight: 600; color: var(--neutral-gray);">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="form-control" placeholder="Catatan tambahan..."></textarea>
                    </div>

                </div> 

            </div>

            <div class="right-column right-column-stack" style="display: flex; flex-direction: column; gap: 20px;">
                
                <div class="card" style="text-align: center;">
                    <h5 style="color: var(--primer-darkblue); font-weight: 700; margin-bottom: 15px;">🔍 Scan Barcode</h5>
                    <div id="reader" class="scanner-area"></div>
                    <button type="button" id="btnScanBarcode" class="btn btn-brand-dark" style="width: 100%; justify-content: center;">
                        <i class="ph ph-camera" style="font-size: 1.2rem;"></i> Mulai Scan Kamera
                    </button>
                </div>

                <div class="card">
                    <h5 style="color: var(--primer-darkblue); font-weight: 700; margin-bottom: 15px;">📸 Bukti Nota / SJ</h5>
                    <div id="preview-container" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; min-height: 50px;">
                        <div class="upload-area-dashed">Belum ada file dipilih</div>
                    </div>
                    <button type="button" id="btn-add-file" class="btn" 
                            style="width: 100%; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 600; justify-content: center;">
                        <i class="ph ph-plus"></i> Tambah Foto/PDF
                    </button>
                    <input type="file" id="bukti_foto_input" accept="image/*, application/pdf" style="display: none;">
                    <input type="file" id="bukti_foto_final" name="bukti_foto[]" multiple style="display: none;">
                </div>

                <div class="action-buttons-grid">
                    <button type="reset" class="btn-action-reset" id="btnResetBarangMasuk">
                        <i class="ph ph-arrows-counter-clockwise"></i> Reset
                    </button>
                    <button type="submit" class="btn-action-save">
                        <i class="ph ph-check-circle"></i> Simpan
                    </button>
                </div>

            </div>

        </div>
    </form>

</main>

<script src="<?php echo BASE_URL; ?>js/html5-qrcode.min.js"></script>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>