<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_role('petugas');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: transaksi/index.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM tb_transaksi WHERE id_transaksi = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$struk = $stmt->get_result()->fetch_object();
$stmt->close();

if (!$struk) {
    header("Location: transaksi/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Parkir #<?= $struk->id_transaksi; ?></title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Transaksi Parkir'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Struk Parkir</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Petugas</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box" style="max-width: 520px;">
                    <h3>Struk Parkir #<?= htmlspecialchars($struk->id_transaksi); ?></h3>
                    <div class="table-responsive">
                        <table>
                            <tr><th>Member</th><td><?= htmlspecialchars($struk->nama_member_snapshot); ?></td></tr>
                            <tr><th>Tipe</th><td><?= $struk->tipe_parkir_snapshot === 'member' ? 'Member' : 'Non-member'; ?></td></tr>
                            <tr><th>Area</th><td><?= htmlspecialchars($struk->nama_area_snapshot); ?></td></tr>
                            <tr><th>Plat Nomor</th><td><?= htmlspecialchars($struk->plat_nomor); ?></td></tr>
                            <tr><th>Jenis / Warna</th><td><?= htmlspecialchars($struk->jenis_kendaraan_snapshot) . ' / ' . htmlspecialchars($struk->warna_kendaraan); ?></td></tr>
                            <tr><th>Tarif Dasar / Jam</th><td>Rp <?= number_format($struk->tarif_dasar_snapshot, 0, ',', '.'); ?></td></tr>
                            <?php if ($struk->diskon_persen_snapshot > 0): ?>
                            <tr><th>Diskon Member</th><td><?= rtrim(rtrim(number_format($struk->diskon_persen_snapshot, 2, ',', '.'), '0'), ','); ?>%</td></tr>
                            <?php endif; ?>
                            <tr><th>Tarif Dikenakan / Jam</th><td>Rp <?= number_format($struk->tarif_per_jam_snapshot, 0, ',', '.'); ?></td></tr>
                            <tr><th>Waktu Keluar</th><td><?= htmlspecialchars($struk->waktu_keluar); ?></td></tr>
                            <tr><th>Durasi</th><td><?= htmlspecialchars($struk->durasi_jam); ?> jam</td></tr>
                            <tr><th>Total Biaya</th><td><strong>Rp <?= number_format($struk->biaya_total, 0, ',', '.'); ?></strong></td></tr>
                            <tr><th>Petugas Checkin</th><td><?= htmlspecialchars($struk->nama_petugas_masuk_snapshot); ?></td></tr>
                            <tr><th>Petugas Checkout</th><td><?= htmlspecialchars($struk->nama_petugas_keluar_snapshot); ?></td></tr>
                            <tr><th>Status</th><td><span class="status-badge active"><?= htmlspecialchars($struk->status); ?></span></td></tr>
                        </table>
                    </div>
                    <div class="btn-row" style="margin-bottom: 0; margin-top: 16px;">
                        <button onclick="window.print()" class="btn success">Cetak Struk</button>
                        <a href="transaksi/index.php" class="btn secondary">Kembali</a>
                    </div>
                </div>
            </div>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>