<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <?php
        // Blok Notifikasi
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    <div class="content-header">
        <h1>Manajemen Kategori</h1>
        <a href="<?php echo BASE_URL; ?>admin/addKategori" class="btn btn-primary">+ Tambah Kategori Baru</a>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>admin/kategori" method="GET">
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
            
            <select name="status" class="filter-select">
                <option value="">Semua Status</option>
                 <?php foreach($data['allStatus'] as $stat): ?>
                    <option value="<?php echo $stat['status_id']; ?>" <?php if($data['status_filter'] == $stat['status_id']) echo 'selected'; ?>>
                        <?php echo $stat['nama_status']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="satuan" class="filter-select">
                <option value="">Semua Satuan</option>
                 <?php foreach($data['allSatuan'] as $sat): ?>
                    <option value="<?php echo $sat['satuan_id']; ?>" <?php if($data['satuan_filter'] == $sat['satuan_id']) echo 'selected'; ?>>
                        <?php echo $sat['nama_satuan']; ?>
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
                    <th>ID Kategori</th> <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Loop data kategori
                    foreach ($data['kategori'] as $kat) : 
                ?>
                <tr>
                    <td><?php echo $kat['kategori_id']; ?></td>
                    <td><?php echo htmlspecialchars($kat['nama_kategori']); ?></td>
                    <td><?php echo htmlspecialchars($kat['deskripsi']); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>admin/editKategori/<?php echo $kat['kategori_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        
                        <button type="button" 
                                class="btn btn-danger btn-sm btn-delete" 
                                data-url="<?php echo BASE_URL; ?>admin/deleteKategori/<?php echo $kat['kategori_id']; ?>">
                            Hapus
                        </button>
                    </td>
                </tr>
                <?php 
                    endforeach; 
                ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <span class="pagination-info">Menampilkan Halaman <?php echo $data['currentPage']; ?> dari <?php echo $data['totalPages']; ?></span>
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $queryParams = [];
                if (!empty($data['search'])) {
                    $queryParams['search'] = $data['search'];
                }
                if (!empty($data['kategori_filter'])) {
                    $queryParams['kategori'] = $data['kategori_filter'];
                }
                if (!empty($data['merek_filter'])) {
                    $queryParams['merek'] = $data['merek_filter'];
                }
                if (!empty($data['status_filter'])) {
                    $queryParams['status'] = $data['status_filter'];
                }
                if (!empty($data['satuan_filter'])) {
                    $queryParams['satuan'] = $data['satuan_filter'];
                }
                if (!empty($data['lokasi_filter'])) {
                    $queryParams['lokasi'] = $data['lokasi_filter'];
                }
                
                $filterQuery = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
                ?>
                <?php if ($currentPage > 1) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/kategori/<?php echo $currentPage - 1; ?><?php echo $searchQuery; ?>">Previous</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo BASE_URL; ?>admin/kategori/<?php echo $i; ?><?php echo $searchQuery; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/kategori/<?php echo $currentPage + 1; ?><?php echo $searchQuery; ?>">Next</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>