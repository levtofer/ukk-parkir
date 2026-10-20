<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/sidebar.php";
require_role("admin");

$pesan = "";
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($id <= 0) {
  header("Location: index.php");
  exit();
}

$stmt = $db->prepare("SELECT * FROM tb_tarif WHERE id_tarif = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$tarif = $result->fetch_object();
$stmt->close();

if (!$tarif) {
  header("Location: index.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $jenis_kendaraan = trim($_POST["jenis_kendaraan"] ?? '');
  $tarif_per_jam = trim($_POST["tarif_per_jam"] ?? '');
  $diskon_member = trim($_POST["diskon_member"] ?? '');

  // Tarif tidak boleh diubah selama ada kendaraan yang parkir dengan tarif ini
  // (menjaga biaya checkout dan nama jenis kendaraan tetap konsisten).
  $masih_parkir = 0;
  $cek = $db->prepare("SELECT COUNT(*) FROM tb_parkir WHERE id_tarif = ? AND status = 'masuk'");
  $cek->bind_param("i", $id);
  $cek->execute();
  $cek->bind_result($masih_parkir);
  $cek->fetch();
  $cek->close();

  if ($masih_parkir > 0) {
    $pesan = "Tarif tidak bisa diubah selama masih ada kendaraan yang parkir dengan tarif ini ($masih_parkir).";
  } elseif (!empty($jenis_kendaraan) && is_numeric($tarif_per_jam) && $tarif_per_jam > 0 && is_numeric($diskon_member) && $diskon_member >= 0 && $diskon_member <= 100) {
    $stmt = $db->prepare(
      "UPDATE tb_tarif SET jenis_kendaraan = ?, tarif_per_jam = ?, diskon_member = ? WHERE id_tarif = ?"
    );
    $stmt->bind_param("sddi", $jenis_kendaraan, $tarif_per_jam, $diskon_member, $id);

    if ($stmt->execute()) {
      catat_log($db, $_SESSION["id_user"], "Edit tarif #$id");
      header("Location: index.php");
      exit();
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
    <title>Edit Data Tarif - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Tarif Parkir'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Edit Data Tarif</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box">
                    <h3>Edit Tarif</h3>

                    <?php if (!empty($pesan)): ?>
                        <div class="alert error"><?= htmlspecialchars($pesan) ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Jenis Kendaraan:</label>
                            <input type="text" name="jenis_kendaraan" value="<?= htmlspecialchars($tarif->jenis_kendaraan) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Tarif Dasar / Jam (non-member):</label>
                            <input type="number" name="tarif_per_jam" value="<?= htmlspecialchars($tarif->tarif_per_jam) ?>" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Diskon Member (%):</label>
                            <input type="number" name="diskon_member" value="<?= htmlspecialchars($tarif->diskon_member) ?>" min="0" max="100" step="0.01" required>
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