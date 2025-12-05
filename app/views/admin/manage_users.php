<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <?php
        // Blok Notifikasi Flash Message
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="toolbar-floating">
        
        <div class="search-box-wrapper">
            <i class="ph ph-magnifying-glass search-icon"></i>
            <input type="text" id="liveSearchInput" class="table-search-input" 
                   placeholder="Cari Nama atau Email..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>"
                   data-base-url="<?php echo BASE_URL; ?>"
                   data-current-user-id="<?php echo $_SESSION['user_id']; ?>">
        </div>

        <div class="toolbar-actions">
            
            <select id="filterRole" class="filter-select-clean" style="width: 160px; margin-right: 5px; cursor: pointer;">
                <option value="">Semua Role</option>
                <option value="admin" <?php if($data['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                <option value="staff" <?php if($data['role'] == 'staff') echo 'selected'; ?>>Staff Gudang</option>
                <option value="pemilik" <?php if($data['role'] == 'pemilik') echo 'selected'; ?>>Pemilik</option>
                <option value="peminjam" <?php if($data['role'] == 'peminjam') echo 'selected'; ?>>Peminjam</option>
            </select>

            <button type="button" id="btnBulkDelete" class="btn btn-brand-dark btn-sm" style="display: none; padding: 8px 15px;">
                <i class="ph ph-trash"></i> Hapus (<span id="selectedCount">0</span>)
            </button>

            <button type="button" 
                    class="btn btn-brand-dark btn-sm btn-import-users" 
                    style="padding: 8px 15px;"
                    data-url="<?php echo BASE_URL; ?>admin/importUsers">
                <i class="ph ph-file-csv"></i> Import
            </button>
            
            <a href="<?php echo BASE_URL; ?>admin/addUser" class="btn btn-brand-dark btn-sm" style="padding: 8px 20px;">
                <i class="ph ph-plus"></i> Tambah Pengguna
            </a>
        </div>
    </div>

    <div class="table-card">
        <div class="table-wrapper-flat">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAll" style="transform: scale(1.2); cursor: pointer;">
                        </th>
                        <th>Nama Lengkap</th>
                        <th>Email (Login)</th>
                        <th>Role</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php if (empty($data['users'])): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 20px; color: #666;">Data tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['users'] as $user) : ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php if($user['user_id'] != $_SESSION['user_id']): ?>
                                    <input type="checkbox" class="user-checkbox" value="<?php echo $user['user_id']; ?>" style="transform: scale(1.2); cursor: pointer;">
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong>
                                
                            </td>
                            
                            <td style="color: #666;"><?php echo htmlspecialchars($user['email']); ?></td>
                            
                            <td>
                                <?php 
                                    $roleClass = '';
                                    if($user['role'] == 'admin') $roleClass = 'color: #7c3aed; background: #f3e8ff; border: 1px solid #d8b4fe;';
                                    elseif($user['role'] == 'staff') $roleClass = 'color: #059669; background: #ecfdf5; border: 1px solid #6ee7b7;';
                                    elseif($user['role'] == 'pemilik') $roleClass = 'color: #d97706; background: #fffbeb; border: 1px solid #fde68a;';
                                    else $roleClass = 'color: #4b5563; background: #f3f4f6; border: 1px solid #d1d5db;';
                                ?>
                                <span style="text-transform: capitalize; font-weight: 700; font-size: 0.8rem; padding: 4px 10px; border-radius: 20px; <?php echo $roleClass; ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            
                            <td>
                                <div class="action-buttons">
                                    
                                    

                                    <a href="<?php echo BASE_URL; ?>admin/editUser/<?php echo $user['user_id']; ?>" class="btn-icon edit" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                    
                                    <?php if($user['user_id'] != $_SESSION['user_id']): ?>
                                    <button type="button" 
                                            class="btn-icon delete btn-delete" 
                                            data-url="<?php echo BASE_URL; ?>admin/deleteUser/<?php echo $user['user_id']; ?>"
                                            title="Hapus">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-container custom-pagination" id="paginationContainerUsers">
        <span class="pagination-info">Menampilkan Halaman <?php echo $data['currentPage']; ?> dari <?php echo $data['totalPages']; ?></span>
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    
                    // Pertahankan parameter search/filter saat pindah halaman
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

                <?php 
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
                
                if($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

                for ($i = $start; $i <= $end; $i++) : ?>
                    <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>admin/users/<?php echo $i; ?><?php echo $filterQuery; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; ?>
                
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