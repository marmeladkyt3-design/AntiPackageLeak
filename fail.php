<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
checkMaintenance();
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
<title>Оплата не прошла — <?php echo $site_name; ?></title>
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
:root{--bg:<?php echo $C['bg']; ?>;--bg2:<?php echo $C['bg2']; ?>;--panel:<?php echo $C['panel']; ?>;--panel-h:<?php echo $C['panel_h']; ?>;--line:<?php echo $C['line']; ?>;--line2:<?php echo $C['line2']; ?>;--accent:<?php echo $C['accent']; ?>;--accent-rgb:<?php echo $C['accent_rgb']; ?>;--grad1:<?php echo $C['grad1']; ?>;--grad2:<?php echo $C['grad2']; ?>;--grad3:<?php echo $C['grad3']; ?>;--ink:<?php echo $C['ink']; ?>;--ink2:<?php echo $C['ink2']; ?>;--dim:<?php echo $C['dim']; ?>;--faint:<?php echo $C['faint']; ?>;--success:<?php echo $C['success']; ?>;--danger:<?php echo $C['danger']; ?>;--grad:linear-gradient(135deg,var(--grad1),var(--grad2) 55%,var(--grad3));--accent-light:<?php echo $C['accent_light']; ?>;--accent-dark:<?php echo $C['accent_dark']; ?>;--pink:<?php echo $C['pink']; ?>}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg);color:var(--ink2);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;-webkit-font-smoothing:antialiased}

.bg-fx{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;background-image: url('assets/img/background.jpg');background-size:cover;background-position:center}
.bg-fx::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,0.5);-webkit-backdrop-filter:blur(16px) saturate(1.2);backdrop-filter:blur(16px) saturate(1.2)}
.shader-orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.shader-orbs .orb{position:absolute;border-radius:50%;filter:blur(90px);mix-blend-mode:screen;will-change:transform}
.orb--1{width:380px;height:380px;background:radial-gradient(circle,rgba(90,99,232,0.45) 0%,transparent 70%);top:-10%;left:-8%;animation:o1 20s ease-in-out infinite alternate}
.orb--2{width:300px;height:300px;background:radial-gradient(circle,rgba(139,92,246,0.38) 0%,transparent 70%);bottom:-12%;right:-6%;animation:o2 24s ease-in-out infinite alternate}
@keyframes o1{0%{transform:translate(0,0) scale(1)}33%{transform:translate(70px,50px) scale(1.08)}66%{transform:translate(-40px,90px) scale(0.94)}100%{transform:translate(50px,30px) scale(1.04)}}
@keyframes o2{0%{transform:translate(0,0) scale(1)}33%{transform:translate(-60px,-35px) scale(1.06)}66%{transform:translate(50px,-70px) scale(0.9)}100%{transform:translate(-25px,-45px) scale(1)}}

.card{width:100%;max-width:420px;background:var(--panel);border:1px solid rgba(255,255,255,0.09);border-radius:22px;padding:38px 32px 30px;text-align:center;box-shadow:0 30px 80px -20px rgba(0,0,0,0.8);-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px);position:relative;overflow:hidden}
.card::after{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,rgba(248,113,113,0.5),transparent)}
.icon{width:74px;height:74px;margin:0 auto 22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;color:#fca5a5;background:rgba(244,63,94,0.12);border:1px solid rgba(244,63,94,0.35);box-shadow:0 0 34px rgba(244,63,94,0.2)}
h1{font-family:'Sora',sans-serif;font-size:22px;font-weight:700;color:#fff;margin-bottom:10px}
p{font-size:14px;line-height:1.7;color:var(--dim);margin-bottom:8px}
.note{font-size:13px;color:var(--dim);margin:14px 0 26px}
.note a{color:var(--accent);font-weight:600;text-decoration:none;transition:color 0.18s}
.note a:hover{color:#fff}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:13px 26px;border-radius:13px;font-size:14px;font-weight:700;font-family:inherit;color:#fff;background:var(--grad);box-shadow:0 14px 34px -12px rgba(var(--accent-rgb),0.6);text-decoration:none;transition:transform 0.25s ease,box-shadow 0.25s ease;cursor:pointer}
.btn:hover{transform:translateY(-2px);box-shadow:0 18px 40px -12px rgba(var(--accent-rgb),0.7)}
.btn-ghost{display:inline-flex;align-items:center;gap:8px;margin-top:12px;padding:11px 20px;border-radius:12px;font-size:13px;font-weight:600;color:var(--ink2);border:1px solid rgba(255,255,255,0.13);background:rgba(255,255,255,0.028);text-decoration:none;transition:all 0.2s ease;cursor:pointer}
.btn-ghost:hover{color:#fff;border-color:rgba(var(--accent-rgb),0.45)}
.btns{display:flex;flex-direction:column;align-items:center;gap:10px}
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
<div class="bg-fx">
    <div class="shader-orbs"><div class="orb orb--1"></div><div class="orb orb--2"></div></div>
</div>
<div class="card">
    <div class="icon"><i class="fas fa-times"></i></div>
    <h1>Оплата не прошла</h1>
    <p>Платёж был отменён или не завершён. Деньги не списаны — вы можете попробовать снова.</p>
    <div class="note">Возникли вопросы? Свяжитесь с нами: <a href="<?php echo $TELEGRAM_LINK ?? 'https://t.me/AntiPackageLeak'; ?>" target="_blank">Telegram</a></div>
    <div class="btns">
        <a href="/shop" class="btn"><i class="fas fa-redo"></i> Попробовать снова</a>
        <a href="/profile" class="btn-ghost"><i class="fas fa-user"></i> Перейти в профиль</a>
    </div>
</div>
<script src="/devtools.js"></script>
<?php include 'loader_js.php'; ?>
</body>
</html>
