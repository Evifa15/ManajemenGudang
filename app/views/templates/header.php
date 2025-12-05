<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Gudang</title>   
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=<?php echo time(); ?>">  
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* Pastikan icon memiliki dimensi */
        i.ph {
            display: inline-block !important;
            vertical-align: middle !important;
            line-height: 1 !important;
            font-weight: normal !important;
            font-style: normal !important;
        }
        /* Perbaikan khusus untuk tombol bulat */
        .btn-icon i.ph {
            font-size: 1.3rem !important;
        }
    </style>
</head>
<body>
   
<div class="app-container">