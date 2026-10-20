<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['id_user']) && !empty($_SESSION['role'])) {
    header("Location: " . BASE_URL . "/" . $_SESSION['role'] . "/index.php");
} else {
    header("Location: " . BASE_URL . "/login.php");
}
exit;
