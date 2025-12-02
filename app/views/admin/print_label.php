<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
    $p = $data['product'];
?>

<main class="app-content">
    
    <div class="content-header no-print">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>admin/barang" class="btn" style="background: #6c757d; color: white;">&larr; Kembali</a>
            <h1>Cetak Label: <?php echo htmlspecialchars($p['nama_barang']); ?></h1>
        </div>
    </div>

    <div class="widget no-print" style="max-width: 600px; margin-bottom: 30px;">
        <h3 style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">🖨️ Pengaturan Cetak</h3>
        
        <div class="form-group">
            <label>Jumlah Label yang akan dicetak:</label>
            <input type="number" id="qtyPrint" class="form-control" value="10" min="1" max="100" style="font-size: 1.2em;">
        </div>
        
        <div class="form-group">
            <label>Ukuran/Skala Barcode:</label>
            <select id="scalePrint" class="form-control">
                <option value="1">Kecil (Compact)</option>
                <option value="1.5" selected>Sedang (Standar)</option>
                <option value="2">Besar</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button onclick="generateLabels()" class="btn btn-primary">🔄 Generate Preview</button>
            <button onclick="window.print()" class="btn btn-success">🖨️ Cetak Sekarang</button>
        </div>
    </div>

    <div id="printArea">
        </div>

</main>

<script src="<?php echo BASE_URL; ?>js/JsBarcode.all.min.js"></script>
<script>
    if (typeof JsBarcode === 'undefined') {
        // Fallback ke CDN jika lokal gagal
        document.write('<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>');
    }
</script>

<script>
    // Data dari PHP
    const productCode = "<?php echo $p['kode_barang']; ?>";
    const productName = "<?php echo $p['nama_barang']; ?>";

    function generateLabels() {
        // Cek apakah library sudah dimuat
        if (typeof JsBarcode === 'undefined') {
            alert("Library Barcode belum siap. Pastikan koneksi internet atau file JsBarcode.all.min.js ada.");
            return;
        }

        const qty = document.getElementById('qtyPrint').value;
        const scale = parseFloat(document.getElementById('scalePrint').value); // Pastikan float
        const container = document.getElementById('printArea');
        
        container.innerHTML = ''; // Reset

        for (let i = 0; i < qty; i++) {
            // 1. Buat Wrapper Label
            const labelDiv = document.createElement('div');
            labelDiv.className = 'label-sticker';
            
            // 2. Buat Elemen SVG untuk Barcode
            // PENTING: JsBarcode butuh elemen <svg>, <canvas>, atau <img>
            const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            
            // 3. Masukkan Nama Barang
            const nameTag = document.createElement('div');
            nameTag.className = 'label-name';
            nameTag.innerText = productName.substring(0, 25); // Potong jika kepanjangan

            // 4. Susun
            labelDiv.appendChild(nameTag);
            labelDiv.appendChild(svg);
            container.appendChild(labelDiv);

            // 5. Generate Barcode
            try {
                JsBarcode(svg, productCode, {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2 * scale, // Sesuaikan lebar garis dengan skala
                    height: 40 * scale, // Sesuaikan tinggi dengan skala
                    displayValue: true,
                    fontSize: 14 * scale, // Sesuaikan font dengan skala
                    margin: 5
                });
            } catch (e) {
                console.error("Gagal generate barcode:", e);
                labelDiv.innerHTML += "<p style='color:red; font-size:10px;'>Error Barcode</p>";
            }
        }
    }

    // Generate otomatis saat halaman dimuat (tunggu sebentar agar library siap)
    window.addEventListener('load', function() {
        setTimeout(generateLabels, 500); 
    });
</script>

<style>
    /* Styling Label di Layar */
    #printArea {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .label-sticker {
        border: 1px dashed #ccc;
        padding: 10px;
        background: white;
        text-align: center;
        width: 220px; /* Sedikit diperlebar */
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }

    .label-name {
        font-weight: bold;
        font-size: 12px;
        margin-bottom: 5px;
        text-transform: uppercase;
        word-wrap: break-word;
        width: 100%;
    }
    
    /* Pastikan SVG tidak keluar batas */
    .label-sticker svg {
        max-width: 100%;
        height: auto;
    }

    /* --- CSS KHUSUS PRINT (PENTING) --- */
    @media print {
        /* Sembunyikan Sidebar, Header, dan Panel Setting */
        .app-sidebar, .app-header, .no-print {
            display: none !important;
        }

        /* Atur Margin Kertas */
        @page {
            margin: 0.5cm;
            size: auto;
        }

        body {
            background: white;
        }

        .app-content {
            margin: 0;
            padding: 0;
            overflow: visible;
            height: auto;
        }

        #printArea {
            display: block;
        }

        .label-sticker {
            border: 1px solid #ddd; /* Ganti dashed jadi solid tipis saat print */
            float: left; /* Agar berjejer */
            margin: 5px;
            page-break-inside: avoid; /* Jangan potong stiker di tengah halaman */
        }
    }
</style>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>