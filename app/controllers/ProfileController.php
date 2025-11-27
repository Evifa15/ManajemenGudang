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
     * Memproses update Info Pribadi & Kontak (DENGAN FITUR UPLOAD FOTO)
     */
    public function processProfileInfo() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'profile/index');
            exit;
        }

        // 1. Ambil Data Profil Lama (Untuk mendapatkan nama foto saat ini)
        $userModel = $this->model('User_model');
        $currentProfile = $userModel->getJoinedUserProfile($_SESSION['user_id']);
        
        // Default: Gunakan foto lama (jika user tidak upload foto baru)
        $fotoNama = $currentProfile['foto_profil']; 

        // 2. Logika Upload Foto Baru
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['foto_profil'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            // Validasi Ekstensi
            if (in_array($fileExt, $allowed)) {
                // Validasi Ukuran (Max 2MB)
                if ($file['size'] <= 2000000) { 
                    // Buat nama file unik: profil_[USER_ID]_[WAKTU].[EXT]
                    $newFileName = "profil_" . $_SESSION['user_id'] . "_" . time() . "." . $fileExt;
                    
                    // Tentukan lokasi simpan (public/uploads/profil/)
                    $destination = APPROOT . '/../public/uploads/profil/' . $newFileName;
                    
                    // Buat folder jika belum ada (Penting!)
                    if (!file_exists(dirname($destination))) {
                        mkdir(dirname($destination), 0777, true);
                    }

                    // Pindahkan file
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // Hapus foto lama dari folder (untuk hemat penyimpanan)
                        // Cek apakah foto lama ada dan bukan null
                        if ($fotoNama && file_exists(APPROOT . '/../public/uploads/profil/' . $fotoNama)) {
                            unlink(APPROOT . '/../public/uploads/profil/' . $fotoNama);
                        }
                        
                        // Update variabel untuk disimpan ke DB
                        $fotoNama = $newFileName; 
                    } else {
                        $_SESSION['flash_message'] = ['text' => 'Gagal memindahkan file foto.', 'type' => 'error'];
                        header('Location: ' . BASE_URL . 'profile/index');
                        exit;
                    }
                } else {
                    $_SESSION['flash_message'] = ['text' => 'Ukuran foto terlalu besar (Max 2MB).', 'type' => 'error'];
                    header('Location: ' . BASE_URL . 'profile/index');
                    exit;
                }
            } else {
                $_SESSION['flash_message'] = ['text' => 'Format file tidak didukung (Gunakan JPG/PNG).', 'type' => 'error'];
                header('Location: ' . BASE_URL . 'profile/index');
                exit;
            }
        }

        // 3. Kumpulkan Data untuk Update Database
        $data = [
            'user_id' => $_SESSION['user_id'],
            'nama_lengkap' => $_POST['nama_lengkap'],
            'foto_profil' => $fotoNama, // <--- INI KUNCINYA (File baru atau lama)
            'tempat_lahir' => $_POST['tempat_lahir'],
            'tanggal_lahir' => $_POST['tanggal_lahir'],
            'agama' => $_POST['agama'],
            'telepon' => $_POST['telepon'],
            'alamat' => $_POST['alamat'],
            'kota' => $_POST['kota'],
            'provinsi' => $_POST['provinsi'],
            'kode_pos' => $_POST['kode_pos']
        ];
        
        // 4. Eksekusi Update
        if ($userModel->updateProfile($data)) {
            // Update nama di session agar header langsung berubah jika nama diganti
            $_SESSION['nama_lengkap'] = $data['nama_lengkap']; 
            $_SESSION['flash_message'] = ['text' => 'Profil berhasil diperbarui.', 'type' => 'success'];
        } else {
            $_SESSION['flash_message'] = ['text' => 'Gagal mengupdate profil di database.', 'type' => 'error'];
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