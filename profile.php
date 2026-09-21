<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
require_once 'site_config.php';

date_default_timezone_set('Europe/Moscow');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkMaintenance();
if (!isLoggedIn()) { redirect('/login'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(403); die('Invalid CSRF token');
}

try { $pdo->query("SELECT 1 FROM user_avatars LIMIT 1"); } catch (PDOException $e) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_avatars` (`id` int(11) NOT NULL AUTO_INCREMENT,`user_id` int(11) NOT NULL,`avatar_url` varchar(500) NOT NULL,`is_file` tinyint(1) NOT NULL DEFAULT 0,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),KEY `user_id` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
try { $pdo->exec("CREATE TABLE IF NOT EXISTS `reviews` (`id` int(11) NOT NULL AUTO_INCREMENT,`user_id` int(11) NOT NULL,`username` varchar(64) NOT NULL,`avatar_url` varchar(255) DEFAULT NULL,`rating` tinyint(1) NOT NULL DEFAULT 5,`comment` text NOT NULL,`status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',`moderated_by` int(11) DEFAULT NULL,`moderated_at` datetime DEFAULT NULL,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),KEY `user_id` (`user_id`),KEY `status` (`status`),KEY `created_at` (`created_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (PDOException $e) {}

$user = getUser($pdo, $_SESSION['user_id']);
if (!$user) { session_destroy(); redirect('/login'); }
$current_user = $user;

$msg = ''; $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
    $sub_active = $user['subscription_end'] && strtotime($user['subscription_end']) > time();
    if (!$sub_active) {
        $_SESSION['msg'] = 'Отзывы могут оставлять только пользователи с активной подпиской';
        $_SESSION['msg_type'] = 'error';
    } else {
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) $rating = 5;
        if (empty($comment) || strlen($comment) < 5) {
            $_SESSION['msg'] = 'Отзыв должен содержать минимум 5 символов';
            $_SESSION['msg_type'] = 'error';
        } else {
            $stmt = $pdo->prepare("SELECT id, status FROM reviews WHERE user_id = ? AND status != 'rejected'");
            $stmt->execute([$user['id']]);
            $existing = $stmt->fetch();
            if ($existing) {
                if ($existing['status'] === 'pending') {
                    $_SESSION['msg'] = 'Ваш отзыв уже отправлен на модерацию.'; $_SESSION['msg_type'] = 'error';
                } elseif ($existing['status'] === 'approved') {
                    $_SESSION['msg'] = 'Ваш отзыв уже опубликован.'; $_SESSION['msg_type'] = 'error';
                } else {
                    $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, status = 'pending', moderated_by = NULL, moderated_at = NULL, created_at = NOW() WHERE user_id = ?");
                    $stmt->execute([$rating, $comment, $user['id']]);
                    $_SESSION['msg'] = 'Отзыв отправлен на повторную модерацию!'; $_SESSION['msg_type'] = 'success';
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO reviews (user_id, username, avatar_url, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$user['id'], $user['username'], $user['avatar_url'], $rating, $comment]);
                $_SESSION['msg'] = 'Отзыв отправлен на модерацию!'; $_SESSION['msg_type'] = 'success';
            }
        }
    }
    redirect('/profile');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($current) || empty($new) || empty($confirm)) {
            $_SESSION['msg'] = 'Заполните все поля'; $_SESSION['msg_type'] = 'error';
        } elseif (!password_verify($current, $user['password'])) {
            $_SESSION['msg'] = 'Текущий пароль неверный'; $_SESSION['msg_type'] = 'error';
        } elseif (strlen($new) < 6) {
            $_SESSION['msg'] = 'Минимум 6 символов'; $_SESSION['msg_type'] = 'error';
        } elseif ($new !== $confirm) {
            $_SESSION['msg'] = 'Пароли не совпадают'; $_SESSION['msg_type'] = 'error';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            $_SESSION['msg'] = 'Пароль изменён'; $_SESSION['msg_type'] = 'success';
        }
        redirect('/profile');
    }
    if ($_POST['action'] === 'activate_key') {
        $key = trim($_POST['license_key'] ?? '');
        $stmt = $pdo->prepare("SELECT * FROM license_keys WHERE `key` = ? AND used = 0");
        $stmt->execute([$key]); $license = $stmt->fetch();
        if (!$license) {
            $_SESSION['msg'] = 'Недействительный ключ'; $_SESSION['msg_type'] = 'error';
        } else {
            $base = ($user['subscription_end'] && strtotime($user['subscription_end']) > time()) ? strtotime($user['subscription_end']) : time();
            $new_end = date('Y-m-d H:i:s', $base + ($license['days'] * 86400));
            $stmt = $pdo->prepare("UPDATE users SET subscription_end = ?, role = 'user' WHERE id = ?");
            $stmt->execute([$new_end, $user['id']]);
            $stmt = $pdo->prepare("UPDATE license_keys SET used = 1, used_by = ?, used_at = NOW() WHERE id = ?");
            $stmt->execute([$user['id'], $license['id']]);
            $_SESSION['msg'] = 'Подписка продлена на ' . $license['days'] . ' дней'; $_SESSION['msg_type'] = 'success';
        }
        redirect('/profile');
    }
    if ($_POST['action'] === 'upload_avatar') {
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar_file'];
            if ($file['size'] > 2 * 1024 * 1024) {
                $_SESSION['msg'] = 'Файл до 2MB'; $_SESSION['msg_type'] = 'error';
            } else {
                $info = @getimagesize($file['tmp_name']);
                if (!$info) {
                    $_SESSION['msg'] = 'Не изображение'; $_SESSION['msg_type'] = 'error';
                } else {
                    $ext_map = [IMAGETYPE_JPEG=>'jpg',IMAGETYPE_PNG=>'png',IMAGETYPE_GIF=>'gif',IMAGETYPE_WEBP=>'webp'];
                    $ext = $ext_map[$info[2]] ?? null;
                    if (!$ext) {
                        $_SESSION['msg'] = 'JPG, PNG, GIF, WEBP'; $_SESSION['msg_type'] = 'error';
                    } else {
                        $dir = __DIR__ . '/uploads/avatars/';
                        if (!file_exists($dir)) mkdir($dir, 0777, true);
                        $fn = 'avatar_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $dir . $fn)) {
                            $url = '/uploads/avatars/' . $fn;
                            $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?")->execute([$url, $user['id']]);
                            $pdo->prepare("INSERT IGNORE INTO user_avatars (user_id, avatar_url, is_file) VALUES (?, ?, 1)")->execute([$user['id'], $url]);
                            $_SESSION['msg'] = 'Аватар загружен'; $_SESSION['msg_type'] = 'success';
                        } else {
                            $_SESSION['msg'] = 'Ошибка загрузки'; $_SESSION['msg_type'] = 'error';
                        }
                    }
                }
            }
        } else {
            $_SESSION['msg'] = 'Выберите файл'; $_SESSION['msg_type'] = 'error';
        }
        redirect('/profile');
    }
    if ($_POST['action'] === 'select_avatar') {
        $av_id = (int)$_POST['avatar_id'];
        $stmt = $pdo->prepare("SELECT avatar_url FROM user_avatars WHERE id = ? AND user_id = ?");
        $stmt->execute([$av_id, $user['id']]); $av = $stmt->fetch();
        if ($av) {
            $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?")->execute([$av['avatar_url'], $user['id']]);
            $_SESSION['msg'] = 'Аватар изменён'; $_SESSION['msg_type'] = 'success';
        }
        redirect('/profile');
    }
}

if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg']; $msg_type = $_SESSION['msg_type'];
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

$user = getUser($pdo, $_SESSION['user_id']);
$stmt = $pdo->prepare("SELECT DISTINCT avatar_url, id, is_file, created_at FROM user_avatars WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user['id']]); $recent_avatars = $stmt->fetchAll();

$sub_active = $user['subscription_end'] && strtotime($user['subscription_end']) > time();
$sub_days = $sub_active ? ceil((strtotime($user['subscription_end']) - time()) / 86400) : 0;
$sub_end_date = $sub_active ? date('d.m.Y', strtotime($user['subscription_end'])) : null;
$reg_date = date('d.m.Y', strtotime($user['created_at']));
$avatar_url = $user['avatar_url'] ?? null;
$site_name = htmlspecialchars($SITE_NAME ?? 'AntiPackageLeak');
$is_admin = $user && userHasRight($pdo, $user, 'can_admin');

$hwid_masked = '';
if (!empty($user['hwid'])) {
    $hwid_masked = substr((string)$user['hwid'], 0, 3) . '***';
}

$launcher_current_version = '1.0.0';
$launcher_maintenance = false;
$launcher_can_download = false;
try {
    $ls = getLauncherSettings($pdo);
    $launcher_current_version = $ls['current_version'] ?? '1.0.0';
    $launcher_maintenance = ($ls['maintenance_mode'] ?? '0') === '1';
} catch (PDOException $e) {}
$launcher_can_download = !$launcher_maintenance && $sub_active;

$launcher_launches = getTotalLaunches($pdo);

$effective_role_key = $user ? resolveRole($pdo, $user) : 'guest';
$role_info = $user ? getRoleInfo($pdo, $effective_role_key) : null;
$role_display = 'User'; $role_icon = 'fa-user';
if ($role_info) { $role_display = $role_info['name']; if (!empty($role_info['icon'])) $role_icon = $role_info['icon']; }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#08080f">
    <title><?php echo $site_name; ?> — Личный кабинет</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style><?php include 'style.css'; ?></style>
<style>
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
.hero-letter{display:inline-block;opacity:0;transform:translateY(20px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1)}
.hero-letter.vis{opacity:1;transform:translateY(0)}
.cab-tile,.cab-action{opacity:0;transition:opacity 0.4s cubic-bezier(0.22,1,0.36,1),transform 0.4s cubic-bezier(0.22,1,0.36,1)}
.cab-tile:nth-child(1),.cab-action:nth-child(1){transform:translate(-25px,-15px) rotate(-1.5deg) scale(0.95)}
.cab-tile:nth-child(2),.cab-action:nth-child(2){transform:translate(25px,-12px) rotate(1.5deg) scale(0.95)}
.cab-tile:nth-child(3),.cab-action:nth-child(3){transform:translate(-20px,15px) rotate(-1deg) scale(0.95)}
.cab-tile:nth-child(4),.cab-action:nth-child(4){transform:translate(20px,12px) rotate(1deg) scale(0.95)}
.cab-tile:nth-child(5),.cab-action:nth-child(5){transform:translate(0,18px) rotate(0) scale(0.95)}
.cab-tile:nth-child(6),.cab-action:nth-child(6){transform:translate(0,-18px) rotate(0) scale(0.95)}
.cab-tile.in,.cab-action.in{opacity:1!important;transform:translate(0,0) rotate(0) scale(1)!important}
.cab-profile{opacity:0;transform:translateY(30px);transition:all 0.6s cubic-bezier(0.22,1,0.36,1)}
.cab-profile.in{opacity:1;transform:translateY(0)}
.scroll-top{position:fixed;bottom:32px;right:32px;width:44px;height:44px;border-radius:14px;background:rgba(255,255,255,0.06);-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.08);color:#fff;font-size:16px;display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:0;transform:translateY(20px);transition:all 0.3s cubic-bezier(0.22,1,0.36,1);z-index:90;pointer-events:none}
.scroll-top.show{opacity:1;transform:translateY(0);pointer-events:auto}
.scroll-top:hover{background:rgba(var(--color-accent-rgb),0.15);border-color:rgba(var(--color-accent-rgb),0.3)}
.particles{position:fixed;inset:0;pointer-events:none;z-index:-1;overflow:hidden}
.particle{position:absolute;border-radius:50%;background:rgba(99,102,241,0.3);animation:particleFloat linear infinite}
@keyframes particleFloat{0%{transform:translateY(100vh) translateX(0) scale(0);opacity:0}10%{opacity:1;transform:translateY(80vh) translateX(10px) scale(1)}90%{opacity:1}100%{transform:translateY(-10vh) translateX(-20px) scale(0.5);opacity:0}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:0.01ms!important;transition-duration:0.01ms!important}.hero-letter,.orb{animation:none!important;opacity:1!important;transform:none!important}}
:root {
    --color-accent: <?php echo $C['accent']; ?>;
    --color-accent-rgb: <?php echo $C['accent_rgb']; ?>;
    --color-accent-light: <?php echo $C['accent_light']; ?>;
    --color-accent-dark: <?php echo $C['accent_dark']; ?>;
}
.app, .app--inner { background: transparent !important; }
.header, .header.nav { position: sticky; top: 0; z-index: 100; padding: 1.25rem 1.5rem; background: transparent; border-bottom: none; }
.header.nav.scrolled { border-bottom: none; }
.header-content { position: relative; display: flex; align-items: center; justify-content: center; gap: 1.4rem; max-width: 1060px; margin: 0 auto; background: rgba(255,255,255,0.05); -webkit-backdrop-filter: blur(30px); backdrop-filter: blur(30px); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 0.7rem 1.1rem; min-height: 60px; box-shadow: 0 18px 44px -20px rgba(0,0,0,0.55); }
.header-brand-link { position: absolute; left: 1rem; display: inline-flex; align-items: center; gap: 0.55rem; min-height: 2.25rem; flex-shrink: 0; color: inherit; text-decoration: none; max-width: 160px; }
.header-logo { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; flex-shrink: 0; filter:none; }
.header-logo img { width: 100%; height: 100%; object-fit: contain; display: block; }
.header-logo i { font-size: 15px; color: var(--color-accent); }
.header-brand { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.brand-shine { position: relative; display: inline-block; color: #fff; -webkit-text-fill-color: #ffffff; text-shadow: 0 0 8px rgba(255,255,255,0.18); isolation: isolate; }
.brand-shine:before { content: attr(data-text); position: absolute; top: 0; right: 0; bottom: 0; left: 0; pointer-events: none; background-image: linear-gradient(100deg,transparent 0%,transparent 35%,var(--color-accent,#68aeff) 50%,transparent 65%,transparent 100%); background-size: 220% 100%; background-position: 140% 0; background-repeat: no-repeat; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; animation: brand-shine 4s ease-in-out infinite;  }
@keyframes brand-shine { 0% { background-position: 140% 0; } 55%, to { background-position: -40% 0; } }
.header-nav { display: flex; align-items: center; gap: 0.15rem; transform: translateX(-3rem); }
.header-nav a { display: inline-flex; align-items: center; gap: 0.34rem; font-family: 'Inter', sans-serif; font-size: 0.77rem; font-weight: 500; color: var(--dim); text-decoration: none; line-height: 1; white-space: nowrap; padding: 6px 11px; border-radius: 11px; transition: color 0.18s ease, background 0.18s ease; }
.header-nav a:hover { color: #fff; background: var(--panel); }
.header-nav a.active { color: #fff; background: rgba(var(--color-accent-rgb),0.12); }
.header-nav a i { font-size: 0.8rem; }
.header-actions { position: absolute; right: 1rem; display: flex; align-items: center; gap: 0.45rem; }
.header-action { display: inline-flex; align-items: center; justify-content: center; gap: 0.38rem; min-height: 2.25rem; padding: 0 0.8rem; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; background: rgba(255,255,255,0.04); color: #fff; -webkit-text-fill-color: #ffffff; font-family: 'Inter', sans-serif; font-size: 0.82rem; font-weight: 500; line-height: 1; text-decoration: none; white-space: nowrap; cursor: pointer; transition: background 0.18s ease, border-color 0.18s ease; }
.header-action:hover { border-color: rgba(255,255,255,0.13); background: rgba(255,255,255,0.09); }
.header-action--profile { padding-left: 0.45rem; }
.header-avatar { width: 22px; height: 22px; border-radius: 50%; object-fit: cover; flex-shrink: 0; pointer-events: none; }
.header-action i { font-size: 0.8rem; }
.burger { display: none; width: 42px; height: 42px; border: 1px solid var(--line2); border-radius: 12px; align-items: center; justify-content: center; font-size: 15px; color: var(--ink); }
.m-menu { display: none; flex-direction: column; gap: 4px; padding: 12px 18px 20px; border-bottom: 1px solid var(--line); }
.m-menu a { padding: 12px 14px; border-radius: 12px; font-size: 14px; font-weight: 500; color: var(--ink2); }
.m-menu a.active, .m-menu a:hover { background: var(--panel); color: var(--ink); }
.nav.open .m-menu { display: flex; }
@media (max-width: 900px) { .header-nav { display: none; } .header-content { justify-content: space-between; } .header-brand-link { position: static; left: auto; } .header-actions { position: static; right: auto; } .burger { display: inline-flex; } }
@media (max-width: 768px) { .header, .header.nav { padding: 0.9rem 1rem; } .header-content { border-radius: 16px; } .header-brand-link { max-width: 120px; } }
@media (max-width: 520px) { .header-action span { display: none; } .header-action { width: 2.25rem; padding: 0; justify-content: center; } .header-brand { font-size: 0.9rem; } }
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
<div class="particles" id="particles"></div>

<div id="root">
<div class="app app--inner">
<header class="header nav" id="nav">
<div class="header-content">
    <a class="header-brand-link" href="/main">
        <span class="header-logo"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></span>
        <span class="header-brand brand-shine" data-text="<?php echo $site_name; ?>"><?php echo $site_name; ?></span>
    </a>
    <nav class="header-nav">
        <a href="/main">Главная</a>
        <a href="/shop">Магазин</a>
        <a href="/rules">Правила</a>
        <a href="/privacy">Соглашение</a>
        <a href="/profile" class="active">Профиль</a>
    </nav>
    <div class="header-actions">
        <?php if (isLoggedIn() && $current_user): ?>
            <button class="header-action header-action--profile" type="button" onclick="location.href='/profile'">
                <img class="header-avatar" src="/assets/ava.png" alt="" onerror="this.style.display='none'">
                <span>Profile</span>
            </button>
        <?php else: ?>
            <a href="/login" class="header-action"><i class="fas fa-sign-in-alt"></i><span>Войти</span></a>
            <a href="/register" class="header-action"><i class="fas fa-user-plus"></i><span>Регистрация</span></a>
        <?php endif; ?>
        <button class="burger" id="burgerBtn" aria-label="Меню"><i class="fas fa-bars"></i></button>
    </div>
</div>
<div class="m-menu">
    <a href="/main">Главная</a>
    <a href="/shop">Магазин</a>
    <a href="/rules">Правила</a>
    <a href="/privacy">Соглашение</a>
    <a href="/profile" class="active">Профиль</a>
    <a href="/addons">Дополнения</a>
    <?php if (isLoggedIn()): ?>
        <a href="/logout">Выйти</a>
    <?php else: ?>
        <a href="/login">Войти</a>
        <a href="/register">Регистрация</a>
    <?php endif; ?>
</div>
</header>

<div class="route-view">
<div class="cabinet">
<main class="cabinet__content">
    <div class="cabinet__head">
        <div>
            <p class="cabinet__kicker">Личный кабинет</p>
            <h1 class="cabinet__title">Профиль</h1>
        </div>
        <a href="/logout" class="cabinet__logout">
            <i class="fas fa-right-from-bracket"></i> Выйти
        </a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="msg <?php echo $msg_type; ?>"><i class="fas fa-<?php echo $msg_type==='success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <section class="cab-profile">
        <div class="cab-profile__avatar">
            <img src="/assets/ava.png" alt="avatar" onerror="this.style.display='none'">
        </div>
        <div class="cab-profile__info">
            <h2 class="cab-profile__name"><?php echo htmlspecialchars($user['username']); ?></h2>
            <p class="cab-profile__email"><?php echo htmlspecialchars($user['email']); ?></p>
            <div class="cab-badges">
                <span class="cab-badge"><i class="fas <?php echo $role_icon; ?>"></i> <?php echo $role_display; ?></span>
                <span class="cab-badge"><i class="fas fa-hashtag"></i> UID <?php echo (int)$user['id']; ?></span>
            </div>
        </div>
        <div class="cab-profile__sub">
            <span class="cab-profile__sub-label">Подписка</span>
            <span class="cab-profile__sub-value"><?php echo $sub_active ? "Активна до {$sub_end_date} ({$sub_days} дн.)" : 'Нет подписки'; ?></span>
        </div>
    </section>

    <section class="cab-section">
        <div class="cab-section__head">
            <h3 class="cab-section__title">Аккаунт</h3>
            <p class="cab-section__subtitle">Подписка, доступ и состояние защиты аккаунта.</p>
        </div>
        <div class="cab-tiles">
            <div class="cab-tile">
                <span class="cab-tile__icon"><i class="fas fa-gem"></i></span>
                <span class="cab-tile__label">Подписка</span>
                <span class="cab-tile__value"><?php echo $sub_active ? "Ещё {$sub_days} дн." : 'Нет'; ?></span>
            </div>
            <div class="cab-tile">
                <span class="cab-tile__icon"><i class="fas fa-calendar"></i></span>
                <span class="cab-tile__label">Регистрация</span>
                <span class="cab-tile__value"><?php echo $reg_date; ?></span>
            </div>
            <div class="cab-tile">
                <span class="cab-tile__icon"><i class="fas fa-key"></i></span>
                <span class="cab-tile__label">Доступ</span>
                <span class="cab-tile__value"><?php echo $sub_active ? 'Активен' : 'Неактивен'; ?></span>
            </div>
            <div class="cab-tile">
                <span class="cab-tile__icon"><i class="fas fa-hashtag"></i></span>
                <span class="cab-tile__label">UID</span>
                <span class="cab-tile__value"><?php echo (int)$user['id']; ?></span>
            </div>
            <div class="cab-tile cab-tile--wide">
                <span class="cab-tile__icon"><i class="fas fa-microchip"></i></span>
                <span class="cab-tile__label">HWID</span>
                <span class="cab-tile__value"><?php echo !empty($user['hwid']) ? htmlspecialchars($hwid_masked) . ' <i class="fas fa-check" style="color:#34d399"></i>' : '—'; ?></span>
            </div>
        </div>
    </section>

    <section class="cab-section">
        <div class="cab-section__head">
            <h3 class="cab-section__title">Действия</h3>
            <p class="cab-section__subtitle">Быстрые переходы в нужные разделы.</p>
        </div>
        <div class="cab-actions">
            <a href="/shop" class="cab-action">
                <span class="cab-action__icon"><i class="fas fa-shopping-cart"></i></span>
                <span class="cab-action__text">
                    <span class="cab-action__title">Продукты</span>
                    <span class="cab-action__note">Подписки и товары</span>
                </span>
                <i class="fas fa-arrow-up-right cab-action__arrow"></i>
            </a>
            <a href="/addons" class="cab-action">
                <span class="cab-action__icon"><i class="fas fa-puzzle-piece"></i></span>
                <span class="cab-action__text">
                    <span class="cab-action__title">Дополнения</span>
                    <span class="cab-action__note">Ключ и смена пароля</span>
                </span>
                <i class="fas fa-arrow-up-right cab-action__arrow"></i>
            </a>
            <a href="/referral" class="cab-action">
                <span class="cab-action__icon"><i class="fas fa-user-plus"></i></span>
                <span class="cab-action__text">
                    <span class="cab-action__title">Реферальная программа</span>
                    <span class="cab-action__note">Приглашай друзей — получай подписку</span>
                </span>
                <i class="fas fa-arrow-up-right cab-action__arrow"></i>
            </a>
            <?php if ($launcher_can_download): ?>
                <a href="/download_launcher" class="cab-action">
                    <span class="cab-action__icon"><i class="fas fa-download"></i></span>
                    <span class="cab-action__text">
                        <span class="cab-action__title">Скачать лаунчер</span>
                        <span class="cab-action__note">Версия <?php echo htmlspecialchars($launcher_current_version); ?></span>
                    </span>
                    <i class="fas fa-arrow-up-right cab-action__arrow"></i>
                </a>
            <?php else: ?>
                <div class="cab-action cab-action--disabled">
                    <span class="cab-action__icon"><i class="fas fa-download"></i></span>
                    <span class="cab-action__text">
                        <span class="cab-action__title">Скачать лаунчер</span>
                        <span class="cab-action__note"><?php echo $launcher_maintenance ? 'Технические работы' : 'Нужна активная подписка'; ?></span>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
</div>
</div>

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
</div>
</div>

<button class="scroll-top" id="scrollTop" aria-label="Наверх"><i class="fas fa-arrow-up"></i></button>

<style>
*, *::before, *::after { -webkit-user-select: none !important; -moz-user-select: none !important; -ms-user-select: none !important; user-select: none !important; }
input, textarea { -webkit-user-select: text !important; -moz-user-select: text !important; -ms-user-select: text !important; user-select: text !important; }
</style>
<script src="/devtools.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var nav = document.getElementById('nav');
    document.getElementById('burgerBtn').addEventListener('click', function() {
        nav.classList.toggle('open');
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) { entry.target.classList.add('in'); observer.unobserve(entry.target); }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(function(el) { observer.observe(el); });
});
</script>
<script>
['mouseover','mousemove','mousedown','focus'].forEach(function(evt){document.addEventListener(evt,function(e){var a=e.target.closest('a');if(a){window.status='';setTimeout(function(){window.status=''},0)}})});
setInterval(function(){window.status=''},50);
</script>
<script src="/lang.js"></script>
<script>
(function(){
    var h1=document.querySelector('.cabinet__title');
    if(!h1||window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;
    var txt=h1.textContent;h1.innerHTML='';
    var words=txt.split(/\s+/);
    words.forEach(function(w,i){
        var span=document.createElement('span');span.className='hero-letter';span.textContent=w;h1.appendChild(span);
        if(i<words.length-1)h1.appendChild(document.createTextNode(' '));
    });
    setTimeout(function(){
        h1.querySelectorAll('.hero-letter').forEach(function(l,i){
            setTimeout(function(){l.classList.add('vis')},200+i*120);
        });
    },150);
})();
</script>
<script>
(function(){
    if(window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;
    var obs=new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if(!e.isIntersecting)return;
            var d=e.target.getAttribute('data-stagger');
            if(d)e.target.style.transitionDelay=(parseInt(d)*50)+'ms';
            e.target.classList.add('in');
            obs.unobserve(e.target);
        });
    },{threshold:0.06,rootMargin:'0px 0px -30px 0px'});
    document.querySelectorAll('.cab-tile, .cab-action').forEach(function(el,i){
        el.setAttribute('data-stagger',i);
        obs.observe(el);
    });
    var profObs=new IntersectionObserver(function(entries){
        entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');profObs.unobserve(e.target)}});
    },{threshold:0.04});
    var p=document.querySelector('.cab-profile');
    if(p)profObs.observe(p);
})();
</script>
<script>
(function(){
    if(!('ontouchstart' in window)){
        document.querySelectorAll('.cab-tile, .cab-action').forEach(function(card){
            card.addEventListener('mousemove',function(e){
                var r=card.getBoundingClientRect();
                var x=(e.clientX-r.left)/r.width;
                var y=(e.clientY-r.top)/r.height;
                card.style.setProperty('--mx',(x*100)+'%');
                card.style.setProperty('--my',(y*100)+'%');
                card.style.transform='rotateY('+(x-0.5)*8+'deg) rotateX('+(0.5-y)*8+'deg) translateZ(6px)';
                card.style.transition='transform 0.15s cubic-bezier(0.22,1,0.36,1)';
            });
            card.addEventListener('mouseleave',function(){
                card.style.transform='';
                card.style.transition='transform 0.5s cubic-bezier(0.22,1,0.36,1)';
            });
        });
    }
})();
</script>
<script>
(function(){
    var c=document.getElementById('particles');
    if(!c)return;
    for(var i=0;i<18;i++){
        var p=document.createElement('div');
        p.className='particle';
        var s=Math.random()*2+1;
        p.style.width=s+'px';p.style.height=s+'px';
        p.style.left=Math.random()*100+'%';
        p.style.animationDuration=(Math.random()*12+8)+'s';
        p.style.animationDelay=(Math.random()*10)+'s';
        p.style.opacity=Math.random()*0.4+0.1;
        c.appendChild(p);
    }
})();
</script>
<script>
(function(){
    var btn=document.getElementById('scrollTop');
    if(!btn)return;
    window.addEventListener('scroll',function(){
        btn.classList.toggle('show',window.scrollY>300);
    },{passive:true});
    btn.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'})});
})();
</script>
<?php include 'loader_js.php'; ?>
</body>
</html>
