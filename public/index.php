<?php
// (Asumsi) File ini ada di /public/index.php

// 1. Mulai session di satu tempat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Muat file Konfigurasi Utama
// (Path-nya '../' karena kita ada di dalam folder 'public')
require_once '../app/config/config.php';

// 3. Muat file 'Induk' (Pondasi)
// Ini adalah 'cetakan' untuk semua controller dan model
require_once '../app/core/Controller.php';
require_once '../app/core/Model.php';

// 4. Muat 'Jantung' Router
require_once '../app/core/App.php';


// 5. Jalankan Router!
$app = new App;