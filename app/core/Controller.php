<?php
/*
 * Base Controller (Induk)
 * Tugas: Menyediakan helper untuk memuat Model & View
 */
class Controller {

    /**
     * Helper untuk memuat Model
     * @param string $model Nama file Model (misal: 'User_model')
     * @return object Instance dari Model
     */
    public function model($model) {
        // Cek apakah file model ada
        // REVISI: Menggunakan konstanta APPROOT
        if (file_exists(APPROOT . '/models/' . $model . '.php')) {
            // REVISI: Menggunakan konstanta APPROOT
            require_once APPROOT . '/models/' . $model . '.php';
            
            // Inisiasi model dan kembalikan
            return new $model();
        } else {
            // Hentikan jika file model tidak ada
            die('Model "' . $model . '" tidak ditemukan');
        }
    }

    /**
     * Helper untuk memuat View
     * @param string $view Nama file View (misal: 'auth/login')
     * @param array $data Data yang ingin dikirim ke View (opsional)
     */
    public function view($view, $data = []) {
        // Cek apakah file view ada
        // REVISI: Menggunakan konstanta APPROOT
        if (file_exists(APPROOT . '/views/' . $view . '.php')) {
            
            // Ekstrak data agar bisa diakses sebagai variabel di view
            extract($data);
            
            // REVISI: Menggunakan konstanta APPROOT
            require_once APPROOT . '/views/' . $view . '.php';
        } else {
            // Hentikan jika file view tidak ada
            die('View "' . $view . '" tidak ditemukan');
        }
    }
}