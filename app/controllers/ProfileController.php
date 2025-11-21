<?php

class ProfileController extends Controller {

    public function __construct() {
        // GATEKEEPER: Cukup pastikan user sudah login.
        // Tidak peduli role-nya apa, semua bisa akses profil.
        if (!isset($_SESSION['is_logged_in'])) {
            header('Location: ' . BASE_URL . 'auth/index');
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

    /**
     * Menampilkan Riwayat Absensi User (Support AJAX Realtime)
     */
    public function absensi($page = 1) {
        // 1. Setup Filter
        $filters = [
            'user_id' => $_SESSION['user_id'], 
            'month'   => $_GET['month'] ?? date('m'),
            'year'    => $_GET['year'] ?? date('Y')
        ];

        // 2. Setup Paginasi
        $limit = 10;
        $page = (int)$page;
        if ($page < 1) $page = 1;

        // 3. Panggil Model
        $absensiModel = $this->model('Absensi_model');
        
        $totalData = $absensiModel->getTotalAbsensiCount($filters);
        $totalPages = ceil($totalData / $limit);
        $offset = ($page - 1) * $limit;
        $history = $absensiModel->getAbsensiPaginated($limit, $offset, $filters);

        // --- 4. LOGIKA AJAX (BARU) ---
        if (isset($_GET['ajax'])) {
            $formattedData = [];
            foreach ($history as $absen) {
                $totalJam = '-';
                $displayStatus = $absen['status'];
                
                // Kalkulasi Jam Kerja
                if ($absen['status'] == 'Hadir') {
                    if ($absen['waktu_masuk']) {
                        if ($absen['waktu_pulang']) {
                            $checkin = new DateTime($absen['waktu_masuk']);
                            $checkout = new DateTime($absen['waktu_pulang']);
                            $interval = $checkin->diff($checkout);
                            $totalJam = $interval->format('%h jam %i mnt');
                        } else {
                            $displayStatus = 'Masih Bekerja';
                        }
                    }
                }

                $formattedData[] = [
                    'tanggal'       => date('d-m-Y', strtotime($absen['tanggal'])),
                    'waktu_masuk'   => $absen['waktu_masuk'] ? date('H:i', strtotime($absen['waktu_masuk'])) : '-',
                    'waktu_pulang'  => $absen['waktu_pulang'] ? date('H:i', strtotime($absen['waktu_pulang'])) : '-',
                    'total_jam'     => $totalJam,
                    'status_raw'    => $absen['status'], // Status asli untuk logic warna di JS
                    'display_status'=> $displayStatus,   // Status teks untuk ditampilkan
                    'keterangan'    => $absen['keterangan'] ?? '-',
                    'bukti_foto'    => $absen['bukti_foto']
                ];
            }

            header('Content-Type: application/json');
            echo json_encode([
                'absensi' => $formattedData,
                'totalPages' => $totalPages,
                'currentPage' => $page
            ]);
            exit; // Stop di sini jika AJAX
        }
        // -----------------------------

        // 5. Siapkan Data (Jika bukan AJAX)
        $data = [
            'judul' => 'Riwayat Absensi Saya',
            'absensi' => $history,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'filters' => $filters
        ];
        
        $this->view('profile/history_absensi', $data);
    }
}