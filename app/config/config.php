<?php
date_default_timezone_set('Asia/Jakarta');
define('APPROOT', dirname(dirname(__FILE__))); // -> .../ManajemenGudang/app

// 2. BASE_URL (URL Root)
// Ini adalah URL ke folder 'public' Anda
// PENTING: Sesuaikan dengan setup XAMPP Anda!
define('BASE_URL', 'http://localhost/ManajemenGudang/public/');

// 3. Info Database (Sesuai Rancangan Anda)
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti jika beda
define('DB_PASS', '');     // Ganti jika beda
define('DB_NAME', 'manajemengudang');