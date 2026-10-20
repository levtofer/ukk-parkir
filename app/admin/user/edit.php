<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('admin');

$pesan = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM tb_user WHERE id_user = ? AND role = 'petugas'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_object();
$stmt->close();

if (!$user) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    // Menonaktifkan petugas lewat form ini harus sama aturannya dengan tombol Nonaktifkan.
    $masih_parkir = 0;
    if ($status_aktif === 0 && (int) $user->status_aktif === 1) {
        $cek = $db->prepare("SELECT COUNT(*) FROM tb_parkir WHERE id_user_masuk = ? AND status = 'masuk'");
        $cek->bind_param("i", $id);
        $cek->execute();
        $cek->bind_result($masih_parkir);
        $cek->fetch();
        $cek->close();
    }

    if ($masih_parkir > 0) {
        $pesan = "Tidak bisa menonaktifkan, petugas ini masih punya kendaraan yang belum checkout!";
    } elseif (!empty($nama_lengkap) && !empty($username)) {
        if ($password !== '') {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE tb_user SET nama_lengkap = ?, username = ?, password = ?, status_aktif = ? WHERE id_user = ? AND role = 'petugas'");
            $stmt->bind_param("sssii", $nama_lengkap, $username, $password_hash, $status_aktif, $id);
        } else {
            $stmt = $db->prepare("UPDATE tb_user SET nama_lengkap = ?, username = ?, status_aktif = ? WHERE id_user = ? AND role = 'petugas'");
            $stmt->bind_param("ssii", $nama_lengkap, $username, $status_aktif, $id);
        }

        if ($stmt->execute()) {
            catat_log($db, $_SESSION['id_user'], "Edit user petugas #$id");
            header("Location: index.php");
            exit;
        } else {
            error_log('[db] ' . $stmt->error);
            $pesan = "Gagal memperbarui data. Pastikan data valid dan belum ada (duplikat).";
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
    <title>Edit User Petugas - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Management User'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Edit User Petugas</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box">
                    <h3>Edit Akun Petugas</h3>

                    <?php if (!empty($pesan)): ?>
                        <div class="alert error"><?= htmlspecialchars($pesan); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap:</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($user->nama_lengkap); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user->username); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Role:</label>
                            <input type="text" value="Petugas" readonly class="readonly">
                        </div>
                        <div class="form-group">
                            <label>Password <small>(Kosongkan jika tidak ingin diubah)</small>:</label>
                            <input type="password" name="password" placeholder="••••••••">
                        </div>
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="status_aktif" value="1" <?= $user->status_aktif == 1 ? 'checked' : ''; ?>>
                                Status Aktif
                            </label>
                        </div>
                        <div class="btn-row">
                            <button type="submit" class="btn">Update</button>
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