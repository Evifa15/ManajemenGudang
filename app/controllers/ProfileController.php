<?php

class ProfileController extends Controller {

    public function __construct() {
        // GATEKEEPER: Cukup pastikan user sudah login.
        // Tidak peduli role-nya apa, semua bisa akses profil.
        if (!isset($_SESSION['is_logged_in'])) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }
        
        // Cek jika status 'baru', paksa ganti password dulu
        if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == 'baru') {
             header('Location: ' . BASE_URL . 'auth/forceChangePassword');
            exit;
        }
    }

    /**
     * Menampilkan halaman profil (3 Tab)
     */
    public function index() {
        $userModel = $this->model('User_model');
        // Ambil data gabungan (users + user_profiles)
        $profileData = $userModel->getJoinedUserProfile($_SESSION['user_id']);

        $data = [
            'judul' => 'Profil Saya',
            'user' => $profileData
        ];
        
        $this->view('profile/index', $data);
    }

    /**
     * Memproses update Info Pribadi & Kontak
     */
    public function processProfileInfo() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'profile/index');
            exit;
        }

        // Kumpulkan data (termasuk foto, tapi logic upload foto belum dibuat)
        $data = [
            'user_id' => $_SESSION['user_id'],
            'nama_lengkap' => $_POST['nama_lengkap'],
            'foto_profil' => null, // (Kita akan tambahkan logic upload foto nanti)
            'tempat_lahir' => $_POST['tempat_lahir'],
            'tanggal_lahir' => $_POST['tanggal_lahir'],
            'agama' => $_POST['agama'],
            'telepon' => $_POST['telepon'],
            'alamat' => $_POST['alamat'],
            'kota' => $_POST['kota'],
            'provinsi' => $_POST['provinsi'],
            'kode_pos' => $_POST['kode_pos']
        ];

        $userModel = $this->model('User_model');
        
        if ($userModel->updateProfile($data)) {
            // Update nama di session
            $_SESSION['nama_lengkap'] = $data['nama_lengkap']; 
            $_SESSION['flash_message'] = ['text' => 'Profil berhasil di-update.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal mengupdate profil.', 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'profile/index');
        exit;
    }

    /**
     * Memproses ganti password mandiri
     */
    public function processChangePassword() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'profile/index');
            exit;
        }

        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Validasi
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_message'] = ['text' => 'Password baru tidak cocok!', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'profile/index');
            exit;
        }
        if (strlen($newPassword) < 6) {
            $_SESSION['flash_message'] = ['text' => 'Password baru minimal harus 6 karakter.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'profile/index');
            exit;
        }

        $userModel = $this->model('User_model');
        $result = $userModel->changePassword($_SESSION['user_id'], $oldPassword, $newPassword);

        if ($result['success']) {
            $_SESSION['flash_message'] = ['text' => $result['message'], 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => $result['message'], 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'profile/index');
        exit;
    }
}