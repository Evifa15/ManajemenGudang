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
        <h1>Manajemen Lokasi</h1>
        <a href="<?php echo BASE_URL; ?>admin/addLokasi" class="btn btn-primary">+ Tambah Lokasi Baru</a>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>admin/lokasi" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Cari Kode, Nama Rak, atau Zona..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Kode Lokasi</th>
                    <th>Nama Rak / Area</th>
                    <th>Zona</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Loop data lokasi
                    foreach ($data['lokasi'] as $lok) : 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($lok['kode_lokasi']); ?></td>
                    <td><?php echo htmlspecialchars($lok['nama_rak']); ?></td>
                    <td><?php echo htmlspecialchars($lok['zona']); ?></td>
                    <td><?php echo htmlspecialchars($lok['deskripsi']); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>admin/editLokasi/<?php echo $lok['lokasi_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        
                        <button type="button" 
                                class="btn btn-danger btn-sm btn-delete" 
                                data-url="<?php echo BASE_URL; ?>admin/deleteLokasi/<?php echo $lok['lokasi_id']; ?>">
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
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $searchQuery = !empty($data['search']) ? '?search=' . urlencode($data['search']) : '';
                ?>
                <?php if ($currentPage > 1) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/lokasi/<?php echo $currentPage - 1; ?><?php echo $searchQuery; ?>">Previous</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo BASE_URL; ?>admin/lokasi/<?php echo $i; ?><?php echo $searchQuery; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/lokasi/<?php echo $currentPage + 1; ?><?php echo $searchQuery; ?>">Next</a></li>
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