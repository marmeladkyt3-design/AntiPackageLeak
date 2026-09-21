<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkMaintenance();

$current_user = isLoggedIn() ? getUser($pdo, $_SESSION['user_id']) : null;
$site_name = htmlspecialchars($SITE_NAME ?? 'Placeholder');
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($is_ajax) { error_reporting(0); ini_set('display_errors', '0'); }

function ajaxOut($data) { if (ob_get_length()) ob_clean(); header('Content-Type: application/json'); echo json_encode($data); exit; }

$msg = '';
$msg_type = '';

$plans = [];
try {
    $plan_rows = $pdo->query("SELECT * FROM shop_plans WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    foreach ($plan_rows as $pr) {
        $feats = json_decode($pr['features'], true) ?: [];
        $plans[$pr['slug']] = [
            'name' => $pr['name'],
            'price' => (int)$pr['price'],
            'features' => $feats,
            'badge' => $pr['badge'] ?? '',
        ];
    }
} catch (PDOException $e) {}
if (empty($plans)) {
    $plans['forever'] = ['name' => 'Навсегда', 'price' => 200, 'features' => ['Бессрочный доступ', 'Все функции и модули', 'Без оплаты хостинга и БД', 'Поддержка 24/7', 'Регулярные обновления']];
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (`id` int(11) NOT NULL AUTO_INCREMENT,`order_id` varchar(64) NOT NULL,`user_id` int(11) NOT NULL,`plan` varchar(32) NOT NULL,`amount` decimal(10,2) NOT NULL,`currency` varchar(8) NOT NULL DEFAULT 'RUB',`status` varchar(20) NOT NULL DEFAULT 'created',`payment_id` varchar(64) DEFAULT NULL,`promo_code` varchar(64) DEFAULT NULL,`test` tinyint(1) NOT NULL DEFAULT 0,`created_at` datetime DEFAULT CURRENT_TIMESTAMP,`paid_at` datetime DEFAULT NULL,`updated_at` datetime DEFAULT NULL,PRIMARY KEY (`id`),UNIQUE KEY `order_id` (`order_id`),KEY `user_id` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `promo_codes` (`id` int(11) NOT NULL AUTO_INCREMENT,`code` varchar(64) NOT NULL,`discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',`discount_value` int(11) NOT NULL DEFAULT 0,`min_purchase` int(11) NOT NULL DEFAULT 0,`max_uses` int(11) DEFAULT NULL,`used_count` int(11) NOT NULL DEFAULT 0,`expires_at` datetime DEFAULT NULL,`active` tinyint(1) NOT NULL DEFAULT 1,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),UNIQUE KEY `code` (`code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

if (isset($_GET['remove_promo'])) {
    unset($_SESSION['promo_code'], $_SESSION['promo_discount'], $_SESSION['promo_type']);
    $msg = 'Промокод удалён';
    $msg_type = 'success';
    if ($is_ajax) ajaxOut(['ok' => true, 'msg' => $msg]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply_promo') {
    try {
        $code = trim($_POST['promo_code'] ?? '');
        if (!empty($code)) {
            $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND active = 1");
            $stmt->execute([$code]);
            $promo = $stmt->fetch();
            if ($promo) {
                $expired = !empty($promo['expires_at']) && strtotime($promo['expires_at']) < time();
                $maxed = ($promo['max_uses'] ?? 0) > 0 && ($promo['used_count'] ?? 0) >= $promo['max_uses'];
                if ($expired) {
                    $msg = 'Промокод просрочен';
                    $msg_type = 'error';
                } elseif ($maxed) {
                    $msg = 'Промокод больше не работает';
                    $msg_type = 'error';
                } else {
                    $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?")->execute([$promo['id']]);
                    $_SESSION['promo_code'] = $code;
                    $_SESSION['promo_discount'] = $promo['discount_value'];
                    $_SESSION['promo_type'] = $promo['discount_type'];
                    $msg = 'Промокод активирован! Скидка ' . $promo['discount_value'] . ($promo['discount_type'] == 'percent' ? '%' : '₽');
                    $msg_type = 'success';
                }
            } else {
                $msg = 'Неверный промокод';
                $msg_type = 'error';
            }
        }
        if ($is_ajax) ajaxOut([
            'ok' => $msg_type === 'success',
            'msg' => $msg,
            'promo_active' => isset($_SESSION['promo_code']),
            'promo_code' => $_SESSION['promo_code'] ?? '',
            'discount' => $_SESSION['promo_discount'] ?? 0,
            'dtype' => $_SESSION['promo_type'] ?? 'percent',
        ]);
    } catch (PDOException $e) {
        if ($is_ajax) ajaxOut(['ok' => false, 'msg' => 'Ошибка сервера']);
    }
}

if (empty($msg) && isset($_SESSION['pay_flash'])) {
    $msg = $_SESSION['pay_flash'];
    $msg_type = $_SESSION['pay_flash_type'] ?? 'error';
    unset($_SESSION['pay_flash'], $_SESSION['pay_flash_type']);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_SESSION['promo_code'])) {
    unset($_SESSION['promo_code'], $_SESSION['promo_discount'], $_SESSION['promo_type']);
}

$promo_active = isset($_SESSION['promo_code']);
$plan_discount = $promo_active ? (int)($_SESSION['promo_discount'] ?? 0) : 0;
$plan_dtype = $promo_active ? ($_SESSION['promo_type'] ?? 'percent') : 'percent';

function calcPlanPrice($base, $promo_active, $discount, $type) {
    if (!$promo_active) return $base;
    if ($type === 'percent') return max(1, (int)round($base * (100 - (int)$discount) / 100));
    return max(1, $base - (int)$discount);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_payment') {
    if (!rollypay_enabled()) {
        $_SESSION['pay_flash'] = 'Платёжная система не настроена';
        $_SESSION['pay_flash_type'] = 'error';
        header('Location: /shop');
        exit;
    }
    if (!isLoggedIn()) {
        $_SESSION['pay_flash'] = 'Войдите в аккаунт';
        $_SESSION['pay_flash_type'] = 'error';
        header('Location: /login');
        exit;
    }
    $plan_key = $_POST['plan'] ?? '';
    if (!isset($plans[$plan_key])) {
        $_SESSION['pay_flash'] = 'Тариф не найден';
        $_SESSION['pay_flash_type'] = 'error';
        header('Location: /shop');
        exit;
    }

    $p_discount = $promo_active ? (int)($_SESSION['promo_discount'] ?? 0) : 0;
    $p_dtype = $promo_active ? ($_SESSION['promo_type'] ?? 'percent') : 'percent';
    $amount = calcPlanPrice($plans[$plan_key]['price'], $promo_active, $p_discount, $p_dtype);
    $order_prefix = !empty($ORDER_PREFIX) ? $ORDER_PREFIX : 'shop';
    $order_id = $order_prefix . '_' . $_SESSION['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(3));

    try {
        $pdo->prepare("INSERT INTO payments (order_id, user_id, plan, amount, promo_code) VALUES (?, ?, ?, ?, ?)")
            ->execute([$order_id, $_SESSION['user_id'], $plan_key, $amount, $promo_active ? $_SESSION['promo_code'] : null]);
    } catch (PDOException $e) {
        $_SESSION['pay_flash'] = 'Ошибка базы данных';
        $_SESSION['pay_flash_type'] = 'error';
        header('Location: /shop');
        exit;
    }

    $result = rollypay_create_payment(
        $amount,
        $order_id,
        $plans[$plan_key]['name'],
        rollypay_base_url() . '/success.php?o=' . urlencode($order_id),
        rollypay_base_url() . '/fail.php'
    );

    if (!empty($result['error'])) {
        try { $pdo->prepare("UPDATE payments SET status = 'failed_api' WHERE order_id = ?")->execute([$order_id]); } catch (PDOException $e) {}
        $_SESSION['pay_flash'] = $result['error'];
        $_SESSION['pay_flash_type'] = 'error';
        header('Location: /shop');
        exit;
    }

    try {
        $pdo->prepare("UPDATE payments SET payment_id = ?, status = 'pending', test = ? WHERE order_id = ?")
            ->execute([$result['payment_id'] ?? null, !empty($result['test']) ? 1 : 0, $order_id]);
    } catch (PDOException $e) {}

    header('Location: ' . $result['pay_url']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
<meta name="theme-color" content="#08080f">
<title>Магазин — <?php echo $site_name; ?></title>
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
:root{
    --bg:<?php echo $C['bg']; ?>;--bg2:<?php echo $C['bg2']; ?>;--panel:<?php echo $C['panel']; ?>;--panel-h:<?php echo $C['panel_h']; ?>;
    --line:<?php echo $C['line']; ?>;--line2:<?php echo $C['line2']; ?>;--accent:<?php echo $C['accent']; ?>;--accent-rgb:<?php echo $C['accent_rgb']; ?>;
    --grad1:<?php echo $C['grad1']; ?>;--grad2:<?php echo $C['grad2']; ?>;--grad3:<?php echo $C['grad3']; ?>;
    --ink:<?php echo $C['ink']; ?>;--ink2:<?php echo $C['ink2']; ?>;--dim:<?php echo $C['dim']; ?>;--faint:<?php echo $C['faint']; ?>;
    --success:<?php echo $C['success']; ?>;--danger:<?php echo $C['danger']; ?>;
    --grad:linear-gradient(135deg,var(--grad1),var(--grad2) 55%,var(--grad3));--grad-soft:linear-gradient(135deg,rgba(<?php echo $C['accent_rgb']; ?>,0.14),rgba(<?php echo $C['accent_rgb']; ?>,0.10));
    --accent-light:<?php echo $C['accent_light']; ?>;--accent-dark:<?php echo $C['accent_dark']; ?>;--pink:<?php echo $C['pink']; ?>;
    --shadow:0 24px 70px -24px rgba(0,0,0,0.65);--glow:0 0 0 1px rgba(<?php echo $C['accent_rgb']; ?>,0.16),0 22px 60px -22px rgba(<?php echo $C['accent_rgb']; ?>,0.35);
    --r:18px;--r-sm:12px;--r-lg:26px;
}
*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg) url('assets/img/background.jpg') center/cover no-repeat fixed;color:var(--ink2);line-height:1.65;overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:9px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:#232a44;border-radius:9px;border:2px solid var(--bg)}
a{color:inherit;text-decoration:none}button{font-family:inherit;background:none;border:none;cursor:pointer;color:inherit}img{max-width:100%;display:block}
.container{width:min(100% - 44px,1180px);margin:0 auto}
.grad{background:var(--grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}

.bg-fx{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;background-image:url('assets/img/background.jpg');background-size:cover;background-position:center}
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

.hero{min-height:auto;display:flex;flex-direction:column;align-items:center;text-align:center;padding:90px 24px 16px}
.hero h1{font-family:'Sora',sans-serif;font-size:clamp(32px,5vw,56px);font-weight:800;line-height:1.05;letter-spacing:-0.04em;color:#fff;max-width:600px;margin-bottom:12px;opacity:0;transform:translateY(20px);transition:all 0.55s cubic-bezier(0.22,1,0.36,1)}
.hero h1.vis{opacity:1;transform:translateY(0)}
.hero-sub{font-size:15px;color:rgba(255,255,255,0.38);max-width:440px;line-height:1.7;opacity:0;transform:translateY(16px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1) 0.06s}
.hero-sub.vis{opacity:1;transform:translateY(0)}
.hero-letter{display:inline-block;opacity:0;transform:translateY(24px) rotateX(30deg);transition:all 0.45s cubic-bezier(0.22,1,0.36,1)}
.hero-letter.vis{opacity:1;transform:translateY(0) rotateX(0)}

.plans{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:40px;margin-bottom:48px}
.plan{position:relative;border-radius:20px;border:1px solid rgba(255,255,255,0.08);background:rgba(14,16,26,0.75);padding:32px 28px;display:flex;flex-direction:column;opacity:0;transform:translateY(24px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1),border-color 0.3s,box-shadow 0.3s;-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px)}
.plan.vis{opacity:1;transform:translateY(0)}
.plan:nth-child(2){transition-delay:0.1s}
.plan:nth-child(3){transition-delay:0.2s}
.plan.featured{border-color:rgba(var(--accent-rgb),0.45);background:linear-gradient(180deg,rgba(var(--accent-rgb),0.12) 0%,rgba(16,18,30,0.85) 40%);box-shadow:0 20px 50px -10px rgba(0,0,0,0.9),0 0 30px rgba(var(--accent-rgb),0.4)}
.plan:hover{border-color:rgba(var(--accent-rgb),0.35);transform:translateY(-4px);box-shadow:0 20px 40px -10px rgba(0,0,0,0.8)}
.plan-tag{position:absolute;top:-12px;left:50%;transform:translateX(-50%);display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:999px;border:none;background:linear-gradient(135deg,var(--accent),var(--accent-dark));font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#fff}
.plan-tag i{font-size:5px}
.plan-name{font-family:'Sora',sans-serif;font-size:20px;font-weight:700;color:#fff;margin-bottom:6px}
.plan-desc{font-size:13px;color:rgba(255,255,255,0.35);margin-bottom:0}
.plan-price{margin:18px 0;display:flex;align-items:baseline;gap:6px}
.plan-price .currency{font-size:20px;font-weight:700;color:var(--accent-light)}
.plan-price .amount{font-family:'Sora',sans-serif;font-size:42px;font-weight:800;letter-spacing:-0.04em;color:#fff;line-height:1}
.plan-price .period{font-size:13px;color:rgba(255,255,255,0.25)}
.plan-old{font-size:13px;color:rgba(255,255,255,0.25);text-decoration:line-through;margin-bottom:16px}
.plan-feats{list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.06)}
.plan-feats li{display:flex;align-items:center;gap:9px;font-size:13px;color:rgba(255,255,255,0.5)}
.plan-feats li i{color:#6ee7b7;font-size:12px;flex-shrink:0}
.plan-buy{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;padding:14px;border:none;border-radius:999px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#fff;font-size:14px;font-weight:700;font-family:'Sora',sans-serif;cursor:pointer;transition:all 0.25s cubic-bezier(0.22,1,0.36,1);position:relative;overflow:hidden}
.plan.featured .plan-buy,.plan-buy:hover{background:linear-gradient(135deg,var(--accent),var(--accent-dark));box-shadow:0 6px 20px rgba(var(--accent-rgb),0.5);border-color:transparent}
.plan-buy::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.1),transparent);transform:translateX(-100%);transition:transform 0.5s}
.plan-buy:hover::after{transform:translateX(100%)}
.plan-note{text-align:center;font-size:11px;color:rgba(255,255,255,0.2);margin-top:10px}

.alert{display:flex;align-items:center;gap:9px;padding:12px 16px;border-radius:12px;font-size:13px;margin-bottom:16px;opacity:0;transform:translateY(10px);transition:all 0.35s cubic-bezier(0.22,1,0.36,1)}
.alert.vis{opacity:1;transform:translateY(0)}
.alert-success{color:#6ee7b7;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.15);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px)}
.alert-error{color:#fda4af;background:rgba(244,63,94,0.06);border:1px solid rgba(244,63,94,0.15);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px)}

.pay-overlay{position:fixed;inset:0;z-index:1000;display:flex;align-items:center;justify-content:center;background:rgba(5,5,10,0.6);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);opacity:0;pointer-events:none;transition:opacity 0.3s;padding:20px}
.pay-overlay.open{opacity:1;pointer-events:auto}
.pay-modal{position:relative;width:100%;max-width:400px;border-radius:24px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.03);backdrop-filter:blur(40px);-webkit-backdrop-filter:blur(40px);padding:28px;box-shadow:0 40px 100px -20px rgba(0,0,0,0.7);transform:translateY(20px) scale(0.97);transition:transform 0.3s cubic-bezier(0.22,1,0.36,1);overflow:hidden}
.pay-modal::before{content:'';position:absolute;top:-1px;left:20%;right:20%;height:1px;background:linear-gradient(90deg,transparent,rgba(var(--accent-rgb),0.4),transparent)}
.pay-overlay.open .pay-modal{transform:translateY(0) scale(1)}
.pay-close{position:absolute;top:14px;right:14px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:var(--dim);background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.06);transition:all 0.2s}
.pay-close:hover{color:#fff;background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.12)}
.pay-head{display:flex;align-items:center;gap:14px;margin-bottom:20px}
.pay-icon{width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(var(--accent-rgb),0.2),rgba(var(--accent-rgb),0.08));border:1px solid rgba(var(--accent-rgb),0.3);color:var(--accent-light);font-size:17px;transition:transform 0.3s}
.pay-modal:hover .pay-icon{transform:scale(1.05)}
.pay-sub{font-size:11px;color:var(--faint);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:2px}
.pay-name{font-family:'Sora',sans-serif;font-size:18px;font-weight:700;color:var(--ink)}
.pay-total{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:rgba(var(--accent-rgb),0.06);border:1px solid rgba(var(--accent-rgb),0.15);border-radius:14px;margin-bottom:18px;transition:border-color 0.3s}
.pay-total:hover{border-color:rgba(var(--accent-rgb),0.3)}
.pay-total span{font-size:12.5px;color:var(--dim);font-weight:500}
.pay-total strong{font-family:'Sora',sans-serif;font-size:24px;font-weight:800;color:var(--ink);letter-spacing:-0.02em}
.pay-promo-title{display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:var(--ink2);margin-bottom:10px}
.pay-promo-title i{color:var(--accent-light);font-size:11px}
.pay-promo-form{display:flex;gap:8px;margin-bottom:8px}
.pay-promo-form input{flex:1;min-width:0;padding:10px 14px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:var(--ink);font-size:13px;font-family:inherit;outline:none;transition:border-color 0.25s,background 0.25s}
.pay-promo-form input:focus{border-color:rgba(var(--accent-rgb),0.4);background:rgba(255,255,255,0.05)}
.pay-promo-btn{padding:10px 16px;border:none;border-radius:10px;background:rgba(var(--accent-rgb),0.12);border:1px solid rgba(var(--accent-rgb),0.25);color:var(--ink);font-size:12.5px;font-weight:600;font-family:inherit;transition:all 0.25s}
.pay-promo-btn:hover{background:rgba(var(--accent-rgb),0.2);border-color:rgba(var(--accent-rgb),0.4)}
.pay-promo-active{display:flex;align-items:center;gap:7px;padding:10px 12px;border-radius:10px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:#6ee7b7;font-size:12px;margin-top:8px}
.pay-promo-active b{color:#a7f3d0}
.pay-promo-active a{color:var(--accent-light);font-weight:600;margin-left:auto;text-decoration:none;transition:color 0.2s}
.pay-promo-active a:hover{color:#fff}
.pay-check{display:flex;align-items:flex-start;gap:10px;cursor:pointer;margin-bottom:18px}
.pay-check input{display:none}
.pay-check-box{flex-shrink:0;width:20px;height:20px;margin-top:1px;border-radius:6px;border:1.5px solid rgba(255,255,255,0.12);background:rgba(255,255,255,0.02);display:flex;align-items:center;justify-content:center;color:transparent;font-size:10px;transition:all 0.25s}
.pay-check input:checked+.pay-check-box{background:var(--grad);border-color:transparent;color:#fff;box-shadow:0 4px 14px -4px rgba(var(--accent-rgb),0.5)}
.pay-check-text{font-size:12.5px;line-height:1.5;color:var(--ink2)}
.pay-check-text a{color:var(--accent-light);font-weight:600}
.pay-submit{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;border:none;border-radius:14px;font-size:14px;font-weight:700;font-family:inherit;color:#fff;background:var(--grad);box-shadow:0 14px 34px -10px rgba(var(--accent-rgb),0.55);transition:all 0.3s cubic-bezier(0.22,1,0.36,1);position:relative;overflow:hidden}
.pay-submit:hover{transform:translateY(-2px);box-shadow:0 18px 40px -10px rgba(var(--accent-rgb),0.7)}
.pay-submit::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.1),transparent);transform:translateX(-100%);transition:transform 0.5s}
.pay-submit:hover::after{transform:translateX(100%)}
.pay-submit:disabled{opacity:0.35;cursor:not-allowed;transform:none!important;box-shadow:none!important}
.pay-submit:disabled::after{display:none}
.pay-note{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;font-size:11px;color:var(--faint)}
.pay-note i{color:rgba(var(--accent-rgb),0.5);font-size:10px}

.pay-flash{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(12px);z-index:1100;padding:11px 18px;border-radius:11px;font-size:12.5px;font-weight:600;opacity:0;pointer-events:none;transition:all 0.25s;max-width:calc(100vw - 40px);text-align:center;box-shadow:0 14px 36px -10px rgba(0,0,0,0.7)}
.pay-flash.show{opacity:1;transform:translateX(-50%) translateY(0)}
.pay-flash--success{color:#6ee7b7;background:rgba(16,185,129,0.16);border:1px solid rgba(16,185,129,0.4)}
.pay-flash--error{color:#fda4af;background:rgba(244,63,94,0.16);border:1px solid rgba(244,63,94,0.4)}

.cta{margin-bottom:48px}
.cta-box{border-radius:20px;border:1px solid rgba(var(--accent-rgb),0.15);background:linear-gradient(135deg,rgba(var(--accent-rgb),0.05),rgba(var(--accent-rgb),0.02));padding:40px 28px;text-align:center;position:relative;overflow:hidden;backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px)}
.cta-box::before{content:'';position:absolute;width:360px;height:360px;border-radius:50%;background:radial-gradient(circle,rgba(var(--accent-rgb),0.15),transparent 70%);top:-180px;left:50%;transform:translateX(-50%);pointer-events:none}
.cta-box h2{position:relative;font-family:'Sora',sans-serif;font-size:clamp(20px,3vw,30px);font-weight:800;color:#fff;margin-bottom:8px}
.cta-box p{position:relative;font-size:13.5px;color:rgba(255,255,255,0.35);margin-bottom:24px}
.cta-btn{position:relative;display:inline-flex;align-items:center;gap:7px;padding:12px 24px;border-radius:12px;font-size:13px;font-weight:700;background:var(--grad);color:#fff;box-shadow:0 8px 28px -8px rgba(var(--accent-rgb),0.6);transition:all 0.25s;border:none;cursor:pointer;font-family:inherit}
.cta-btn:hover{transform:translateY(-2px);box-shadow:0 12px 36px -8px rgba(var(--accent-rgb),0.7)}

.footer{position:relative;background:transparent;padding:0 20px 32px}
.footer{margin-top:180px;border-top:0}
.footer__inner{width:min(1100px,calc(100vw - 40px));margin:0 auto;padding:28px 28px 20px;display:grid;grid-template-columns:minmax(0,1.3fr) auto auto auto;gap:40px;justify-items:start;text-align:left;border-radius:24px;border:1px solid rgba(255,255,255,.05);background:rgba(255,255,255,.015);-webkit-backdrop-filter:blur(24px);backdrop-filter:blur(24px);box-shadow:inset 0 1px rgba(255,255,255,.04)}
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
.footer__link{font-family:Inter,sans-serif;font-size:.8rem;line-height:1.2;color:rgba(255,255,255,.5);text-decoration:none;transition:color .22s ease}
.footer__link:hover{color:#fff}
.footer__credit{grid-column:1/-1;margin-top:10px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);text-align:center;font-family:Inter,sans-serif;font-size:.78rem;line-height:1.3;color:rgba(255,255,255,.4)}
.footer__credit-link{color:rgba(255,255,255,.6);text-decoration:none;transition:color .22s ease}
.footer__credit-link:hover{color:#fff}
@media(max-width:1080px){.footer__inner{padding:24px 24px 18px}}
@media(max-width:860px){.footer__inner{padding:22px 22px 16px}}
@media(max-width:760px){.footer__inner{padding:20px 20px 14px;grid-template-columns:1fr 1fr;gap:28px}}
@media(max-width:640px){.footer{padding:0 16px 20px}.footer__inner{padding:20px 16px 14px;grid-template-columns:1fr;gap:24px}}

.scroll-top{position:fixed;bottom:24px;right:24px;width:42px;height:42px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px);color:rgba(255,255,255,0.5);font-size:14px;display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:0;transform:translateY(12px);transition:all 0.25s cubic-bezier(0.22,1,0.36,1);z-index:90;pointer-events:none}
.scroll-top.visible{opacity:1;transform:translateY(0);pointer-events:auto}
.scroll-top:hover{border-color:rgba(255,255,255,0.15);background:rgba(255,255,255,0.08);color:#fff}

@media(max-width:900px){.plans{grid-template-columns:repeat(2,1fr)}.hero{padding-top:70px}}
@media(max-width:640px){.plans{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:0.01ms!important;transition-duration:0.01ms!important}.hero h1,.hero-sub,.plan,.alert{opacity:1!important;transform:none!important}.hero-letter{opacity:1!important;transform:none!important}}
</style>
<?php include 'loader_css.php'; ?>
</head>
<body>
<?php include 'loader_html.php'; ?>

<div class="bg-fx">
    <div class="shader-orbs"><div class="orb orb--1"></div><div class="orb orb--2"></div><div class="orb orb--3"></div><div class="orb orb--4"></div></div>
    <div class="halo halo--a"></div><div class="halo halo--b"></div><div class="halo halo--c"></div>
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
            <a href="/shop" class="active">Магазин</a>
            <a href="/rules">Правила</a>
            <a href="/privacy">Соглашение</a>
            <a href="/profile">Профиль</a>
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
        <a href="/shop" class="active">Магазин</a>
        <a href="/rules">Правила</a>
        <a href="/privacy">Соглашение</a>
        <a href="/profile">Профиль</a>
        <?php if (isLoggedIn()): ?><a href="/logout">Выйти</a><?php else: ?><a href="/login">Войти</a><a href="/register">Регистрация</a><?php endif; ?>
    </div>
</header>

<section class="hero">
    <h1>Выбери свой<br><span class="grad">тариф</span></h1>
    <p class="hero-sub">Оплатил и играешь. Без подписок и скрытых платежей.</p>
</section>

<section class="container">
    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msg_type; ?> vis" id="alertMsg">
            <i class="fas fa-<?php echo $msg_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <div class="plans">
        <?php foreach ($plans as $pk => $pv): ?>
        <div class="plan featured">
            <?php if (!empty($pv['badge'])): ?><div class="plan-tag"><i class="fas fa-circle"></i> <?php echo htmlspecialchars($pv['badge']); ?></div><?php endif; ?>
            <div class="plan-name"><?php echo htmlspecialchars($pv['name']); ?></div>
            <div class="plan-price">
                <span class="currency">&#8381;</span>
                <span class="amount"><?php echo calcPlanPrice($pv['price'], $promo_active, $plan_discount, $plan_dtype); ?></span>
                <span class="period">/ <?php echo $pk === 'forever' ? 'навсегда' : $pk; ?></span>
            </div>
            <?php if ($promo_active && $plan_discount > 0): ?><div class="plan-old"><?php echo (int)$pv['price']; ?> &#8381;</div><?php endif; ?>
            <ul class="plan-feats">
                <?php foreach ($pv['features'] as $feat): ?>
                <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feat); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="plan-buy" data-plan="<?php echo htmlspecialchars($pk); ?>"><i class="fas fa-credit-card"></i> Купить</button>
            <div class="plan-note"><i class="fas fa-lock"></i> Безопасная оплата</div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="cta">
        <div class="cta-box">
            <h2>Остались вопросы?</h2>
            <p>Свяжитесь с нами в Telegram</p>
            <a href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank" rel="noopener" class="cta-btn"><i class="fab fa-telegram"></i> Telegram</a>
        </div>
    </div>
</section>

<div class="pay-overlay" id="payOverlay">
    <div class="pay-modal">
        <button type="button" class="pay-close" id="payClose" title="Закрыть"><i class="fas fa-times"></i></button>
        <div class="pay-head">
            <div class="pay-icon"><i class="fas fa-shield-alt"></i></div>
            <div><div class="pay-sub">Оформление</div><div class="pay-name" id="payName">Навсегда</div></div>
        </div>
        <div class="pay-total">
            <span>К оплате</span>
            <strong id="payPrice">200 ₽</strong>
        </div>
        <div id="payPromo" data-disc="<?php echo (int)($plan_discount ?? 0); ?>" data-dtype="<?php echo ($plan_dtype ?? 'percent') === 'percent' ? 'percent' : 'fixed'; ?>">
            <div class="pay-promo-title"><i class="fas fa-ticket-alt"></i> Промокод</div>
            <form method="POST" class="pay-promo-form" id="payPromoForm" novalidate>
                <input autocomplete="off" type="hidden" name="action" value="apply_promo">
                <input autocomplete="off" type="text" name="promo_code" id="payPromoInput" placeholder="Код" value="<?php echo $promo_active ? htmlspecialchars($_SESSION['promo_code']) : ''; ?>">
                <button type="submit" class="pay-promo-btn">OK</button>
            </form>
            <div class="pay-promo-active" id="payPromoActive"<?php echo $promo_active ? '' : ' style="display:none"'; ?>>
                <i class="fas fa-check-circle"></i>
                <span id="payPromoActiveText">Промокод: <b><?php echo htmlspecialchars($_SESSION['promo_code'] ?? ''); ?></b></span>
                <a href="#" id="payPromoRemove">Убрать</a>
            </div>
        </div>
        <label class="pay-check" id="payCheckWrap">
            <input autocomplete="off" type="checkbox" id="payCheck">
            <span class="pay-check-box"><i class="fas fa-check"></i></span>
            <span class="pay-check-text">Принимаю <a href="/privacy" target="_blank">условия</a></span>
        </label>
        <form method="POST" action="/shop" id="payForm" novalidate>
            <input autocomplete="off" type="hidden" name="action" value="create_payment">
            <input autocomplete="off" type="hidden" name="plan" id="payPlanSubmit" value="forever">
            <button type="submit" class="pay-submit" id="payBtn" disabled><i class="fas fa-credit-card"></i> Перейти к оплате</button>
        </form>
        <div class="pay-note"><i class="fas fa-lock"></i> Безопасная оплата</div>
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

<button class="scroll-top" aria-label="Наверх"><i class="fas fa-chevron-up"></i></button>

<script>
(function(){
    var nav=document.getElementById('nav'),ticking=false;
    window.addEventListener('scroll',function(){if(ticking)return;ticking=true;requestAnimationFrame(function(){nav.classList.toggle('scrolled',window.scrollY>10);ticking=false})},{passive:true});
    document.getElementById('burgerBtn').addEventListener('click',function(){nav.classList.toggle('open')});

    var h1=document.querySelector('.hero h1');
    if(h1){
        var txt=h1.innerHTML,buffer='',inTag=false;
        h1.innerHTML='';
        for(var i=0;i<txt.length;i++){
            var c=txt[i];
            if(c==='<'){inTag=true;buffer+=c;continue}
            if(c==='>'){inTag=false;buffer+=c;h1.insertAdjacentHTML('beforeend',buffer);buffer='';continue}
            if(inTag){buffer+=c;continue}
            if(c===' '||c==='\n'){h1.insertAdjacentHTML('beforeend',' ');continue}
            h1.insertAdjacentHTML('beforeend','<span class="hero-letter">'+c+'</span>');
        }
        setTimeout(function(){h1.querySelectorAll('.hero-letter').forEach(function(l,i){setTimeout(function(){l.classList.add('vis')},150+i*40)})},80);
    }

    setTimeout(function(){document.querySelectorAll('.hero h1,.hero-sub').forEach(function(el){if(el)el.classList.add('vis')})},60);

    var obs=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('vis');obs.unobserve(e.target)}})},{threshold:0.05,rootMargin:'0px 0px -20px 0px'});
    document.querySelectorAll('.plan,.cta-box,.alert').forEach(function(el){obs.observe(el)});

    var PLANS=<?php echo json_encode($plans, JSON_UNESCAPED_UNICODE); ?>;
    var overlay=document.getElementById('payOverlay');
    var check=document.getElementById('payCheck');
    var payBtn=document.getElementById('payBtn');
    var currentPlan='forever';

    function fmtPrice(base,disc,dtype){if(!disc||disc<=0)return base;if(dtype==='percent')return Math.max(1,Math.round(base*(100-disc)/100));return Math.max(1,base-disc)}

    function refreshPrice(){
        var el=document.getElementById('payPromo');
        var d=parseInt(el.getAttribute('data-disc')||'0',10);
        var t=el.getAttribute('data-dtype')||'percent';
        document.getElementById('payPrice').textContent=fmtPrice(PLANS[currentPlan].price,d,t)+' ₽';
    }

    function openPay(k){
        if(!PLANS[k])return;
        currentPlan=k;
        document.getElementById('payName').textContent=PLANS[k].name;
        document.getElementById('payPlanSubmit').value=k;
        check.checked=false;
        payBtn.disabled=true;
        refreshPrice();
        overlay.classList.add('open');
        document.body.style.overflow='hidden';
    }

    function closePay(){overlay.classList.remove('open');document.body.style.overflow=''}

    if(location.hash==='#pay'&&PLANS[currentPlan])openPay(currentPlan);

    document.querySelectorAll('.plan-buy').forEach(function(b){
        b.addEventListener('click',function(){openPay(this.getAttribute('data-plan'))});
    });
    document.getElementById('payClose').addEventListener('click',closePay);
    overlay.addEventListener('click',function(e){if(e.target===overlay)closePay()});
    document.addEventListener('keydown',function(e){if(e.key==='Escape'&&overlay.classList.contains('open'))closePay()});
    check.addEventListener('change',function(){payBtn.disabled=!check.checked});

    document.getElementById('payPromoRemove').addEventListener('click',function(e){
        e.preventDefault();
        fetch(location.pathname+'?remove_promo=1',{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json()}).then(function(d){
            if(d.ok){document.getElementById('payPromoActive').style.display='none';document.getElementById('payPromo').setAttribute('data-disc','0');document.getElementById('payPromo').setAttribute('data-dtype','percent');refreshPrice();flashMsg(d.msg||'Промокод убран','success')}
        }).catch(function(){window.location.href='/shop?remove_promo=1'});
    });

    var flashEl=null;
    function flashMsg(t,c){if(!flashEl){flashEl=document.createElement('div');flashEl.id='payFlash';document.body.appendChild(flashEl)}flashEl.textContent=t;flashEl.className='pay-flash pay-flash--'+c;clearTimeout(flashEl._t);flashEl._t=setTimeout(function(){flashEl.classList.remove('show')},3000);requestAnimationFrame(function(){flashEl.classList.add('show')})}

    document.getElementById('payForm').addEventListener('submit',function(e){
        if(!check.checked){e.preventDefault();flashMsg('Примите условия','error');return}
        payBtn.disabled=true;payBtn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Загрузка...';
        setTimeout(function(){payBtn.disabled=false;payBtn.innerHTML='<i class="fas fa-credit-card"></i> Перейти к оплате'},8000);
    });

    document.getElementById('payPromoForm').addEventListener('submit',function(e){
        e.preventDefault();
        var input=document.getElementById('payPromoInput');
        if(!input||!input.value.trim())return;
        var btn=this.querySelector('.pay-promo-btn');
        var o=btn.innerHTML;btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin"></i>';
        var x=new XMLHttpRequest();x.open('POST',location.pathname,true);x.setRequestHeader('X-Requested-With','XMLHttpRequest');
        x.onreadystatechange=function(){
            if(x.readyState!==4)return;btn.disabled=false;btn.innerHTML=o;
            var d=null;try{d=JSON.parse(x.responseText)}catch(err){}
            if(d&&d.ok){
                var t=document.getElementById('payPromoActiveText');
                if(t)t.innerHTML='Промокод: <b>'+(d.promo_code||'')+'</b>, -'+(d.discount||0)+(d.dtype==='fixed'?' руб.':'%');
                document.getElementById('payPromoActive').style.display='flex';
                input.value='';
                document.getElementById('payPromo').setAttribute('data-disc',d.discount||0);
                document.getElementById('payPromo').setAttribute('data-dtype',d.dtype||'percent');
                refreshPrice();
                flashMsg(d.msg||'Активирован!','success');
            }else{flashMsg(d&&d.msg?d.msg:'Ошибка','error')}
        };
        x.send(new FormData(this));
    });

    var btn=document.querySelector('.scroll-top');if(btn){
        window.addEventListener('scroll',function(){if(window.scrollY>400){btn.classList.add('visible')}else{btn.classList.remove('visible')}},{passive:true});
        btn.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'})});
    }

    ['mouseover','mousemove','mousedown','focus'].forEach(function(evt){document.addEventListener(evt,function(e){var a=e.target.closest('a');if(a){window.status='';setTimeout(function(){window.status=''},0)}})});
    setInterval(function(){window.status=''},50);
})();
</script>
<script src="/devtools.js"></script>
<?php include 'loader_js.php'; ?>
<script src="/lang.js"></script>
</body>
</html>