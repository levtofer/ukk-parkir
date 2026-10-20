<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id_tarif = intval($_POST['id'] ?? 0);

    // Block deletion ONLY if an active vehicle ('masuk') is currently assigned to this tariff
    $stmt = $db->prepare("SELECT COUNT(*) AS jumlah FROM tb_parkir WHERE id_tarif = ? AND status = 'masuk'");
    $stmt->bind_param("i", $id_tarif);
    $stmt->execute();
    $cek = $stmt->get_result()->fetch_object();
    $stmt->close();

    if ($cek->jumlah > 0) {
        echo "<script>
                alert('Tidak bisa menghapus, masih ada kendaraan yang belum checkout dengan tarif ini!');
                window.location.href = 'index.php';
              </script>";
        exit;
    }

    $stmt = $db->prepare("DELETE FROM tb_tarif WHERE id_tarif = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_tarif);

        if ($stmt->execute()) {
            catat_log($db, $_SESSION['id_user'], "Hapus tarif #$id_tarif");
            echo "<script>
                    alert('Data tarif berhasil dihapus!');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus data! Pastikan Foreign Key database mengizinkan penghapusan.');
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