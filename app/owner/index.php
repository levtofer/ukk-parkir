<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_role('owner');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Dashboard'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Dashboard Owner</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Owner</span>
                </div>
            </header>

            <div class="content">
                <div class="stat-card welcome">
                    <div class="label">Selamat Datang</div>
                    <div class="value">
                        Halo, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong>! Anda login sebagai <strong>Owner</strong>.
                    </div>
                </div>

                <h2>Navigasi Laporan</h2>
                <div class="btn-row">
                    <a href="rekap.php" class="btn">Rekap Transaksi</a>
                </div>
            </div>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>