<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_admin.php';
?>

<main class="app-content">
    <div class="content-header">
        <h1>Laporan & Analitik Transaksi</h1>
        <div>
            <button class="btn btn-primary" onclick="window.print();">🖨️ Cetak Laporan</button>
        </div>
    </div>

    <div class="tab-nav">
        <a href="#tab-analitik" class="tab-nav-link active">📊 Analitik & Grafik</a>
        <a href="#tab-masuk" class="tab-nav-link">📥 Barang Masuk</a>
        <a href="#tab-keluar" class="tab-nav-link">📤 Barang Keluar</a>
        <a href="#tab-rusak" class="tab-nav-link">⚠️ Barang Rusak/Retur</a>
    </div>

    <div class="tab-content">
        
        <div id="tab-analitik" class="tab-pane active">
            
            <div class="widget" style="margin-bottom: 20px; padding: 20px;">
                <h3>📈 Tren Pergerakan Barang (6 Bulan Terakhir)</h3>
                <div style="height: 300px;">
                    <canvas id="grafikAnalitik"
                            data-labels='<?php echo $data['grafik']['labels']; ?>'
                            data-masuk='<?php echo $data['grafik']['masuk']; ?>'
                            data-keluar='<?php echo $data['grafik']['keluar']; ?>'>
                    </canvas>
                </div>
            </div>

            <div class="widget" style="margin-bottom: 20px; padding: 20px;">
                 <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
                    📋 Rincian Arus Transaksi Bulanan
                 </h3>
                 <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; border-bottom: 2px solid #ddd; text-align: left;">Periode (Bulan)</th>
                            <th style="padding: 12px; border-bottom: 2px solid #ddd; text-align: center;">Total Barang Masuk</th>
                            <th style="padding: 12px; border-bottom: 2px solid #ddd; text-align: center;">Total Barang Keluar</th>
                            <th style="padding: 12px; border-bottom: 2px solid #ddd; text-align: center;">Net Flow (Selisih)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['grafik_raw'])): ?>
                            <tr><td colspan="4" style="text-align:center; padding:15px;">Belum ada data transaksi.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['grafik_raw'] as $row): 
                                $selisih = $row['total_masuk'] - $row['total_keluar'];
                                $color = $selisih >= 0 ? 'green' : 'red';
                                $sign = $selisih > 0 ? '+' : '';
                            ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;">
                                    <strong><?php echo date('F Y', strtotime($row['bulan'] . '-01')); ?></strong>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <span style="color: #007bff; font-weight: bold; background: #e8f4fd; padding: 4px 8px; border-radius: 4px;">
                                        ⬇ <?php echo $row['total_masuk']; ?>
                                    </span>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <span style="color: #dc3545; font-weight: bold; background: #f8d7da; padding: 4px 8px; border-radius: 4px;">
                                        ⬆ <?php echo $row['total_keluar']; ?>
                                    </span>
                                </td>
                                <td style="padding: 10px; text-align: center; font-weight: bold; color: <?php echo $color; ?>;">
                                    <?php echo $sign . $selisih; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                 </table>
            </div>

            <div class="dashboard-widgets" style="grid-template-columns: 1fr 1fr;">
                <div class="widget">
                    <h3 style="color: #28a745; border-bottom: 2px solid #28a745;">🔥 Top 5 Paling Laris (30 Hari)</h3>
                    <?php if(empty($data['fast_moving'])): ?>
                        <p style="padding: 20px; text-align: center; color: #666;">Belum ada data transaksi keluar.</p>
                    <?php else: ?>
                        <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
                            <tr style="background: #f0f0f0; font-weight: bold;">
                                <td style="padding: 8px;">Barang</td>
                                <td style="padding: 8px; text-align: right;">Terjual</td>
                            </tr>
                            <?php foreach($data['fast_moving'] as $item): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px;">
                                    <strong><?php echo htmlspecialchars($item['nama_barang']); ?></strong><br>
                                    <small style="color:#888;"><?php echo htmlspecialchars($item['kode_barang']); ?></small>
                                </td>
                                <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 1.1em;">
                                    <?php echo $item['total_keluar']; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="widget">
                    <h3 style="color: #dc3545; border-bottom: 2px solid #dc3545;">🧊 Top 5 Jarang Keluar (Ada Stok)</h3>
                    <?php if(empty($data['slow_moving'])): ?>
                        <p style="padding: 20px; text-align: center; color: #666;">Tidak ada barang 'mangkrak'.</p>
                    <?php else: ?>
                        <table style="width: 100%; margin-top: 10px; border-collapse: collapse;">
                            <tr style="background: #f0f0f0; font-weight: bold;">
                                <td style="padding: 8px;">Barang</td>
                                <td style="padding: 8px; text-align: center;">Keluar</td>
                                <td style="padding: 8px; text-align: right;">Sisa Stok</td>
                            </tr>
                            <?php foreach($data['slow_moving'] as $item): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px;">
                                    <strong><?php echo htmlspecialchars($item['nama_barang']); ?></strong><br>
                                    <small style="color:#888;"><?php echo htmlspecialchars($item['kode_barang']); ?></small>
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <?php echo $item['total_keluar']; ?>
                                </td>
                                <td style="padding: 8px; text-align: right; color: orange; font-weight: bold;">
                                    <?php echo $item['sisa_stok']; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="tab-masuk" class="tab-pane">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th> <th>Barang</th> <th>Jumlah</th> <th>Supplier</th> <th>Oleh</th>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding: 10px; text-align: center;">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatBarangMasuk" class="btn btn-sm btn-primary">Lihat Selengkapnya di Menu Transaksi &rarr;</a>
                </div>
            </div>
        </div>

        <div id="tab-keluar" class="tab-pane">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th> <th>Barang</th> <th>Jumlah</th> <th>Tujuan</th> <th>Oleh</th>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding: 10px; text-align: center;">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatBarangKeluar" class="btn btn-sm btn-primary">Lihat Selengkapnya di Menu Transaksi &rarr;</a>
                </div>
            </div>
        </div>

        <div id="tab-rusak" class="tab-pane">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th> <th>Barang</th> <th>Jumlah</th> <th>Status</th> <th>Ket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['riwayat_rusak'] as $his) : ?>
                        <tr>
                            <td><?php echo date('d-m-Y H:i', strtotime($his['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($his['nama_barang']); ?></td>
                            <td><strong><?php echo (int)$his['jumlah']; ?></strong></td> 
                            <td><?php echo htmlspecialchars($his['nama_status']); ?></td>
                            <td><?php echo htmlspecialchars($his['keterangan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                 <div style="padding: 10px; text-align: center;">
                    <a href="<?php echo BASE_URL; ?>admin/riwayatReturRusak" class="btn btn-sm btn-primary">Lihat Selengkapnya di Menu Transaksi &rarr;</a>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo BASE_URL; ?>js/main.js"></script>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>