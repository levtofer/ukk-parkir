<?php
// Helper autentikasi berbasis PHP session.

// Atur cookie session sebelum session_start() supaya lebih aman.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,                     // cookie tidak bisa dibaca JavaScript
        'samesite' => 'Lax',                    // mengurangi risiko CSRF
        'secure'   => !empty($_SERVER['HTTPS']) // hanya kirim lewat HTTPS jika tersedia
    ]);
    session_start();
}

/**
 * Hapus session sepenuhnya, termasuk data dan cookie-nya.
 */
function hancurkan_sesi()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $p['path'],
            $p['domain'],
            $p['secure'],
            $p['httponly']
        );
    }

    session_destroy();
}

/**
 * Arahkan ke halaman login.
 */
function redirect_login()
{
    $base = defined('BASE_URL') ? BASE_URL : '';
    header("Location: " . $base . "/login.php");
    exit;
}

/**
 * Pastikan user sudah login dan rolenya sesuai.
 * Role dan status diambil langsung dari database, bukan dari session,
 * supaya perubahan role oleh admin langsung berlaku.
 *
 * @param string|array $roles Role yang diizinkan, mis. 'admin' atau ['admin','petugas']
 * @param mysqli|null  $db    Koneksi database. Jika kosong, pakai variabel global $db.
 */
function require_role($roles, $db = null)
{
    $roles = (array) $roles;

    if ($db === null) {
        global $db;
    }

    if (empty($_SESSION['id_user'])) {
        redirect_login();
    }

    if (!$db) {
        error_log('[auth] Koneksi database tidak tersedia di require_role()');
        http_response_code(500);
        exit('Terjadi kesalahan pada server.');
    }

    $stmt = $db->prepare("SELECT role, status_aktif FROM tb_user WHERE id_user = ?");
    if (!$stmt) {
        error_log('[auth] Prepare gagal: ' . $db->error);
        http_response_code(500);
        exit('Terjadi kesalahan pada server.');
    }

    $id = (int) $_SESSION['id_user'];
    $role_db = null;
    $status_db = null;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    // bind_result dipakai (bukan get_result) supaya tidak butuh driver mysqlnd.
    $stmt->bind_result($role_db, $status_db);
    $ada = $stmt->fetch();
    $stmt->close();

    // User tidak ditemukan atau sudah dinonaktifkan.
    if (!$ada || (int) $status_db !== 1) {
        hancurkan_sesi();
        redirect_login();
    }

    // Sinkronkan role di session dengan role terbaru dari database.
    $_SESSION['role'] = $role_db;

    // Role tidak sesuai: tampilkan 403 (bukan redirect) agar tidak terjadi redirect loop.
    if (!in_array($role_db, $roles, true)) {
        http_response_code(403);
        exit('Anda tidak memiliki akses ke halaman ini.');
    }
}

/**
 * Catat satu baris ke tb_log_aktivitas. Panggil setelah $db tersedia.
 */
function catat_log($db, $id_user, $aktivitas)
{
    $stmt = $db->prepare("INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
    if (!$stmt) {
        error_log('[log] Prepare gagal: ' . $db->error);
        return;
    }

    $stmt->bind_param("is", $id_user, $aktivitas);
    if (!$stmt->execute()) {
        error_log('[log] Gagal mencatat aktivitas: ' . $stmt->error);
    }
    $stmt->close();
}
