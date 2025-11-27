<?php
// File: update_db_v2.php
// Script untuk upgrade database ke versi "Delegasi Tugas Stock Opname"

require_once 'app/config/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "<h1>Upgrade Database: Fitur Opname V2</h1>";
    echo "<hr>";

    // --- 1. UPDATE TABEL opname_periods ---
    echo "<h3>1. Memperbarui Tabel 'opname_periods'...</h3>";
    
    $columnsToAdd = [
        "ADD COLUMN nomor_sp VARCHAR(50) NULL AFTER period_id",
        "ADD COLUMN target_selesai DATETIME NULL AFTER start_date",
        "ADD COLUMN catatan_admin TEXT NULL AFTER target_selesai",
        "ADD COLUMN scope_kategori TEXT NULL COMMENT 'ID Kategori dipisah koma atau ALL' AFTER catatan_admin"
    ];

    foreach ($columnsToAdd as $sqlPart) {
        try {
            $pdo->exec("ALTER TABLE opname_periods " . $sqlPart);
            echo "✅ Berhasil menambah kolom baru.<br>";
        } catch (PDOException $e) {
            // Abaikan error jika kolom sudah ada (Duplicate column name)
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "ℹ️ Kolom sudah ada (Skip).<br>";
            } else {
                echo "❌ Error: " . $e->getMessage() . "<br>";
            }
        }
    }

    // --- 2. BUAT TABEL BARU opname_tasks ---
    echo "<br><h3>2. Membuat Tabel Baru 'opname_tasks'...</h3>";

    $sqlCreateTable = "CREATE TABLE IF NOT EXISTS opname_tasks (
        task_id INT(11) PRIMARY KEY AUTO_INCREMENT,
        period_id INT(11) NOT NULL,
        kategori_id INT(11) NOT NULL,
        assigned_to_user_id INT(11) DEFAULT NULL,
        status_task ENUM('Pending', 'In Progress', 'Submitted') DEFAULT 'Pending',
        waktu_mulai DATETIME NULL,
        waktu_selesai DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        -- Foreign Keys
        CONSTRAINT fk_task_period FOREIGN KEY (period_id) REFERENCES opname_periods(period_id) ON DELETE CASCADE,
        CONSTRAINT fk_task_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(kategori_id) ON DELETE CASCADE,
        CONSTRAINT fk_task_user FOREIGN KEY (assigned_to_user_id) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    try {
        $pdo->exec($sqlCreateTable);
        echo "✅ Tabel 'opname_tasks' berhasil dibuat (atau sudah ada).<br>";
    } catch (PDOException $e) {
        echo "❌ Gagal membuat tabel: " . $e->getMessage() . "<br>";
    }

    echo "<hr>";
    echo "<h3>🎉 Upgrade Database Selesai!</h3>";
    echo "Silakan hapus file <code>update_db_v2.php</code> ini dan lanjut ke tahap coding Model.<br>";
    echo "<br><a href='".BASE_URL."admin/stockOpname'>Kembali ke Admin Panel</a>";

} catch (Exception $e) {
    die("Terjadi Kesalahan Fatal: " . $e->getMessage());
}
?>