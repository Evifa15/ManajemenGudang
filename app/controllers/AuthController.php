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
        
        // Cek Login
        $result = $userModel->checkLogin($email, $password);

        if ($result['status'] === 'SUCCESS') {
            session_regenerate_id(true);
            
            // [RESET] Hapus hitungan gagal jika berhasil login
            unset($_SESSION['failed_login_count']);

            $user = $result['data'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['welcome_popup'] = $user['nama_lengkap'];            
            
            $this->redirectBasedOnRole($user['role']);
            exit;

        } else {
            // [LOGIKA BARU] Hitung kegagalan menggunakan Session
            if (!isset($_SESSION['failed_login_count'])) {
                $_SESSION['failed_login_count'] = 0;
            }
            $_SESSION['failed_login_count']++;

            $pesan = '';
            $tipe = 'alert-danger'; 

            // Cek apakah sudah 5 kali gagal
            if ($_SESSION['failed_login_count'] >= 5) {
                // Tampilkan pesan khusus, tapi TIDAK MENGUNCI akun
                $pesan = '<strong>Gagal login?</strong><br>Silahkan hubungi admin untuk bantuan reset password.';
                $tipe = 'alert-warning'; // Warna kuning/orange
            } else {
                // Tampilkan pesan error biasa
                switch ($result['status']) {
                    case 'EMAIL_NOT_FOUND':
                        $pesan = ' <strong>Email Salah!</strong><br>Email tersebut tidak terdaftar.';
                        break;
                    case 'PASSWORD_WRONG':
                        $pesan = ' <strong>Password Salah!</strong><br>Silakan coba lagi.';
                        break;
                    default:
                        $pesan = 'Terjadi kesalahan sistem.';
                }
            }

            $_SESSION['login_error'] = [
                'pesan' => $pesan,
                'tipe'  => $tipe
            ];
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
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/index');
        exit;
    }
}