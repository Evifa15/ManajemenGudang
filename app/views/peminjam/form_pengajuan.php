<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_peminjam.php';
    $product = $data['product'];
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Pengajuan Peminjaman</h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>peminjam/processPengajuan" method="POST">
            
            <fieldset>
                <legend>Detail Barang</legend>
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <p><strong>Barang yang Diajukan:</strong> <?php echo htmlspecialchars($product['nama_barang']); ?></p>
                <p><strong>Kategori:</strong> <?php echo htmlspecialchars($product['nama_kategori']); ?></p>
            </fieldset>

            <fieldset>
                <legend>Rencana Waktu Peminjaman</legend>
                <div class="form-group">
                    <label for="tgl_rencana_pinjam">Tanggal Rencana Ambil</label>
                    <input type="date" id="tgl_rencana_pinjam" name="tgl_rencana_pinjam" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="tgl_rencana_kembali">Tanggal Rencana Kembali</label>
                    <input type="date" id="tgl_rencana_kembali" name="tgl_rencana_kembali" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                </div>
            </fieldset>

            <fieldset>
                 <legend>Alasan</legend>
                <div class="form-group">
                    <label for="alasan_pinjam">Alasan Kebutuhan / Keterangan (Wajib)</label>
                    <textarea id="alasan_pinjam" name="alasan_pinjam" rows="3" required placeholder="Misal: Untuk keperluan rapat bulanan departemen marketing"></textarea>
                </div>
            </fieldset>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Kirim Pengajuan Peminjaman</button>
                <a href="<?php echo BASE_URL; ?>peminjam/katalog" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>
</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>