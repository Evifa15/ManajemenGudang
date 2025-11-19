<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Manajemen Gudang</title>   
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">  
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    
<div class="app-container">
    
    <header class="app-header">
        <div class="logo">
            Manajemen Gudang
        </div>
        <div class="user-profile">
            <span>Halo, <?php echo $_SESSION['nama_lengkap']; ?>!</span>
            
            <!-- PERBAIKAN: Menghapus index.php?url= -->
            <a href="<?php echo BASE_URL; ?>auth/logout" class="btn-logout">Logout</a>
        </div>
    </header>