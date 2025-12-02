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

    public function processProfileInfo() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'profile/index');
            exit;
        }

        // 1. Ambil Data Profil Lama
        $userModel = $this->model('User_model');
        $currentProfile = $userModel->getJoinedUserProfile($_SESSION['user_id']);
        
        $fotoNama = $currentProfile['foto_profil'] ?? null; 

        // 2. Logika Upload Foto Baru
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['foto_profil'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExt, $allowed)) {
                if ($file['size'] <= 2000000) { 
                    $newFileName = "profil_" . $_SESSION['user_id'] . "_" . time() . "." . $fileExt;
                    $destination = APPROOT . '/../public/uploads/profil/' . $newFileName;
                    
                    if (!file_exists(dirname($destination))) {
                        mkdir(dirname($destination), 0777, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        if ($fotoNama && file_exists(APPROOT . '/../public/uploads/profil/' . $fotoNama)) {
                            unlink(APPROOT . '/../public/uploads/profil/' . $fotoNama);
                        }
                        $fotoNama = $newFileName; 
                    }
                }
            }
        }

        // 3. Kumpulkan Data (DENGAN VALIDASI NULL)
        // Fungsi helper kecil untuk mengubah string kosong jadi NULL
        $fixNull = function($val) {
            return empty($val) ? null : $val;
        };

        $data = [
            'user_id'       => $_SESSION['user_id'],
            'nama_lengkap'  => $_POST['nama_lengkap'],
            'foto_profil'   => $fotoNama,
            'tempat_lahir'  => $fixNull($_POST['tempat_lahir']),
            'tanggal_lahir' => $fixNull($_POST['tanggal_lahir']), // <--- INI KRUSIAL
            'agama'         => $fixNull($_POST['agama']),
            'telepon'       => $fixNull($_POST['telepon']),
            'alamat'        => $fixNull($_POST['alamat']),
            'kota'          => $fixNull($_POST['kota']),
            'provinsi'      => $fixNull($_POST['provinsi']),
            'kode_pos'      => $fixNull($_POST['kode_pos'])
        ];
        
        // 4. Eksekusi Update
        try {
            if ($userModel->updateProfile($data)) {
                $_SESSION['nama_lengkap'] = $data['nama_lengkap']; 
                $_SESSION['flash_message'] = ['text' => 'Profil berhasil diperbarui.', 'type' => 'success'];
            } else {
                $_SESSION['flash_message'] = ['text' => 'Gagal mengupdate profil database.', 'type' => 'error'];
            }
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['text' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
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