<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';

// ============================================================
// ЗАЩИТА ОТ БОТОВ
// ============================================================

session_start();
checkMaintenance();

if (!isLoggedIn()) {
    redirect('/login');
}

$current_user = getUser($pdo, $_SESSION['user_id']);
if (!userHasRight($pdo, $current_user, 'can_admin')) {
    redirect('/profile');
}

// Проверка CSRF-токена
$csrf = $_SESSION['csrf'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($csrf, $_POST['csrf'] ?? '')) {
    http_response_code(403);
    die('Неверный CSRF-токен');
}
$get_actions = ['delete_role'];
foreach ($get_actions as $ga) {
    if (isset($_GET[$ga]) && !hash_equals($csrf, $_GET['csrf'] ?? '')) {
        http_response_code(403);
        die('Неверный CSRF-токен');
    }
}

$msg = '';
$msg_type = '';

// ========== СОЗДАНИЕ ТАБЛИЦЫ РОЛЕЙ ==========
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_key VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        icon VARCHAR(50) NOT NULL DEFAULT 'fa-user-tag',
        can_admin TINYINT(1) NOT NULL DEFAULT 0,
        can_support TINYINT(1) NOT NULL DEFAULT 0,
        as_admin TINYINT(1) NOT NULL DEFAULT 0,
        is_auto TINYINT(1) NOT NULL DEFAULT 0,
        auto_type VARCHAR(20) DEFAULT NULL,
        is_system TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // Авто-исправление коллации, чтобы сравнение с users (utf8mb4_general_ci) работало
    try {
        $pdo->exec("ALTER TABLE roles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    } catch (PDOException $e) {}

    // Заполняем стандартные роли при первом запуске
    $count = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ((int)$count === 0) {
        $seed = [
            ['admin', 'Администратор', 'fa-crown', 1, 1, 1, 0, null, 1, 100],
            ['user', 'User', 'fa-user', 0, 0, 0, 1, 'has_sub', 0, 30],
            ['guest', 'Guest', 'fa-user-slash', 0, 0, 0, 1, 'no_sub', 0, 10],
            ['premium', 'Premium', 'fa-star', 0, 0, 0, 0, null, 0, 50],
            ['support', 'Support', 'fa-headset', 0, 1, 1, 0, null, 0, 60],
            ['media', 'Media', 'fa-video', 0, 0, 0, 0, null, 0, 20],
        ];
        $ins = $pdo->prepare("INSERT INTO roles (role_key, name, icon, can_admin, can_support, as_admin, is_auto, auto_type, is_system, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($seed as $s) {
            $ins->execute($s);
        }
    }

    $cols_p = $pdo->query("SHOW COLUMNS FROM roles LIKE 'priority'");
    if ($cols_p->rowCount() == 0) {
        $pdo->exec("ALTER TABLE roles ADD COLUMN `priority` INT NOT NULL DEFAULT 30 AFTER `is_system`");
        $pdo->exec("UPDATE roles SET priority = 100 WHERE role_key = 'admin'");
        $pdo->exec("UPDATE roles SET priority = 60 WHERE role_key = 'support'");
        $pdo->exec("UPDATE roles SET priority = 50 WHERE role_key = 'premium'");
        $pdo->exec("UPDATE roles SET priority = 30 WHERE role_key = 'user'");
        $pdo->exec("UPDATE roles SET priority = 20 WHERE role_key = 'media'");
        $pdo->exec("UPDATE roles SET priority = 10 WHERE role_key = 'guest'");
    }
} catch (PDOException $e) {
    $msg = 'Ошибка создания таблицы ролей: ' . $e->getMessage();
    $msg_type = 'error';
    $sql_hint = "ALTER TABLE roles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
}

// автоисправление: при каждом открытии страницы сбрасываем can_admin/as_admin/can_support у ролей которые не admin
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    try {
        $pdo->exec("UPDATE roles SET can_admin = 0, as_admin = 0 WHERE role_key != 'admin' AND (can_admin = 1 OR as_admin = 1)");
    } catch (PDOException $e) {}
}

// ручной fix по кнопке
if (isset($_GET['fix_roles']) && $_GET['csrf'] === $csrf) {
    try {
        $pdo->exec("UPDATE roles SET can_admin = 0, as_admin = 0 WHERE role_key != 'admin' AND (can_admin = 1 OR as_admin = 1)");
        $msg = "Все роли кроме admin сброшены (can_admin=0, as_admin=0)";
        $msg_type = 'success';
    } catch (PDOException $e) {
        $msg = "Ошибка: " . $e->getMessage();
        $msg_type = 'error';
    }
}

// ========== ДОБАВЛЕНИЕ РОЛИ ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_role') {
    $role_key = strtolower(trim(preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['role_key'] ?? '')));
    $name = trim($_POST['name'] ?? '');
    $icon = trim(preg_replace('/[^a-z0-9- ]/', '', $_POST['icon'] ?? 'fa-user-tag'));
    $can_admin = isset($_POST['new_can_admin']) ? 1 : 0;
    $can_support = isset($_POST['new_can_support']) ? 1 : 0;
    $as_admin = isset($_POST['new_as_admin']) ? 1 : 0;
    $is_auto = isset($_POST['new_is_auto']) ? 1 : 0;
    $auto_type = $is_auto ? (($_POST['new_auto_type'] ?? 'has_sub') === 'no_sub' ? 'no_sub' : 'has_sub') : null;
    $priority = max(0, min(999, (int)($_POST['new_priority'] ?? 30)));

    if ($role_key === '' || $name === '') {
        $msg = 'Укажите ключ роли (латиницей) и название';
        $msg_type = 'error';
    } else {
        $exists_check = $pdo->prepare("SELECT id FROM roles WHERE role_key = ?");
        $exists_check->execute([$role_key]);
        if ($exists_check->fetch()) {
            $msg = "Роль '$role_key' уже существует";
            $msg_type = 'error';
        } else {
            $stmt = $pdo->prepare("INSERT INTO roles (role_key, name, icon, can_admin, can_support, as_admin, is_auto, auto_type, is_system, priority) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)");
            $stmt->execute([$role_key, $name, $icon, $can_admin, $can_support, $as_admin, $is_auto, $auto_type, $priority]);
            $msg = "Роль '$name' создана";
            $msg_type = 'success';
            logAdminAction($pdo, $current_user['id'], $current_user['username'], 'create_role', null, null, "Роль $role_key ($name)");
        }
    }
}

// ========== ОБНОВЛЕНИЕ РОЛИ ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_role') {
    $rid = (int)($_POST['role_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $icon = trim(preg_replace('/[^a-z0-9- ]/', '', $_POST['icon'] ?? 'fa-user-tag'));
    $can_admin = isset($_POST['can_admin']) ? 1 : 0;
    $can_support = isset($_POST['can_support']) ? 1 : 0;
    $as_admin = isset($_POST['as_admin']) ? 1 : 0;
    $is_auto = isset($_POST['is_auto']) ? 1 : 0;
    $auto_type = $is_auto ? (($_POST['auto_type'] ?? 'has_sub') === 'no_sub' ? 'no_sub' : 'has_sub') : null;
    $priority = max(0, min(999, (int)($_POST['priority'] ?? 30)));

    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->execute([$rid]);
    $role = $stmt->fetch();

    if (!$role) {
        $msg = 'Роль не найдена';
        $msg_type = 'error';
    } elseif ($name === '') {
        $msg = 'Название роли не может быть пустым';
        $msg_type = 'error';
    } else {
        // системные роли нельзя лишать права админа
        if ($role['is_system'] && !$can_admin) {
            $can_admin = 1;
        }
        $stmt = $pdo->prepare("UPDATE roles SET name = ?, icon = ?, can_admin = ?, can_support = ?, as_admin = ?, is_auto = ?, auto_type = ?, priority = ? WHERE id = ?");
        $stmt->execute([$name, $icon, $can_admin, $can_support, $as_admin, $is_auto, $auto_type, $priority, $rid]);
        $msg = "Роль '{$role['role_key']}' обновлена";
        $msg_type = 'success';
        logAdminAction($pdo, $current_user['id'], $current_user['username'], 'update_role', null, null, "Роль {$role['role_key']}");
    }
}

// ========== УДАЛЕНИЕ РОЛИ ==========
if (isset($_GET['delete_role'])) {
    $rid = (int)$_GET['delete_role'];
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->execute([$rid]);
    $role = $stmt->fetch();

    if (!$role) {
        $msg = 'Роль не найдена';
        $msg_type = 'error';
    } elseif ($role['is_system']) {
        $msg = 'Системную роль удалить нельзя';
        $msg_type = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role['role_key']]);
        $users_with = (int)$stmt->fetchColumn();
        if ($users_with > 0) {
            $pdo->prepare("UPDATE users SET role = 'user' WHERE role = ?")->execute([$role['role_key']]);
        }
        $pdo->prepare("DELETE FROM roles WHERE id = ?")->execute([$rid]);
        $msg = "Роль '{$role['name']}' удалена" . ($users_with > 0 ? ", $users_with пользователей перенесено в User" : '');
        $msg_type = 'success';
        logAdminAction($pdo, $current_user['id'], $current_user['username'], 'delete_role', null, null, "Роль {$role['role_key']} ($users_with пользователей)");
    }
}

// ========== ДАННЫЕ ДЛЯ ОТОБРАЖЕНИЯ ==========
$roles = [];
try {
    $stmt = $pdo->query("SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role = r.role_key) AS user_count FROM roles r ORDER BY r.is_system DESC, r.id ASC");
    $roles = $stmt->fetchAll();
} catch (PDOException $e) {
    $roles = [];
    $msg = 'Таблица ролей недоступна: ' . $e->getMessage();
    $msg_type = 'error';
}
$site_name = htmlspecialchars($SITE_NAME ?? 'Placeholder');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <!-- Performance: Font preloads -->
    <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.gstatic.com/s/inter/v19/UcCO3FwrK3iLTeHuS_fvQtMwCp50KnMw2boKoduKmMEVuLyfAZ9hjp-Ek-_EeA.woff2">
    <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.gstatic.com/s/sora/v20/BMgS_f-qkpgTSE9BHNk.woff2">
    
    <!-- Performance: DNS prefetch for external resources -->
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://antiaileaks.ct.ws">
    
    <!-- Performance: Preconnect for critical origins -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#08080f">
    <title>Роли — <?php echo $site_name; ?></title>
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
.tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:24px;padding:6px;border-radius:16px;border:1px solid var(--line);background:var(--panel);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px)}
.tab{display:inline-flex;align-items:center;gap:7px;padding:10px 16px;border-radius:12px;font-size:13px;font-weight:600;color:var(--dim);text-decoration:none;transition:all 0.2s ease}
.tab:hover{color:#fff;background:rgba(255,255,255,0.05)}
.tab.active{color:#fff;background:rgba(var(--accent-rgb),0.15)}
.tab i{font-size:13px}
.card{padding:24px;border-radius:18px;border:1px solid var(--line);background:var(--panel);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px);margin-bottom:20px}
.card-title{font-family:'Sora',sans-serif;font-size:15px;font-weight:700;color:#fff;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.card-title i{color:var(--accent);font-size:14px}
.search-box{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.form-control{padding:10px 14px;border-radius:10px;border:1px solid var(--line2);background:rgba(255,255,255,0.03);color:#fff;font-family:'Inter',sans-serif;font-size:13px;outline:none;transition:border-color 0.2s}
.form-control:focus{border-color:rgba(var(--accent-rgb),0.5)}
select.form-control{cursor:pointer}
textarea.form-control{resize:vertical;min-height:80px}
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:12.5px;font-weight:600;color:var(--ink2);margin-bottom:6px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 18px;border-radius:10px;font-family:'Inter',sans-serif;font-size:12.5px;font-weight:600;border:none;cursor:pointer;transition:all 0.2s;text-decoration:none}
.btn-primary{background:var(--grad);color:#fff;box-shadow:0 8px 24px -10px rgba(var(--accent-rgb),0.5)}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 12px 28px -10px rgba(var(--accent-rgb),0.6)}
.btn-danger{color:#fca5a5;border:1px solid rgba(244,63,94,0.3);background:rgba(244,63,94,0.1)}
.btn-danger:hover{background:rgba(244,63,94,0.2);border-color:rgba(244,63,94,0.5);color:#fecaca;transform:translateY(-1px)}
.btn-success{color:#6ee7b7;border:1px solid rgba(16,185,129,0.3);background:rgba(16,185,129,0.1)}
.btn-success:hover{background:rgba(16,185,129,0.2);border-color:rgba(16,185,129,0.5);color:#a7f3d0;transform:translateY(-1px)}
.btn-warning{color:#fcd34d;border:1px solid rgba(245,158,11,0.3);background:rgba(245,158,11,0.1)}
.btn-warning:hover{background:rgba(245,158,11,0.2);border-color:rgba(245,158,11,0.5);color:#fde68a;transform:translateY(-1px)}
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
.pagination{display:flex;gap:4px;justify-content:center;margin-top:16px}
.pagination .page{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;font-size:12.5px;font-weight:600;color:var(--dim);text-decoration:none;border:1px solid var(--line);transition:all 0.2s}
.pagination .page:hover{color:#fff;background:rgba(255,255,255,0.05)}
.pagination .page.active{color:#fff;background:rgba(var(--accent-rgb),0.15);border-color:rgba(var(--accent-rgb),0.3)}
.modal-overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.7);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px}
.modal-overlay.open{display:flex}
.modal-box{width:100%;max-width:440px;padding:28px;border-radius:20px;border:1px solid var(--line);background:rgba(12,12,23,0.95);-webkit-backdrop-filter:blur(24px);backdrop-filter:blur(24px);box-shadow:0 30px 80px -20px rgba(0,0,0,0.8)}
.modal-box h3{font-family:'Sora',sans-serif;font-size:17px;font-weight:700;color:#fff;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.modal-box h3 i{color:var(--accent)}
.modal-box .sub{font-size:13px;color:var(--dim);margin-bottom:16px}
.stars-input{display:flex;gap:4px;margin-bottom:12px}
.star-btn{font-size:18px;color:var(--faint);cursor:pointer;transition:color 0.15s}
.star-btn.active{color:#fcd34d}
.rules-grid,.privacy-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.rule-card,.privacy-card{position:relative;padding:24px;border-radius:16px;border:1px solid var(--line);background:var(--panel);-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px);transition:all 0.3s}
.rule-card:hover,.privacy-card:hover{transform:translateY(-3px);border-color:rgba(var(--accent-rgb),0.35)}
.rule-card .number,.privacy-card .number{font-family:'Sora',sans-serif;font-size:10px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);margin-bottom:10px}
.rule-card .title,.privacy-card .title{font-family:'Sora',sans-serif;font-size:15px;font-weight:700;color:#fff;margin-bottom:8px}
.rule-card .text,.privacy-card .text{font-size:13px;color:var(--ink2);line-height:1.7}
.rule-card .list,.privacy-card .list{margin:10px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:6px}
.rule-card .list li,.privacy-card .list li{display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--ink2)}
.note-card{display:flex;align-items:flex-start;gap:12px;padding:18px;border-radius:14px;border:1px solid rgba(251,191,36,0.2);background:rgba(251,191,36,0.04);margin-bottom:20px}
.note-card i{color:#fbbf24;font-size:16px;margin-top:2px}
.note-card p{font-size:13px;color:var(--ink2)}
.social-links{display:flex;justify-content:center;gap:10px;margin-top:16px}
.social-links a{width:40px;height:40px;border-radius:12px;border:1px solid var(--line2);background:var(--panel);display:flex;align-items:center;justify-content:center;font-size:15px;color:var(--ink2);transition:all 0.25s}
.social-links a:hover{transform:translateY(-3px);border-color:rgba(var(--accent-rgb),0.4);color:#fff}
.reveal{opacity:0;transform:translateY(24px);transition:opacity 0.5s ease,transform 0.5s ease}
.reveal.in{opacity:1;transform:translateY(0)}
.reveal.d-1{transition-delay:0.06s}.reveal.d-2{transition-delay:0.12s}.reveal.d-3{transition-delay:0.18s}
@media(max-width:768px){.tabs{flex-direction:column}.tab{justify-content:center}.rules-grid,.privacy-grid{grid-template-columns:1fr}.table-wrapper{font-size:12px}th,td{padding:8px 10px}}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:768px){.grid-2{grid-template-columns:1fr}}
.footer{position:relative;background:transparent;padding:0 20px 32px;margin-top:180px;border-top:0}
.footer__inner{width:min(1100px,calc(100vw - 40px));margin:0 auto;padding:28px 28px 20px;display:grid;grid-template-columns:minmax(0,1.3fr) auto auto auto;gap:40px;justify-items:start;text-align:left;border-radius:24px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02);-webkit-backdrop-filter:blur(18px);backdrop-filter:blur(18px);box-shadow:inset 0 1px rgba(255,255,255,.04)}
.footer__brand-block{display:flex;flex-direction:column;align-items:flex-start}
.footer__brand{display:inline-flex;align-items:center;gap:8px}
.footer__mark{display:flex;align-items:center;justify-content:center}
.footer__mark img{width:24px;height:24px;object-fit:contain}
.footer__brand-name{font-family:Inter,sans-serif;font-size:1.1rem;font-weight:500;letter-spacing:-.3px;color:#fff}
.footer__copyright{margin-top:12px;font-family:Inter,sans-serif;font-size:.74rem;line-height:1.4;color:rgba(255,255,255,.4)}
.footer__socials{display:flex;gap:10px;margin-top:16px}
.footer__social{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.8);font-size:16px;text-decoration:none;transition:border-color .2s ease,color .2s ease,background .2s ease}
.footer__social:hover{color:#fff;background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15)}
.footer__nav-group{min-width:120px}
.footer__title{font-family:Inter,sans-serif;font-size:.95rem;font-weight:500;letter-spacing:-.02em;color:#fff}
.footer__links{display:flex;flex-direction:column;align-items:flex-start;gap:8px;margin-top:12px}
.footer__link{font-family:Inter,sans-serif;font-size:.84rem;color:rgba(255,255,255,.45);text-decoration:none;transition:color .18s ease}
.footer__link:hover{color:rgba(255,255,255,.85)}
.footer__credit{grid-column:1/-1;text-align:center;font-family:Inter,sans-serif;font-size:.75rem;color:rgba(255,255,255,.25);margin-top:8px;border-top:1px solid rgba(255,255,255,.05);padding-top:16px}
.footer__credit-link{color:rgba(255,255,255,.4);text-decoration:none;transition:color .18s}
.footer__credit-link:hover{color:rgba(255,255,255,.8)}
@media(max-width:900px){.footer__inner{grid-template-columns:1fr 1fr;gap:28px}}
@media(max-width:600px){.footer__inner{grid-template-columns:1fr;gap:20px;text-align:center;padding:24px 18px 16px}.footer__brand-block{align-items:center}.footer__socials{justify-content:center}.footer__nav-group{align-items:center}.footer__links{align-items:center}.footer__brand{justify-content:center}}
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            .orb, .halo, .veil { animation: none !important; }
        }</style>
<?php include 'loader_css.php'; ?>
</head>
<body>
<?php include 'loader_html.php'; ?>

<!-- ===== ФОН ===== -->
<div class="bg-fx">
    <div class="shader-orbs"><div class="orb orb--1"></div><div class="orb orb--2"></div><div class="orb orb--3"></div><div class="orb orb--4"></div></div>
    <div class="halo halo--a"></div>
    <div class="halo halo--b"></div>
    <div class="halo halo--c"></div>
    <div class="veil"></div>
</div>

<!-- ===== НАВБАР ===== -->
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

<!-- ===== STRAP: УПРАВЛЕНИЕ РОЛЯМИ ===== -->
<main class="page">
    <div class="container">

    <div class="bar-top reveal">
        <div class="page-head" style="margin-bottom:0;">
            <h1><i class="fas fa-user-tag"></i> Роли <span class="grad">пользователей</span></h1>
            <p>Добавляйте, удаляйте роли и настраивайте их права</p>
        </div>
        <a href="/admin" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> В админ-панель</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?php echo $msg_type; ?> reveal d-1">
            <i class="fas fa-<?php echo $msg_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($sql_hint)): ?>
        <div class="card reveal d-1">
            <div class="card-title"><i class="fas fa-database"></i> Выполните SQL вручную (phpMyAdmin)</div>
            <div class="section-sub">Хостинг не дал создать таблицу автоматически. Откройте phpMyAdmin, вкладку SQL вашей БД, вставьте и выполните:</div>
            <pre style="background:rgba(0,0,0,0.35);border:1px solid var(--line);border-radius:var(--r-sm);padding:1rem;overflow-x:auto;font-size:0.78rem;line-height:1.5;color:#a5b4fc;"><?php echo htmlspecialchars($sql_hint); ?></pre>
        </div>
    <?php endif; ?>

    <!-- ===== ДОБАВЛЕНИЕ РОЛИ ===== -->
    <div class="card reveal d-1">
        <div class="card-title"><i class="fas fa-plus"></i> Новая роль</div>
        <form method="POST" class="form-grid" novalidate>
            <input autocomplete="off" type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <input autocomplete="off" type="hidden" name="action" value="add_role">
            <div>
                <label class="form-label">Ключ (латиницей)</label>
                <input autocomplete="off" type="text" name="role_key" class="form-control" placeholder="Напр. moderator" required>
            </div>
            <div>
                <label class="form-label">Название</label>
                <input autocomplete="off" type="text" name="name" class="form-control" placeholder="Напр. Модератор" required>
            </div>
            <div>
                <label class="form-label">Иконка (FontAwesome)</label>
                <input autocomplete="off" type="text" name="icon" class="form-control" placeholder="fa-user-shield" value="fa-user-tag">
            </div>
            <div class="chk-row" style="grid-column: 1/-1;">
                <label class="chk"><input autocomplete="off" type="checkbox" name="new_can_admin"> Доступ к админ-панели</label>
                <label class="chk"><input autocomplete="off" type="checkbox" name="new_can_support"> Ответы в поддержке</label>
                <label class="chk"><input autocomplete="off" type="checkbox" name="new_as_admin"> Показывать как «Администратор»</label>
                <label class="chk"><input autocomplete="off" type="checkbox" name="new_is_auto" id="newAutoChk"> Авто-роль</label>
                <label class="chk" id="newAutoTypeWrap" style="display:none;">
                    <select name="new_auto_type" class="form-control" style="width:auto;padding:0.4rem 0.7rem;">
                        <option value="has_sub">Выдаётся при активной подписке</option>
                        <option value="no_sub">Выдаётся без подписки</option>
                    </select>
                </label>
            </div>
            <div>
                <label class="form-label">Приоритет (чем выше — тем больше доступ)</label>
                <input autocomplete="off" type="number" name="new_priority" class="form-control" value="30" min="0" max="999" placeholder="30">
                <small style="color:var(--dim);font-size:11px;">admin=100, support=60, premium=50, user=30, media=20, guest=10</small>
            </div>
            <div style="grid-column: 1/-1;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Создать роль</button>
            </div>
        </form>
    </div>

    <!-- ===== СПИСОК РОЛЕЙ ===== -->
    <div class="card reveal d-2">
        <div class="card-title"><i class="fas fa-list"></i> Существующие роли <a href="?fix_roles=1&csrf=<?php echo $csrf; ?>" class="btn btn-sm btn-danger" style="margin-left:auto;font-size:11px;" onclick="return confirm('Сбросить can_admin и as_admin у ВСЕХ ролей кроме admin?')"><i class="fas fa-wrench"></i> Починить роли</a></div>
        <div class="section-sub">Авто-роли (User/Guest) назначаются автоматически по подписке. Системные роли (красные) удалить нельзя.</div>

        <?php if (empty($roles)): ?>
            <div style="text-align:center;color:var(--faint);padding:30px;">Нет ролей</div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">ID</th>
                        <th>Роль</th>
                        <th>Права</th>
                        <th style="width:110px;">Пользователей</th>
                        <th style="min-width:200px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role):
                        $is_system = $role['is_system'] == 1;
                        $is_auto = $role['is_auto'] == 1;
                    ?>
                    <tr>
                        <td><span class="id-cell">#<?php echo $role['id']; ?></span></td>
                        <td>
                            <div class="role-name-cell">
                                <span class="role-icon-preview"><i class="fas <?php echo htmlspecialchars($role['icon']); ?>"></i></span>
                                <div>
                                    <strong><?php echo htmlspecialchars($role['name']); ?></strong>
                                    <div style="font-size:0.72rem;color:var(--faint);">
                                        <?php echo htmlspecialchars($role['role_key']); ?>
                                        <?php if ($is_system): ?>
                                            <span class="role-badge system"><i class="fas fa-lock"></i> Системная</span>
                                        <?php endif; ?>
                                        <?php if ($is_auto): ?>
                                            <span class="role-badge auto"><i class="fas fa-magic"></i> Авто: <?php echo $role['auto_type'] === 'no_sub' ? 'без подписки' : 'есть подписка'; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="tag <?php echo $role['can_admin'] ? 'on' : 'off'; ?>"><i class="fas fa-shield-alt"></i> Админ</span>
                            <span class="tag <?php echo $role['can_support'] ? 'on' : 'off'; ?>"><i class="fas fa-headset"></i> Поддержка</span>
                            <span class="tag <?php echo $role['as_admin'] ? 'on' : 'off'; ?>"><i class="fas fa-crown"></i> Как админ</span>
                        </td>
                        <td><?php echo (int)$role['user_count']; ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline" onclick="toggleEdit(<?php echo $role['id']; ?>)"><i class="fas fa-pen"></i> Изменить</button>
                            <?php if (!$is_system): ?>
                                <a href="?delete_role=<?php echo $role['id']; ?>&csrf=<?php echo $csrf; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить роль «<?php echo htmlspecialchars($role['name']); ?>»? Пользователи с этой ролью перейдут в User.')"><i class="fas fa-trash"></i> Удалить</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr id="editRow<?php echo $role['id']; ?>" style="display:none;">
                        <td colspan="5">
                            <form method="POST" class="form-grid" novalidate>
                                <input autocomplete="off" type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                                <input autocomplete="off" type="hidden" name="action" value="update_role">
                                <input autocomplete="off" type="hidden" name="role_id" value="<?php echo $role['id']; ?>">
                                <div>
                                    <label class="form-label">Название</label>
                                    <input autocomplete="off" type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($role['name']); ?>" required>
                                </div>
                                <div>
                                    <label class="form-label">Иконка</label>
                                    <input autocomplete="off" type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($role['icon']); ?>">
                                </div>
                                <div class="chk-row" style="grid-column: 1/-1;">
                                    <label class="chk"><input autocomplete="off" type="checkbox" name="can_admin" <?php echo $role['can_admin'] ? 'checked' : ''; ?>> Доступ к админ-панели</label>
                                    <label class="chk"><input autocomplete="off" type="checkbox" name="can_support" <?php echo $role['can_support'] ? 'checked' : ''; ?>> Ответы в поддержке</label>
                                    <label class="chk"><input autocomplete="off" type="checkbox" name="as_admin" <?php echo $role['as_admin'] ? 'checked' : ''; ?>> Показывать как «Администратор»</label>
                                    <label class="chk"><input autocomplete="off" type="checkbox" name="is_auto" class="autoChk" data-wrap="autoType<?php echo $role['id']; ?>" <?php echo $is_auto ? 'checked' : ''; ?>> Авто-роль</label>
                                    <label class="chk" id="autoType<?php echo $role['id']; ?>" <?php echo $is_auto ? '' : 'style="display:none;"'; ?>>
                                        <select name="auto_type" class="form-control" style="width:auto;padding:0.4rem 0.7rem;">
                                            <option value="has_sub" <?php echo $role['auto_type'] === 'has_sub' ? 'selected' : ''; ?>>Выдаётся при активной подписке</option>
                                            <option value="no_sub" <?php echo $role['auto_type'] === 'no_sub' ? 'selected' : ''; ?>>Выдаётся без подписки</option>
                                        </select>
                                    </label>
                                </div>
                                <div>
                                    <label class="form-label">Приоритет (чем выше — тем больше доступ)</label>
                                    <input autocomplete="off" type="number" name="priority" class="form-control" value="<?php echo (int)($role['priority'] ?? 30); ?>" min="0" max="999">
                                </div>
                                <div style="grid-column: 1/-1;">
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check"></i> Сохранить</button>
                                    <button type="button" class="btn btn-sm btn-outline" onclick="toggleEdit(<?php echo $role['id']; ?>)">Отмена</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    </div>
</main>

<script>
    function toggleEdit(id) {
        var row = document.getElementById('editRow' + id);
        if (row) row.style.display = row.style.display === 'none' ? '' : 'none';
    }

    var newAuto = document.getElementById('newAutoChk');
    if (newAuto) {
        newAuto.addEventListener('change', function () {
            var wrap = document.getElementById('newAutoTypeWrap');
            if (wrap) wrap.style.display = this.checked ? '' : 'none';
        });
    }

    var autoChks = document.querySelectorAll('.autoChk');
    for (var i = 0; i < autoChks.length; i++) {
        autoChks[i].addEventListener('change', function () {
            var wrap = document.getElementById(this.getAttribute('data-wrap'));
            if (wrap) wrap.style.display = this.checked ? '' : 'none';
        });
    }
</script>
<?php include 'loader_js.php'; ?>
</body>
</html>
