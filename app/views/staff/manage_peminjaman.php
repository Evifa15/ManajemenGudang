<?php
    require_once APPROOT . '/views/templates/header.php';
    require_once APPROOT . '/views/templates/sidebar_staff.php';
?>

<main class="app-content">
    
    <div class="content-header">
        <h1>Manajemen Peminjaman Barang</h1>
    </div>

    <?php
        if(isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo '<div class="flash-message ' . $flash['type'] . '">' . $flash['text'] . '</div>';
            unset($_SESSION['flash_message']);
        }
    ?>

    <div class="tab-nav">
        <a href="#tab-baru" class="tab-nav-link active">Permintaan Baru (<?php echo count($data['permintaan_baru']); ?>)</a>
        <a href="#tab-disetujui" class="tab-nav-link">Siap Diambil (<?php echo count($data['disetujui']); ?>)</a>
        <a href="#tab-dipinjam" class="tab-nav-link">Sedang Dipinjam (<?php echo count($data['sedang_dipinjam']); ?>)</a>
        <a href="#tab-riwayat" class="tab-nav-link">Riwayat (<?php echo count($data['riwayat']); ?>)</a>
    </div>

    <div class="tab-content">
        
        <div id="tab-baru" class="tab-pane active">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tgl. Pengajuan</th>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th>Rencana Pinjam</th>
                            <th>Rencana Kembali</th>
                            <th>Alasan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['permintaan_baru'] as $req) : ?>
                        <tr>
                            <td><?php echo date('d-m-Y H:i', strtotime($req['tgl_pengajuan'])); ?></td>
                            <td><?php echo htmlspecialchars($req['nama_peminjam']); ?></td>
                            <td><?php echo htmlspecialchars($req['nama_barang']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($req['tgl_rencana_pinjam'])); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($req['tgl_rencana_kembali'])); ?></td>
                            <td><?php echo htmlspecialchars($req['alasan_pinjam']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>staff/approveLoan/<?php echo $req['peminjaman_id']; ?>" class="btn btn-primary btn-sm">Setujui</a>
                                <button type="button" class="btn btn-danger btn-sm btn-reject" data-id="<?php echo $req['peminjaman_id']; ?>">Tolak</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-disetujui" class="tab-pane">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th>Rencana Pinjam</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['disetujui'] as $req) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['nama_peminjam']); ?></td>
                            <td><?php echo htmlspecialchars($req['nama_barang']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($req['tgl_rencana_pinjam'])); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>staff/handoverLoan/<?php echo $req['peminjaman_id']; ?>" class="btn btn-primary btn-sm">Catat Serahkan Barang</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-dipinjam" class="tab-pane">
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th>Rencana Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php foreach ($data['sedang_dipinjam'] as $req) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['nama_peminjam']); ?></td>
                            <td><?php echo htmlspecialchars($req['nama_barang']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($req['tgl_rencana_kembali'])); ?></td>
                            <td>
                                <?php if($req['status_pinjam'] == 'Jatuh Tempo'): ?>
                                    <span style="color: red; font-weight: bold;">JATUH TEMPO</span>
                                <?php else: ?>
                                    Sedang Dipinjam
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>staff/returnLoan/<?php echo $req['peminjaman_id']; ?>" class="btn btn-primary btn-sm">Catat Pengembalian</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-riwayat" class="tab-pane">
            <div class="content-table">
                 <table>
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php foreach ($data['riwayat'] as $req) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['nama_peminjam']); ?></td>
                            <td><?php echo htmlspecialchars($req['nama_barang']); ?></td>
                            <td><?php echo htmlspecialchars($req['status_pinjam']); ?></td>
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
                
                // Hapus 'active' dari semua link dan pane
                tabLinks.forEach(l => l.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));

                // Tambah 'active' ke link yang diklik
                this.classList.add('active');
                
                // Tampilkan pane yang sesuai
                const targetPane = document.querySelector(this.getAttribute('href'));
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });

        // Logika untuk tombol "Tolak" (SweetAlert Input)
        const rejectButtons = document.querySelectorAll('.btn-reject');
        rejectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const peminjamanId = this.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Tolak Peminjaman',
                    text: 'Masukkan alasan penolakan (wajib):',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Tolak',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Anda harus mengisi alasan penolakan!'
                        }
                    },
                    preConfirm: (alasan) => {
                        // Kirim data ke server menggunakan form tersembunyi
                        // Ini lebih aman daripada GET
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '<?php echo BASE_URL; ?>staff/rejectLoan';

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'peminjaman_id';
                        idInput.value = peminjamanId;
                        form.appendChild(idInput);

                        const alasanInput = document.createElement('input');
                        alasanInput.type = 'hidden';
                        alasanInput.name = 'alasan_penolakan';
                        alasanInput.value = alasan;
                        form.appendChild(alasanInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>

<?php
    require_once APPROOT . '/views/templates/footer.php';
?>