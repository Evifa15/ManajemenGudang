<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    
    <div class="content-header">
        <h1>Manajemen Barang</h1>
        <div class="header-buttons">
            <!-- TOMBOL HAPUS MASAL -->
            <button type="button" id="btnBulkDelete" class="btn btn-danger" style="display: none; margin-right: 10px;" 
                    data-url="<?php echo BASE_URL; ?>admin/deleteBulkBarang">
                🗑️ Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>

            <a href="<?php echo BASE_URL; ?>admin/masterDataConfig" class="btn" style="background-color: #6c757d; color: white;">
                ⚙️ Konfigurasi Data Atribut
            </a>
            <a href="<?php echo BASE_URL; ?>admin/addBarang" class="btn btn-primary">
                + Tambah Barang Baru
            </a>
        </div>
    </div>

    <!-- FILTER & SEARCH -->
    <div class="search-container" style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 250px;">
                <input type="text" id="liveSearch" class="form-control" 
                       placeholder="🔍 Cari Kode, Nama, Merek..." 
                       value="<?php echo htmlspecialchars($data['search']); ?>"
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select id="filterKategori" class="filter-select live-filter" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">- Semua Kategori -</option>
                    <?php foreach($data['allKategori'] as $kat): ?>
                        <option value="<?php echo $kat['kategori_id']; ?>" <?php if($data['kategori_filter'] == $kat['kategori_id']) echo 'selected'; ?>>
                            <?php echo $kat['nama_kategori']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select id="filterMerek" class="filter-select live-filter" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">- Semua Merek -</option>
                     <?php foreach($data['allMerek'] as $mrk): ?>
                        <option value="<?php echo $mrk['merek_id']; ?>" <?php if($data['merek_filter'] == $mrk['merek_id']) echo 'selected'; ?>>
                            <?php echo $mrk['nama_merek']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select id="filterStatus" class="filter-select live-filter" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    <option value="">- Semua Status -</option>
                     <?php foreach($data['allStatus'] as $stat): ?>
                        <option value="<?php echo $stat['status_id']; ?>" <?php if($data['status_filter'] == $stat['status_id']) echo 'selected'; ?>>
                            <?php echo $stat['nama_status']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <a href="<?php echo BASE_URL; ?>admin/barang" class="btn btn-danger" style="padding: 10px 15px;" title="Reset Filter">↻</a>
            </div>
        </div>
    </div>

    <!-- TABEL -->
    <div class="content-table">
        <table>
            <thead>
                <tr>
                    <!-- HEADER CHECKBOX -->
                    <th style="text-align:center; width: 40px;">
                        <input type="checkbox" id="selectAll" style="transform: scale(1.2); cursor: pointer;">
                    </th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Merek</th>
                    <th>Stok Saat Ini</th>
                    <th>Satuan</th>
                    <th>Stok Min.</th>
                    <th>Lokasi (Contoh)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="barangTableBody">
                <?php if (empty($data['products'])): ?>
                    <tr><td colspan="10" style="text-align:center;">Data tidak ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['products'] as $prod) : ?>
                    <tr>
                        <!-- BODY CHECKBOX -->
                        <td style="text-align:center;">
                            <input type="checkbox" class="row-checkbox" value="<?php echo $prod['product_id']; ?>" style="transform: scale(1.2); cursor: pointer;">
                        </td>
                        <td><?php echo htmlspecialchars($prod['kode_barang']); ?></td>
                        <td><?php echo htmlspecialchars($prod['nama_barang']); ?></td>
                        <td><?php echo htmlspecialchars($prod['nama_kategori']); ?></td>
                        <td><?php echo htmlspecialchars($prod['nama_merek']); ?></td>
                        <td><strong><?php echo (int)$prod['stok_saat_ini']; ?></strong></td> 
                        <td><?php echo htmlspecialchars($prod['nama_satuan']); ?></td>
                        <td><?php echo htmlspecialchars($prod['stok_minimum']); ?></td>
                        <td><?php echo htmlspecialchars($prod['kode_lokasi']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>admin/editBarang/<?php echo $prod['product_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <button type="button" class="btn btn-danger btn-sm btn-delete" 
                                    data-url="<?php echo BASE_URL; ?>admin/deleteBarang/<?php echo $prod['product_id']; ?>">
                                Hapus
                            </button>  
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination-container" id="paginationContainer">
        <nav>
            <ul class="pagination">
                <?php
                    $currentPage = $data['currentPage'];
                    $totalPages = $data['totalPages'];
                    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
                    echo '<li class="page-item '.$prevDisabled.'"><a class="page-link" href="javascript:void(0);" data-page="'.($currentPage - 1).'">Previous</a></li>';
                    $start = 1; $end = $totalPages;
                    if ($totalPages > 5) {
                         $start = max(1, $currentPage - 2);
                         $end = min($totalPages, $currentPage + 2);
                    }
                    for ($i = $start; $i <= $end; $i++) {
                        $active = ($i == $currentPage) ? 'active' : '';
                        echo '<li class="page-item '.$active.'"><a class="page-link" href="javascript:void(0);" data-page="'.$i.'">'.$i.'</a></li>';
                    }
                    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
                    echo '<li class="page-item '.$nextDisabled.'"><a class="page-link" href="javascript:void(0);" data-page="'.($currentPage + 1).'">Next</a></li>';
                ?>
            </ul>
        </nav>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. LOGIKA LIVE SEARCH & PAGINATION ---
    const searchInput = document.getElementById('liveSearch');
    const filterKategori = document.getElementById('filterKategori');
    const filterMerek = document.getElementById('filterMerek');
    const filterStatus = document.getElementById('filterStatus');
    const tableBody = document.getElementById('barangTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const baseUrl = "<?php echo BASE_URL; ?>admin/barang";
    const bulkDeleteBtn = document.getElementById('btnBulkDelete');
    const selectedCountSpan = document.getElementById('selectedCount');
    const selectAllCheckbox = document.getElementById('selectAll');

    let currentPage = <?php echo $data['currentPage']; ?>;

    function debounce(func, timeout = 300){
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    const performSearch = (targetPage = 1) => {
        const searchVal = searchInput.value;
        const katVal = filterKategori.value;
        const merekVal = filterMerek.value;
        const statVal = filterStatus.value;

        currentPage = targetPage;

        const params = new URLSearchParams({
            ajax: 1,
            page: currentPage,
            search: searchVal,
            kategori: katVal,
            merek: merekVal,
            status: statVal
        });

        tableBody.style.opacity = '0.4';

        fetch(`${baseUrl}?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.html;
                tableBody.style.opacity = '1';
                paginationContainer.innerHTML = data.pagination;

                params.delete('ajax'); 
                const newUrl = `${baseUrl}?${params.toString()}`;
                window.history.pushState({path: newUrl}, '', newUrl);

                // Reset Checkbox State setelah refresh tabel
                if(selectAllCheckbox) selectAllCheckbox.checked = false;
                updateBulkBtn(); // Sembunyikan tombol hapus
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.style.opacity = '1';
            });
    };

    searchInput.addEventListener('input', debounce(() => performSearch(1)));
    const liveFilters = document.querySelectorAll('.live-filter');
    liveFilters.forEach(el => {
        el.addEventListener('change', () => performSearch(1));
    });
    paginationContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('page-link')) {
            e.preventDefault();
            const parentLi = e.target.parentElement;
            if (parentLi.classList.contains('disabled') || parentLi.classList.contains('active')) {
                return;
            }
            const page = e.target.getAttribute('data-page');
            if (page) {
                performSearch(page);
            }
        }
    });

    // --- 2. LOGIKA BULK DELETE & CHECKBOX ---
    
    function updateBulkBtn() {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        if (selectedCountSpan) selectedCountSpan.textContent = checked;
        
        if (bulkDeleteBtn) {
            bulkDeleteBtn.style.display = checked > 0 ? 'inline-block' : 'none';
        }
    }

    // Event: Select All
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkBtn();
        });
    }

    // Event: Checkbox Baris (Delegation)
    tableBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateBulkBtn();
            // Update Select All status
            const total = document.querySelectorAll('.row-checkbox').length;
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            if(selectAllCheckbox) selectAllCheckbox.checked = (total === checked && total > 0);
        }
    });

    // Event: Klik Tombol Hapus Masal
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(checked).map(cb => cb.value);

            if (ids.length === 0) return;

            Swal.fire({
                title: `Hapus ${ids.length} Barang?`,
                text: "Data yang dihapus (termasuk stok) tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({title: 'Memproses...', didOpen: () => Swal.showLoading()});
                    
                    fetch(url, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ids: ids})
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            Swal.fire('Berhasil', data.message, 'success').then(() => performSearch(currentPage)); // Refresh tabel
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                    });
                }
            });
        });
    }

    // --- 3. LOGIKA HAPUS SATUAN (Fix Tombol Aksi) ---
    // Kita gunakan Event Delegation karena tabel bisa di-render ulang oleh AJAX
    tableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete')) {
            e.preventDefault();
            const deleteUrl = e.target.getAttribute('data-url');

            Swal.fire({
                title: 'Hapus Barang Ini?',
                text: "Data tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        }
    });

});
</script>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>