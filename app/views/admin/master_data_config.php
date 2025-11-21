<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="<?php echo BASE_URL; ?>admin/barang" class="btn" style="background: #6c757d; color: white;">
                &larr; Kembali ke Barang
            </a>
            <h1>Konfigurasi Data Master</h1>
        </div>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="tab-nav">
        <a href="#tab-kategori" class="tab-nav-link active">Kategori</a>
        <a href="#tab-merek" class="tab-nav-link">Merek</a>
        <a href="#tab-satuan" class="tab-nav-link">Satuan</a>
        <a href="#tab-status" class="tab-nav-link">Status Barang</a>
        <a href="#tab-lokasi" class="tab-nav-link">Lokasi / Rak</a>
        <a href="#tab-supplier" class="tab-nav-link">Supplier</a>
    </div>

    <div class="tab-content">
        
        <!-- 1. TAB KATEGORI -->
        <div id="view-kategori" class="tab-pane active">
            <div style="margin-bottom: 15px; display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                    <a href="<?php echo BASE_URL; ?>admin/addKategori" class="btn btn-primary">+ Tambah Kategori</a>
                    <button type="button" class="btn btn-danger btn-bulk-tab" style="display: none;" 
                            data-url="<?php echo BASE_URL; ?>admin/deleteBulkKategori">
                        Hapus Terpilih
                    </button>
                </div>
                <!-- Search Bar Kategori -->
                <input type="text" class="search-tab-input" placeholder="Cari Kategori..." 
                       style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
            </div>
            <div class="content-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;">
                                <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                            </th>
                            <th>ID</th><th>Nama Kategori</th><th>Deskripsi</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['kategori'] as $row) : ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['kategori_id']; ?>" style="transform: scale(1.2);">
                            </td>
                            <td><?php echo $row['kategori_id']; ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/editKategori/<?php echo $row['kategori_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteKategori/<?php echo $row['kategori_id']; ?>">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. TAB MEREK -->
        <div id="view-merek" class="tab-pane">
            <div style="margin-bottom: 15px; display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                    <a href="<?php echo BASE_URL; ?>admin/addMerek" class="btn btn-primary">+ Tambah Merek</a>
                    <button type="button" class="btn btn-danger btn-bulk-tab" style="display: none;" 
                            data-url="<?php echo BASE_URL; ?>admin/deleteBulkMerek">
                        Hapus Terpilih
                    </button>
                </div>
                <!-- Search Bar Merek -->
                <input type="text" class="search-tab-input" placeholder="Cari Merek..." 
                       style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
            </div>
            <div class="content-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;">
                                <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                            </th>
                            <th>ID</th><th>Nama Merek</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['merek'] as $row) : ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['merek_id']; ?>" style="transform: scale(1.2);">
                            </td>
                            <td><?php echo $row['merek_id']; ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['nama_merek']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/editMerek/<?php echo $row['merek_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteMerek/<?php echo $row['merek_id']; ?>">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. TAB SATUAN -->
        <div id="view-satuan" class="tab-pane">
            <div style="margin-bottom: 15px; display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                    <a href="<?php echo BASE_URL; ?>admin/addSatuan" class="btn btn-primary">+ Tambah Satuan</a>
                    <button type="button" class="btn btn-danger btn-bulk-tab" style="display: none;" 
                            data-url="<?php echo BASE_URL; ?>admin/deleteBulkSatuan">
                        Hapus Terpilih
                    </button>
                </div>
                <!-- Search Bar Satuan -->
                <input type="text" class="search-tab-input" placeholder="Cari Satuan..." 
                       style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
            </div>
            <div class="content-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;">
                                <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                            </th>
                            <th>ID</th><th>Nama Satuan</th><th>Singkatan</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['satuan'] as $row) : ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['satuan_id']; ?>" style="transform: scale(1.2);">
                            </td>
                            <td><?php echo $row['satuan_id']; ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['nama_satuan']); ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['singkatan']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/editSatuan/<?php echo $row['satuan_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteSatuan/<?php echo $row['satuan_id']; ?>">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. TAB STATUS -->
        <div id="view-status" class="tab-pane">
            <div style="margin-bottom: 15px; display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                    <a href="<?php echo BASE_URL; ?>admin/addStatus" class="btn btn-primary">+ Tambah Status</a>
                    <button type="button" class="btn btn-danger btn-bulk-tab" style="display: none;" 
                            data-url="<?php echo BASE_URL; ?>admin/deleteBulkStatus">
                        Hapus Terpilih
                    </button>
                </div>
                <!-- Search Bar Status -->
                <input type="text" class="search-tab-input" placeholder="Cari Status..." 
                       style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
            </div>
            <div class="content-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;">
                                <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                            </th>
                            <th>ID</th><th>Status</th><th>Deskripsi</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['status'] as $row) : ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['status_id']; ?>" style="transform: scale(1.2);">
                            </td>
                            <td><?php echo $row['status_id']; ?></td>
                            <td class="searchable">
                                <?php echo htmlspecialchars($row['nama_status']); ?>
                                <?php if($row['nama_status'] == 'Tersedia' || $row['nama_status'] == 'Rusak'): ?>
                                    <small style="color:red; display:block;">(System)</small>
                                <?php endif; ?>
                            </td>
                            <td class="searchable"><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/editStatus/<?php echo $row['status_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <?php if($row['nama_status'] != 'Tersedia'): ?>
                                    <button class="btn btn-danger btn-sm btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteStatus/<?php echo $row['status_id']; ?>">Hapus</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. TAB LOKASI -->
        <div id="view-lokasi" class="tab-pane">
            <div style="margin-bottom: 15px; display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                    <a href="<?php echo BASE_URL; ?>admin/addLokasi" class="btn btn-primary">+ Tambah Lokasi</a>
                    <button type="button" class="btn btn-danger btn-bulk-tab" style="display: none;" 
                            data-url="<?php echo BASE_URL; ?>admin/deleteBulkLokasi">
                        Hapus Terpilih
                    </button>
                </div>
                <!-- Search Bar Lokasi -->
                <input type="text" class="search-tab-input" placeholder="Cari Kode / Rak..." 
                       style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
            </div>
            <div class="content-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;">
                                <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                            </th>
                            <th>Kode</th><th>Nama Rak</th><th>Zona</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['lokasi'] as $row) : ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['lokasi_id']; ?>" style="transform: scale(1.2);">
                            </td>
                            <td class="searchable"><?php echo htmlspecialchars($row['kode_lokasi']); ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['nama_rak']); ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['zona']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/editLokasi/<?php echo $row['lokasi_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteLokasi/<?php echo $row['lokasi_id']; ?>">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. TAB SUPPLIER -->
        <div id="view-supplier" class="tab-pane">
            <div style="margin-bottom: 15px; display:flex; justify-content:space-between; align-items:center; gap: 10px;">
                <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                    <a href="<?php echo BASE_URL; ?>admin/addSupplier" class="btn btn-primary">+ Tambah Supplier</a>
                    <button type="button" class="btn btn-danger btn-bulk-tab" style="display: none;" 
                            data-url="<?php echo BASE_URL; ?>admin/deleteBulkSupplier">
                        Hapus Terpilih
                    </button>
                </div>
                <!-- Search Bar Supplier -->
                <input type="text" class="search-tab-input" placeholder="Cari Supplier / Kontak..." 
                       style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
            </div>
            <div class="content-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="text-align:center; width: 40px;">
                                <input type="checkbox" class="select-all-tab" style="transform: scale(1.2);">
                            </th>
                            <th>Nama Supplier</th><th>Kontak</th><th>Telepon</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['suppliers'] as $row) : ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="row-checkbox-tab" value="<?php echo $row['supplier_id']; ?>" style="transform: scale(1.2);">
                            </td>
                            <td class="searchable"><?php echo htmlspecialchars($row['nama_supplier']); ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['kontak_person']); ?></td>
                            <td class="searchable"><?php echo htmlspecialchars($row['telepon']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/editSupplier/<?php echo $row['supplier_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm btn-delete" data-url="<?php echo BASE_URL; ?>admin/deleteSupplier/<?php echo $row['supplier_id']; ?>">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Logic Tabulasi (Mencegah Scroll Jump)
    const tabLinks = document.querySelectorAll('.tab-nav-link');
    const tabPanes = document.querySelectorAll('.tab-pane');

    function activateTab(hash) {
        tabLinks.forEach(l => l.classList.remove('active'));
        tabPanes.forEach(p => p.classList.remove('active'));

        const activeLink = document.querySelector(`.tab-nav-link[href="${hash}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
            const targetId = hash.replace('#tab-', '#view-');
            const targetPane = document.querySelector(targetId);
            if (targetPane) targetPane.classList.add('active');
        } else {
            if(tabLinks.length > 0) {
                tabLinks[0].classList.add('active');
                tabPanes[0].classList.add('active');
            }
        }
        document.querySelector('.app-content').scrollTop = 0;
    }

    if(window.location.hash) activateTab(window.location.hash);
    else activateTab('#tab-kategori');

    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const hash = this.getAttribute('href');
            history.replaceState(null, null, hash);
            activateTab(hash);
        });
    });

    // 2. LOGIKA BULK DELETE KHUSUS HALAMAN TABULASI
    
    document.querySelectorAll('.select-all-tab').forEach(selectAll => {
        selectAll.addEventListener('change', function() {
            const table = this.closest('table');
            const checkboxes = table.querySelectorAll('.row-checkbox-tab');
            // Hanya centang yang VISIBLE (yang tidak disembunyikan oleh pencarian)
            checkboxes.forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = this.checked;
                }
            });
            toggleBulkBtn(this);
        });
    });

    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.addEventListener('change', function(e) {
            if(e.target.classList.contains('row-checkbox-tab')) {
                toggleBulkBtn(e.target);
            }
        });
    });

    function toggleBulkBtn(element) {
        const tabPane = element.closest('.tab-pane');
        const btn = tabPane.querySelector('.btn-bulk-tab');
        const checked = tabPane.querySelectorAll('.row-checkbox-tab:checked').length;
        
        if(checked > 0) {
            btn.style.display = 'inline-block';
            btn.textContent = `Hapus Terpilih (${checked})`;
        } else {
            btn.style.display = 'none';
        }
    }

    document.querySelectorAll('.btn-bulk-tab').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabPane = this.closest('.tab-pane');
            const checked = tabPane.querySelectorAll('.row-checkbox-tab:checked');
            const ids = Array.from(checked).map(cb => cb.value);
            const url = this.getAttribute('data-url');

            if (ids.length === 0) return;

            Swal.fire({
                title: `Hapus ${ids.length} Data?`,
                text: "Data yang dihapus tidak bisa dikembalikan!",
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
                            Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
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
    });

    // 3. LOGIKA PENCARIAN REAL-TIME DI SETIAP TAB
    document.querySelectorAll('.search-tab-input').forEach(input => {
        input.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const tabPane = this.closest('.tab-pane');
            const tableRows = tabPane.querySelectorAll('.data-table tbody tr');

            tableRows.forEach(row => {
                let textContent = "";
                // Ambil teks dari kolom yang punya class 'searchable'
                row.querySelectorAll('.searchable').forEach(col => {
                    textContent += col.textContent.toLowerCase() + " ";
                });

                if (textContent.includes(searchText)) {
                    row.style.display = ""; // Tampilkan
                } else {
                    row.style.display = "none"; // Sembunyikan
                    // Uncheck checkbox baris yang disembunyikan
                    const checkbox = row.querySelector('.row-checkbox-tab');
                    if(checkbox) checkbox.checked = false; 
                }
            });
            
            // Update tombol hapus karena jumlah checkbox berubah
            toggleBulkBtn(this);
        });
    });
});
</script>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>