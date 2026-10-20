<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('admin');

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role = 'petugas';
    $password = $_POST['password'] ?? '';
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    if (!empty($nama_lengkap) && !empty($username) && $password !== '') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO tb_user (nama_lengkap, username, role, password, status_aktif) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nama_lengkap, $username, $role, $password_hash, $status_aktif);

        if ($stmt->execute()) {
            catat_log($db, $_SESSION['id_user'], "Tambah user petugas: " . $username);
            header("Location: index.php");
            exit;
        } else {
            error_log('[db] ' . $stmt->error);
            $pesan = "Gagal menyimpan data. Pastikan data valid dan belum ada (duplikat).";
        }
        $stmt->close();
    } else {
        $pesan = "Semua field harus diisi dengan benar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User Petugas - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Management User'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Tambah User Petugas</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box">
                    <h3>Tambah Akun Petugas Baru</h3>

                    <?php if (!empty($pesan)): ?>
                        <div class="alert error"><?= htmlspecialchars($pesan); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap:</label>
                            <input type="text" name="nama_lengkap" required autofocus>
                        </div>
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Role:</label>
                            <input type="text" value="Petugas" readonly class="readonly">
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" required placeholder="••••••••">
                        </div>
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="status_aktif" value="1" checked>
                                Status Aktif
                            </label>
                        </div>
                        <div class="btn-row">
                            <button type="submit" class="btn">Simpan</button>
                            <a href="index.php" class="btn secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php include __DIR__ . '/../../includes/footer.php'; ?>
        </main>
    </div>
</body>

</html>