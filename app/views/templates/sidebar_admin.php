<nav class="app-sidebar">
    <ul>
        <li class="<?php echo ($data['judul'] == 'Dashboard Admin') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>admin/dashboard">Dashboard</a>
        </li>
        
        <li class="<?php echo (
                str_starts_with($data['judul'], 'Manajemen Barang') || 
                str_starts_with($data['judul'], 'Tambah Barang') || 
                str_starts_with($data['judul'], 'Edit Barang') ||
                str_starts_with($data['judul'], 'Konfigurasi Data') // Supaya tetap aktif saat di halaman Tabulasi
            ) ? 'active' : ''; ?>">
            
            <a href="<?php echo BASE_URL; ?>admin/barang">Master Data Barang</a>
        </li>

        <li><a href="#">Menu Transaksi</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Barang Masuk')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk">Riwayat Barang Masuk</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Barang Keluar')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatBarangKeluar">Riwayat Barang Keluar</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Retur/Rusak')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatReturRusak">Riwayat Retur/Rusak</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Peminjaman')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatPeminjaman">Riwayat Peminjaman</a>
                </li>
            </ul>
        </li>

        <li><a href="#">Menu Operasi Kritis</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Perintah Opname')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/perintahOpname">Perintah Opname (Aktif)</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Riwayat Opname')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatOpname">Riwayat / Arsip Opname</a>
                </li>
            </ul>
        </li>

        <li><a href="#">Administrasi Sistem</a>
            <ul class="submenu">
                <li class="<?php echo ($data['judul'] == 'Manajemen Pengguna') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/users">Manajemen Pengguna</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Backup & Restore')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/manageBackup">Backup & Restore</a>
                </li>
            </ul>
        </li>

        <li><a href="#">Laporan & Pengawasan</a>
            <ul class="submenu">
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Stok Akhir')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/laporanStok">Laporan Stok Akhir</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Transaksi')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/laporanTransaksi">Laporan Transaksi</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Laporan Peminjaman')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/laporanPeminjaman">Laporan Peminjaman</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Rekap Absensi')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/rekapAbsensi">Rekap Absensi</a>
                </li>
                <li class="<?php echo (str_starts_with($data['judul'], 'Audit Trail')) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>admin/auditTrail">Audit Trail</a>
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