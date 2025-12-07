<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
    
    $kode = $data['kode'] ?? ''; 
    $nama = $data['nama'] ?? '';
?>

<main class="app-content">
    <div class="print-layout-grid">
        
        <div class="col-left">
            <div class="settings-card"> 
                <div class="settings-header">
                    <i class="ph ph-sliders-horizontal" style="color: var(--primer-yellow); font-size: 1.5rem;"></i>
                    Konfigurasi Label
                </div>
                
                <form id="labelForm">
                    
                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label style="font-size: 0.8rem; color: #64748b;">Kode Barang</label>
                            <input type="text" class="form-control" id="kodeBarang" value="<?= htmlspecialchars($kode); ?>" readonly 
                                   style="font-weight: bold; color: var(--primer-darkblue); border: none; background: transparent; padding: 0;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.8rem; color: #64748b;">Nama Barang</label>
                            <div style="font-weight: 600; color: var(--text-main); line-height: 1.3;" id="namaBarangDisplay">
                                <?= htmlspecialchars($nama); ?>
                            </div>
                            <input type="hidden" id="namaBarang" value="<?= htmlspecialchars($nama); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="barcodeType">Tipe Kode</label>
                        <div style="position: relative;">
                            <select class="form-control" id="barcodeType" style="appearance: none;">
                                <option value="qrcode">QR Code (Modern)</option>
                                <option value="barcode">Barcode 1D (Klasik)</option>
                            </select>
                            <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="labelSize">Ukuran Stiker</label>
                        <div style="position: relative;">
                            <select class="form-control" id="labelSize" style="appearance: none;">
                                <option value="50x50">Medium Square (50mm x 50mm)</option>
                                <option value="40x30">Small Rect (40mm x 30mm)</option>
                                <option value="30x20">Tiny (30mm x 20mm)</option>
                                <option value="60x40">Large Rect (60mm x 40mm)</option>
                            </select>
                            <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 25px;">
                        <label style="margin-bottom: 10px; display: block;">Opsi Tampilan</label>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label class="custom-checkbox" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" id="showName" checked style="width: 18px; height: 18px; accent-color: var(--primer-darkblue);"> 
                                <span>Tampilkan Nama Barang</span>
                            </label>
                            
                            <label class="custom-checkbox" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" id="showCode" checked style="width: 18px; height: 18px; accent-color: var(--primer-darkblue);"> 
                                <span>Tampilkan Kode (Text)</span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <div class="dropdown-export-wrapper" style="width: 100%;">
                            <button type="button" id="btnExportMenu" class="btn btn-brand-dark" style="width: 100%; justify-content: center; height: 45px;">
                                <i class="ph ph-printer" style="font-size: 1.2rem;"></i> Cetak / Download
                                <i class="ph ph-caret-down" style="margin-left: auto;"></i>
                            </button>
                            
                            <div id="exportDropdown" class="dropdown-menu-custom" style="width: 100%;">
                                <a href="#" class="btn-export-action export-opt" data-format="print">
                                    <i class="ph ph-printer" style="color: #64748b;"></i> Cetak ke Printer
                                </a>
                                <a href="#" class="btn-export-action export-opt" data-format="png">
                                    <i class="ph ph-image" style="color: #0ea5e9;"></i> Download PNG
                                </a>
                                <a href="#" class="btn-export-action export-opt" data-format="jpg">
                                    <i class="ph ph-image-square" style="color: #10b981;"></i> Download JPG
                                </a>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="col-right">
            <div class="preview-card">
                <div class="settings-header">
                    <i class="ph ph-eye" style="color: var(--primer-lightblue); font-size: 1.5rem;"></i>
                    Live Preview
                </div>
                
                <div class="preview-stage">
                    <div id="printArea">
                        </div>
                </div>

                <p style="text-align: center; color: #94a3b8; font-size: 0.85rem; margin-top: 15px;">
                    *Preview ini adalah representasi visual. Kualitas cetak asli akan beresolusi tinggi.
                </p>
            </div>
        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script src="<?php echo BASE_URL; ?>js/modules/print-label.js?v=3.0"></script>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>