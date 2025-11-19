<?php
    // 1. Memuat "Kepala" Halaman
    require_once APPROOT . '/views/templates/header.php';
?>

<?php
    // 2. Memuat "Sidebar" Halaman
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <?php
        // Blok Notifikasi (Sudah Benar)
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    <div class="content-header">
        <h1>Manajemen Pengguna</h1>
        <!-- PERBAIKAN: Menghapus index.php?url= -->
        <a href="<?php echo BASE_URL; ?>admin/addUser" class="btn btn-primary">+ Tambah Pengguna Baru</a>
    </div>

    <div class="search-container">
        <form action="<?php echo BASE_URL; ?>admin/users" method="GET">
            <input type="text" name="search" class="search-input" 
                   placeholder="Cari Nama atau Email..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>">
            <select name="role" class="filter-select">
                <option value="">Semua Role</option>
                <option value="admin" <?php if($data['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                <option value="staff" <?php if($data['role'] == 'staff') echo 'selected'; ?>>Staff Gudang</option>
                <option value="pemilik" <?php if($data['role'] == 'pemilik') echo 'selected'; ?>>Pemilik</option>
                <option value="peminjam" <?php if($data['role'] == 'peminjam') echo 'selected'; ?>>Peminjam</option>
            </select>
            
            <select name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="aktif" <?php if($data['status'] == 'aktif') echo 'selected'; ?>>Aktif</option>
                <option value="baru" <?php if($data['status'] == 'baru') echo 'selected'; ?>>Baru</option>
            </select>

            <button type="submit" class="btn btn-primary">Filter / Cari</button>
        </form>
    </div>
        <div class="import-container">
        <form action="<?php echo BASE_URL; ?>admin/importUsers" method="POST" enctype="multipart/form-data">
            <label for="csv_file">Import Pengguna dari CSV:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            <button type="submit" class="btn btn-primary">Import</button>
            <small>(Format: Nama Lengkap, Email, Role)</small>
        </form>
    </div>
    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Email (Login)</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($data['users'] as $user) : 
                ?>
                <tr>
                    <td><?php echo $user['nama_lengkap']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td><?php echo $user['status_login']; ?></td>
                    <td>
                        <!-- PERBAIKAN: Menghapus index.php?url= -->
                        <a href="<?php echo BASE_URL; ?>admin/editUser/<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        
                        <!-- PERBAIKAN: Menghapus index.php?url= -->
                        <button type_button="button" 
                                class="btn btn-danger btn-sm btn-delete" 
                                data-url="<?php echo BASE_URL; ?>admin/deleteUser/<?php echo $user['user_id']; ?>">
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
                
                // ⬇️ --- GANTI BLOK LOGIKA INI --- ⬇️
                // Bangun query string untuk semua filter
                $queryParams = [];
                if (!empty($data['search'])) {
                    $queryParams['search'] = $data['search'];
                }
                if (!empty($data['role'])) {
                    $queryParams['role'] = $data['role'];
                }
                if (!empty($data['status'])) {
                    $queryParams['status'] = $data['status'];
                }
                // http_build_query akan mengubah array menjadi string URL
                // (misal: ?search=evi&role=staff)
                $filterQuery = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
                // ⬆️ --- SAMPAI SINI --- ⬆️
            ?>

            <?php if ($currentPage > 1) : ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>admin/users/<?php echo $currentPage - 1; ?><?php echo $filterQuery; ?>">
                        Previous
                    </a>
                </li>
            <?php else : // ... (sisanya sama) ... ?>
                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <?php endif; ?>


            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo BASE_URL; ?>admin/users/<?php echo $i; ?><?php echo $filterQuery; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages) : ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>admin/users/<?php echo $currentPage + 1; ?><?php echo $filterQuery; ?>">
                        Next
                    </a>
                </li>
            <?php else : // ... (sisanya sama) ... ?>
                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
            <?php endif; ?>

        </ul>
    </nav>
</div>
</main>
<?php
    // 5. Memuat "Kaki" Halaman
    require_once APPROOT . '/views/templates/footer.php';
?>