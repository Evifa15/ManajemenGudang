<?php
// 1. Muat file konfigurasi untuk mengambil info DB
require_once 'app/config/config.php';

echo "Mencoba membuat koneksi ke database: " . DB_NAME . "...<br>";

// 2. Buat koneksi Database (PDO)
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    // INI BAGIAN PENTING: $pdo dibuat di sini
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); 
    
    echo "Koneksi database berhasil!<br>";

} catch (PDOException $e) {
    // Hentikan jika koneksi gagal
    die('Koneksi Database Gagal: ' . $e->getMessage());
}


// --- Kode Pembuatan User (yang sudah ada) ---
$email = 'admin@gudang.com';
$password_mentah = 'admin123';
$nama_lengkap = 'Admin Utama';
$role = 'admin';
$status_login = 'aktif';

// PENTING!
$password_hash = password_hash($password_mentah, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (email, password, nama_lengkap, role, status_login) 
        VALUES (:email, :password, :nama, :role, :status)";

// 3. Eksekusi query
try {
    // Baris 18 Anda (atau sekitar baris 40-an di kode ini)
    // sekarang bisa menggunakan $pdo karena sudah dibuat di atas
    $stmt = $pdo->prepare($sql); 
    $stmt->execute([
        'email' => $email,
        'password' => $password_hash,
        'nama' => $nama_lengkap,
        'role' => $role,
        'status' => $status_login
    ]);

    echo "=====================================<br>";
    echo "BERHASIL: User Admin berhasil dibuat!<br>";
    echo "Email: " . $email . "<br>";
    echo "Password: " . $password_mentah . "<br>";
    echo "=====================================<br>";
    echo "<b>PENTING: Harap HAPUS file 'buat_admin.php' ini sekarang!</b>";

} catch (PDOException $e) {
    // Tangani jika user sudah ada (email duplikat)
    if ($e->errorInfo[1] == 1062) {
        echo "=====================================<br>";
        echo "ERROR: User dengan email '" . $email . "' sudah ada.<br>";
        echo "Anda tidak perlu menjalankan skrip ini lagi.<br>";
        echo "=====================================<br>";
    } else {
        // Error lain
        echo "Gagal membuat user: " . $e->getMessage();
    }
}
?>