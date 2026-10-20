<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('admin');
require_once __DIR__ . '/../../includes/helper.php';

$total_area = (int) $db->query("SELECT COUNT(*) FROM tb_area_parkir")->fetch_row()[0];
$pg = siapkan_paginasi($total_area, 15);
$result = $db->query("
    SELECT a.*,
           (SELECT COUNT(*)
            FROM tb_parkir p
            WHERE p.id_area = a.id_area AND p.status = 'masuk') AS terisi_aktual
    FROM tb_area_parkir a
    ORDER BY a.id_area
    LIMIT {$pg['per']} OFFSET {$pg['offset']}
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Area Parkir - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Area Parkir'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Data Area Parkir</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="btn-row">
                    <a href="tambah.php" class="btn">Tambah Area Parkir</a>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Area</th>
                                <th>Kapasitas</th>
                                <th>Peruntukan</th>
                                <th>Terisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = $pg['offset'] + 1; ?>
                                <?php while ($area = $result->fetch_object()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($area->nama_area); ?></td>
                                        <td><?= htmlspecialchars($area->kapasitas); ?></td>
                                        <td><?= ['member' => 'Member', 'non_member' => 'Non-member', 'campuran' => 'Campuran'][$area->peruntukan] ?? htmlspecialchars($area->peruntukan); ?></td>
                                        <td><?= htmlspecialchars($area->terisi_aktual); ?></td>
                                        <td>
                                            <a href="edit.php?id=<?= $area->id_area; ?>" class="btn secondary sm">Edit</a>
                                            <form action="hapus.php" method="POST" style="display:inline"
                                                onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                <input type="hidden" name="id" value="<?= $area->id_area; ?>">
                                                <button type="submit" class="btn danger sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data area parkir.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php render_paginasi($pg); ?>
                </div>
            </div>
            <?php include __DIR__ . '/../../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>