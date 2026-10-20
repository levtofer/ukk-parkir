<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_role('admin');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Dashboard'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Dashboard Admin</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="stat-card welcome">
                    <div class="label">Selamat Datang</div>
                    <div class="value">
                        Halo, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>! Anda login sebagai <strong><?= htmlspecialchars($_SESSION['role']); ?></strong>.
                    </div>
                </div>

                <h2>Navigasi</h2>
                <div class="btn-row navigation">
                    <a href="area/index.php" class="btn">Area Parkir</a>
                    <a href="tarif/index.php" class="btn">Tarif Parkir</a>
                    <a href="user/index.php" class="btn">User Management</a>
                    <a href="member/index.php" class="btn">Member</a>
                    <a href="log/index.php" class="btn secondary">Log Aktivitas</a>
                </div>
            </div>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>