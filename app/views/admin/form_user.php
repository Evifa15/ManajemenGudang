<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <!-- 2. FORM CARD (Centered & Clean) -->
    <div class="card" style="max-width: 900px; margin: 0 auto; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        
        <form action="<?php echo BASE_URL; ?>admin/processAddUser" method="POST">
            
            <!-- Baris 1: Nama & Email -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 20px;">
                
                <div class="form-group">
                    <label for="nama_lengkap" style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" 
                           class="form-control"
                           style="padding: 12px; border-radius: 8px;"
                           placeholder="Contoh: Budi Santoso" required>
                </div>

                <div class="form-group">
                    <label for="email" style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;">Email (Login)</label>
                    <input type="email" id="email" name="email" 
                           class="form-control"
                           style="padding: 12px; border-radius: 8px;"
                           placeholder="Contoh: budi@gudang.com" required>
                </div>

            </div>

            <!-- Baris 2: Tanggal Lahir (Pengganti Password) & Role -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
                
                <!-- INPUT TANGGAL LAHIR -->
                <div class="form-group">
                    <label for="tanggal_lahir" style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                           class="form-control"
                           style="padding: 12px; border-radius: 8px;"
                           required>
                    <small style="color: #64748b; font-size: 0.85rem; margin-top: 5px; display: block;">
                        <i class="ph ph-info"></i> Password awal akan dibuat dari tanggal lahir (Format: DDMMYYYY).
                    </small>
                </div>

                <!-- INPUT ROLE -->
                <div class="form-group">
                    <label for="role" style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;">Role (Hak Akses)</label>
                    <div style="position: relative;">
                        <select id="role" name="role" required class="form-control" style="padding: 12px; border-radius: 8px; appearance: none; cursor: pointer;">
                            <option value="" disabled selected>-- Pilih Role --</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff Gudang</option>
                            <option value="pemilik">Pemilik</option>
                            <option value="peminjam">Peminjam</option>
                        </select>
                        <!-- Custom Arrow Icon -->
                        <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                    </div>
                </div>

            </div>

            <!-- Divider -->
            <div style="border-top: 1px solid #f1f5f9; margin-bottom: 25px;"></div>

            <!-- Actions -->
            <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 15px;">
                <!-- Tombol Batal (KUNING) -->
                <a href="<?php echo BASE_URL; ?>admin/users" class="btn" style="background-color: #f8c21a; color: #152e4d; padding: 12px 25px; border-radius: 8px; font-weight: 600; border: 1px solid #f8c21a;">
                    Batal
                </a>
                
                <!-- Tombol Simpan (BIRU TUA) -->
                <button type="submit" class="btn btn-brand-dark" style="padding: 12px 30px; border-radius: 8px; font-size: 1rem;">
                    <i class="ph ph-floppy-disk" style="font-size: 1.2rem;"></i> Simpan Pengguna
                </button>
            </div>

        </form>
    </div>

</main>
<?php
    require_once APPROOT . '/views/templates/footer.php';
?>