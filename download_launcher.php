<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
require_once 'site_config.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isLoggedIn()) { header('Location: /login'); exit; }

$user = getUser($pdo, $_SESSION['user_id']);
if (!$user) { session_destroy(); header('Location: /login'); exit; }

$launcher_url = '/launcher.exe';
$launcher_version = '1.0.0';
try {
    $stmt = $pdo->query("SELECT * FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $v = $stmt->fetch();
    if ($v) {
        $launcher_url = $v['url_launcher'] ?? $launcher_url;
        $launcher_version = $v['version'] ?? $launcher_version;
    }
} catch (PDOException $e) {}

header('Location: ' . $launcher_url);
exit;
