<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    
    <div class="dashboard-grid-custom">
        
        <div class="card welcome-card">
            <div class="welcome-content">
                <div class="profile-section">
                    <div class="profile-placeholder">
                        <span>FOTO</span>
                    </div>
                    <div class="profile-text">
                        <span class="greeting">Selamat Datang,</span>
                        <h2 class="user-name">Admin (Utama)</h2>
                        <span class="user-role-badge">Administrator</span>
                    </div>
                </div>

                <div class="attendance-section">
                    <div class="attendance-status check-in">
                        <span class="icon">✅</span> Masuk: 13:43 WIB
                    </div>
                    <button class="btn-checkout">
                        <span class="icon"></span> CHECK-OUT (PULANG)
                    </button>
                    <p class="note">Jangan lupa checkout sebelum pulang.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">
                <span></span> Akses Cepat
            </h3>
            
            <div class="shortcut-list">
                <a href="<?php echo BASE_URL; ?>admin/addBarang" class="btn-shortcut">
                    <span class="icon">📦</span> Tambah Barang Baru
                </a>
                <a href="<?php echo BASE_URL; ?>admin/addUser" class="btn-shortcut">
                    <span class="icon">👤</span> Tambah User Baru
                </a>
                <a href="<?php echo BASE_URL; ?>admin/perintahOpname" class="btn-shortcut">
                    <span class="icon">📝</span> Mulai Stock Opname
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manageBackup" class="btn-shortcut">
                    <span class="icon">💾</span> Backup Database
                </a>
            </div>
        </div>

    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>