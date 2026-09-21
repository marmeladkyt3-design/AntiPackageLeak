<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
require_once 'includes/auth_functions.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkMaintenance();
if (isLoggedIn()) { redirect('/profile'); }

try { $pdo->exec("CREATE TABLE IF NOT EXISTS `user_2fa` (`user_id` int(11) NOT NULL,`secret` varchar(64) NOT NULL,`enabled` tinyint(1) NOT NULL DEFAULT 0,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (PDOException $e) {}

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
function send_login_2fa_email($to, $code) {
    $subject = 'AntiPackageLeak — Код входа';
    $body = "Ваш код для входа: $code\n\nКод действителен в течение 5 минут.\nЕсли вы не запрашивали вход, проигнорируйте это письмо.";
    return smtp_send($to, $subject, $body);
}



$error = '';
$ip = $_SERVER['REMOTE_ADDR'];
$attemptCheck = checkLoginAttempts($pdo, $ip);
if ($attemptCheck['blocked']) {
    $error = "Слишком много неудачных попыток. Попробуйте снова через " . $attemptCheck['remaining'] . " минут.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$attemptCheck['blocked']) {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $totp_code = trim($_POST['totp_code'] ?? '');

    if (!empty($totp_code) && !empty($_SESSION['pending_2fa_user_id'])) {
        $pending_id = (int)$_SESSION['pending_2fa_user_id'];
        if (!isset($_SESSION['login_2fa_code']) || !isset($_SESSION['login_2fa_time'])) {
            $error = 'Код не был отправлен. Попробуйте войти заново.';
            unset($_SESSION['pending_2fa_user_id']);
        } elseif (time() - $_SESSION['login_2fa_time'] > 300) {
            unset($_SESSION['login_2fa_code'], $_SESSION['login_2fa_time'], $_SESSION['pending_2fa_user_id']);
            $error = 'Код истёк. Попробуйте войти заново.';
        } elseif (strlen($totp_code) !== 6 || !preg_match('/^\d{6}$/', $totp_code)) {
            $error = 'Введите 6-значный код';
        } elseif (!hash_equals($_SESSION['login_2fa_code'], $totp_code)) {
            $error = 'Неверный код';
        } else {
            unset($_SESSION['login_2fa_code'], $_SESSION['login_2fa_time'], $_SESSION['pending_2fa_user_id']);
            $user = getUser($pdo, $pending_id);
            if ($user) {
                resetLoginAttempts($pdo, $ip);
                $_SESSION['user_id'] = $user['id'];
                session_regenerate_id(true);
                $pdo->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?")->execute([$ip, $user['id']]);
                redirect('/profile');
            } else {
                $error = 'Пользователь не найден';
            }
        }
    } else {
        $result = auth_verify($pdo, $login, $password, $ip);

        if (!$result['ok']) {
            if (isset($result['error']) && $result['error'] === 'banned') {
                $error = "BANNED|" . ($result['minutes'] ?? 0) . "|" . ($result['reason'] ?? 'Нарушение правил');
            } elseif (isset($result['error']) && $result['error'] === 'temp_banned') {
                $error = "BANNED|" . ($result['minutes'] ?? 0) . "|" . ($result['reason'] ?? 'Нарушение правил');
            } elseif (isset($result['error']) && $result['error'] === 'no_subscription') {
                $error = 'Нет активной подписки';
            } else {
                $error = $result['error'] ?? 'Ошибка входа';
            }
        } else {
            $user = $result['user'];
            if (auth_check_2fa($pdo, $user['id'])) {
                $_SESSION['pending_2fa_user_id'] = $user['id'];
                $_SESSION['pending_2fa_login'] = $login;
                $email_code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['login_2fa_code'] = $email_code;
                $_SESSION['login_2fa_time'] = time();
                send_login_2fa_email($user['email'], $email_code);
                $pending_2fa = true;
            } else {
                $_SESSION['user_id'] = $user['id'];
                session_regenerate_id(true);
                redirect('/profile');
            }
        }
    }
}

$show_2fa = !empty($_SESSION['pending_2fa_user_id']);

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
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="theme-color" content="#08080f">
<title>Вход — <?php echo $site_name; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{-webkit-user-select:none!important;-moz-user-select:none!important;-ms-user-select:none!important;user-select:none!important}
input,textarea{-webkit-user-select:text!important;-moz-user-select:text!important;-ms-user-select:text!important;user-select:text!important}
:root{--bg:<?php echo $C['bg']; ?>;--panel:<?php echo $C['panel']; ?>;--line:<?php echo $C['line']; ?>;--line2:<?php echo $C['line2']; ?>;--accent:<?php echo $C['accent']; ?>;--accent-rgb:<?php echo $C['accent_rgb']; ?>;--grad1:<?php echo $C['grad1']; ?>;--grad2:<?php echo $C['grad2']; ?>;--grad3:<?php echo $C['grad3']; ?>;--ink:<?php echo $C['ink']; ?>;--ink2:<?php echo $C['ink2']; ?>;--dim:<?php echo $C['dim']; ?>;--faint:<?php echo $C['faint']; ?>;--accent-light:<?php echo $C['accent_light']; ?>;--grad:linear-gradient(135deg,var(--grad1),var(--grad2) 55%,var(--grad3))}
*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg);color:var(--ink2);line-height:1.65;overflow-x:hidden;min-height:100vh;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:9px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:#232a44;border-radius:9px;border:2px solid var(--bg)}
a{color:inherit;text-decoration:none}button{font-family:inherit;background:none;border:none;cursor:pointer;color:inherit}
.grad{background:var(--grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}

.bg-fx{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;background-image:url('assets/img/background.jpg');background-size:cover;background-position:center}
.bg-fx::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,0.5);-webkit-backdrop-filter:blur(16px) saturate(1.2);backdrop-filter:blur(16px) saturate(1.2)}


.header,.header.nav{position:sticky;top:0;z-index:100;padding:1.25rem 1.5rem;background:transparent;border-bottom:none}
.header.nav.scrolled{border-bottom:none}
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
.header-action.active{color:#fff;background:rgba(var(--accent-rgb),0.12)}
.header-action i{font-size:0.8rem}
.burger{display:none;width:42px;height:42px;border:1px solid rgba(255,255,255,0.1);border-radius:12px;align-items:center;justify-content:center;font-size:15px;color:#fff}
.m-menu{display:none;flex-direction:column;gap:4px;padding:12px 18px 20px;border-bottom:1px solid rgba(255,255,255,0.06)}
.m-menu a{padding:12px 14px;border-radius:12px;font-size:14px;font-weight:500;color:rgba(255,255,255,0.5);transition:all 0.18s}
.m-menu a.active,.m-menu a:hover{background:rgba(255,255,255,0.05);color:#fff}
.header.open .m-menu{display:flex}
@media(max-width:900px){.header-nav{display:none}.header-content{justify-content:space-between}.header-brand-link{position:static;left:auto}.header-actions{position:static;right:auto}.burger{display:inline-flex}}
@media(max-width:768px){.header,.header.nav{padding:0.9rem 1rem}.header-content{border-radius:16px}.header-brand-link{max-width:120px}}
@media(max-width:520px){.header-action span{display:none}.header-action{width:2.25rem;padding:0;justify-content:center}.header-brand{font-size:0.9rem}}

.split{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}
.split__left{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;position:relative}
.split__brand{margin-bottom:20px;opacity:0;transform:translateY(20px);transition:all 0.6s cubic-bezier(0.22,1,0.36,1)}
.split__brand.vis{opacity:1;transform:translateY(0)}
.split__logo{width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.split__logo img{width:36px;height:36px;object-fit:contain}
.split__name{font-family:'Sora',sans-serif;font-size:28px;font-weight:800;color:#fff;letter-spacing:-0.03em;margin-bottom:8px}
.split__tagline{font-size:14px;color:var(--dim);max-width:300px;line-height:1.7}
.split__features{display:flex;flex-direction:column;gap:14px;margin-top:32px;max-width:300px;opacity:0;transform:translateY(20px);transition:all 0.6s cubic-bezier(0.22,1,0.36,1) 0.2s}
.split__features.vis{opacity:1;transform:translateY(0)}
.split__feat{display:flex;align-items:center;gap:12px;font-size:13px;color:rgba(255,255,255,0.5)}
.split__feat-icon{width:32px;height:32px;border-radius:10px;background:rgba(var(--accent-rgb),0.08);display:flex;align-items:center;justify-content:center;font-size:13px;color:var(--accent);flex-shrink:0}
.split__divider{position:absolute;right:0;top:15%;bottom:15%;width:1px;background:linear-gradient(180deg,transparent,rgba(255,255,255,0.06),transparent)}

.split__right{display:flex;align-items:center;justify-content:center;padding:40px}
.auth-card{width:100%;max-width:400px;padding:40px 36px;border-radius:24px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.025);-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px);position:relative;opacity:0;transform:translateY(24px);transition:all 0.6s cubic-bezier(0.22,1,0.36,1) 0.15s}
.auth-card.vis{opacity:1;transform:translateY(0)}
.auth-card::before{display:none}
.auth-card__title{font-family:'Sora',sans-serif;font-size:22px;font-weight:700;color:#fff;margin-bottom:4px;text-align:center}
.auth-card__sub{font-size:13px;color:var(--dim);text-align:center;margin-bottom:28px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:600;color:var(--ink2);margin-bottom:6px}
.input-wrap{position:relative}
.input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--faint);pointer-events:none}
.input-wrap input{width:100%;padding:12px 14px 12px 40px;border:1px solid rgba(255,255,255,0.08);border-radius:12px;background:rgba(255,255,255,0.03);color:#fff;font-family:'Inter',sans-serif;font-size:13.5px;outline:none;transition:border-color 0.2s,box-shadow 0.2s}
.input-wrap input::placeholder{color:var(--faint)}
.input-wrap input:focus{border-color:rgba(var(--accent-rgb),0.5);box-shadow:0 0 0 3px rgba(var(--accent-rgb),0.08)}
.btn{width:100%;padding:13px;border:0;border-radius:14px;background:var(--grad);color:#fff;font-family:'Inter',sans-serif;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 12px 32px -8px rgba(var(--accent-rgb),0.5);transition:all 0.25s cubic-bezier(0.22,1,0.36,1)}
.btn:hover{transform:translateY(-2px);box-shadow:0 18px 42px -8px rgba(var(--accent-rgb),0.6)}
.btn:active{transform:translateY(0)}
.alert{padding:14px 16px;border-radius:12px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:10px}
.alert-error{border:1px solid rgba(248,113,113,0.3);background:rgba(248,113,113,0.06);color:#fca5a5}
.alert-ban{padding:20px;border-radius:14px;border:1px solid rgba(248,113,113,0.3);background:rgba(248,113,113,0.06);color:#fca5a5;margin-bottom:18px;text-align:center}
.alert-ban .ban-icon{display:inline-flex;width:40px;height:40px;border-radius:50%;background:rgba(248,113,113,0.15);align-items:center;justify-content:center;font-size:16px;margin-bottom:10px}
.alert-ban strong{display:block;font-size:15px;color:#fca5a5;margin-bottom:6px}
.auth-links{text-align:center;margin-top:20px;font-size:13px;color:var(--dim)}
.auth-links a{color:var(--accent);font-weight:600;transition:color 0.2s}
.auth-links a:hover{color:#fff}
.forgot{text-align:center;margin-top:12px}
.forgot a{color:rgba(255,255,255,0.35);font-size:12px;transition:color 0.2s}
.forgot a:hover{color:#fff}

.footer{position:relative;background:transparent;padding:0 20px 32px;margin-top:60px}
.footer__inner{width:min(1100px,calc(100vw - 40px));margin:0 auto;padding:28px 28px 20px;display:grid;grid-template-columns:minmax(0,1.3fr) auto auto auto;gap:40px;justify-items:start;text-align:left;border-radius:24px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02);-webkit-backdrop-filter:blur(18px);backdrop-filter:blur(18px);box-shadow:inset 0 1px rgba(255,255,255,.04)}
.footer__brand-block{display:flex;flex-direction:column;align-items:flex-start}
.footer__brand{display:inline-flex;align-items:center;gap:8px}
.footer__mark{display:flex;align-items:center;justify-content:center}
.footer__mark img{width:24px;height:24px;object-fit:contain}
.footer__brand-name{font-family:Inter,sans-serif;font-size:1.1rem;font-weight:500;letter-spacing:-.3px;color:#fff}
.footer__copyright{margin-top:12px;font-family:Inter,sans-serif;font-size:.74rem;line-height:1.4;color:rgba(255,255,255,.4)}
.footer__socials{display:flex;gap:10px;margin-top:16px}
.footer__social{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.8);font-size:16px;text-decoration:none;transition:border-color .2s,color .2s,background .2s}
.footer__social:hover{color:#fff;background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15)}
.footer__nav-group{min-width:120px}
.footer__title{font-family:Inter,sans-serif;font-size:.95rem;font-weight:500;letter-spacing:-.02em;color:#fff}
.footer__links{display:flex;flex-direction:column;align-items:flex-start;gap:8px;margin-top:12px}
.footer__link{font-family:Inter,sans-serif;font-size:.84rem;color:rgba(255,255,255,.45);text-decoration:none;transition:color .18s}
.footer__link:hover{color:rgba(255,255,255,.85)}
.footer__credit{grid-column:1/-1;text-align:center;font-family:Inter,sans-serif;font-size:.75rem;color:rgba(255,255,255,.25);margin-top:8px;border-top:1px solid rgba(255,255,255,.05);padding-top:16px}
.footer__credit-link{color:rgba(255,255,255,.4);text-decoration:none;transition:color .18s}
.footer__credit-link:hover{color:rgba(255,255,255,.8)}

@media(max-width:768px){.split{grid-template-columns:1fr;min-height:auto}.split__left{padding:60px 24px 30px}.split__divider{display:none}.split__features{display:none}.split__right{padding:0 20px 40px}.footer__inner{grid-template-columns:1fr;gap:20px;text-align:center;padding:24px 18px 16px}.footer__brand-block,.footer__nav-group,.footer__links{align-items:center}.footer__socials{justify-content:center}}
@media(prefers-reduced-motion:reduce){*{animation-duration:0.01ms!important;transition-duration:0.01ms!important}.split__brand,.split__features,.auth-card{opacity:1!important;transform:none!important}}
</style>
<?php include 'loader_css.php'; ?>
<script>
document.addEventListener('keydown',function(e){if(e.key==='F12'||(e.ctrlKey&&e.shiftKey&&(e.key==='I'||e.key==='i'||e.key==='J'||e.key==='j'||e.key==='C'||e.key==='c'))||(e.ctrlKey&&e.key==='u')){e.preventDefault();e.stopPropagation();return false}},true);
document.addEventListener('contextmenu',function(e){e.preventDefault();return false});
document.addEventListener('copy',function(e){e.preventDefault();return false});
document.addEventListener('cut',function(e){e.preventDefault();return false});
document.addEventListener('selectstart',function(e){if(e.target.tagName!=='INPUT'&&e.target.tagName!=='TEXTAREA'){e.preventDefault();return false}});
</script>
</head>
<body>
<?php include 'loader_html.php'; ?>
<div class="bg-fx"></div>

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
        <a href="/login" class="header-action active"><i class="fas fa-sign-in-alt"></i><span>Войти</span></a>
        <a href="/register" class="header-action"><i class="fas fa-user-plus"></i><span>Регистрация</span></a>
        <button class="burger" id="burgerBtn" aria-label="Меню"><i class="fas fa-bars"></i></button>
    </div>
</div>
<div class="m-menu">
    <a href="/main">Главная</a>
    <a href="/shop">Магазин</a>
    <a href="/rules">Правила</a>
    <a href="/privacy">Соглашение</a>
    <a href="/profile">Профиль</a>
    <a href="/login" class="active">Войти</a>
    <a href="/register">Регистрация</a>
</div>
</header>

<div class="split">
    <div class="split__left">
        <div class="split__brand" id="splitBrand">
            <div class="split__logo"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></div>
            <div class="split__name"><?php echo $site_name; ?></div>
            <p class="split__tagline">Безопасный доступ к вашему кабинету и управление подпиской</p>
        </div>
        <div class="split__features" id="splitFeatures">
            <div class="split__feat"><span class="split__feat-icon"><i class="fas fa-shield-halved"></i></span>Защита аккаунта и данных</div>
            <div class="split__feat"><span class="split__feat-icon"><i class="fas fa-bolt"></i></span>Быстрый доступ к лаунчеру</div>
            <div class="split__feat"><span class="split__feat-icon"><i class="fas fa-headset"></i></span>Поддержка 24/7</div>
        </div>
        <div class="split__divider"></div>
    </div>
    <div class="split__right">
        <div class="auth-card" id="authCard">
            <h1 class="auth-card__title"><?php echo $show_2fa ? 'Код подтверждения' : 'Вход в аккаунт'; ?></h1>
            <p class="auth-card__sub"><?php echo $show_2fa ? 'Код отправлен на почту, привязанную к аккаунту' : 'Введите свои данные для входа'; ?></p>

            <?php if (strpos($error, 'BANNED|') === 0):
                $parts = explode('|', $error, 3);
                $ban_minutes = (int)($parts[1] ?? 0);
                $ban_reason = $parts[2] ?? 'Нарушение правил';
            ?>
                <div class="alert-ban">
                    <span class="ban-icon"><i class="fas fa-lock"></i></span>
                    <strong>Аккаунт заблокирован</strong>
                    <?php if ($ban_minutes > 0): ?>
                        <div style="font-size:12.5px;color:var(--dim);margin-bottom:4px"><i class="fas fa-clock"></i> <?php echo $ban_minutes; ?> минут(ы)</div>
                    <?php endif; ?>
                    <div style="font-size:12.5px;color:var(--dim)"><strong>Причина:</strong> <?php echo htmlspecialchars($ban_reason); ?></div>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($show_2fa): ?>
            <form method="POST" id="loginForm" novalidate>
                <div class="form-group">
                    <label>Код из письма</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="text" name="totp_code" placeholder="6-значный код" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="off" required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn"><i class="fas fa-check"></i> Подтвердить</button>
            </form>
            <?php else: ?>
            <form method="POST" id="loginForm" novalidate>
                <div class="form-group">
                    <label>Логин или Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" name="login" placeholder="Введите логин или email" value="<?php echo htmlspecialchars($login ?? ''); ?>" required autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Введите пароль" required autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="btn"><i class="fas fa-arrow-right"></i> Войти</button>
                <div class="forgot"><a href="/reset_password">Забыли пароль?</a></div>
            </form>
            <?php endif; ?>
            <div class="auth-links">Нет аккаунта? <a href="/register">Зарегистрироваться</a></div>
        </div>
    </div>
</div>

<footer class="footer">
<div class="footer__inner">
    <div class="footer__brand-block">
        <div class="footer__brand"><div class="footer__mark"><img src="/assets/logo.png" alt="" style="width:24px;height:24px;object-fit:contain" onerror="this.style.display='none'"></div><span class="footer__brand-name"><?php echo $site_name; ?></span></div>
        <p class="footer__copyright">&copy; <?php echo $site_name; ?> <?php echo date('Y'); ?>. Все права защищены.</p>
        <div class="footer__socials"><a class="footer__social" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank"><i class="fab fa-telegram"></i></a></div>
    </div>
    <div class="footer__nav-group"><h3 class="footer__title">Навигация</h3><nav class="footer__links"><a class="footer__link" href="/main">Главная</a><a class="footer__link" href="/shop">Магазин</a><a class="footer__link" href="/rules">Правила</a><a class="footer__link" href="/privacy">Соглашение</a></nav></div>
    <div class="footer__nav-group"><h3 class="footer__title">Документы</h3><nav class="footer__links"><a class="footer__link" href="/privacy">Политика конфиденциальности</a><a class="footer__link" href="/rules">Пользовательское соглашение</a></nav></div>
    <div class="footer__nav-group"><h3 class="footer__title">Поддержка</h3><nav class="footer__links"><a class="footer__link" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank">Telegram-канал</a></nav></div>
    <p class="footer__credit">made by <a class="footer__credit-link" href="https://t.me/kodexnull" target="_blank">kodexnull</a> special for <a class="footer__credit-link" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank"><?php echo $site_name; ?></a></p>
</div>
</footer>

<script>
(function(){
    setTimeout(function(){document.getElementById('splitBrand').classList.add('vis');document.getElementById('splitFeatures').classList.add('vis');document.getElementById('authCard').classList.add('vis')},100);
    var nav=document.getElementById('nav'),ticking=false;
    window.addEventListener('scroll',function(){if(ticking)return;ticking=true;requestAnimationFrame(function(){nav.classList.toggle('scrolled',window.scrollY>10);ticking=false})},{passive:true});
    document.getElementById('burgerBtn').addEventListener('click',function(){nav.classList.toggle('open')});
})();
</script>


<script src="/devtools.js"></script>
<?php include 'loader_js.php'; ?>
</body>
</html>