<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Pergudangan</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css"> 
</head>
<body>
    <div class="login-container">
        <h2>Manajemen Gudang</h2>
        <p>Silakan login untuk melanjutkan</p>
        <?php if (isset($_SESSION['login_error'])): ?>          
            <div id="loginAlert" class="alert <?php echo $_SESSION['login_error']['tipe']; ?>">
                <?php echo $_SESSION['login_error']['pesan']; ?>
            </div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>       
        <form action="<?php echo BASE_URL; ?>auth/processLogin" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="email@anda.com">
            </div>          
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Password">
            </div>
            <div class="form-group">
                <button type="submit" class="btn-login">LOGIN</button>
            </div>
        </form>
    </div>
    <script src="<?php echo BASE_URL; ?>js/main.js"></script>
</body>
</html>