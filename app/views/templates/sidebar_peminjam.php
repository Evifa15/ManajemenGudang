<?php
    // Helper untuk cek URL aktif
    $currentUrl = $_GET['url'] ?? 'peminjam/dashboard';
    $urlParts = explode('/', $currentUrl);
    $method = $urlParts[1] ?? 'dashboard'; 
?>

<nav class="app-sidebar">
    <div class="sidebar-header">
        <div class="brand-logo">📦 Gudang</div>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo ($method == 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>peminjam/dashboard">
                    <span>Dashboard</span>
                </a>
            </li>
            
            <?php 
                $isLayananActive = in_array($method, ['katalog', 'riwayatSaya', 'formPeminjaman']); 
            ?>
            <li class="<?php echo $isLayananActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Layanan Peminjaman</span>
                    <i class="ph ph-caret-down arrow-icon"></i> 
                </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'katalog' || $method == 'formPeminjaman') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>peminjam/katalog">Katalog & Ajukan</a>
                    </li>
                    <li class="<?php echo ($method == 'riwayatSaya') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>peminjam/riwayatSaya">Riwayat Saya</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-profile-link">
        <a href="<?php echo BASE_URL; ?>profile/index">Profil Saya</a>
    </div>
</nav>

<div class="main-content">
    
    <header class="app-header">
        <div class="mobile-menu-toggle">
            ☰
        </div>
        
        <div class="page-title">
            <?php echo $data['judul']; ?> 
        </div>
        
        <div class="app-header-actions" style="display: flex; align-items: center; gap: 10px;">
            
            <?php if (isset($data['back_button'])): ?>
                <a href="<?php echo $data['back_button']['url']; ?>" 
                   class="btn btn-sm" 
                   style="background-color: #152e4d; color: white; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; font-weight: 600; border-radius: 8px; padding: 8px 15px; height: 38px; border: 1px solid #152e4d; transition: all 0.2s;">
                    <i class="ph ph-arrow-left" style="font-weight: bold;"></i> 
                    <?php echo $data['back_button']['label']; ?>
                </a>
            <?php endif; ?>

            <span style="font-size: 0.9rem; color: #64748b; margin-right: 5px;" class="no-mobile">
                Halo, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong>
            </span>

            <a href="<?php echo BASE_URL; ?>auth/logout" class="btn btn-outline-danger" 
               style="border-color: #ffcccc; color: #d63384; background: #fff0f6; border-radius: 8px; padding: 8px 15px; font-weight: 600; height: 38px; display: inline-flex; align-items: center; text-decoration: none;"
               onclick="return confirm('Yakin ingin keluar?');">
                Logout
            </a>
        </div>
    </header>
    
    <div class="app-content">