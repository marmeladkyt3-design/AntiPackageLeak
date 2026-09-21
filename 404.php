<?php
http_response_code(404);
require_once 'colors_loader.php';
require_once 'site_config.php';
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkMaintenance();

$current_user = isLoggedIn() ? getUser($pdo, $_SESSION['user_id']) : null;
$avatar_url = $current_user ? ($current_user['avatar_url'] ?? null) : null;
$site_name = htmlspecialchars($SITE_NAME ?? 'AntiPackageLeak');
$current_year = date('Y');
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
<meta name="robots" content="noindex, nofollow">
<title>404 — Страница не найдена | <?php echo $site_name; ?></title>
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
}
*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg) url('assets/img/background.jpg') center/cover no-repeat fixed;color:var(--ink2);line-height:1.65;overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:9px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:#232a44;border-radius:9px;border:2px solid var(--bg)}::-webkit-scrollbar-thumb:hover{background:#3b4470}
a{color:inherit;text-decoration:none}button{font-family:inherit;background:none;border:none;cursor:pointer;color:inherit}img{max-width:100%;display:block}
.container{width:min(100% - 44px,1180px);margin:0 auto}
.grad{background:var(--grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}

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
.header.nav.scrolled{border-bottom:none}
.header-content{position:relative;display:flex;align-items:center;justify-content:center;gap:1.4rem;max-width:1060px;margin:0 auto;background:rgba(255,255,255,0.05);-webkit-backdrop-filter:blur(30px);backdrop-filter:blur(30px);border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:0.7rem 1.1rem;min-height:60px;box-shadow:0 18px 44px -20px rgba(0,0,0,0.55)}
.header-brand-link{position:absolute;left:1rem;display:inline-flex;align-items:center;gap:0.55rem;min-height:2.25rem;flex-shrink:0;color:inherit;text-decoration:none;max-width:160px}
.header-logo{display:flex;align-items:center;justify-content:center;width:30px;height:30px;flex-shrink:0;filter:none}
.header-logo img{width:100%;height:100%;object-fit:contain;display:block}
.header-logo i{font-size:15px;color:var(--accent)}
.header-brand{font-family:'Sora',sans-serif;font-size:1.05rem;font-weight:700;letter-spacing:-0.02em;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.brand-shine{position:relative;display:inline-block;color:#fff;-webkit-text-fill-color:#ffffff;text-shadow:0 0 8px rgba(255,255,255,0.18);isolation:isolate}
.brand-shine:before{content:attr(data-text);position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none;background-image:linear-gradient(100deg,transparent 0%,transparent 35%,var(--accent,#68aeff) 50%,transparent 65%,transparent 100%);background-size:220% 100%;background-position:140% 0;background-repeat:no-repeat;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;animation:brandShine 4s ease-in-out infinite;}
@keyframes brandShine{0%{background-position:140% 0}55%,to{background-position:-40% 0}}
.header-nav{display:flex;align-items:center;gap:0.15rem;transform:translateX(-3rem)}
.header-nav a{display:inline-flex;align-items:center;gap:0.34rem;font-family:'Inter',sans-serif;font-size:0.77rem;font-weight:500;color:var(--dim);text-decoration:none;line-height:1;white-space:nowrap;padding:6px 11px;border-radius:11px;transition:color 0.18s ease,background 0.18s ease}
.header-nav a:hover{color:#fff;background:var(--panel)}
.header-nav a.active{color:#fff;background:rgba(var(--accent-rgb),0.12)}
.header-nav a i{font-size:0.8rem}
.header-actions{position:absolute;right:1rem;display:flex;align-items:center;gap:0.45rem}
.header-action{display:inline-flex;align-items:center;justify-content:center;gap:0.38rem;min-height:2.25rem;padding:0 0.8rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;background:rgba(255,255,255,0.04);color:#fff;-webkit-text-fill-color:#ffffff;font-family:'Inter',sans-serif;font-size:0.82rem;font-weight:500;line-height:1;text-decoration:none;white-space:nowrap;cursor:pointer;transition:background 0.18s ease,border-color 0.18s ease}
.header-action:hover{border-color:rgba(255,255,255,0.13);background:rgba(255,255,255,0.09)}
.header-action--profile{padding-left:0.45rem}
.header-avatar{width:22px;height:22px;border-radius:50%;object-fit:cover;flex-shrink:0;pointer-events:none}
.header-action i{font-size:0.8rem}
.burger{display:none;width:42px;height:42px;border:1px solid var(--line2);border-radius:12px;align-items:center;justify-content:center;font-size:15px;color:var(--ink)}
.m-menu{display:none;flex-direction:column;gap:4px;padding:12px 18px 20px;border-bottom:1px solid var(--line)}
.m-menu a{padding:12px 14px;border-radius:12px;font-size:14px;font-weight:500;color:var(--ink2)}
.m-menu a.active,.m-menu a:hover{background:var(--panel);color:var(--ink)}
.nav.open .m-menu{display:flex}
@media(max-width:900px){.header-nav{display:none}.header-content{justify-content:space-between}.header-brand-link{position:static;left:auto}.header-actions{position:static;right:auto}.burger{display:inline-flex}}
@media(max-width:768px){.header,.header.nav{padding:0.9rem 1rem}.header-content{border-radius:16px}.header-brand-link{max-width:120px}}
@media(max-width:520px){.header-action span{display:none}.header-action{width:2.25rem;padding:0;justify-content:center}.header-brand{font-size:0.9rem}}

.error-page{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px 24px 60px;position:relative}
.error-page h1{font-family:'Sora',sans-serif;font-size:clamp(80px,15vw,180px);font-weight:800;line-height:1;color:#fff;opacity:0;transform:translateY(24px);transition:all 0.55s cubic-bezier(0.22,1,0.36,1)}
.error-page h1.vis{opacity:1;transform:translateY(0)}
.error-page h2{font-family:'Sora',sans-serif;font-size:clamp(24px,4vw,36px);font-weight:700;color:rgba(255,255,255,0.8);margin:12px 0 16px;opacity:0;transform:translateY(20px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1) 0.06s}
.error-page h2.vis{opacity:1;transform:translateY(0)}
.error-page p{font-size:17px;color:rgba(255,255,255,0.4);max-width:460px;line-height:1.75;margin-bottom:44px;opacity:0;transform:translateY(18px);transition:all 0.48s cubic-bezier(0.22,1,0.36,1) 0.12s}
.error-page p.vis{opacity:1;transform:translateY(0)}
.error-btns{display:flex;gap:12px;opacity:0;transform:translateY(18px);transition:all 0.48s cubic-bezier(0.22,1,0.36,1) 0.18s}
.error-btns.vis{opacity:1;transform:translateY(0)}
.error-btn{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:14px;font-size:14px;font-weight:700;transition:all 0.25s cubic-bezier(0.22,1,0.36,1);position:relative;overflow:hidden}
.error-btn.primary{background:var(--grad);color:#fff;box-shadow:0 8px 32px -8px rgba(var(--accent-rgb),0.6)}
.error-btn.primary:hover{transform:translateY(-3px);box-shadow:0 14px 44px -8px rgba(var(--accent-rgb),0.75)}
.error-btn.primary::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.12),transparent);transform:translateX(-100%);transition:transform 0.5s}
.error-btn.primary:hover::after{transform:translateX(100%)}
.error-btn.outline{border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.65);background:rgba(255,255,255,0.03)}
.error-btn.outline:hover{border-color:rgba(255,255,255,0.22);background:rgba(255,255,255,0.06);color:#fff}

@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:0.01ms!important;transition-duration:0.01ms!important}.error-page h1,.error-page h2,.error-page p,.error-btns{opacity:1!important;transform:none!important}}

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
.footer__link{font-family:Inter,sans-serif;font-size:.8rem;line-height:1.2;color:rgba(255,255,255,.5);text-decoration:none;transition:color .22s ease}
.footer__link:hover{color:#fff}
.footer__credit{grid-column:1/-1;margin-top:10px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);text-align:center;font-family:Inter,sans-serif;font-size:.78rem;line-height:1.3;color:rgba(255,255,255,.4)}
.footer__credit-link{color:rgba(255,255,255,.6);text-decoration:none;transition:color .22s ease}
.footer__credit-link:hover{color:#fff}
@media(max-width:1080px){.footer__inner{padding:24px 24px 18px}}
@media(max-width:860px){.footer__inner{padding:22px 22px 16px}}
@media(max-width:760px){.footer__inner{padding:20px 20px 14px;grid-template-columns:1fr 1fr;gap:28px}}
@media(max-width:640px){.footer{padding:0 16px 20px}.footer__inner{padding:20px 16px 14px;grid-template-columns:1fr;gap:24px}}
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
            <a href="/shop">Магазин</a>
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
        <a href="/shop">Магазин</a>
        <a href="/rules">Правила</a>
        <a href="/privacy">Соглашение</a>
        <a href="/profile">Профиль</a>
        <?php if (isLoggedIn()): ?><a href="/logout">Выйти</a><?php else: ?><a href="/login">Войти</a><a href="/register">Регистрация</a><?php endif; ?>
    </div>
</header>

<main class="error-page">
    <h1>404</h1>
    <h2>Страница не найдена</h2>
    <p>Похоже, вы попали в несуществующий раздел.<br>Ничего страшного — вернитесь на главную или выберите нужный пункт в меню.</p>
    <div class="error-btns">
        <a href="/main" class="error-btn primary"><i class="fas fa-home"></i> На главную</a>
        <a href="/shop" class="error-btn outline"><i class="fas fa-tag"></i> Магазин</a>
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

<script>
(function(){
    var nav=document.getElementById('nav'),ticking=false;
    window.addEventListener('scroll',function(){if(ticking)return;ticking=true;requestAnimationFrame(function(){nav.classList.toggle('scrolled',window.scrollY>10);ticking=false})},{passive:true});
    document.getElementById('burgerBtn').addEventListener('click',function(){nav.classList.toggle('open')});
})();

(function(){
    var els=document.querySelectorAll('.error-page h1,.error-page h2,.error-page p,.error-btns');
    setTimeout(function(){els.forEach(function(el){if(el)el.classList.add('vis')})},60);
})();
</script>
<script src="/devtools.js"></script>
<?php include 'loader_js.php'; ?>
</body>
</html>