<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    // Data user yang akan diedit (dikirim dari controller)
    $user = $data['user']; 
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Edit Pengguna: <?php echo $user['nama_lengkap']; ?></h1>
    </div>

    <div class="form-container">
        <!-- PERBAIKAN: Menghapus index.php?url= dari action -->
        <form action="<?php echo BASE_URL; ?>admin/processUpdateUser" method="POST">
            
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo $user['nama_lengkap']; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email (Untuk Login)</label>
                <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ganti password">
            </div>

            <div class="form-group">
                <label for="role">Role (Hak Akses)</label>
                <select id="role" name="role" required>
                    <option value="admin" <?php if($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                    <option value="staff" <?php if($user['role'] == 'staff') echo 'selected'; ?>>Staff Gudang</option>
                    <option value="pemilik" <?php if($user['role'] == 'pemilik') echo 'selected'; ?>>Pemilik</option>
                    <option value="peminjam" <?php if($user['role'] == 'peminjam') echo 'selected'; ?>>Peminjam</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status_login">Status Login</label>
                <select id="status_login" name="status_login" required>
                    <option value="aktif" <?php if($user['status_login'] == 'aktif') echo 'selected'; ?>>Aktif</option>
                    <option value="baru" <?php if($user['status_login'] == 'baru') echo 'selected'; ?>>Baru (Perlu Ganti Password)</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Pengguna</button>
                <!-- PERBAIKAN: Menghapus index.php?url= -->
                <a href="<?php echo BASE_URL; ?>admin/users" class="btn btn-danger">Batal</a>
            </div>

        </form>
    </div>

</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>