<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
checkMaintenance();

try {
  $stmt = $pdo->query("SELECT (SELECT COUNT(*) FROM users) AS u, (SELECT COUNT(*) FROM launcher_stats) AS s, (SELECT COUNT(*) FROM launcher_versions) AS v");
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $clients_count = (int)$row['u']; $launches_count = (int)$row['s']; $updates_count = (int)$row['v'];
} catch (PDOException $e) { $clients_count = 0; $launches_count = 0; $updates_count = 0; }

$current_year = date('Y');
$current_user = isLoggedIn() ? getUser($pdo, $_SESSION['user_id']) : null;
$avatar_url = $current_user ? ($current_user['avatar_url'] ?? null) : null;
$site_name = htmlspecialchars($SITE_NAME ?? 'Placeholder');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="description" content="<?php echo $site_name; ?> — Твой игровой клиент">
<meta name="theme-color" content="#08080f">
<title><?php echo $site_name; ?> — Игровой клиент для Minecraft</title>
<link rel="icon" type="image/png" href="/assets/logo.png">
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://fonts.gstatic.com">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet"></noscript>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{-webkit-user-select:none!important;user-select:none!important}
input,textarea{-webkit-user-select:text!important;user-select:text!important}
:root{
  --bg:<?php echo $C['bg']; ?>;--bg2:<?php echo $C['bg2']; ?>;
  --panel:<?php echo $C['panel']; ?>;--panel-h:<?php echo $C['panel_h']; ?>;
  --line:<?php echo $C['line']; ?>;--line2:<?php echo $C['line2']; ?>;
  --accent:<?php echo $C['accent']; ?>;--accent-rgb:<?php echo $C['accent_rgb']; ?>;
  --accent-light:<?php echo $C['accent_light']; ?>;--accent-dark:<?php echo $C['accent_dark']; ?>;
  --ink:<?php echo $C['ink']; ?>;--ink2:<?php echo $C['ink2']; ?>;--dim:<?php echo $C['dim']; ?>;--faint:<?php echo $C['faint']; ?>;
  --success:<?php echo $C['success']; ?>;--danger:<?php echo $C['danger']; ?>;
  --grad:linear-gradient(135deg,<?php echo $C['grad1']; ?>,<?php echo $C['grad2']; ?> 55%,<?php echo $C['grad3']; ?>);
}
*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg);color:var(--ink2);line-height:1.65;overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:9px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:#232a44;border-radius:9px;border:2px solid var(--bg)}
a{color:inherit;text-decoration:none}button{font-family:inherit;background:none;border:none;cursor:pointer;color:inherit}
.grad{background:var(--grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
.wrap{width:min(100% - 44px,1180px);margin:0 auto}

/* ═══════════════════ BACKGROUND ═══════════════════ */
.world{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;background-image:url('assets/img/background.jpg');background-size:cover;background-position:center}
.world::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,0.5);-webkit-backdrop-filter:blur(16px) saturate(1.2);backdrop-filter:blur(16px) saturate(1.2)}


/* ═══════════════════ BOSS BAR (original) ═══════════════════ */
.header,.header.nav{position:sticky;top:0;z-index:100;padding:1.25rem 1.5rem;background:transparent;border-bottom:none}
.header.nav.scrolled{border-bottom:none}
.header-content{position:relative;display:flex;align-items:center;justify-content:center;gap:1.4rem;max-width:1060px;margin:0 auto;background:rgba(255,255,255,calc(0.03 + var(--nav-a,0)*0.05));-webkit-backdrop-filter:blur(calc(20px + var(--nav-a,0)*15px)) saturate(calc(1 + var(--nav-a,0)*0.4));backdrop-filter:blur(calc(20px + var(--nav-a,0)*15px)) saturate(calc(1 + var(--nav-a,0)*0.4));border:1px solid rgba(255,255,255,calc(0.06 + var(--nav-a,0)*0.04));border-radius:20px;padding:0.7rem 1.1rem;min-height:60px;box-shadow:0 calc(12px + var(--nav-a,0)*8px) calc(32px + var(--nav-a,0)*16px) calc(-12px - var(--nav-a,0)*4px) rgba(0,0,calc(0.4 + var(--nav-a,0)*0.15));transition:background .4s cubic-bezier(.22,1,.36,1),backdrop-filter .4s cubic-bezier(.22,1,.36,1),border-color .4s cubic-bezier(.22,1,.36,1),box-shadow .4s cubic-bezier(.22,1,.36,1)}
.header-brand-link{position:absolute;left:1rem;display:inline-flex;align-items:center;gap:0.55rem;min-height:2.25rem;flex-shrink:0;color:inherit;text-decoration:none;max-width:160px}
.header-logo{display:flex;align-items:center;justify-content:center;width:30px;height:30px;flex-shrink:0}
.header-logo img{width:100%;height:100%;object-fit:contain;display:block}
.header-brand{font-family:'Sora',sans-serif;font-size:1.05rem;font-weight:700;letter-spacing:-0.02em;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.brand-shine{position:relative;display:inline-block;color:#fff;-webkit-text-fill-color:#ffffff;text-shadow:0 0 8px rgba(255,255,255,0.18);isolation:isolate}
.brand-shine:before{content:attr(data-text);position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none;background-image:linear-gradient(100deg,transparent 0%,transparent 35%,var(--accent,#68aeff) 50%,transparent 65%,transparent 100%);background-size:220% 100%;background-position:140% 0;background-repeat:no-repeat;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;animation:brandShine 4s ease-in-out infinite}
@keyframes brandShine{0%{background-position:140% 0}55%,to{background-position:-40% 0}}
.header-nav{display:flex;align-items:center;gap:0.15rem;transform:translateX(-3rem)}
.header-nav a{display:inline-flex;align-items:center;gap:0.34rem;font-family:'Inter',sans-serif;font-size:0.77rem;font-weight:500;color:var(--dim);text-decoration:none;line-height:1;white-space:nowrap;padding:6px 11px;border-radius:11px;transition:color 0.18s ease,background 0.18s ease}
.header-nav a:hover{color:#fff;background:var(--panel)}
.header-nav a.active{color:#fff;background:rgba(var(--accent-rgb),0.12)}
.header-actions{position:absolute;right:1rem;display:flex;align-items:center;gap:0.45rem}
.header-action{display:inline-flex;align-items:center;justify-content:center;gap:0.38rem;min-height:2.25rem;padding:0 0.8rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;background:rgba(255,255,255,0.04);color:#fff;-webkit-text-fill-color:#ffffff;font-family:'Inter',sans-serif;font-size:0.82rem;font-weight:500;line-height:1;text-decoration:none;white-space:nowrap;cursor:pointer;transition:background 0.18s ease,border-color 0.18s ease}
.header-action:hover{border-color:rgba(255,255,255,0.13);background:rgba(255,255,255,0.09)}
.header-action--profile{padding-left:0.45rem}
.header-avatar{width:22px;height:22px;border-radius:50%;object-fit:cover;flex-shrink:0}
.header-action i{font-size:0.8rem}
.burger{display:none;width:42px;height:42px;border:1px solid var(--line2);border-radius:12px;align-items:center;justify-content:center;font-size:15px;color:var(--ink)}
.m-menu{display:none;flex-direction:column;gap:4px;padding:12px 18px 20px;border-bottom:1px solid var(--line)}
.m-menu a{padding:12px 14px;border-radius:12px;font-size:14px;font-weight:500;color:var(--ink2)}
.m-menu a.active,.m-menu a:hover{background:var(--panel);color:var(--ink)}
.nav.open .m-menu{display:flex}
@media(max-width:900px){.header-nav{display:none}.header-content{justify-content:space-between}.header-brand-link{position:static;left:auto}.header-actions{position:static;right:auto}.burger{display:inline-flex}}
@media(max-width:768px){.header,.header.nav{padding:0.9rem 1rem}.header-content{border-radius:16px}.header-brand-link{max-width:120px}}
@media(max-width:520px){.header-action span{display:none}.header-action{width:2.25rem;padding:0;justify-content:center}.header-brand{font-size:0.9rem}}

/* ═══════════════════ STAGE — hero ═══════════════════ */
.stage{min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px 24px 60px;position:relative;will-change:transform,opacity;transition:transform .08s linear,opacity .08s linear}
.stage__tag{display:inline-flex;align-items:center;gap:8px;padding:7px 18px;border-radius:999px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);font-size:11px;font-weight:600;color:var(--dim);margin-bottom:36px;opacity:0;transform:translateY(18px);transition:all .5s cubic-bezier(.22,1,.36,1)}
.stage__tag.vis{opacity:1;transform:none}
.stage__tag i{color:var(--accent-light);font-size:10px}
.stage h1{font-family:'Sora',sans-serif;font-size:clamp(42px,7vw,80px);font-weight:800;line-height:1;letter-spacing:-.05em;color:#fff;max-width:780px;margin-bottom:22px;opacity:0;transform:translateY(30px);transition:all .6s cubic-bezier(.22,1,.36,1)}
.stage h1.vis{opacity:1;transform:none}
.stage__bar{width:56px;height:3px;border-radius:99px;background:var(--grad);margin:0 auto 22px;opacity:0;transform:scaleX(0);transition:all .5s cubic-bezier(.22,1,.36,1) .06s}
.stage__bar.vis{opacity:1;transform:none}
.stage__desc{font-size:16px;color:rgba(255,255,255,0.35);max-width:460px;line-height:1.7;margin-bottom:44px;opacity:0;transform:translateY(20px);transition:all .5s cubic-bezier(.22,1,.36,1) .1s}
.stage__desc.vis{opacity:1;transform:none}
.stage__actions{display:flex;gap:14px;margin-bottom:68px;opacity:0;transform:translateY(18px);transition:all .48s cubic-bezier(.22,1,.36,1) .16s}
.stage__actions.vis{opacity:1;transform:none}
.btn{display:inline-flex;align-items:center;gap:9px;padding:14px 28px;border-radius:14px;font-size:14px;font-weight:700;transition:all .22s cubic-bezier(.22,1,.36,1);position:relative;overflow:hidden}
.btn--fill{background:var(--grad);color:#fff;box-shadow:0 8px 32px -8px rgba(var(--accent-rgb),0.6)}
.btn--fill:hover{transform:translateY(-3px);box-shadow:0 14px 44px -8px rgba(var(--accent-rgb),0.75)}
.btn--fill::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.12),transparent);transform:translateX(-100%);transition:transform .5s}
.btn--fill:hover::after{transform:translateX(100%)}
.btn--ghost{border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.65);background:rgba(255,255,255,0.03)}
.btn--ghost:hover{border-color:rgba(255,255,255,0.22);background:rgba(255,255,255,0.06);color:#fff}
.stage__metrics{display:flex;gap:52px;opacity:0;transform:translateY(14px);transition:all .48s cubic-bezier(.22,1,.36,1) .22s}
.stage__metrics.vis{opacity:1;transform:none}
.metric{text-align:center}
.metric b{display:block;font-family:'Sora',sans-serif;font-size:30px;font-weight:800;color:#fff}
.metric span{font-size:11px;color:rgba(255,255,255,0.25);margin-top:4px}
._letter{display:inline-block;opacity:0;transform:translateY(30px) rotateX(40deg);transition:all .5s cubic-bezier(.22,1,.36,1)}
._letter.vis{opacity:1;transform:none}
.scroll-down{position:absolute;bottom:28px;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:6px;color:rgba(255,255,255,0.15);font-size:11px;animation:bob 2.5s ease-in-out infinite}
@keyframes bob{0%,100%{transform:translateX(-50%) translateY(0)}50%{transform:translateX(-50%) translateY(5px)}}

/* ═══════════════════ CAPSULE — section header ═══════════════════ */
.capsule{text-align:center;margin-bottom:48px;opacity:0;transform:translateY(24px);transition:all .5s cubic-bezier(.22,1,.36,1)}
.capsule.vis{opacity:1;transform:none}
.capsule__label{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:12px}
.capsule__heading{font-family:'Sora',sans-serif;font-size:clamp(26px,3.5vw,40px);font-weight:800;color:#fff;letter-spacing:-.03em}

/* ═══════════════════ CARDS — features ═══════════════════ */
.cards{padding:40px 0 60px}
.row3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.card{padding:32px 28px;border-radius:20px;border:1px solid rgba(255,255,255,0.05);background:rgba(255,255,255,0.02);position:relative;overflow:hidden;transition:all .4s cubic-bezier(.22,1,.36,1);opacity:0;transform:translateY(24px)}
.card.vis{opacity:1;transform:none}
.card:hover{border-color:rgba(var(--accent-rgb),0.2);background:rgba(255,255,255,0.035);transform:translateY(-4px)}
.card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(var(--accent-rgb),0.3),transparent);opacity:0;transition:opacity .3s}
.card:hover::before{opacity:1}
.card__icon{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,rgba(var(--accent-rgb),0.12),rgba(var(--accent-rgb),0.04));display:flex;align-items:center;justify-content:center;color:var(--accent-light);font-size:18px;margin-bottom:20px;transition:transform .35s cubic-bezier(.34,1.56,.64,1)}
.card:hover .card__icon{transform:scale(1.1) rotate(-3deg)}
.card__idx{position:absolute;top:24px;right:24px;font-family:'Sora',sans-serif;font-size:42px;font-weight:800;color:rgba(255,255,255,0.025);line-height:1}
.card h3{font-family:'Sora',sans-serif;font-size:17px;font-weight:700;color:#fff;margin-bottom:8px}
.card p{font-size:13.5px;color:rgba(255,255,255,0.35);line-height:1.7}

/* ═══════════════════ DUAL — launcher ═══════════════════ */
.dual{padding:20px 0 60px}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:stretch}
.app{border-radius:20px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.025);overflow:hidden;opacity:0;transform:translateX(-30px);transition:all .55s cubic-bezier(.22,1,.36,1)}
.app.vis{opacity:1;transform:none}
.app__chrome{display:flex;align-items:center;gap:6px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,0.05)}
.app__pip{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,0.08)}
.app__pip:first-child{background:rgba(var(--accent-rgb),0.6)}
.app__label{margin-left:8px;font-size:11px;font-weight:600;color:rgba(255,255,255,0.22);letter-spacing:.04em;text-transform:uppercase}
.app__main{padding:28px;display:flex;flex-direction:column;justify-content:center;position:relative}
.app__main::before{content:'';position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;background:radial-gradient(circle,rgba(var(--accent-rgb),0.1),transparent 70%);pointer-events:none}
.app__top{display:flex;align-items:center;gap:14px;margin-bottom:24px}
.app__icon{width:48px;height:48px;border-radius:14px;background:var(--grad);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px -6px rgba(var(--accent-rgb),0.5);transition:transform .3s cubic-bezier(.34,1.56,.64,1)}
.app:hover .app__icon{transform:rotate(-5deg) scale(1.04)}
.app__icon img{width:36px;height:36px;object-fit:contain}
.app__title{font-family:'Sora',sans-serif;font-size:17px;font-weight:700;color:#fff}
.app__online{font-size:11px;color:rgba(var(--accent-light),0.8);display:flex;align-items:center;gap:6px;margin-top:2px}
.app__online i{width:6px;height:6px;border-radius:50%;background:var(--success);box-shadow:0 0 6px rgba(52,211,153,0.6)}
.app__tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px}
.tag{font-size:11.5px;font-weight:600;padding:5px 12px;border-radius:100px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.03);color:rgba(255,255,255,0.3);transition:all .2s}
.tag.on{border-color:rgba(var(--accent-rgb),0.4);background:rgba(var(--accent-rgb),0.1);color:var(--accent-light)}
.tag:hover{border-color:rgba(var(--accent-rgb),0.35);background:rgba(var(--accent-rgb),0.06)}
.bar{margin-bottom:24px}
.bar__hd{display:flex;justify-content:space-between;font-size:11px;color:rgba(255,255,255,0.22);margin-bottom:8px}
.bar__hd b{color:rgba(255,255,255,0.5);font-weight:600}
.bar__track{height:6px;border-radius:100px;background:rgba(255,255,255,0.05);overflow:hidden}
.bar__fill{height:100%;border-radius:100px;background:var(--grad);width:0;transition:width 1.8s cubic-bezier(.22,1,.36,1);box-shadow:0 0 12px rgba(var(--accent-rgb),0.4);position:relative}
.bar__fill::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.25),transparent);animation:shine 2s infinite}
@keyframes shine{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
.go{width:100%;padding:14px;border-radius:14px;background:var(--grad);color:#fff;font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 8px 28px -6px rgba(var(--accent-rgb),0.5);transition:all .25s cubic-bezier(.22,1,.36,1);position:relative;overflow:hidden}
.go:hover{transform:translateY(-2px);box-shadow:0 12px 36px -6px rgba(var(--accent-rgb),0.7)}
.go::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.1),transparent);transform:translateX(-100%);transition:transform .5s}
.go:hover::after{transform:translateX(100%)}

.info{border-radius:20px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.025);padding:36px;display:flex;flex-direction:column;justify-content:center;opacity:0;transform:translateX(30px);transition:all .55s cubic-bezier(.22,1,.36,1) .1s}
.info.vis{opacity:1;transform:none}
.info__ic{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,rgba(var(--accent-rgb),0.15),rgba(var(--accent-rgb),0.05));display:flex;align-items:center;justify-content:center;color:var(--accent-light);font-size:22px;margin-bottom:24px}
.info h2{font-family:'Sora',sans-serif;font-size:24px;font-weight:800;color:#fff;margin-bottom:12px;line-height:1.2}
.info p{font-size:14px;color:rgba(255,255,255,0.38);line-height:1.75;margin-bottom:28px}
.info__list{display:flex;flex-direction:column;gap:12px}
.info__row{display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,0.45)}
.info__row i{color:var(--accent);font-size:12px;width:20px;text-align:center}

/* ═══════════════════ SCREEN — video ═══════════════════ */
.screen{padding:20px 0 60px}
.screen__frame{border-radius:20px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.025);overflow:hidden;position:relative;opacity:0;transform:scale(0.97);transition:all .55s cubic-bezier(.22,1,.36,1)}
.screen__frame.vis{opacity:1;transform:none}
.screen__frame iframe{width:100%;aspect-ratio:16/9;min-height:380px;border:none;display:block}
.screen__fade{position:absolute;inset:0;background:linear-gradient(to bottom,transparent 60%,var(--bg));pointer-events:none;z-index:1}

/* ═══════════════════ PROMPT — CTA ═══════════════════ */
.prompt{padding:20px 0 0}
.prompt__box{border-radius:24px;border:1px solid rgba(var(--accent-rgb),0.2);background:linear-gradient(135deg,rgba(var(--accent-rgb),0.07),rgba(var(--accent-rgb),0.02));padding:60px 32px;text-align:center;position:relative;overflow:hidden}
.prompt__box::before{content:'';position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(var(--accent-rgb),0.15),transparent 70%);top:-200px;left:50%;transform:translateX(-50%);pointer-events:none}
.prompt__box h2{position:relative;font-family:'Sora',sans-serif;font-size:clamp(24px,3.5vw,38px);font-weight:800;color:#fff;margin-bottom:12px}
.prompt__box p{position:relative;font-size:15px;color:rgba(255,255,255,0.38);margin-bottom:32px}
.prompt__box .btn{position:relative}

/* ═══════════════════ FOOTER (original) ═══════════════════ */
.footer{position:relative;background:transparent;padding:0 20px 32px;margin-top:160px}
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
.footer__link{font-family:Inter,sans-serif;font-size:.8rem;line-height:1.2;color:rgba(255,255,255,.5);text-decoration:none;transition:color .22s ease}
.footer__link:hover{color:#fff}
.footer__credit{grid-column:1/-1;margin-top:10px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);text-align:center;font-family:Inter,sans-serif;font-size:.78rem;line-height:1.3;color:rgba(255,255,255,.4)}
.footer__credit a{color:rgba(255,255,255,.6);text-decoration:none;transition:color .22s ease}
.footer__credit a:hover{color:#fff}

/* ═══════════════════ BACK TO TOP ═══════════════════ */
.top{position:fixed;bottom:32px;right:32px;width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);backdrop-filter:blur(12px);color:rgba(255,255,255,0.45);font-size:16px;display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:0;transform:translateY(16px);transition:all .4s cubic-bezier(.22,1,.36,1);pointer-events:none;z-index:90;will-change:transform,opacity}
.top.vis{opacity:1;transform:translateY(0);pointer-events:auto}
.top:hover{background:rgba(var(--accent-rgb),0.15);border-color:rgba(var(--accent-rgb),0.3);color:#fff;transform:translateY(-3px)}

/* ═══════════════════ CLICKGUI SECTION ═══════════════════ */
.clickgui-section{padding:20px 0 60px}
.section-head{text-align:center;max-width:700px;margin:0 auto 3rem}
.section-kicker{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:12px;display:inline-flex;align-items:center;gap:8px}
.section-title{font-family:'Sora',sans-serif;font-size:clamp(26px,3.5vw,40px);font-weight:800;color:#fff;letter-spacing:-.03em}
.section-sub{font-size:14px;color:rgba(255,255,255,0.38);margin-top:12px;line-height:1.6}
.gui-window-wrapper{max-width:1020px;margin:0 auto;border-radius:20px;background:rgba(14,16,26,0.88);-webkit-backdrop-filter:blur(28px);backdrop-filter:blur(28px);border:1px solid rgba(255,255,255,0.1);box-shadow:0 28px 70px -15px rgba(0,0,0,0.9),0 0 35px rgba(var(--accent-rgb),0.18);overflow:hidden}
.gui-titlebar{display:flex;align-items:center;justify-content:space-between;padding:14px 22px;background:rgba(255,255,255,0.03);border-bottom:1px solid rgba(255,255,255,0.06)}
.gui-window-controls{display:flex;gap:7px}
.gui-dot{width:11px;height:11px;border-radius:50%}.gui-dot.close{background:#ff5f56}.gui-dot.min{background:#ffbd2e}.gui-dot.max{background:#27c93f}
.gui-window-title{font-family:'Inter',sans-serif;font-size:13px;color:rgba(255,255,255,0.35)}.gui-window-title span{color:var(--accent-light);font-weight:700}
.gui-interactive-hint{font-size:11px;color:var(--accent-light);background:rgba(var(--accent-rgb),0.12);padding:3px 10px;border-radius:999px;border:1px solid rgba(var(--accent-rgb),0.3)}
.gui-body{display:grid;grid-template-columns:210px 1fr;min-height:480px}
.gui-sidebar{background:rgba(10,11,18,0.6);border-right:1px solid rgba(255,255,255,0.06);padding:18px 14px;display:flex;flex-direction:column;justify-content:space-between}
.gui-tab-list{display:flex;flex-direction:column;gap:6px}
.gui-tab-btn{display:flex;align-items:center;gap:12px;padding:11px 15px;border-radius:10px;font-size:14px;font-weight:600;color:rgba(255,255,255,0.35);transition:all .2s;text-align:left;width:100%;border:none;background:none;cursor:pointer;font-family:inherit}
.gui-tab-btn i{width:18px;text-align:center;color:rgba(255,255,255,0.2)}
.gui-tab-btn:hover{color:#fff;background:rgba(255,255,255,0.04)}
.gui-tab-btn.active{color:#fff;background:rgba(var(--accent-rgb),0.12);border:1px solid rgba(var(--accent-rgb),0.3)}
.gui-tab-btn.active i{color:var(--accent-light)}
.gui-sidebar-footer{padding:12px;background:rgba(255,255,255,0.02);border-radius:10px;border:1px solid rgba(255,255,255,0.04)}
.user-hud{display:flex;align-items:center;gap:10px}
.user-hud-avatar{width:30px;height:30px;border-radius:50%;object-fit:cover;border:1px solid var(--accent)}
.user-hud-info{font-size:12px}.user-hud-name{font-weight:700;color:#fff}.user-hud-sub{color:#6ee7b7;font-size:11px}
.gui-content-pane{padding:24px;overflow-y:auto;max-height:520px}
.gui-tab-panel{display:none}.gui-tab-panel.active{display:block}
.modules-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px}
.module-card{background:rgba(20,22,34,0.55);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:18px;transition:all .2s}
.module-card:hover{border-color:rgba(255,255,255,0.12);background:rgba(24,27,42,0.7)}
.module-card.enabled{border-color:rgba(var(--accent-rgb),0.35);background:rgba(var(--accent-rgb),0.08)}
.module-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:7px}
.module-name-wrap{display:flex;align-items:center;gap:8px}
.module-name{font-size:14px;font-weight:700;color:#fff}
.module-keybind{font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.3)}
.custom-switch{position:relative;width:38px;height:22px;cursor:pointer}
.custom-switch input{opacity:0;width:0;height:0;position:absolute}
.switch-slider{position:absolute;inset:0;background-color:rgba(255,255,255,0.1);border-radius:999px;transition:.25s}
.switch-slider::before{position:absolute;content:"";height:14px;width:14px;left:4px;bottom:4px;background-color:#fff;border-radius:50%;transition:.25s}
.custom-switch input:checked+.switch-slider{background-color:var(--accent)}
.custom-switch input:checked+.switch-slider::before{transform:translateX(16px)}
.module-desc{font-size:12px;color:rgba(255,255,255,0.35);line-height:1.4;margin-bottom:12px}
.module-setting{margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,0.05)}
.setting-label-row{display:flex;justify-content:space-between;align-items:center;font-size:11px;color:rgba(255,255,255,0.35);margin-bottom:6px}
.setting-val-badge{color:var(--accent-light);font-weight:700}
.cyber-slider{width:100%;height:5px;border-radius:999px;background:rgba(255,255,255,0.1);outline:none;-webkit-appearance:none;cursor:pointer}
.cyber-slider::-webkit-slider-thumb{-webkit-appearance:none;width:13px;height:13px;border-radius:50%;background:#fff;cursor:pointer}
.clickgui-note{text-align:center;margin-top:20px;font-size:12px;color:rgba(255,255,255,0.2);font-style:italic}

/* ═══════════════════ BREAKPOINTS ═══════════════════ */
@media(max-width:768px){.row3,.row2{grid-template-columns:1fr}.stage h1{font-size:clamp(32px,9vw,52px)}.stage__metrics{gap:28px}.metric b{font-size:24px}.footer__inner{grid-template-columns:1fr;gap:20px;text-align:center;padding:20px 16px 14px}.footer__brand-block,.footer__nav-group,.footer__links{align-items:center}.footer__socials{justify-content:center}}
@media(max-width:640px){.gui-body{grid-template-columns:1fr}.gui-sidebar{border-right:none;border-bottom:1px solid rgba(255,255,255,0.06)}.gui-tab-list{flex-direction:row;overflow-x:auto;padding-bottom:4px}.gui-tab-btn{white-space:nowrap}}
/* ═══════════════════ BLUR ON SCROLL — sections blur until revealed ═══════════════════ */
.reveal{opacity:0;filter:blur(12px);transform:translateY(40px) scale(0.97);transition:opacity .7s cubic-bezier(.22,1,.36,1),filter .7s cubic-bezier(.22,1,.36,1),transform .7s cubic-bezier(.22,1,.36,1)}
.reveal.vis{opacity:1;filter:blur(0);transform:none}
.reveal[data-delay="1"]{transition-delay:.1s}.reveal[data-delay="2"]{transition-delay:.2s}.reveal[data-delay="3"]{transition-delay:.3s}

/* ═══════════════════ MAGNETIC BUTTONS ═══════════════════ */
.mag{position:relative;transition:transform .3s cubic-bezier(.22,1,.36,1)}
.mag::before{content:'';position:absolute;inset:-2px;border-radius:inherit;background:var(--grad);opacity:0;transition:opacity .3s;z-index:-1;filter:blur(12px)}
.mag:hover::before{opacity:.4}

/* ═══════════════════ GLOW PULSE ON CARDS ═══════════════════ */
.card{--gx:50%;--gy:50%}
.card::after{content:'';position:absolute;inset:0;border-radius:inherit;background:radial-gradient(circle 200px at var(--gx) var(--gy),rgba(var(--accent-rgb),0.12),transparent);opacity:0;transition:opacity .4s;pointer-events:none}
.card:hover::after{opacity:1}

/* ═══════════════════ TILT 3D ═══════════════════ */
.tilt{perspective:800px;transform-style:preserve-3d}

/* ═══════════════════ ANIMATED GRADIENT BORDER ═══════════════════ */
.prompt__box{position:relative}
.prompt__box::after{content:'';position:absolute;inset:0;border-radius:inherit;border:2px solid transparent;background:linear-gradient(var(--bg),var(--bg)) padding-box,conic-gradient(from var(--border-angle,0deg),transparent 40%,var(--accent) 50%,transparent 60%) border-box;opacity:.5;animation:borderSpin 6s linear infinite;pointer-events:none}
@keyframes borderSpin{to{--border-angle:360deg}}
@property --border-angle{syntax:'<angle>';initial-value:0deg;inherits:false}

/* ═══════════════════ HERO FLOATING DOTS ═══════════════════ */
.stage::before{display:none}
.stage::after{display:none}
@keyframes floatDot{0%{transform:translate(0,0) scale(1)}50%{transform:translate(20px,-30px) scale(1.15)}100%{transform:translate(-15px,20px) scale(0.9)}}

/* ═══════════════════ HERO PARALLAX GLOW ═══════════════════ */
.stage__glow{display:none}

/* ═══════════════════ SCROLL PROGRESS ═══════════════════ */
.scroll-prog{position:fixed;top:0;left:0;height:3px;background:var(--grad);z-index:200;transform-origin:left;transform:scaleX(0);transition:transform .08s linear;will-change:transform}

/* ═══════════════════ CARD STAGGER ═══════════════════ */
.card[data-stagger] .card__icon{transition-delay:calc(var(--d,0) * 60ms)}

/* ═══════════════════ COUNTER GLOW ═══════════════════ */
.metric b{position:relative}
.metric b::after{content:'';position:absolute;inset:-4px -8px;border-radius:12px;background:rgba(var(--accent-rgb),0.06);filter:blur(8px);opacity:0;transition:opacity .3s}
.metric:hover b::after{opacity:1}

/* ═══════════════════ BREAKPOINTS ═══════════════════ */
@media(max-width:768px){.row3,.row2{grid-template-columns:1fr}.stage h1{font-size:clamp(32px,9vw,52px)}.stage__metrics{gap:28px}.metric b{font-size:24px}.footer__inner{grid-template-columns:1fr;gap:20px;text-align:center;padding:20px 16px 14px}.footer__brand-block,.footer__nav-group,.footer__links{align-items:center}.footer__socials{justify-content:center}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}.stage h1,.stage__desc,.stage__actions,.stage__metrics,.stage__tag,.capsule,.card,.app,.info,.screen__frame,.prompt__box,.reveal,._letter{opacity:1!important;transform:none!important;filter:none!important}}
</style>
</head>
<body>
<noscript><style>.reveal,.rv{opacity:1!important;transform:none!important;filter:none!important}._letter{opacity:1!important;transform:none!important}</style></noscript>

<div class="scroll-prog" id="scrollProg"></div>
<div class="world"></div>

<header class="header nav" id="nav">
<div class="header-content">
  <a href="/main" class="header-brand-link">
    <span class="header-logo"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></span>
    <span class="header-brand brand-shine" data-text="<?php echo $site_name; ?>"><?php echo $site_name; ?></span>
  </a>
  <nav class="header-nav">
    <a href="/main" class="active">Главная</a>
    <a href="/shop">Магазин</a>
    <a href="/rules">Правила</a>
    <a href="/privacy">Соглашение</a>
    <a href="/profile">Профиль</a>
  </nav>
  <div class="header-actions">
    <?php if (isLoggedIn() && $current_user): ?>
      <button class="header-action header-action--profile" type="button" onclick="location.href='/profile'">
        <?php if ($avatar_url): ?><img class="header-avatar" src="<?php echo htmlspecialchars($avatar_url); ?>" alt="" onerror="this.style.display='none'"><?php else: ?><img class="header-avatar" src="/assets/ava.png" alt="" onerror="this.style.display='none'"><?php endif; ?>
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
  <a href="/main" class="active">Главная</a>
  <a href="/shop">Магазин</a>
  <a href="/rules">Правила</a>
  <a href="/privacy">Соглашение</a>
  <a href="/profile">Профиль</a>
  <?php if (isLoggedIn()): ?><a href="/logout">Выйти</a><?php else: ?><a href="/login">Войти</a><a href="/register">Регистрация</a><?php endif; ?>
</div>
</header>

<section class="stage">
  <div class="stage__glow"></div>
  <div class="stage__tag" id="sTag"><i class="fas fa-shield-halved"></i> Безопасный клиент для Minecraft</div>
  <h1 id="sH1">Играй как удобно</h1>
  <div class="stage__bar" id="sBar"></div>
  <p class="stage__desc" id="sDesc">Стабильный клиент, быстрый запуск и всё что нужно — без лишнего шума</p>
  <div class="stage__actions" id="sActs">
    <?php if (isLoggedIn()): ?>
      <a href="/profile" class="btn btn--fill mag"><i class="fas fa-rocket"></i> Личный кабинет</a>
    <?php else: ?>
      <a href="/login" class="btn btn--fill mag"><i class="fas fa-rocket"></i> Начать</a>
    <?php endif; ?>
    <a href="/shop" class="btn btn--ghost mag"><i class="fas fa-tag"></i> Магазин</a>
  </div>
  <div class="stage__metrics" id="sMet">
    <div class="metric"><b class="grad" data-count="<?php echo $launches_count; ?>">0</b><span>запусков</span></div>
    <div class="metric"><b class="grad" data-count="<?php echo $clients_count; ?>">0</b><span>клиентов</span></div>
    <div class="metric"><b class="grad" data-count="<?php echo $updates_count; ?>">0</b><span>версий</span></div>
  </div>
  <div class="scroll-down"><i class="fas fa-chevron-down"></i><span>листай вниз</span></div>
</section>

<section class="cards reveal"><div class="wrap">
  <div class="capsule" id="capFeat">
    <div class="capsule__label">Возможности</div>
    <h2 class="capsule__heading">Всё для комфортной игры</h2>
  </div>
  <div class="row3">
    <div class="card tilt" data-stagger="0" style="--d:0"><div class="card__idx">01</div><div class="card__icon"><i class="fas fa-bolt"></i></div><h3>Быстрый запуск</h3><p>Пара кликов — и вы в игре. Даже на слабом железе.</p></div>
    <div class="card tilt" data-stagger="1" style="--d:1"><div class="card__idx">02</div><div class="card__icon"><i class="fas fa-shield-halved"></i></div><h3>Защита</h3><p>HWID и аккаунт под надёжной защитой.</p></div>
    <div class="card tilt" data-stagger="2" style="--d:2"><div class="card__idx">03</div><div class="card__icon"><i class="fas fa-rotate"></i></div><h3>Обновления</h3><p>Клиент готов к обновлению сразу после выхода.</p></div>
    <div class="card tilt" data-stagger="3" style="--d:3"><div class="card__idx">04</div><div class="card__icon"><i class="fas fa-code-branch"></i></div><h3>Множество версий</h3><p>От классических до свежих — переключение в один клик.</p></div>
    <div class="card tilt" data-stagger="4" style="--d:4"><div class="card__idx">05</div><div class="card__icon"><i class="fas fa-sliders"></i></div><h3>Настройка под себя</h3><p>Гибкие параметры и сохранение конфигурации.</p></div>
    <div class="card tilt" data-stagger="5" style="--d:5"><div class="card__idx">06</div><div class="card__icon"><i class="fas fa-headset"></i></div><h3>Поддержка 24/7</h3><p>Вопросы — в Discord или Telegram. Живые люди, без ботов.</p></div>
  </div>
</div></section>

<section class="clickgui-section reveal" id="clickgui"><div class="wrap">
  <div class="section-head">
    <div class="section-kicker"><i class="fas fa-sliders-h"></i> Интерактивный интерфейс</div>
    <h2 class="section-title">Внутриигровой ClickGUI</h2>
    <p class="section-sub">Попробуйте функционал клиента прямо на сайте. Все переключатели и ползунки интерактивны.</p>
  </div>
  <div class="gui-window-wrapper">
    <div class="gui-titlebar">
      <div class="gui-window-controls"><span class="gui-dot close"></span><span class="gui-dot min"></span><span class="gui-dot max"></span></div>
      <div class="gui-window-title"><i class="fas fa-terminal"></i> <?php echo $site_name; ?> — <span>ClickGUI</span></div>
      <div class="gui-interactive-hint">Кликабельно</div>
    </div>
    <div class="gui-body">
      <div class="gui-sidebar">
        <div class="gui-tab-list">
          <button class="gui-tab-btn active" data-tab="combat"><i class="fas fa-crosshairs"></i> <span>Combat</span></button>
          <button class="gui-tab-btn" data-tab="movement"><i class="fas fa-running"></i> <span>Movement</span></button>
          <button class="gui-tab-btn" data-tab="render"><i class="fas fa-eye"></i> <span>Render</span></button>
          <button class="gui-tab-btn" data-tab="player"><i class="fas fa-user-shield"></i> <span>Player</span></button>
          <button class="gui-tab-btn" data-tab="world"><i class="fas fa-globe"></i> <span>World</span></button>
        </div>
        <div class="gui-sidebar-footer">
          <div class="user-hud">
            <img src="<?php echo $avatar_url ? htmlspecialchars($avatar_url) : '/assets/ava.png'; ?>" alt="" class="user-hud-avatar">
            <div class="user-hud-info">
              <div class="user-hud-name"><?php echo isLoggedIn() ? htmlspecialchars($current_user['username'] ?? 'Player') : 'Player_Pro'; ?></div>
              <div class="user-hud-sub">&#9679; Активен</div>
            </div>
          </div>
        </div>
      </div>
      <div class="gui-content-pane">
        <div class="gui-tab-panel active" id="tab-combat">
          <div class="modules-grid">
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">KillAura</span><span class="module-keybind">[R]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Плавная атака целей с идеальной ротацией.</p><div class="module-setting"><div class="setting-label-row"><span>Дистанция:</span><span class="setting-val-badge">3.6&#x43C;</span></div><input type="range" class="cyber-slider" min="3.0" max="5.0" step="0.1" value="3.6" data-unit="&#x43C;"></div></div>
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">AutoTotem</span><span class="module-keybind">[F]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Быстрая замена тотема при опасном уровне здоровья.</p><div class="module-setting"><div class="setting-label-row"><span>Задержка:</span><span class="setting-val-badge">10&#x43C;&#x441;</span></div><input type="range" class="cyber-slider" min="0" max="80" step="5" value="10"></div></div>
            <div class="module-card"><div class="module-header"><div class="module-name-wrap"><span class="module-name">HitBoxes</span><span class="module-keybind">[H]</span></div><label class="custom-switch"><input type="checkbox"><span class="switch-slider"></span></label></div><p class="module-desc">Расширение хитбоксов противников.</p></div>
            <div class="module-card"><div class="module-header"><div class="module-name-wrap"><span class="module-name">TriggerBot</span><span class="module-keybind">[V]</span></div><label class="custom-switch"><input type="checkbox"><span class="switch-slider"></span></label></div><p class="module-desc">Автоматический клик при наведении прицела на врага.</p></div>
          </div>
        </div>
        <div class="gui-tab-panel" id="tab-movement">
          <div class="modules-grid">
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">TargetStrafe</span><span class="module-keybind">[X]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Стрейф вокруг цели на высокой скорости.</p><div class="module-setting"><div class="setting-label-row"><span>Радиус:</span><span class="setting-val-badge">2.4&#x43C;</span></div><input type="range" class="cyber-slider" min="1.5" max="4.0" step="0.1" value="2.4"></div></div>
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">ElytraFly</span><span class="module-keybind">[G]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Стабильный полет на элитрах без фейерверков.</p></div>
            <div class="module-card"><div class="module-header"><div class="module-name-wrap"><span class="module-name">Speed</span><span class="module-keybind">[B]</span></div><label class="custom-switch"><input type="checkbox"><span class="switch-slider"></span></label></div><p class="module-desc">Ускоренное передвижение и распрыжка по блокам.</p></div>
            <div class="module-card"><div class="module-header"><div class="module-name-wrap"><span class="module-name">Spider</span><span class="module-keybind">[J]</span></div><label class="custom-switch"><input type="checkbox"><span class="switch-slider"></span></label></div><p class="module-desc">Подъем по вертикальным стенам.</p></div>
          </div>
        </div>
        <div class="gui-tab-panel" id="tab-render">
          <div class="modules-grid">
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">NameTags & ESP</span><span class="module-keybind">[P]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Отображение игроков, их брони и полоски здоровья.</p></div>
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">Tracers</span><span class="module-keybind">[T]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Линии трассировки до игроков в радиусе видимости.</p></div>
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">UI Blur</span><span class="module-keybind">NONE</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Красивое размытие фона при открытом меню.</p></div>
            <div class="module-card"><div class="module-header"><div class="module-name-wrap"><span class="module-name">FullBright</span><span class="module-keybind">NONE</span></div><label class="custom-switch"><input type="checkbox"><span class="switch-slider"></span></label></div><p class="module-desc">Постоянная максимальная яркость в пещерах.</p></div>
          </div>
        </div>
        <div class="gui-tab-panel" id="tab-player">
          <div class="modules-grid">
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">InventoryCleaner</span><span class="module-keybind">[C]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Автоматическая сортировка инвентаря и очистка от мусора.</p></div>
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">ChestStealer</span><span class="module-keybind">[Y]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Быстрый сбор лута из сундуков за доли секунды.</p></div>
            <div class="module-card"><div class="module-header"><div class="module-name-wrap"><span class="module-name">FreeCam</span><span class="module-keybind">[U]</span></div><label class="custom-switch"><input type="checkbox"><span class="switch-slider"></span></label></div><p class="module-desc">Свободный полёт камеры для разведки местности.</p></div>
            <div class="module-card"><div class="module-header"><div class="module-name-wrap"><span class="module-name">AutoArmor</span><span class="module-keybind">NONE</span></div><label class="custom-switch"><input type="checkbox"><span class="switch-slider"></span></label></div><p class="module-desc">Автоматическое надевание лучшей брони.</p></div>
          </div>
        </div>
        <div class="gui-tab-panel" id="tab-world">
          <div class="modules-grid">
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">Scaffold</span><span class="module-keybind">[Z]</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Автоматическая постройка мостов под ногами.</p></div>
            <div class="module-card enabled"><div class="module-header"><div class="module-name-wrap"><span class="module-name">FastPlace</span><span class="module-keybind">NONE</span></div><label class="custom-switch"><input type="checkbox" checked><span class="switch-slider"></span></label></div><p class="module-desc">Мгновенная установка блоков и кристаллов.</p></div>
            <div class="module-card"><div class="module-header"><div class="module-name-wrap"><span class="module-name">AutoFarm</span><span class="module-keybind">NONE</span></div><label class="custom-switch"><input type="checkbox"><span class="switch-slider"></span></label></div><p class="module-desc">Авто-копка и фарм мобов на спавнерах.</p></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="clickgui-note"><i class="fas fa-info-circle"></i> Это всего лишь показ на сайте, в игре отличается</div>
</div></section>

<section class="screen reveal"><div class="wrap">
  <div class="screen__frame" id="vidFrame">
    <div class="screen__fade"></div>
    <iframe src="<?php echo htmlspecialchars($YOUTUBE_LINK ?? 'https://www.youtube.com/embed/rgk3ZHFmHm4'); ?>" title="Обзор клиента" loading="lazy" allowfullscreen></iframe>
  </div>
</div></section>

<section class="prompt reveal"><div class="wrap">
  <div class="prompt__box" id="ctaBox">
    <h2>Готовы попробовать?</h2>
    <p>Создайте аккаунт и начните играть уже сегодня</p>
    <?php if (isLoggedIn()): ?>
      <a href="/shop" class="btn btn--fill mag"><i class="fas fa-cart-shopping"></i> В магазин</a>
    <?php else: ?>
      <a href="/register" class="btn btn--fill mag"><i class="fas fa-user-plus"></i> Регистрация</a>
    <?php endif; ?>
  </div>
</div></section>

<footer class="footer">
<div class="footer__inner">
  <div class="footer__brand-block">
    <div class="footer__brand"><div class="footer__mark"><img src="/assets/logo.png" alt="" style="width:24px;height:24px;object-fit:contain" onerror="this.style.display='none'"></div><span class="footer__brand-name"><?php echo $site_name; ?></span></div>
    <p class="footer__copyright">&copy; <?php echo $site_name; ?> <?php echo $current_year; ?>. Все права защищены.</p>
    <div class="footer__socials"><a class="footer__social" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank"><i class="fab fa-telegram"></i></a></div>
  </div>
  <div class="footer__nav-group"><h3 class="footer__title">Навигация</h3><nav class="footer__links"><a class="footer__link" href="/main">Главная</a><a class="footer__link" href="/shop">Магазин</a><a class="footer__link" href="/rules">Правила</a><a class="footer__link" href="/privacy">Соглашение</a></nav></div>
  <div class="footer__nav-group"><h3 class="footer__title">Документы</h3><nav class="footer__links"><a class="footer__link" href="/privacy">Политика конфиденциальности</a><a class="footer__link" href="/rules">Пользовательское соглашение</a></nav></div>
  <div class="footer__nav-group"><h3 class="footer__title">Поддержка</h3><nav class="footer__links"><a class="footer__link" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank">Telegram-канал</a></nav></div>
  <p class="footer__credit">made by <a href="https://t.me/kodexnull" target="_blank">kodexnull</a> special for <a href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank"><?php echo $site_name; ?></a></p>
</div>
</footer>

<button class="top" aria-label="Наверх"><i class="fas fa-chevron-up"></i></button>

<script>
(function(){
  var rm=window.matchMedia('(prefers-reduced-motion:reduce)').matches;

  /* ═══════════════ SMOOTH SCROLL ENGINE ═══════════════ */
  var smoothY=window.scrollY,targetY=0,animating=false;
  function easeOutCubic(t){return 1-Math.pow(1-t,3)}
  function smoothLoop(){
    if(!animating)return;
    var diff=targetY-smoothY;
    if(Math.abs(diff)<0.5){smoothY=targetY;window.scrollTo(0,smoothY);animating=false;return}
    smoothY+=diff*0.08;
    window.scrollTo(0,Math.round(smoothY));
    requestAnimationFrame(smoothLoop);
  }
  function smoothScrollTo(y){
    targetY=Math.max(0,Math.min(y,document.documentElement.scrollHeight-window.innerHeight));
    if(!animating){animating=true;smoothLoop()}
  }
  if(!rm){
    /* override native scroll for anchor clicks */
    document.addEventListener('click',function(e){
      var a=e.target.closest('a[href]');
      if(!a)return;
      var href=a.getAttribute('href');
      if(!href||href.charAt(0)!=='#'||href.length<2)return;
      var target=document.getElementById(href.slice(1));
      if(!target)return;
      e.preventDefault();
      var rect=target.getBoundingClientRect();
      smoothScrollTo(window.scrollY+rect.top-80);
    });
  }

  /* ═══════════════ NAVBAR + SCROLL PROGRESS ═══════════════ */
  var nav=document.getElementById('nav'),navContent=nav?nav.querySelector('.header-content'):null,prog=document.getElementById('scrollProg'),tick=false;
  window.addEventListener('scroll',function(){if(tick)return;tick=true;requestAnimationFrame(function(){
    var sy=window.scrollY;
    nav.classList.toggle('scrolled',sy>10);
    if(navContent){var a=Math.min(sy/300,1);navContent.style.setProperty('--nav-a',a.toFixed(3))}
    var h=document.documentElement.scrollHeight-window.innerHeight;
    if(prog)prog.style.transform='scaleX('+(h>0?sy/h:0)+')';
    tick=false;
  })},{passive:true});
  document.getElementById('burgerBtn').addEventListener('click',function(){nav.classList.toggle('open')});

  /* ═══════════════ HERO LETTER-BY-LETTER ═══════════════ */
  var h1=document.getElementById('sH1');
  if(h1&&!rm){
    var raw=h1.innerHTML,t='',buf='',inTag=false;
    for(var i=0;i<raw.length;i++){var c=raw[i];if(c==='<'){inTag=true;buf+=c;continue}if(c==='>'){inTag=false;buf+=c;t+=buf;buf='';continue}if(inTag){buf+=c;continue}if(c===' '||c==='\n'){t+=' ';continue}t+='<span class="_letter">'+c+'</span>';}
    h1.innerHTML=t;
    setTimeout(function(){h1.querySelectorAll('._letter').forEach(function(el,i){setTimeout(function(){el.classList.add('vis')},200+i*45)})},120);
  }
  setTimeout(function(){
    ['sTag','sDesc','sActs','sMet','sBar'].forEach(function(id){var e=document.getElementById(id);if(e)e.classList.add('vis')});
    if(h1)h1.classList.add('vis');
  },60);

  /* ═══════════════ BLUR-ON-SCROLL REVEAL ═══════════════ */
  if(!rm){
    var revealObs=new IntersectionObserver(function(entries){
      entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('vis');revealObs.unobserve(e.target)}});
    },{threshold:0.08,rootMargin:'0px 0px -60px 0px'});
    document.querySelectorAll('.reveal').forEach(function(el){revealObs.observe(el)});
  }

  /* ═══════════════ STAGGER CARDS ═══════════════ */
  var obs=new IntersectionObserver(function(entries){
    entries.forEach(function(e){if(e.isIntersecting){
      e.target.classList.add('vis');
      var s=e.target.getAttribute('data-stagger');
      if(s!==null)e.target.style.transitionDelay=(parseInt(s)*100)+'ms';
      obs.unobserve(e.target);
    }});
  },{threshold:0.06,rootMargin:'0px 0px -40px 0px'});
  document.querySelectorAll('.card,.capsule,.gui-window-wrapper,.screen__frame,.prompt__box').forEach(function(el){obs.observe(el)});

  /* ═══════════════ 3D TILT + GLOW TRACKING ═══════════════ */
  if(!('ontouchstart' in window)){
    document.querySelectorAll('.card').forEach(function(c){
      c.addEventListener('mousemove',function(e){
        var r=c.getBoundingClientRect();
        var x=e.clientX-r.left,y=e.clientY-r.top;
        var px=(x/r.width)*100,py=(y/r.height)*100;
        c.style.setProperty('--gx',px+'%');
        c.style.setProperty('--gy',py+'%');
        c.style.transform='rotateY('+(px-50)/5+'deg) rotateX('+(50-py)/7+'deg) translateZ(8px) scale(1.02)';
        c.style.transition='transform .12s cubic-bezier(.22,1,.36,1)';
      });
      c.addEventListener('mouseleave',function(){c.style.transform='';c.style.transition='transform .6s cubic-bezier(.22,1,.36,1)'});
    });
  }

  /* ═══════════════ MAGNETIC BUTTONS ═══════════════ */
  if(!('ontouchstart' in window)){
    document.querySelectorAll('.mag').forEach(function(b){
      b.addEventListener('mousemove',function(e){
        var r=b.getBoundingClientRect();
        var x=(e.clientX-r.left-r.width/2)*0.3;
        var y=(e.clientY-r.top-r.height/2)*0.3;
        b.style.transform='translate('+x+'px,'+y+'px) scale(1.04)';
        b.style.transition='transform .2s cubic-bezier(.22,1,.36,1)';
      });
      b.addEventListener('mouseleave',function(){b.style.transform='';b.style.transition='transform .5s cubic-bezier(.22,1,.36,1)'});
    });
  }

  /* ═══════════════ PARALLAX HERO ═══════════════ */
  var heroGlow=document.querySelector('.stage__glow');
  var hero=document.querySelector('.stage');
  var heroTick=false;
  window.addEventListener('scroll',function(){if(heroTick)return;heroTick=true;requestAnimationFrame(function(){
    var sy=window.scrollY,h=window.innerHeight;
    if(sy<h){
      var p=sy/h;
      if(heroGlow){heroGlow.style.transform='translate(-50%,'+(-50+p*40)+'%) scale('+(1+p*0.3)+')';heroGlow.style.opacity=Math.max(0,0.7-p*0.8)}
      if(hero){var tp=Math.min(sy*0.35,h*0.5);var sc=1-sy/h*0.06;var op=1-sy/h*0.55;hero.style.transform='translateY('+tp+'px) scale('+sc+')';hero.style.opacity=op<0?0:op}
    }
    heroTick=false;
  })},{passive:true});


  /* ═══════════════ ANIMATED COUNTERS ═══════════════ */
  var counters=document.querySelectorAll('[data-count]'),countDone=false;
  var countObs=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting&&!countDone){countDone=true;counters.forEach(function(el){var target=parseInt(el.getAttribute('data-count'),10)||0,start=performance.now();(function tick(now){var p=Math.min((now-start)/1200,1),ez=1-Math.pow(1-p,3);el.textContent=Math.floor(ez*target).toLocaleString();if(p<1)requestAnimationFrame(tick);else el.textContent=target.toLocaleString()})(start)})}})},{threshold:0.3});
  var metEl=document.getElementById('sMet');if(metEl)countObs.observe(metEl);

  /* ═══════════════ CLICKGUI TABS ═══════════════ */
  document.querySelectorAll('.gui-tab-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
      document.querySelectorAll('.gui-tab-btn').forEach(function(b){b.classList.remove('active')});
      document.querySelectorAll('.gui-tab-panel').forEach(function(p){p.classList.remove('active')});
      btn.classList.add('active');
      var panel=document.getElementById('tab-'+btn.getAttribute('data-tab'));
      if(panel)panel.classList.add('active');
    });
  });

  /* ═══════════════ CLICKGUI SLIDER VALUES ═══════════════ */
  document.querySelectorAll('.cyber-slider').forEach(function(slider){
    var badge=slider.closest('.module-setting')?.querySelector('.setting-val-badge');
    if(!badge)return;
    slider.addEventListener('input',function(){
      var v=parseFloat(slider.value);
      var unit=slider.getAttribute('data-unit')||'';
      if(String(v).indexOf('.')===-1){badge.textContent=v+unit}else{badge.textContent=v.toFixed(1)+unit}
    });
  });

  /* ═══════════════ SCROLL-TO-TOP ═══════════════ */
  var topBtn=document.querySelector('.top');
  if(topBtn){
    window.addEventListener('scroll',function(){topBtn.classList.toggle('vis',window.scrollY>400)},{passive:true});
    topBtn.addEventListener('click',function(){smoothScrollTo(0)});
  }


})();
setTimeout(function(){document.querySelectorAll('.reveal:not(.vis)').forEach(function(el){el.classList.add('vis')});document.querySelectorAll('.card,.capsule,.gui-window-wrapper,.screen__frame,.prompt__box:not(.vis)').forEach(function(el){el.classList.add('vis')})},2000);
</script>
<script src="/devtools.js"></script>
<script src="/lang.js"></script>
</body>
</html>
