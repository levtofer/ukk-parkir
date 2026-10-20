<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_role('owner');
require_once __DIR__ . '/../includes/helper.php';

function tanggal_valid($tanggal)
{
    $d = DateTime::createFromFormat('Y-m-d', $tanggal);
    return $d !== false && $d->format('Y-m-d') === $tanggal;
}

$dari = isset($_GET['dari']) ? trim($_GET['dari']) : date('Y-m-01');
$sampai = isset($_GET['sampai']) ? trim($_GET['sampai']) : date('Y-m-d');
$pesan_filter = '';

if (!tanggal_valid($dari) || !tanggal_valid($sampai)) {
    $dari = date('Y-m-01');
    $sampai = date('Y-m-d');
    $pesan_filter = 'Format tanggal tidak valid, ditampilkan bulan ini.';
} elseif ($dari > $sampai) {
    [$dari, $sampai] = [$sampai, $dari];
    $pesan_filter = 'Tanggal awal lebih besar dari tanggal akhir, keduanya ditukar.';
}

// Ringkasan dihitung di database untuk SELURUH rentang tanggal (bukan hanya halaman ini).
$stmt = $db->prepare("SELECT COUNT(*) AS jml,
                              COALESCE(SUM(biaya_total), 0) AS total,
                              COALESCE(SUM(CASE WHEN tipe_parkir_snapshot = 'member' THEN biaya_total END), 0) AS total_member
                       FROM tb_transaksi
                       WHERE waktu_keluar >= ? AND waktu_keluar < DATE_ADD(?, INTERVAL 1 DAY)");
$stmt->bind_param("ss", $dari, $sampai);
$stmt->execute();
$ringkas = $stmt->get_result()->fetch_object();
$stmt->close();

$total_transaksi = (int) $ringkas->jml;
$total_pendapatan = (float) $ringkas->total;
$pendapatan_member = (float) $ringkas->total_member;
$pendapatan_tamu = $total_pendapatan - $pendapatan_member;

// Detail transaksi: dibatasi per halaman.
$pg = siapkan_paginasi($total_transaksi, 20);
$stmt = $db->prepare("SELECT id_transaksi, waktu_keluar, durasi_jam, biaya_total,
                              plat_nomor, jenis_kendaraan_snapshot, nama_member_snapshot, tipe_parkir_snapshot
                       FROM tb_transaksi
                       WHERE waktu_keluar >= ? AND waktu_keluar < DATE_ADD(?, INTERVAL 1 DAY)
                       ORDER BY waktu_keluar DESC
                       LIMIT ? OFFSET ?");
$stmt->bind_param("ssii", $dari, $sampai, $pg['per'], $pg['offset']);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_object()) {
    $rows[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Transaksi - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Rekap Laporan'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Rekap Transaksi</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Owner</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box" style="max-width: 100%;">
                    <h3>Filter Tanggal</h3>
                    <?php if ($pesan_filter !== ''): ?>
                        <div class="alert error"><?= htmlspecialchars($pesan_filter); ?></div>
                    <?php endif; ?>
                    <form action="" method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
                        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                            <label>Dari:</label>
                            <input type="date" name="dari" value="<?= htmlspecialchars($dari); ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                            <label>Sampai:</label>
                            <input type="date" name="sampai" value="<?= htmlspecialchars($sampai); ?>">
                        </div>
                        <button type="submit" class="btn">Tampilkan</button>
                    </form>
                </div>

                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="label">Total Transaksi</div>
                        <div class="value"><?= $total_transaksi; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Total Pendapatan</div>
                        <div class="value">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Pendapatan Member</div>
                        <div class="value">Rp <?= number_format($pendapatan_member, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Pendapatan Non-member</div>
                        <div class="value">Rp <?= number_format($pendapatan_tamu, 0, ',', '.'); ?></div>
                    </div>
                </div>

                <h2>Detail Transaksi</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Member</th>
                                <th>Tipe</th>
                                <th>Plat Nomor</th>
                                <th>Jenis</th>
                                <th>Waktu Keluar</th>
                                <th>Durasi (jam)</th>
                                <th>Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rows) > 0): ?>
                                <?php $no = $pg['offset'] + 1; ?>
                                <?php foreach ($rows as $t): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($t->nama_member_snapshot); ?></td>
                                        <td><?= $t->tipe_parkir_snapshot === 'member' ? 'Member' : 'Non-member'; ?></td>
                                        <td><?= htmlspecialchars($t->plat_nomor); ?></td>
                                        <td><?= htmlspecialchars($t->jenis_kendaraan_snapshot); ?></td>
                                        <td><?= htmlspecialchars($t->waktu_keluar); ?></td>
                                        <td><?= htmlspecialchars($t->durasi_jam); ?></td>
                                        <td>Rp <?= number_format($t->biaya_total, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">Tidak ada transaksi pada rentang tanggal ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php render_paginasi($pg); ?>
                </div>
            </div>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>