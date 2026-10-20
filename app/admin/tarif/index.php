<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('admin');

$result = $db->query("SELECT * FROM tb_tarif");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tarif - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Tarif Parkir'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Data Tarif Parkir</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="btn-row">
                    <a href="tambah.php" class="btn">Tambah Tarif</a>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Jenis Kendaraan</th>
                                <th>Tarif Dasar / Jam</th>
                                <th>Diskon Member</th>
                                <th>Tarif Member / Jam</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($tarif = $result->fetch_object()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($tarif->jenis_kendaraan); ?></td>
                                        <td>Rp <?= number_format($tarif->tarif_per_jam, 0, ',', '.'); ?></td>
                                        <td><?= rtrim(rtrim(number_format($tarif->diskon_member, 2, ',', '.'), '0'), ','); ?>%</td>
                                        <td>Rp <?= number_format(round($tarif->tarif_per_jam * (1 - $tarif->diskon_member / 100)), 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="edit.php?id=<?= $tarif->id_tarif; ?>" class="btn secondary sm">Edit</a>
                                            <form action="hapus.php" method="POST" style="display:inline"
                                                onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                <input type="hidden" name="id" value="<?= $tarif->id_tarif; ?>">
                                                <button type="submit" class="btn danger sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data tarif.</td>
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