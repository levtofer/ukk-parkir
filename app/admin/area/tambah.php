<?php
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/sidebar.php";
require_role("admin");

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nama_area = trim($_POST["nama_area"] ?? '');
  $kapasitas = trim($_POST["kapasitas"] ?? '');
  $peruntukan = trim($_POST["peruntukan"] ?? '');
  if (!empty($nama_area) && ctype_digit($kapasitas) && (int)$kapasitas > 0 && (int)$kapasitas <= 1000000 && in_array($peruntukan, ['member', 'non_member', 'campuran'], true)) {
    $stmt = $db->prepare(
      "INSERT INTO tb_area_parkir (nama_area, kapasitas, peruntukan, terisi) VALUES (?, ?, ?, 0)"
    );
    $stmt->bind_param("sis", $nama_area, $kapasitas, $peruntukan);

    if ($stmt->execute()) {
      catat_log($db, $_SESSION["id_user"], "Tambah area parkir: " . $nama_area);
      header("Location: index.php");
      exit();
    } else {
      error_log('[db] ' . $stmt->error);
      $pesan = "Gagal menyimpan data. Pastikan data valid dan belum ada (duplikat).";
    }
    $stmt->close();
  } else {
    $pesan = (ctype_digit($kapasitas) && (int)$kapasitas > 1000000)
      ? "Kapasitas maksimal 1.000.000."
      : "Semua field harus diisi dengan benar!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Area Parkir - Aplikasi Parkir</title>
  <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
  <div class="app">
    <?php render_sidebar('Area Parkir'); ?>

    <main class="main">
      <header class="topbar">
        <div class="page-title">Tambah Area Parkir</div>
        <div class="user-box">
          <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
          <span class="role-tag">Admin</span>
        </div>
      </header>

      <div class="content">
        <div class="form-box">
          <h3>Tambah Area Baru</h3>

          <?php if (!empty($pesan)): ?>
            <div class="alert error"><?= htmlspecialchars($pesan) ?></div>
          <?php endif; ?>

          <form action="" method="POST">
            <div class="form-group">
              <label>Nama Area:</label>
              <input type="text" name="nama_area" required autofocus>
            </div>
            <div class="form-group">
              <label>Kapasitas:</label>
              <input type="number" name="kapasitas" min="1" max="1000000" required>
            </div>
            <div class="form-group">
              <label>Peruntukan:</label>
              <select name="peruntukan" required>
                <option value="member">Member</option>
                <option value="non_member">Non-member</option>
                <option value="campuran">Campuran (member &amp; non-member)</option>
              </select>
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