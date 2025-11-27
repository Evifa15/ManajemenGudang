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
        <h1>Manajemen Pengguna</h1>
        <div class="header-buttons">
            <button type="button" id="btnBulkDelete" class="btn btn-danger" style="display: none; margin-right: 10px;">
                🗑️ Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>

            <button type="button" 
                    class="btn btn-success btn-import-users" 
                    data-url="<?php echo BASE_URL; ?>admin/importUsers">
                📂 Import CSV
            </button>
            
            <a href="<?php echo BASE_URL; ?>admin/addUser" class="btn btn-primary">+ Tambah Pengguna Baru</a>
        </div>
    </div>

    <div class="search-container" style="display: flex; gap: 15px; align-items: flex-start;">
        
        <div style="flex: 2;">
            <input type="text" id="liveSearchInput" class="search-input" 
                   placeholder="Ketik Nama atau Email untuk mencari..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>"
                   data-base-url="<?php echo BASE_URL; ?>"
                   data-current-user-id="<?php echo $_SESSION['user_id']; ?>"
                   style="width: 100%;">
        </div>

        <form action="<?php echo BASE_URL; ?>admin/users" method="GET" style="flex: 1;">
            <select name="role" id="filterRole" class="filter-select" style="width: 100%; cursor: pointer;">
                <option value="">-- Filter Sesuai Role --</option>
                <option value="admin" <?php if($data['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                <option value="staff" <?php if($data['role'] == 'staff') echo 'selected'; ?>>Staff Gudang</option>
                <option value="pemilik" <?php if($data['role'] == 'pemilik') echo 'selected'; ?>>Pemilik</option>
                <option value="peminjam" <?php if($data['role'] == 'peminjam') echo 'selected'; ?>>Peminjam</option>
            </select>
        </form>

    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" id="selectAll" style="transform: scale(1.2); cursor: pointer;">
                    </th>
                    <th>Nama Lengkap</th>
                    <th>Email (Login)</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php foreach ($data['users'] as $user) : ?>
                <tr>
                    <td style="text-align: center;">
                        <?php if($user['user_id'] != $_SESSION['user_id']): ?>
                            <input type="checkbox" class="user-checkbox" value="<?php echo $user['user_id']; ?>" style="transform: scale(1.2); cursor: pointer;">
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span style="text-transform: capitalize; font-weight: bold;">
                            <?php echo htmlspecialchars($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>admin/editUser/<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        
                        <?php if($user['user_id'] != $_SESSION['user_id']): ?>
                        <button type="button" 
                                class="btn btn-danger btn-sm btn-delete" 
                                data-url="<?php echo BASE_URL; ?>admin/deleteUser/<?php echo $user['user_id']; ?>">
                            Hapus
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $queryParams = [];
                    if (!empty($data['search'])) $queryParams['search'] = $data['search'];
                    if (!empty($data['role'])) $queryParams['role'] = $data['role'];
                    $filterQuery = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
                ?>

                <?php if ($currentPage > 1) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/users/<?php echo $currentPage - 1; ?><?php echo $filterQuery; ?>">Previous</a></li>
                <?php else : ?>
                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>admin/users/<?php echo $i; ?><?php echo $filterQuery; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($currentPage < $totalPages) : ?>
                    <li class="page-item"><a class="page-link" href="<?php echo BASE_URL; ?>admin/users/<?php echo $currentPage + 1; ?><?php echo $filterQuery; ?>">Next</a></li>
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