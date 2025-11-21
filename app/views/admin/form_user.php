<?php
    // Memuat Header
    require_once APPROOT . '/views/templates/header.php';
?>

<?php
    // Memuat Sidebar
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <a href="<?php echo BASE_URL; ?>admin/users" class="btn" style="background-color: #6c757d; color: white; text-decoration: none;">
                &larr; Kembali
            </a>
        <h1>Tambah Pengguna Baru</h1>
    </div>

    <div class="form-container">
        <form action="<?php echo BASE_URL; ?>admin/processAddUser" method="POST">
            
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" 
                       placeholder="Misal: Budi Santoso" required>
            </div>

            <div class="form-group">
                <label for="email">Email (Untuk Login)</label>
                <input type="email" id="email" name="email" 
                       placeholder="Misal: budi@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Masukkan password awal untuk user" required>
            </div>

            <div class="form-group">
                <label for="role">Role (Hak Akses)</label>
                <select id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff Gudang</option>
                    <option value="pemilik">Pemilik</option>
                    <option value="peminjam">Peminjam</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                <a href="<?php echo BASE_URL; ?>admin/users" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>

</main>
<?php
    // Memuat Footer
    require_once APPROOT . '/views/templates/footer.php';
?>