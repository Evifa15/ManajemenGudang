<nav class="app-sidebar">
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
    
    <div class="sidebar-profile-link">
        <a href="<?php echo BASE_URL; ?>profile/index" 
           class="<?php echo (str_starts_with($data['judul'], 'Profil Saya')) ? 'active' : ''; ?>">
            Profil Saya
        </a>
    </div>
</nav>