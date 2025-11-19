<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Cari Lokasi Barang</h1>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>staff/viewLokasi" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Masukkan Nama Barang atau Kode Barang..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>" required>
            <button type="submit" class="btn btn-primary">Cari Lokasi</button>
        </form>
    </div>

    <?php if (!empty($data['search'])): ?>
        <h3 style="margin-top: 20px;">Hasil Pencarian untuk: "<?php echo htmlspecialchars($data['search']); ?>"</h3>
        
        <?php if (empty($data['results'])): ?>
            <div class="flash-message error" style="margin-top: 10px;">
                Barang tidak ditemukan atau stok 'Tersedia' kosong.
            </div>
        <?php else: ?>
            <div class="content-table" style="margin-top: 10px;">
                <table>
                    <thead>
                        <tr>
                            <th>Lokasi (Rak)</th>
                            <th>Nomor Lot/Batch</th>
                            <th>Tgl. Kedaluwarsa</th>
                            <th>Stok di Lokasi Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['results'] as $item) : ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['kode_lokasi']); ?></strong>
                                <small>(<?php echo htmlspecialchars($item['nama_rak']); ?>)</small>
                            </td>
                            <td><?php echo htmlspecialchars($item['lot_number'] ?? '-'); ?></td>
                            <td><?php echo $item['exp_date'] ? date('d-m-Y', strtotime($item['exp_date'])) : '-'; ?></td>
                            <td><strong><?php echo (int)$item['quantity']; ?> Pcs</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>