<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_role('petugas');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Dashboard'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Dashboard Petugas</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Petugas</span>
                </div>
            </header>

            <div class="content">
                <div class="stat-card welcome">
                    <div class="label">Selamat Datang</div>
                    <div class="value">
                        Halo, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>! Anda login sebagai <strong>Petugas</strong>.
                    </div>
                </div>

                <h2>Navigasi</h2>
                <div class="btn-row">
                    <a href="transaksi/index.php" class="btn">Transaksi Parkir (Checkin / Checkout)</a>
                </div>
            </div>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>