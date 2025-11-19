<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Form Lapor Barang Rusak / Retur</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>staff/processReturBarang" method="POST">
            
            <fieldset>
                <legend>Pilih Barang (Stok yang Tersedia)</legend>
                <div class="form-group">
                    <label for="product_id">Pilih Barang</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach($data['products'] as $prod): ?>
                            <option value="<?php echo $prod['product_id']; ?>">
                                <?php echo $prod['kode_barang'] . ' - ' . $prod['nama_barang']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <fieldset id="stock_info_widget" style="display: none;">
                <legend>Pilih Stok (Lot/Lokasi) yang Akan Dikurangi</legend>
                <div class="form-group">
                    <label for="stock_id">Pilih Stok (Lot/Lokasi)</label>
                    <select id="stock_id" name="stock_id" required>
                        </select>
                </div>
                <div id="stock_details" style="color: #333; margin-bottom: 15px;">
                    </div>
            </fieldset>

            <fieldset>
                <legend>Detail Kerusakan / Retur</legend>
                <div class="form-group">
                    <label for="jumlah">Jumlah Rusak/Retur</label>
                    <input type="number" id="jumlah" name="jumlah" value="1" min="1" required>
                    <small id="jumlah_max_info" style="color: red;"></small>
                </div>

                <div class="form-group">
                    <label for="status_id_tujuan">Pindahkan ke Status (Wajib)</label>
                    <select id="status_id_tujuan" name="status_id_tujuan" required>
                        <option value="">-- Pilih Status Baru --</option>
                        <?php foreach($data['status'] as $stat): ?>
                            <?php // Jangan tampilkan 'Tersedia' sebagai pilihan tujuan ?>
                            <?php if (strtolower($stat['nama_status']) != 'tersedia'): ?>
                                <option value="<?php echo $stat['status_id']; ?>"
                                    <?php // Otomatis pilih 'Rusak' jika ada ?>
                                    <?php if(strtolower($stat['nama_status']) == 'rusak') echo 'selected'; ?>>
                                    <?php echo $stat['nama_status']; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="keterangan">Sumber / Keterangan (Wajib)</label>
                    <textarea id="keterangan" name="keterangan" rows="3" 
                              placeholder="Misal: Retur dari Pelanggan, Temuan di Rak (Rusak Internal)" required></textarea>
                </div>
                
                 <input type="hidden" id="exp_date" name="exp_date">

            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Laporan</button>
            </div>
        </form>
    </div>
</main>

<script>
    // URL dasar untuk AJAX
    const STOCK_INFO_URL = "<?php echo BASE_URL; ?>staff/getStockInfo/";
    
    const productSelect = document.getElementById('product_id');
    const stockSelect = document.getElementById('stock_id');
    const stockWidget = document.getElementById('stock_info_widget');
    const stockDetails = document.getElementById('stock_details');
    const jumlahInput = document.getElementById('jumlah');
    const jumlahMaxInfo = document.getElementById('jumlah_max_info');
    const expDateInput = document.getElementById('exp_date'); // Input hidden

    let availableStockData = []; 

    // 1. Saat user memilih barang
    productSelect.addEventListener('change', async function() {
        // ... (Logika reset sama seperti barang keluar) ...
        stockWidget.style.display = 'none';
        stockSelect.innerHTML = '<option value="">-- Loading... --</option>';
        stockDetails.innerHTML = '';
        jumlahInput.max = null;
        jumlahMaxInfo.textContent = '';
        availableStockData = [];
        expDateInput.value = '';

        const productId = this.value;
        if (!productId) {
            stockSelect.innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
            return;
        }

        // 2. Panggil API (method getStockInfo)
        try {
            const response = await fetch(STOCK_INFO_URL + productId);
            availableStockData = await response.json();

            // 3. Tampilkan widget dan isi dropdown stok
            if (availableStockData.length > 0) {
                stockWidget.style.display = 'block';
                stockSelect.innerHTML = ''; 
                
                availableStockData.forEach((stock, index) => {
                    const expDate = stock.exp_date ? `(Exp: ${stock.exp_date})` : '(No Exp)';
                    const text = `Lokasi: ${stock.kode_lokasi} | Lot: ${stock.lot_number || '-'} | Sisa: ${stock.quantity} | ${expDate}`;
                    
                    const option = document.createElement('option');
                    option.value = stock.stock_id;
                    option.textContent = text;
                    stockSelect.appendChild(option);
                });
                
                stockSelect.dispatchEvent(new Event('change'));

            } else {
                stockWidget.style.display = 'block';
                stockSelect.innerHTML = '<option value="">-- Stok Tersedia Tidak Ditemukan --</option>';
            }

        } catch (error) {
            // ... (Logika error) ...
        }
    });

    // 4. Saat user memilih lot/stok spesifik
    stockSelect.addEventListener('change', function() {
        const selectedStockId = this.value;
        const selectedStock = availableStockData.find(s => s.stock_id == selectedStockId);

        if (selectedStock) {
            // 5. Set validasi 'max' dan simpan exp_date
            jumlahInput.max = selectedStock.quantity;
            jumlahMaxInfo.textContent = `Stok tersedia di lot/lokasi ini: ${selectedStock.quantity}`;
            expDateInput.value = selectedStock.exp_date; // Simpan exp_date
        } else {
            // ... (Reset) ...
        }
    });

    // 6. Validasi jumlah (Sama seperti barang keluar)
    jumlahInput.addEventListener('input', function() {
        // ... (Logika validasi max) ...
    });
</script>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>