<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content" data-base-url="<?php echo BASE_URL; ?>">
    
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_message']['type']; ?>">
            <?php echo $_SESSION['flash_message']['text']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <div class="content-header">
        <h1>Riwayat Retur / Barang Rusak</h1>
    </div>

    <div class="history-top-bar">
        
        <div class="history-search-wrapper">
            <input type="text" id="liveSearchRetur" 
                   class="history-search-input" 
                   placeholder="Cari Barang, Status, Keterangan, atau Staff..." 
                   value="<?php echo htmlspecialchars($data['search']); ?>"
                   data-base-url="<?php echo BASE_URL; ?>">
        </div>

        <div class="history-header-buttons">
            
            <button type="button" id="btnToggleFilter" class="btn btn-secondary" 
                    style="height: 42px; display: flex; align-items: center; border: 1px solid #cbd5e1; color: #64748b; background: #fff; padding: 0 15px; font-weight: 600; white-space: nowrap;"
                    title="Buka/Tutup Filter Tanggal">
                 <i class="ph ph-funnel" style="font-size: 1.2rem; margin-right: 5px;"></i> Filter
            </button>

            <div class="dropdown-export-wrapper">
                <button type="button" id="btnToggleExportRetur" class="btn btn-brand-dark" 
                        style="height: 42px; display: flex; align-items: center; padding: 0 18px; white-space: nowrap;">
                    <i class="ph ph-export" style="font-size: 1.2rem; margin-right: 8px;"></i> Export
                    <i class="ph ph-caret-down" style="margin-left: 5px; font-size: 1rem;"></i>
                </button>

                <div id="exportMenuRetur" class="dropdown-menu-custom">
                    <a href="#" class="btn-export-retur-action" data-type="excel">
                        <i class="ph ph-microsoft-excel-logo" style="color: #10b981; font-size: 1.2rem;"></i> 
                        Excel (.xls)
                    </a>
                    <a href="#" class="btn-export-retur-action" data-type="csv">
                        <i class="ph ph-file-csv" style="color: #0ea5e9; font-size: 1.2rem;"></i> 
                        CSV (.csv)
                    </a>
                    <a href="#" class="btn-export-retur-action" data-type="pdf">
                        <i class="ph ph-file-pdf" style="color: #ef4444; font-size: 1.2rem;"></i> 
                        PDF Document
                    </a>
                </div>
            </div>
            
        </div>
    </div>

    <div id="filterPanel" class="search-card compact-filter" style="display: none; margin-bottom: 20px;">
        
        <div class="filter-panel-row">
            <div class="filter-panel-item">
                <label class="filter-label">Dari Tanggal</label>
                <input type="date" id="startDateRetur" class="filter-select-clean" 
                       value="<?php echo htmlspecialchars($data['start_date']); ?>">
            </div>

            <div class="filter-panel-item">
                <label class="filter-label">Sampai Tanggal</label>
                <input type="date" id="endDateRetur" class="filter-select-clean" 
                       value="<?php echo htmlspecialchars($data['end_date']); ?>">
            </div>

            <div style="flex: 0 0 auto;">
                <button type="button" id="btnResetRetur" class="btn-reset-filter" title="Reset Filter">
                    <i class="ph ph-arrow-counter-clockwise" style="font-size: 1.2rem; font-weight: bold;"></i>
                </button>
            </div>
        </div>
        
        <div style="padding-bottom: 10px;">
            <small style="color: #94a3b8;">*Data otomatis diperbarui saat tanggal dipilih.</small>
        </div>
    </div>

    <div class="table-card">
        <div class="table-wrapper-flat">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal Lapor</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Status Baru</th>
                        <th>Asal / Keterangan</th>
                        <th>Dilaporkan oleh</th>
                        <th>Lot/Batch</th>
                    </tr>
                </thead>
                <tbody id="tableBodyRetur">
                    <?php if (empty($data['history'])): ?>
                        <tr><td colspan="7" style="text-align:center; padding: 30px; color: #999;">Data tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['history'] as $his) : ?>
                        <tr>
                            <td style="color: #334155; font-size: 0.9rem;">
                                <?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?>
                            </td>
                            
                            <td>
                                <strong style="color: #152e4d;"><?php echo htmlspecialchars($his['nama_barang']); ?></strong>
                            </td>
                            
                            <td style="font-size: 1rem; color: #ef4444;"> 
                                <strong><?php echo (int)$his['jumlah']; ?></strong>
                            </td> 
                            
                            <td>
                                <span style="background: #fff1f2; color: #be123c; border: 1px solid #fda4af; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 700;">
                                    <?php echo htmlspecialchars($his['nama_status']); ?>
                                </span>
                            </td>
                            
                            <td style="color: #475569; font-style: italic;">
                                "<?php echo htmlspecialchars($his['keterangan']); ?>"
                            </td>
                            
                            <td>
                                <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; color: #475569;">
                                    <?php echo htmlspecialchars($his['staff_nama']); ?>
                                </span>
                            </td>
                            
                            <td style="font-family: monospace; color: #2f8ba9;">
                                <?php echo htmlspecialchars($his['lot_number']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-container custom-pagination" id="paginationContainerRetur">
        <span class="pagination-info">Menampilkan Halaman <?php echo $data['currentPage']; ?> dari <?php echo $data['totalPages']; ?></span>
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    
                    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
                    echo '<li class="page-item '.$prevDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage - 1).'">Previous</a></li>';
                    
                    if($totalPages > 0) {
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        
                        if($start > 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

                        for ($i = $start; $i <= $end; $i++) {
                            $active = ($i == $currentPage) ? 'active' : '';
                            echo '<li class="page-item '.$active.'"><a class="page-link" href="#" data-page="'.$i.'">'.$i.'</a></li>';
                        }

                        if($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }

                    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
                    echo '<li class="page-item '.$nextDisabled.'"><a class="page-link" href="#" data-page="'.($currentPage + 1).'">Next</a></li>';
                ?>
            </ul>
        </nav>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Logic Toggle Filter Panel (Show/Hide)
    const btnToggleFilter = document.getElementById('btnToggleFilter');
    const filterPanel = document.getElementById('filterPanel');

    if (btnToggleFilter && filterPanel) {
        btnToggleFilter.addEventListener('click', function() {
            if (filterPanel.style.display === 'none') {
                // Buka Panel
                filterPanel.style.display = 'block';
                
                // Ubah tampilan tombol jadi aktif
                this.style.backgroundColor = '#e2e8f0';
                this.style.color = '#152e4d';
                this.style.borderColor = '#152e4d';
            } else {
                // Tutup Panel
                filterPanel.style.display = 'none';
                
                // Kembalikan tampilan tombol
                this.style.backgroundColor = '#fff'; 
                this.style.color = '#64748b';
                this.style.borderColor = '#cbd5e1';
            }
        });
    }

    // 2. Logic Reset Filter
    const btnResetRetur = document.getElementById('btnResetRetur');
    if (btnResetRetur) {
        btnResetRetur.addEventListener('click', function() {
            // Kosongkan input tanggal
            document.getElementById('startDateRetur').value = '';
            document.getElementById('endDateRetur').value = '';
            
            const searchInput = document.getElementById('liveSearchRetur');
            if(searchInput) {
                searchInput.value = '';
                // Panggil event input agar main.js melakukan reload data tanpa filter
                searchInput.dispatchEvent(new Event('input')); 
            }
        });
    }

    // 3. Logic Export Dropdown (Sama dengan halaman lain)
    const btnToggleExport = document.getElementById('btnToggleExportRetur');
    const menuExport = document.getElementById('exportMenuRetur');

    if (btnToggleExport && menuExport) {
        // Toggle Menu
        btnToggleExport.addEventListener('click', function(e) {
            e.stopPropagation();
            menuExport.classList.toggle('show');
        });

        // Tutup jika klik di luar
        window.addEventListener('click', function(e) {
            if (!btnToggleExport.contains(e.target) && !menuExport.contains(e.target)) {
                menuExport.classList.remove('show');
            }
        });

        // Handle Klik Item Export
        menuExport.querySelectorAll('.btn-export-retur-action').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const type = this.getAttribute('data-type');
                const searchVal = document.getElementById('liveSearchRetur').value;
                const startVal = document.getElementById('startDateRetur').value;
                const endVal = document.getElementById('endDateRetur').value;
                const baseUrl = document.querySelector('main').getAttribute('data-base-url');

                // Tutup menu
                menuExport.classList.remove('show');

                const params = new URLSearchParams({
                    search: searchVal,
                    start_date: startVal,
                    end_date: endVal
                });

                // Tampilkan Loading Swal
                Swal.fire({
                    title: 'Memproses Export...',
                    text: 'Menyiapkan file ' + type.toUpperCase(),
                    timer: 1500,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // Redirect Export
                // Pastikan route 'admin/exportRiwayatRetur' sudah dibuat di Controller,
                // jika belum ada, Anda bisa pakai 'admin/exportRiwayatKeluar' sementara atau buat fungsi baru.
                // Di sini saya asumsikan Anda akan menggunakan exportRiwayatReturRusak atau sejenisnya.
                // Jika belum ada, ganti URL di bawah ini.
                setTimeout(() => {
                    // Sesuaikan nama method di controller, misal: exportRiwayatReturRusak
                    window.location.href = `${baseUrl}admin/exportRiwayatReturRusak/${type}?${params.toString()}`;
                }, 500);
            });
        });
    }
});
</script>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>