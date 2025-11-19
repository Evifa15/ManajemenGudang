<?php
    // 1. Panggil Header
    require_once APPROOT . '/views/templates/header.php';
    // 2. Panggil Sidebar KHUSUS PEMILIK
    require_once APPROOT . '/views/templates/sidebar_pemilik.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Laporan & Analitik Transaksi (Read-Only)</h1>
        <div>
            <button class="btn btn-primary" onclick="window.print();">Cetak Laporan</button>
        </div>
    </div>

    <div class="tab-nav">
        <a href="#tab-analitik" class="tab-nav-link active">Analitik & Grafik</a>
        <a href="#tab-masuk" class="tab-nav-link">Laporan Barang Masuk</a>
        <a href="#tab-keluar" class="tab-nav-link">Laporan Barang Keluar</a>
        <a href="#tab-rusak" class="tab-nav-link">Laporan Barang Rusak/Retur</a>
    </div>

    <div class="tab-content">
        
        <div id="tab-analitik" class="tab-pane active">
            <div class="widget">
                <h3>(Dalam Pengembangan)</h3>
                <p>Area ini akan menampilkan grafik Chart.js untuk data *Fast Moving* dan *Slow Moving*.</p>
                </div>
        </div>

        <div id="tab-masuk" class="tab-pane">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal Input</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Supplier</th>
                            <th>Diinput oleh (Staff)</th>
                            <th>Lot/Batch</th>
                            <th>Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['riwayat_masuk'] as $his) : ?>
                        <tr>
                            <td><?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($his['nama_barang']); ?></td>
                            <td><strong><?php echo (int)$his['jumlah']; ?></strong></td> 
                            <td><?php echo htmlspecialchars($his['nama_supplier']); ?></td>
                            <td><?php echo htmlspecialchars($his['staff_nama']); ?></td>
                            <td><?php echo htmlspecialchars($his['lot_number']); ?></td>
                            <td>
                                <?php if($his['bukti_foto']): ?>
                                    <a href="<?php echo BASE_URL . 'uploads/bukti_transaksi/' . $his['bukti_foto']; ?>" target="_blank" class="btn btn-primary btn-sm">Lihat</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-keluar" class="tab-pane">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal Input</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Tujuan / Keterangan</th>
                            <th>Diambil oleh (Staff)</th>
                            <th>Lot/Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['riwayat_keluar'] as $his) : ?>
                        <tr>
                            <td><?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($his['nama_barang']); ?></td>
                            <td><strong><?php echo (int)$his['jumlah']; ?></strong></td> 
                            <td><?php echo htmlspecialchars($his['keterangan']); ?></td>
                            <td><?php echo htmlspecialchars($his['staff_nama']); ?></td>
                            <td><?php echo htmlspecialchars($his['lot_number']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-rusak" class="tab-pane">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal Lapor</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Status Baru</th>
                            <th>Dilaporkan oleh (Staff)</th>
                            <th>Lot/Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['riwayat_rusak'] as $his) : ?>
                        <tr>
                            <td><?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($his['nama_barang']); ?></td>
                            <td><strong><?php echo (int)$his['jumlah']; ?></strong></td> 
                            <td><?php echo htmlspecialchars($his['nama_status']); ?></td>
                            <td><?php echo htmlspecialchars($his['staff_nama']); ?></td>
                            <td><?php echo htmlspecialchars($his['lot_number']); ?></td>
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
        const tabLinks = document.querySelectorAll('.tab-nav-link');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                tabLinks.forEach(l => l.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                const targetPane = document.querySelector(this.getAttribute('href'));
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
    });
</script>

<?php
    // 3. Panggil Footer
    require_once APPROOT . '/views/templates/footer.php';
?>