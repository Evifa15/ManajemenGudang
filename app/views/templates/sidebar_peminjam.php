<nav class="app-sidebar">
    <div class="sidebar-header">
        <div class="brand-logo">📦 Gudang</div>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo ($data['judul'] == 'Dashboard Peminjam') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>peminjam/dashboard">Dashboard</a>
            </li>
            
            <li><a href="#">Layanan Peminjaman</a>
                <ul class="submenu">
                    <li class="<?php echo (str_starts_with($data['judul'], 'Katalog Peminjaman')) ? 'active' : ''; ?>">
                        <a href="<?php echo BASE_URL; ?>peminjam/katalog">Katalog & Ajukan</a>
                    </li>
                    <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Peminjaman Saya')) ? 'active' : ''; ?>">
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
        <div class="page-title">
            <?php echo $data['judul']; ?> 
        </div>
        <div class="user-profile">
            <span>Halo, <strong><?php echo $_SESSION['nama_lengkap']; ?></strong> (Peminjam)</span>
            <a href="<?php echo BASE_URL; ?>auth/logout" class="btn-logout" onclick="return confirm('Yakin ingin keluar?');">Logout</a>
        </div>
    </header>