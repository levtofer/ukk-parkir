<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('petugas');
require_once __DIR__ . '/../../includes/helper.php';

// Kendaraan yang masih di dalam (belum checkout), dibagi per halaman
$total_aktif = (int) $db->query("SELECT COUNT(*) FROM tb_parkir WHERE status = 'masuk'")->fetch_row()[0];
$pg_aktif = siapkan_paginasi($total_aktif, 15);
$aktif = $db->query("SELECT p.*, m.nama_member, a.nama_area
                      FROM tb_parkir p
                      LEFT JOIN tb_member m ON p.id_member = m.id_member
                      LEFT JOIN tb_area_parkir a ON p.id_area = a.id_area
                      WHERE p.status = 'masuk'
                      ORDER BY p.waktu_masuk DESC
                      LIMIT {$pg_aktif['per']} OFFSET {$pg_aktif['offset']}");

// Transaksi yang sudah selesai (untuk cetak struk ulang)
$selesai = $db->query("SELECT t.id_transaksi, t.waktu_keluar, t.biaya_total, t.plat_nomor, t.tipe_parkir_snapshot
                        FROM tb_transaksi t
                        ORDER BY t.waktu_keluar DESC
                        LIMIT 20");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Parkir - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Transaksi Parkir'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Transaksi Parkir</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Petugas</span>
                </div>
            </header>

            <div class="content">
                <div class="btn-row">
                    <a href="checkin.php" class="btn success">+ Checkin Kendaraan Baru</a>
                </div>

                <h2>Kendaraan Sedang Parkir</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Member</th>
                                <th>Tipe</th>
                                <th>Area</th>
                                <th>Plat Nomor</th>
                                <th>Jenis</th>
                                <th>Warna</th>
                                <th>Waktu Masuk</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($aktif->num_rows > 0): ?>
                                <?php $no = $pg_aktif['offset'] + 1; ?>
                                <?php while ($p = $aktif->fetch_object()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $p->tipe_parkir === 'member' ? htmlspecialchars($p->nama_member ?? '-') : 'Non-member'; ?></td>
                                        <td><?= $p->tipe_parkir === 'member' ? 'Member' : 'Non-member'; ?></td>
                                        <td><?= htmlspecialchars($p->nama_area ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($p->plat_nomor); ?></td>
                                        <td><?= htmlspecialchars($p->jenis_kendaraan); ?></td>
                                        <td><?= htmlspecialchars($p->warna_kendaraan); ?></td>
                                        <td><?= htmlspecialchars($p->waktu_masuk); ?></td>
                                        <td>
                                            <a href="checkout.php?id=<?= $p->id_parkir; ?>" class="btn sm danger">Checkout</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">Tidak ada kendaraan yang sedang parkir.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php render_paginasi($pg_aktif); ?>
                </div>

                <h2>Transaksi Selesai Terbaru</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Plat Nomor</th>
                                <th>Tipe</th>
                                <th>Waktu Keluar</th>
                                <th>Biaya</th>
                                <th>Struk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($selesai->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($t = $selesai->fetch_object()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($t->plat_nomor); ?></td>
                                        <td><?= $t->tipe_parkir_snapshot === 'member' ? 'Member' : 'Non-member'; ?></td>
                                        <td><?= htmlspecialchars($t->waktu_keluar); ?></td>
                                        <td>Rp <?= number_format($t->biaya_total, 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="../struk.php?id=<?= $t->id_transaksi; ?>" class="btn sm secondary" target="_blank">Cetak Struk</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">Belum ada transaksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php include __DIR__ . '/../../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>