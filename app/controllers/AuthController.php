<?php

class AuthController extends Controller {

    public function index() {
        $this->view('auth/login'); 
    }

    public function processLogin() {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/index'); // PERBAIKAN URL
            exit;
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        $userModel = $this->model('User_model'); 
        $user = $userModel->checkLogin($email, $password);

        if ($user) {
            // (Kita TIDAK perlu session_start() di sini,
            // karena index.php sudah menjalankannya)

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_logged_in'] = true;
            
            // Simpan status_login di session untuk gatekeeper
            $_SESSION['status_login'] = $user['status_login']; 

            if ($user['status_login'] == 'baru') {
                // Arahkan ke halaman ganti password paksa (URL CANTIK)
                header('Location: ' . BASE_URL . 'auth/forceChangePassword'); // PERBAIKAN URL
            } else {
                $this->redirectBasedOnRole($user['role']);
            }
            exit;

        } else {
            // Kembalikan ke halaman login (URL CANTIK)
            header('Location: ' . BASE_URL . 'auth/index?error=1'); // PERBAIKAN URL
            exit;
        }
    }

    private function redirectBasedOnRole($role) {
        $url = '';
        switch ($role) {
            case 'admin':
                $url = 'admin/dashboard';
                break;
            case 'staff':
                $url = 'staff/dashboard';
                break;
            case 'pemilik':
                $url = 'pemilik/dashboard';
                break;
            case 'peminjam':
                $url = 'peminjam/dashboard';
                break;
            default:
                $url = 'auth/index';
        }
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    /**
     * Menampilkan halaman ganti password paksa
     */
    public function forceChangePassword() {
        // PERBAIKAN: Hapus session_start()
        
        // Pastikan hanya user 'baru' yang bisa akses
        if (!isset($_SESSION['is_logged_in']) || $_SESSION['status_login'] != 'baru') {
            // PERBAIKAN URL:
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }
        
        $this->view('auth/ganti_password_paksa');
    }

    /**
     * ⬇️ --- TAMBAHKAN METHOD BARU INI --- ⬇️
     * Memproses form ganti password paksa
     */
    public function processForceChangePassword() {
        // 1. Gatekeeper: Pastikan sudah login dan status 'baru'
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['is_logged_in']) || $_SESSION['status_login'] != 'baru') {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }

        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // 2. Validasi: Password harus cocok
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_message'] = ['text' => 'Password baru tidak cocok!', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'auth/forceChangePassword');
            exit;
        }

        // 3. Validasi: Password tidak boleh kosong (atau terlalu pendek)
        if (strlen($newPassword) < 6) {
            $_SESSION['flash_message'] = ['text' => 'Password baru minimal harus 6 karakter.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'auth/forceChangePassword');
            exit;
        }

        // 4. Update Database
        $userModel = $this->model('User_model');
        $userId = $_SESSION['user_id'];
        
        if ($userModel->updatePasswordAndActivate($userId, $newPassword)) {
            // 5. Update session dan arahkan ke dashboard
            $_SESSION['status_login'] = 'aktif';
            $_SESSION['flash_message'] = ['text' => 'Password berhasil diperbarui. Selamat datang!', 'type' => 'success'];
            $this->redirectBasedOnRole($_SESSION['role']);
        } else {
            // Gagal update
            $_SESSION['flash_message'] = ['text' => 'Gagal mengupdate password. Coba lagi.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'auth/forceChangePassword');
            exit;
        }
    }


    /**
     * Logika untuk Logout
     */
    public function logout() {
        // PERBAIKAN: Hapus session_start()
        
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/index'); // PERBAIKAN URL
        exit;
    }
}