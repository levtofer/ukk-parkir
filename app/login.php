<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';

// Sudah login? Arahkan ke dashboard, jangan logout paksa.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !empty($_SESSION['id_user']) && !empty($_SESSION['role'])) {
    header("Location: " . BASE_URL . "/" . $_SESSION['role'] . "/index.php");
    exit;
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION = [];
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && $password !== '') {
        $stmt = $db->prepare("SELECT * FROM tb_user WHERE username = ? AND status_aktif = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_object();
        $stmt->close();

        if ($user && password_verify($password, $user->password)) {
            session_regenerate_id(true);
            $_SESSION['id_user'] = $user->id_user;
            $_SESSION['nama_lengkap'] = $user->nama_lengkap;
            $_SESSION['role'] = $user->role;

            catat_log($db, $user->id_user, "Login sebagai " . $user->role);

            header("Location: " . BASE_URL . "/" . $user->role . "/index.php");
            exit;
        } else {
            $pesan = "Username atau password salah!";
        }
    } else {
        $pesan = "Username dan password harus diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Parkir</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.ico" type="image/x-icon">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Login Aplikasi Parkir</h2>

            <?php if (!empty($pesan)): ?>
                <div class="alert error"><?= htmlspecialchars($pesan); ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn lg" style="width: 100%;">Login</button>
            </form>
        </div>
    </div>
</body>

</html>