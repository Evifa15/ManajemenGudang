<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
    $p = $data['product'];
?>

<main class="app-content">
    
    <div class="card no-print" style="border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border-radius: 12px; padding: 40px; margin-bottom: 30px; max-width: 800px; margin-left: auto; margin-right: auto; margin-top: 20px;">
        
        <div style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px;">
            
            <div style="width: 60px; height: 60px; background: #e0f2fe; color: var(--primer-darkblue); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                <i class="ph ph-qr-code" style="font-size: 32px;"></i>
            </div>
            
            <h1 style="margin: 0; font-size: 1.8rem; color: var(--primer-darkblue); font-weight: 800; margin-bottom: 8px;">
                Cetak Label Barcode
            </h1>
            
            <p style="color: #64748b; font-size: 1rem; margin: 0;">
                Produk: <strong style="color: var(--primer-lightblue);"><?php echo htmlspecialchars($p['nama_barang']); ?></strong>
                <span style="margin: 0 8px; color: #cbd5e1;">|</span>
                Kode: <strong><?php echo htmlspecialchars($p['kode_barang']); ?></strong>
            </p>
        </div>

        <div style="background-color: #f8fafc; padding: 25px; border-radius: 10px; border: 1px solid #e2e8f0;">
            <h4 style="font-size: 0.95rem; color: #152e4d; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-sliders-horizontal"></i> Pengaturan Cetak
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 5px; display: block;">Jumlah Label</label>
                    <div style="position: relative;">
                        <input type="number" id="qtyPrint" class="form-control" value="10" min="1" max="100" 
                               style="padding: 12px; border-radius: 8px; font-weight: bold; color: #152e4d; border-color: #cbd5e1;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 5px; display: block;">Ukuran Label</label>
                    <div style="position: relative;">
                        <select id="scalePrint" class="form-control" style="padding: 12px; border-radius: 8px; font-weight: 600; border-color: #cbd5e1; appearance: none;">
                            <option value="1">Kecil (Compact)</option>
                            <option value="1.5" selected>Sedang (Standar)</option>
                            <option value="2">Besar (Jelas)</option>
                        </select>
                        <i class="ph ph-caret-down" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 25px; display: flex; gap: 15px;">
            <button onclick="generateLabels()" class="btn" 
                    style="flex: 1; background-color: #ffffff; color: var(--primer-lightblue); border: 1px solid var(--primer-lightblue); font-weight: 600; padding: 12px; border-radius: 8px;">
                <i class="ph ph-arrows-clockwise"></i> Update Preview
            </button>
            
            <button onclick="window.print()" class="btn btn-brand-dark" 
                    style="flex: 2; padding: 12px; border-radius: 8px; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <i class="ph ph-printer" style="font-size: 1.4rem;"></i> Cetak Sekarang
            </button>
        </div>
    </div>

    <div class="no-print" style="max-width: 800px; margin: 0 auto;">
        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 15px; gap: 10px;">
            <span style="height: 1px; background: #e2e8f0; flex: 1;"></span>
            <span style="color: #94a3b8; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Tampilan Label</span>
            <span style="height: 1px; background: #e2e8f0; flex: 1;"></span>
        </div>
    </div>

    <div id="printArea">
        </div>

</main>

<script src="<?php echo BASE_URL; ?>js/JsBarcode.all.min.js"></script>
<script>
    if (typeof JsBarcode === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>');
    }
</script>

<script>
    const productCode = "<?php echo $p['kode_barang']; ?>";
    const productName = "<?php echo $p['nama_barang']; ?>";

    function generateLabels() {
        if (typeof JsBarcode === 'undefined') return;

        const qty = document.getElementById('qtyPrint').value;
        const scale = parseFloat(document.getElementById('scalePrint').value);
        const container = document.getElementById('printArea');
        
        container.innerHTML = ''; 

        for (let i = 0; i < qty; i++) {
            const labelDiv = document.createElement('div');
            labelDiv.className = 'label-sticker';
            
            const nameTag = document.createElement('div');
            nameTag.className = 'label-name';
            // Potong nama jika terlalu panjang
            nameTag.innerText = productName.length > 35 ? productName.substring(0, 35) + '...' : productName; 

            const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            
            labelDiv.appendChild(nameTag);
            labelDiv.appendChild(svg);
            container.appendChild(labelDiv);

            try {
                JsBarcode(svg, productCode, {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2 * scale,
                    height: 40 * scale,
                    displayValue: true,
                    fontSize: 12 * scale,
                    margin: 5,
                    textMargin: 2
                });
            } catch (e) {
                console.error(e);
            }
        }
    }

    window.addEventListener('load', function() {
        setTimeout(generateLabels, 300); 
    });
</script>

<style>
    /* --- PREVIEW MODE (LAYAR) --- */
    #printArea {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
        padding-bottom: 50px;
    }

    .label-sticker {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        width: 240px; /* Ukuran umum label */
        min-height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }

    /* Hiasan kuning di atas label saat preview */
    .label-sticker::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--primer-yellow);
        opacity: 0.5;
    }

    .label-sticker:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.08);
        border-color: var(--primer-lightblue);
    }

    .label-name {
        font-weight: 700;
        font-size: 11px;
        color: #152e4d;
        margin-bottom: 2px;
        text-transform: uppercase;
        line-height: 1.3;
        padding: 0 5px;
    }
    
    .label-sticker svg {
        max-width: 100%;
        height: auto;
    }

    /* --- PRINT MODE (CETAK) --- */
    @media print {
        .app-sidebar, .app-header, .no-print { display: none !important; }
        .app-content { margin: 0 !important; padding: 0 !important; width: 100%; }
        body { background: white !important; }

        #printArea {
            display: block;
            width: 100%;
        }

        .label-sticker {
            float: left;
            border: 1px solid #000; /* Border hitam tipis untuk panduan potong */
            margin: 5px;
            box-shadow: none;
            border-radius: 0;
            width: 220px; /* Sesuaikan fisik */
            page-break-inside: avoid;
        }
        
        .label-sticker::before { display: none; } /* Hapus hiasan kuning saat print */
    }
</style>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>