<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manajemen Gudang</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=<?php echo time(); ?>">
    
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-auth">
    
    <div class="login-wrapper">
        <div class="login-card-split">
            
            <div class="login-info">
                <div class="info-header">
                    <h3 style="color:white; display:flex; align-items:center; gap:10px;">
                        <i class="ph ph-warehouse" style="color: var(--primer-yellow);"></i> WMS Gudang
                    </h3>
                    <p style="opacity: 0.8; font-size: 0.9rem; margin-top: 5px;">(Warehouse Management System)</p>
                </div>
                
                <div class="info-content">
                    <div class="info-item">
                        <i class="ph ph-shield-check"></i>
                        <div class="text">
                            <strong>Akses Aman</strong>
                            <p>Hanya pengguna yang terdaftar yang dapat mengakses sistem ini.</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="ph ph-headset"></i>
                        <div class="text">
                            <strong>Butuh Bantuan?</strong>
                            <p>Hubungi Administrator jika Anda lupa password.</p>
                        </div>
                    </div>
                </div>

                <div class="info-footer" style="text-align: center; opacity: 0.5; font-size: 0.8rem;">
                    &copy; <?php echo date('Y'); ?> Manajemen Gudang v1.0
                </div>
            </div>

            <div class="login-form-section">
                <div class="login-header">
                    <h2>Selamat Datang</h2>
                    <p>Silakan masuk untuk memulai sesi kerja Anda.</p>
                </div>

                <?php if (isset($_SESSION['login_error'])): ?>          
                    <div class="flash-message <?php echo strpos($_SESSION['login_error']['tipe'], 'danger') !== false ? 'error' : 'warning'; ?>" 
                         style="margin-bottom: 20px; text-align: left; display: flex; align-items: center; gap: 10px;">
                        <i class="ph ph-warning-circle" style="font-size: 1.5rem;"></i>
                        <span><?php echo $_SESSION['login_error']['pesan']; ?></span>
                    </div>
                    <?php unset($_SESSION['login_error']); ?>
                <?php endif; ?>       

                <form action="<?php echo BASE_URL; ?>auth/processLogin" method="POST">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div style="position: relative;">
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="nama@perusahaan.com" 
                                   style="padding-left: 45px;" required autofocus>
                            <i class="ph ph-envelope-simple" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.2rem;"></i>
                        </div>
                    </div>          
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="••••••••" 
                                   style="padding-left: 45px;" required>
                            <i class="ph ph-lock-key" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.2rem;"></i>
                            
                            <button type="button" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b;">
                                <i class="ph ph-eye-slash" style="font-size: 1.2rem;"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-brand-dark btn-block">
                        <i class="ph ph-sign-in"></i> MASUK SEKARANG
                    </button>

                </form>
            </div>

        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>js/modules/auth.js"></script>
</body>
</html>