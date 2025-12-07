<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>

<main class="app-content" id="formReturRusakPage" data-base-url="<?php echo BASE_URL; ?>">
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_message']['type']; ?>" style="margin-bottom: 20px;">
            <?php echo $_SESSION['flash_message']['text']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <form id="formReturRusak" action="<?php echo BASE_URL; ?>staff/processReturBarang" method="POST" enctype="multipart/form-data">
        
        <div class="form-layout-grid">
            
            <div class="left-column">
                
                <div class="card">
                    
                    <h4 class="card-header-custom">
                        <i class="ph ph-warning-circle" style="color: #ef4444;"></i> Data Kerusakan / Retur
                    </h4>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; color: var(--neutral-gray); margin-bottom: 8px; display: block;">
                            Barang Bermasalah <span style="color:red">*</span>
                        </label>
                        <div style="position: relative;">
                            <select id="product_id" name="product_id" required class="form-control" 
                                    style="width: 100%; font-weight: 500; appearance: none; -webkit-appearance: none; -moz-appearance: none;">
                                <option value="">-- Cari Nama atau Kode Barang --</option>
                                <?php foreach($data['products'] as $prod): ?>
                                    <option value="<?php echo $prod['product_id']; ?>" data-kode="<?php echo $prod['kode_barang']; ?>">
                                        <?php echo $prod['kode_barang'] . ' - ' . $prod['nama_barang']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                        </div>
                        <small style="color: #64748b; margin-top: 5px; display: block;">
                            Tips: Gunakan scanner untuk pencarian cepat.
                        </small>
                    </div>

                    <div id="stock_info_widget" class="widget-info-blue" style="margin-bottom: 25px;">
                        <div style="margin-bottom: 10px;">
                            <label style="font-weight: 700; color: #152e4d; display: flex; align-items: center; gap: 5px;">
                                <i class="ph ph-stack"></i> Pilih Batch <span style="color:red">*</span>
                            </label>
                        </div>

                        <div class="form-group" style="margin-bottom: 15px;">
                            <div style="position: relative;">
                                <select id="stock_id" name="stock_id" required disabled class="form-control"
                                        style="width: 100%; appearance: none; -webkit-appearance: none; -moz-appearance: none; font-weight: 600; color: #152e4d; border-color: #2f8ba9;">
                                    <option value="">-- Pilih Barang Terlebih Dahulu --</option>
                                </select>
                                <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #152e4d; pointer-events: none;"></i>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.8rem; font-weight: 600; color: #152e4d; margin-bottom: 5px; display: block;">Sisa Stok</label>
                                <input type="text" id="view_qty" readonly class="form-control" placeholder="0"
                                       style="background: #e0f2fe; border-color: #bae6fd; color: #0284c7; font-weight: bold; text-align: center;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.8rem; font-weight: 600; color: #152e4d; margin-bottom: 5px; display: block;">Tgl. Prod</label>
                                <input type="text" id="view_prod" readonly class="form-control" placeholder="-"
                                       style="background: #fff; border-color: #cbd5e1; color: #64748b; font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.8rem; font-weight: 600; color: #dc2626; margin-bottom: 5px; display: block;">Tgl. Exp</label>
                                <input type="text" id="view_exp" readonly class="form-control" placeholder="-"
                                       style="background: #fef2f2; border-color: #fecaca; color: #dc2626; font-size: 0.85rem;">
                            </div>
                        </div>
                    </div>

                    <div class="separator" style="margin: 25px 0; border-top: 1px dashed #e2e8f0;"></div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-weight: 600; color: var(--neutral-gray); margin-bottom: 8px; display: block;">
                                Jenis / Kategori <span style="color:red">*</span>
                            </label>
                            <div style="position: relative;">
                                <select name="status_id_tujuan" required class="form-control" style="appearance: none; height: 48px;">
                                    <option value="">-- Pilih Kondisi Akhir --</option>
                                    <?php foreach($data['status'] as $st): ?>
                                        <?php if(strtolower($st['nama_status']) !== 'tersedia'): ?>
                                            <option value="<?php echo $st['status_id']; ?>">
                                                <?php echo $st['nama_status']; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-weight: 600; color: var(--neutral-gray); margin-bottom: 8px; display: block;">
                                Jumlah <span style="color:red">*</span>
                            </label>
                            <input type="number" id="jumlah" name="jumlah" value="1" min="1" required class="form-control" 
                                   style="font-weight: bold; color: #ef4444; font-size: 1.1rem; height: 48px;">
                            <small id="jumlah_max_info" style="font-weight: 600; margin-top: 5px; display: block; color: #64748b;"></small>
                        </div>

                    </div>

                    <div class="form-group" style="margin-top: 20px; margin-bottom: 0;">
                        <label style="font-weight: 600; color: var(--neutral-gray); margin-bottom: 8px; display: block;">
                            Keterangan / Kronologi
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="2" class="form-control" placeholder="Jelaskan kondisi kerusakan atau alasan retur..."></textarea>
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
                    <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 10px; line-height: 1.4;">
                        Scan kode barang untuk memilih barang otomatis.
                    </p>
                </div>

                <div class="card">
                    <h5 style="color: var(--primer-darkblue); font-weight: 700; margin-bottom: 15px;">📸 Foto Bukti Fisik</h5>
                    <div id="preview-container" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; min-height: 50px;">
                        <div class="upload-area-dashed">
                            Belum ada foto dipilih
                        </div>
                    </div>
                    <button type="button" id="btn-add-file" class="btn" 
                            style="width: 100%; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 600; justify-content: center;">
                        <i class="ph ph-plus"></i> Tambah Foto
                    </button>
                    <input type="file" id="bukti_foto_input" accept="image/*" style="display: none;">
                    <input type="file" id="bukti_foto_final" name="bukti_foto[]" multiple style="display: none;">
                </div>

                <div class="action-buttons-grid">
                    <button type="reset" class="btn-action-reset" id="btnResetRetur">
                        <i class="ph ph-arrows-counter-clockwise"></i> Reset
                    </button>
                    <button type="submit" class="btn-action-save" style="background-color: #ef4444; border-color: #ef4444; color: white;">
                        <i class="ph ph-warning-circle"></i> Laporkan
                    </button>
                </div>

            </div>

        </div>
    </form>
</main>

<script src="<?php echo BASE_URL; ?>js/html5-qrcode.min.js"></script>

<style>
    /* Override hover untuk tombol simpan jadi merah gelap */
    .btn-action-save:hover {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4) !important;
    }
</style>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>