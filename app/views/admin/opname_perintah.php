<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';

    $activePeriod = $data['activePeriod'];
    $report = $data['reconciliationReport'];
?>

<style>
    .opname-header-card {
        background: #fff;
        border-left: 5px solid #007bff;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        border-radius: 5px;
    }
    .task-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .task-status {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.85em;
        font-weight: bold;
    }
    .status-pending { background: #e2e3e5; color: #383d41; }
    .status-progress { background: #cce5ff; color: #004085; }
    .status-submitted { background: #d4edda; color: #155724; }
    
    .opname-status-box {
        padding: 40px;
        margin-top: 20px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid transparent;
        margin-bottom: 30px; 
    }
    .opname-status-box h2, .opname-status-box h3 { margin-bottom: 10px; }
    .opname-status-box p { margin-bottom: 20px; font-size: 1.1em; }
</style>

<main class="app-content" id="opnamePerintahPage" data-base-url="<?php echo BASE_URL; ?>">
    
    <div class="content-header">
        <h1>Perintah Stock Opname (Aktif)</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <?php if (!$activePeriod): ?>
        
        <div class="form-container" style="border-top: 5px solid #007bff;">
            <form action="<?php echo BASE_URL; ?>admin/startNewOpname" method="POST">
                <fieldset>
                    <legend>📝 Buat Surat Perintah Stock Opname (SP-SO)</legend>
                    
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Nomor Surat Perintah (SP)</label>
                            <input type="text" name="nomor_sp" placeholder="Masukkan Nomor SP (Misal: SP/SO/001)" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Target / Deadline Selesai</label>
                            <input type="datetime-local" name="target_selesai" value="<?php echo date('Y-m-d\TH:i', strtotime('+1 day')); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Instruksi / Catatan Khusus untuk Staff</label>
                        <textarea name="catatan_admin" rows="2" placeholder="Contoh: Fokus hitung barang reject di rak belakang juga."></textarea>
                    </div>

                    <div class="form-group">
                        <label style="margin-bottom: 10px; display:block;">Pilih Lingkup (Scope) Kategori:</label>
                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ccc; border-radius: 5px;">
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; background: #f9f9f9;">
                                <label style="font-weight: bold; cursor: pointer; flex-grow: 1;">
                                    <input type="checkbox" id="checkAll" name="kategori_ids[]" value="ALL" checked style="transform: scale(1.2); margin-right: 8px;"> 
                                    Pilih Semua Kategori
                                </label>
                            </div>

                            <?php foreach($data['allKategori'] as $kat): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee;">
                                    
                                    <label style="cursor: pointer; flex-grow: 1; display: flex; align-items: center;">
                                        <input type="checkbox" class="cat-check" name="kategori_ids[]" value="<?php echo $kat['kategori_id']; ?>" checked style="transform: scale(1.2); margin-right: 10px;"> 
                                        <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                                    </label>

                                    <button type="button" class="btn btn-sm btn-info btn-detail-cat" 
                                            data-id="<?php echo $kat['kategori_id']; ?>" 
                                            data-nama="<?php echo htmlspecialchars($kat['nama_kategori']); ?>"
                                            style="background: #17a2b8; border:none; color:white; border-radius: 4px; padding: 2px 8px; font-size: 0.8em; cursor:pointer;"
                                            title="Lihat daftar barang di kategori ini">
                                        👁️ Detail
                                    </button>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1em;">
                            🚀 Terbitkan Surat Perintah & Mulai Opname
                        </button>
                    </div>
                </fieldset>
            </form>
        </div>

    <?php elseif ($activePeriod && $report == null): ?>
        
        <div class="opname-header-card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 style="color: #007bff; margin-bottom: 5px;"><?php echo htmlspecialchars($activePeriod['nomor_sp']); ?></h2>
                    <p><strong>Status:</strong> <span class="status-progress" style="font-size:1em;">SEDANG BERLANGSUNG</span></p>
                    <p><small>Mulai: <?php echo date('d M Y, H:i', strtotime($activePeriod['start_date'])); ?> | Oleh: <?php echo htmlspecialchars($activePeriod['admin_name']); ?></small></p>
                    <?php if($activePeriod['catatan_admin']): ?>
                        <div style="background: #fff3cd; padding: 10px; margin-top: 10px; border-radius: 5px; border: 1px solid #ffeeba;">
                            <strong>Catatan:</strong> "<?php echo htmlspecialchars($activePeriod['catatan_admin']); ?>"
                        </div>
                    <?php endif; ?>
                </div>
                <div style="text-align: right;">
                    <a href="<?php echo BASE_URL; ?>admin/perintahOpname?view_report=1" class="btn btn-warning" style="margin-bottom: 5px;">
                        📋 Tarik Laporan & Finalisasi
                    </a>
                    <br><small>Klik jika semua tugas selesai.</small>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 15px;">Pantauan Progres Tugas (Per Kategori)</h3>
        <div class="task-list">
            <?php if(empty($data['taskProgress'])): ?>
                <p>Tidak ada data tugas.</p>
            <?php else: ?>
                <?php foreach($data['taskProgress'] as $task): ?>
                    <div class="task-card">
                        <div>
                            <strong><?php echo htmlspecialchars($task['nama_kategori']); ?></strong><br>
                            <small>
                                <?php if ($task['status_task'] == 'Pending') echo "Belum ada yang mengambil";
                                    else echo "Dikerjakan oleh: <strong>" . htmlspecialchars($task['staff_name']) . "</strong>"; ?>
                            </small>
                        </div>
                        <div class="task-status status-<?php echo strtolower(str_replace(' ', '-', $task['status_task'])); ?>">
                            <?php echo $task['status_task']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php elseif ($activePeriod && $report != null): ?>
        
        <div class="opname-status-box" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24;">
            <h3>⚠️ Tahap Rekonsiliasi & Finalisasi</h3>
            <p>SP: <strong><?php echo htmlspecialchars($activePeriod['nomor_sp']); ?></strong></p>
            <p>Periksa selisih di bawah ini sebelum menutup SP ini.</p>
        </div>
        
        <form action="<?php echo BASE_URL; ?>admin/finalizeOpname" method="POST">
            <div class="content-table" style="margin-top: 20px;">
                <table style="width: 100%;">
                    <thead style="position: sticky; top: 0; background: #eee; z-index: 10;">
                        <tr>
                            <th>Kode</th> <th>Nama Barang</th> <th style="text-align: center;">Stok Sistem</th> <th style="text-align: center;">Stok Fisik</th> <th style="text-align: center;">Selisih</th> <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report as $item) : 
                            $selisih = $item['selisih'];
                            $rowStyle = ($selisih != 0) ? 'background-color: #fff3cd;' : '';
                            $statusText = ($selisih != 0) ? "<strong style='color:red;'>$selisih</strong>" : '<span style="color:green;">✔</span>';
                        ?>
                        <tr style="<?php echo $rowStyle; ?>">
                            <td><?php echo htmlspecialchars($item['kode_barang']); ?></td>
                            <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                            <td style="text-align: center;"><?php echo $item['stok_sistem']; ?></td>
                            <td style="text-align: center; font-weight: bold;"><?php echo $item['stok_fisik']; ?></td>
                            <td style="text-align: center;"><?php echo $statusText; ?>
                                <input type="hidden" name="adjustment[<?php echo $item['product_id']; ?>]" value="<?php echo $selisih; ?>">
                            </td>
                            <td style="text-align: center; font-size: 0.9em;">
                                <?php echo ($selisih == 0) ? 'Aman' : 'Adjust'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="form-actions" style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-top: 1px solid #ddd; text-align: right;">
                <a href="<?php echo BASE_URL; ?>admin/perintahOpname" class="btn" style="background: #6c757d; color: white; margin-right: 10px;">&larr; Kembali Monitor</a>
                <button type="submit" class="btn btn-danger" onclick="return confirm('Tutup periode opname ini?');">✅ Setujui & Tutup SP</button>
            </div>
        </form>
    <?php endif; ?>
</main>

<?php require_once APPROOT . '/views/templates/footer.php'; ?>