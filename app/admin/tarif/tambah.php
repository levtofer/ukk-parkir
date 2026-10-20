<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/sidebar.php";
require_role("admin");

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $jenis_kendaraan = trim($_POST["jenis_kendaraan"] ?? '');
    $tarif_per_jam = trim($_POST["tarif_per_jam"] ?? '');
    $diskon_member = trim($_POST["diskon_member"] ?? '');

    if (!empty($jenis_kendaraan) && is_numeric($tarif_per_jam) && $tarif_per_jam > 0 && is_numeric($diskon_member) && $diskon_member >= 0 && $diskon_member <= 100) {
        $stmt = $db->prepare(
            "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam, diskon_member) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sdd", $jenis_kendaraan, $tarif_per_jam, $diskon_member);

        if ($stmt->execute()) {
            catat_log($db, $_SESSION["id_user"], "Tambah tarif: " . $jenis_kendaraan);
            header("Location: index.php");
            exit();
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
    <title>Tambah Data Tarif - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Tarif Parkir'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Tambah Data Tarif</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box">
                    <h3>Tambah Tarif Baru</h3>

                    <?php if (!empty($pesan)): ?>
                        <div class="alert error"><?= htmlspecialchars($pesan) ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Jenis Kendaraan:</label>
                            <input type="text" name="jenis_kendaraan" placeholder="motor / mobil / truk / dll" required autofocus>
                        </div>
                        <div class="form-group">
                            <label>Tarif Dasar / Jam (non-member):</label>
                            <input type="number" name="tarif_per_jam" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Diskon Member (%):</label>
                            <input type="number" name="diskon_member" value="50" min="0" max="100" step="0.01" required>
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