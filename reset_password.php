<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'site_config.php';
require_once 'colors_loader.php';

function smtp_send($to, $subject, $body) {
    $host = 'smtp.gmail.com';
    $port = 587;
    $user = 'antiaileak@gmail.com';
    $pass = 'aoenfotbukjxetgr';
    $from = 'antiaileak@gmail.com';
    $from_name = 'AntiPackageLeak';

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

session_start();
checkMaintenance();

$SITE_NAME = $SITE_NAME ?? 'AntiPackageLeak';
$error = '';
$success = '';
$step = isset($_SESSION['reset_step']) ? $_SESSION['reset_step'] : 1;
$email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';

if (isLoggedIn()) {
    redirect('/profile');
}

// Step 1: Send code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_code') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Введите email';
    } else {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            $error = 'Email не найден';
        } else {
            $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $_SESSION['reset_code'] = $code;
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_step'] = 2;
            $_SESSION['reset_code_time'] = time();
            $step = 2;

            $subject = "{$SITE_NAME} — Код восстановления пароля";
            $message = "Ваш код для восстановления пароля: {$code}\n\nЕсли вы не запрашивали восстановление — просто проигнорируйте это письмо.\n\n{$SITE_NAME}";
            smtp_send($email, $subject, $message);
            $success = "Код отправлен на {$email}";
        }
    }
}

// Step 2: Verify code + reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_code') {
    $code = trim($_POST['code'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($code) || empty($new_pass) || empty($confirm)) {
        $error = 'Заполните все поля';
    } elseif ($code !== ($_SESSION['reset_code'] ?? '')) {
        $error = 'Неверный код';
    } elseif (time() - ($_SESSION['reset_code_time'] ?? 0) > 600) {
        $error = 'Код истёк. Запросите новый.';
        $_SESSION['reset_step'] = 1;
        $step = 1;
    } elseif (strlen($new_pass) < 6) {
        $error = 'Минимум 6 символов';
    } elseif ($new_pass !== $confirm) {
        $error = 'Пароли не совпадают';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([password_hash($new_pass, PASSWORD_DEFAULT), $_SESSION['reset_user_id']]);
        unset($_SESSION['reset_code'], $_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_code_time']);
        $success = 'Пароль изменён! Теперь войдите.';
        $step = 3;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restart') {
    unset($_SESSION['reset_code'], $_SESSION['reset_user_id'], $_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_code_time']);
    $step = 1;
}
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
    <title>Восстановление — <?php echo htmlspecialchars($SITE_NAME); ?></title>
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
:root{--bg:#08080f;--bg2:#0c0c17;--panel:rgba(255,255,255,.028);--panel-h:rgba(255,255,255,.045);--line:rgba(255,255,255,.07);--line2:rgba(255,255,255,.13);--ink:#eceefb;--dim:rgba(255,255,255,.4);--faint:rgba(255,255,255,.2);--accent:#a78bfa;--accent-rgb:167,139,250;--accent2:rgba(167,139,250,.12);--danger:#f87171;--success:#34d399}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;display:flex;flex-direction:column;-webkit-user-select:none;user-select:none}
.bg-fx{position:fixed;inset:0;z-index:0;pointer-events:none;background:var(--bg) url("/assets/img/background.jpg") center/cover no-repeat}
.bg-fx::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,.65);-webkit-backdrop-filter:blur(18px) saturate(1.1);backdrop-filter:blur(18px) saturate(1.1)}
main{position:relative;z-index:1;flex:1;display:flex;align-items:center;justify-content:center;padding:24px}
.auth-card{width:100%;max-width:400px;background:var(--panel);border:1px solid var(--line);border-radius:20px;padding:32px 28px 28px;position:relative;overflow:hidden}
.auth-card::before{content:"";position:absolute;inset:-1px;border-radius:20px;padding:1px;background:linear-gradient(135deg,rgba(var(--accent-rgb),.18),transparent 40%);-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none}
.auth-icon{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,var(--accent),#7c3aed);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;margin:0 auto 18px;box-shadow:0 12px 32px -8px rgba(var(--accent-rgb),.45)}
.auth-title{font-family:'Sora',sans-serif;font-size:22px;font-weight:700;text-align:center;margin-bottom:6px;letter-spacing:-.02em}
.auth-subtitle{text-align:center;font-size:13px;color:var(--dim);margin-bottom:24px;line-height:1.5}
.field{position:relative;margin-bottom:14px}
.field i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--faint);font-size:14px;transition:color .2s}
.field input{width:100%;height:46px;padding:0 14px 0 40px;border:1px solid var(--line);border-radius:12px;background:rgba(255,255,255,.03);color:var(--ink);font-family:'Inter',sans-serif;font-size:14px;outline:none;transition:border-color .2s,background .2s}
.field input::placeholder{color:var(--faint)}
.field input:focus{border-color:rgba(var(--accent-rgb),.45);background:rgba(var(--accent-rgb),.04)}
.field input:focus+i{color:var(--accent)}
.btn{width:100%;height:46px;border:none;border-radius:12px;font-family:'Inter',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-primary{background:linear-gradient(135deg,var(--accent),#7c3aed);color:#fff;box-shadow:0 10px 30px -8px rgba(var(--accent-rgb),.45)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 16px 40px -8px rgba(var(--accent-rgb),.6)}
.btn-primary:active{transform:translateY(0)}
.auth-footer{text-align:center;margin-top:18px;font-size:13px;color:var(--dim)}
.auth-footer a{color:var(--accent);text-decoration:none;transition:opacity .2s}
.auth-footer a:hover{opacity:.8}
.msg{padding:10px 14px;border-radius:10px;font-size:12.5px;margin-bottom:16px;display:flex;align-items:center;gap:8px;line-height:1.4}
.msg.error{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);color:var(--danger)}
.msg.success{background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);color:var(--success)}
.footer{text-align:center;padding:20px;font-size:11px;color:var(--faint);position:relative;z-index:1}
.footer a{color:var(--accent);text-decoration:none}
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            .orb, .halo, .veil { animation: none !important; }
        }</style>
</head>
<body>
<div class="bg-fx"></div>
<main>
    <div class="auth-card">
        <div class="auth-icon"><i class="fas fa-lock"></i></div>
        <h1 class="auth-title">Восстановление</h1>
        <p class="auth-subtitle">
            <?php if ($step === 1): ?>
                Введите email — мы отправим код для сброса пароля
            <?php elseif ($step === 2): ?>
                Введите код из письма и новый пароль
            <?php else: ?>
                Пароль успешно изменён!
            <?php endif; ?>
        </p>

        <?php if ($error): ?>
            <div class="msg error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
        <form method="POST" novalidate>
            <input type="hidden" name="action" value="send_code">
            <div class="field">
                <input type="email" name="email" placeholder="Email" required autofocus>
                <i class="fas fa-envelope"></i>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Отправить код</button>
        </form>
        <?php elseif ($step === 2): ?>
        <form method="POST" novalidate>
            <input type="hidden" name="action" value="verify_code">
            <div class="field">
                <input type="text" name="code" placeholder="Код из письма" maxlength="6" required autofocus autocomplete="off" style="text-align:center;font-size:18px;letter-spacing:8px;font-family:monospace">
                <i class="fas fa-key"></i>
            </div>
            <div class="field">
                <input type="password" name="new_password" placeholder="Новый пароль" required>
                <i class="fas fa-lock"></i>
            </div>
            <div class="field">
                <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
                <i class="fas fa-lock"></i>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Сменить пароль</button>
        </form>
        <?php elseif ($step === 3): ?>
        <a href="/login" class="btn btn-primary" style="text-decoration:none"><i class="fas fa-sign-in-alt"></i> Войти</a>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="/login"><i class="fas fa-arrow-left"></i> Назад к входу</a>
            <?php if ($step === 2): ?>
                <br><br>
                <form method="POST" style="display:inline" novalidate><input type="hidden" name="action" value="restart"><button type="submit" style="background:none;border:none;color:var(--dim);font-size:12px;cursor:pointer;text-decoration:underline">Запросить новый код</button></form>
            <?php endif; ?>
        </div>
    </div>
</main>
<div class="footer">&copy; <?php echo $SITE_NAME; ?> <?php echo date('Y'); ?></div>
</body>
</html>
