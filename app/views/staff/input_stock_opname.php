<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';

    // Ambil data produk JSON (sama seperti form transaksi)
    $productsJson = json_encode($data['products']);
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Input Stock Opname</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    
    <?php if (!$data['isOpnameActive']): ?>
        <div class="flash-message error" style="text-align: center; font-size: 1.2em; padding: 40px;">
            ❌ Tidak ada periode **Stock Opname** yang sedang aktif. <br>
            Hubungi Admin jika Anda merasa ini adalah kesalahan.
        </div>
    <?php else: ?>
        <div class="flash-message success" style="text-align: center;">
            PERIODE OPNAME SEDANG AKTIF. Harap segera lakukan penghitungan fisik.
        </div>

        <div class="form-container">
            <form action="<?php echo BASE_URL; ?>staff/processInputOpname" method="POST">
                
                <fieldset>
                    <legend>Pencatatan Hitungan Fisik</legend>
                    
                    <div class="form-group">
                        <label for="product_id">Pilih Barang (atau Scan Barcode)</label>
                        <select id="product_id" name="product_id" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach($data['products'] as $prod): ?>
                                <option value="<?php echo $prod['product_id']; ?>" data-lacak_lot="<?php echo $prod['lacak_lot_serial']; ?>">
                                    <?php echo $prod['kode_barang'] . ' - ' . $prod['nama_barang']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: blue;">(Asumsi: Scan Barcode akan mengisi field ini secara otomatis)</small>
                    </div>

                    <div id="lot_tracking_fields" class="form-group" style="display: none;">
                        <label for="lot_number">Nomor Lot / Batch</label>
                        <input type="text" id="lot_number" name="lot_number" placeholder="Wajib diisi jika barang dilacak">
                    </div>

                    <div class="form-group">
                        <label for="stok_fisik">Jumlah Hitungan Fisik (Wajib)</label>
                        <input type="number" id="stok_fisik" name="stok_fisik" min="0" required placeholder="Masukkan jumlah yang ADA di rak.">
                    </div>

                    <div class="form-group">
                        <label for="catatan">Catatan</label>
                        <textarea id="catatan" name="catatan" rows="2" placeholder="Contoh: Stok sistem tidak sesuai, ditemukan kelebihan 5 pcs."></textarea>
                    </div>

                </fieldset>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Hitungan Stok</button>
                    </div>
            </form>
        </div>
    <?php endif; ?>
</main>

<script>
    const productsData = <?php echo $productsJson; ?>;
    const productMap = new Map();
    productsData.forEach(prod => {
        productMap.set(String(prod.product_id), prod.lacak_lot_serial);
    });

    const productSelect = document.getElementById('product_id');
    const lotFields = document.getElementById('lot_tracking_fields');
    const lotNumberInput = document.getElementById('lot_number');

    productSelect.addEventListener('change', function() {
        const selectedProductId = this.value;
        const selectedProduct = productsData.find(p => String(p.product_id) === selectedProductId);

        if (selectedProduct && selectedProduct.lacak_lot_serial == "1") {
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