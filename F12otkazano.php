<?php
require_once 'colors_loader.php';
require_once 'site_config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$site_name = htmlspecialchars($SITE_NAME ?? 'AntiPackageLeak');
header('HTTP/1.1 403 Forbidden');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#08080f">
    <meta name="robots" content="noindex, nofollow">
    <title>Доступ запрещён — <?php echo $site_name; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{-webkit-user-select:none!important;-moz-user-select:none!important;-ms-user-select:none!important;user-select:none!important;box-sizing:border-box}
*{margin:0;padding:0}
body{font-family:'Inter',-apple-system,sans-serif;background:#08080f;color:#b4bacd;overflow-x:hidden;min-height:100vh;display:flex;flex-direction:column}

.bg-fx{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden}
.bg-fx::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,0.6);-webkit-backdrop-filter:blur(16px);backdrop-filter:blur(16px)}
.shader-orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.shader-orbs .orb{position:absolute;border-radius:50%;filter:blur(90px);mix-blend-mode:screen;will-change:transform}
.orb--1{width:400px;height:400px;background:radial-gradient(circle,rgba(244,63,94,0.4) 0%,transparent 70%);top:-12%;left:-10%;animation:o1 18s ease-in-out infinite alternate}
.orb--2{width:320px;height:320px;background:radial-gradient(circle,rgba(239,68,68,0.35) 0%,transparent 70%);bottom:-14%;right:-8%;animation:o2 22s ease-in-out infinite alternate}
.orb--3{width:200px;height:200px;background:radial-gradient(circle,rgba(248,113,113,0.2) 0%,transparent 70%);top:40%;left:55%;animation:o3 16s ease-in-out infinite alternate}
@keyframes o1{0%{transform:translate(0,0) scale(1)}33%{transform:translate(60px,40px) scale(1.06)}66%{transform:translate(-35px,80px) scale(0.92)}100%{transform:translate(45px,25px) scale(1.03)}}
@keyframes o2{0%{transform:translate(0,0) scale(1)}33%{transform:translate(-50px,-30px) scale(1.05)}66%{transform:translate(45px,-60px) scale(0.88)}100%{transform:translate(-20px,-40px) scale(1)}}
@keyframes o3{0%{transform:translate(0,0) scale(1)}50%{transform:translate(-70px,30px) scale(1.1)}100%{transform:translate(30px,-50px) scale(0.86)}}
.halo{position:absolute;border-radius:50%;pointer-events:none}
.halo--a{width:600px;height:600px;background:radial-gradient(circle,rgba(244,63,94,0.08) 0%,transparent 60%);top:-220px;right:-140px}
.halo--b{width:500px;height:500px;background:radial-gradient(circle,rgba(239,68,68,0.06) 0%,transparent 60%);bottom:-200px;left:-160px}

.page{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:40px 24px;position:relative;z-index:1}

.icon-wrap{position:relative;margin-bottom:32px;opacity:0;transform:translateY(28px) scale(0.85);transition:all 0.7s cubic-bezier(0.22,1,0.36,1)}
.icon-wrap.vis{opacity:1;transform:translateY(0) scale(1)}
.icon-shield{width:100px;height:100px;border-radius:28px;border:1px solid rgba(244,63,94,0.15);background:rgba(244,63,94,0.05);display:flex;align-items:center;justify-content:center;position:relative}
.icon-shield::before{content:'';position:absolute;inset:-6px;border-radius:32px;border:1px solid rgba(244,63,94,0.08)}
.icon-shield i{font-size:40px;color:#fda4af}
.icon-pulse{position:absolute;top:-4px;right:-4px;width:20px;height:20px;border-radius:50%;background:#ef4444;box-shadow:0 0 16px rgba(239,68,68,0.5);animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.3);opacity:0.6}}

.code{font-family:'Sora',sans-serif;font-size:clamp(72px,14vw,160px);font-weight:800;line-height:1;letter-spacing:-0.04em;opacity:0;transform:translateY(24px);transition:all 0.6s cubic-bezier(0.22,1,0.36,1) 0.08s}
.code.vis{opacity:1;transform:translateY(0);background:linear-gradient(135deg,#f43f5e 0%,#ef4444 40%,#dc2626 100%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}

.title{font-family:'Sora',sans-serif;font-size:clamp(18px,3vw,26px);font-weight:700;color:rgba(255,255,255,0.85);margin:16px 0 12px;opacity:0;transform:translateY(20px);transition:all 0.55s cubic-bezier(0.22,1,0.36,1) 0.14s}
.title.vis{opacity:1;transform:translateY(0)}

.desc{font-size:15px;color:rgba(255,255,255,0.35);max-width:460px;line-height:1.75;margin-bottom:40px;opacity:0;transform:translateY(18px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1) 0.2s}
.desc.vis{opacity:1;transform:translateY(0)}

.btns{display:flex;gap:14px;opacity:0;transform:translateY(16px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1) 0.28s}
.btns.vis{opacity:1;transform:translateY(0)}
.btn{display:inline-flex;align-items:center;gap:9px;padding:14px 30px;border-radius:14px;font-size:14px;font-weight:700;cursor:pointer;text-decoration:none;transition:all 0.25s cubic-bezier(0.22,1,0.36,1);position:relative;overflow:hidden}
.btn-primary{background:linear-gradient(135deg,#f43f5e,#ef4444 55%,#dc2626);color:#fff;box-shadow:0 10px 36px -10px rgba(239,68,68,0.5)}
.btn-primary:hover{transform:translateY(-3px);box-shadow:0 16px 48px -10px rgba(239,68,68,0.65)}
.btn-primary::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.1),transparent);transform:translateX(-100%);transition:transform 0.5s}
.btn-primary:hover::after{transform:translateX(100%)}
.btn-ghost{border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.55);background:rgba(255,255,255,0.025)}
.btn-ghost:hover{border-color:rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);color:#fff}

.info-bar{margin-top:48px;display:flex;align-items:center;gap:10px;padding:12px 20px;border-radius:14px;border:1px solid rgba(255,255,255,0.05);background:rgba(255,255,255,0.02);opacity:0;transform:translateY(14px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1) 0.34s}
.info-bar.vis{opacity:1;transform:translateY(0)}
.info-bar i{font-size:14px;color:rgba(255,255,255,0.25)}
.info-bar span{font-size:12.5px;color:rgba(255,255,255,0.3)}

.footer{position:relative;background:transparent;padding:0 20px 32px;margin-top:auto;border-top:0}
.footer__inner{width:min(1100px,calc(100vw - 40px));margin:0 auto;padding:28px 28px 20px;display:grid;grid-template-columns:minmax(0,1.3fr) auto auto auto;gap:40px;justify-items:start;text-align:left;border-radius:24px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02);-webkit-backdrop-filter:blur(18px);backdrop-filter:blur(18px);box-shadow:inset 0 1px rgba(255,255,255,.04)}
.footer__brand-block{display:flex;flex-direction:column;align-items:flex-start}
.footer__brand{display:inline-flex;align-items:center;gap:8px}
.footer__mark{display:flex;align-items:center;justify-content:center}
.footer__mark img{width:24px;height:24px;object-fit:contain}
.footer__brand-name{font-family:Inter,sans-serif;font-size:1.1rem;font-weight:500;letter-spacing:-.3px;color:#fff}
.footer__copyright{margin-top:12px;font-size:.74rem;color:rgba(255,255,255,.4)}
.footer__socials{display:flex;gap:10px;margin-top:16px}
.footer__social{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.8);font-size:16px;text-decoration:none;transition:all .2s}
.footer__social:hover{color:#fff;background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15)}
.footer__nav-group{min-width:120px}
.footer__title{font-size:.95rem;font-weight:500;color:#fff}
.footer__links{display:flex;flex-direction:column;align-items:flex-start;gap:8px;margin-top:12px}
.footer__link{font-size:.8rem;color:rgba(255,255,255,.5);text-decoration:none;transition:color .22s}
.footer__link:hover{color:#fff}
.footer__credit{grid-column:1/-1;margin-top:10px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);text-align:center;font-size:.78rem;color:rgba(255,255,255,.4)}
.footer__credit-link{color:rgba(255,255,255,.6);text-decoration:none;transition:color .22s}
.footer__credit-link:hover{color:#fff}
@media(max-width:760px){.footer__inner{grid-template-columns:1fr 1fr;gap:28px}}
@media(max-width:640px){.footer__inner{grid-template-columns:1fr;gap:24px}}

@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:0.01ms!important;transition-duration:0.01ms!important}.icon-wrap,.code,.title,.desc,.btns,.info-bar{opacity:1!important;transform:none!important}}
</style>
</head>
<body>

<div class="bg-fx">
    <div class="shader-orbs"><div class="orb orb--1"></div><div class="orb orb--2"></div><div class="orb orb--3"></div></div>
    <div class="halo halo--a"></div><div class="halo halo--b"></div>
</div>

<main class="page">
    <div class="icon-wrap" id="el1">
        <div class="icon-shield"><i class="fas fa-shield-halved"></i></div>
        <div class="icon-pulse"></div>
    </div>
    <div class="code" id="el2">403</div>
    <h1 class="title" id="el3">Доступ запрещён</h1>
    <p class="desc" id="el4">Открытие инструментов разработчика запрещено политикой безопасности проекта. Закройте DevTools и вернитесь на сайт.</p>
    <div class="btns" id="el5">
        <a href="/main" class="btn btn-primary"><i class="fas fa-home"></i> На главную</a>
        <a href="javascript:history.back()" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Назад</a>
    </div>
    <div class="info-bar" id="el6">
        <i class="fas fa-circle-info"></i>
        <span>Если вы считаете, что это ошибка — напишите в <a href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" style="color:rgba(255,255,255,0.6);text-decoration:underline" target="_blank">Telegram</a></span>
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
    var els=['el1','el2','el3','el4','el5','el6'];
    setTimeout(function(){els.forEach(function(id,i){var el=document.getElementById(id);if(el)el.classList.add('vis')})},80);
})();
</script>
<script>
document.addEventListener('keydown',function(e){if(e.key==='F12'||(e.ctrlKey&&e.shiftKey&&['I','i','J','j','C','c'].indexOf(e.key)!==-1)||(e.ctrlKey&&e.key==='u')){e.preventDefault();e.stopPropagation();return false}},true);
document.addEventListener('contextmenu',function(e){e.preventDefault();return false});
</script>
</body>
</html>