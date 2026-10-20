<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('admin');
require_once __DIR__ . '/../../includes/helper.php';

$total_member = (int) $db->query("SELECT COUNT(*) FROM tb_member")->fetch_row()[0];
$pg = siapkan_paginasi($total_member, 15);
$result = $db->query("SELECT m.*, a.nama_area, u.nama_lengkap AS nama_petugas
                       FROM tb_member m
                       JOIN tb_area_parkir a ON m.id_area = a.id_area
                       JOIN tb_user u ON m.id_user = u.id_user
                       ORDER BY m.id_member
                       LIMIT {$pg['per']} OFFSET {$pg['offset']}");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Member - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Member'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Data Member</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="btn-row">
                    <a href="tambah.php" class="btn">Tambah Member</a>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Member</th>
                                <th>Jenis Kelamin</th>
                                <th>No HP</th>
                                <th>Tgl Daftar</th>
                                <th>Area</th>
                                <th>Didaftarkan Oleh</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = $pg['offset'] + 1; ?>
                                <?php while ($member = $result->fetch_object()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($member->nama_member); ?></td>
                                        <td><?= $member->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                        <td><?= htmlspecialchars($member->no_hp); ?></td>
                                        <td><?= htmlspecialchars($member->tanggal_daftar); ?></td>
                                        <td><?= htmlspecialchars($member->nama_area); ?></td>
                                        <td><?= htmlspecialchars($member->nama_petugas); ?></td>
                                        <td>
                                            <span
                                                class="status-badge <?= $member->status_aktif == 'aktif' ? 'active' : 'inactive'; ?>">
                                                <?= htmlspecialchars(ucfirst($member->status_aktif)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?= $member->id_member; ?>" class="btn secondary sm">Edit</a>
                                            <form action="hapus.php" method="POST" style="display:inline"
                                                onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                <input type="hidden" name="id" value="<?= $member->id_member; ?>">
                                                <button type="submit" class="btn danger sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada data member.</td>
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