<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('admin');

$result = $db->query("SELECT l.*, u.nama_lengkap, u.role
                       FROM tb_log_aktivitas l
                       JOIN tb_user u ON l.id_user = u.id_user
                       ORDER BY l.waktu_aktivitas DESC
                       LIMIT 200");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Log Aktivitas'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Log Aktivitas</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <p class="subtitle">Menampilkan 200 aktivitas terbaru.</p>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($log = $result->fetch_object()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($log->waktu_aktivitas); ?></td>
                                        <td><?= htmlspecialchars($log->nama_lengkap); ?></td>
                                        <td>
                                            <span class="role-badge <?= strtolower($log->role); ?>">
                                                <?= htmlspecialchars(ucfirst($log->role)); ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($log->aktivitas); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada log aktivitas.</td>
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