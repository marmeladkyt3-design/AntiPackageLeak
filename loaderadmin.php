<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';

if (!isLoggedIn()) redirect('/login');
$current_user = getUser($pdo, $_SESSION['user_id']);
if (!userHasRight($pdo, $current_user, 'can_admin')) redirect('/');

$csrf = $_SESSION['csrf'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($csrf, $_POST['csrf'] ?? '')) {
    http_response_code(403);
    die('Неверный CSRF-токен');
}
foreach (['delete_version'] as $ga) {
    if (isset($_GET[$ga]) && !hash_equals($csrf, $_GET['csrf'] ?? '')) {
        http_response_code(403);
        die('Неверный CSRF-токен');
    }
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `launcher_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(100) NOT NULL,
        `setting_value` text NOT NULL,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `launcher_versions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `version` varchar(50) NOT NULL,
        `download_url` text NOT NULL,
        `url_game_jar` text NOT NULL,
        `url_libs` text NOT NULL,
        `url_assets` text NOT NULL,
        `url_java` text NOT NULL,
        `url_natives` text NOT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmt = $pdo->query("SELECT COUNT(*) FROM launcher_settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `launcher_settings` (`setting_key`, `setting_value`) VALUES
            ('maintenance_mode', '0'),
            ('maintenance_message', 'Launcher is under maintenance.'),
            ('launcher_download_url', ''),
            ('current_version', '1.0.0'),
            ('discord_link', ''),
            ('telegram_link', ''),
            ('youtube_link', ''),
            ('site_url', 'https://antiaileaks.ct.ws')");
    }

    $pdo->exec("INSERT IGNORE INTO `launcher_settings` (`setting_key`, `setting_value`) VALUES
        ('java_download_url', ''),
        ('java_download_hash', ''),
        ('global_assets_url', ''),
        ('global_assets_hash', ''),
        ('maintenance_bypass_roles', 'admin')");

    $cols = $pdo->query("SHOW COLUMNS FROM launcher_versions LIKE 'url_game_jar'");
    if ($cols->rowCount() == 0) {
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `url_game_jar` text NOT NULL DEFAULT '' AFTER `download_url`");
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `url_libs` text NOT NULL DEFAULT '' AFTER `url_game_jar`");
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `url_assets` text NOT NULL DEFAULT '' AFTER `url_libs`");
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `url_java` text NOT NULL DEFAULT '' AFTER `url_assets`");
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `url_natives` text NOT NULL DEFAULT '' AFTER `url_java`");
    }

    $cols2 = $pdo->query("SHOW COLUMNS FROM launcher_versions LIKE 'install_dir'");
    if ($cols2->rowCount() == 0) {
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `install_dir` varchar(100) NOT NULL DEFAULT 'PouchCrack' AFTER `url_natives`");
    }

    $cols3 = $pdo->query("SHOW COLUMNS FROM launcher_versions LIKE 'hash_game_jar'");
    if ($cols3->rowCount() == 0) {
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `hash_game_jar` varchar(64) NOT NULL DEFAULT '' AFTER `install_dir`");
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `hash_libs` varchar(64) NOT NULL DEFAULT '' AFTER `hash_game_jar`");
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `hash_assets` varchar(64) NOT NULL DEFAULT '' AFTER `hash_libs`");
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `hash_java` varchar(64) NOT NULL DEFAULT '' AFTER `hash_assets`");
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `hash_natives` varchar(64) NOT NULL DEFAULT '' AFTER `hash_java`");
    }

    $cols4 = $pdo->query("SHOW COLUMNS FROM launcher_versions LIKE 'crypto_key'");
    if ($cols4->rowCount() == 0) {
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `crypto_key` text NOT NULL DEFAULT '' AFTER `hash_natives`");
    }

    $cols5 = $pdo->query("SHOW COLUMNS FROM launcher_versions LIKE 'url_fake_jar'");
    if ($cols5->rowCount() == 0) {
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `url_fake_jar` text NOT NULL DEFAULT '' AFTER `crypto_key`");
    }

    $cols6 = $pdo->query("SHOW COLUMNS FROM launcher_versions LIKE 'required_role'");
    if ($cols6->rowCount() == 0) {
        $pdo->exec("ALTER TABLE launcher_versions ADD COLUMN `required_role` varchar(50) NOT NULL DEFAULT '' AFTER `install_dir`");
    }

    $defaults = [
        'launcher_build_min'    => '1',
        'launcher_force_update' => '0',
        'launcher_update_url'   => 'https://antiaileaks.ct.ws/download_launcher.php',
        'launcher_update_msg'   => 'Доступна новая версия лаунчера. Обновите для продолжения.',
    ];
    foreach ($defaults as $k => $v) {
        $chk = $pdo->prepare("SELECT setting_value FROM launcher_settings WHERE setting_key = ? LIMIT 1");
        $chk->execute([$k]);
        if (!$chk->fetch()) {
            $ins = $pdo->prepare("INSERT INTO launcher_settings (setting_key, setting_value) VALUES (?, ?)");
            $ins->execute([$k, $v]);
        }
    }
} catch (PDOException $e) {}

function computeHashFromUrl($url) {
    return '';
}

$msg = '';
$msg_type = '';

if (isset($_GET['delete_version'])) {
    $id = (int)$_GET['delete_version'];
    $pdo->prepare("DELETE FROM launcher_versions WHERE id = ?")->execute([$id]);
    $_SESSION['admin_msg'] = 'Версия удалена';
    $_SESSION['admin_msg_type'] = 'success';
    header('Location: /loaderadmin?tab=versions');
    exit;
}

if (isset($_SESSION['admin_msg'])) {
    $msg = $_SESSION['admin_msg'];
    $msg_type = $_SESSION['admin_msg_type'];
    unset($_SESSION['admin_msg'], $_SESSION['admin_msg_type']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['save_settings'])) {
        $fields = ['maintenance_mode', 'maintenance_message', 'launcher_download_url', 'current_version',
                    'discord_link', 'telegram_link', 'youtube_link', 'site_url',
                    'java_download_url', 'java_download_hash',
                    'global_assets_url', 'global_assets_hash', 'maintenance_bypass_roles',
                    'launcher_build_min', 'launcher_update_url', 'launcher_update_msg'];
        foreach ($fields as $f) {
            $val = ($f === 'maintenance_mode') ? (isset($_POST[$f]) ? '1' : '0') : ($_POST[$f] ?? '');
            $pdo->prepare("UPDATE launcher_settings SET setting_value = ? WHERE setting_key = ?")->execute([$val, $f]);
        }
        $forceVal = isset($_POST['launcher_force_update']) ? '1' : '0';
        $pdo->prepare("UPDATE launcher_settings SET setting_value = ? WHERE setting_key = 'launcher_force_update'")->execute([$forceVal]);
        $msg = 'Настройки сохранены';
        $msg_type = 'success';
    }

    if (isset($_POST['add_version'])) {
        $version = trim($_POST['version'] ?? '');
        $download_url = trim($_POST['download_url'] ?? '');
        $url_game_jar = trim($_POST['url_game_jar'] ?? '');
        $url_libs = trim($_POST['url_libs'] ?? '');
        $url_natives = trim($_POST['url_natives'] ?? '');
        $install_dir = trim($_POST['install_dir'] ?? '');
        if (empty($install_dir)) $install_dir = preg_replace('/[^a-zA-Z0-9]/', '', $version);
        $url_fake_jar = trim($_POST['url_fake_jar'] ?? '');
        $hash_game_jar = trim($_POST['hash_game_jar'] ?? '');
        $hash_libs = trim($_POST['hash_libs'] ?? '');
        $hash_natives = trim($_POST['hash_natives'] ?? '');
        $crypto_key = trim($_POST['crypto_key'] ?? '');
        $required_role = trim($_POST['required_role'] ?? '');

        if (empty($hash_game_jar) && !empty($url_game_jar)) $hash_game_jar = computeHashFromUrl($url_game_jar);
        if (empty($hash_libs) && !empty($url_libs)) $hash_libs = computeHashFromUrl($url_libs);
        if (empty($hash_natives) && !empty($url_natives)) $hash_natives = computeHashFromUrl($url_natives);

        if (empty($version)) {
            $msg = 'Введите номер версии';
            $msg_type = 'error';
        } else {
            $check = $pdo->prepare("SELECT id FROM launcher_versions WHERE version = ?");
            $check->execute([$version]);
            if ($check->fetch()) {
                $msg = 'Такая версия уже существует';
                $msg_type = 'error';
            } else {
                $stmt = $pdo->prepare("INSERT INTO launcher_versions (version, download_url, url_game_jar, url_libs, url_natives, url_fake_jar, install_dir, required_role, hash_game_jar, hash_libs, hash_natives, crypto_key) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$version, $download_url, $url_game_jar, $url_libs, $url_natives, $url_fake_jar, $install_dir, $required_role, $hash_game_jar, $hash_libs, $hash_natives, $crypto_key]);
                $msg = 'Версия добавлена (хэши: ' . ($hash_game_jar ? 'OK' : '—') . ')';
                $msg_type = 'success';
            }
        }
    }

    if (isset($_POST['edit_version'])) {
        $id = (int)$_POST['version_id'];
        $version = trim($_POST['version'] ?? '');
        $download_url = trim($_POST['download_url'] ?? '');
        $url_game_jar = trim($_POST['url_game_jar'] ?? '');
        $url_libs = trim($_POST['url_libs'] ?? '');
        $url_natives = trim($_POST['url_natives'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $install_dir = trim($_POST['install_dir'] ?? '');
        if (empty($install_dir)) $install_dir = preg_replace('/[^a-zA-Z0-9]/', '', $version);
        $url_fake_jar = trim($_POST['url_fake_jar'] ?? '');
        $hash_game_jar = trim($_POST['hash_game_jar'] ?? '');
        $hash_libs = trim($_POST['hash_libs'] ?? '');
        $hash_natives = trim($_POST['hash_natives'] ?? '');
        $crypto_key = trim($_POST['crypto_key'] ?? '');
        $required_role = trim($_POST['required_role'] ?? '');

        if (empty($hash_game_jar) && !empty($url_game_jar)) $hash_game_jar = computeHashFromUrl($url_game_jar);
        if (empty($hash_libs) && !empty($url_libs)) $hash_libs = computeHashFromUrl($url_libs);
        if (empty($hash_natives) && !empty($url_natives)) $hash_natives = computeHashFromUrl($url_natives);

        if (empty($version)) {
            $msg = 'Введите номер версии';
            $msg_type = 'error';
        } else {
            $stmt = $pdo->prepare("UPDATE launcher_versions SET version=?, download_url=?, url_game_jar=?, url_libs=?, url_natives=?, url_fake_jar=?, is_active=?, install_dir=?, required_role=?, hash_game_jar=?, hash_libs=?, hash_natives=?, crypto_key=? WHERE id=?");
            $stmt->execute([$version, $download_url, $url_game_jar, $url_libs, $url_natives, $url_fake_jar, $is_active, $install_dir, $required_role, $hash_game_jar, $hash_libs, $hash_natives, $crypto_key, $id]);
            $msg = 'Версия обновлена (хэши: ' . ($hash_game_jar ? 'OK' : '—') . ')';
            $msg_type = 'success';
        }
    }
}

$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM launcher_settings");
while ($row = $stmt->fetch()) $settings[$row['setting_key']] = $row['setting_value'];

$versions = $pdo->query("SELECT * FROM launcher_versions ORDER BY id DESC")->fetchAll();

$tab = $_GET['tab'] ?? 'settings';
$avatar_url = $current_user['avatar_url'] ?? null;
$site_name = htmlspecialchars($SITE_NAME ?? 'Placeholder');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.gstatic.com/s/inter/v19/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hjp-Ek-_EeA.woff2">
    <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.gstatic.com/s/sora/v20/BMgS_f-qkpgTSE9BHNk.woff2">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://antiaileaks.ct.ws">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#08080f">
    <title>Управление лаунчером — <?php echo $site_name; ?></title>
<style>
*,*::before,*::after{-webkit-user-select:none!important;-moz-user-select:none!important;-ms-user-select:none!important;user-select:none!important}
input,textarea{-webkit-user-select:text!important;-moz-user-select:text!important;-ms-user-select:text!important;user-select:text!important}
</style>
<script>
document.addEventListener('keydown',function(e){if(e.key==='F12'||(e.ctrlKey&&e.shiftKey&&(e.key==='I'||e.key==='i'||e.key==='J'||e.key==='j'||e.key==='C'||e.key==='c'))||(e.ctrlKey&&e.key==='u')){e.preventDefault();e.stopPropagation();return false}},true);
document.addEventListener('contextmenu',function(e){e.preventDefault();return false});
document.addEventListener('copy',function(e){e.preventDefault();return false});
document.addEventListener('cut',function(e){e.preventDefault();return false});
document.addEventListener('selectstart',function(e){if(e.target.tagName!=='INPUT'&&e.target.tagName!=='TEXTAREA'){e.preventDefault();return false}});
</script>

    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{--bg:<?php echo $C['bg']; ?>;--bg2:<?php echo $C['bg2']; ?>;--panel:<?php echo $C['panel']; ?>;--panel-h:<?php echo $C['panel_h']; ?>;--line:<?php echo $C['line']; ?>;--line2:<?php echo $C['line2']; ?>;--accent:<?php echo $C['accent']; ?>;--accent-rgb:<?php echo $C['accent_rgb']; ?>;--grad1:<?php echo $C['grad1']; ?>;--grad2:<?php echo $C['grad2']; ?>;--grad3:<?php echo $C['grad3']; ?>;--ink:<?php echo $C['ink']; ?>;--ink2:<?php echo $C['ink2']; ?>;--dim:<?php echo $C['dim']; ?>;--faint:<?php echo $C['faint']; ?>;--success:<?php echo $C['success']; ?>;--danger:<?php echo $C['danger']; ?>;--grad:linear-gradient(135deg,var(--grad1),var(--grad2) 55%,var(--grad3));--accent-light:<?php echo $C['accent_light']; ?>;--accent-dark:<?php echo $C['accent_dark']; ?>;--pink:<?php echo $C['pink']; ?>;--indigo:#6366f1;--purple:#8b5cf6;--violet:#a78bfa;--r:18px;--r-sm:12px;--r-lg:26px}
*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg) url('assets/img/background.jpg') center/cover no-repeat fixed;color:var(--ink2);line-height:1.65;overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:9px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:#232a44;border-radius:9px;border:2px solid var(--bg)}
a{color:inherit;text-decoration:none}button{font-family:inherit;background:none;border:none;cursor:pointer;color:inherit}img{max-width:100%;display:block}
.grad{background:var(--grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
.container{width:min(100% - 44px,1180px);margin:0 auto}
.bg-fx{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;background-image: url('assets/img/background.jpg');background-size:cover;background-position:center}
.bg-fx::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,0.5);-webkit-backdrop-filter:blur(16px) saturate(1.2);backdrop-filter:blur(16px) saturate(1.2)}
.shader-orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.shader-orbs .orb{position:absolute;border-radius:50%;filter:blur(90px);mix-blend-mode:screen;will-change:transform}
.orb--1{width:380px;height:380px;background:radial-gradient(circle,rgba(90,99,232,0.45) 0%,transparent 70%);top:-10%;left:-8%;animation:o1 20s ease-in-out infinite alternate}
.orb--2{width:300px;height:300px;background:radial-gradient(circle,rgba(139,92,246,0.38) 0%,transparent 70%);bottom:-12%;right:-6%;animation:o2 24s ease-in-out infinite alternate}
.orb--3{width:240px;height:240px;background:radial-gradient(circle,rgba(168,85,247,0.32) 0%,transparent 70%);top:35%;left:50%;animation:o3 17s ease-in-out infinite alternate}
.orb--4{width:180px;height:180px;background:radial-gradient(circle,rgba(74,82,208,0.28) 0%,transparent 70%);top:12%;right:18%;animation:o4 22s ease-in-out infinite alternate}
@keyframes o1{0%{transform:translate(0,0) scale(1)}33%{transform:translate(70px,50px) scale(1.08)}66%{transform:translate(-40px,90px) scale(0.94)}100%{transform:translate(50px,30px) scale(1.04)}}
@keyframes o2{0%{transform:translate(0,0) scale(1)}33%{transform:translate(-60px,-35px) scale(1.06)}66%{transform:translate(50px,-70px) scale(0.9)}100%{transform:translate(-25px,-45px) scale(1)}}
@keyframes o3{0%{transform:translate(0,0) scale(1)}50%{transform:translate(-80px,35px) scale(1.12)}100%{transform:translate(35px,-55px) scale(0.88)}}
@keyframes o4{0%{transform:translate(0,0) scale(0.9)}40%{transform:translate(45px,55px) scale(1.08)}100%{transform:translate(-55px,-25px) scale(0.94)}}
.bg-fx .halo{position:absolute;border-radius:50%;pointer-events:none}
.bg-fx .halo--a{width:640px;height:640px;background:radial-gradient(circle at center,rgba(99,102,241,0.13) 0%,transparent 62%);top:-260px;right:-160px}
.bg-fx .halo--b{width:560px;height:560px;background:radial-gradient(circle at center,rgba(139,92,246,0.10) 0%,transparent 62%);bottom:-240px;left:-180px}
.bg-fx .halo--c{width:400px;height:400px;background:radial-gradient(circle at center,rgba(232,121,249,0.05) 0%,transparent 62%);top:42%;left:62%}
.bg-fx .veil{position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.35'/%3E%3C/svg%3E");opacity:0.02}
.header,.header.nav{position:sticky;top:0;z-index:100;padding:1.25rem 1.5rem;background:transparent;border-bottom:none}
.header-content{position:relative;display:flex;align-items:center;justify-content:center;gap:1.4rem;max-width:1060px;margin:0 auto;background:rgba(255,255,255,0.05);-webkit-backdrop-filter:blur(30px);backdrop-filter:blur(30px);border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:0.7rem 1.1rem;min-height:60px;box-shadow:0 18px 44px -20px rgba(0,0,0,0.55)}
.header-brand-link{position:absolute;left:1rem;display:inline-flex;align-items:center;gap:0.55rem;min-height:2.25rem;flex-shrink:0;color:inherit;text-decoration:none;max-width:160px}
.header-logo{display:flex;align-items:center;justify-content:center;width:30px;height:30px;flex-shrink:0;filter:none}
.header-logo img{width:100%;height:100%;object-fit:contain;display:block}
.header-brand{font-family:'Sora',sans-serif;font-size:1.05rem;font-weight:700;letter-spacing:-0.02em;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.brand-shine{position:relative;display:inline-block;color:#fff;-webkit-text-fill-color:#ffffff;text-shadow:0 0 8px rgba(255,255,255,0.18);isolation:isolate}
.brand-shine:before{content:attr(data-text);position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none;background-image:linear-gradient(100deg,transparent 0%,transparent 35%,var(--accent,#68aeff) 50%,transparent 65%,transparent 100%);background-size:220% 100%;background-position:140% 0;background-repeat:no-repeat;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;animation:brandShine 4s ease-in-out infinite;}
@keyframes brandShine{0%{background-position:140% 0}55%,to{background-position:-40% 0}}
.header-nav{display:flex;align-items:center;gap:0.15rem;transform:translateX(-3rem)}
.header-nav a{display:inline-flex;align-items:center;gap:0.34rem;font-family:'Inter',sans-serif;font-size:0.77rem;font-weight:500;color:var(--dim);text-decoration:none;line-height:1;white-space:nowrap;padding:6px 11px;border-radius:11px;transition:color 0.18s ease,background 0.18s ease}
.header-nav a:hover{color:#fff;background:var(--panel)}
.header-nav a.active{color:#fff;background:rgba(var(--accent-rgb),0.12)}
.header-actions{position:absolute;right:1rem;display:flex;align-items:center;gap:0.45rem}
.header-action{display:inline-flex;align-items:center;justify-content:center;gap:0.38rem;min-height:2.25rem;padding:0 0.8rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;background:rgba(255,255,255,0.04);color:#fff;-webkit-text-fill-color:#ffffff;font-family:'Inter',sans-serif;font-size:0.82rem;font-weight:500;line-height:1;text-decoration:none;white-space:nowrap;cursor:pointer;transition:background 0.18s ease,border-color 0.18s ease}
.header-action:hover{border-color:rgba(255,255,255,0.13);background:rgba(255,255,255,0.09)}
.header-action--profile{padding-left:0.45rem}
.header-avatar{width:22px;height:22px;border-radius:50%;object-fit:cover;flex-shrink:0;pointer-events:none}
.header-action i{font-size:0.8rem}
.burger{display:none;width:42px;height:42px;border:1px solid rgba(255,255,255,0.1);border-radius:12px;align-items:center;justify-content:center;font-size:15px;color:#fff}
.m-menu{display:none;flex-direction:column;gap:4px;padding:12px 18px 20px;border-bottom:1px solid rgba(255,255,255,0.06)}
.m-menu a{padding:12px 14px;border-radius:12px;font-size:14px;font-weight:500;color:rgba(255,255,255,0.5);transition:all 0.18s}
.m-menu a.active,.m-menu a:hover{background:rgba(255,255,255,0.05);color:#fff}
.header.open .m-menu{display:flex}
@media(max-width:900px){.header-nav{display:none}.header-content{justify-content:space-between}.header-brand-link{position:static;left:auto}.header-actions{position:static;right:auto}.burger{display:inline-flex}}
@media(max-width:768px){.header,.header.nav{padding:0.9rem 1rem}.header-content{border-radius:16px}.header-brand-link{max-width:120px}}
@media(max-width:520px){.header-action span{display:none}.header-action{width:2.25rem;padding:0;justify-content:center}.header-brand{font-size:0.9rem}}
.page{padding:20px 0 100px}.page .container{max-width:1200px}
.page-head{margin-bottom:30px;padding-top:20px}.page-head.center{text-align:center}
.page-head h1{font-family:'Sora',sans-serif;font-size:clamp(24px,3.5vw,34px);font-weight:700;letter-spacing:-0.02em;color:#fff}
.page-head h1 i{color:var(--accent);margin-right:8px}
.page-head p{font-size:14px;color:var(--dim);margin-top:8px}
.alert{padding:14px 18px;border-radius:12px;font-size:13.5px;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.alert-success{border:1px solid rgba(16,185,129,0.3);background:rgba(16,185,129,0.06);color:#6ee7b7}
.alert-error{border:1px solid rgba(248,113,113,0.3);background:rgba(248,113,113,0.06);color:#fca5a5}

.tabs{position:relative;display:flex;gap:0;margin-bottom:24px;padding:4px;border-radius:16px;border:1px solid var(--line);background:var(--panel);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px)}
.tab-indicator{position:absolute;top:4px;bottom:4px;border-radius:12px;background:rgba(var(--accent-rgb),0.15);transition:left 0.35s cubic-bezier(0.22,1,0.36,1),width 0.35s cubic-bezier(0.22,1,0.36,1);pointer-events:none;z-index:0}
.tab{position:relative;z-index:1;display:inline-flex;align-items:center;gap:7px;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;color:var(--dim);text-decoration:none;transition:color 0.25s ease;background:transparent}
.tab:hover{color:#fff}
.tab.active{color:#fff}
.tab i{font-size:13px}

.card{padding:24px;border-radius:18px;border:1px solid var(--line);background:var(--panel);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px);margin-bottom:20px}
.card-title{font-family:'Sora',sans-serif;font-size:15px;font-weight:700;color:#fff;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.card-title i{color:var(--accent);font-size:14px}
.form-control{padding:10px 14px;border-radius:10px;border:1px solid var(--line2);background:rgba(255,255,255,0.03);color:#fff;font-family:'Inter',sans-serif;font-size:13px;outline:none;transition:border-color 0.25s,box-shadow 0.25s,background 0.25s}
.form-control:focus{border-color:rgba(var(--accent-rgb),0.5);box-shadow:0 0 0 3px rgba(var(--accent-rgb),0.08);background:rgba(255,255,255,0.04)}
select.form-control{cursor:pointer}
textarea.form-control{resize:vertical;min-height:80px}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12.5px;font-weight:600;color:var(--ink2);margin-bottom:6px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 18px;border-radius:10px;font-family:'Inter',sans-serif;font-size:12.5px;font-weight:600;border:none;cursor:pointer;transition:all 0.25s cubic-bezier(0.22,1,0.36,1);text-decoration:none}
.btn-primary{background:var(--grad);color:#fff;box-shadow:0 8px 24px -10px rgba(var(--accent-rgb),0.5);position:relative;overflow:hidden}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 28px -6px rgba(var(--accent-rgb),0.65)}
.btn-primary::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.12),transparent);transform:translateX(-100%);transition:transform 0.45s}
.btn-primary:hover::after{transform:translateX(100%)}
.btn-danger{color:#fca5a5;border:1px solid rgba(244,63,94,0.3);background:rgba(244,63,94,0.1)}
.btn-danger:hover{background:rgba(244,63,94,0.2);border-color:rgba(244,63,94,0.5);color:#fecaca;transform:translateY(-2px)}
.btn-success{color:#6ee7b7;border:1px solid rgba(16,185,129,0.3);background:rgba(16,185,129,0.1)}
.btn-success:hover{background:rgba(16,185,129,0.2);border-color:rgba(16,185,129,0.5);color:#a7f3d0;transform:translateY(-2px)}
.btn-warning{color:#fcd34d;border:1px solid rgba(245,158,11,0.3);background:rgba(245,158,11,0.1)}
.btn-warning:hover{background:rgba(245,158,11,0.2);border-color:rgba(245,158,11,0.5);color:#fde68a;transform:translateY(-2px)}
.btn-sm{padding:7px 14px;font-size:12px;border-radius:8px}
.btn-group{display:flex;gap:8px;flex-wrap:wrap}
.table-wrapper{overflow-x:auto;border-radius:12px;border:1px solid var(--line);margin-bottom:16px}
table{width:100%;border-collapse:collapse;font-size:13px}
thead{background:rgba(255,255,255,0.03)}
th{padding:12px 14px;text-align:left;font-weight:600;color:var(--ink2);border-bottom:1px solid var(--line);white-space:nowrap}
td{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.04);color:var(--ink2)}
tr:hover td{background:rgba(255,255,255,0.02)}
.badge{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:0.03em}
.badge-green{background:rgba(16,185,129,0.12);color:#6ee7b7;border:1px solid rgba(16,185,129,0.25)}
.badge-red{background:rgba(244,63,94,0.12);color:#fca5a5;border:1px solid rgba(244,63,94,0.25)}
.badge-yellow{background:rgba(245,158,11,0.12);color:#fcd34d;border:1px solid rgba(245,158,11,0.25)}
.badge-blue{background:rgba(99,102,241,0.12);color:#a5b4fc;border:1px solid rgba(99,102,241,0.25)}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:768px){.grid-2{grid-template-columns:1fr}}

.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(16px);z-index:2000;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:600;opacity:0;pointer-events:none;transition:all 0.3s cubic-bezier(0.22,1,0.36,1);box-shadow:0 14px 36px -10px rgba(0,0,0,0.7);display:flex;align-items:center;gap:8px}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0);pointer-events:auto}
.toast-success{color:#6ee7b7;background:rgba(16,185,129,0.16);border:1px solid rgba(16,185,129,0.4)}
.toast-error{color:#fca5a5;background:rgba(248,113,113,0.16);border:1px solid rgba(248,113,113,0.4)}

.overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.7);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);align-items:flex-end;justify-content:center}
.overlay.open{display:flex}
.overlay-panel{width:100%;max-width:600px;max-height:85vh;overflow-y:auto;background:rgba(12,12,23,0.98);border-radius:24px 24px 0 0;padding:32px;border:1px solid rgba(255,255,255,0.08);box-shadow:0 -20px 60px rgba(0,0,0,0.5);transform:translateY(100%);transition:transform 0.4s cubic-bezier(0.34,1.56,0.64,1)}
.overlay.open .overlay-panel{transform:translateY(0)}
.overlay-close{position:absolute;top:16px;right:16px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:var(--dim);background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.06);transition:all 0.2s;z-index:10}
.overlay-close:hover{color:#fff;background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.12);transform:rotate(90deg)}
.overlay-panel h3{font-family:'Sora',sans-serif;font-size:17px;font-weight:700;color:#fff;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.overlay-panel h3 i{color:var(--accent)}
.overlay-panel .sub{font-size:13px;color:var(--dim);margin-bottom:16px}
.overlay-panel .btn-group{margin-top:20px}
.overlay-panel .form-group label{font-size:12.5px;font-weight:600;color:var(--ink2);margin-bottom:6px}
.overlay-panel .form-group{margin-bottom:12px}

.reveal{opacity:0;transform:translateY(24px);transition:opacity 0.5s ease,transform 0.5s ease}
.reveal.in{opacity:1;transform:translateY(0)}
.reveal.d-1{transition-delay:0.06s}.reveal.d-2{transition-delay:0.12s}.reveal.d-3{transition-delay:0.18s}

.card{opacity:0;transform:translateY(20px);transition:opacity 0.4s ease,transform 0.4s ease,border-color 0.3s}
.card.in{opacity:1;transform:translateY(0)}
.card.d-0{transition-delay:0s}.card.d-1{transition-delay:0.08s}.card.d-2{transition-delay:0.16s}

@keyframes shimmer{0%{background-position:140% 0}55%,to{background-position:-40% 0}}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulseGlow{0%,100%{box-shadow:0 0 0 0 rgba(var(--accent-rgb),0)}50%{box-shadow:0 0 0 4px rgba(var(--accent-rgb),0.12)}}
@keyframes slideIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.alert{animation:fadeUp 0.4s cubic-bezier(0.22,1,0.36,1)}

@media(max-width:768px){.tabs{flex-direction:column}.tab{justify-content:center}.table-wrapper{font-size:12px}th,td{padding:8px 10px}}
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
    .orb, .halo, .veil { animation: none !important; }
}
</style>
<?php include 'loader_css.php'; ?>
</head>
<body>
<?php include 'loader_html.php'; ?>

<div class="bg-fx">
    <div class="shader-orbs"><div class="orb orb--1"></div><div class="orb orb--2"></div><div class="orb orb--3"></div><div class="orb orb--4"></div></div>
    <div class="halo halo--a"></div>
    <div class="halo halo--b"></div>
    <div class="halo halo--c"></div>
    <div class="veil"></div>
</div>

<header class="header nav" id="nav">
    <div class="header-content">
        <a href="/main" class="header-brand-link">
            <span class="header-logo"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></span>
            <span class="header-brand brand-shine" data-text="<?php echo $site_name; ?>"><?php echo $site_name; ?></span>
        </a>
        <nav class="header-nav">
            <a href="/main">Главная</a>
            <a href="/shop">Магазин</a>
            <a href="/rules">Правила</a>
            <a href="/privacy">Соглашение</a>
            <a href="/profile">Профиль</a>
        </nav>
        <div class="header-actions">
            <button class="header-action header-action--profile" type="button" onclick="location.href='/profile'">
                <img class="header-avatar" src="/assets/ava.png" alt="" onerror="this.style.display='none'">
                <span>Profile</span>
            </button>
            <button class="burger" id="burgerBtn" aria-label="Меню"><i class="fas fa-bars"></i></button>
        </div>
    </div>
    <div class="m-menu">
        <a href="/main">Главная</a>
        <a href="/shop">Магазин</a>
        <a href="/rules">Правила</a>
        <a href="/privacy">Соглашение</a>
        <a href="/profile">Профиль</a>
        <a href="/logout">Выйти</a>
    </div>
</header>

<main class="page">
    <div class="container">

    <div class="page-head center reveal">
        <h1><i class="fas fa-tools"></i> Управление <span class="grad">лаунчером</span></h1>
        <p>Настройка лаунчера и управление версиями</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msg_type; ?> reveal d-1" id="flashAlert">
            <i class="fas fa-<?php echo $msg_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <div class="tabs reveal d-1" id="tabsBar">
        <div class="tab-indicator" id="tabIndicator"></div>
        <a href="?tab=settings" class="tab <?php echo $tab === 'settings' ? 'active' : ''; ?>" data-tab="settings">
            <i class="fas fa-sliders-h"></i> Настройки
        </a>
        <a href="?tab=versions" class="tab <?php echo $tab === 'versions' ? 'active' : ''; ?>" data-tab="versions">
            <i class="fas fa-code-branch"></i> Версии
        </a>
    </div>

    <?php if ($tab === 'settings'): ?>
    <div class="card reveal d-1" data-stagger>
        <div class="card-title"><i class="fas fa-cog"></i> Общие настройки</div>
        <form method="POST" novalidate>
            <input autocomplete="off" type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <div class="form-group">
                <div class="checkbox-group">
                    <input autocomplete="off" type="checkbox" name="maintenance_mode" value="1" <?php echo ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?>>
                    <label>Включить режим обслуживания</label>
                </div>
            </div>
            <div class="form-group">
                <label>Сообщение при техработах</label>
                <textarea name="maintenance_message" class="form-control" rows="3"><?php echo htmlspecialchars($settings['maintenance_message'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Кто видит лаунчер во время техработ (роли через запятую)</label>
                <input autocomplete="off" type="text" name="maintenance_bypass_roles" class="form-control" value="<?php echo htmlspecialchars($settings['maintenance_bypass_roles'] ?? 'admin'); ?>" placeholder="admin,premium">
                <small style="color:var(--faint);font-size:11px;">Перечислите role_key через запятую. Оставьте пусто = никому не показывать.</small>
            </div>
            <div class="form-group">
                <label>Ссылка для скачивания лаунчера</label>
                <input autocomplete="off" type="text" name="launcher_download_url" class="form-control" value="<?php echo htmlspecialchars($settings['launcher_download_url'] ?? ''); ?>" placeholder="https://...">
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label style="color:var(--accent);"><i class="fas fa-coffee"></i> Java — задаётся один раз, скачивается всеми клиентами</label>
            </div>
            <div class="form-group">
                <label>URL java.zip <span class="badge <?php echo !empty($settings['java_download_url'] ?? '') ? 'badge-green' : 'badge-red'; ?>"><?php echo !empty($settings['java_download_url'] ?? '') ? 'OK' : '—'; ?></span></label>
                <input autocomplete="off" type="text" name="java_download_url" id="java_download_url" class="form-control" value="<?php echo htmlspecialchars($settings['java_download_url'] ?? ''); ?>" placeholder="https://.../java.zip">
            </div>
            <div class="form-group">
                <label>SHA-256 java.zip</label>
                <div style="display:flex;gap:6px;">
                    <input autocomplete="off" type="text" name="java_download_hash" id="java_download_hash" class="form-control" value="<?php echo htmlspecialchars($settings['java_download_hash'] ?? ''); ?>" placeholder="Хэш">
                    <button type="button" class="btn btn-sm btn-success" onclick="calcHash('java_download_url','java_download_hash')"><i class="fas fa-calculator"></i></button>
                </div>
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label style="color:var(--accent);"><i class="fas fa-box-open"></i> Assets — глобальные, одни для всех версий</label>
            </div>
            <div class="form-group">
                <label>URL assets.zip (глобальный) <span class="badge <?php echo !empty($settings['global_assets_url'] ?? '') ? 'badge-green' : 'badge-red'; ?>"><?php echo !empty($settings['global_assets_url'] ?? '') ? 'OK' : '—'; ?></span></label>
                <input autocomplete="off" type="text" name="global_assets_url" id="global_assets_url" class="form-control" value="<?php echo htmlspecialchars($settings['global_assets_url'] ?? ''); ?>" placeholder="https://.../assets.zip">
            </div>
            <div class="form-group">
                <label>SHA-256 assets.zip</label>
                <div style="display:flex;gap:6px;">
                    <input autocomplete="off" type="text" name="global_assets_hash" id="global_assets_hash" class="form-control" value="<?php echo htmlspecialchars($settings['global_assets_hash'] ?? ''); ?>" placeholder="Хэш">
                    <button type="button" class="btn btn-sm btn-success" onclick="calcHash('global_assets_url','global_assets_hash')"><i class="fas fa-calculator"></i></button>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Текущая версия лаунчера</label>
                    <input autocomplete="off" type="text" name="current_version" class="form-control" value="<?php echo htmlspecialchars($settings['current_version'] ?? '1.0.0'); ?>" placeholder="1.0.0">
                </div>
                <div class="form-group">
                    <label>URL сайта (для API)</label>
                    <input autocomplete="off" type="text" name="site_url" class="form-control" value="<?php echo htmlspecialchars($settings['site_url'] ?? ''); ?>" placeholder="https://antiaileaks.ct.ws">
                </div>
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label style="color:var(--accent);"><i class="fas fa-shield-alt"></i> Валидация версии лаунчера</label>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Минимальный build лаунчера</label>
                    <input autocomplete="off" type="number" name="launcher_build_min" class="form-control" value="<?php echo htmlspecialchars($settings['launcher_build_min'] ?? '1'); ?>" placeholder="1" min="1">
                    <small style="color:var(--faint);font-size:11px;">Если лаунчер прислал build меньше этого — он не запустится.</small>
                </div>
                <div class="form-group">
                    <div class="checkbox-group" style="margin-top:28px;">
                        <input autocomplete="off" type="checkbox" name="launcher_force_update" value="1" <?php echo ($settings['launcher_force_update'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <label>Принудительная проверка (блокировать устаревшие лаунчеры)</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>URL скачивания обновления лаунчера</label>
                <input autocomplete="off" type="text" name="launcher_update_url" class="form-control" value="<?php echo htmlspecialchars($settings['launcher_update_url'] ?? ''); ?>" placeholder="https://.../0x15Loader.exe">
            </div>
            <div class="form-group">
                <label>Сообщение при устаревании</label>
                <input autocomplete="off" type="text" name="launcher_update_msg" class="form-control" value="<?php echo htmlspecialchars($settings['launcher_update_msg'] ?? 'Доступна новая версия лаунчера. Обновите для продолжения.'); ?>">
            </div>
            <button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить настройки</button>
        </form>
    </div>

    <div class="card reveal d-2" data-stagger>
        <div class="card-title"><i class="fas fa-share-alt"></i> Социальные сети</div>
        <form method="POST" novalidate>
            <input autocomplete="off" type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <div class="grid-2">
                <div class="form-group">
                    <label>Discord</label>
                    <input autocomplete="off" type="text" name="discord_link" class="form-control" value="<?php echo htmlspecialchars($settings['discord_link'] ?? ''); ?>" placeholder="https://discord.gg/...">
                </div>
                <div class="form-group">
                    <label>Telegram</label>
                    <input autocomplete="off" type="text" name="telegram_link" class="form-control" value="<?php echo htmlspecialchars($settings['telegram_link'] ?? ''); ?>" placeholder="https://t.me/...">
                </div>
            </div>
            <div class="form-group">
                <label>YouTube</label>
                <input autocomplete="off" type="text" name="youtube_link" class="form-control" value="<?php echo htmlspecialchars($settings['youtube_link'] ?? ''); ?>" placeholder="https://youtube.com/...">
            </div>
            <button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить настройки</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($tab === 'versions'): ?>
    <div class="card reveal d-1" data-stagger>
        <div class="card-title"><i class="fas fa-plus"></i> Добавить версию</div>
        <form method="POST" novalidate>
            <input autocomplete="off" type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <div class="grid-2">
                <div class="form-group">
                    <label>Номер версии</label>
                    <input autocomplete="off" type="text" name="version" class="form-control" placeholder="1.16.5" required>
                </div>
                <div class="form-group">
                    <label>Имя папки (C:\SkeetGuard\...)</label>
                    <input autocomplete="off" type="text" name="install_dir" class="form-control" placeholder="PouchLeaked (авто)">
                </div>
                <div class="form-group">
                    <label>Требуемая роль (пусто = всем)</label>
                    <select name="required_role" class="form-control">
                        <option value="">Все пользователи</option>
                        <?php foreach (getRolesMap($pdo) as $rk => $ri): ?>
                            <option value="<?php echo htmlspecialchars($rk); ?>"><?php echo htmlspecialchars($ri['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ссылка на JSON</label>
                    <input autocomplete="off" type="text" name="download_url" class="form-control" placeholder="https://.../version.json">
                </div>
            </div>
            <div class="form-group">
                <label>URL game.jar</label>
                <input autocomplete="off" type="text" name="url_game_jar" class="form-control" placeholder="https://www.dropbox.com/scl/fi/.../game.jar?rlkey=...&dl=1">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>URL libs.zip</label>
                    <input autocomplete="off" type="text" name="url_libs" class="form-control" placeholder="https://.../libs.zip">
                </div>
                <div class="form-group">
                    <label>URL natives.zip</label>
                    <input autocomplete="off" type="text" name="url_natives" class="form-control" placeholder="https://.../natives.zip">
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>SHA-256 game.jar</label>
                    <div style="display:flex;gap:6px;">
                        <input autocomplete="off" type="text" name="hash_game_jar" id="add_hash_game_jar" class="form-control" placeholder="Хэш">
                        <button type="button" class="btn btn-sm btn-success" onclick="calcHash('url_game_jar','add_hash_game_jar')"><i class="fas fa-calculator"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>SHA-256 libs</label>
                    <div style="display:flex;gap:6px;">
                        <input autocomplete="off" type="text" name="hash_libs" id="add_hash_libs" class="form-control" placeholder="Хэш">
                        <button type="button" class="btn btn-sm btn-success" onclick="calcHash('url_libs','add_hash_libs')"><i class="fas fa-calculator"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>SHA-256 natives</label>
                    <div style="display:flex;gap:6px;">
                        <input autocomplete="off" type="text" name="hash_natives" id="add_hash_natives" class="form-control" placeholder="Хэш">
                        <button type="button" class="btn btn-sm btn-success" onclick="calcHash('url_natives','add_hash_natives')"><i class="fas fa-calculator"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Crypto Key (обфускатор)</label>
                    <div style="display:flex;gap:6px;">
                        <input type="password" name="crypto_key" id="add_crypto_key" class="form-control" placeholder="KEY из обфускатора" style="font-family:monospace;font-size:11px;" autocomplete="new-password">
                        <button type="button" class="btn btn-sm btn-success" onclick="toggleKey('add_crypto_key', this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
            <button type="submit" name="add_version" class="btn btn-primary"><i class="fas fa-plus"></i> Добавить версию</button>
        </form>
    </div>

    <div class="card reveal d-2" data-stagger>
        <div class="card-title"><i class="fas fa-list"></i> Список версий</div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px;">ID</th>
                        <th>Версия</th>
                        <th>Папка</th>
                        <th>game</th>
                        <th>libs</th>
                        <th>java (общий)</th>
                        <th>natives</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($versions)): ?>
                        <tr><td colspan="9" style="text-align:center;color:var(--faint);padding:40px;">Нет версий</td></tr>
                    <?php else: ?>
                        <?php foreach ($versions as $v): ?>
                        <tr>
                            <td>#<?php echo $v['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($v['version']); ?></strong></td>
                            <td><?php echo htmlspecialchars($v['install_dir'] ?? 'PouchCrack'); ?></td>
                            <td><span class="badge <?php echo $v['url_game_jar'] ? 'badge-green' : 'badge-red'; ?>"><?php echo $v['url_game_jar'] ? 'OK' : '—'; ?></span></td>
                            <td><span class="badge <?php echo $v['url_libs'] ? 'badge-green' : 'badge-red'; ?>"><?php echo $v['url_libs'] ? 'OK' : '—'; ?></span></td>
                            <td><span class="badge <?php echo !empty($settings['java_download_url'] ?? '') ? 'badge-green' : 'badge-red'; ?>"><?php echo !empty($settings['java_download_url'] ?? '') ? 'OK' : '—'; ?></span></td>
                            <td><span class="badge <?php echo $v['url_natives'] ? 'badge-green' : 'badge-red'; ?>"><?php echo $v['url_natives'] ? 'OK' : '—'; ?></span></td>
                            <td><span class="badge <?php echo $v['is_active'] ? 'badge-green' : 'badge-red'; ?>"><?php echo $v['is_active'] ? 'Активна' : 'Выкл'; ?></span></td>
                            <td style="white-space:nowrap;">
                                <button class="btn btn-sm btn-primary" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($v, JSON_HEX_APOS|JSON_HEX_TAG)); ?>)"><i class="fas fa-edit"></i></button>
                                <a href="?delete_version=<?php echo $v['id']; ?>&tab=versions&csrf=<?php echo $csrf; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить версию?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    </div>
</main>

<footer class="footer">
<div class="footer__inner">
    <div class="footer__brand-block">
        <div class="footer__brand">
            <div class="footer__mark"><img src="/assets/logo.png" alt="" style="width:24px;height:24px;object-fit:contain" onerror="this.style.display='none'"></div>
            <span class="footer__brand-name"><?php echo $site_name; ?></span>
        </div>
        <p class="footer__copyright">&copy; <?php echo $site_name; ?> <?php echo date('Y'); ?>. Все права защищены.</p>
        <div class="footer__socials">
            <a class="footer__social" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" aria-label="Telegram" target="_blank" rel="noopener noreferrer"><i class="fab fa-telegram"></i></a>
        </div>
    </div>
    <div class="footer__nav-group">
        <h3 class="footer__title">Навигация</h3>
        <nav class="footer__links">
            <a class="footer__link" href="/main">Главная</a>
            <a class="footer__link" href="/shop">Магазин</a>
            <a class="footer__link" href="/rules">Правила</a>
            <a class="footer__link" href="/privacy">Соглашение</a>
        </nav>
    </div>
    <div class="footer__nav-group">
        <h3 class="footer__title">Документы</h3>
        <nav class="footer__links">
            <a class="footer__link" href="/privacy">Политика конфиденциальности</a>
            <a class="footer__link" href="/rules">Пользовательское соглашение</a>
        </nav>
    </div>
    <div class="footer__nav-group">
        <h3 class="footer__title">Поддержка</h3>
        <nav class="footer__links">
            <a class="footer__link" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank" rel="noopener noreferrer">Telegram-канал</a>
        </nav>
    </div>
    <p class="footer__credit">made by <a class="footer__credit-link" href="https://t.me/kodexnull" target="_blank" rel="noopener noreferrer">kodexnull</a> special for <a class="footer__credit-link" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank" rel="noopener noreferrer"><?php echo $site_name; ?></a></p>
</div>
</footer>

<!-- Edit Overlay Panel -->
<div class="overlay" id="editOverlay">
    <div class="overlay-panel" id="editPanel">
        <button class="overlay-close" onclick="closeOverlay()" aria-label="Закрыть"><i class="fas fa-times"></i></button>
        <h3><i class="fas fa-edit"></i> Редактирование версии</h3>
        <form method="POST" id="editForm" novalidate>
            <input autocomplete="off" type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <input autocomplete="off" type="hidden" name="version_id" id="edit_version_id">
            <div class="grid-2">
                <div class="form-group">
                    <label>Версия</label>
                    <input autocomplete="off" type="text" name="version" id="edit_version" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Имя папки</label>
                    <input autocomplete="off" type="text" name="install_dir" id="edit_install_dir" class="form-control">
                </div>
                <div class="form-group">
                    <label>Требуемая роль (пусто = всем)</label>
                    <select name="required_role" id="edit_required_role" class="form-control">
                        <option value="">Все пользователи</option>
                        <?php foreach (getRolesMap($pdo) as $rk => $ri): ?>
                            <option value="<?php echo htmlspecialchars($rk); ?>"><?php echo htmlspecialchars($ri['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ссылка на JSON</label>
                    <input autocomplete="off" type="text" name="download_url" id="edit_download_url" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>URL game.jar</label>
                <input autocomplete="off" type="text" name="url_game_jar" id="edit_url_game_jar" class="form-control">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>URL libs.zip</label>
                    <input autocomplete="off" type="text" name="url_libs" id="edit_url_libs" class="form-control">
                </div>
                <div class="form-group">
                    <label>URL natives.zip</label>
                    <input autocomplete="off" type="text" name="url_natives" id="edit_url_natives" class="form-control">
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>SHA-256 game.jar</label>
                    <div style="display:flex;gap:6px;">
                        <input autocomplete="off" type="text" name="hash_game_jar" id="edit_hash_game_jar" class="form-control" placeholder="Хэш">
                        <button type="button" class="btn btn-sm btn-success" onclick="calcHash('edit_url_game_jar','edit_hash_game_jar')"><i class="fas fa-calculator"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>SHA-256 libs</label>
                    <div style="display:flex;gap:6px;">
                        <input autocomplete="off" type="text" name="hash_libs" id="edit_hash_libs" class="form-control" placeholder="Хэш">
                        <button type="button" class="btn btn-sm btn-success" onclick="calcHash('edit_url_libs','edit_hash_libs')"><i class="fas fa-calculator"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>SHA-256 natives</label>
                    <div style="display:flex;gap:6px;">
                        <input autocomplete="off" type="text" name="hash_natives" id="edit_hash_natives" class="form-control" placeholder="Хэш">
                        <button type="button" class="btn btn-sm btn-success" onclick="calcHash('edit_url_natives','edit_hash_natives')"><i class="fas fa-calculator"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Crypto Key</label>
                    <div style="display:flex;gap:6px;">
                        <input type="password" name="crypto_key" id="edit_crypto_key" class="form-control" placeholder="KEY из обфускатора" style="font-family:monospace;font-size:11px;" autocomplete="new-password">
                        <button type="button" class="btn btn-sm btn-success" onclick="toggleKey('edit_crypto_key', this)"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="checkbox-group">
                    <input autocomplete="off" type="checkbox" name="is_active" id="edit_is_active" value="1">
                    <label>Активна (лаунчер скачивает файлы этой версии)</label>
                </div>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-danger" onclick="closeOverlay()"><i class="fas fa-times"></i> Отмена</button>
                <button type="submit" name="edit_version" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var nav = document.getElementById('nav');
    var ticking = false;
    window.addEventListener('scroll', function() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function() {
            nav.classList.toggle('scrolled', window.scrollY > 10);
            ticking = false;
        });
    }, { passive: true });

    document.getElementById('burgerBtn').addEventListener('click', function() {
        nav.classList.toggle('open');
    });

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal:not(.in)').forEach(function(el) {
        observer.observe(el);
    });

    document.querySelectorAll('.card[data-stagger]').forEach(function(el, i) {
        el.style.transitionDelay = (i * 0.08) + 's';
        observer.observe(el);
    });

    function positionIndicator() {
        var activeTab = document.querySelector('.tab.active');
        var indicator = document.getElementById('tabIndicator');
        if (!activeTab || !indicator) return;
        var bar = document.getElementById('tabsBar');
        var barRect = bar.getBoundingClientRect();
        var tabRect = activeTab.getBoundingClientRect();
        indicator.style.left = (tabRect.left - barRect.left) + 'px';
        indicator.style.width = tabRect.width + 'px';
    }
    positionIndicator();
    window.addEventListener('resize', positionIndicator);

    var flash = document.getElementById('flashAlert');
    if (flash) {
        var type = flash.classList.contains('alert-success') ? 'success' : 'error';
        showToast(flash.textContent.trim(), type);
        setTimeout(function(){ flash.remove(); }, 200);
    }
});

function showToast(text, type) {
    var t = document.createElement('div');
    t.className = 'toast toast-' + (type || 'success');
    t.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + text;
    document.body.appendChild(t);
    requestAnimationFrame(function(){ t.classList.add('show'); });
    setTimeout(function(){ t.classList.remove('show'); setTimeout(function(){ t.remove(); }, 400); }, 3500);
}

function openEditModal(v) {
    document.getElementById('editOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('edit_version_id').value = v.id;
    document.getElementById('edit_version').value = v.version || '';
    document.getElementById('edit_install_dir').value = v.install_dir || '';
    document.getElementById('edit_required_role').value = v.required_role || '';
    document.getElementById('edit_download_url').value = v.download_url || '';
    document.getElementById('edit_url_game_jar').value = v.url_game_jar || '';
    document.getElementById('edit_url_libs').value = v.url_libs || '';
    document.getElementById('edit_url_natives').value = v.url_natives || '';
    document.getElementById('edit_hash_game_jar').value = v.hash_game_jar || '';
    document.getElementById('edit_hash_libs').value = v.hash_libs || '';
    document.getElementById('edit_hash_natives').value = v.hash_natives || '';
    document.getElementById('edit_crypto_key').value = v.crypto_key || '';
    document.getElementById('edit_is_active').checked = v.is_active == 1;
}

function closeOverlay() {
    document.getElementById('editOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

document.getElementById('editOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeOverlay();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var ov = document.getElementById('editOverlay');
        if (ov && ov.classList.contains('open')) closeOverlay();
    }
});

function toggleKey(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    if (btn) btn.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
}

function calcHash(urlFieldId, hashFieldId) {
    var url = document.getElementById(urlFieldId).value.trim();
    var hashInput = document.getElementById(hashFieldId);
    if (!url) { showToast('Введите URL', 'error'); return; }

    var btn = hashInput.parentElement.querySelector('button');
    var origText = btn ? btn.innerHTML : '';
    if (btn) { btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btn.disabled = true; }

    fetch('/compute_hash.php?url=' + encodeURIComponent(url))
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.ok) {
                hashInput.value = d.hash;
                if (btn) { btn.innerHTML = '<i class="fas fa-check"></i>'; btn.disabled = false; }
                setTimeout(function() { if (btn) { btn.innerHTML = origText; btn.disabled = false; } }, 1500);
            } else {
                showToast('Ошибка: ' + (d.error || 'unknown'), 'error');
                if (btn) { btn.innerHTML = origText; btn.disabled = false; }
            }
        })
        .catch(function(e) {
            showToast('Ошибка сети: ' + e.message, 'error');
            if (btn) { btn.innerHTML = origText; btn.disabled = false; }
        });
}
</script>

<script>
(function() {
    var LOCK_URL = 'https://antiaileaks.ct.ws/F12otkazano';
    document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
    var kicked = false;
    function kick() { if (!kicked) { kicked = true; window.location.replace(LOCK_URL); } }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || e.keyCode === 123) { e.preventDefault(); kick(); return false; }
        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j' || e.key === 'C' || e.key === 'c')) { e.preventDefault(); kick(); return false; }
    });
    function chk() {
        if ((window.outerWidth - window.innerWidth) > 160 || (window.outerHeight - window.innerHeight) > 160) kick();
    }
    setInterval(chk, 1000);
    window.addEventListener('resize', chk);
})();
</script>

<script>
(function () {
    'use strict';
    var D = {
        'Главная': 'Home',
        'Магазин': 'Shop',
        'Правила': 'Rules',
        'Соглашение': 'Terms',
        'Поддержка': 'Support',
        'Профиль': 'Profile',
        'Личный кабинет': 'Account',
        'Войти': 'Sign in',
        'Вход': 'Sign in',
        'Вход —': 'Sign in —',
        'Регистрация': 'Sign up',
        'Регистрация —': 'Sign up —',
        'Выйти': 'Log out',
        'Меню': 'Menu',
        'В сети': 'Online',
        ' готов к запуску': ' ready to launch',
        'В сети · готов к запуску': 'Online · ready to launch',
        'Управление лаунчером —': 'Launcher management —',
        'Управление': 'Management',
        'лаунчером': 'launcher',
        'Настройка лаунчера и управление версиями': 'Configure the launcher and manage versions',
        'Настройки': 'Settings',
        'Версии': 'Versions',
        'Общие настройки': 'General settings',
        'Включить режим обслуживания': 'Enable maintenance mode',
        'Сообщение при техработах': 'Maintenance message',
        'Ссылка для скачивания лаунчера': 'Launcher download link',
        'Java — задаётся один раз, скачивается всеми клиентами': 'Java — set once, downloaded by all clients',
        'Хэш': 'Hash',
        'Текущая версия лаунчера': 'Current launcher version',
        'URL сайта (для API)': 'Site URL (for API)',
        'Сохранить настройки': 'Save settings',
        'Социальные сети': 'Social networks',
        'Добавить версию': 'Add version',
        'Номер версии': 'Version number',
        'Имя папки (C:\\SkeetGuard\\...)': 'Folder name (C:\\SkeetGuard\\...)',
        'Имя папки': 'Folder name',
        'PouchLeaked (авто)': 'PouchLeaked (auto)',
        'Ссылка на JSON': 'JSON link',
        'Crypto Key (обфускатор)': 'Crypto Key (obfuscator)',
        'KEY из обфускатора': 'KEY from obfuscator',
        'Список версий': 'Version list',
        'Версия': 'Version',
        'Папка': 'Folder',
        'java (общий)': 'java (shared)',
        'Удалить версию?': 'Delete version?',
        'Редактирование версии': 'Edit version',
        'Активна (лаунчер скачивает файлы этой версии)': 'Active (the launcher downloads this version files)',
        'Версия удалена': 'Version deleted',
        'Введите номер версии': 'Enter version number',
        'Такая версия уже существует': 'This version already exists',
        'Версия добавлена (хэши:': 'Version added (hashes:',
        'Версия обновлена (хэши:': 'Version updated (hashes:',
        'Введите URL': 'Enter URL',
        'Ошибка:': 'Error:',
        'Все пользователи': 'All users',
        'Все права защищены': 'All rights reserved',
        'Навигация': 'Navigation',
        'Документы': 'Documents',
        'Политика конфиденциальности': 'Privacy Policy',
        'Пользовательское соглашение': 'Terms of Service',
        'Telegram-канал': 'Telegram channel',
        'Отмена': 'Cancel',
        'Сохранить': 'Save',
        'Закрыть': 'Close'
    };
    var STORAGE_KEY = 'aial_lang';
    var current = 'ru';
    try { current = localStorage.getItem(STORAGE_KEY) || 'ru'; } catch (e) {}
    if (current !== 'ru' && current !== 'en') current = 'ru';
    var keys = Object.keys(D).sort(function (a, b) { return b.length - a.length; });
    function collapse(s) { return String(s || '').replace(/\s+/g, ' ').trim(); }
    function isWordChar(ch) { return !!ch && /[a-zA-Zа-яА-ЯёЁ0-9_]/.test(ch); }
    function translateText(text) {
        for (var i = 0; i < keys.length; i++) {
            var k = keys[i];
            var idx = text.indexOf(k);
            if (idx === -1) continue;
            var before = idx > 0 ? text.charAt(idx - 1) : '';
            var after = idx + k.length < text.length ? text.charAt(idx + k.length) : '';
            if (!isWordChar(before) && !isWordChar(after)) {
                text = text.slice(0, idx) + D[k] + text.slice(idx + k.length);
            }
        }
        return text;
    }
    function translateNodes() {
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                var p = node.parentNode;
                if (!p) return NodeFilter.FILTER_REJECT;
                var tag = p.nodeName;
                if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'IFRAME' || tag === 'NOSCRIPT' || tag === 'TEXTAREA') return NodeFilter.FILTER_REJECT;
                var t = collapse(node.nodeValue);
                if (!t) return NodeFilter.FILTER_REJECT;
                for (var i = 0; i < keys.length; i++) {
                    if (t.indexOf(keys[i]) !== -1) return NodeFilter.FILTER_ACCEPT;
                }
                return NodeFilter.FILTER_REJECT;
            }
        });
        var n;
        while ((n = walker.nextNode())) {
            if (!n.__ruText) n.__ruText = n.nodeValue;
            var fresh = translateText(n.__ruText);
            if (fresh !== n.nodeValue) n.nodeValue = fresh;
        }
    }
    function translateAttrs() {
        var attrs = ['placeholder', 'title', 'aria-label'];
        for (var i = 0; i < attrs.length; i++) {
            var els = document.querySelectorAll('[' + attrs[i] + ']');
            for (var j = 0; j < els.length; j++) {
                var el = els[j];
                if (!el.__ruAttrs) el.__ruAttrs = {};
                var v = collapse(el.getAttribute(attrs[i]));
                if (v && D[v]) {
                    if (!el.__ruAttrs[attrs[i]]) el.__ruAttrs[attrs[i]] = el.getAttribute(attrs[i]);
                    el.setAttribute(attrs[i], D[v]);
                }
            }
        }
    }
    function restoreRu() {
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                return node.__ruText !== undefined ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });
        var n;
        while ((n = walker.nextNode())) { n.nodeValue = n.__ruText; }
        var attrs = ['placeholder', 'title', 'aria-label'];
        for (var i = 0; i < attrs.length; i++) {
            var els = document.querySelectorAll('[' + attrs[i] + ']');
            for (var j = 0; j < els.length; j++) {
                var el = els[j];
                if (el.__ruAttrs && el.__ruAttrs[attrs[i]] !== undefined) {
                    el.setAttribute(attrs[i], el.__ruAttrs[attrs[i]]);
                }
            }
        }
    }
    function buildSwitcher() {
        var host = document.querySelector('.header-actions');
        if (!host) host = document.querySelector('.header-nav');
        if (!host) return;
        if (document.querySelector('.lang-switch')) return;
        var style = document.createElement('style');
        style.textContent = '.lang-switch{display:inline-flex;align-items:center;gap:2px;padding:3px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);border-radius:999px;margin-right:.15rem;}.lang-btn{border:0;background:transparent;color:rgba(255,255,255,.4);font-family:Inter,sans-serif;font-size:.72rem;font-weight:600;line-height:1;padding:5px 8px;border-radius:999px;cursor:pointer;transition:color .18s ease,background .18s ease;}.lang-btn:hover{color:#fff;}.lang-btn.active{color:#fff;background:rgba(255,255,255,.1);}';
        document.head.appendChild(style);
        var wrap = document.createElement('div');
        wrap.className = 'lang-switch';
        function make(lng, label, title) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'lang-btn' + (current === lng ? ' active' : '');
            b.textContent = label;
            b.title = title;
            b.addEventListener('click', function () { setLang(lng); });
            return b;
        }
        wrap.appendChild(make('ru', 'RU', 'Русский'));
        wrap.appendChild(make('en', 'EN', 'English'));
        var ref = host.querySelector('.burger') || host.querySelector('.user-chip');
        if (ref) host.insertBefore(wrap, ref);
        else host.appendChild(wrap);
    }
    function setLang(lng) {
        if (lng === current) return;
        current = lng;
        try { localStorage.setItem(STORAGE_KEY, lng); } catch (e) {}
        document.documentElement.setAttribute('lang', lng === 'ru' ? 'ru' : 'en');
        var btns = document.querySelectorAll('.lang-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].classList.toggle('active', btns[i].textContent === (lng === 'ru' ? 'RU' : 'EN'));
        }
        if (lng === 'en') { translateNodes(); translateAttrs(); } else { restoreRu(); }
    }
    function boot() {
        document.documentElement.setAttribute('lang', current === 'ru' ? 'ru' : 'en');
        buildSwitcher();
        if (current === 'en') { translateNodes(); translateAttrs(); }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
    window.__lang = current;
})();
</script>
<?php include 'loader_js.php'; ?>
</body>
</html>
