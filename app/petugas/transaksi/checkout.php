<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('petugas');
require_once __DIR__ . '/../../includes/helper.php';

$pesan = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Changed JOIN tb_tarif to LEFT JOIN tb_tarif and added COALESCE for safety
$stmt = $db->prepare("SELECT p.*, m.nama_member, a.nama_area, COALESCE(t.tarif_per_jam, 0) AS tarif_dasar, COALESCE(t.diskon_member, 0) AS diskon_member, u1.nama_lengkap AS petugas_masuk
                       FROM tb_parkir p
                       LEFT JOIN tb_member m ON p.id_member = m.id_member
                       JOIN tb_area_parkir a ON p.id_area = a.id_area
                       LEFT JOIN tb_tarif t ON p.id_tarif = t.id_tarif
                       JOIN tb_user u1 ON p.id_user_masuk = u1.id_user
                       WHERE p.id_parkir = ? AND p.status = 'masuk'");
$stmt->bind_param("i", $id);
$stmt->execute();
$parkir = $stmt->get_result()->fetch_object();
$stmt->close();

if (!$parkir) {
    header("Location: index.php");
    exit;
}

$waktu_masuk = new DateTime($parkir->waktu_masuk);
$sekarang = new DateTime();
$selisih_detik = $sekarang->getTimestamp() - $waktu_masuk->getTimestamp();
$durasi_jam = hitung_durasi_jam($selisih_detik);

// Member mendapat diskon sesuai tarif; non-member membayar tarif dasar.
$is_member = $parkir->tipe_parkir === 'member';
$nama_tampil = $is_member ? $parkir->nama_member : 'Non-member';
$diskon_persen = $is_member ? (float) $parkir->diskon_member : 0.0;
$tarif_dasar = (float) $parkir->tarif_dasar;
$tarif_dikenakan = hitung_tarif_dikenakan($tarif_dasar, $diskon_persen);
$biaya_total = $durasi_jam * $tarif_dikenakan;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user_keluar = $_SESSION['id_user'];
    $nama_petugas_keluar = $_SESSION['nama_lengkap'];

    $db->begin_transaction();
    try {
        $stmt = $db->prepare("INSERT INTO tb_transaksi
                               (id_parkir, plat_nomor, warna_kendaraan, jenis_kendaraan_snapshot,
                                tarif_per_jam_snapshot, tarif_dasar_snapshot, diskon_persen_snapshot,
                                tipe_parkir_snapshot, nama_member_snapshot, nama_area_snapshot,
                                nama_petugas_masuk_snapshot, waktu_keluar, durasi_jam, biaya_total,
                                id_user_keluar, nama_petugas_keluar_snapshot, status)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, 'lunas')");
        if (!$stmt)
            throw new Exception($db->error);
        $stmt->bind_param(
            "isssdddssssidis",
            $id,
            $parkir->plat_nomor,
            $parkir->warna_kendaraan,
            $parkir->jenis_kendaraan,
            $tarif_dikenakan,
            $tarif_dasar,
            $diskon_persen,
            $parkir->tipe_parkir,
            $nama_tampil,
            $parkir->nama_area,
            $parkir->petugas_masuk,
            $durasi_jam,
            $biaya_total,
            $id_user_keluar,
            $nama_petugas_keluar
        );
        if (!$stmt->execute())
            throw new Exception($stmt->error);
        $id_transaksi = $db->insert_id;
        $stmt->close();

        $stmt = $db->prepare("UPDATE tb_parkir SET status = 'keluar' WHERE id_parkir = ? AND status = 'masuk'");
        if (!$stmt)
            throw new Exception($db->error);
        $stmt->bind_param("i", $id);
        if (!$stmt->execute() || $stmt->affected_rows !== 1)
            throw new Exception("Kendaraan sudah checkout atau gagal diupdate");
        $stmt->close();

        $stmt = $db->prepare("UPDATE tb_area_parkir a
            SET a.terisi = (SELECT COUNT(*) FROM tb_parkir p
                            WHERE p.id_area = a.id_area AND p.status = 'masuk')
            WHERE a.id_area = ?");
        $stmt->bind_param("i", $parkir->id_area);
        if (!$stmt->execute())
            throw new Exception($stmt->error);
        $stmt->close();

        $db->commit();
        catat_log($db, $id_user_keluar, "Checkout kendaraan: " . $parkir->plat_nomor);

        header("Location: ../struk.php?id=" . $id_transaksi);
        exit;
    } catch (Exception $e) {
        $db->rollback();
        error_log('[checkout] ' . $e->getMessage());
        $pesan = "Gagal memproses checkout. Kendaraan mungkin sudah di-checkout, atau terjadi kesalahan. Coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Kendaraan - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Transaksi Parkir'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Checkout Kendaraan</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Petugas</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box" style="max-width: 520px;">
                    <h3>Konfirmasi Checkout</h3>

                    <?php if (!empty($pesan)): ?>
                        <div class="alert error"><?= htmlspecialchars($pesan); ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th>Member</th>
                                <td><?= htmlspecialchars($nama_tampil); ?></td>
                            </tr>
                            <tr>
                                <th>Area</th>
                                <td><?= htmlspecialchars($parkir->nama_area); ?></td>
                            </tr>
                            <tr>
                                <th>Plat Nomor</th>
                                <td><?= htmlspecialchars($parkir->plat_nomor); ?></td>
                            </tr>
                            <tr>
                                <th>Jenis Kendaraan</th>
                                <td><?= htmlspecialchars($parkir->jenis_kendaraan); ?></td>
                            </tr>
                            <tr>
                                <th>Waktu Masuk</th>
                                <td><?= htmlspecialchars($parkir->waktu_masuk); ?></td>
                            </tr>
                            <tr>
                                <th>Waktu Keluar (sekarang)</th>
                                <td><?= $sekarang->format('Y-m-d H:i:s'); ?></td>
                            </tr>
                            <tr>
                                <th>Durasi</th>
                                <td><?= $durasi_jam; ?> jam</td>
                            </tr>
                            <tr>
                                <th>Tarif Dasar / Jam</th>
                                <td>Rp <?= number_format($tarif_dasar, 0, ',', '.'); ?></td>
                            </tr>
                            <?php if ($diskon_persen > 0): ?>
                            <tr>
                                <th>Diskon Member</th>
                                <td><?= rtrim(rtrim(number_format($diskon_persen, 2, ',', '.'), '0'), ','); ?>%</td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Tarif Dikenakan / Jam</th>
                                <td>Rp <?= number_format($tarif_dikenakan, 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <th>Total Biaya</th>
                                <td><strong>Rp <?= number_format($biaya_total, 0, ',', '.'); ?></strong></td>
                            </tr>
                        </table>
                    </div>

                    <form action="" method="POST" style="margin-top: 16px;">
                        <div class="btn-row" style="margin-bottom: 0;">
                            <button type="submit" class="btn danger">Konfirmasi Checkout</button>
                            <a href="index.php" class="btn secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php include __DIR__ . '/../../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>