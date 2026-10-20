<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id_user = intval($_POST['id'] ?? 0);

    $stmt = $db->prepare("SELECT COUNT(*) AS jumlah FROM tb_parkir WHERE id_user_masuk = ? AND status = 'masuk'");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $cek = $stmt->get_result()->fetch_object();
    $stmt->close();

    if ($cek->jumlah > 0) {
        echo "<script>
                alert('Tidak bisa menghapus, petugas ini masih punya kendaraan yang belum checkout!');
                window.location.href = 'index.php';
              </script>";
        exit;
    }

    $stmt = $db->prepare("UPDATE tb_user SET status_aktif = 0 WHERE id_user = ? AND role = 'petugas'");
    if ($stmt) {
        $stmt->bind_param("i", $id_user);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            catat_log($db, $_SESSION['id_user'], "Nonaktif user petugas #$id_user");
            echo "<script>
                    alert('Data user berhasil dinonaktifkan!');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data (akun tidak ditemukan, sudah dinonaktifkan atau bukan petugas)!');
                    window.location.href = 'index.php';
                  </script>";
        }
        $stmt->close();
    } else {
        error_log('[hapus] ' . $db->error);
        echo "<script>
                alert('Gagal memproses permintaan!');
                window.location.href = 'index.php';
              </script>";
    }
} else {
    header("Location: index.php");
    exit;
}
?>