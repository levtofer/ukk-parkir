<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "db_parkir";

mysqli_report(MYSQLI_REPORT_OFF);

$db = new mysqli($host, $username, $password, $dbname);

if ($db->connect_error) {
    die("Koneksi database gagal: " . $db->connect_error);
}

date_default_timezone_set('Asia/Jakarta');
$db->query("SET time_zone = '+07:00'");

if (!defined('BASE_URL')) {
    $app_root_dir = str_replace('\\', '/', dirname(__DIR__));
    $script_file = $_SERVER['SCRIPT_FILENAME'] ?? '';
    $script_dir = $script_file ? str_replace('\\', '/', dirname(realpath($script_file) ?: $script_file)) : $app_root_dir;

    $depth = 0;
    if (strpos($script_dir, $app_root_dir) === 0) {
        $rel = trim(substr($script_dir, strlen($app_root_dir)), '/');
        $depth = ($rel === '') ? 0 : substr_count($rel, '/') + 1;
    }

    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    $url_dir_parts = explode('/', trim(dirname($script_name), '/'));
    if ($url_dir_parts === ['']) {
        $url_dir_parts = [];
    }
    $base_parts = $depth > 0 ? array_slice($url_dir_parts, 0, max(0, count($url_dir_parts) - $depth)) : $url_dir_parts;

    define('BASE_URL', $base_parts ? '/' . implode('/', $base_parts) : '');
}

?>