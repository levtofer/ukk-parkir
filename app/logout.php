<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';

require_role(['admin', 'petugas', 'owner']);

// 1. Ambil detail user sebelum menghapus session
$role = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['id_user'] ?? null;

// 2. Tangani aksi logout saat dikonfirmasi
if (isset($_POST['confirm_logout'])) {
    if ($userId) {
        catat_log($db, $userId, "Logout");
    }

    // Hapus session sepenuhnya (data + cookie)
    hancurkan_sesi();

    header("Location: " . BASE_URL . "/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.ico" type="image/x-icon">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Yakin untuk logout?</h2>
            <form method="POST" action="">
                <div class="btn-row">
                    <button type="submit" name="confirm_logout" class="btn danger">Logout</button>
                    <a href="<?= BASE_URL . '/' . htmlspecialchars($role) . '/index.php' ?>"
                        class="btn secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>