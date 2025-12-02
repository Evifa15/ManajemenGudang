<?php
require_once 'app/config/config.php';
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Ubah tipe data kolom bukti_foto menjadi TEXT agar muat banyak
    $pdo->exec("ALTER TABLE stock_transactions MODIFY COLUMN bukti_foto TEXT");

    echo "<h1>✅ Sukses! Database siap menampung banyak foto.</h1>";
    echo "Silakan hapus file ini.";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>