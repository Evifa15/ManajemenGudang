<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Backup & Restore Database</h1>
    </div>

    <?php
        // Blok Notifikasi
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="form-container" style="border-left: 5px solid #007bff;">
        <form action="<?php echo BASE_URL; ?>admin/processBackup" method="POST">
            <fieldset>
                <legend>Backup Database (Aman)</legend>
                <p>Klik tombol di bawah ini untuk mengunduh (download) salinan lengkap database (`.sql`) ke komputer Anda. Lakukan ini secara rutin (misal: mingguan).</p>
                <div class="form-actions" style="border-top: none; padding-top: 0;">
                    <button type="submit" class="btn btn-primary" style="font-size: 1.1em; padding: 12px 20px;">
                        BACKUP DATABASE SEKARANG
                    </button>
                </div>
            </fieldset>
        </form>
    </div>

    <div class="form-container" style="border-left: 5px solid #dc3545; margin-top: 30px;">
        <form action="<?php echo BASE_URL; ?>admin/processRestore" method="POST" enctype="multipart/form-data" onsubmit="return confirm('PERINGATAN TERAKHIR!\nIni akan menghapus semua data saat ini. Lanjutkan?');">
            <fieldset>
                <legend style="color: #dc3545;">Restore Database (Sangat Berbahaya)</legend>
                <p style="color: #721c24;">
                    PERHATIAN: Tindakan ini akan **MENGHAPUS SEMUA DATA SAAT INI** dan menggantinya dengan data dari file backup yang Anda unggah. Data yang terhapus tidak bisa dikembalikan.
                </p>
                
                <div class="form-group">
                    <label for="sql_file">Pilih File Backup (.sql) untuk di-Restore</label>
                    <input type="file" id="sql_file" name="sql_file" accept=".sql" required>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_restore">Ketik "RESTORE" untuk Konfirmasi (Wajib)</label>
                    <input type="text" id="konfirmasi_restore" name="konfirmasi_restore" 
                           placeholder="Ketik 'RESTORE' di sini" required>
                </div>

                <div class="form-actions" style="border-top: none; padding-top: 0;">
                    <button type="submit" class="btn btn-danger" style="font-size: 1.1em; padding: 12px 20px;">
                        RESTORE DATABASE
                    </button>
                </div>
            </fieldset>
        </form>
    </div>
</main>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>