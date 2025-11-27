<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';

    // Ambil data produk JSON (Hanya dirender jika di workspace)
    $productsJson = !empty($data['products']) ? json_encode($data['products']) : '[]';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Input Stock Opname</h1>
    </div>

    <?php
        // Menampilkan Flash Message
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>
    
    <?php if (!$data['isOpnameActive']): ?>
        <div class="flash-message error" style="text-align: center; font-size: 1.2em; padding: 40px; background: #e9ecef; color: #495057; border: 1px solid #ced4da;">
            💤 Tidak ada periode **Stock Opname** yang sedang aktif. <br>
            Silakan lakukan pekerjaan rutin lainnya.
        </div>

    <?php elseif ($data['viewState'] == 'lobby'): ?>
        
        <div class="flash-message success" style="text-align: center; border: 2px solid #c3e6cb; margin-bottom: 30px;">
            <h3>🔔 PERIODE OPNAME AKTIF (<?php echo $data['activePeriod']['nomor_sp']; ?>)</h3>
            <p>Silakan ambil tugas perhitungan berdasarkan kategori di bawah ini.</p>
            <?php if($data['activePeriod']['catatan_admin']): ?>
                <small style="display:block; margin-top:5px; color: #856404; background:#fff3cd; padding:5px;">
                    Catatan Admin: "<?php echo htmlspecialchars($data['activePeriod']['catatan_admin']); ?>"
                </small>
            <?php endif; ?>
        </div>

        <div class="content-table" style="border: 2px solid #007bff; margin-bottom: 30px;">
            <h3 style="background: #007bff; color: white; padding: 10px; margin: 0;">📂 Tugas Saya (Sedang Dikerjakan)</h3>
            <div style="padding: 10px;">
                <?php if (empty($data['myTasks'])): ?>
                    <p style="text-align: center; color: #666; padding: 20px;">Belum ada tugas yang Anda ambil. Silakan pilih dari daftar di bawah.</p>
                <?php else: ?>
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Waktu Mulai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['myTasks'] as $task): ?>
                            <tr style="background: #e8f4fd;">
                                <td><strong><?php echo htmlspecialchars($task['nama_kategori']); ?></strong></td>
                                <td><?php echo date('H:i', strtotime($task['waktu_mulai'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>staff/inputOpname/<?php echo $task['task_id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                       ✏️ Lanjutkan Input
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-table">
            <h3>📋 Daftar Semua Tugas (Available)</h3>
            <table style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Kategori Barang</th>
                        <th>Status</th>
                        <th>Diambil Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['availableTasks'] as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['nama_kategori']); ?></td>
                        <td>
                            <?php if($task['status_task'] == 'Pending'): ?>
                                <span style="color: orange;">Menunggu</span>
                            <?php elseif($task['status_task'] == 'In Progress'): ?>
                                <span style="color: blue;">Proses</span>
                            <?php else: ?>
                                <span style="color: green;">Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($task['staff_name'] ?? '-'); ?></td>
                        <td>
                            <?php if($task['status_task'] == 'Pending'): ?>
                                <a href="<?php echo BASE_URL; ?>staff/claimTask/<?php echo $task['task_id']; ?>" 
                                   class="btn btn-success btn-sm">
                                   ✋ Ambil Tugas Ini
                                </a>
                            <?php elseif($task['assigned_to_user_id'] == $_SESSION['user_id'] && $task['status_task'] == 'In Progress'): ?>
                                <span style="color: #007bff; font-weight: bold;">(Ada di Tugas Saya)</span>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled>Terkunci</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($data['viewState'] == 'workspace'): ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <a href="<?php echo BASE_URL; ?>staff/inputOpname" class="btn btn-secondary">&larr; Kembali ke Lobi</a>
            <h2 style="color: #004085;">Kategori: <?php echo htmlspecialchars($data['currentTask']['nama_kategori']); ?></h2>
        </div>

        <div style="background: #e8f4fd; padding: 20px; border-radius: 8px; border: 1px solid #b8daff; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin-bottom: 5px;"><strong>Mode Pengerjaan</strong></p>
                <p style="font-size: 0.9em;">Silakan hitung dan input semua barang di kategori ini.</p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>staff/submitTask/<?php echo $data['currentTask']['task_id']; ?>" 
                   class="btn btn-success" 
                   onclick="return confirm('Yakin sudah selesai menghitung SEMUA barang di kategori ini? Tugas akan dikunci dan diserahkan ke Admin.');">
                   ✅ Selesai & Submit Kategori Ini
                </a>
            </div>
        </div>

        <div class="form-container">
            <form action="<?php echo BASE_URL; ?>staff/processInputOpname" method="POST">
                <input type="hidden" name="period_id" value="<?php echo $data['activePeriod']['period_id']; ?>">
                
                <fieldset>
                    <legend>Pencatatan Hitungan Fisik</legend>
                    
                    <div class="form-group">
                        <label for="product_id">Pilih Barang (Hanya Kategori <?php echo htmlspecialchars($data['currentTask']['nama_kategori']); ?>)</label>
                        <select id="product_id" name="product_id" required class="form-control" style="padding: 10px; font-size: 1.1em;">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach($data['products'] as $prod): ?>
                                <option value="<?php echo $prod['product_id']; ?>" data-lacak_lot="<?php echo $prod['lacak_lot_serial']; ?>">
                                    <?php echo $prod['kode_barang'] . ' - ' . $prod['nama_barang']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="lot_tracking_fields" class="form-group" style="display: none;">
                        <label for="lot_number">Nomor Lot / Batch</label>
                        <input type="text" id="lot_number" name="lot_number" placeholder="Wajib diisi jika barang dilacak">
                    </div>

                    <div class="form-group">
                        <label for="stok_fisik">Jumlah Hitungan Fisik (Wajib)</label>
                        <input type="number" id="stok_fisik" name="stok_fisik" min="0" required placeholder="Masukkan jumlah yang ADA di rak." style="font-size: 1.2em; font-weight: bold;">
                    </div>

                    <div class="form-group">
                        <label for="catatan">Catatan (Opsional)</label>
                        <textarea id="catatan" name="catatan" rows="2" placeholder="Contoh: Barang rusak 2 pcs, salah lokasi, dll."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">💾 Simpan Hitungan</button>
                    </div>

                </fieldset>
            </form>
        </div>

        <div class="content-table" style="margin-top: 30px;">
            <h3 style="border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 15px;">
                📋 Checklist Pengerjaan: <?php echo htmlspecialchars($data['currentTask']['nama_kategori']); ?>
            </h3>
            
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Input Anda</th>
                        <th>Waktu Input</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Mapping data inputan agar mudah dicari
                    $entriesMap = [];
                    if (!empty($data['myEntries'])) {
                        foreach ($data['myEntries'] as $entry) {
                            // Kunci array pakai product_id agar unik
                            // (Jika ada multiple lot untuk 1 produk, ini hanya ambil yang terakhir, 
                            //  tapi cukup untuk indikator "Sudah/Belum")
                            $entriesMap[$entry['product_id']] = $entry;
                        }
                    }

                    $no = 1;
                    $countSudah = 0;
                    
                    foreach ($data['products'] as $prod): 
                        // Cek apakah barang ini sudah diinput?
                        $isDone = isset($entriesMap[$prod['product_id']]);
                        $entryData = $isDone ? $entriesMap[$prod['product_id']] : null;
                        
                        if ($isDone) $countSudah++;
                    ?>
                    <tr style="<?php echo $isDone ? 'background-color: #e6fffa;' : ''; ?>">
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($prod['kode_barang']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($prod['nama_barang']); ?>
                            <?php if($prod['lacak_lot_serial']): ?>
                                <br><small style="color:blue;">(Item Lot/Batch)</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isDone): ?>
                                <strong style="font-size: 1.1em; color: #007bff;"><?php echo $entryData['stok_fisik']; ?></strong>
                                <?php if(!empty($entryData['lot_number'])): ?>
                                    <br><small>Lot: <?php echo htmlspecialchars($entryData['lot_number']); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #ccc;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $isDone ? date('H:i', strtotime($entryData['created_at'])) : '-'; ?>
                        </td>
                        <td>
                            <?php if ($isDone): ?>
                                <span style="color: green; font-weight: bold;">✔ Sudah</span>
                            <?php else: ?>
                                <span style="color: #999;">Belum</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 15px; background: #eee; height: 20px; border-radius: 10px; overflow: hidden;">
                <?php 
                    $totalItems = count($data['products']);
                    $persen = ($totalItems > 0) ? ($countSudah / $totalItems) * 100 : 0;
                ?>
                <div style="background: #28a745; width: <?php echo $persen; ?>%; height: 100%;"></div>
            </div>
            <p style="text-align: right; margin-top: 5px;">
                <strong><?php echo $countSudah; ?></strong> dari <strong><?php echo $totalItems; ?></strong> barang telah dihitung.
            </p>

        </div>
        
        <script>
            const productsData = <?php echo $productsJson; ?>;
            const productSelect = document.getElementById('product_id');
            const lotFields = document.getElementById('lot_tracking_fields');
            const lotNumberInput = document.getElementById('lot_number');

            productSelect.addEventListener('change', function() {
                const selectedProductId = this.value;
                // Cari data produk di JSON
                const selectedProduct = productsData.find(p => String(p.product_id) === selectedProductId);

                if (selectedProduct && selectedProduct.lacak_lot_serial == "1") {
                    lotFields.style.display = 'block'; 
                    lotNumberInput.required = true;    
                } else {
                    lotFields.style.display = 'none';  
                    lotNumberInput.required = false;   
                    lotNumberInput.value = '';         
                }
            });
        </script>

    <?php endif; ?>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>