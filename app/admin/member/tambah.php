<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('admin');

$pesan = "";
// Member hanya boleh di area peruntukan member atau campuran.
$areas = $db->query("SELECT id_area, nama_area FROM tb_area_parkir WHERE peruntukan IN ('member', 'campuran') ORDER BY nama_area");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_member = trim($_POST['nama_member'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $id_user = intval($_SESSION['id_user']);
    $id_area = intval($_POST['id_area'] ?? 0);
    $status_aktif = 'aktif';

    $area_valid = 0;
    if ($id_area > 0) {
        $cek_area = $db->prepare("SELECT COUNT(*) FROM tb_area_parkir WHERE id_area = ? AND peruntukan IN ('member', 'campuran')");
        $cek_area->bind_param("i", $id_area);
        $cek_area->execute();
        $cek_area->bind_result($area_valid);
        $cek_area->fetch();
        $cek_area->close();
    }

    $sudah_ada = 0;
    if ($nama_member !== '' && $no_hp !== '') {
        $cek = $db->prepare("SELECT COUNT(*) FROM tb_member WHERE nama_member = ? AND no_hp = ?");
        $cek->bind_param("ss", $nama_member, $no_hp);
        $cek->execute();
        $cek->bind_result($sudah_ada);
        $cek->fetch();
        $cek->close();
    }

    if ($sudah_ada > 0) {
        $pesan = "Member dengan nama dan nomor HP yang sama sudah terdaftar.";
    } elseif ($id_area > 0 && $area_valid === 0) {
        $pesan = "Area tidak valid untuk member. Pilih area dengan peruntukan Member atau Campuran.";
    } elseif (!empty($nama_member) && in_array($jenis_kelamin, ['L','P'], true) && preg_match('/^[0-9]{8,15}$/', $no_hp) && $id_user > 0 && $id_area > 0) {
        $stmt = $db->prepare("INSERT INTO tb_member (nama_member, jenis_kelamin, no_hp, tanggal_daftar, status_aktif, id_user, id_area) VALUES (?, ?, ?, CURDATE(), ?, ?, ?)");
        $stmt->bind_param("ssssii", $nama_member, $jenis_kelamin, $no_hp, $status_aktif, $id_user, $id_area);

        if ($stmt->execute()) {
            catat_log($db, $_SESSION['id_user'], "Tambah member: " . $nama_member);
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
    <title>Tambah Member - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Member'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Tambah Member</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Admin</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box">
                    <h3>Tambah Member Baru</h3>

                    <?php if (!empty($pesan)): ?>
                        <div class="alert error"><?= htmlspecialchars($pesan); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Nama Member:</label>
                            <input type="text" name="nama_member" required autofocus>
                        </div>
                        <div class="form-group">
                            <label>Jenis Kelamin:</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="jenis_kelamin" value="L" required> Laki-laki
                                </label>
                                <label>
                                    <input type="radio" name="jenis_kelamin" value="P" required> Perempuan
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>No. HP:</label>
                            <input type="text" name="no_hp" required>
                        </div>
                        <div class="form-group">
                            <label>Area Wilayah:</label>
                            <select name="id_area" required>
                                <option value="">-- Pilih Area --</option>
                                <?php while ($area = $areas->fetch_object()): ?>
                                    <option value="<?= $area->id_area; ?>"><?= htmlspecialchars($area->nama_area); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Didaftarkan Oleh (Admin):</label>
                            <input type="text" value="<?= htmlspecialchars($_SESSION['nama_lengkap']); ?>" readonly class="readonly">
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