<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
    
    // Siapkan data JSON untuk JavaScript
    $productsJson = json_encode($data['products']);
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
                <legend>Detail Barang</legend>
                <div class="form-group">
                    <label for="product_id">Pilih Barang</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach($data['products'] as $prod): ?>
                            <option value="<?php echo $prod['product_id']; ?>" data-lacak_lot="<?php echo $prod['lacak_lot_serial']; ?>">
                                <?php echo $prod['kode_barang'] . ' - ' . $prod['nama_barang']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="lot_tracking_fields" style="display: none;">
                    <div class="form-group">
                        <label for="lot_number">Nomor Lot / Batch</label>
                        <input type="text" id="lot_number" name="lot_number" placeholder="Misal: BATCH-001">
                    </div>
                    <div class="form-group">
                        <label for="exp_date">Tanggal Kedaluwarsa (Opsional)</label>
                        <input type="date" id="exp_date" name="exp_date">
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
                    <textarea id="keterangan" name="keterangan" rows="3" placeholder="Misal: Dus sedikit basah"></textarea>
                </div>

                <div class="form-group">
                    <label for="bukti_foto">Upload Foto Nota / Surat Jalan (Opsional)</label>
                    <input type="file" id="bukti_foto" name="bukti_foto" accept="image/png, image/jpeg, application/pdf">
                </div>
            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Transaksi Masuk</button>
            </div>
        </form>
    </div>
</main>

<script>
    // Ambil data produk dari PHP
    const productsData = <?php echo $productsJson; ?>;
    
    const productMap = new Map();
    productsData.forEach(prod => {
        // Pastikan product_id adalah string untuk pencocokan
        productMap.set(String(prod.product_id), prod.lacak_lot_serial);
    });

    const productSelect = document.getElementById('product_id');
    const lotFields = document.getElementById('lot_tracking_fields');
    const lotNumberInput = document.getElementById('lot_number');

    productSelect.addEventListener('change', function() {
        const selectedProductId = this.value;
        const lacakLot = productMap.get(selectedProductId);

        if (lacakLot == "1") {
            lotFields.style.display = 'block'; 
            lotNumberInput.required = true;    
        } else {
            lotFields.style.display = 'none';  
            lotNumberInput.required = false;   
            lotNumberInput.value = '';         
        }
    });
</script>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>