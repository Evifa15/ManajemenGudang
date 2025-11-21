<?php

class AuthController extends Controller {

    public function index() {
        $this->view('auth/login'); 
    }

    public function processLogin() {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        $userModel = $this->model('User_model'); 
        $result = $userModel->checkLogin($email, $password);

        if ($result['status'] === 'SUCCESS') {
            // ... (Logika Login Sukses TETAP SAMA seperti sebelumnya) ...
            session_regenerate_id(true);
            $user = $result['data'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['welcome_popup'] = $user['nama_lengkap']; 
            
            $this->redirectBasedOnRole($user['role']);
            exit;

        } else {
            // --- REVISI BAGIAN ERROR ---
            
            $pesan = '';
            $tipe = 'alert-danger'; // Merah (Default)

            switch ($result['status']) {
                case 'EMAIL_NOT_FOUND':
                    $pesan = '❌ <strong>Email Salah!</strong><br>Email tersebut tidak terdaftar.';
                    break;
                case 'PASSWORD_WRONG':
                    $pesan = '🔑 <strong>Password Salah!</strong><br>Silakan coba lagi.';
                    break;
                case 'ACCOUNT_LOCKED':
                case 'LOCKED_NOW':
                    $pesan = '⚠️ <strong>Akun Terkunci!</strong><br>Gagal login 5x. Hubungi Admin.';
                    $tipe = 'alert-warning'; // Kuning
                    break;
                default:
                    $pesan = 'Terjadi kesalahan sistem.';
            }

            // SIMPAN KE SESSION (Flash Data)
            $_SESSION['login_error'] = [
                'pesan' => $pesan,
                'tipe'  => $tipe
            ];

            // Redirect BERSIH (Tanpa ?error=...)
            header('Location: ' . BASE_URL . 'auth/index'); 
            exit;
        }
    }

    private function redirectBasedOnRole($role) {
        $url = '';
        switch ($role) {
            case 'admin': $url = 'admin/dashboard'; break;
            case 'staff': $url = 'staff/dashboard'; break;
            case 'pemilik': $url = 'pemilik/dashboard'; break;
            case 'peminjam': $url = 'peminjam/dashboard'; break;
            default: $url = 'auth/index';
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
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/index');
        exit;
    }
}