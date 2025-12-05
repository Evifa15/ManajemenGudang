<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Barang</title>
    <style>
        /* Reset & Base */
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        
        /* Header Laporan */
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .header h2 { margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; color: #152e4d; }
        .header p { margin: 0; font-size: 12px; color: #666; }

        /* Tabel Data */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #152e4d; color: #fff; padding: 8px; text-align: left; font-weight: bold; font-size: 11px; }
        td { border-bottom: 1px solid #ddd; padding: 8px; font-size: 11px; vertical-align: middle; }
        
        /* Zebra Striping */
        tr:nth-child(even) { background-color: #f9f9f9; }

        /* Utility */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; border: 1px solid #ccc; }
        
        /* Status Colors (Optional) */
        .status-aman { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .status-bahaya { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
    </style>
</head>
<body>

    <div id="area-print">
        
        <div class="header">
            <h2>Laporan Stok Gudang</h2>
            <p>Dicetak pada: <?php echo $data['tanggal']; ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align:center;">No</th>
                    <th style="width: 12%;">Kode</th>
                    <th style="width: 20%;">Nama Barang</th>
                    <th style="width: 15%;">Kategori</th>
                    <th style="width: 13%;">Merek</th>
                    <th style="width: 15%;">Lokasi</th>
                    <th style="width: 10%; text-align:center;">Stok</th>
                    <th style="width: 10%;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(empty($data['barang'])): ?>
                    <tr><td colspan="8" class="text-center">Tidak ada data yang sesuai filter.</td></tr>
                <?php else: 
                    $no = 1;
                    foreach($data['barang'] as $row): 
                ?>
                    <tr>
                        <td class="text-center"><?php echo $no++; ?></td>
                        <td><strong><?php echo $row['kode_barang']; ?></strong></td>
                        <td><?php echo $row['nama_barang']; ?></td>
                        <td><?php echo $row['nama_kategori']; ?></td>
                        <td><?php echo $row['nama_merek']; ?></td>
                        <td><?php echo $row['kode_lokasi']; ?></td>
                        <td class="text-center" style="font-weight: bold; font-size:12px;"><?php echo $row['stok_total']; ?></td>
                        <td><?php echo $row['nama_satuan']; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; text-align: right; font-size: 10px; color: #888;">
            <p>Dokumen ini digenerate otomatis oleh sistem.</p>
        </div>

    </div>

</body>
</html>