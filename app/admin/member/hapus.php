<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header("Location: index.php");
    exit;
}

$id_member = intval($_POST['id']);

// Ambil nama dulu supaya bisa dicatat di log (datanya hilang setelah dihapus).
$stmt = $db->prepare("SELECT nama_member FROM tb_member WHERE id_member = ?");
if (!$stmt) {
    error_log('[member-hapus] ' . $db->error);
    echo "<script>alert('Gagal menghapus data member!'); window.location.href = 'index.php';</script>";
    exit;
}
$stmt->bind_param("i", $id_member);
$stmt->execute();
$row = $stmt->get_result()->fetch_object();
$stmt->close();

if (!$row) {
    echo "<script>alert('Member tidak ditemukan!'); window.location.href = 'index.php';</script>";
    exit;
}

// Hapus hanya jika tidak ada kendaraan yang masih di dalam. Cek dan hapus
// dijalankan dalam SATU query supaya tidak ada celah antara cek dan hapus.
// Riwayat tb_parkir (status 'keluar') tetap ada; id_member-nya menjadi NULL
// (FK ON DELETE SET NULL). Struk tetap aman karena ada snapshot di tb_transaksi.
$stmt = $db->prepare("DELETE FROM tb_member
                      WHERE id_member = ?
                        AND NOT EXISTS (SELECT 1 FROM tb_parkir
                                        WHERE id_member = ? AND status = 'masuk')");
if (!$stmt) {
    error_log('[member-hapus] ' . $db->error);
    echo "<script>alert('Gagal menghapus data member!'); window.location.href = 'index.php';</script>";
    exit;
}
$stmt->bind_param("ii", $id_member, $id_member);
$ok = $stmt->execute();
$terhapus = $stmt->affected_rows;
$stmt->close();

if ($ok && $terhapus === 1) {
    catat_log($db, $_SESSION['id_user'], "Hapus member #$id_member ({$row->nama_member})");
    echo "<script>alert('Data member berhasil dihapus!'); window.location.href = 'index.php';</script>";
} elseif ($ok) {
    echo "<script>alert('Tidak bisa menghapus, member ini masih punya kendaraan yang belum checkout!'); window.location.href = 'index.php';</script>";
} else {
    error_log('[member-hapus] ' . $db->error);
    echo "<script>alert('Gagal menghapus data member!'); window.location.href = 'index.php';</script>";
}
