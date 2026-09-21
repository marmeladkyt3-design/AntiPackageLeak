<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';

// ============================================================
// ЗАЩИТА ОТ БОТОВ (как на index.php)
// ============================================================

session_start();
checkMaintenance();

$current_year = date('Y');

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
if (!function_exists('getUser')) {
    function getUser($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}

$current_user = null;
$avatar_url = null;
if (isLoggedIn()) {
    $current_user = getUser($pdo, $_SESSION['user_id']);
    $avatar_url = $current_user ? ($current_user['avatar_url'] ?? null) : null;
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
    <title>Политика конфиденциальности — <?php echo $site_name; ?></title>
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

<!-- ===== PRIVACY ===== -->
<main class="page">
    <div class="container">

        <div class="page-head center reveal">
            <div class="icon"><i class="fas fa-shield-alt"></i></div>
            <h1>Политика <span class="grad">конфиденциальности</span></h1>
            <p>Порядок обработки персональных данных Пользователей сервиса <?php echo $site_name; ?> · Обновлено: <?php echo date('d.m.Y'); ?></p>
        </div>

        <div class="privacy-grid">

            <div class="privacy-card reveal">
                <div class="number">1. Общие положения</div>
                <p class="text">1.1. Настоящая Политика конфиденциальности определяет порядок сбора, обработки, хранения и защиты персональных данных Пользователей онлайн-сервиса (далее — «Сервис»).</p>
                <p class="text" style="margin-top:8px;">1.2. Обработка персональных данных Пользователей осуществляется в соответствии с настоящей Политикой и применимым законодательством.</p>
                <p class="text" style="margin-top:8px;">1.3. Используя Сервис, Пользователь подтверждает, что ознакомился с настоящей Политикой.</p>
            </div>

            <div class="privacy-card reveal d-1">
                <div class="number">2. Обрабатываемые данные</div>
                <p class="text">2.1. В зависимости от используемого функционала Оператор может обрабатывать:</p>
                <ul class="list">
                    <li><i class="fas fa-circle icon-check"></i> имя и контактные данные;</li>
                    <li><i class="fas fa-circle icon-check"></i> идентификаторы учётной записи;</li>
                    <li><i class="fas fa-circle icon-check"></i> адрес электронной почты и номер телефона, если они предоставлены;</li>
                    <li><i class="fas fa-circle icon-check"></i> сведения о заказах и подписках;</li>
                    <li><i class="fas fa-circle icon-check"></i> сведения о платежах и их статусе;</li>
                    <li><i class="fas fa-circle icon-check"></i> технические данные устройства и подключения;</li>
                    <li><i class="fas fa-circle icon-check"></i> информацию, предоставленную Пользователем при обращении в поддержку.</li>
                </ul>
                <p class="text" style="margin-top:8px;">2.2. Оператор не запрашивает пароли, платёжные коды и иные конфиденциальные данные, если их предоставление не требуется соответствующим официальным сервисом.</p>
            </div>

            <div class="privacy-card reveal d-2">
                <div class="number">3. Цели обработки</div>
                <p class="text">3.1. Персональные данные обрабатываются для:</p>
                <ul class="list">
                    <li><i class="fas fa-circle icon-check"></i> предоставления товаров и услуг;</li>
                    <li><i class="fas fa-circle icon-check"></i> регистрации и идентификации Пользователя;</li>
                    <li><i class="fas fa-circle icon-check"></i> обработки платежей;</li>
                    <li><i class="fas fa-circle icon-check"></i> управления заказами и подписками;</li>
                    <li><i class="fas fa-circle icon-check"></i> предоставления технической поддержки;</li>
                    <li><i class="fas fa-circle icon-check"></i> обеспечения безопасности Сервиса;</li>
                    <li><i class="fas fa-circle icon-check"></i> предотвращения мошенничества и злоупотреблений;</li>
                    <li><i class="fas fa-circle icon-check"></i> улучшения работы Сервиса;</li>
                    <li><i class="fas fa-circle icon-check"></i> выполнения требований законодательства.</li>
                </ul>
            </div>

            <div class="privacy-card reveal d-1">
                <div class="number">4. Основания обработки</div>
                <p class="text">4.1. Обработка персональных данных осуществляется на основании согласия Пользователя, необходимости исполнения договора, выполнения требований законодательства, а также иных законных оснований, предусмотренных применимым законодательством.</p>
            </div>

            <div class="privacy-card reveal d-2">
                <div class="number">5. Передача данных третьим лицам</div>
                <p class="text">5.1. Оператор не продаёт персональные данные Пользователей третьим лицам.</p>
                <p class="text" style="margin-top:8px;">5.2. Данные могут передаваться платёжным, техническим, информационным и иным поставщикам услуг в объёме, необходимом для функционирования Сервиса.</p>
                <p class="text" style="margin-top:8px;">5.3. Передача данных государственным органам осуществляется исключительно в случаях и порядке, предусмотренных применимым законодательством.</p>
            </div>

            <div class="privacy-card reveal d-1">
                <div class="number">6. Платёжные данные</div>
                <p class="text">6.1. Обработка банковских карт и иных платёжных реквизитов может осуществляться непосредственно сторонним платёжным провайдером.</p>
                <p class="text" style="margin-top:8px;">6.2. Если иное не предусмотрено используемой платёжной инфраструктурой, Оператор не хранит полные реквизиты банковских карт Пользователей.</p>
            </div>

            <div class="privacy-card reveal d-2">
                <div class="number">7. Хранение и защита данных</div>
                <p class="text">7.1. Персональные данные хранятся только в течение периода, необходимого для достижения целей обработки, либо в течение срока, установленного законодательством.</p>
                <p class="text" style="margin-top:8px;">7.2. Оператор принимает разумные технические и организационные меры для защиты данных от утраты, изменения, раскрытия и несанкционированного доступа.</p>
                <p class="text" style="margin-top:8px;">7.3. После достижения целей обработки данные могут быть удалены или обезличены, если их дальнейшее хранение не требуется законодательством.</p>
            </div>

            <div class="privacy-card reveal d-1">
                <div class="number">8. Права Пользователя</div>
                <p class="text">8.1. В предусмотренных законом случаях Пользователь вправе запросить доступ к своим персональным данным, их изменение или удаление, а также воспользоваться иными предусмотренными законодательством правами.</p>
                <p class="text" style="margin-top:8px;">8.2. Для реализации своих прав Пользователь может обратиться к Оператору по указанным в Сервисе контактным данным.</p>
            </div>

            <div class="privacy-card reveal d-2">
                <div class="number">9. Изменение Политики</div>
                <p class="text">9.1. Оператор вправе изменять настоящую Политику в связи с изменением законодательства, функциональности Сервиса или порядка обработки данных.</p>
                <p class="text" style="margin-top:8px;">9.2. Актуальная редакция Политики публикуется в Сервисе.</p>
            </div>

            <div class="privacy-card reveal d-1">
                <div class="number">10. Контактная информация</div>
                <p class="text">10.1. По вопросам использования Сервиса Заказчик может обратиться в службу поддержки по указанным в Сервисе контактным данным.</p>
            </div>

            <div class="contact-card reveal d-1">
                <i class="fas fa-info-circle"></i>
                <div class="title">Остались вопросы?</div>
                <p class="text">Используя <?php echo $site_name; ?>, вы подтверждаете, что ознакомились с настоящей Политикой конфиденциальности. Если у вас есть вопросы — свяжитесь с нами.</p>
                <div class="social-links">
                    <a href="<?php echo $DISCORD_LINK ?? 'https://discord.gg/FH3DND8Shj'; ?>" target="_blank"><i class="fab fa-discord"></i></a>
                    <a href="<?php echo $TELEGRAM_LINK ?? 'https://t.me/AntiPackageLeak'; ?>" target="_blank"><i class="fab fa-telegram"></i></a>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- ===== ФУТЕР ===== -->
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

<!-- ===== SCRIPTS ===== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var nav = document.getElementById('nav');
    var ticking = false;
    window.addEventListener('scroll', function() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function() {
            nav.classList.toggle('scrolled', window.scrollY > 10);
            ticking = false;
        });
    }, { passive: true });

    document.getElementById('burgerBtn').addEventListener('click', function() {
        nav.classList.toggle('open');
    });

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal:not(.in)').forEach(function(el) {
        observer.observe(el);
    });
});
</script>

<script src="/devtools.js"></script>


<script>(function () {
    'use strict';

    var D = {
        'Главная': 'Home',
        'Магазин': 'Shop',
        'Правила': 'Rules',
        'Соглашение': 'Terms',
        'Поддержка': 'Support',
        'Профиль': 'Profile',
        'Личный кабинет': 'Account',
        'Войти': 'Sign in',
        'Вход': 'Sign in',
        'Вход —': 'Sign in —',
        'Регистрация': 'Sign up',
        'Регистрация —': 'Sign up —',
        'Выйти': 'Log out',
        'Меню': 'Menu',
        'В сети': 'Online',
        'готов к запуску': 'ready to launch',
        'В сети · готов к запуску': 'Online · ready to launch',

        'Новая версия уже доступна': 'A new version is already available',
        'Клиент, который': 'A client that',
        'просто работает': 'just works',
        'и делает своё дело': 'and does the job',
        'Стабильный запуск, понятный интерфейс и своевременные обновления —': 'Stable launch, clean interface and timely updates —',
        'без лишних слов и обещаний': 'no empty promises',
        'Всё, что нужно для комфортной игры.': 'Everything you need for a comfortable game.',
        'запусков': 'launches',
        'клиентов': 'clients',
        'клиенты': 'clients',
        'версий': 'versions',
        'Launcher': 'Launcher',
        'Играть': 'Play',
        'Обновление файлов': 'Updating files',
        'Пара кликов — и вы в игре. Клиент стартует быстро даже на не самом мощном железе.': 'A couple of clicks — and you are in the game. The client starts fast even on not-so-powerful hardware.',
        'Быстрый запуск': 'Fast launch',
        'Автообновления': 'Auto updates',

        'Возможности': 'Features',
        'Всё необходимое': 'Everything you need',
        'для': 'for',
        'комфортной игры': 'a comfortable game',
        'Ничего лишнего — только то, что действительно влияет на игровой опыт.': 'Nothing extra — only what really matters for your gaming experience.',
        'Надёжная защита': 'Reliable protection',
        'Аккаунт и HWID под защитой: продуманная система проверок без лишнего дискомфорта.': 'Account and HWID protected: a well-designed verification system without extra hassle.',
        'Своевременные обновления': 'Timely updates',
        'Вышло обновление — клиент готов. Без долгих ожиданий и переносов сроков.': 'Update released — the client is ready. No long waits or delays.',
        'Множество версий': 'Many versions',
        'От классических до самых свежих. Переключение между версиями — в один клик.': 'From classic to the newest. Switch between versions in one click.',
        'Настройка под себя': 'Customize it',
        'Гибкие параметры и пресеты: настройте клиент под свой стиль и сохраните конфиг.': 'Flexible settings and presets: tune the client to your style and save the config.',
        'Поддержка на связи': 'Support is online',
        'Вопросы — в Discord или Telegram. Отвечают живые люди, без ботов и шаблонов.': 'Questions — in Discord or Telegram. Real people answer, no bots or templates.',

        'Отзывы': 'Reviews',
        'Что говорят': 'What our',
        'наши': 'our',
        'Реальные отзывы тех, кто уже играет вместе с нами.': 'Real reviews from those already playing with us.',
        'Куплено': 'Purchased',
        'Отзывов пока нет — станьте первым!': 'No reviews yet — be the first!',

        'Обзор': 'Overview',
        'Посмотрите': 'See for',
        'сами': 'yourself',
        'Короткое видео о клиенте — интерфейс и возможности за пару минут.': 'A short video about the client — interface and features in a couple of minutes.',
        'Обзор клиента': 'Client overview',

        'Готовы попробовать?': 'Ready to try it out?',
        'Создайте аккаунт и начните играть уже сегодня': 'Create an account and start playing today',
        'В магазин': 'Go to shop',

        'Платформа': 'Platform',
        'Разделы': 'Sections',
        'Мы на связи': 'Contact us',
        '— все права защищены': '— All rights reserved',
        'все права защищены': 'All rights reserved',
        'Не связан с Mojang и Microsoft': 'Not affiliated with Mojang or Microsoft',
        'Игровой клиент для Minecraft. Стабильно, понятно и без лишнего.': 'A Minecraft client. Stable, clear and without extra stuff.',
        'Discord': 'Discord',
        'Telegram': 'Telegram',
        'YouTube': 'YouTube',

        'Создать': 'Create',
        'Найти': 'Search',
        'Сбросить': 'Reset',
        'Удалить': 'Delete',
        'Отмена': 'Cancel',
        'Сохранить': 'Save',
        'Отправить': 'Send',
        'Закрыть': 'Close',
        'Назад': 'Back',
        'Заполните все поля': 'Fill in all fields',
        'Неверный CSRF-токен': 'Invalid CSRF token',
        'Ошибка сети:': 'Network error:',

        'Добро пожаловать': 'Welcome',
        'Войдите в свой аккаунт': 'Sign in to your account',
        'Логин или Email': 'Login or Email',
        'Логин': 'Login',
        'Email': 'Email',
        'Пароль': 'Password',
        'Подтверждение': 'Confirmation',
        'Повторите пароль': 'Repeat password',
        'Введите логин или email': 'Enter login or email',
        'Введите пароль': 'Enter password',
        'Введите логин': 'Enter login',
        'Введите email': 'Enter email',
        'Минимум 6 символов': 'Minimum 6 characters',
        'Нет аккаунта?': 'No account?',
        'Уже есть аккаунт?': 'Already have an account?',
        'Создать аккаунт': 'Create account',
        'Зарегистрироваться': 'Sign up',
        'Присоединяйся к': 'Join',
        'Слишком много неудачных попыток. Попробуйте снова через': 'Too many failed attempts. Try again in',
        'минут.': 'minutes.',
        'минут(ы)': 'minute(s)',
        'Подтвердите что вы не робот': 'Confirm you are not a robot',
        'Проверка не пройдена. Попробуйте снова.': 'Verification failed. Try again.',
        'Неверный логин или пароль': 'Invalid login or password',
        'Нарушение правил': 'Rules violation',
        'HWID в черном списке': 'HWID is blacklisted',
        'IP в черном списке': 'IP is blacklisted',
        'Пожалуйста, подтвердите что вы не робот': 'Please confirm you are not a robot',
        'Аккаунт заблокирован': 'Account blocked',
        'Причина:': 'Reason:',
        'По всем вопросам': 'For any questions',
        'обращайтесь в поддержку': 'contact support',
        'Логин должен быть от 3 до 20 символов': 'Login must be 3 to 20 characters',
        'Логин может содержать только латиницу, цифры и _': 'Login may contain only Latin letters, digits and _',
        'Некорректный email': 'Invalid email',
        'Пароль должен быть не менее 6 символов': 'Password must be at least 6 characters',
        'Пароли не совпадают': 'Passwords do not match',
        'Пользователь с таким логином или email уже существует': 'A user with this login or email already exists',

        'Профиль —': 'Profile —',
        'Добро пожаловать,': 'Welcome,',
        'Все данные, подписка и настройки аккаунта': 'All your data, subscription and account settings',
        '— в одном месте': '— in one place',
        'Выбрать аватарку': 'Choose avatar',
        'У вас пока нет сохранённых аватарок': 'You have no saved avatars',
        'Загрузить новую': 'Upload new',
        'Подписка': 'Subscription',
        'Активна': 'Active',
        'Неактивна': 'Inactive',
        'до': 'until',
        'Активация ключа': 'Key activation',
        'Введите лицензионный ключ': 'Enter license key',
        'Активировать': 'Activate',
        'Ключ продлевает подписку сверх текущей': 'The key extends your subscription beyond the current one',
        'Ключ активирован! Подписка продлена на': 'Key activated! Subscription extended for',
        'дней': 'days',
        'дня': 'days',
        'Информация об аккаунте': 'Account info',
        'Привязан': 'Linked',
        'Не привязан': 'Not linked',
        'отсутствует': 'missing',
        'Последний вход': 'Last login',
        'Лаунчер': 'Launcher',
        'ОС': 'OS',
        'Процессор': 'CPU',
        'Память': 'Memory',
        'Версия:': 'Version:',
        'Скачать лаунчер': 'Download launcher',
        'Не скачалось?': 'Did not download?',
        'Лаунчер на технических работах — скачивание временно недоступно': 'Launcher is under maintenance — download temporarily unavailable',
        'Скачивание лаунчера доступно только с активной подпиской': 'Launcher download is available only with an active subscription',
        'Нужна помощь?': 'Need help?',
        'Остались вопросы по подписке, лаунчеру или аккаунту? Напишите нам — поможем.': 'Questions about subscription, launcher or account? Write to us — we will help.',
        'Открыть поддержку': 'Open support',
        'Безопасность': 'Security',
        'Смена пароля': 'Change password',
        'Текущий пароль': 'Current password',
        'Новый пароль (мин. 6)': 'New password (min. 6)',
        'Повторите новый пароль': 'Repeat new password',
        'Сменить пароль': 'Change password',
        'Ваш отзыв': 'Your review',
        'Ваш отзыв проходит модерацию': 'Your review is under moderation',
        'Ваш отзыв опубликован': 'Your review is published',
        'Отзыв отклонён — отправьте новый': 'Review rejected — submit a new one',
        'Поделитесь своим опытом...': 'Share your experience...',
        'Отправить повторно': 'Send again',
        'Оставить отзыв': 'Leave a review',
        'Отзывы проходят модерацию перед публикацией': 'Reviews are moderated before publishing',
        'Отзывы могут оставлять только пользователи с активной подпиской': 'Only users with an active subscription can leave reviews',
        'Общий чат': 'General chat',
        'Онлайн:': 'Online:',
        'Сообщения хранятся 1 час': 'Messages are stored for 1 hour',
        'Пока нет сообщений — напишите первым!': 'No messages yet — write the first!',
        'с лаунчера': 'from launcher',
        'с сайта': 'from website',
        'Напишите сообщение...': 'Type a message...',
        'Чат доступен только для премиум-пользователей': 'Chat is available only for premium users',
        'Чат доступен только для пользователей с активной подпиской': 'Chat is available only for users with an active subscription',
        'Вы забанены в чате до': 'You are banned in chat until',
        'Пожалуйста, подождите 4 секунды перед отправкой следующего сообщения': 'Please wait 4 seconds before sending the next message',
        'Сообщение должно содержать минимум 2 символа': 'The message must contain at least 2 characters',
        'Сообщение не должно превышать 500 символов': 'The message must not exceed 500 characters',
        'Вы забанены в чате на 1 час за спам': 'You are banned in chat for 1 hour for spam',
        'Отзыв должен содержать минимум 5 символов': 'The review must contain at least 5 characters',
        'Ваш отзыв уже отправлен на модерацию. Ожидайте проверки.': 'Your review has been sent for moderation. Wait for review.',
        'Ваш отзыв уже опубликован и одобрен.': 'Your review is already published and approved.',
        'Ваш отзыв отправлен на повторную модерацию!': 'Your review has been sent for re-moderation!',
        'Спасибо за ваш отзыв! Он отправлен на модерацию.': 'Thank you for your review! It has been sent for moderation.',
        'Текущий пароль неверный': 'Current password is incorrect',
        'Новый пароль должен быть не менее 6 символов': 'New password must be at least 6 characters',
        'Пароль успешно изменён': 'Password changed successfully',
        'Недействительный ключ': 'Invalid key',
        'Файл не должен превышать 2MB': 'File must not exceed 2MB',
        'Файл не является изображением': 'File is not an image',
        'Разрешены только JPG, PNG, GIF, WEBP': 'Only JPG, PNG, GIF, WEBP are allowed',
        'Аватарка успешно загружена': 'Avatar uploaded successfully',
        'Ошибка при загрузке файла': 'Error uploading file',
        'Выберите файл для загрузки': 'Choose a file to upload',
        'Аватарка изменена': 'Avatar changed',
        'Пользователь': 'User',
        'Администратор': 'Administrator',
        '+30д': '+30d',
        '+90д': '+90d',
        '+365д': '+365d',
        'Отзыв': 'Review',

        'Магазин —': 'Shop —',
        'Выбери свой тариф': 'Choose your plan',
        'Выбери свой': 'Choose your',
        'тариф': 'plan',
        'Получи доступ ко всем возможностям': 'Get access to all features',
        'Популярный': 'Popular',
        '1 месяц': '1 month',
        'Полный доступ к клиенту': 'Full access to the client',
        'Все функции и модули': 'All features and modules',
        'Поддержка 24/7': '24/7 support',
        'Регулярные обновления': 'Regular updates',
        'Где купить': 'Where to buy',
        'Автоматическая активация после оплаты': 'Automatic activation after payment',
        'Недоступно': 'Unavailable',
        '1 год': '1 year',
        'Приоритетная поддержка': 'Priority support',
        'Ранний доступ к бета-версиям': 'Early access to beta versions',
        'Временно недоступно': 'Temporarily unavailable',
        'Лучший выбор': 'Best choice',
        'Навсегда': 'Forever',
        'Бессрочный доступ': 'Lifetime access',
        'VIP поддержка': 'VIP support',
        'Эксклюзивный контент': 'Exclusive content',
        'Промокод просрочен': 'Promo code expired',
        'Промокод больше не работает': 'Promo code no longer works',
        'Промокод активирован! Скидка': 'Promo code activated! Discount',
        'Неверный промокод': 'Invalid promo code',
        'Промокод удалён': 'Promo code deleted',

        'Правила —': 'Rules —',
        'Правила использования': 'Terms of use',
        'использования': 'of use',
        'Ознакомьтесь с основными правилами использования нашего сервиса': 'Read the basic rules of using our service',
        'Раздел 1': 'Section 1',
        'Раздел 2': 'Section 2',
        'Раздел 3': 'Section 3',
        'Раздел 4': 'Section 4',
        'Раздел 5': 'Section 5',
        'Общие положения': 'General provisions',
        'Запрещенные действия': 'Prohibited actions',
        'Ответственность': 'Liability',
        'Конфиденциальность': 'Confidentiality',
        'Изменение правил': 'Changes to rules',
        'Используя': 'By using',
        ', вы автоматически соглашаетесь с данными правилами. Нарушение может привести к блокировке аккаунта без предупреждения.': ', you automatically agree to these rules. A violation may result in account blocking without notice.',
        'Распространение клиента без разрешения администрации': 'Distributing the client without permission of the administration',
        'Продажа аккаунтов и ключей': 'Selling accounts and keys',
        'Использование читов на приватных серверах без согласия владельца': 'Using cheats on private servers without the owner consent',
        'Оскорбление других пользователей и администрации': 'Offending other users and the administration',
        'Попытка взлома или обхода системы лицензирования': 'Attempting to hack or bypass the licensing system',
        'Создание нескольких аккаунтов для получения преимущества': 'Creating multiple accounts to gain an advantage',
        'Администрация не несет ответственности за последствия использования клиента на сторонних серверах. Вы используете продукт на свой страх и риск. Мы не гарантируем, что клиент будет работать на всех серверах без исключения.': 'The administration is not responsible for the consequences of using the client on third-party servers. You use the product at your own risk. We do not guarantee the client will work on all servers.',
        'Мы не передаем ваши личные данные третьим лицам. Вся информация, которую вы предоставляете при регистрации, используется исключительно для работы сервиса и не разглашается.': 'We do not share your personal data with third parties. All information you provide during registration is used solely for the service and is not disclosed.',
        'Администрация оставляет за собой право изменять данные правила в любое время без предварительного уведомления. Актуальная версия всегда доступна на этой странице.': 'The administration reserves the right to change these rules at any time without prior notice. The current version is always available on this page.',
        'По всем вопросам обращайтесь в нашу поддержку — мы всегда на связи.': 'For any questions contact our support — we are always online.',

        'Соглашение —': 'Terms —',
        'Пользовательское соглашение': 'User agreement',
        'Пользовательское': 'User',
        'Условия использования сервиса': 'Terms of service',
        'Обновлено:': 'Updated:',
        'Сбор данных': 'Data collection',
        'Мы собираем минимально необходимую информацию для работы сервиса: имя пользователя, email, HWID для привязки лицензии к устройству, IP-адрес для обеспечения безопасности и предотвращения мошенничества.': 'We collect the minimum information needed for the service: username, email, HWID to bind the license to your device, and IP address for security and fraud prevention.',
        'Хранение данных': 'Data storage',
        'Все пароли шифруются с использованием современных алгоритмов хеширования. Мы не храним пароли в открытом виде. Ваши данные находятся на защищённых серверах с ограниченным доступом.': 'All passwords are encrypted with modern hashing algorithms. We do not store passwords in plain text. Your data is on protected servers with restricted access.',
        'Пароли хранятся в зашифрованном виде': 'Passwords are stored encrypted',
        'Доступ к данным имеют только авторизованные сотрудники': 'Only authorized staff have access to data',
        'Регулярное резервное копирование': 'Regular backups',
        'Третьи стороны': 'Third parties',
        'Мы не передаём, не продаём и не раскрываем ваши персональные данные третьим лицам. Вся собранная информация используется исключительно для работы сервиса и улучшения качества обслуживания.': 'We do not transfer, sell or disclose your personal data to third parties. All collected information is used solely for the service and to improve quality.',
        'Ваши права': 'Your rights',
        'Вы имеете право запросить удаление вашего аккаунта и всех связанных с ним данных. Для этого необходимо обратиться в службу поддержки. После удаления аккаунта восстановление невозможно.': 'You have the right to request deletion of your account and all related data. To do so, contact support. Account recovery is impossible after deletion.',
        'Право на доступ к своим данным': 'Right to access your data',
        'Право на исправление неточных данных': 'Right to correct inaccurate data',
        'Право на удаление аккаунта': 'Right to delete the account',
        'Право на отзыв согласия': 'Right to withdraw consent',
        'Изменения в политике': 'Policy changes',
        'Мы можем обновлять данное соглашение время от времени. О всех существенных изменениях мы уведомим пользователей через сайт или email. Продолжение использования сервиса после изменений означает ваше согласие с новой версией.': 'We may update this agreement from time to time. We will notify users of significant changes via the site or email. Continued use after changes means you accept the new version.',
        'Остались вопросы?': 'Any questions?',
        ', вы соглашаетесь с условиями данного пользовательского соглашения. Если у вас есть вопросы — свяжитесь с нами.': ', you agree to the terms of this user agreement. If you have questions — contact us.',

        'Поддержка —': 'Support —',
        'Служба поддержки': 'Support service',
        'Служба': 'Support',
        'поддержки': 'service',
        'Мы ответим вам в течение 24 часов': 'We will reply within 24 hours',
        'Мои обращения': 'My tickets',
        'Админ-панель': 'Admin panel',
        'Всего': 'Total',
        'Открытых': 'Open',
        'В работе': 'In progress',
        'Закрытых': 'Closed',
        'Создать обращение': 'Create ticket',
        'У вас пока нет обращений': 'You have no tickets yet',
        'Открыт': 'Open',
        'Закрыт': 'Closed',
        'Удалить этот тикет? Все сообщения будут удалены безвозвратно.': 'Delete this ticket? All messages will be permanently deleted.',
        'Новое обращение': 'New ticket',
        'Тема обращения': 'Ticket subject',
        'Например: Проблема с активацией': 'E.g. Activation issue',
        'Сообщение': 'Message',
        'Опишите вашу проблему подробнее...': 'Describe your problem in detail...',
        'Обращение #': 'Ticket #',
        'Изменить статус': 'Change status',
        'Удалить тикет': 'Delete ticket',
        'Введите ваше сообщение... (Ctrl+Enter для отправки)': 'Type your message... (Ctrl+Enter to send)',
        'Этот тикет закрыт. Нельзя оставлять новые сообщения.': 'This ticket is closed. No new messages allowed.',
        'Назад к списку': 'Back to list',
        'Отправка...': 'Sending...',
        'Ошибка при отправке': 'Error sending',
        'Ваш аккаунт заблокирован за спам тикетами. Осталось': 'Your account is blocked for ticket spam. Remaining',
        'Вы создали слишком много обращений (3 за 5 минут). Ваш аккаунт заблокирован на 10 минут.': 'You have created too many tickets (3 per 5 minutes). Your account is blocked for 10 minutes.',
        'С вашего IP слишком много обращений. Аккаунт заблокирован на 30 минут.': 'Too many tickets from your IP. The account is blocked for 30 minutes.',
        'Спам тикетами (10 минут)': 'Ticket spam (10 minutes)',
        'Спам тикетами с одного IP': 'Ticket spam from one IP',
        'Тикет удалён': 'Ticket deleted',
        'Обращение создано! Ожидайте ответа.': 'Ticket created! Waiting for a reply.',
        'Тикет не найден': 'Ticket not found',
        'Введите сообщение': 'Enter a message',
        'Новый ответ в поддержке': 'New reply in support',
        'Администратор ответил на ваше обращение': 'Administrator replied to your ticket',
        'Ответ отправлен!': 'Reply sent!',
        'Статус изменён': 'Status changed',

        'Доступ запрещён. Только для администраторов.': 'Access denied. Administrators only.',
        'Укажите IP-адрес или подсеть': 'Enter an IP address or subnet',
        'Некорректный IP или подсеть': 'Invalid IP or subnet',
        'Добавлен вручную': 'Added manually',
        'IP добавлен в чёрный список': 'IP added to blacklist',
        'Запись удалена из чёрного списка': 'Entry removed from blacklist',
        'Настройки сохранены': 'Settings saved',
        'Логи визитов очищены': 'Visit logs cleared',
        'Логи угроз очищены': 'Threat logs cleared',
        'Фаервол, трекинг посетителей и защита от атак в реальном времени': 'Firewall, visitor tracking and real-time attack protection',
        'Дашборд': 'Dashboard',
        'Посетители': 'Visitors',
        'Угрозы': 'Threats',
        'Чёрный список': 'Blacklist',
        'визитов сегодня': 'visits today',
        'уникальных IP сегодня': 'unique IPs today',
        'запросов за 24 часа': 'requests in 24h',
        'атак заблокировано всего': 'attacks blocked total',
        'атак сегодня': 'attacks today',
        'IP в чёрном списке': 'IPs in blacklist',
        'Визиты за последние 14 дней': 'Visits in the last 14 days',
        'Топ страниц (24ч)': 'Top pages (24h)',
        'Топ стран (7 дней)': 'Top countries (7 days)',
        'Топ User-Agent (24ч)': 'Top user agents (24h)',
        'Пока нет данных': 'No data yet',
        'Поиск по IP или странице...': 'Search by IP or page...',
        'Очистить все логи визитов?': 'Clear all visit logs?',
        'Очистить': 'Clear',
        'Время': 'Time',
        'Страна': 'Country',
        'Метод': 'Method',
        'Страница': 'Page',
        'Реферер': 'Referrer',
        'Визиты не найдены': 'No visits found',
        'Атак не зафиксировано — всё чисто': 'No attacks detected — all clear',
        'всего попыток': 'total attempts',
        'Очистить все логи угроз?': 'Clear all threat logs?',
        'Тип': 'Type',
        'Угроз не обнаружено': 'No threats detected',
        'Заблокировать IP / подсеть': 'Block IP / subnet',
        'IP-адрес или CIDR-подсеть': 'IP address or CIDR subnet',
        'Например: 192.168.1.5 или 45.132.0.0/24': 'E.g. 192.168.1.5 or 45.132.0.0/24',
        'Поддерживаются одиночные IP и подсети CIDR': 'Single IPs and CIDR subnets are supported',
        'Причина': 'Reason',
        'Например: DDoS, спам, атаки': 'E.g. DDoS, spam, attacks',
        'Длительность': 'Duration',
        '0 = навсегда': '0 = forever',
        'Заблокировать': 'Block',
        'IP / Подсеть': 'IP / Subnet',
        'Кем добавлен': 'Added by',
        'Действует до': 'Valid until',
        'Добавлен': 'Added',
        'Чёрный список пуст': 'Blacklist is empty',
        'истёк': 'expired',
        'навсегда': 'forever',
        'Разблокировать?': 'Unblock?',
        'Настройки фаервола': 'Firewall settings',
        'Защита включена': 'Protection enabled',
        'Выключив, сайт перестанет блокировать атаки и считать визиты': 'If disabled, the site stops blocking attacks and counting visits',
        'Блокировать ботов и сканеры': 'Block bots and scanners',
        'sqlmap, nikto, curl, wget, python-requests и другие': 'sqlmap, nikto, curl, wget, python-requests and others',
        'Лимит запросов на IP': 'Requests limit per IP',
        'за окно (ниже)': 'per window (below)',
        'Глобальный лимит (все IP)': 'Global limit (all IPs)',
        '0 = выключен. Блокирует поток запросов даже с разных IP': '0 = off. Blocks request flow even from different IPs',
        'Вес одного захода': 'Weight of one visit',
        '1 заход = N запросов для лимитов (по умолчанию 2)': '1 visit = N requests for limits (default 2)',
        'Бан IP при атаке с разных IP, минут': 'Ban IP on attack from different IPs, minutes',
        'при превышении глобального лимита виновники уходят в чёрный список': 'when the global limit is exceeded, offenders go to the blacklist',
        'Окно rate limit, секунд': 'Rate limit window, seconds',
        'Порог угроз для автобана': 'Threat threshold for auto-ban',
        'сколько атак за 10 минут, чтобы IP попал в чёрный список': 'how many attacks in 10 minutes to blacklist an IP',
        'Автобан, минут': 'Auto-ban, minutes',
        'Хранение визитов, дней': 'Visit storage, days',
        'Защищено': 'Protected',
        'Защищено AstraDLC — фаервол активен: трекинг, блокировка атак, rate limit': 'Protected by AntiPackageLeak — firewall active: tracking, attack blocking, rate limit',
        'AstraDLC — фаервол активен: трекинг, блокировка атак, rate': 'by AntiPackageLeak — firewall active: tracking, attack blocking, rate',

        'Админ панель —': 'Admin panel —',
        'Админ': 'Admin',
        'панель': 'panel',
        'Управление пользователями, ключами, промокодами и отзывами': 'Manage users, keys, promo codes and reviews',
        'Пользователи': 'Users',
        'Ключи': 'Keys',
        'Промокоды': 'Promo codes',
        'Логи': 'Logs',
        'Список пользователей': 'User list',
        'Поиск по ID или username...': 'Search by ID or username...',
        'Роль': 'Role',
        'Действия': 'Actions',
        'Нет пользователей': 'No users',
        'Изменить роль пользователя?': 'Change user role?',
        'Нет': 'No',
        'Выдать подписку на 30 дней': 'Grant subscription for 30 days',
        'Выдать подписку на 90 дней': 'Grant subscription for 90 days',
        'Выдать подписку на 365 дней': 'Grant subscription for 365 days',
        'Выдать подписку навсегда': 'Grant lifetime subscription',
        'Снять подписку': 'Remove subscription',
        'Снять подписку?': 'Remove subscription?',
        'Сбросить HWID': 'Reset HWID',
        'Сбросить HWID?': 'Reset HWID?',
        'Разбан': 'Unban',
        'Разбанить пользователя?': 'Unban user?',
        'Бан': 'Ban',
        'Изменить пароль?': 'Change password?',
        'Введите новый пароль': 'Enter new password',
        'Премиум': 'Premium',
        'Генерация ключей': 'Key generation',
        'Дней': 'Days',
        'Количество': 'Quantity',
        'Создать ключи': 'Create keys',
        'Список ключей': 'Key list',
        'Ключ': 'Key',
        'Код': 'Code',
        'Статус': 'Status',
        'Кем использован': 'Used by',
        'Нет ключей': 'No keys',
        'Использован': 'Used',
        'Активен': 'Active',
        'Удалить ключ?': 'Delete key?',
        'Создать промокод': 'Create promo code',
        'Код (авто если пусто)': 'Code (auto if empty)',
        'Проценты (%)': 'Percent (%)',
        'Фикс (₽)': 'Fixed (₽)',
        'Скидка': 'Discount',
        'Мин. покупка': 'Min. purchase',
        'Макс. использований': 'Max uses',
        '0 (безлимит)': '0 (unlimited)',
        'Список промокодов': 'Promo code list',
        'Использований': 'Uses',
        'Бессрочно': 'Indefinite',
        'Выкл': 'Off',
        'Истёк': 'Expired',
        'Лимит': 'Limit',
        'Удалить промокод?': 'Delete promo code?',
        'Управление отзывами': 'Review management',
        'На модерации (': 'Pending (',
        'Одобренные (': 'Approved (',
        'Отклоненные (': 'Rejected (',
        'Рейтинг': 'Rating',
        'Дата': 'Date',
        'Модератор': 'Moderator',
        'Нет отзывов': 'No reviews',
        'Удален': 'Deleted',
        'Одобрить этот отзыв?': 'Approve this review?',
        'Отклонить этот отзыв?': 'Reject this review?',
        'Удалить этот отзыв?': 'Delete this review?',
        'Журнал действий': 'Action log',
        'Действие': 'Action',
        'Цель': 'Target',
        'Детали': 'Details',
        'Нет записей': 'No records',
        'Создал ключи': 'Created keys',
        'Выдал подписку': 'Granted subscription',
        'Снял подписку': 'Removed subscription',
        'Забанил': 'Banned',
        'Разбанил': 'Unbanned',
        'Сбросил HWID': 'Reset HWID',
        'Сменил пароль': 'Changed password',
        'Сменил роль': 'Changed role',
        'Удалил ключ': 'Deleted key',
        'Создал промокод': 'Created promo code',
        'Удалил промокод': 'Deleted promo code',
        'Переключил промокод': 'Toggled promo code',
        'Одобрил отзыв': 'Approved review',
        'Отклонил отзыв': 'Rejected review',
        'Удалил отзыв': 'Deleted review',
        'Отредактировал отзыв': 'Edited review',
        'Блокировка': 'Blocking',
        'Пользователь:': 'User:',
        'Причина блокировки': 'Block reason',
        'Бан по HWID': 'HWID ban',
        'Забанить': 'Ban',
        'Редактировать отзыв': 'Edit review',
        'Текст отзыва': 'Review text',
        'Отзыв одобрен': 'Review approved',
        'Отзыв отклонен': 'Review rejected',
        'Отзыв удален': 'Review deleted',
        'Отзыв отредактирован': 'Review edited',
        'Создано': 'Created',
        'ключей на': 'keys for',
        'Пользователю #': 'User #',
        'выдана подписка на': 'granted subscription for',
        'НАВСЕГДА': 'FOREVER',
        'Подписка пользователя #': 'Subscription of user #',
        'снята': 'removed',
        'Пользователь #': 'User #',
        'ЗАБАНЕН. Причина:': 'BANNED. Reason:',
        'Пользователь был выкинут из аккаунта.': 'User was logged out.',
        'HWID также заблокирован.': 'HWID is also blocked.',
        'Пользователь не найден': 'User not found',
        'РАЗБАНЕН': 'UNBANNED',
        'пользователя #': 'of user #',
        'сброшен': 'reset',
        'Пароль пользователя #': 'Password of user #',
        'изменён': 'changed',
        'Роль пользователя #': 'Role of user #',
        'изменена на': 'changed to',
        'Промокод': 'Promo code',
        'уже существует': 'already exists',
        'создан': 'created',
        'Вы не можете заблокировать самого себя!': 'You cannot block yourself!',

        'Управление лаунчером —': 'Launcher management —',
        'Управление': 'Management',
        'лаунчером': 'launcher',
        'Настройка лаунчера, версий, новостей и просмотр статистики': 'Configure the launcher, versions, news and view statistics',
        'Настройки': 'Settings',
        'Версии': 'Versions',
        'Новости': 'News',
        'Статистика': 'Statistics',
        'Общие настройки': 'General settings',
        'Включить режим обслуживания': 'Enable maintenance mode',
        'Сообщение при техработах': 'Maintenance message',
        'Ссылка для скачивания лаунчера': 'Launcher download link',
        'Java — задаётся один раз, скачивается всеми клиентами': 'Java — set once, downloaded by all clients',
        'Хэш': 'Hash',
        'Текущая версия лаунчера': 'Current launcher version',
        'URL сайта (для API)': 'Site URL (for API)',
        'Сохранить настройки': 'Save settings',
        'Социальные сети': 'Social networks',
        'Добавить версию': 'Add version',
        'Номер версии': 'Version number',
        'Имя папки (C:\\SkeetGuard\\...)': 'Folder name (C:\\SkeetGuard\\...)',
        'Имя папки': 'Folder name',
        'PouchLeaked (авто)': 'PouchLeaked (auto)',
        'Ссылка на JSON': 'JSON link',
        'URL fake.jar (фейковый jar для поставки)': 'fake.jar URL (fake jar for delivery)',
        'Crypto Key (обфускатор)': 'Crypto Key (obfuscator)',
        'KEY из обфускатора': 'KEY from obfuscator',
        'Список версий': 'Version list',
        'Версия': 'Version',
        'Папка': 'Folder',
        'java (общий)': 'java (shared)',
        'Удалить версию?': 'Delete version?',
        'Добавить новость': 'Add news',
        'Заголовок': 'Title',
        'Заголовок новости': 'News title',
        'Содержание': 'Content',
        'Текст новости...': 'News text...',
        'Добавить': 'Add',
        'Список новостей': 'News list',
        'Нет новостей': 'No news',
        'Удалить новость?': 'Delete news?',
        'Запуски за 7 дней': 'Launches in 7 days',
        'Последние запуски': 'Recent launches',
        'Редактирование версии': 'Edit version',
        'Активна (лаунчер скачивает файлы этой версии)': 'Active (the launcher downloads this version files)',
        'Версия удалена': 'Version deleted',
        'Новость удалена': 'News deleted',
        'Введите номер версии': 'Enter version number',
        'Такая версия уже существует': 'This version already exists',
        'Версия добавлена (хэши:': 'Version added (hashes:',
        'Версия обновлена (хэши:': 'Version updated (hashes:',
        'Новость добавлена': 'News added',
        'Введите URL': 'Enter URL',
        'Ошибка:': 'Error:'
    };

    /* ---------- НАСТРОЙКИ ---------- */
    var STORAGE_KEY = 'aial_lang';
    var current = 'ru';
    try { current = localStorage.getItem(STORAGE_KEY) || 'ru'; } catch (e) {}
    if (current !== 'ru' && current !== 'en') current = 'ru';

    var keys = Object.keys(D).sort(function (a, b) { return b.length - a.length; });

    function collapse(s) {
        return String(s || '').replace(/\s+/g, ' ').trim();
    }

    function isWordChar(ch) {
        return !!ch && /[a-zA-Zа-яА-ЯёЁ0-9_]/.test(ch);
    }

    /* ---------- ПЕРЕВОД ТЕКСТОВЫХ УЗЛОВ (подстрока с границами слов) ---------- */
    function translateText(text) {
        for (var i = 0; i < keys.length; i++) {
            var k = keys[i];
            var idx = text.indexOf(k);
            if (idx === -1) continue;
            var before = idx > 0 ? text.charAt(idx - 1) : '';
            var after = idx + k.length < text.length ? text.charAt(idx + k.length) : '';
            if (!isWordChar(before) && !isWordChar(after)) {
                text = text.slice(0, idx) + D[k] + text.slice(idx + k.length);
            }
        }
        return text;
    }

    function translateNodes() {
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                var p = node.parentNode;
                if (!p) return NodeFilter.FILTER_REJECT;
                var tag = p.nodeName;
                if (tag === 'SCRIPT' || tag === 'STYLE' || tag === 'IFRAME' || tag === 'NOSCRIPT' || tag === 'TEXTAREA') return NodeFilter.FILTER_REJECT;
                var t = collapse(node.nodeValue);
                if (!t) return NodeFilter.FILTER_REJECT;
                for (var i = 0; i < keys.length; i++) {
                    var k = keys[i];
                    if (t.indexOf(k) !== -1) return NodeFilter.FILTER_ACCEPT;
                }
                return NodeFilter.FILTER_REJECT;
            }
        });
        var n;
        while ((n = walker.nextNode())) {
            if (!n.__ruText) n.__ruText = n.nodeValue;
            var fresh = translateText(n.__ruText);
            if (fresh !== n.nodeValue) n.nodeValue = fresh;
        }
    }

    /* ---------- ПЕРЕВОД АТРИБУТОВ placeholder / title / aria-label ---------- */
    function translateAttrs() {
        var attrs = ['placeholder', 'title', 'aria-label'];
        for (var i = 0; i < attrs.length; i++) {
            var els = document.querySelectorAll('[' + attrs[i] + ']');
            for (var j = 0; j < els.length; j++) {
                var el = els[j];
                if (!el.__ruAttrs) el.__ruAttrs = {};
                var v = collapse(el.getAttribute(attrs[i]));
                if (v && D[v]) {
                    if (!el.__ruAttrs[attrs[i]]) el.__ruAttrs[attrs[i]] = el.getAttribute(attrs[i]);
                    el.setAttribute(attrs[i], D[v]);
                }
            }
        }
    }

    /* ---------- ВОССТАНОВЛЕНИЕ РУССКОГО ТЕКСТА (без перезагрузки) ---------- */
    function restoreRu() {
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                return node.__ruText !== undefined ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            }
        });
        var n;
        while ((n = walker.nextNode())) {
            n.nodeValue = n.__ruText;
        }
        var attrs = ['placeholder', 'title', 'aria-label'];
        for (var i = 0; i < attrs.length; i++) {
            var els = document.querySelectorAll('[' + attrs[i] + ']');
            for (var j = 0; j < els.length; j++) {
                var el = els[j];
                if (el.__ruAttrs && el.__ruAttrs[attrs[i]] !== undefined) {
                    el.setAttribute(attrs[i], el.__ruAttrs[attrs[i]]);
                }
            }
        }
    }

    /* ---------- КНОПКИ RU/EN В ШАПКЕ ---------- */
    function buildSwitcher() {
        var host = document.querySelector('.header-actions');
        if (!host) host = document.querySelector('.header-nav');
        if (!host) return;
        if (document.querySelector('.lang-switch')) return;

        var style = document.createElement('style');
        style.textContent = '.lang-switch{display:inline-flex;align-items:center;gap:2px;padding:3px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);border-radius:999px;margin-right:.15rem;}' +
            '.lang-btn{border:0;background:transparent;color:rgba(255,255,255,.4);font-family:Inter,sans-serif;font-size:.72rem;font-weight:600;line-height:1;padding:5px 8px;border-radius:999px;cursor:pointer;transition:color .18s ease,background .18s ease;}' +
            '.lang-btn:hover{color:#fff;}' +
            '.lang-btn.active{color:#fff;background:rgba(255,255,255,.1);}';
        document.head.appendChild(style);

        var wrap = document.createElement('div');
        wrap.className = 'lang-switch';

        function make(lng, label, title) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'lang-btn' + (current === lng ? ' active' : '');
            b.textContent = label;
            b.title = title;
            b.addEventListener('click', function () { setLang(lng); });
            return b;
        }
        wrap.appendChild(make('ru', 'RU', 'Русский'));
        wrap.appendChild(make('en', 'EN', 'English'));

        var ref = host.querySelector('.burger') || host.querySelector('.user-chip');
        if (ref) host.insertBefore(wrap, ref);
        else host.appendChild(wrap);
    }

    function setLang(lng) {
        if (lng === current) return;
        current = lng;
        try { localStorage.setItem(STORAGE_KEY, lng); } catch (e) {}
        document.documentElement.setAttribute('lang', lng === 'ru' ? 'ru' : 'en');
        var btns = document.querySelectorAll('.lang-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].classList.toggle('active', btns[i].textContent === (lng === 'ru' ? 'RU' : 'EN'));
        }
        if (lng === 'en') {
            translateNodes();
            translateAttrs();
        } else {
            restoreRu();
        }
    }

    function boot() {
        document.documentElement.setAttribute('lang', current === 'ru' ? 'ru' : 'en');
        buildSwitcher();
        if (current === 'en') {
            translateNodes();
            translateAttrs();
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();

    window.__lang = current;
})();</script>
<?php include 'loader_js.php'; ?>
</body>
</html>
