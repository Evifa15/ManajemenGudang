<?php
    // 1. Panggil Header
    require_once APPROOT . '/views/templates/header.php';
    // 2. Panggil Sidebar KHUSUS PEMILIK
    require_once APPROOT . '/views/templates/sidebar_pemilik.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Lihat Daftar Barang (Katalog)</h1>
        </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>pemilik/viewBarang" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Cari Kode atau Nama Barang..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>">
            
            <select name="kategori" class="filter-select">
                <option value="">Semua Kategori</option>
                <?php foreach($data['allKategori'] as $kat): ?>
                    <option value="<?php echo $kat['kategori_id']; ?>" <?php if($data['kategori_filter'] == $kat['kategori_id']) echo 'selected'; ?>>
                        <?php echo $kat['nama_kategori']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="merek" class="filter-select">
                <option value="">Semua Merek</option>
                 <?php foreach($data['allMerek'] as $mrk): ?>
                    <option value="<?php echo $mrk['merek_id']; ?>" <?php if($data['merek_filter'] == $mrk['merek_id']) echo 'selected'; ?>>
                        <?php echo $mrk['nama_merek']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="lokasi" class="filter-select">
                <option value="">Semua Lokasi</option>
                 <?php foreach($data['allLokasi'] as $lok): ?>
                    <option value="<?php echo $lok['lokasi_id']; ?>" <?php if($data['lokasi_filter'] == $lok['lokasi_id']) echo 'selected'; ?>>
                        <?php echo $lok['kode_lokasi']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter / Cari</button>
        </form>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Merek</th>
                    <th>Stok Saat Ini</th>
                    <th>Satuan</th>
                    <th>Stok Min.</th>
                    <th>Lokasi (Contoh)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['products'] as $prod) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($prod['kode_barang']); ?></td>
                    <td><?php echo htmlspecialchars($prod['nama_barang']); ?></td>
                    <td><?php echo htmlspecialchars($prod['nama_kategori']); ?></td>
                    <td><?php echo htmlspecialchars($prod['nama_merek']); ?></td>
                    <td><strong><?php echo (int)$prod['stok_saat_ini']; ?></strong></td> 
                    <td><?php echo htmlspecialchars($prod['nama_satuan']); ?></td>
                    <td><?php echo htmlspecialchars($prod['stok_minimum']); ?></td>
                    <td><?php echo htmlspecialchars($prod['kode_lokasi']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        </div>
</main>

<?php
    // 3. Panggil Footer
    require_once APPROOT . '/views/templates/footer.php';
?>