<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
require_once 'site_config.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkMaintenance();
if (!isLoggedIn()) { redirect('/login'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(403); die('CSRF');
}
$user = getUser($pdo, $_SESSION['user_id']);
if (!$user) { session_destroy(); redirect('/login'); }
$current_user = $user;

$SITE_NAME = $SITE_NAME ?? 'AntiPackageLeak';
$msg = $_SESSION['flash_msg'] ?? ''; $msg_type = $_SESSION['flash_msg_type'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_msg_type']);
$show_2fa_success = !empty($_SESSION['flash_2fa_success']);
unset($_SESSION['flash_2fa_success']);

try { $pdo->exec("CREATE TABLE IF NOT EXISTS `user_2fa` (`user_id` int(11) NOT NULL,`secret` varchar(64) NOT NULL,`enabled` tinyint(1) NOT NULL DEFAULT 0,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (PDOException $e) {}

function totp_base32_encode($data) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = ''; foreach (str_split($data) as $c) { $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT); }
    $bits = str_split($bits, 5);
    $out = ''; foreach ($bits as $b) { $out .= $chars[bindec(str_pad($b, 5, '0'))]; }
    return $out;
}

function totp_generate_secret() {
    return totp_base32_encode(random_bytes(20));
}

function totp_code($secret, $offset = 0) {
    $secret = strtoupper($secret);
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $key = 0; foreach (str_split($secret) as $c) { $key = ($key << 5) | strpos($chars, $c); }
    $keyBytes = []; for ($i = 7; $i >= 0; $i--) { $keyBytes[] = ($key >> ($i * 8)) & 0xff; }
    $time = pack('N*', 0) . pack('N*', (int)floor(time() / 30) + $offset);
    $hmac = hash_hmac('sha1', $time, $key, true);
    $offset = ord($hmac[19]) & 0x0f;
    $code = ((ord($hmac[$offset]) & 0x7f) << 24) | ((ord($hmac[$offset+1]) & 0xff) << 16) | ((ord($hmac[$offset+2]) & 0xff) << 8) | (ord($hmac[$offset+3]) & 0xff);
    return str_pad($code % 1000000, 6, '0', STR_PAD_LEFT);
}

function totp_verify($secret, $code) {
    for ($i = -1; $i <= 1; $i++) { if (hash_equals(totp_code($secret, $i), $code)) return true; }
    return false;
}

function smtp_send($to, $subject, $body) {
    $host = 'smtp.gmail.com';
    $port = 587;
    $user = 'antiaileak@gmail.com';
    $pass = 'aoenfotbukjxetgr';
    $from = 'antiaileak@gmail.com';
    $from_name = 'antiaileak';

    $errno = 0; $errstr = '';
    $sock = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$sock) return false;

    $resp = fgets($sock, 512);
    if (strpos($resp, '220') !== 0) { fclose($sock); return false; }

    fputs($sock, "EHLO antiaileaks.ct.ws\r\n");
    while ($line = fgets($sock, 512)) { if (strpos($line, '250 ') === 0 || $line[3] === ' ') break; }

    fputs($sock, "STARTTLS\r\n");
    $resp = fgets($sock, 512);
    if (strpos($resp, '220') !== 0) { fclose($sock); return false; }

    $crypto = stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    if (!$crypto) { fclose($sock); return false; }

    fputs($sock, "EHLO antiaileaks.ct.ws\r\n");
    while ($line = fgets($sock, 512)) { if (strpos($line, '250 ') === 0 || $line[3] === ' ') break; }

    fputs($sock, "AUTH LOGIN\r\n");
    $resp = fgets($sock, 512);
    if (strpos($resp, '334') !== 0) { fclose($sock); return false; }

    fputs($sock, base64_encode($user) . "\r\n");
    $resp = fgets($sock, 512);
    fputs($sock, base64_encode($pass) . "\r\n");
    $resp = fgets($sock, 512);
    if (strpos($resp, '235') !== 0) { fclose($sock); return false; }

    fputs($sock, "MAIL FROM:<{$from}>\r\n"); fgets($sock, 512);
    fputs($sock, "RCPT TO:<{$to}>\r\n"); fgets($sock, 512);
    fputs($sock, "DATA\r\n"); fgets($sock, 512);

    $hdrs = "From: =?UTF-8?B?" . base64_encode($from_name) . "?= <{$from}>\r\n";
    $hdrs .= "To: <{$to}>\r\n";
    $hdrs .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $hdrs .= "MIME-Version: 1.0\r\n";
    $hdrs .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $hdrs .= "Content-Transfer-Encoding: base64\r\n";
    $hdrs .= "Date: " . date('r') . "\r\n";
    $hdrs .= "\r\n" . chunk_split(base64_encode($body));

    fputs($sock, $hdrs . "\r\n.\r\n");
    $resp = fgets($sock, 512);

    fputs($sock, "QUIT\r\n");
    fclose($sock);
    return true;
}
function send_2fa_email($to, $code) {
    $subject = 'AntiPackageLeak — Код подтверждения 2FA';
    $body = "Ваш код подтверждения: $code\n\nКод действителен в течение 10 минут.\nЕсли вы не запрашивали эту операцию, проигнорируйте это письмо.";
    return smtp_send($to, $subject, $body);
}

$has_2fa = false;
$secret_2fa = '';
$stmt2fa = $pdo->prepare("SELECT secret, enabled FROM user_2fa WHERE user_id = ?");
$stmt2fa->execute([$user['id']]);
$row2fa = $stmt2fa->fetch();
if ($row2fa) { $has_2fa = (bool)$row2fa['enabled']; $secret_2fa = $row2fa['secret']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'activate_key') {
    $key = trim($_POST['license_key'] ?? '');
    if (empty($key)) { $msg = 'Введите ключ'; $msg_type = 'error'; }
    else {
        $stmt = $pdo->prepare("SELECT * FROM license_keys WHERE key_code = ? AND used_by IS NULL");
        $stmt->execute([$key]); $lk = $stmt->fetch();
        if (!$lk) { $msg = 'Ключ недействителен'; $msg_type = 'error'; }
        else {
            $days = (int)($lk['days'] ?? 30);
            $now = time();
            $cur_end = ($user['subscription_end'] && strtotime($user['subscription_end']) > $now) ? strtotime($user['subscription_end']) : $now;
            $new_end = date('Y-m-d H:i:s', $cur_end + $days * 86400);
            $pdo->prepare("UPDATE users SET subscription_end = ? WHERE id = ?")->execute([$new_end, $user['id']]);
            $pdo->prepare("UPDATE license_keys SET used_by = ?, used_at = NOW() WHERE id = ?")->execute([$user['id'], $lk['id']]);
            $msg = "Подписка продлена на {$days} дней"; $msg_type = 'success';
        }
    }
    $_SESSION['flash_msg'] = $msg; $_SESSION['flash_msg_type'] = $msg_type;
    header('Location: /addons'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $cur = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $conf = $_POST['confirm_password'] ?? '';
    if (!password_verify($cur, $user['password'])) { $msg = 'Неверный текущий пароль'; $msg_type = 'error'; }
    elseif (strlen($new) < 6) { $msg = 'Минимум 6 символов'; $msg_type = 'error'; }
    elseif ($new !== $conf) { $msg = 'Пароли не совпадают'; $msg_type = 'error'; }
    else {
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        $msg = 'Пароль изменён'; $msg_type = 'success';
    }
    $_SESSION['flash_msg'] = $msg; $_SESSION['flash_msg_type'] = $msg_type;
    header('Location: /addons'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'enable_2fa') {
    $code = trim($_POST['email_code'] ?? '');
    $existing = $pdo->prepare("SELECT secret, enabled FROM user_2fa WHERE user_id = ?");
    $existing->execute([$user['id']]);
    $ex = $existing->fetch();
    if (!$ex || $ex['enabled']) { $msg = '2FA уже включена или не найдена'; $msg_type = 'error'; }
    elseif (empty($code) || strlen($code) !== 6) { $msg = 'Введите 6-значный код из письма'; $msg_type = 'error'; }
    elseif (!isset($_SESSION['email_2fa_code']) || !isset($_SESSION['email_2fa_time'])) { $msg = 'Код не был отправлен. Нажмите «Настроить 2FA» заново.'; $msg_type = 'error'; }
    elseif (time() - $_SESSION['email_2fa_time'] > 600) { unset($_SESSION['email_2fa_code'], $_SESSION['email_2fa_time']); $msg = 'Код истёк. Запросите новый.'; $msg_type = 'error'; }
    elseif (!hash_equals($_SESSION['email_2fa_code'], $code)) { $msg = 'Неверный код из письма'; $msg_type = 'error'; }
    else {
        unset($_SESSION['email_2fa_code'], $_SESSION['email_2fa_time']);
        $pdo->prepare("UPDATE user_2fa SET enabled = 1 WHERE user_id = ?")->execute([$user['id']]);
        $has_2fa = true;
        $_SESSION['flash_2fa_success'] = 1;
        header('Location: /addons'); exit;
    }
    $_SESSION['flash_msg'] = $msg; $_SESSION['flash_msg_type'] = $msg_type;
    header('Location: /addons'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'init_disable_2fa') {
    $existing = $pdo->prepare("SELECT secret, enabled FROM user_2fa WHERE user_id = ?");
    $existing->execute([$user['id']]);
    $ex = $existing->fetch();
    if (!$ex || !$ex['enabled']) { $msg = '2FA не включена'; $msg_type = 'error'; }
    else {
        $email_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['disable_2fa_code'] = $email_code;
        $_SESSION['disable_2fa_time'] = time();
        $email_sent = send_2fa_email($user['email'], $email_code);
        $msg = $email_sent ? 'Код подтверждения отправлен на вашу почту.' : 'Ошибка отправки письма. Попробуйте позже.';
        $msg_type = $email_sent ? 'success' : 'error';
    }
    $_SESSION['flash_msg'] = $msg; $_SESSION['flash_msg_type'] = $msg_type;
    header('Location: /addons'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'disable_2fa') {
    $code = trim($_POST['email_code'] ?? '');
    $existing = $pdo->prepare("SELECT secret, enabled FROM user_2fa WHERE user_id = ?");
    $existing->execute([$user['id']]);
    $ex = $existing->fetch();
    if (!$ex || !$ex['enabled']) { $msg = '2FA не включена'; $msg_type = 'error'; }
    elseif (empty($code) || strlen($code) !== 6) { $msg = 'Введите 6-значный код из письма'; $msg_type = 'error'; }
    elseif (!isset($_SESSION['disable_2fa_code']) || !isset($_SESSION['disable_2fa_time'])) { $msg = 'Код не был отправлен. Нажмите «Отправить код» заново.'; $msg_type = 'error'; }
    elseif (time() - $_SESSION['disable_2fa_time'] > 600) { unset($_SESSION['disable_2fa_code'], $_SESSION['disable_2fa_time']); $msg = 'Код истёк. Запросите новый.'; $msg_type = 'error'; }
    elseif (!hash_equals($_SESSION['disable_2fa_code'], $code)) { $msg = 'Неверный код из письма'; $msg_type = 'error'; }
    else {
        unset($_SESSION['disable_2fa_code'], $_SESSION['disable_2fa_time']);
        $pdo->prepare("UPDATE user_2fa SET enabled = 0 WHERE user_id = ?")->execute([$user['id']]);
        $has_2fa = false;
        $msg = 'Двухфакторная аутентификация отключена'; $msg_type = 'success';
    }
    $_SESSION['flash_msg'] = $msg; $_SESSION['flash_msg_type'] = $msg_type;
    header('Location: /addons'); exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'init_2fa') {
    $existing = $pdo->prepare("SELECT secret, enabled FROM user_2fa WHERE user_id = ?");
    $existing->execute([$user['id']]);
    $ex = $existing->fetch();
    if ($ex && $ex['enabled']) { $msg = '2FA уже включена'; $msg_type = 'error'; }
    else {
        $email_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['email_2fa_code'] = $email_code;
        $_SESSION['email_2fa_time'] = time();
        $new_secret = totp_generate_secret();
        if ($ex) { $pdo->prepare("UPDATE user_2fa SET secret = ?, enabled = 0 WHERE user_id = ?")->execute([$new_secret, $user['id']]); }
        else { $pdo->prepare("INSERT INTO user_2fa (user_id, secret, enabled) VALUES (?, ?, 0)")->execute([$user['id'], $new_secret]); }
        $secret_2fa = $new_secret;
        $email_sent = send_2fa_email($user['email'], $email_code);
        $msg = $email_sent ? 'Код подтверждения отправлен на вашу почту. Проверьте входящие.' : 'Ошибка отправки письма. Попробуйте позже.';
        $msg_type = $email_sent ? 'success' : 'error';
    }
    $_SESSION['flash_msg'] = $msg; $_SESSION['flash_msg_type'] = $msg_type;
    header('Location: /addons'); exit;
}

$site_name = htmlspecialchars($SITE_NAME);
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
<title>Дополнения — <?php echo $site_name; ?></title>
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--bg:<?php echo $C['bg']; ?>;--bg2:<?php echo $C['bg2']; ?>;--panel:<?php echo $C['panel']; ?>;--panel-h:<?php echo $C['panel_h']; ?>;--line:<?php echo $C['line']; ?>;--line2:<?php echo $C['line2']; ?>;--accent:<?php echo $C['accent']; ?>;--accent-rgb:<?php echo $C['accent_rgb']; ?>;--grad1:<?php echo $C['grad1']; ?>;--grad2:<?php echo $C['grad2']; ?>;--grad3:<?php echo $C['grad3']; ?>;--ink:<?php echo $C['ink']; ?>;--ink2:<?php echo $C['ink2']; ?>;--dim:<?php echo $C['dim']; ?>;--faint:<?php echo $C['faint']; ?>;--success:<?php echo $C['success']; ?>;--danger:<?php echo $C['danger']; ?>;--grad:linear-gradient(135deg,var(--grad1),var(--grad2) 55%,var(--grad3));--accent-light:<?php echo $C['accent_light']; ?>;--accent-dark:<?php echo $C['accent_dark']; ?>;--pink:<?php echo $C['pink']; ?>}
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
@media(max-width:520px){.header-action span{display:none}.header-action{width:2.25rem;padding:0;justify:center}.header-brand{font-size:0.9rem}}

.page{padding:20px 0 100px}
.cabinet__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:30px;padding-top:20px}
.cabinet__kicker{font-size:12px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);margin-bottom:6px}
.cabinet__title{font-family:'Sora',sans-serif;font-size:clamp(24px,3.5vw,34px);font-weight:700;letter-spacing:-0.02em;color:#fff}
.cabinet__logout{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:1px solid rgba(248,113,113,0.3);background:rgba(248,113,113,0.06);color:#fca5a5;font-size:13px;font-weight:600;text-decoration:none;transition:all 0.2s ease}
.cabinet__logout:hover{background:rgba(248,113,113,0.12);border-color:rgba(248,113,113,0.5)}

.toast-container{position:fixed;top:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none}
.toast{pointer-events:auto;padding:14px 22px;border-radius:14px;font-size:13.5px;font-weight:500;display:flex;align-items:center;gap:10px;backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);box-shadow:0 12px 40px -12px rgba(0,0,0,0.5);transform:translateX(calc(100% + 40px));opacity:0;transition:transform 0.45s cubic-bezier(0.22,1,0.36,1),opacity 0.35s ease}
.toast.show{transform:translateX(0);opacity:1}
.toast.success{border:1px solid rgba(16,185,129,0.35);background:rgba(16,185,129,0.1);color:#6ee7b7}
.toast.error{border:1px solid rgba(248,113,113,0.35);background:rgba(248,113,113,0.1);color:#fca5a5}
.toast i{font-size:15px}
.toast .toast-close{margin-left:auto;opacity:0.5;cursor:pointer;font-size:12px;transition:opacity 0.2s}
.toast .toast-close:hover{opacity:1}

.cab-section{margin-bottom:24px;opacity:0;transform:translateY(30px)}
.cab-section.revealed{opacity:1;transform:translateY(0);transition:opacity 0.6s cubic-bezier(0.22,1,0.36,1),transform 0.6s cubic-bezier(0.22,1,0.36,1)}
.cab-section__head{margin-bottom:14px}
.cab-section__title{font-family:'Sora',sans-serif;font-size:16px;font-weight:700;color:#fff;margin-bottom:4px}
.cab-section__subtitle{font-size:13px;color:var(--dim)}

.cab-form-card{padding:24px;border-radius:18px;border:1px solid var(--line);background:var(--panel);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px);opacity:0;transform:scale(0.92);transition:opacity 0.5s cubic-bezier(0.22,1,0.36,1),transform 0.5s cubic-bezier(0.22,1,0.36,1);will-change:transform}
.cab-form-card.visible{opacity:1;transform:scale(1)}
.cab-form-card:hover{border-color:rgba(var(--accent-rgb),0.25)}

.field{display:flex;align-items:center;gap:8px;padding:0 14px;height:44px;border-radius:12px;border:1px solid var(--line2);background:rgba(255,255,255,0.03);color:#fff;transition:border-color 0.2s,box-shadow 0.3s;opacity:0;transform:translateX(-30px)}
.field.slide-in{opacity:1;transform:translateX(0);transition:opacity 0.4s cubic-bezier(0.22,1,0.36,1),transform 0.4s cubic-bezier(0.22,1,0.36,1),border-color 0.2s,box-shadow 0.3s}
.field:focus-within{border-color:rgba(var(--accent-rgb),0.5);box-shadow:0 0 0 3px rgba(var(--accent-rgb),0.08)}
.field i{color:var(--faint);font-size:13px;flex-shrink:0}
.field input{flex:1;background:none;border:none;outline:none;color:#fff;font-family:'Inter',sans-serif;font-size:13.5px;width:100%}
.field input::placeholder{color:var(--faint)}

.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:44px;padding:0 22px;border-radius:12px;font-family:'Inter',sans-serif;font-size:13.5px;font-weight:700;border:none;cursor:pointer;transition:transform 0.25s cubic-bezier(0.22,1,0.36,1),box-shadow 0.25s ease;text-decoration:none;opacity:0;transform:translateY(16px)}
.btn.slide-in{opacity:1;transform:translateY(0);transition:transform 0.25s cubic-bezier(0.22,1,0.36,1),box-shadow 0.25s ease}
.btn-primary{background:var(--grad);color:#fff;box-shadow:0 10px 28px -10px rgba(var(--accent-rgb),0.5)}
.btn-primary:hover{transform:translateY(-2px) scale(1.02);box-shadow:0 14px 34px -10px rgba(var(--accent-rgb),0.65),0 0 24px rgba(var(--accent-rgb),0.3)}
.btn-primary:active{transform:translateY(0) scale(0.98)}

.cab-form-card .field+.field{margin-top:10px}
.cab-form-card .btn{margin-top:14px;width:100%}

.particles-wrap{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.particle{position:absolute;border-radius:50%;background:var(--accent);opacity:0;animation:floatParticle linear infinite}
@keyframes floatParticle{
0%{transform:translateY(100vh) scale(0);opacity:0}
10%{opacity:0.25}
50%{opacity:0.12}
90%{opacity:0.25}
100%{transform:translateY(-20vh) scale(1);opacity:0}
}

.footer{position:relative;background:transparent;padding:0 20px 32px}
.footer{margin-top:80px;border-top:0}
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
    .cab-section, .cab-form-card, .field, .btn { opacity:1 !important; transform:none !important; }
}
.twofa-status{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;font-size:13px;font-weight:600;margin-bottom:14px}
.twofa-status.on{border:1px solid rgba(16,185,129,0.3);background:rgba(16,185,129,0.08);color:#6ee7b7}
.twofa-status.on i{font-size:15px;color:#34d399}
.twofa-setup{display:flex;gap:24px;align-items:flex-start;margin-bottom:18px;padding:24px;border-radius:18px;border:1px solid var(--line);background:var(--panel);backdrop-filter:blur(16px)}
.twofa-qr{flex-shrink:0;border-radius:14px;overflow:hidden;background:#fff;padding:8px}
.twofa-qr img{display:block;width:180px;height:180px;border-radius:8px}
.twofa-secret{flex:1;min-width:0}
.twofa-secret__label{font-size:13px;color:var(--dim);margin-bottom:8px}
.twofa-secret__code{display:block;padding:10px 14px;border-radius:10px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);font-family:'Sora',monospace;font-size:16px;font-weight:700;color:#fff;letter-spacing:0.08em;word-break:break-all;line-height:1.5}
.twofa-secret__hint{font-size:12px;color:var(--faint);margin-top:10px}
@media(max-width:600px){.twofa-setup{flex-direction:column;align-items:center}.twofa-qr img{width:160px;height:160px}}
.overlay-2fa{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(12px);background:rgba(8,8,15,0.8);opacity:0;pointer-events:none;transition:opacity .3s ease}
.overlay-2fa.show{opacity:1;pointer-events:all}
.overlay-2fa__card{max-width:420px;width:90%;background:var(--panel);border:1px solid var(--line);border-radius:24px;padding:36px 32px;text-align:center;transform:translateY(20px) scale(.96);transition:transform .3s ease;box-shadow:0 32px 64px rgba(0,0,0,.5)}
.overlay-2fa.show .overlay-2fa__card{transform:translateY(0) scale(1)}
.overlay-2fa__icon{width:64px;height:64px;margin:0 auto 20px;border-radius:20px;background:rgba(var(--accent-rgb),0.12);display:flex;align-items:center;justify-content:center;font-size:28px;color:var(--accent)}
.overlay-2fa__title{font-family:'Sora',sans-serif;font-size:20px;font-weight:700;color:#fff;margin-bottom:10px}
.overlay-2fa__text{font-size:14px;color:var(--dim);line-height:1.6;margin-bottom:28px}
.overlay-2fa__btns{display:flex;gap:12px;justify-content:center}
.overlay-2fa__btn{padding:12px 28px;border-radius:14px;font-size:14px;font-weight:600;cursor:pointer;border:none;transition:all .2s ease}
.overlay-2fa__btn--cancel{background:rgba(255,255,255,0.06);color:var(--dim);border:1px solid rgba(255,255,255,0.08)}
.overlay-2fa__btn--cancel:hover{background:rgba(255,255,255,0.1);color:#fff}
.overlay-2fa__btn--ok{background:var(--accent);color:#fff}
.overlay-2fa__btn--ok:hover{filter:brightness(1.1);transform:translateY(-1px)}
.twofa-success{position:fixed;inset:0;z-index:10000;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px 24px;background:var(--bg) url('assets/img/background.jpg') center/cover no-repeat fixed}
.twofa-success .bg-fx{position:absolute;inset:0;z-index:-1;pointer-events:none;overflow:hidden}
.twofa-success .bg-fx::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,0.5);-webkit-backdrop-filter:blur(16px) saturate(1.2);backdrop-filter:blur(16px) saturate(1.2)}
.twofa-success h1{font-family:'Sora',sans-serif;font-size:clamp(80px,15vw,180px);font-weight:800;line-height:1;color:#fff;opacity:0;transform:translateY(24px);transition:all .55s cubic-bezier(.22,1,.36,1)}
.twofa-success h1.vis{opacity:1;transform:translateY(0)}
.twofa-success h2{font-family:'Sora',sans-serif;font-size:clamp(24px,4vw,36px);font-weight:700;color:rgba(255,255,255,.8);margin:12px 0 16px;opacity:0;transform:translateY(20px);transition:all .5s cubic-bezier(.22,1,.36,1) .06s}
.twofa-success h2.vis{opacity:1;transform:translateY(0)}
.twofa-success p{font-size:17px;color:rgba(255,255,255,.4);max-width:460px;line-height:1.75;margin-bottom:44px;opacity:0;transform:translateY(18px);transition:all .48s cubic-bezier(.22,1,.36,1) .12s}
.twofa-success p.vis{opacity:1;transform:translateY(0)}
.twofa-success .success-icon{width:80px;height:80px;margin:0 auto 24px;border-radius:50%;background:rgba(16,185,129,0.12);border:2px solid rgba(16,185,129,0.3);display:flex;align-items:center;justify-content:center;font-size:36px;color:#34d399;opacity:0;transform:scale(.5);transition:all .5s cubic-bezier(.22,1,.36,1)}
.twofa-success .success-icon.vis{opacity:1;transform:scale(1)}
</style>
<?php include 'loader_css.php'; ?>
</head>
<body>
<?php include 'loader_html.php'; ?>

<div class="particles-wrap" id="particles"></div>

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
            <span class="header-brand brand-shine" data-text="<?php echo $SITE_NAME; ?>"><?php echo $SITE_NAME; ?></span>
        </a>
        <nav class="header-nav">
            <a href="/main">Главная</a>
            <a href="/shop">Магазин</a>
            <a href="/rules">Правила</a>
            <a href="/privacy">Соглашение</a>
            <a href="/addons" class="active">Дополнения</a>
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
        <a href="/addons" class="active">Дополнения</a>
        <a href="/profile">Профиль</a>
        <a href="/logout">Выйти</a>
    </div>
</header>

<main class="page">
    <div class="container">
        <div class="cabinet__head">
            <div>
                <p class="cabinet__kicker" id="cabinetKicker">Дополнения</p>
                <h1 class="cabinet__title" id="cabinetTitle">Настройки</h1>
            </div>
            <a href="/profile" class="cabinet__logout" style="border-color:rgba(var(--accent-rgb),0.3);background:rgba(var(--accent-rgb),0.06);color:var(--accent-light)">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="msg <?php echo $msg_type; ?>" id="phpMsg" data-type="<?php echo $msg_type; ?>"><i class="fas fa-<?php echo $msg_type==='success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <section class="cab-section" data-reveal>
            <div class="cab-section__head">
                <h3 class="cab-section__title">Активация ключа</h3>
                <p class="cab-section__subtitle">Введи ключ, чтобы продлить подписку.</p>
            </div>
            <div class="cab-form-card">
                <form method="POST" novalidate>
                    <div class="field" data-field="0">
                        <i class="fas fa-key"></i>
                        <input type="text" name="license_key" placeholder="AntiPackageLeak-XXX-XXX" autocomplete="off" required>
                    </div>
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>" autocomplete="off">
                    <input type="hidden" name="action" value="activate_key" autocomplete="off">
                    <button type="submit" class="btn btn-primary" data-field="1" style="width:100%"><i class="fas fa-check"></i> Активировать</button>
                </form>
            </div>
        </section>

        <section class="cab-section" data-reveal>
            <div class="cab-section__head">
                <h3 class="cab-section__title">Смена пароля</h3>
                <p class="cab-section__subtitle">Новый пароль для входа в кабинет.</p>
            </div>
            <div class="cab-form-card">
                <form method="POST" novalidate>
                    <div class="field" data-field="0">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="new_password" placeholder="Новый пароль" autocomplete="off" required>
                    </div>
                    <div class="field" data-field="1">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" placeholder="Подтвердите пароль" autocomplete="off" required>
                    </div>
                    <div class="field" data-field="2">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="current_password" placeholder="Текущий пароль" autocomplete="off" required>
                    </div>
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>" autocomplete="off">
                    <input type="hidden" name="action" value="change_password" autocomplete="off">
                    <button type="submit" class="btn btn-primary" data-field="3" style="width:100%"><i class="fas fa-sync-alt"></i> Сменить пароль</button>
                </form>
            </div>
        </section>

        <section class="cab-section" data-reveal>
            <div class="cab-section__head">
                <h3 class="cab-section__title"><i class="fas fa-shield-halved" style="color:var(--accent);margin-right:6px"></i>Двухфакторная аутентификация</h3>
                <p class="cab-section__subtitle"><?php echo $has_2fa ? 'Двухфакторная защита включена. Отсканируйте QR-код в Google Authenticator или аналоге.' : 'Защити аккаунт кодом из приложения-аутентификатора.'; ?></p>
            </div>

            <?php if ($has_2fa): ?>
            <div class="twofa-status on">
                <i class="fas fa-shield-check"></i>
                <span>2FA активна</span>
            </div>
            <?php if (!empty($_SESSION['disable_2fa_code'])): ?>
            <div class="cab-form-card" data-reveal>
                <p style="font-size:13px;color:var(--ink2);margin-bottom:12px">Код подтверждения отправлен на <?php echo htmlspecialchars($user['email']); ?>. Введите его ниже.</p>
                <form method="POST" novalidate id="disable2faForm">
                    <div class="field" data-field="0">
                        <i class="fas fa-key"></i>
                        <input type="text" name="email_code" placeholder="6-значный код из письма" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="off" required>
                    </div>
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>" autocomplete="off">
                    <input type="hidden" name="action" value="disable_2fa" autocomplete="off">
                    <button type="submit" class="btn btn-primary" data-field="1" style="width:100%;background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 10px 28px -10px rgba(239,68,68,0.5)"><i class="fas fa-shield-xmark"></i> Отключить 2FA</button>
                </form>
            </div>
            <?php else: ?>
            <div class="cab-form-card" data-reveal>
                <form method="POST" novalidate>
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>" autocomplete="off">
                    <input type="hidden" name="action" value="init_disable_2fa" autocomplete="off">
                    <button type="submit" class="btn btn-primary" data-field="0" style="width:100%;background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 10px 28px -10px rgba(239,68,68,0.5)"><i class="fas fa-shield-xmark"></i> Отключить 2FA</button>
                </form>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="cab-form-card" data-reveal>
                <button type="button" class="btn btn-primary" data-field="0" style="width:100%" onclick="document.getElementById('overlay2fa').classList.add('show')"><i class="fas fa-shield-halved"></i> <?php echo empty($secret_2fa) ? 'Настроить 2FA' : 'Настроить 2FA заново'; ?></button>
            </div>
            <?php if (!empty($secret_2fa) && !empty($_SESSION['email_2fa_code'])): ?>
            <div class="cab-form-card" data-reveal style="margin-top:16px">
                <p style="font-size:13px;color:var(--ink2);margin-bottom:12px">Код отправлен на <?php echo htmlspecialchars($user['email']); ?>. Введите его ниже.</p>
                <form method="POST" novalidate>
                    <div class="field" data-field="0">
                        <i class="fas fa-key"></i>
                        <input type="text" name="email_code" placeholder="6-значный код из письма" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="off" required>
                    </div>
                    <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>" autocomplete="off">
                    <input type="hidden" name="action" value="enable_2fa" autocomplete="off">
                    <button type="submit" class="btn btn-primary" data-field="1" style="width:100%"><i class="fas fa-shield-check"></i> Подтвердить и включить</button>
                </form>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php if ($show_2fa_success): ?>
<div class="twofa-success" id="twofaSuccess">
    <div class="bg-fx">
        <div class="shader-orbs"><div class="orb orb--1"></div><div class="orb orb--2"></div><div class="orb orb--3"></div><div class="orb orb--4"></div></div>
    </div>
    <div class="success-icon" id="sfIcon"><i class="fas fa-check"></i></div>
    <h1 id="sfH1">Вуа-ля!</h1>
    <h2 id="sfH2">Двухфакторная аутентификация включена</h2>
    <p id="sfP">При входе на аккаунт код будет отправлен на вашу почту.<br>Страница обновится автоматически через 3 секунды.</p>
</div>
<script>
(function(){
    setTimeout(function(){document.querySelectorAll('.twofa-success .success-icon,.twofa-success h1,.twofa-success h2,.twofa-success p').forEach(function(el){el.classList.add('vis')})},60);
    setTimeout(function(){location.reload()},3000);
})();
</script>
<?php endif; ?>

<footer class="footer">
<div class="footer__inner">
    <div class="footer__brand-block">
        <div class="footer__brand">
            <div class="footer__mark"><img src="/assets/logo.png" alt="" style="width:24px;height:24px;object-fit:contain" onerror="this.style.display='none'"></div>
            <span class="footer__brand-name"><?php echo $SITE_NAME; ?></span>
        </div>
        <p class="footer__copyright">&copy; <?php echo $SITE_NAME; ?> <?php echo date('Y'); ?>. Все права защищены.</p>
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
    <p class="footer__credit">made by <a class="footer__credit-link" href="https://t.me/kodexnull" target="_blank" rel="noopener noreferrer">kodexnull</a> special for <a class="footer__credit-link" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank" rel="noopener noreferrer"><?php echo $SITE_NAME; ?></a></p>
</div>
</footer>

<div class="toast-container" id="toastContainer"></div>

<script src="/devtools.js"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){

/* burger */
var nav=document.getElementById('nav');
document.getElementById('burgerBtn').addEventListener('click',function(){nav.classList.toggle('open')});

/* floating particles */
(function(){
var wrap=document.getElementById('particles'),count=8;
for(var i=0;i<count;i++){
var p=document.createElement('div');
p.className='particle';
var size=Math.random()*4+2;
p.style.width=size+'px';
p.style.height=size+'px';
p.style.left=Math.random()*100+'%';
p.style.animationDuration=(Math.random()*12+10)+'s';
p.style.animationDelay=(Math.random()*10)+'s';
p.style.opacity=Math.random()*0.2+0.05;
wrap.appendChild(p);
}
})();

/* letter-by-letter staggered heading reveal */
(function(){
var title=document.getElementById('cabinetTitle');
if(!title)return;
var text=title.textContent;
title.textContent='';
title.style.opacity='1';
for(var i=0;i<text.length;i++){
var span=document.createElement('span');
span.textContent=text[i];
span.style.display='inline-block';
span.style.opacity='0';
span.style.transform='translateY(14px)';
span.style.transition='opacity 0.35s ease '+(i*0.04)+'s, transform 0.35s ease '+(i*0.04)+'s';
title.appendChild(span);
}
requestAnimationFrame(function(){
requestAnimationFrame(function(){
var chars=title.querySelectorAll('span');
for(var j=0;j<chars.length;j++){
chars[j].style.opacity='1';
chars[j].style.transform='translateY(0)';
}
});
});
})();

/* cab-section scroll reveal via IntersectionObserver */
(function(){
var sections=document.querySelectorAll('.cab-section[data-reveal]');
if(!('IntersectionObserver' in window)){
for(var i=0;i<sections.length;i++) sections[i].classList.add('revealed');
return;
}
var obs=new IntersectionObserver(function(entries){
entries.forEach(function(entry){
if(entry.isIntersecting){
var el=entry.target;
el.classList.add('revealed');
var cards=el.querySelectorAll('.cab-form-card');
cards.forEach(function(card,i){
setTimeout(function(){card.classList.add('visible');animateFields(card);},120+i*150);
});
obs.unobserve(el);
}
});
},{threshold:0.15});
sections.forEach(function(s){obs.observe(s);});
})();

function animateFields(card){
var fields=card.querySelectorAll('.field[data-field]');
var btn=card.querySelector('.btn[data-field]');
fields.forEach(function(f){
var d=parseInt(f.getAttribute('data-field'))||0;
setTimeout(function(){f.classList.add('slide-in');},100+d*120);
});
if(btn){
var bd=parseInt(btn.getAttribute('data-field'))||0;
setTimeout(function(){btn.classList.add('slide-in');},100+bd*120);
}
}

/* Toast notification */
function showToast(text,type){
var container=document.getElementById('toastContainer');
var t=document.createElement('div');
t.className='toast '+type;
t.innerHTML='<i class="fas fa-'+(type==='success'?'check-circle':'exclamation-circle')+'"></i><span>'+text+'</span><span class="toast-close"><i class="fas fa-times"></i></span>';
container.appendChild(t);
requestAnimationFrame(function(){requestAnimationFrame(function(){t.classList.add('show');})});
function dismiss(){t.classList.remove('show');setTimeout(function(){if(t.parentNode)t.parentNode.removeChild(t);},400);}
t.querySelector('.toast-close').addEventListener('click',dismiss);
setTimeout(dismiss,3000);
}

/* display PHP messages as toasts */
var phpMsg=document.getElementById('phpMsg');
if(phpMsg){
var type=phpMsg.getAttribute('data-type')||'success';
showToast(phpMsg.textContent.trim(),type);
phpMsg.style.display='none';
}

});
</script>
<script>
['mouseover','mousemove','mousedown','focus'].forEach(function(evt){document.addEventListener(evt,function(e){var a=e.target.closest('a');if(a){window.status='';setTimeout(function(){window.status=''},0)}})});
setInterval(function(){window.status=''},50);
</script>
<script src="/lang.js"></script>
<?php include 'loader_js.php'; ?>

<div class="overlay-2fa" id="overlay2fa" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="overlay-2fa__card">
        <div class="overlay-2fa__icon"><i class="fas fa-envelope-circle-check"></i></div>
        <h3 class="overlay-2fa__title">Настройка 2FA</h3>
        <p class="overlay-2fa__text">Все коды подтверждения будут отправлены на вашу почту, привязанную к аккаунту.<br><br>Убедитесь, что у вас есть доступ к ней.</p>
        <div class="overlay-2fa__btns">
            <button class="overlay-2fa__btn overlay-2fa__btn--cancel" onclick="document.getElementById('overlay2fa').classList.remove('show')">Отмена</button>
            <form method="POST" style="display:inline">
                <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>" autocomplete="off">
                <input type="hidden" name="action" value="init_2fa" autocomplete="off">
                <button type="submit" class="overlay-2fa__btn overlay-2fa__btn--ok">Продолжить</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
