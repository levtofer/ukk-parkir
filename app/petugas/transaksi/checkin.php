<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role('petugas');

$pesan = "";

// Member aktif beserta nama area-nya (area otomatis mengikuti member).
$members_list = $db->query("SELECT m.id_member, m.nama_member, m.id_area, a.nama_area
                            FROM tb_member m
                            JOIN tb_area_parkir a ON m.id_area = a.id_area
                            WHERE m.status_aktif = 'aktif'
                            ORDER BY m.nama_member")->fetch_all(MYSQLI_ASSOC);

// Area untuk non-member: hanya yang masih ada sisa slot.
$areas_list = $db->query("SELECT a.id_area, a.nama_area, a.kapasitas,
                                 (SELECT COUNT(*) FROM tb_parkir p WHERE p.id_area = a.id_area AND p.status = 'masuk') AS terisi_aktual
                          FROM tb_area_parkir a
                          WHERE a.peruntukan IN ('non_member', 'campuran')
                          ORDER BY a.nama_area")->fetch_all(MYSQLI_ASSOC);
$areas_list = array_values(array_filter($areas_list, fn($a) => $a['terisi_aktual'] < $a['kapasitas']));

$jenis_list = $db->query("SELECT DISTINCT jenis_kendaraan FROM tb_tarif ORDER BY jenis_kendaraan")->fetch_all(MYSQLI_ASSOC);
$jenis_options = array_column($jenis_list, 'jenis_kendaraan');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // id_member = 0 berarti non-member.
    $id_member = intval($_POST['id_member'] ?? 0);
    $id_area_post = intval($_POST['id_area'] ?? 0);
    // Normalisasi plat: huruf besar tanpa spasi (mis. "b 1234 xy" -> "B1234XY"),
    // supaya pengecekan plat ganda tidak bisa dilewati dengan beda huruf/spasi.
    $plat_nomor = strtoupper(preg_replace('/\s+/', '', trim($_POST['plat_nomor'] ?? '')));
    $warna_kendaraan = trim($_POST['warna_kendaraan'] ?? '');
    $jenis_kendaraan = trim($_POST['jenis_kendaraan'] ?? '');

    if ($id_member >= 0 && preg_match('/^[A-Z0-9]{3,15}$/', $plat_nomor) && !empty($warna_kendaraan) && in_array($jenis_kendaraan, $jenis_options)) {

        $stmt = $db->prepare("SELECT id_tarif FROM tb_tarif WHERE jenis_kendaraan = ? LIMIT 1");
        $stmt->bind_param("s", $jenis_kendaraan);
        $stmt->execute();
        $tarif = $stmt->get_result()->fetch_object();
        $stmt->close();

        $tipe_parkir = $id_member > 0 ? 'member' : 'non_member';
        $id_area = 0;
        $error_tipe = "";

        if ($tipe_parkir === 'member') {
            // Area diambil dari data member, bukan dari form.
            $stmt = $db->prepare("SELECT id_area, status_aktif FROM tb_member WHERE id_member = ?");
            $stmt->bind_param("i", $id_member);
            $stmt->execute();
            $member_row = $stmt->get_result()->fetch_object();
            $stmt->close();

            if (!$member_row) {
                $error_tipe = "Member tidak ditemukan.";
            } elseif ($member_row->status_aktif !== 'aktif') {
                $error_tipe = "Member sedang suspended.";
            } else {
                $id_area = (int) $member_row->id_area;
            }
        } else {
            $stmt = $db->prepare("SELECT id_area FROM tb_area_parkir WHERE id_area = ? AND peruntukan IN ('non_member', 'campuran')");
            $stmt->bind_param("i", $id_area_post);
            $stmt->execute();
            $area_row = $stmt->get_result()->fetch_object();
            $stmt->close();

            if (!$area_row) {
                $error_tipe = "Pilih area parkir yang valid untuk non-member.";
            } else {
                $id_area = (int) $area_row->id_area;
            }
        }

        if (!$tarif) {
            $pesan = "Belum ada tarif untuk jenis kendaraan ini. Tambahkan dulu di CRUD Tarif.";
        } elseif ($error_tipe !== "") {
            $pesan = $error_tipe;
        } else {
            $id_tarif = $tarif->id_tarif;
            $id_user_masuk = $_SESSION['id_user'];
            $id_member_db = $tipe_parkir === 'member' ? $id_member : null;

            $db->begin_transaction();
            try {
                // Kunci baris area: check-in bersamaan di area yang sama diproses bergantian,
                // jadi kapasitas dan plat ganda dicek pada data yang paling baru.
                $kapasitas = 0;
                $stmt = $db->prepare("SELECT kapasitas FROM tb_area_parkir WHERE id_area = ? FOR UPDATE");
                if (!$stmt) {
                    throw new Exception($db->error);
                }
                $stmt->bind_param("i", $id_area);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                $stmt->bind_result($kapasitas);
                $stmt->fetch();
                $stmt->close();

                $terisi = 0;
                $stmt = $db->prepare("SELECT COUNT(*) FROM tb_parkir WHERE id_area = ? AND status = 'masuk'");
                if (!$stmt) {
                    throw new Exception($db->error);
                }
                $stmt->bind_param("i", $id_area);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                $stmt->bind_result($terisi);
                $stmt->fetch();
                $stmt->close();

                $sudah_parkir = 0;
                $stmt = $db->prepare("SELECT COUNT(*) FROM tb_parkir WHERE plat_nomor = ? AND status = 'masuk'");
                if (!$stmt) {
                    throw new Exception($db->error);
                }
                $stmt->bind_param("s", $plat_nomor);
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                $stmt->bind_result($sudah_parkir);
                $stmt->fetch();
                $stmt->close();

                if ($terisi >= $kapasitas) {
                    $db->rollback();
                    $pesan = "Area parkir penuh.";
                } elseif ($sudah_parkir > 0) {
                    $db->rollback();
                    $pesan = "Kendaraan dengan plat ini masih tercatat parkir.";
                } else {
                    $stmt = $db->prepare("INSERT INTO tb_parkir (id_member, id_area, tipe_parkir, plat_nomor, warna_kendaraan, jenis_kendaraan, id_tarif, waktu_masuk, id_user_masuk, status)
                                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'masuk')");
                    if (!$stmt) {
                        throw new Exception($db->error);
                    }
                    $stmt->bind_param("iissssii", $id_member_db, $id_area, $tipe_parkir, $plat_nomor, $warna_kendaraan, $jenis_kendaraan, $id_tarif, $id_user_masuk);
                    if (!$stmt->execute()) {
                        throw new Exception($stmt->error);
                    }
                    $stmt->close();

                    $stmt = $db->prepare("UPDATE tb_area_parkir a
                        SET a.terisi = (SELECT COUNT(*) FROM tb_parkir p
                                        WHERE p.id_area = a.id_area AND p.status = 'masuk')
                        WHERE a.id_area = ?");
                    if (!$stmt) {
                        throw new Exception($db->error);
                    }
                    $stmt->bind_param("i", $id_area);
                    if (!$stmt->execute()) {
                        throw new Exception($stmt->error);
                    }
                    $stmt->close();

                    $db->commit();
                    catat_log($db, $id_user_masuk, "Checkin " . ($tipe_parkir === 'member' ? "member" : "non-member") . ": " . $plat_nomor);
                    header("Location: index.php");
                    exit;
                }
            } catch (Exception $e) {
                $db->rollback();
                error_log('[checkin] ' . $e->getMessage());
                $pesan = "Gagal menyimpan data. Silakan coba lagi.";
            }
        }
    } else {
        $pesan = "Semua field harus diisi dengan benar! (plat: huruf/angka, 3-15 karakter)";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkin Kendaraan - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>

<body>
    <div class="app">
        <?php render_sidebar('Transaksi Parkir'); ?>

        <main class="main">
            <header class="topbar">
                <div class="page-title">Checkin Kendaraan</div>
                <div class="user-box">
                    <span><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                    <span class="role-tag">Petugas</span>
                </div>
            </header>

            <div class="content">
                <div class="form-box">
                    <h3>Form Checkin</h3>

                    <?php if (!empty($pesan)): ?>
                        <div class="alert error"><?= htmlspecialchars($pesan); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label>Member</label>
                            <select name="id_member" id="id_member">
                                <option value="0" <?= (($_POST['id_member'] ?? '0') === '0') ? 'selected' : ''; ?>>Non-member</option>
                                <?php foreach ($members_list as $m): ?>
                                    <option value="<?= $m['id_member']; ?>"
                                        data-area="<?= htmlspecialchars($m['nama_area']); ?>"
                                        <?= (($_POST['id_member'] ?? '') == $m['id_member']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($m['nama_member']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" id="grup-area-member" style="display: none;">
                            <label>Area (sesuai member)</label>
                            <input type="text" id="area_member" value="" readonly>
                        </div>
                        <div class="form-group" id="grup-area-tamu">
                            <label>Area Parkir (non-member)</label>
                            <select name="id_area" id="id_area">
                                <?php if (empty($areas_list)): ?>
                                    <option value="">-- Tidak ada area non-member yang tersedia --</option>
                                <?php else: ?>
                                    <?php foreach ($areas_list as $a): ?>
                                        <option value="<?= $a['id_area']; ?>" <?= (($_POST['id_area'] ?? '') == $a['id_area']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($a['nama_area']); ?> (sisa <?= $a['kapasitas'] - $a['terisi_aktual']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Plat Nomor</label>
                            <input type="text" name="plat_nomor" required placeholder="Contoh: B 1234 ABC" value="<?= htmlspecialchars($_POST['plat_nomor'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Warna Kendaraan</label>
                            <input type="text" name="warna_kendaraan" required placeholder="Contoh: Hitam" value="<?= htmlspecialchars($_POST['warna_kendaraan'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Jenis Kendaraan</label>
                            <select name="jenis_kendaraan" required>
                                <?php if (empty($jenis_options)): ?>
                                    <option value="">-- Belum ada data tarif --</option>
                                <?php else: ?>
                                    <?php foreach ($jenis_options as $j): ?>
                                        <option value="<?= htmlspecialchars($j); ?>" <?= (($_POST['jenis_kendaraan'] ?? '') === $j) ? 'selected' : ''; ?>><?= htmlspecialchars(ucfirst($j)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="btn-row" style="margin-bottom: 0; margin-top: 20px;">
                            <button type="submit" class="btn success">Checkin</button>
                            <a href="index.php" class="btn secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php include __DIR__ . '/../../includes/footer.php'; ?>
        </main>
    </div>
    <script>
        // Non-member: pilih area sendiri. Member: area mengikuti data member (hanya tampilan, server tetap memvalidasi).
        (function () {
            var sel = document.getElementById('id_member');
            var grupMember = document.getElementById('grup-area-member');
            var grupTamu = document.getElementById('grup-area-tamu');
            var areaMember = document.getElementById('area_member');
            var areaTamu = document.getElementById('id_area');

            function update() {
                var tamu = sel.value === '0';
                grupTamu.style.display = tamu ? '' : 'none';
                grupMember.style.display = tamu ? 'none' : '';
                areaTamu.disabled = !tamu;
                areaTamu.required = tamu;
                if (!tamu) {
                    areaMember.value = sel.options[sel.selectedIndex].getAttribute('data-area') || '';
                }
            }
            sel.addEventListener('change', update);
            update();
        })();
    </script>
</body>

</html>
