<?php

class PeminjamController extends Controller {

    public function __construct() {
        // 1. GATEKEEPER: Pastikan sudah login
        if (!isset($_SESSION['is_logged_in'])) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }

        // 2. GATEKEEPER: Pastikan role-nya 'peminjam' (atau Admin/Staff untuk tes, tapi di sini kita batasi)
        if ($_SESSION['role'] != 'peminjam') {
            $_SESSION['flash_message'] = ['text' => 'Anda tidak memiliki hak akses sebagai peminjam.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }
        
        // 3. GATEKEEPER: Pastikan status 'aktif'
        if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == 'baru') {
             header('Location: ' . BASE_URL . 'auth/forceChangePassword');
            exit;
        }
    }

    /**
     * Halaman Dashboard (Beranda) Peminjam
     */
    public function index() {
        $this->dashboard();
    }
    
    public function dashboard() {
        $loanModel = $this->model('Loan_model');

        // Ambil riwayat peminjaman peminjam ini saja
        $history = $loanModel->getRiwayatPeminjamanByPeminjamId($_SESSION['user_id']);
        
        $data = [
            'judul' => 'Dashboard Peminjam',
            'history' => $history
        ];
        
        $this->view('peminjam/dashboard', $data);
    }

    /**
     * Menampilkan halaman Katalog Barang yang Bisa Dipinjam
     */
    public function katalog() {
        $productModel = $this->model('Product_model');
        // Hanya ambil barang yang ditandai 'bisa_dipinjam'
        $availableProducts = $productModel->getProductsForLoan(); 

        $data = [
            'judul' => 'Katalog Peminjaman',
            'products' => $availableProducts
        ];
        
        $this->view('peminjam/katalog_peminjaman', $data);
    }
    
    /**
     * Menampilkan Form Pengajuan Peminjaman untuk Barang tertentu
     */
    public function ajukan($product_id) {
        $productModel = $this->model('Product_model');
        $product = $productModel->getProductById($product_id);

        if (!$product || $product['bisa_dipinjam'] != 1) {
            $_SESSION['flash_message'] = ['text' => 'Barang tidak ditemukan atau tidak bisa dipinjam.', 'type' => 'error'];
            header('Location: ' . BASE_URL . 'peminjam/katalog');
            exit;
        }

        $data = [
            'judul' => 'Pengajuan Peminjaman',
            'product' => $product
        ];
        $this->view('peminjam/form_pengajuan', $data);
    }

    /**
     * Memproses form pengajuan peminjaman
     */
    public function processPengajuan() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . BASE_URL . 'peminjam/katalog');
            exit;
        }
        
        $data = [
            'product_id' => $_POST['product_id'],
            'peminjam_user_id' => $_SESSION['user_id'],
            'tgl_rencana_pinjam' => $_POST['tgl_rencana_pinjam'],
            'tgl_rencana_kembali' => $_POST['tgl_rencana_kembali'],
            'alasan_pinjam' => $_POST['alasan_pinjam'],
            'status_pinjam' => 'Diajukan' // Status awal
        ];
        
        $loanModel = $this->model('Loan_model');

        try {
            $loanModel->createLoanRequest($data);
            $_SESSION['flash_message'] = ['text' => 'Pengajuan peminjaman berhasil dikirim! Menunggu persetujuan Staff.', 'type' => 'success'];
        } catch (Exception $e) {
            $_SESSION['flash_message'] = ['text' => 'Gagal mengirim pengajuan: ' . $e->getMessage(), 'type' => 'error'];
        }

        header('Location: ' . BASE_URL . 'peminjam/riwayatSaya');
        exit;
    }
    
    /**
     * Menampilkan riwayat peminjaman user ini
     */
    public function riwayatSaya() {
        $loanModel = $this->model('Loan_model');
        $history = $loanModel->getRiwayatPeminjamanByPeminjamId($_SESSION['user_id']);

        $data = [
            'judul' => 'Riwayat Peminjaman Saya',
            'history' => $history
        ];
        $this->view('peminjam/riwayat_peminjaman', $data);
    }
}