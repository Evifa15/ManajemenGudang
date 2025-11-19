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

        <form action="<?php echo BASE_URL; ?>auth/processLogin" method="POST">
            
            <?php 
                if (isset($_GET['error'])) {
                    echo '<p class="error-message">Email atau password salah!</p>';
                }
            ?>

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
</body>
</html>