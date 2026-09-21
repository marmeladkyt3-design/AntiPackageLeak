<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkMaintenance();
$current_user = isLoggedIn() ? getUser($pdo, $_SESSION['user_id']) : null;
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
<meta name="description" content="<?php echo $site_name; ?> — Правила использования">
<meta name="theme-color" content="#08080f">
<title>Правила — <?php echo $site_name; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{-webkit-user-select:none!important;-moz-user-select:none!important;-ms-user-select:none!important;user-select:none!important}
input,textarea{-webkit-user-select:text!important;-moz-user-select:text!important;-ms-user-select:text!important;user-select:text!important}
:root{--bg:<?php echo $C['bg']; ?>;--panel:<?php echo $C['panel']; ?>;--line:<?php echo $C['line']; ?>;--line2:<?php echo $C['line2']; ?>;--accent:<?php echo $C['accent']; ?>;--accent-rgb:<?php echo $C['accent_rgb']; ?>;--grad1:<?php echo $C['grad1']; ?>;--grad2:<?php echo $C['grad2']; ?>;--grad3:<?php echo $C['grad3']; ?>;--ink:<?php echo $C['ink']; ?>;--ink2:<?php echo $C['ink2']; ?>;--dim:<?php echo $C['dim']; ?>;--accent-light:<?php echo $C['accent_light']; ?>;--grad:linear-gradient(135deg,var(--grad1),var(--grad2) 55%,var(--grad3))}
*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg) url('assets/img/background.jpg') center/cover no-repeat fixed;color:var(--ink2);line-height:1.65;overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:9px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:#232a44;border-radius:9px;border:2px solid var(--bg)}
a{color:inherit;text-decoration:none}button{font-family:inherit;background:none;border:none;cursor:pointer;color:inherit}img{max-width:100%;display:block}
.container{width:min(100% - 44px,960px);margin:0 auto}
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
.particles{position:fixed;inset:0;pointer-events:none;z-index:-1;overflow:hidden}
.particle{position:absolute;border-radius:50%;background:rgba(var(--accent-rgb),0.3);animation:particleFloat linear infinite}
@keyframes particleFloat{0%{transform:translateY(100vh) translateX(0) scale(0);opacity:0}10%{opacity:1;transform:translateY(80vh) translateX(10px) scale(1)}90%{opacity:1}100%{transform:translateY(-10vh) translateX(-20px) scale(0.5);opacity:0}}

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
.hero__badge{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;border-radius:999px;border:1px solid rgba(var(--accent-rgb),0.2);background:rgba(var(--accent-rgb),0.06);font-size:12px;font-weight:600;color:var(--accent-light);margin-bottom:24px;opacity:0;transform:translateY(12px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1)}
.hero__badge.vis{opacity:1;transform:translateY(0)}
.hero__badge i{font-size:10px}
.hero h1{font-family:'Sora',sans-serif;font-size:clamp(32px,5vw,56px);font-weight:800;line-height:1.05;letter-spacing:-0.04em;color:#fff;max-width:640px;margin-bottom:16px}
.hero__sub{font-size:15px;color:var(--dim);max-width:420px;line-height:1.7;opacity:0;transform:translateY(16px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1) 0.5s}
.hero.vis .hero__sub{opacity:1;transform:translateY(0)}
.hero-letter{display:inline-block;opacity:0;transform:translateY(20px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1)}
.hero-letter.vis{opacity:1;transform:translateY(0)}

.rules{padding:10px 0 80px}
.section{padding:32px 0;opacity:0;transform:translateY(24px);transition:all 0.6s cubic-bezier(0.22,1,0.36,1)}
.section.vis{opacity:1;transform:translateY(0)}
.section+.section{border-top:1px solid rgba(255,255,255,0.04)}
.section__head{display:flex;align-items:center;gap:18px;margin-bottom:20px}
.section__icon{width:52px;height:52px;border-radius:16px;border:1px solid rgba(var(--accent-rgb),0.2);background:rgba(var(--accent-rgb),0.06);display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--accent);flex-shrink:0;transition:all 0.4s cubic-bezier(0.22,1,0.36,1)}
.section.vis .section__icon{transform:scale(1.05);border-color:rgba(var(--accent-rgb),0.35);box-shadow:0 0 20px rgba(var(--accent-rgb),0.1)}
.section__meta{flex:1}
.section__num{font-family:'Sora',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--accent);margin-bottom:2px}
.section__title{font-family:'Sora',sans-serif;font-size:22px;font-weight:700;color:#fff;letter-spacing:-0.01em}
.section__body{padding-left:70px}
.section__text{font-size:14px;color:rgba(255,255,255,0.4);line-height:1.75;margin-bottom:14px}
.section__text:last-child{margin-bottom:0}
.section__list{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:12px}
.section__list li{position:relative;display:flex;align-items:flex-start;gap:14px;padding:14px 18px;border-radius:14px;border:1px solid rgba(255,255,255,0.04);background:rgba(255,255,255,0.018);transition:all 0.35s cubic-bezier(0.22,1,0.36,1);opacity:0;transform:translateX(-12px)}
.section.vis .section__list li{opacity:1;transform:translateX(0)}
.section.vis .section__list li:nth-child(1){transition-delay:0.1s}
.section.vis .section__list li:nth-child(2){transition-delay:0.18s}
.section.vis .section__list li:nth-child(3){transition-delay:0.26s}
.section.vis .section__list li:nth-child(4){transition-delay:0.34s}
.section.vis .section__list li:nth-child(5){transition-delay:0.42s}
.section.vis .section__list li:nth-child(6){transition-delay:0.5s}
.section__list li:hover{border-color:rgba(var(--accent-rgb),0.15);background:rgba(255,255,255,0.035)}
.section__list-icon{width:36px;height:36px;border-radius:10px;background:rgba(var(--accent-rgb),0.08);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--accent);flex-shrink:0;margin-top:1px}
.section__list-text{font-size:13.5px;color:rgba(255,255,255,0.4);line-height:1.65}

.highlight{display:flex;align-items:flex-start;gap:14px;padding:20px 24px;border-radius:16px;border:1px solid rgba(16,185,129,0.2);background:rgba(16,185,129,0.04);opacity:0;transform:translateY(16px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1)}
.highlight.vis{opacity:1;transform:translateY(0)}
.highlight i{color:#10b981;font-size:18px;margin-top:2px;flex-shrink:0}
.highlight p{font-size:13.5px;color:var(--ink2);line-height:1.7}

.notice{display:flex;align-items:flex-start;gap:14px;padding:20px 24px;border-radius:16px;border:1px solid rgba(251,191,36,0.2);background:rgba(251,191,36,0.04);opacity:0;transform:translateY(16px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1);margin-top:16px}
.notice.vis{opacity:1;transform:translateY(0)}
.notice i{color:#fbbf24;font-size:18px;margin-top:2px;flex-shrink:0}
.notice p{font-size:13.5px;color:var(--ink2);line-height:1.7}

.socials{display:flex;justify-content:center;gap:12px;margin-top:32px;opacity:0;transform:translateY(14px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1) 0.1s}
.socials.vis{opacity:1;transform:translateY(0)}
.socials a{width:44px;height:44px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);display:flex;align-items:center;justify-content:center;font-size:17px;color:rgba(255,255,255,0.45);transition:all 0.25s cubic-bezier(0.22,1,0.36,1)}
.socials a:hover{transform:translateY(-3px) scale(1.06);border-color:rgba(var(--accent-rgb),0.4);color:#fff;box-shadow:0 8px 24px -8px rgba(var(--accent-rgb),0.3);background:rgba(255,255,255,0.08)}

.scroll-top{position:fixed;bottom:28px;right:28px;width:44px;height:44px;border-radius:14px;border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.05);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.4);font-size:14px;cursor:pointer;opacity:0;transform:translateY(12px);transition:all 0.3s cubic-bezier(0.22,1,0.36,1);z-index:90;pointer-events:none}
.scroll-top.visible{opacity:1;transform:translateY(0);pointer-events:auto}
.scroll-top:hover{color:#fff;background:rgba(255,255,255,0.1);border-color:rgba(var(--accent-rgb),0.3)}

.footer{position:relative;background:transparent;padding:0 20px 32px;margin-top:120px;border-top:0}
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
@media(max-width:760px){.footer__inner{padding:20px 20px 14px;grid-template-columns:1fr 1fr;gap:28px}}
@media(max-width:640px){.footer{padding:0 16px 20px}.footer__inner{padding:20px 16px 14px;grid-template-columns:1fr;gap:24px}.section__body{padding-left:0}.section__head{flex-direction:column;align-items:flex-start;gap:10px}.section__icon{width:42px;height:42px;border-radius:12px;font-size:16px}}

@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:0.01ms!important;transition-duration:0.01ms!important}.hero h1,.hero__badge,.hero__sub,.section,.highlight,.notice,.socials,.section__list li,.hero-letter{opacity:1!important;transform:none!important}.orb{animation:none!important}}
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
<div class="particles" id="particles"></div>

<header class="header nav" id="nav">
    <div class="header-content">
        <a href="/main" class="header-brand-link">
            <span class="header-logo"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></span>
            <span class="header-brand brand-shine" data-text="<?php echo $site_name; ?>"><?php echo $site_name; ?></span>
        </a>
        <nav class="header-nav">
            <a href="/main">Главная</a>
            <a href="/shop">Магазин</a>
            <a href="/rules" class="active">Правила</a>
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
        <a href="/rules" class="active">Правила</a>
        <a href="/privacy">Соглашение</a>
        <a href="/profile">Профиль</a>
        <?php if (isLoggedIn()): ?><a href="/logout">Выйти</a><?php else: ?><a href="/login">Войти</a><a href="/register">Регистрация</a><?php endif; ?>
    </div>
</header>

<section class="hero" id="hero">
    <div class="hero__badge" id="heroBadge"><i class="fas fa-book-open"></i> Правила и условия</div>
    <h1 id="heroHeading">Правила <span class="grad">использования</span></h1>
    <p class="hero__sub">Ознакомьтесь с основными правилами использования нашего сервиса</p>
</section>

<section class="rules"><div class="container">

    <div class="section" data-stagger="0">
        <div class="section__head">
            <div class="section__icon"><i class="fas fa-gavel"></i></div>
            <div class="section__meta">
                <div class="section__num">01</div>
                <h2 class="section__title">Общие положения</h2>
            </div>
        </div>
        <div class="section__body">
            <p class="section__text">Пользователь обязан соблюдать настоящие правила. Запрещено распространение, деобфускация и модификация продукта. Любое нарушение повлечёт немедленную блокировку аккаунта.</p>
            <ul class="section__list">
                <li><span class="section__list-icon"><i class="fas fa-file-contract"></i></span><span class="section__list-text">Использование сервиса означает полное принятие данных правил</span></li>
                <li><span class="section__list-icon"><i class="fas fa-user-check"></i></span><span class="section__list-text">Один аккаунт — одно устройство (привязка по HWID)</span></li>
                <li><span class="section__list-icon"><i class="fas fa-shield-halved"></i></span><span class="section__list-text">Администрация оставляет за собой право изменять правила без уведомления</span></li>
            </ul>
        </div>
    </div>

    <div class="section" data-stagger="1">
        <div class="section__head">
            <div class="section__icon"><i class="fas fa-ban"></i></div>
            <div class="section__meta">
                <div class="section__num">02</div>
                <h2 class="section__title">Запрещённые действия</h2>
            </div>
        </div>
        <div class="section__body">
            <p class="section__text">Следующие действия строго запрещены и влекут за собой permanent ban без возможности обжалования.</p>
            <ul class="section__list">
                <li><span class="section__list-icon"><i class="fas fa-code"></i></span><span class="section__list-text">Декомпиляция и реверс-инжиниринг продукта</span></li>
                <li><span class="section__list-icon"><i class="fas fa-right-to-bracket"></i></span><span class="section__list-text">Передача доступа третьим лицам</span></li>
                <li><span class="section__list-icon"><i class="fas fa-microchip"></i></span><span class="section__list-text">Обфускация HWID для обхода лицензии</span></li>
                <li><span class="section__list-icon"><i class="fas fa-robot"></i></span><span class="section__list-text">Автоматизация действий с использованием ботов</span></li>
                <li><span class="section__list-icon"><i class="fas fa-share-nodes"></i></span><span class="section__list-text">Распространение продукта через сторонние ресурсы</span></li>
            </ul>
        </div>
    </div>

    <div class="section" data-stagger="2">
        <div class="section__head">
            <div class="section__icon"><i class="fas fa-key"></i></div>
            <div class="section__meta">
                <div class="section__num">03</div>
                <h2 class="section__title">Лицензия и подписка</h2>
            </div>
        </div>
        <div class="section__body">
            <p class="section__text">Каждая подписка привязана к одному устройству. Смена HWID возможна только через обращение в поддержку.</p>
            <ul class="section__list">
                <li><span class="section__list-icon"><i class="fas fa-fingerprint"></i></span><span class="section__list-text">HWID привязывается автоматически при первом запуске</span></li>
                <li><span class="section__list-icon"><i class="fas fa-rotate"></i></span><span class="section__list-text">Смена HWID — не чаще 1 раза в 30 дней</span></li>
                <li><span class="section__list-icon"><i class="fas fa-gem"></i></span><span class="section__list-text">Активация ключа возможна только на одном аккаунте</span></li>
                <li><span class="section__list-icon"><i class="fas fa-clock"></i></span><span class="section__list-text">Подписка не приостанавливается и не компенсируется</span></li>
            </ul>
        </div>
    </div>

    <div class="section" data-stagger="3">
        <div class="section__head">
            <div class="section__icon"><i class="fas fa-gavel"></i></div>
            <div class="section__meta">
                <div class="section__num">04</div>
                <h2 class="section__title">Наказания</h2>
            </div>
        </div>
        <div class="section__body">
            <p class="section__text">За нарушение правил предусмотрены следующие меры воздействия.</p>
            <ul class="section__list">
                <li><span class="section__list-icon"><i class="fas fa-globe"></i></span><span class="section__list-text">IP бан — блокировка по IP-адресу</span></li>
                <li><span class="section__list-icon"><i class="fas fa-microchip"></i></span><span class="section__list-text">HWID бан — блокировка по оборудованию</span></li>
                <li><span class="section__list-icon"><i class="fas fa-user-slash"></i></span><span class="section__list-text">Бан аккаунта — полная блокировка учётной записи</span></li>
                <li><span class="section__list-icon"><i class="fas fa-skull"></i></span><span class="section__list-text">Пермабан — без возможности восстановления</span></li>
            </ul>
        </div>
    </div>

    <div class="section" data-stagger="4">
        <div class="section__head">
            <div class="section__icon"><i class="fas fa-headset"></i></div>
            <div class="section__meta">
                <div class="section__num">05</div>
                <h2 class="section__title">Поддержка и споры</h2>
            </div>
        </div>
        <div class="section__body">
            <p class="section__text">По всем вопросам обращайтесь в поддержку. Решение по спорным ситуациям принимается администрацией в течение 24 часов.</p>
            <ul class="section__list">
                <li><span class="section__list-icon"><i class="fab fa-telegram"></i></span><span class="section__list-text">Telegram — основной канал связи с поддержкой</span></li>
                <li><span class="section__list-icon"><i class="fas fa-clock"></i></span><span class="section__list-text">Время ответа — до 24 часов в рабочие дни</span></li>
                <li><span class="section__list-icon"><i class="fas fa-balance-scale"></i></span><span class="section__list-text">Решение администрации является окончательным</span></li>
            </ul>
        </div>
    </div>

    <div class="highlight" id="highlight">
        <i class="fas fa-circle-check"></i>
        <p>Соблюдая правила, вы помогаете нам поддерживать безопасное и комфортное пространство для всех пользователей.</p>
    </div>

    <div class="notice" id="notice">
        <i class="fas fa-triangle-exclamation"></i>
        <p>Незнание правил не освобождает от ответственности. Убедитесь, что вы ознакомились со всеми разделами.</p>
    </div>

    <div class="socials" id="socials">
        <a href="<?php echo htmlspecialchars($DISCORD_LINK ?? 'https://discord.gg/FH3DND8Shj'); ?>" target="_blank"><i class="fab fa-discord"></i></a>
        <a href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? 'https://t.me/AntiPackageLeak'); ?>" target="_blank"><i class="fab fa-telegram"></i></a>
        <a href="<?php echo htmlspecialchars($YOUTUBE_LINK ?? 'https://www.youtube.com/watch?v=rgk3ZHFmHm4'); ?>" target="_blank"><i class="fab fa-youtube"></i></a>
    </div>
</div></section>

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
})();
(function(){
    var rm=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var h1=document.getElementById('heroHeading');
    if(h1&&!rm){
        var txt=h1.textContent;h1.innerHTML='';
        var words=txt.split(/\s+/);
        words.forEach(function(w,i){
            var span=document.createElement('span');span.className='hero-letter';span.textContent=w;h1.appendChild(span);
            if(i<words.length-1)h1.appendChild(document.createTextNode(' '));
        });
        setTimeout(function(){h1.querySelectorAll('.hero-letter').forEach(function(l,i){setTimeout(function(){l.classList.add('vis')},200+i*120)})},150);
    }
    setTimeout(function(){document.getElementById('hero').classList.add('vis');var b=document.getElementById('heroBadge');if(b)b.classList.add('vis')},60);
})();
(function(){
    var rm=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var obs=new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if(!e.isIntersecting)return;
            e.target.classList.add('vis');
            if(!rm&&e.target.hasAttribute('data-stagger'))e.target.style.transitionDelay=(parseInt(e.target.getAttribute('data-stagger'))*100)+'ms';
            obs.unobserve(e.target);
        });
    },{threshold:0.06,rootMargin:'0px 0px -40px 0px'});
    document.querySelectorAll('.section').forEach(function(el){obs.observe(el)});
    var h=document.getElementById('highlight'),n=document.getElementById('notice'),s=document.getElementById('socials');
    if(h)obs.observe(h);if(n)obs.observe(n);if(s)obs.observe(s);
})();
(function(){
    var c=document.getElementById('particles');if(!c)return;
    for(var i=0;i<18;i++){var p=document.createElement('div');p.className='particle';var s=Math.random()*2+1;p.style.width=s+'px';p.style.height=s+'px';p.style.left=Math.random()*100+'%';p.style.animationDuration=(Math.random()*12+8)+'s';p.style.animationDelay=(Math.random()*10)+'s';p.style.opacity=Math.random()*0.4+0.1;c.appendChild(p);}
})();
(function(){
    if(!('ontouchstart' in window)){
        document.querySelectorAll('.section__list li').forEach(function(li){
            li.addEventListener('mousemove',function(e){
                var r=li.getBoundingClientRect();
                var x=((e.clientX-r.left)/r.width)*100;
                var y=((e.clientY-r.top)/r.height)*100;
                li.style.transform='rotateY('+(x-50)/8+'deg) rotateX('+(50-y)/10+'deg) translateZ(4px)';
                li.style.transition='transform 0.15s cubic-bezier(0.22,1,0.36,1)';
            });
            li.addEventListener('mouseleave',function(){li.style.transform='';li.style.transition='transform 0.5s cubic-bezier(0.22,1,0.36,1)'});
        });
    }
})();
(function(){
    var hero=document.querySelector('.hero');if(!hero)return;var ticking=false;
    window.addEventListener('scroll',function(){if(ticking)return;ticking=true;requestAnimationFrame(function(){var sy=window.scrollY;var h=window.innerHeight;if(sy<h){var p=Math.min(sy*0.35,h*0.5);var scale=1-sy/h*0.06;var opacity=1-sy/h*0.55;hero.style.transform='translateY('+p+'px) scale('+scale+')';hero.style.opacity=opacity<0?0:opacity}ticking=false})},{passive:true});
})();
(function(){
    var btn=document.querySelector('.scroll-top');if(!btn)return;
    window.addEventListener('scroll',function(){if(window.scrollY>400){btn.classList.add('visible')}else{btn.classList.remove('visible')}},{passive:true});
    btn.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'})});
})();
</script>
<script src="/devtools.js"></script>
<?php include 'loader_js.php'; ?>
<script src="/lang.js"></script>
</body>
</html>
