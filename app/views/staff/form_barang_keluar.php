<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Form Input Barang Keluar</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>staff/processBarangKeluar" method="POST">
            
            <fieldset>
                <legend>Pilih Barang</legend>
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
                <legend>Stok Tersedia (Diurutkan FEFO)</legend>
                <div class="form-group">
                    <label for="stock_id">Pilih Stok (Lot/Lokasi)</label>
                    <select id="stock_id" name="stock_id" required>
                        </select>
                </div>
                <div id="stock_details" style="color: #333; margin-bottom: 15px;">
                    </div>
            </fieldset>

            <fieldset>
                <legend>Detail Pengeluaran</legend>
                <div class="form-group">
                    <label for="jumlah">Jumlah Keluar</label>
                    <input type="number" id="jumlah" name="jumlah" value="1" min="1" required>
                    <small id="jumlah_max_info" style="color: red;"></small>
                </div>
                <div class="form-group">
                    <label for="keterangan">Tujuan / Keterangan (Wajib)</label>
                    <textarea id="keterangan" name="keterangan" rows="3" 
                              placeholder="Misal: Penjualan Ritel, Sample Marketing" required></textarea>
                </div>
            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Transaksi Keluar</button>
            </div>
        </form>
    </div>
</main>

<script>
    // URL dasar untuk AJAX
    const STOCK_INFO_URL = "<?php echo BASE_URL; ?>staff/getStockInfo/";
    
    // Elemen-elemen DOM
    const productSelect = document.getElementById('product_id');
    const stockSelect = document.getElementById('stock_id');
    const stockWidget = document.getElementById('stock_info_widget');
    const stockDetails = document.getElementById('stock_details');
    const jumlahInput = document.getElementById('jumlah');
    const jumlahMaxInfo = document.getElementById('jumlah_max_info');

    let availableStockData = []; // Menyimpan data stok dari server

    // 1. Saat user memilih barang
    productSelect.addEventListener('change', async function() {
        const productId = this.value;
        
        // Reset form
        stockWidget.style.display = 'none';
        stockSelect.innerHTML = '<option value="">-- Loading... --</option>';
        stockDetails.innerHTML = '';
        jumlahInput.max = null;
        jumlahMaxInfo.textContent = '';
        availableStockData = [];

        if (!productId) {
            stockSelect.innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
            return;
        }

        // 2. Panggil API (method getStockInfo)
        try {
            const response = await fetch(STOCK_INFO_URL + productId);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            availableStockData = await response.json();

            // 3. Tampilkan widget dan isi dropdown stok
            if (availableStockData.length > 0) {
                stockWidget.style.display = 'block';
                stockSelect.innerHTML = ''; // Kosongkan
                
                // Tambahkan opsi (Rekomendasi FEFO)
                availableStockData.forEach((stock, index) => {
                    const expDate = stock.exp_date ? `(Exp: ${stock.exp_date})` : '(No Exp)';
                    const text = `Lokasi: ${stock.kode_lokasi} | Lot: ${stock.lot_number || '-'} | Sisa: ${stock.quantity} | ${expDate}`;
                    
                    const option = document.createElement('option');
                    option.value = stock.stock_id; // Kirim stock_id
                    option.textContent = text;
                    
                    // Rekomendasikan yang pertama (karena sudah diurutkan FEFO)
                    if (index === 0) {
                        option.textContent += ' [REKOMENDASI]';
                        option.selected = true;
                    }
                    stockSelect.appendChild(option);
                });
                
                // Picu 'change' di dropdown stok untuk pertama kali
                stockSelect.dispatchEvent(new Event('change'));

            } else {
                stockWidget.style.display = 'block';
                stockSelect.innerHTML = '<option value="">-- Stok Tidak Tersedia --</option>';
            }

        } catch (error) {
            stockWidget.style.display = 'block';
            stockSelect.innerHTML = '<option value="">-- Gagal memuat stok --</option>';
            console.error('Fetch error:', error);
        }
    });

    // 4. Saat user memilih lot/stok spesifik
    stockSelect.addEventListener('change', function() {
        const selectedStockId = this.value;
        // Cari data stok yang sesuai di array
        const selectedStock = availableStockData.find(s => s.stock_id == selectedStockId);

        if (selectedStock) {
            // 5. Set validasi 'max' di input jumlah
            jumlahInput.max = selectedStock.quantity;
            jumlahMaxInfo.textContent = `Stok tersedia di lot/lokasi ini: ${selectedStock.quantity}`;
        } else {
            jumlahInput.max = null;
            jumlahMaxInfo.textContent = '';
        }
    });

    // 6. Validasi jumlah saat user mengetik
    jumlahInput.addEventListener('input', function() {
        const max = parseInt(this.max, 10);
        const currentValue = parseInt(this.value, 10);
        if (currentValue > max) {
            this.value = max; // Otomatis set ke nilai max
            jumlahMaxInfo.textContent = `Stok HANYA tersisa ${max} di lot/lokasi ini!`;
        } else {
            jumlahMaxInfo.textContent = `Stok tersedia di lot/lokasi ini: ${max}`;
        }
    });

</script>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>