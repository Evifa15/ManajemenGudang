<?php
    // 1. Panggil Header
    require_once APPROOT . '/views/templates/header.php';
    // 2. Panggil Sidebar KHUSUS ADMIN
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Audit Trail / Log Aktivitas Sistem</h1>
    </div>

    <div class.search-container">
        <form action="<?php echo BASE_URL; ?>admin/auditTrail" method="GET">
            
            <select name="user_id" class="filter-select">
                <option value="">Semua Pengguna</option>
                <?php foreach($data['allUsers'] as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>" <?php if($data['filters']['user_id'] == $user['user_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($user['nama_lengkap']); ?> (<?php echo $user['role']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="start_date" class="filter-select" value="<?php echo htmlspecialchars($data['filters']['start_date'] ?? ''); ?>">
            <input type="date" name="end_date" class="filter-select" value="<?php echo htmlspecialchars($data['filters']['end_date'] ?? ''); ?>">
            
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <th>Waktu (Timestamp)</th>
                    <th>Pengguna</th>
                    <th>Role</th>
                    <th>Aksi yang Dilakukan</th>
                    <th>Modul</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['logs'] as $log) : ?>
                <tr>
                    <td><?php echo date('d-m-Y H:i:s', strtotime($log['waktu'])); ?></td>
                    <td><?php echo htmlspecialchars($log['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($log['role']); ?></td>
                    <td><?php echo htmlspecialchars($log['aksi']); ?></td>
                    <td><?php echo htmlspecialchars($log['modul']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>