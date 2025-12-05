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
        <h1>Manajemen Satuan</h1>
        <a href="<?php echo BASE_URL; ?>admin/addSatuan" class="btn btn-primary">+ Tambah Satuan Baru</a>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>admin/satuan" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Cari Nama Satuan atau Singkatan..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>ID Satuan</th>
                    <th>Nama Satuan</th>
                    <th>Singkatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Loop data satuan
                    foreach ($data['satuan'] as $sat) : 
                ?>
                <tr>
                    <td><?php echo $sat['satuan_id']; ?></td>
                    <td><?php echo htmlspecialchars($sat['nama_satuan']); ?></td>
                    <td><?php echo htmlspecialchars($sat['singkatan']); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>admin/editSatuan/<?php echo $sat['satuan_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        
                        <button type="button" 
                                class="btn btn-danger btn-sm btn-delete" 
                                data-url="<?php echo BASE_URL; ?>admin/deleteSatuan/<?php echo $sat['satuan_id']; ?>">
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
                    $searchQuery = !empty($data['search']) ? '?search=' . urlencode($data['search']) : '';
                ?>
                <?php if ($currentPage > 1) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/satuan/<?php echo $currentPage - 1; ?><?php echo $searchQuery; ?>">Previous</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo BASE_URL; ?>admin/satuan/<?php echo $i; ?><?php echo $searchQuery; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/satuan/<?php echo $currentPage + 1; ?><?php echo $searchQuery; ?>">Next</a></li>
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