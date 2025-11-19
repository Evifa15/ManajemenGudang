<nav class="app-sidebar">
    <ul>
        <!-- Link Dashboard -->
        <li class="<?php echo ($data['judul'] == 'Dashboard Staff') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>staff/dashboard">Dashboard</a>
        </li>
        
        <!-- Menu Transaksi Utama (Sesuai Rancangan) -->
        <li><a href="#">Menu Transaksi</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Form Barang Masuk')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>staff/barangMasuk">Form Barang Masuk</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Form Barang Keluar')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>staff/barangKeluar">Form Barang Keluar</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Form Retur/Rusak')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>staff/returBarang">Form Retur/Rusak</a>
                </li>
            </ul>
        </li>

        <!-- Menu Operasional (Sesuai Rancangan) -->
        <li><a href="#">Menu Operasional</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Manajemen Peminjaman')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>staff/manajemenPeminjaman">Manajemen Peminjaman</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Input Stock Opname')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>staff/inputOpname">Input Stock Opname</a>
                </li>
            </ul>
        </li>

        <!-- Menu Lihat Data (Sesuai Rancangan) -->
        <li><a href="#">Lihat Data (Read-Only)</a>
            <ul class="submenu">
                <li class="<?php echo ($data['judul'] == 'Cek Stok Barang') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>staff/viewStok">Cek Stok Barang</a>
                </li>
                <li class="<?php echo ($data['judul'] == 'Cek Lokasi Barang') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>staff/viewLokasi">Cek Lokasi Barang</a>
                </li>
                <li class="<?php echo ($data['judul'] == 'Riwayat Input Saya') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>staff/riwayatSaya">Riwayat Input Saya</a>
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