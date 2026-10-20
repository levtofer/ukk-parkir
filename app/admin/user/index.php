<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('admin');

$result = $db->query("SELECT * FROM tb_user WHERE role = 'petugas'");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User (Petugas) - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Management User'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Data User (Petugas)</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="btn-row">
                    <a href="tambah.php" class="btn">Tambah User</a>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Status Aktif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($user = $result->fetch_object()): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($user->nama_lengkap); ?></td>
                                        <td><?= htmlspecialchars($user->username); ?></td>
                                        <td>
                                            <span class="status-badge <?= $user->status_aktif == 1 ? 'active' : 'inactive'; ?>">
                                                <?= $user->status_aktif == 1 ? 'Aktif' : 'Tidak Aktif'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?= $user->id_user; ?>" class="btn secondary sm">Edit</a>
                                            <form action="hapus.php" method="POST" style="display:inline"
                                                onsubmit="return confirm('Yakin ingin menonaktifkan user ini?')">
                                                <input type="hidden" name="id" value="<?= $user->id_user; ?>">
                                                <button type="submit" class="btn danger sm">Nonaktifkan</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data petugas.</td>
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