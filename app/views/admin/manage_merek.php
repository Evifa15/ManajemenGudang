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
        <h1>Manajemen Merek</h1>
        <a href="<?php echo BASE_URL; ?>admin/addMerek" class="btn btn-primary">+ Tambah Merek Baru</a>
    </div>

    <!-- Form Pencarian -->
    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>admin/merek" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Cari Nama Merek..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <!-- Tabel Data Merek -->
    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>ID Merek</th> <th>Nama Merek</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Loop data merek
                    foreach ($data['merek'] as $mrk) : 
                ?>
                <tr>
                    <td><?php echo $mrk['merek_id']; ?></td>
                    <td><?php echo htmlspecialchars($mrk['nama_merek']); ?></td>
                    <td>
                        <!-- Tombol Aksi -->
                        <a href="<?php echo BASE_URL; ?>admin/editMerek/<?php echo $mrk['merek_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        
                        <button type="button" 
                                class="btn btn-danger btn-sm btn-delete" 
                                data-url="<?php echo BASE_URL; ?>admin/deleteMerek/<?php echo $mrk['merek_id']; ?>">
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

    <!-- Paginasi -->
    <div class="pagination-container">
        <span class="pagination-info">Menampilkan Halaman <?php echo $data['currentPage']; ?> dari <?php echo $data['totalPages']; ?></span>
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $searchQuery = !empty($data['search']) ? '?search=' . urlencode($data['search']) : '';
                ?>
                <!-- Tombol Previous -->
                <?php if ($currentPage > 1) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/merek/<?php echo $currentPage - 1; ?><?php echo $searchQuery; ?>">Previous</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <?php endif; ?>
                <!-- Tombol Angka -->
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>"><a class="page-link" href="<?php echo BASE_URL; ?>admin/merek/<?php echo $i; ?><?php echo $searchQuery; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <!-- Tombol Next -->
                <?php if ($currentPage < $totalPages) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/merek/<?php echo $currentPage + 1; ?><?php echo $searchQuery; ?>">Next</a></li>
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