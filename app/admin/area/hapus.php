<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id_area = intval($_POST['id'] ?? 0);

    $stmt = $db->prepare("SELECT COUNT(*) AS jumlah
                           FROM tb_parkir p
                           WHERE p.id_area = ? AND p.status = 'masuk'");
    $stmt->bind_param("i", $id_area);
    $stmt->execute();
    $cek = $stmt->get_result()->fetch_object();
    $stmt->close();

    if ($cek->jumlah > 0) {
        echo "<script>
                alert('Tidak bisa menghapus, masih ada kendaraan yang belum checkout di area ini!');
                window.location.href = 'index.php';
              </script>";
        exit;
    }

    $stmt = $db->prepare("SELECT COUNT(*) AS jumlah FROM tb_member WHERE id_area = ?");
    $stmt->bind_param("i", $id_area);
    $stmt->execute();
    $cek_member = $stmt->get_result()->fetch_object();
    $stmt->close();

    if ($cek_member->jumlah > 0) {
        echo "<script>
            alert('Tidak bisa menghapus, area ini masih punya member!');
            window.location.href = 'index.php';
          </script>";
        exit;
    }

    $stmt = $db->prepare("DELETE FROM tb_area_parkir WHERE id_area = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_area);

        if ($stmt->execute()) {
            catat_log($db, $_SESSION['id_user'], "Hapus area parkir #$id_area");
            echo "<script>
                    alert('Data area parkir berhasil dihapus!');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data!');
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