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

$stmt = $db->prepare("SELECT * FROM tb_area_parkir WHERE id_area = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$area = $result->fetch_object();
$stmt->close();

if (!$area) {
  header("Location: index.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nama_area = trim($_POST["nama_area"] ?? '');
  $kapasitas = trim($_POST["kapasitas"] ?? '');
  $peruntukan = trim($_POST["peruntukan"] ?? '');
  $peruntukan_ok = in_array($peruntukan, ['member', 'non_member', 'campuran'], true);

  // Peruntukan tidak boleh bentrok dengan data yang sudah ada di area ini.
  $error_peruntukan = "";
  if ($peruntukan_ok && $peruntukan !== $area->peruntukan) {
    if ($peruntukan === 'non_member') {
      $jml_member = 0;
      $c = $db->prepare("SELECT COUNT(*) FROM tb_member WHERE id_area = ?");
      $c->bind_param("i", $id);
      $c->execute();
      $c->bind_result($jml_member);
      $c->fetch();
      $c->close();
      if ($jml_member > 0) {
        $error_peruntukan = "Tidak bisa diubah ke Non-member, area ini masih punya $jml_member member.";
      }
    } elseif ($peruntukan === 'member') {
      $jml_tamu = 0;
      $c = $db->prepare("SELECT COUNT(*) FROM tb_parkir WHERE id_area = ? AND status = 'masuk' AND tipe_parkir = 'non_member'");
      $c->bind_param("i", $id);
      $c->execute();
      $c->bind_result($jml_tamu);
      $c->fetch();
      $c->close();
      if ($jml_tamu > 0) {
        $error_peruntukan = "Tidak bisa diubah ke Member, masih ada $jml_tamu kendaraan non-member yang parkir di area ini.";
      }
    }
  }

  $terisi_sekarang = 0;
  $cek = $db->prepare("SELECT COUNT(*) FROM tb_parkir p WHERE p.id_area = ? AND p.status = 'masuk'");
  $cek->bind_param("i", $id);
  $cek->execute();
  $cek->bind_result($terisi_sekarang);
  $cek->fetch();
  $cek->close();
  $kapasitas_ok = ctype_digit($kapasitas) && (int) $kapasitas > 0 && (int) $kapasitas <= 1000000;
  if (!empty($nama_area) && $kapasitas_ok && $peruntukan_ok && $error_peruntukan === "" && (int) $kapasitas >= $terisi_sekarang) {
    $stmt = $db->prepare(
      "UPDATE tb_area_parkir SET nama_area = ?, kapasitas = ?, peruntukan = ? WHERE id_area = ?"
    );
    $stmt->bind_param("sisi", $nama_area, $kapasitas, $peruntukan, $id);

    if ($stmt->execute()) {
      catat_log($db, $_SESSION["id_user"], "Edit area parkir #$id");
      header("Location: index.php");
      exit();
    } else {
      error_log('[db] ' . $stmt->error);
      $pesan = "Gagal memperbarui data. Pastikan data valid dan belum ada (duplikat).";
    }
    $stmt->close();
  } else {
    $pesan = ($error_peruntukan !== "")
      ? $error_peruntukan
      : (($kapasitas_ok && (int) $kapasitas < $terisi_sekarang)
      ? "Kapasitas tidak boleh lebih kecil dari kendaraan yang sedang parkir ($terisi_sekarang)."
      : ((ctype_digit($kapasitas) && (int) $kapasitas > 1000000)
        ? "Kapasitas maksimal 1.000.000."
        : "Semua field harus diisi dengan benar!"));
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Area Parkir - Aplikasi Parkir</title>
  <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
  <div class="app">
    <?php render_sidebar('Area Parkir'); ?>

    <main class="main">
      <header class="topbar">
        <div class="page-title">Edit Area Parkir</div>
        <div class="user-box">
          <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
          <span class="role-tag">Admin</span>
        </div>
      </header>

      <div class="content">
        <div class="form-box">
          <h3>Edit Data Area</h3>

          <?php if (!empty($pesan)): ?>
            <div class="alert error"><?= htmlspecialchars($pesan) ?></div>
          <?php endif; ?>

          <form action="" method="POST">
            <div class="form-group">
              <label>Nama Area:</label>
              <input type="text" name="nama_area" value="<?= htmlspecialchars($area->nama_area) ?>" required>
            </div>
            <div class="form-group">
              <label>Kapasitas:</label>
              <input type="number" name="kapasitas" value="<?= htmlspecialchars($area->kapasitas) ?>" min="1" max="1000000" required>
            </div>
            <div class="form-group">
              <label>Peruntukan:</label>
              <select name="peruntukan" required>
                <option value="member"<?= $area->peruntukan === 'member' ? ' selected' : '' ?>>Member</option>
                <option value="non_member"<?= $area->peruntukan === 'non_member' ? ' selected' : '' ?>>Non-member</option>
                <option value="campuran"<?= $area->peruntukan === 'campuran' ? ' selected' : '' ?>>Campuran (member &amp; non-member)</option>
              </select>
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