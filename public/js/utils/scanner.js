/**
 * --------------------------------------------------------------------------
 * Scanner Helper
 * Wrapper untuk library html5-qrcode.
 * Mengelola logika scan kamera webcam untuk barcode/QR.
 * --------------------------------------------------------------------------
 */

const ScannerHelper = {

    /**
     * Inisialisasi Scanner
     * @param {string} readerId - ID elemen div tempat kamera muncul (default: 'reader')
     * @param {string} buttonId - ID tombol trigger scan (default: 'btnScanBarcode')
     * @param {function} onScanSuccess - Callback function yang dipanggil saat barcode terbaca
     */
    init: (readerId = 'reader', buttonId = 'btnScanBarcode', onScanSuccess) => {
        const readerDiv = document.getElementById(readerId);
        const btnScan = document.getElementById(buttonId);
        
        // Safety check: jika elemen tidak ada di halaman ini, berhenti.
        if (!readerDiv || !btnScan) return;

        let html5QrcodeScanner = null;

        // --- 1. Event Listener Tombol Scan ---
        btnScan.addEventListener('click', function() {
            // Cek apakah scanner sedang aktif (terlihat)
            const isScanning = (readerDiv.style.display === 'block');

            if (!isScanning) {
                // A. MULAI SCANNING
                readerDiv.style.display = 'block';
                
                // Ubah tampilan tombol jadi Merah ("Stop Scan")
                btnScan.innerHTML = '<i class="ph ph-x-circle" style="font-size: 1.2rem;"></i> Stop Scan';
                btnScan.classList.remove('btn-brand-dark');
                btnScan.style.backgroundColor = '#ef4444'; 
                btnScan.style.borderColor = '#ef4444';
                btnScan.style.color = '#ffffff';

                // Konfigurasi Library Scanner
                const config = { 
                    fps: 10, // Frame per second (kecepatan baca)
                    qrbox: { width: 280, height: 150 }, // Kotak fokus persegi panjang (cocok utk barcode garis)
                    aspectRatio: 1.0,
                    // Daftar format yang didukung (QR + Barcode Garis 1D)
                    formatsToSupport: [ 
                        0, // QR_CODE
                        1, // AZTEC
                        2, // CODABAR
                        3, // CODE_39
                        4, // CODE_93
                        5, // CODE_128 (Paling umum utk logistik)
                        6, // DATA_MATRIX
                        8, // ITF
                        9, // EAN_13
                        10, // EAN_8
                        11, // PDF_417
                        14 // UPC_A
                    ]
                };

                // Buat Instance Baru
                html5QrcodeScanner = new Html5QrcodeScanner(readerId, config, /* verbose= */ false);
                
                // Render Kamera
                html5QrcodeScanner.render((decodedText, decodedResult) => {
                    // --- SUKSES BACA ---
                    console.log(`Scan Result: ${decodedText}`);
                    
                    // 1. Matikan kamera otomatis setelah berhasil
                    stopScanner();

                    // 2. Jalankan logika custom (misal: pilih dropdown)
                    if (typeof onScanSuccess === 'function') {
                        onScanSuccess(decodedText.trim());
                    }

                }, (errorMessage) => {
                    // --- GAGAL BACA (Per Frame) ---
                    // Biarkan kosong agar console browser tidak penuh spam "Scanning..."
                });

            } else {
                // B. STOP SCANNING (User klik tombol Stop)
                stopScanner();
            }
        });

        // --- 2. Helper Lokal: Matikan Scanner & Reset UI ---
        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(() => {
                    readerDiv.style.display = 'none';
                    
                    // Reset Tombol ke Biru ("Mulai Scan")
                    btnScan.innerHTML = '<i class="ph ph-camera" style="font-size: 1.2rem;"></i> Mulai Scan Kamera';
                    btnScan.classList.add('btn-brand-dark');
                    btnScan.style.backgroundColor = ''; 
                    btnScan.style.borderColor = '';
                    btnScan.style.color = '';
                    
                    html5QrcodeScanner = null;
                }).catch(error => {
                    console.error("Gagal stop kamera:", error);
                });
            } else {
                // Fallback jika belum init tapi display block
                readerDiv.style.display = 'none';
            }
        }
    },

    /**
     * Auto Init Product Search
     * Fungsi praktis untuk langsung menghubungkan scanner dengan dropdown #product_id.
     * Cukup panggil WMSScanner.autoInitProductSearch() di halaman transaksi.
     */
    autoInitProductSearch: () => {
        const productSelect = document.getElementById('product_id');
        
        // Hanya jalankan jika ada dropdown produk di halaman ini
        if (productSelect) {
            ScannerHelper.init('reader', 'btnScanBarcode', (scannedCode) => {
                let found = false;

                // Loop semua opsi di dropdown
                for (let i = 0; i < productSelect.options.length; i++) {
                    const optKode = productSelect.options[i].getAttribute('data-kode');
                    
                    // Bandingkan kode (Case Insensitive)
                    if (optKode && optKode.toUpperCase() === scannedCode.toUpperCase()) {
                        productSelect.selectedIndex = i; // Pilih opsi
                        found = true;
                        
                        // Trigger event 'change' agar logika lain (misal load stok/batch) jalan
                        productSelect.dispatchEvent(new Event('change'));

                        // Feedback Visual Sukses
                        Swal.fire({
                            icon: 'success',
                            title: 'Ditemukan!',
                            text: `${scannedCode} - ${productSelect.options[i].text}`,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        break;
                    }
                }

                // Jika barang tidak ada di dropdown
                if (!found) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    
                    Toast.fire({
                        icon: 'warning',
                        title: `Kode "${scannedCode}" tidak terdaftar.`
                    });
                }
            });
        }
    }
};

// Export ke Global Scope agar bisa dipanggil dari module lain
window.WMSScanner = ScannerHelper;