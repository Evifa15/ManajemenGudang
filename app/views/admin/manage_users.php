<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    <div class="top-action-bar" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 20px; flex-wrap: nowrap;">
        
        <div class="search-hero-wrapper" style="flex-grow: 1; position: relative; height: 42px; min-width: 200px;">
            <i class="ph ph-magnifying-glass search-icon" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 1.2rem; z-index: 5;"></i>
            <input type="text" id="liveSearchUser" 
                   class="search-hero-input" 
                   placeholder="Cari Nama atau Email..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>"
                   data-base-url="<?php echo BASE_URL; ?>"
                   style="width: 100%; height: 100%; padding: 0 15px 0 45px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none; transition: border-color 0.2s;">
        </div>

        <div class="header-buttons" style="display: flex; gap: 8px; align-items: center; flex-shrink: 0;">
            
            <button type="button" id="btnToggleFilterUser" class="btn btn-secondary" 
                    style="height: 42px; display: flex; align-items: center; border: 1px solid #cbd5e1; color: #64748b; background: #fff; padding: 0 15px; font-weight: 600; white-space: nowrap;">
                 <i class="ph ph-funnel" style="font-size: 1.2rem; margin-right: 5px;"></i> Filter
            </button>

            <button type="button" id="btnBulkDeleteUser" class="btn" 
                    style="display: none; height: 42px; align-items: center; padding: 0 15px; background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; white-space: nowrap;">
                <i class="ph ph-trash" style="font-size: 1.2rem; margin-right: 5px;"></i> Hapus (<span id="selectedCountUser">0</span>)
            </button>

            <button type="button" class="btn btn-brand-dark btn-import-users" 
                    data-url="<?php echo BASE_URL; ?>admin/importUsers"
                    style="height: 42px; display: flex; align-items: center; padding: 0 15px; white-space: nowrap;">
                <i class="ph ph-file-csv" style="font-size: 1.2rem; margin-right: 5px;"></i> Import
            </button>
            
            <a href="<?php echo BASE_URL; ?>admin/addUser" class="btn btn-brand-dark" 
               style="height: 42px; display: flex; align-items: center; padding: 0 20px; white-space: nowrap; text-decoration: none;">
                <i class="ph ph-plus" style="font-size: 1.2rem; margin-right: 5px;"></i> Tambah
            </a>
        </div>
    </div>

    <div id="filterPanelUser" class="filter-panel" style="display: none;">
        <div class="filter-grid">
            
            <div class="filter-item">
                <label class="filter-label">Filter Role (Hak Akses)</label>
                <div style="position: relative;">
                    <select id="filterRoleUser" class="filter-select-clean">
                        <option value="">- Semua Role -</option>
                        <option value="admin" <?php if($data['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="staff" <?php if($data['role'] == 'staff') echo 'selected'; ?>>Staff Gudang</option>
                        <option value="pemilik" <?php if($data['role'] == 'pemilik') echo 'selected'; ?>>Pemilik</option>
                        <option value="peminjam" <?php if($data['role'] == 'peminjam') echo 'selected'; ?>>Peminjam</option>
                    </select>
                    <i class="ph ph-caret-down" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #64748b;"></i>
                </div>
            </div>

            <div class="filter-item"></div> 
            
            <div class="filter-item">
                <label class="filter-label">&nbsp;</label> 
                <button type="button" id="btnResetFilterUser" class="btn-reset-filter" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise" style="margin-right: 5px; font-size: 1.2rem;"></i> Reset
                </button>
            </div>

        </div>
    </div>

    <div class="table-card">
        <div class="table-wrapper-flat">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAllUser" style="transform: scale(1.2); cursor: pointer;">
                        </th>
                        <th>Nama Lengkap</th>
                        <th>Email (Login)</th>
                        <th>Role</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <?php if (empty($data['users'])): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 30px; color: #999;">Data tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['users'] as $user) : ?>
                        <tr>
                            <td style="text-align: center;">
                                <?php if($user['user_id'] != $_SESSION['user_id']): ?>
                                    <input type="checkbox" class="user-checkbox" value="<?php echo $user['user_id']; ?>" style="transform: scale(1.2); cursor: pointer;">
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; background: #e0f2fe; color: #0369a1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                        <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong>
                                </div>
                            </td>
                            
                            <td style="color: #64748b; font-family: sans-serif;"><?php echo htmlspecialchars($user['email']); ?></td>
                            
                            <td>
                                <?php 
                                    $roleClass = '';
                                    if($user['role'] == 'admin') $roleClass = 'color: #7c3aed; background: #f3e8ff; border: 1px solid #d8b4fe;';
                                    elseif($user['role'] == 'staff') $roleClass = 'color: #059669; background: #ecfdf5; border: 1px solid #6ee7b7;';
                                    elseif($user['role'] == 'pemilik') $roleClass = 'color: #d97706; background: #fffbeb; border: 1px solid #fde68a;';
                                    else $roleClass = 'color: #4b5563; background: #f3f4f6; border: 1px solid #d1d5db;';
                                ?>
                                <span style="text-transform: capitalize; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; <?php echo $roleClass; ?>">
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
                    
                    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
                    echo '<li class="page-item '.$prevDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage - 1).'">Previous</a></li>';
                    
                    if($totalPages > 0) {
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        
                        if($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $currentPage) ? 'active' : '';
                            echo '<li class="page-item '.$active.'"><a class="page-link" href="#" data-page="'.$i.'">'.$i.'</a></li>';
                        }

                        if($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }

                    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
                    echo '<li class="page-item '.$nextDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage + 1).'">Next</a></li>';
                ?>
            </ul>
        </nav>
    </div>

</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>