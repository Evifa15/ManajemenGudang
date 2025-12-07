<?php
    // Helper untuk cek URL aktif (Sama seperti logic di Admin)
    $currentUrl = $_GET['url'] ?? 'staff/dashboard';
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
                <a href="<?php echo BASE_URL; ?>staff/dashboard">
                    <span>Dashboard</span>
                </a>
            </li>
            
            <?php 
                $isTransaksiActive = in_array($method, ['barangMasuk', 'barangKeluar', 'returBarang']); 
            ?>
            <li class="<?php echo $isTransaksiActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);"> 
                    <span>Menu Transaksi</span>
                    <i class="ph ph-caret-down arrow-icon"></i> 
                </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'barangMasuk') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/barangMasuk">Input Barang Masuk</a>
                    </li>
                    <li class="<?php echo ($method == 'barangKeluar') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/barangKeluar">Input Barang Keluar</a>
                    </li>
                    <li class="<?php echo ($method == 'returBarang') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/returBarang">Lapor Retur/Rusak</a>
                    </li>
                </ul>
            </li>

            <?php 
                $isOperasionalActive = in_array($method, ['manajemenPeminjaman', 'inputOpname']); 
            ?>
            <li class="<?php echo $isOperasionalActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Menu Operasional</span>
                    <i class="ph ph-caret-down arrow-icon"></i> 
                </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'manajemenPeminjaman') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/manajemenPeminjaman">Manajemen Peminjaman</a>
                    </li>
                    <li class="<?php echo ($method == 'inputOpname') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/inputOpname">Input Stock Opname</a>
                    </li>
                </ul>
            </li>

            <?php 
                $isLihatDataActive = in_array($method, ['viewStok', 'viewLokasi', 'riwayatSaya']); 
            ?>
            <li class="<?php echo $isLihatDataActive ? 'active' : ''; ?>">
                <a href="javascript:void(0);">
                    <span>Lihat Data</span>
                    <i class="ph ph-caret-down arrow-icon"></i> 
                </a>
                <ul class="submenu">
                    <li class="<?php echo ($method == 'viewStok') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/viewStok">Cek Stok Barang</a>
                    </li>
                    <li class="<?php echo ($method == 'viewLokasi') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/viewLokasi">Cek Lokasi Barang</a>
                    </li>
                    <li class="<?php echo ($method == 'riwayatSaya') ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>staff/riwayatSaya">Riwayat Input Saya</a>
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

            <a href="<?php echo BASE_URL; ?>auth/logout" class="btn btn-outline-danger" 
               style="border-color: #ffcccc; color: #d63384; background: #fff0f6; border-radius: 8px; padding: 8px 15px; font-weight: 600; height: 38px; display: inline-flex; align-items: center; text-decoration: none;"
               onclick="return confirm('Yakin ingin keluar?');">
                Logout
            </a>

        </div>
    </header>
    
    <div class="app-content">