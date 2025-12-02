<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manajemen Gudang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=<?php echo time(); ?>">
</head>
<body class="bg-auth">
    
    <div class="login-wrapper">
        <div class="login-card-split">
            
            <div class="login-info">
                <div class="info-header">
                    <h3>Pusat Bantuan</h3>
                    <p>Kendala saat masuk?</p>
                </div>
                
                <div class="info-content">
                    <div class="info-item">
                        <span class="icon">💡</span>
                        <div class="text">
                            <strong>Cek Kredensial</strong>
                            <p>Pastikan email dan password Anda benar. Perhatikan huruf besar/kecil (Capslock).</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <span class="icon">🔒</span>
                        <div class="text">
                            <strong>Keamanan Akun</strong>
                            <p>Sistem akan mengunci akun otomatis jika salah password <strong>5 kali</strong> berturut-turut.</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <span class="icon">📞</span>
                        <div class="text">
                            <strong>Hubungi Admin</strong>
                            <p>Jika akun terkunci atau lupa password, segera hubungi Administrator Gudang.</p>
                        </div>
                    </div>
                </div>

                <div class="info-footer">
                    <small>Sistem Manajemen Gudang v1.0</small>
                </div>
            </div>

            <div class="login-form-section">
                <div class="login-header">
                    <div class="brand-logo">📦</div>
                    <h2>Selamat Datang</h2>
                    <p>Masuk ke dashboard operasional</p>
                </div>

                <?php if (isset($_SESSION['login_error'])): ?>          
                    <div class="flash-message <?php echo strpos($_SESSION['login_error']['tipe'], 'danger') !== false ? 'error' : 'warning'; ?> fade-in">
                        <span><?php echo $_SESSION['login_error']['pesan']; ?></span>
                    </div>
                    <?php unset($_SESSION['login_error']); ?>
                <?php endif; ?>       

                <form action="<?php echo BASE_URL; ?>auth/processLogin" method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="nama@gudang.com" required>
                    </div>          
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        LOGIN SEKARANG
                    </button>
                </form>
            </div>

        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>js/main.js"></script>
</body>
</html>