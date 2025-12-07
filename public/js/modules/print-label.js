// manajemengudang/public/js/modules/print-label.js

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Ambil Elemen DOM
    const inputs = {
        kode: document.getElementById('kodeBarang'),
        nama: document.getElementById('namaBarang'),
        type: document.getElementById('barcodeType'),
        size: document.getElementById('labelSize'),
        showName: document.getElementById('showName'),
        showCode: document.getElementById('showCode'),
        btnExportMenu: document.getElementById('btnExportMenu'),
        exportDropdown: document.getElementById('exportDropdown'),
        exportOptions: document.querySelectorAll('.export-opt'),
        printArea: document.getElementById('printArea')
    };

    // 2. Konfigurasi Ukuran Label (width dan height dalam satuan mm)
    const labelConfigs = {
        '50x50': { width: '50mm', height: '50mm', fontSize: '10px' },
        '40x30': { width: '40mm', height: '30mm', fontSize: '8px' },
        '30x20': { width: '30mm', height: '20mm', fontSize: '6px' },
        '60x40': { width: '60mm', height: '40mm', fontSize: '12px' },
    };

    // Fungsi utilitas untuk konversi mm ke pixel (asumsi 1mm = 3.779527559px pada 96 DPI)
    const mmToPx = (mm) => Math.round(parseFloat(mm.replace('mm', '')) * 3.779527559);

    // 3. Fungsi Render Utama
    function renderLabels() {
        inputs.printArea.innerHTML = ''; 

        const kode = inputs.kode.value.trim();
        const nama = inputs.nama.value.trim();
        const type = inputs.type.value;
        const sizeKey = inputs.size.value;
        
        const config = labelConfigs[sizeKey] || labelConfigs['50x50'];
        
        // Buat Elemen Label
        const labelDiv = document.createElement('div');
        
        // Gunakan Class CSS untuk styling dasar (Shadow, Border, Background)
        labelDiv.className = 'label-preview-item'; 
        
        // Set ukuran fisik (Untuk kalkulasi rasio)
        // Di preview, kita bisa scale ini menggunakan CSS transform jika perlu, 
        // tapi untuk sekarang biarkan mengikuti ukuran pixel relatif agar terlihat jelas.
        
        // PENTING: Untuk preview di layar, kita konversi mm ke px agak besar biar jelas
        // Atau biarkan fixed size untuk preview (misal max 200px)
        
        // Strategi: Set ukuran fix di preview (misal 200px x ratio)
        // Tapi saat export, gunakan ukuran config asli.
        
        if (inputs.printArea.id === 'printArea') {
            // MODE PREVIEW LAYAR
            // Kita buat labelnya responsive tapi proporsional
            
            // Konversi sederhana untuk tampilan layar (Zoomed in)
            const zoomFactor = 4; // Zoom agar terlihat jelas di layar
            labelDiv.style.width = (parseInt(config.width) * zoomFactor) + 'px';
            labelDiv.style.height = (parseInt(config.height) * zoomFactor) + 'px';
            
            labelDiv.style.padding = '10px'; // Padding visual
        } else {
             // MODE EXPORT (Invisible)
             labelDiv.style.width = config.width; 
             labelDiv.style.height = config.height;
             labelDiv.style.padding = '2px';
        }

        // Flexbox centering content
        labelDiv.style.display = 'flex';
        labelDiv.style.flexDirection = 'column';
        labelDiv.style.alignItems = 'center';
        labelDiv.style.justifyContent = 'center';
        labelDiv.style.textAlign = 'center';

        // --- A. NAMA BARANG ---
        if (inputs.showName.checked) {
            const nameEl = document.createElement('div');
            nameEl.className = 'label-name-class';
            nameEl.textContent = nama;
            
            // Ukuran font proporsional
            nameEl.style.fontSize = inputs.printArea.id === 'printArea' ? '12px' : config.fontSize;
            nameEl.style.fontWeight = 'bold';
            nameEl.style.marginBottom = '5px';
            nameEl.style.lineHeight = '1.2';
            nameEl.style.color = '#000'; // Selalu hitam untuk cetak
            labelDiv.appendChild(nameEl);
        }

        // --- B. CODE CONTAINER ---
        const codeContainer = document.createElement('div');
        codeContainer.className = 'code-container';
        codeContainer.style.flex = '1';
        codeContainer.style.display = 'flex';
        codeContainer.style.alignItems = 'center';
        codeContainer.style.justifyContent = 'center';
        codeContainer.style.width = '100%';
        codeContainer.style.overflow = 'hidden';

        // --- C. RENDER BARCODE/QR ---
        // (Kode pembuatan Canvas/Img Barcode/QR Tetap Sama seperti sebelumnya)
        // ... Copy logika JsBarcode dan qrcode di sini ...
        // Pastikan img.style.maxWidth = '100%' dan height auto.

        if (type === 'barcode') {
            const canvas = document.createElement('canvas');
            try {
                JsBarcode(canvas, kode, {
                    format: "CODE128",
                    displayValue: false, // Kita buat text manual di bawah biar rapi
                    fontSize: 10,
                    margin: 0,
                    height: 50,
                    width: 2
                });
                const img = document.createElement('img');
                img.src = canvas.toDataURL("image/png");
                img.style.maxWidth = '100%';
                img.style.maxHeight = '100%';
                img.style.objectFit = 'contain';
                codeContainer.appendChild(img);
            } catch (e) { }
        } else {
            // QR Code
            try {
                const qr = qrcode(0, 'M');
                qr.addData(kode);
                qr.make();
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = qr.createImgTag(4, 0); 
                const qrImg = tempDiv.querySelector('img');
                if(qrImg) {
                    qrImg.style.maxWidth = '100%';
                    qrImg.style.maxHeight = '100%';
                    qrImg.style.objectFit = 'contain';
                    qrImg.style.imageRendering = 'pixelated';
                    codeContainer.appendChild(qrImg);
                }
            } catch (e) { }
        }
        
        labelDiv.appendChild(codeContainer);

        // --- D. TEXT KODE (BAWAH) ---
        if (inputs.showCode.checked) {
            const textCodeEl = document.createElement('div');
            textCodeEl.textContent = kode;
            textCodeEl.style.fontSize = inputs.printArea.id === 'printArea' ? '10px' : config.fontSize;
            textCodeEl.style.fontFamily = 'monospace';
            textCodeEl.style.marginTop = '2px';
            textCodeEl.style.color = '#000';
            labelDiv.appendChild(textCodeEl);
        }

        inputs.printArea.appendChild(labelDiv);
    }

    // 4. Fungsi Export dan Cetak (Logika tetap sama)
    function exportLabel(format) {
        const labelToExport = inputs.printArea.querySelector('.label-item');

        if (!labelToExport) {
            alert('Tidak ada label untuk diekspor/dicetak.');
            return;
        }

        const kode = inputs.kode.value.trim();
        const sizeKey = inputs.size.value;
        const config = labelConfigs[sizeKey] || labelConfigs['50x50'];

        // Kloning elemen label
        const clone = labelToExport.cloneNode(true);
        
        // --- Setup Clone untuk Export (Ukuran Fisik) ---
        clone.style.width = config.width;
        clone.style.height = config.height;
        clone.style.padding = '3px';
        clone.style.border = '1px solid #000'; 
        clone.style.margin = '2mm';
        clone.style.backgroundColor = 'white'; 
        clone.style.boxShadow = 'none';

        // Atur font size di clone (untuk cetak)
        const cloneNameEl = clone.querySelector('.label-name-class');
        if(cloneNameEl) cloneNameEl.style.fontSize = config.fontSize;

        const cloneCodeTextEl = clone.querySelector('.label-code-text');
        if(cloneCodeTextEl) cloneCodeTextEl.style.fontSize = config.fontSize;
        
        // Pastikan gambar di dalam clone diskala ke 100%
        const cloneImage = clone.querySelector('.code-container img') || clone.querySelector('.code-container canvas');
        if(cloneImage) {
            cloneImage.removeAttribute('width');
            cloneImage.removeAttribute('height');
            cloneImage.style.width = '100%';
            cloneImage.style.height = '100%';
            cloneImage.style.objectFit = 'contain';
        }
        
        // Buat temporary div untuk rendering
        const tempDiv = document.createElement('div');
        tempDiv.style.position = 'absolute';
        tempDiv.style.top = '-9999px';
        tempDiv.style.left = '-9999px';
        tempDiv.appendChild(clone);
        document.body.appendChild(tempDiv);
        
        // Gunakan html2canvas untuk menangkap tampilan
        html2canvas(clone, { 
            scale: 3, 
            useCORS: true 
        }).then(canvas => {
            document.body.removeChild(tempDiv);

            if (format === 'print') {
                const printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>Cetak Label</title>');
                
                printWindow.document.write('<style>');
                printWindow.document.write(`
                    @page { size: auto; margin: 0; }
                    body { margin: 0; padding: 0; background: white; }
                    .print-container { 
                        display: flex; 
                        flex-wrap: wrap; 
                        padding: 5mm; 
                    }
                    .label-item {
                        border: 1px solid #000;
                        padding: 3px;
                        margin: 2mm; 
                        text-align: center;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: space-between;
                        box-sizing: border-box;
                        background: white; 
                        
                        width: ${config.width};
                        height: ${config.height};
                        font-size: ${config.fontSize}; 
                        
                    }
                    .code-container {
                        flex-grow: 1;
                        width: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        overflow: hidden; 
                    }
                    .code-container img, .code-container canvas {
                        max-width: 95%;   
                        max-height: 95%; 
                        width: 95% !important;       
                        height: 95% !important;
                        object-fit: contain; 
                        display: block; 
                        background: transparent !important;
                    }
                    .label-name-class, .label-code-text {
                        max-width: 100%;
                        word-break: break-word;
                        white-space: normal;
                        flex-shrink: 0;
                    }
                `);
                printWindow.document.write('</style>');
                
                printWindow.document.write('</head><body>');
                printWindow.document.write('<div class="print-container">');

                for (let j = 0; j < 10; j++) {
                    printWindow.document.write(clone.outerHTML);
                }
                
                printWindow.document.write('</div>');
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                
                setTimeout(() => printWindow.print(), 200);

            } else {
                // Export ke gambar (PNG/JPG)
                const imageURL = canvas.toDataURL(`image/${format === 'jpg' ? 'jpeg' : 'png'}`);
                const a = document.createElement('a');
                a.href = imageURL;
                a.download = `${kode}_label.${format}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        }).catch(err => {
            console.error("Error during export:", err);
            document.body.removeChild(tempDiv);
            alert("Gagal melakukan export/cetak. Pastikan kode sudah terisi.");
        });
    }


    // 5. Event Listeners untuk Export/Cetak (Logika tetap sama)
    inputs.btnExportMenu.addEventListener('click', function() {
        inputs.exportDropdown.classList.toggle('show');
    });

    inputs.exportOptions.forEach(opt => {
        opt.addEventListener('click', function(event) {
            event.preventDefault();
            inputs.exportDropdown.classList.remove('show');
            const format = this.getAttribute('data-format');
            exportLabel(format);
        });
    });

    window.addEventListener('click', function(event) {
        if (!event.target.matches('#btnExportMenu')) {
            if (inputs.exportDropdown.classList.contains('show')) {
                inputs.exportDropdown.classList.remove('show');
            }
        }
    });


    // 6. Event Listeners Input
    // Tambahkan listener langsung ke elemen untuk memastikan event terdaftar
    const allInputs = [inputs.type, inputs.size, inputs.showName, inputs.showCode];
    allInputs.forEach(el => {
        if(el) el.addEventListener('change', renderLabels);
    });

    // Panggil render awal saat halaman dimuat
    renderLabels();
});