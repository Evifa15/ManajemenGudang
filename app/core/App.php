<?php
/*
 * App Core Class (Router Utama)
 * Mem-parsing URL dan memanggil Controller/Method yang sesuai.
 * Format URL: /controller/method/params
 */
class App {
    // Properti default (jika URL kosong)
    protected $currentController = 'AuthController'; // Controller default
    protected $currentMethod = 'index';     // Method default
    protected $params = [];                 // Parameter

    public function __construct() {
        // 1. Parsing URL
        $url = $this->parseUrl();

        // 2. Cek Controller (Bagian 1 URL)
        if (isset($url[0])) {
            $controllerName = ucwords($url[0]) . 'Controller';
            
            // REVISI: Menggunakan APPROOT (lebih stabil)
            if (file_exists(APPROOT . '/controllers/' . $controllerName . '.php')) {
                $this->currentController = $controllerName;
                unset($url[0]);
            }
            // (Kita bisa tambahkan 'else' di sini untuk halaman 'not found'
            //  tapi untuk sekarang biarkan default)
        }

        // 3. Panggil file controller yang dituju
        // REVISI: Menggunakan APPROOT (lebih stabil)
        require_once APPROOT . '/controllers/' . $this->currentController . '.php';

        // 4. Inisiasi class controller (buat objek baru)
        $this->currentController = new $this->currentController;

        // 5. Cek Method (Bagian 2 URL)
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // 6. Ambil Parameters (Sisa URL)
        $this->params = $url ? array_values($url) : [];

        // 7. JALANKAN!
        call_user_func_array(
            [$this->currentController, $this->currentMethod],
            $this->params
        );
    }

    /**
     * Helper untuk mem-parsing URL
     * @return array URL yang sudah dipecah
     */
    public function parseUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/'); 
            $url = filter_var($url, FILTER_SANITIZE_URL); 
            $url = explode('/', $url); 
            return $url;
        }
        return []; 
    }
}