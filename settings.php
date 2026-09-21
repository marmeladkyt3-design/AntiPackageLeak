<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';

date_default_timezone_set('Europe/Moscow');

session_start();
checkMaintenance();

if (!isLoggedIn()) {
    redirect('/login');
}

// Проверка CSRF-токена для всех POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(403);
    die('Invalid CSRF token');
}

$user = getUser($pdo, $_SESSION['user_id']);

if (!$user) {
    session_destroy();
    redirect('/login');
}

// Таблица настроек лаунчера пользователя
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `user_settings` (
        `user_id` int(11) NOT NULL,
        `ram_mb` int(11) NOT NULL DEFAULT 4096,
        `lang` varchar(5) NOT NULL DEFAULT 'en',
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

// Сохранение настроек
$settings = ['ram_mb' => 4096, 'lang' => 'en'];
try {
    $stmt = $pdo->prepare("SELECT ram_mb, lang FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();
    if ($row) {
        $settings['ram_mb'] = (int)$row['ram_mb'];
        $settings['lang'] = $row['lang'];
    }
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings') {
    $ram = (int)($_POST['ram_mb'] ?? 4096);
    $ram = max(256, min(16384, intval(round($ram / 100) * 100)));
    $lang = in_array($_POST['lang'] ?? 'en', ['en', 'ru'], true) ? $_POST['lang'] : 'en';

    try {
        $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, ram_mb, lang) VALUES (?, ?, ?)
                               ON DUPLICATE KEY UPDATE ram_mb = VALUES(ram_mb), lang = VALUES(lang)");
        $stmt->execute([$user['id'], $ram, $lang]);
        $settings['ram_mb'] = $ram;
        $settings['lang'] = $lang;
        $_SESSION['msg'] = 'Settings saved';
        $_SESSION['msg_type'] = 'success';
    } catch (PDOException $e) {
        $_SESSION['msg'] = 'Failed to save settings';
        $_SESSION['msg_type'] = 'error';
    }
    redirect('/settings');
}

if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'];
    unset($_SESSION['msg']);
    unset($_SESSION['msg_type']);
}

$avatar_url = $user['avatar_url'] ?? null;
$site_name = htmlspecialchars($SITE_NAME ?? 'Placeholder');
?>

<!DOCTYPE html>
<html lang="en">
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
    <title>Settings — <?php echo htmlspecialchars($site_name); ?></title>
<style>
*,*::before,*::after{-webkit-user-select:none!important;-moz-user-select:none!important;-ms-user-select:none!important;user-select:none!important}
input,textarea{-webkit-user-select:text!important;-moz-user-select:text!important;-ms-user-select:text!important;user-select:text!important}
.shader-orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.shader-orbs .orb{position:absolute;border-radius:50%;filter:blur(90px);mix-blend-mode:screen;will-change:transform}
.orb--1{width:380px;height:380px;background:radial-gradient(circle,rgba(90,99,232,0.45) 0%,transparent 70%);top:-10%;left:-8%;animation:o1 20s ease-in-out infinite alternate}
.orb--2{width:300px;height:300px;background:radial-gradient(circle,rgba(139,92,246,0.38) 0%,transparent 70%);bottom:-12%;right:-6%;animation:o2 24s ease-in-out infinite alternate}
.orb--3{width:240px;height:240px;background:radial-gradient(circle,rgba(168,85,247,0.32) 0%,transparent 70%);top:35%;left:50%;animation:o3 17s ease-in-out infinite alternate}
.orb--4{width:180px;height:180px;background:radial-gradient(circle,rgba(74,82,208,0.28) 0%,transparent 70%);top:12%;right:18%;animation:o4 22s ease-in-out infinite alternate}
@keyframes o1{0%{transform:translate(0,0) scale(1)}33%{transform:translate(70px,50px) scale(1.08)}66%{transform:translate(-40px,90px) scale(0.94)}100%{transform:translate(50px,30px) scale(1.04)}}
@keyframes o2{0%{transform:translate(0,0) scale(1)}33%{transform:translate(-60px,-35px) scale(1.06)}66%{transform:translate(50px,-70px) scale(0.9)}100%{transform:translate(-25px,-45px) scale(1)}}
@keyframes o3{0%{transform:translate(0,0) scale(1)}50%{transform:translate(-80px,35px) scale(1.12)}100%{transform:translate(35px,-55px) scale(0.88)}}
@keyframes o4{0%{transform:translate(0,0) scale(0.9)}40%{transform:translate(45px,55px) scale(1.08)}100%{transform:translate(-55px,-25px) scale(0.94)}}</style>
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

        /* ============================================================
           NEBULA — фирменные цвета, чистый премиум
           ============================================================ */

        :root {
            --bg: #08080f;
            --bg2: #0c0c17;
            --panel: rgba(255, 255, 255, 0.028);
            --panel-h: rgba(255, 255, 255, 0.045);
            --line: rgba(255, 255, 255, 0.07);
            --line2: rgba(255, 255, 255, 0.13);

            --indigo: #6366f1;
            --purple: #8b5cf6;
            --violet: #a78bfa;
            --pink: #e879f9;

            --grad: linear-gradient(135deg, #6366f1, #8b5cf6 55%, #a78bfa);
            --grad-soft: linear-gradient(135deg, rgba(var(--color-accent-rgb),0.14), rgba(var(--color-accent-rgb),0.10));

            --ink: #f1f2fa;
            --ink2: #b4bacd;
            --dim: #7d86a3;
            --faint: #4c5470;

            --r: 18px;
            --r-sm: 12px;
            --r-lg: 26px;

            --shadow: 0 24px 70px -24px rgba(0, 0, 0, 0.65);
            --glow: 0 0 0 1px rgba(var(--color-accent-rgb),0.16), 0 22px 60px -22px rgba(var(--color-accent-rgb),0.35);

            /* Карта палитры кабинета */
            --bg-900: #08080f;
            --bg-800: #0c0c17;
            --bg-700: #101019;
            --bg-600: #14141f;
            --bg-500: #181826;
            --outline-900: rgba(255,255,255,0.07);
            --outline-700: rgba(255,255,255,0.12);
            --text-100: #f1f2fa;
            --text-200: #e2e5f2;
            --text-300: #c9cddd;
            --text-500: #8d94ad;
            --text-600: #7d86a3;
            --text-700: #5d6680;
            --accent-color: #6366f1;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--ink2);
            line-height: 1.65;
            overflow-x: hidden;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        ::-webkit-scrollbar { width: 9px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: #232a44; border-radius: 9px; border: 2px solid var(--bg); }
        ::-webkit-scrollbar-thumb:hover { background: #3b4470; }

        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; background: none; border: none; cursor: pointer; color: inherit; }
        img { max-width: 100%; display: block; }

        .container { width: min(100% - 44px, 1180px); margin: 0 auto; }

        .grad {
            background: var(--grad);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }

        /* ===== ФОН ===== */
        .bg-fx {
            position: fixed; inset: 0; z-index: -1; pointer-events: none; overflow: hidden;
            background-image: url('assets/img/background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .bg-fx::before {
            content: ""; position: absolute; inset: 0;
            background: rgba(8, 8, 15, 0.5);
            -webkit-backdrop-filter: blur(16px) saturate(1.2);
            backdrop-filter: blur(16px) saturate(1.2);
        }

        .bg-fx .halo {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .bg-fx .halo--a {
            width: 640px; height: 640px;
            background: radial-gradient(circle at center, rgba(99, 102, 241, 0.13) 0%, transparent 62%);
            top: -260px; right: -160px;
        }

        .bg-fx .halo--b {
            width: 560px; height: 560px;
            background: radial-gradient(circle at center, rgba(139, 92, 246, 0.10) 0%, transparent 62%);
            bottom: -240px; left: -180px;
        }

        .bg-fx .halo--c {
            width: 400px; height: 400px;
            background: radial-gradient(circle at center, rgba(232, 121, 249, 0.05) 0%, transparent 62%);
            top: 42%; left: 62%;
        }

        .bg-fx .veil {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.35'/%3E%3C/svg%3E");
            opacity: 0.02;
        }

        /* ===== НАВБАР ===== */
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

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-size: 13.5px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 13px;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--grad);
            color: #fff;
            box-shadow: 0 12px 34px -12px rgba(99, 102, 241, 0.65);
        }

        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 18px 44px -12px rgba(139, 92, 246, 0.7); }

        .btn-ghost {
            color: var(--ink2);
            border: 1px solid var(--line2);
            background: var(--panel);
        }

        .btn-ghost:hover { color: var(--ink); border-color: rgba(var(--color-accent-rgb),0.45); background: var(--panel-h); }

        .btn-lg { padding: 13px 26px; font-size: 14.5px; border-radius: 15px; }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 14px 4px 4px;
            border: 1px solid var(--line2);
            border-radius: 100px;
            background: var(--panel);
            transition: border-color 0.2s ease;
        }

        .user-chip:hover { border-color: rgba(var(--color-accent-rgb),0.5); }

        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--grad);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            overflow: hidden;
            flex-shrink: 0;
        }

        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .user-chip .uname { font-size: 13px; font-weight: 600; color: var(--ink); max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ===== HERO ===== */
        .hero { position: relative; padding: 76px 0 46px; }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.06fr 0.94fr;
            gap: 60px;
            align-items: center;
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            border-radius: 100px;
            border: 1px solid rgba(139, 92, 246, 0.3);
            background: rgba(139, 92, 246, 0.07);
            font-size: 12.5px;
            font-weight: 600;
            color: var(--violet);
            margin-bottom: 26px;
            letter-spacing: 0.01em;
        }

        .kicker .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--grad); box-shadow: 0 0 12px rgba(var(--color-accent-rgb),0.8); }

        .hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: clamp(36px, 4.6vw, 62px);
            font-weight: 700;
            line-height: 1.08;
            letter-spacing: -0.03em;
            color: var(--ink);
            margin-bottom: 20px;
        }

        .hero-desc {
            font-size: 15.5px;
            line-height: 1.8;
            color: var(--dim);
            max-width: 520px;
            margin-bottom: 32px;
        }

        .hero-desc b { color: var(--ink2); font-weight: 600; }

        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 40px; }

        .hero-stats {
            display: flex;
            align-items: center;
            gap: 0;
            max-width: 520px;
            border-top: 1px solid var(--line);
            padding-top: 26px;
        }

        .hs-item { flex: 1; padding: 0 22px; }
        .hs-item:first-child { padding-left: 0; }
        .hs-item:last-child { padding-right: 0; }
        .hs-item + .hs-item { border-left: 1px solid var(--line); }

        .hs-item .n {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
        }

        .hs-item .n .grad { font-size: 24px; }

        .hs-item .l { font-size: 12px; color: var(--faint); margin-top: 3px; }

        /* --- Псевдо-лаунчер --- */
        .hero-visual { position: relative; }

        .launcher {
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.015));
            border: 1px solid var(--line2);
            border-radius: var(--r-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .launcher::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 220px;
            background: radial-gradient(60% 100% at 50% 0%, rgba(139, 92, 246, 0.16), transparent 75%);
            pointer-events: none;
        }

        .launcher-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 15px 18px;
            border-bottom: 1px solid var(--line);
        }

        .launcher-bar .l-dot { width: 11px; height: 11px; border-radius: 50%; background: #31375c; }
        .launcher-bar .l-dot:nth-child(2) { background: #3d3366; }
        .launcher-bar .l-dot:nth-child(3) { background: #40306e; }
        .launcher-bar .l-dot:first-child { background: #4a3c8f; }

        .launcher-bar .l-title {
            margin-left: 10px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: var(--faint);
        }

        .launcher-body { padding: 26px 26px 28px; position: relative; }

        .launcher-head { display: flex; align-items: center; gap: 15px; margin-bottom: 22px; }

        .launcher-head .big {
            width: 54px; height: 54px;
            border-radius: 16px;
            background: var(--grad);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            box-shadow: 0 14px 38px -10px rgba(99, 102, 241, 0.7);
        }

        .launcher-head .name {
            font-family: 'Sora', sans-serif;
            font-size: 19px;
            font-weight: 700;
            color: var(--ink);
        }

        .launcher-head .status {
            font-size: 12px;
            color: var(--violet);
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 2px;
        }

        .launcher-head .status .dot { width: 7px; height: 7px; border-radius: 50%; background: #34d399; box-shadow: 0 0 10px rgba(52,211,153,0.8); }

        .versions { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 22px; }

        .v-chip {
            font-size: 12.5px;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 100px;
            border: 1px solid var(--line2);
            background: rgba(255, 255, 255, 0.02);
            color: var(--dim);
        }

        .v-chip.on { border-color: rgba(139, 92, 246, 0.5); background: rgba(139, 92, 246, 0.1); color: var(--violet); }

        .dl-progress { margin-bottom: 22px; }

        .dl-progress .hd {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--faint);
            margin-bottom: 9px;
        }

        .dl-progress .hd b { color: var(--ink2); font-weight: 600; }

        .dl-bar { height: 9px; border-radius: 100px; background: rgba(255, 255, 255, 0.06); overflow: hidden; }

        .dl-bar .fill {
            height: 100%;
            width: 72%;
            border-radius: 100px;
            background: var(--grad);
            transition: width 0.4s ease;
            box-shadow: 0 0 14px rgba(139, 92, 246, 0.6);
        }

        .launcher-play {
            width: 100%;
            padding: 15px;
            border-radius: 14px;
            background: var(--grad);
            color: #fff;
            font-size: 14.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 16px 40px -14px rgba(139, 92, 246, 0.7);
            transition: all 0.25s ease;
        }

        .launcher-play:hover { transform: translateY(-2px); box-shadow: 0 22px 52px -14px rgba(139, 92, 246, 0.85); }

        .float-chip {
            position: absolute;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 17px;
            border-radius: 14px;
            border: 1px solid var(--line2);
            background: rgba(12, 12, 23, 0.94);
            font-size: 12.5px;
            font-weight: 600;
            color: var(--ink2);
            box-shadow: var(--shadow);
        }

        .float-chip i { color: var(--violet); }

        .float-chip--1 { top: -18px; left: -22px; }
        .float-chip--2 { bottom: -16px; right: -18px; }

        /* ===== СЕКЦИИ ===== */
        .section { padding: 78px 0 0; content-visibility: auto; contain-intrinsic-size: auto 560px; }

        .sec-head { max-width: 640px; margin-bottom: 40px; }

        .sec-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--violet);
            margin-bottom: 14px;
        }

        .sec-label::before { content: ''; width: 26px; height: 2px; background: var(--grad); border-radius: 2px; }

        .sec-head h2 {
            font-family: 'Sora', sans-serif;
            font-size: clamp(26px, 3.2vw, 40px);
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ink);
            line-height: 1.15;
        }

        .sec-head p { font-size: 14.5px; color: var(--dim); margin-top: 12px; }

        /* ===== ФИЧИ ===== */
        .feat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

        .feat {
            position: relative;
            padding: 28px 26px;
            border-radius: var(--r);
            border: 1px solid var(--line);
            background: var(--panel);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .feat::after {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 2px;
            background: var(--grad);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feat:hover { transform: translateY(-5px); border-color: rgba(var(--color-accent-rgb),0.35); background: var(--panel-h); box-shadow: var(--glow); }
        .feat:hover::after { opacity: 1; }

        .feat .ico {
            width: 46px; height: 46px;
            border-radius: 13px;
            background: var(--grad-soft);
            border: 1px solid rgba(139, 92, 246, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--violet);
            font-size: 17px;
            margin-bottom: 18px;
        }

        .feat .num {
            position: absolute;
            top: 24px; right: 26px;
            font-family: 'Sora', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: var(--faint);
        }

        .feat h3 {
            font-family: 'Sora', sans-serif;
            font-size: 16.5px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 9px;
            letter-spacing: -0.01em;
        }

        .feat p { font-size: 13.5px; color: var(--dim); line-height: 1.7; }

        /* ===== ОТЗЫВЫ ===== */
        .reviews-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

        .rvw {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 26px;
            border-radius: var(--r);
            border: 1px solid var(--line);
            background: var(--panel);
            transition: all 0.3s ease;
        }

        .rvw:hover { transform: translateY(-5px); border-color: rgba(var(--color-accent-rgb),0.35); background: var(--panel-h); box-shadow: var(--glow); }

        .rvw-top { display: flex; align-items: center; gap: 13px; }

        .rvw-av {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: var(--grad);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 700;
            overflow: hidden;
            flex-shrink: 0;
        }

        .rvw-av img { width: 100%; height: 100%; object-fit: cover; }

        .rvw-who { flex: 1; min-width: 0; }

        .rvw-who .name { font-size: 13.5px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .rvw-stars { color: var(--violet); font-size: 10.5px; letter-spacing: 2px; margin-top: 3px; }

        .rvw-stars .empty { color: var(--faint); }

        .rvw-badge {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--violet);
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.22);
            padding: 5px 10px;
            border-radius: 100px;
            white-space: nowrap;
        }

        .rvw-text { font-size: 13.5px; color: var(--ink2); line-height: 1.75; flex: 1; }

        .rvw-date { font-size: 11.5px; color: var(--faint); border-top: 1px solid var(--line); padding-top: 14px; display: flex; align-items: center; gap: 8px; }

        /* ===== ВИДЕО ===== */
        .vid {
            position: relative;
            border-radius: var(--r-lg);
            overflow: hidden;
            border: 1px solid var(--line2);
            box-shadow: var(--shadow);
            background: #000;
        }

        .vid::before {
            content: '';
            position: absolute;
            inset: -1px;
            z-index: 1;
            pointer-events: none;
            border-radius: inherit;
            box-shadow: inset 0 0 0 1px rgba(139, 92, 246, 0.15);
        }

        .vid iframe { width: 100%; height: 500px; border: none; display: block; }

        /* ===== CTA ===== */
        .cta-wrap { padding: 90px 0 20px; content-visibility: auto; contain-intrinsic-size: auto 240px; }

        .cta {
            position: relative;
            overflow: hidden;
            border-radius: var(--r-lg);
            border: 1px solid rgba(139, 92, 246, 0.35);
            background: linear-gradient(135deg, rgba(var(--color-accent-rgb),0.16), rgba(var(--color-accent-rgb),0.12));
            padding: 56px 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            flex-wrap: wrap;
        }

        .cta::before {
            content: '';
            position: absolute;
            width: 480px; height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.22), transparent 70%);
            top: -260px; left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
        }

        .cta h2 {
            position: relative;
            font-family: 'Sora', sans-serif;
            font-size: clamp(24px, 3vw, 34px);
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.02em;
            margin-bottom: 10px;
        }

        .cta p { position: relative; font-size: 14px; color: var(--dim); }

        .cta .btn { position: relative; }

        /* ===== REVEAL ===== */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.72s cubic-bezier(0.22, 1, 0.36, 1), transform 0.72s cubic-bezier(0.22, 1, 0.36, 1); will-change: opacity, transform; }
        .reveal.in { opacity: 1; transform: translateY(0); }
        .d-1 { transition-delay: 0.07s; }
        .d-2 { transition-delay: 0.14s; }
        .d-3 { transition-delay: 0.21s; }
        .d-4 { transition-delay: 0.28s; }

        /* ===== АДАПТИВ ===== */
        @media (max-width: 1024px) {
            .hero-grid { grid-template-columns: 1fr; gap: 56px; }
            .hero-visual { max-width: 560px; margin: 0 auto; width: 100%; }
            .feat-grid, .reviews-grid { grid-template-columns: repeat(2, 1fr); }
            .vid iframe { height: 400px; }
        }

        @media (max-width: 768px) {
            .container { width: min(100% - 30px, 1180px); }
            .feat-grid, .reviews-grid { grid-template-columns: 1fr; }
            .vid iframe { height: 220px; }
            .hero { padding-top: 52px; }
            .hero-stats { flex-direction: column; align-items: stretch; gap: 16px; border-top: none; padding-top: 0; }
            .hs-item { padding: 16px 0 0 !important; border-top: 1px solid var(--line); }
            .hs-item:first-child { padding-top: 0 !important; border-top: none; }
            .hs-item + .hs-item { border-left: none; }
            .cta { padding: 42px 28px; }
            .float-chip--1 { left: -4px; }
            .float-chip--2 { right: -4px; }
        }

        @media (max-width: 480px) {
            .hero-actions .btn { flex: 1; }
        }
        /* ===== PROFILE PAGE ===== */
        .page { padding-top: 48px; padding-bottom: 80px; }
        .page-head { max-width: 640px; margin-bottom: 44px; }
        .page-head .icon {
            width: 64px; height: 64px; border-radius: 19px;
            background: var(--grad); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 25px; margin-bottom: 20px;
            box-shadow: 0 22px 50px -20px rgba(var(--color-accent-rgb),0.65);
        }
        .page-head h1 {
            font-family: 'Sora', sans-serif;
            font-size: clamp(28px, 3.4vw, 40px); font-weight: 700;
            letter-spacing: -0.02em; color: var(--ink);
            margin-bottom: 12px; line-height: 1.15;
        }
        .page-head p { font-size: 14.5px; color: var(--dim); }
        .page-head.center { text-align: center; margin-left: auto; margin-right: auto; }
        .page-head.center .icon { margin-left: auto; margin-right: auto; }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            padding: 28px;
            position: relative;
        }
        .card-title {
            display: flex; align-items: center; gap: 11px;
            font-family: 'Sora', sans-serif;
            font-size: 16px; font-weight: 600; color: var(--ink);
            margin-bottom: 22px;
        }
        .card-title i { color: var(--violet); font-size: 15px; }

        .form-control {
            width: 100%; padding: 12px 15px;
            border-radius: 13px;
            border: 1px solid var(--line2);
            background: rgba(10, 10, 20, 0.6);
            color: var(--ink);
            font-family: 'Inter', sans-serif;
            font-size: 14px; outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-control::placeholder { color: var(--faint); }
        .form-control:focus {
            border-color: rgba(var(--color-accent-rgb),0.6);
            box-shadow: 0 0 0 3px rgba(var(--color-accent-rgb),0.15);
        }
        textarea.form-control { resize: vertical; min-height: 110px; }
        .form-group { margin-bottom: 14px; }
        .btn-block { width: 100%; }

        .alert {
            display: flex; align-items: center; gap: 11px;
            padding: 13px 17px;
            border-radius: 13px;
            font-size: 13.5px;
            margin-bottom: 18px;
        }
        .alert i { flex-shrink: 0; }
        .alert-success { color: #6ee7b7; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); }
        .alert-error { color: #fda4af; background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.25); }

        .chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px; border-radius: 100px;
            font-size: 11px; font-weight: 600;
            border: 1px solid var(--line); color: var(--ink2); background: var(--panel);
        }
        .chip-on { color: #6ee7b7; background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.3); }
        .chip-off { color: var(--faint); }

        /* layout */
        .profile-layout { display: grid; grid-template-columns: 1fr 1.8fr; gap: 18px; align-items: start; }
        .profile-ident { position: sticky; top: 88px; display: flex; flex-direction: column; }
        .avatar-area { text-align: center; display: flex; flex-direction: column; align-items: center; position: relative; }
        .avatar-toggle { position: relative; cursor: pointer; }
        .avatar-ring {
            width: 88px; height: 88px; border-radius: 24px;
            background: var(--grad); padding: 3px;
            box-shadow: 0 18px 44px -18px rgba(var(--color-accent-rgb),0.55);
            transition: transform 0.25s ease;
        }
        .avatar-ring > * {
            width: 100%; height: 100%; border-radius: 21px;
            object-fit: cover;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: 34px;
            background: #0b0b15;
        }
        .avatar-toggle:hover .avatar-ring { transform: translateY(-2px); }
        .avatar-edit {
            position: absolute; right: -4px; bottom: -4px;
            width: 30px; height: 30px; border-radius: 10px;
            background: var(--grad); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; border: 3px solid var(--bg);
        }
        .profile-name { font-family: 'Sora', sans-serif; font-size: 21px; font-weight: 700; color: var(--ink); margin-top: 14px; }
        .profile-id { display: inline-flex; align-items: center; gap: 7px; margin-top: 6px; font-size: 12px; font-weight: 500; color: var(--faint); letter-spacing: 0.02em; }
        .profile-id i { color: var(--violet); font-size: 10px; }
        .role-pill {
            display: inline-flex; align-items: center; gap: 7px;
            margin-top: 12px; padding: 5px 14px; border-radius: 100px;
            font-size: 12px; font-weight: 600;
        }
        .role-user { color: var(--ink2); background: var(--panel); border: 1px solid var(--line); }
        .role-premium { color: #fff; background: var(--grad); }
        .role-admin { color: #fca5a5; background: rgba(244,63,94,0.12); border: 1px solid rgba(244,63,94,0.3); }

        .avatar-panel {
            position: absolute; top: 102px; left: 50%;
            transform: translateX(-50%) translateY(6px);
            width: min(300px, calc(100vw - 40px));
            background: var(--bg2);
            border: 1px solid var(--line2);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 18px;
            opacity: 0; visibility: hidden;
            transition: all 0.22s ease;
            z-index: 60; text-align: left;
        }
        .avatar-panel.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
        .panel-title { font-size: 12.5px; font-weight: 600; color: var(--dim); margin-bottom: 12px; }
        .avatar-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
        .avatar-option {
            aspect-ratio: 1; border-radius: 11px;
            background: var(--panel); border: 1px solid var(--line);
            cursor: pointer; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            color: var(--faint); font-size: 14px; font-weight: 700;
            transition: border-color 0.18s ease, transform 0.18s ease;
        }
        .avatar-option img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-option:hover { border-color: rgba(var(--color-accent-rgb),0.5); transform: scale(1.06); }
        .avatar-option.active { border-color: var(--violet); box-shadow: 0 0 0 2px rgba(var(--color-accent-rgb),0.25); }
        .avatar-none { grid-column: 1/-1; text-align: center; color: var(--faint); font-size: 11px; padding: 8px 0; }
        .upload-row { margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--line); display: flex; justify-content: center; }
        .upload-row label {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 12.5px; color: var(--dim); cursor: pointer; transition: color 0.2s ease;
        }
        .upload-row label:hover { color: var(--violet); }

        .sub-box {
            margin-top: 26px;
            border-radius: 18px; padding: 18px 20px;
            border: 1px solid rgba(var(--color-accent-rgb),0.25);
            background: linear-gradient(135deg, rgba(var(--color-accent-rgb),0.12), rgba(var(--color-accent-rgb),0.05));
            transition: border-color 0.25s ease;
        }
        .sub-box.off { background: var(--panel); border-color: var(--line); }
        .sub-box.on { box-shadow: 0 26px 60px -26px rgba(var(--color-accent-rgb),0.55); }
        .sub-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .sub-label { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--dim); }
        .sub-label i { color: var(--violet); }
        .sub-state { font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 100px; }
        .sub-box.on .sub-state { color: #6ee7b7; background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3); }
        .sub-box.off .sub-state { color: var(--faint); background: var(--panel); border: 1px solid var(--line); }
        .sub-days { font-family: 'Sora', sans-serif; font-size: 30px; font-weight: 800; letter-spacing: -0.02em; color: var(--ink); line-height: 1; }
        .sub-days span { font-size: 13px; font-weight: 600; color: var(--faint); margin-left: 4px; }
        .sub-date { font-size: 12.5px; color: var(--ink2); margin-top: 6px; }
        .sub-bar { margin-top: 14px; height: 6px; border-radius: 100px; background: rgba(255,255,255,0.07); overflow: hidden; }
        .sub-bar .fill { height: 100%; border-radius: 100px; background: var(--grad); }

        .profile-divider { height: 1px; background: var(--line); margin: 24px 0; }
        .info-list { display: grid; gap: 15px; }
        .info-item { display: flex; align-items: center; gap: 12px; }
        .info-item .ic {
            width: 36px; height: 36px; flex-shrink: 0;
            border-radius: 11px; background: var(--grad-soft); color: var(--violet);
            display: flex; align-items: center; justify-content: center; font-size: 13px;
        }
        .info-item .meta { min-width: 0; }
        .info-item .meta .label { font-size: 10.5px; color: var(--faint); text-transform: uppercase; letter-spacing: 0.06em; }
        .info-item .meta .val { font-size: 13.5px; color: var(--ink); font-weight: 600; margin-top: 1px; word-break: break-word; }

        .mini-spec { background: var(--panel); border: 1px solid var(--line); border-radius: 16px; padding: 18px; margin-top: 4px; }
        .mini-spec .ttl { display: flex; align-items: center; gap: 9px; font-family: 'Sora', sans-serif; font-size: 13.5px; font-weight: 600; color: var(--ink); margin-bottom: 12px; }
        .mini-spec .ttl i { color: var(--violet); }
        .mini-spec .row { display: flex; justify-content: space-between; font-size: 12.5px; padding: 7px 0; color: var(--ink2); border-bottom: 1px solid var(--line); }
        .mini-spec .row:last-child { border-bottom: none; }
        .mini-spec .row b { color: var(--ink); font-weight: 600; font-family: 'Sora', sans-serif; font-size: 12px; }
        .launcher-btn { margin-top: 16px; }
        .launcher-help { display: flex; align-items: center; justify-content: center; gap: 5px; margin-top: 12px; font-size: 12px; color: var(--faint); }
        .launcher-help a { color: var(--violet); font-weight: 600; }

        
        .support-link i { color: var(--violet); }
        .support-link a { color: var(--violet); font-weight: 600; }

        .profile-right { display: grid; gap: 18px; min-width: 0; }
        .license-row { display: flex; align-items: center; gap: 10px; }
        .license-row .form-control { flex: 1; min-width: 0; }
        .license-row .btn { flex-shrink: 0; min-width: 170px; justify-content: center; }

        .act-card { margin-top: 6px; }
        .account-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .account-block { display: flex; align-items: center; gap: 12px; background: var(--panel); border: 1px solid var(--line); border-radius: 14px; padding: 15px 16px; min-width: 0; transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
        .account-block:hover { border-color: rgba(var(--color-accent-rgb),0.45); transform: translateY(-2px); box-shadow: 0 14px 34px -16px rgba(var(--color-accent-rgb),0.4); }
        .account-block .ic {
            width: 38px; height: 38px; flex-shrink: 0;
            border-radius: 11px; background: var(--grad-soft); color: var(--violet);
            display: flex; align-items: center; justify-content: center; font-size: 14px;
        }
        .account-block .meta { min-width: 0; }
        .account-block .meta .label { font-size: 10.5px; color: var(--faint); text-transform: uppercase; letter-spacing: 0.06em; }
        .account-block .meta .val { font-size: 13.5px; color: var(--ink); font-weight: 600; margin-top: 2px; word-break: break-word; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .account-block.wide { grid-column: 1 / -1; }

        .cols { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; min-width: 0; }
        
        .launcher-actions { display: flex; gap: 10px; margin-top: 16px; }
        .launcher-actions .btn { flex: 1; justify-content: center; padding: 11px 14px; font-size: 13px; }
        .launcher-ver { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--dim); margin-top: 14px; }
        .launcher-ver i { color: var(--violet); font-size: 11px; }
        .launcher-ver b { color: var(--ink); font-family: 'Sora', sans-serif; font-weight: 600; }
        .launcher-dl { position: relative; overflow: hidden; }
        .launcher-dl::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.22) 50%, transparent 60%);
            transform: translateX(-120%);
            transition: transform 0.55s ease;
            pointer-events: none;
        }
        .launcher-dl:hover::after { transform: translateX(120%); }
        .launcher-dl:hover { transform: translateY(-2px); box-shadow: 0 18px 44px -12px rgba(var(--color-accent-rgb),0.7); }
        .launcher-lock { display: flex; align-items: center; justify-content: center; gap: 9px; margin-top: 16px; padding: 13px 15px; border-radius: 13px; font-size: 12.5px; color: var(--dim); background: var(--panel); border: 1px dashed var(--line2); text-align: center; }
        .launcher-lock i { color: var(--violet); }
        .setting-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
        .setting-block { min-width: 0; }
        .setting-block + .setting-block { border-left: 1px solid var(--line); padding-left: 28px; }
        .setting-label { display: flex; align-items: center; gap: 9px; font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 600; color: var(--ink2); margin-bottom: 16px; }
        .setting-label i { color: var(--violet); font-size: 13px; }
        .hint { font-size: 12px; color: var(--faint); margin-top: 12px; display: flex; align-items: center; gap: 6px; }
        .hint i { color: var(--violet); font-size: 11px; }

        .stars-input { display: flex; gap: 6px; margin: 4px 0 16px; }
        .star-btn { background: none; border: none; font-size: 22px; color: var(--faint); cursor: pointer; transition: color 0.15s ease, transform 0.15s ease; padding: 2px; }
        .star-btn:hover, .star-btn.active { color: #fbbf24; transform: scale(1.12); }
        .review-state { display: flex; align-items: center; gap: 9px; font-size: 13px; padding: 12px 15px; border-radius: 12px; margin-bottom: 14px; }
        .review-state.pending { color: #fcd34d; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.25); }
        .review-state.approved { color: #6ee7b7; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); }
        .review-state.rejected { color: #fda4af; background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.25); }
        .sub-notice { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--dim); background: var(--panel); border: 1px solid var(--line); padding: 14px 16px; border-radius: 12px; }
        .sub-notice i { color: var(--violet); }
        .review-note { font-size: 11px; color: var(--faint); margin-top: 10px; display: flex; align-items: center; gap: 6px; }

        /* chat */
        .chat-card { padding: 0; overflow: hidden; margin-top: 28px; }
        .chat-top { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 18px 24px; border-bottom: 1px solid var(--line); flex-wrap: wrap; }
        .chat-title-el { display: flex; align-items: center; gap: 11px; font-family: 'Sora', sans-serif; font-size: 16px; font-weight: 600; color: var(--ink); }
        .chat-title-el i { color: var(--violet); }
        .chat-meta { display: flex; align-items: center; gap: 16px; }
        .chat-air { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; color: var(--dim); }
        .chat-air i { color: #34d399; animation: chatPulse 2.2s ease-in-out infinite; }
        @keyframes chatPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .chat-hint { font-size: 11.5px; color: var(--faint); }
        .chat-error { display: none; padding: 11px 24px; font-size: 12.5px; color: #fda4af; background: rgba(244,63,94,0.07); border-bottom: 1px solid var(--line); }
        .chat-error.show { display: block; }
        .chat-scroll { display: flex; flex-direction: column; gap: 2px; max-height: 430px; overflow-y: auto; padding: 16px 24px; }
        .chat-scroll::-webkit-scrollbar { width: 8px; }
        .chat-scroll::-webkit-scrollbar-track { background: transparent; }
        .chat-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 100px; }
        .chat-msg { display: flex; gap: 12px; padding: 13px 12px; border-radius: 14px; transition: background 0.15s ease; }
        .chat-msg:hover { background: rgba(255,255,255,0.02); }
        .chat-msg.me { flex-direction: row-reverse; }
        .chat-msg .av {
            width: 38px; height: 38px; flex-shrink: 0;
            border-radius: 12px; background: var(--grad-soft); color: var(--violet);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 15px; overflow: hidden;
        }
        .chat-msg .av img { width: 100%; height: 100%; object-fit: cover; }
        .chat-msg .msg-body { min-width: 0; flex: 1; }
        .chat-msg.me .msg-body { text-align: right; }
        .msg-head { display: flex; align-items: center; gap: 8px; font-size: 12.5px; flex-wrap: wrap; }
        .chat-msg.me .msg-head { justify-content: flex-end; }
        .msg-head .who { font-weight: 700; color: var(--ink); }
        .msg-src { font-size: 10.5px; color: var(--faint); }
        .admin-tag {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 1px 7px; border-radius: 6px;
            font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em;
            color: #fca5a5; background: rgba(244,63,94,0.12); border: 1px solid rgba(244,63,94,0.28);
        }
        .msg-time { margin-left: auto; font-size: 11px; color: var(--faint); }
        .chat-msg.me .msg-time { margin-left: 0; margin-right: auto; }
        .msg-text { font-size: 13.5px; color: var(--ink2); line-height: 1.6; word-break: break-word; margin-top: 2px; }
        .chat-empty { display: flex; flex-direction: column; align-items: center; gap: 12px; justify-content: center; padding: 48px 20px; color: var(--dim); font-size: 13.5px; text-align: center; }
        .chat-empty i { font-size: 34px; color: var(--faint); }
        .chat-foot { display: flex; align-items: center; gap: 10px; padding: 16px 24px; border-top: 1px solid var(--line); background: rgba(255,255,255,0.015); }
        .chat-foot .form-control { flex: 1; min-height: 46px; }
        .btn-chat {
            display: inline-flex; align-items: center; justify-content: center; gap: 9px;
            padding: 0 22px; height: 46px;
            border-radius: 13px;
            background: var(--grad); color: #fff;
            font-size: 13.5px; font-weight: 600; white-space: nowrap;
            box-shadow: 0 14px 34px -14px rgba(var(--color-accent-rgb),0.7);
            transition: filter 0.2s ease, transform 0.2s ease;
            flex-shrink: 0;
        }
        .btn-chat:hover { filter: brightness(1.08); transform: translateY(-1px); }
        .chat-lock { display: flex; align-items: center; justify-content: center; gap: 9px; padding: 18px; font-size: 13.5px; color: var(--dim); border-top: 1px solid var(--line); text-align: center; background: rgba(255,255,255,0.015); }
        .chat-lock.red { color: #fda4af; }

        @media (max-width: 1024px) {
            .profile-layout { grid-template-columns: 1fr; }
            .profile-ident { position: static; }
            .setting-grid { grid-template-columns: 1fr; gap: 0; }
            .setting-block + .setting-block { border-left: none; border-top: 1px solid var(--line); padding-left: 0; padding-top: 26px; margin-top: 26px; }
            .cols { grid-template-columns: 1fr; }
            .account-grid { grid-template-columns: 1fr; }
            .avatar-panel { position: absolute; }
        }
        @media (max-width: 640px) {
            .chat-meta { width: 100%; justify-content: space-between; }
        }

        /* ============================================================
           КАБИНЕТ-ЛЭЙАУТ (всё в одном месте, чат в колонке)
           ============================================================ */
        .cabinet { display: flex; align-items: center; max-width: 1100px; margin: 0 auto; padding: 2.5rem 1.25rem 3rem; min-height: calc(100vh - 120px); }
        .cabinet-content { flex-direction: column; gap: 24px; width: 100%; display: flex; }

        .content-header { flex-direction: column; gap: 12px; display: flex; }
        .content-page { align-items: center; gap: 6px; display: flex; }
        .content-page .page-icon, .content-page .page-text { color: var(--text-500); }
        .content-page .page-text { letter-spacing: 0%; font-size: 13px; font-weight: 600; line-height: 100%; }
        .content-page .page-icon { font-size: 14px; }
        .content-hello { letter-spacing: -1.5%; color: var(--text-100); font-size: 30px; font-weight: 600; line-height: 105%; }
        .content-signature { letter-spacing: 0%; color: var(--text-700); font-size: 14px; font-weight: 500; line-height: 155%; }

        .content-body { display: flex; gap: 16px; align-items: flex-start; }
        .content-column { display: flex; flex-direction: column; }
        .column-user { gap: 16px; width: 100%; max-width: 300px; }
        .column-info { width: 100%; max-width: 776px; gap: 16px; }

        .row-wrapper { align-items: stretch; gap: 16px; display: flex; }
        .row-wrapper.wrap { flex-wrap: wrap; }

        .column-wrapper {
            background: var(--bg-800);
            border: 1px solid var(--outline-900);
            border-radius: 16px;
            padding: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.03);
        }

        .wrapper-user { flex-direction: column; gap: 12px; display: flex; position: relative; }
        .user-header { justify-content: space-between; align-items: center; display: flex; }
        .user-profile { align-items: center; gap: 10px; display: flex; }
        .profile-avatar { position: relative; }
        .profile-avatar img { border-radius: 16px; width: 42px; height: 42px; object-fit: cover; }
        .avatar-fallback {
            display: none; align-items: center; justify-content: center;
            width: 42px; height: 42px; border-radius: 16px;
            background: var(--grad); color: #fff; font-weight: 700; font-size: 16px; letter-spacing: 0.03em;
        }
        .avatar-online {
            background: #34d399; border-radius: 50%; outline: 2px solid var(--bg-800);
            width: 9px; height: 9px; position: absolute; bottom: -2px; right: -2px;
            box-shadow: 0 0 0 2px rgba(52, 211, 153, 0.25);
        }
        .profile-information { flex-direction: column; align-items: flex-start; gap: 3px; display: flex; min-width: 0; }
        .information-role {
            display: flex; align-items: center; gap: 5px;
            letter-spacing: 0%; color: var(--text-600); font-size: 11px; font-weight: 600; line-height: 100%; white-space: nowrap;
        }
        .information-role i { color: var(--accent-color); font-size: 11px; }
        .information-role .role-text { color: var(--accent-color); font-weight: 700; }
        .information-role .role-uid { color: var(--text-600); font-weight: 600; font-size: 11px; padding-left: 8px; }
        .information-username {
            letter-spacing: -0.5%; color: var(--text-200); align-items: center; gap: 5px;
            font-size: 15px; font-weight: 600; line-height: 105%; display: flex; min-width: 0;
        }
        .information-username span { color: var(--text-500); font-size: 12px; font-weight: 600; }
        .information-username .role-pill { margin-top: 0; }
        .user-button {
            display: flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 9px;
            background: var(--bg-600); color: var(--text-500); font-size: 13px;
            cursor: pointer; transition: all 0.18s ease;
        }
        .user-button:hover { color: var(--accent-color); background: rgba(99, 102, 241, 0.12); }
        .user-hr { background: var(--outline-900); width: 100%; height: 1px; }

        .user-tabs { flex-direction: column; gap: 6px; display: flex; }
        .tab {
            background: var(--bg-600); cursor: pointer; border-radius: 10px;
            align-items: center; gap: 8px; padding: 9px 14px; display: flex;
            color: var(--text-300); font-size: 13px; font-weight: 600;
            transition: all 0.18s ease; width: 100%;
        }
        .tab:hover { color: var(--ink); background: var(--bg-500); }
        .tab.active { background: color-mix(in srgb, var(--accent-color), transparent 88%); color: var(--accent-color); }
        .tab i { font-size: 13px; }

        .wrapper-activate-key {
            flex-direction: column; flex: 1; justify-content: center;
            align-items: stretch; gap: 12px; padding: 16px; display: flex;
        }
        .input-wrapper {
            border: 1px solid var(--outline-900); background: var(--bg-700);
            border-radius: 14px; overflow: hidden;
        }
        .input-wrapper label {
            display: flex; align-items: center; gap: 8px; padding: 9px 14px 0;
            font-size: 12px; font-weight: 600; color: var(--text-500);
        }
        .input-wrapper label i { font-size: 12px; color: var(--text-500); }
        .input-wrapper input {
            width: 100%; background: transparent; border: none; outline: none;
            padding: 7px 14px 11px; color: var(--ink); font-size: 13px; font-weight: 500;
        }
        .input-wrapper input::placeholder { color: var(--text-600); }
        .input-wrapper textarea {
            width: 100%; background: transparent; border: none; outline: none; resize: vertical;
            padding: 7px 14px 11px; color: var(--ink); font-size: 13px; font-weight: 500; min-height: 90px;
        }
        .btn-fill {
            display: flex; align-items: center; justify-content: center; gap: 7px;
            background: color-mix(in srgb, var(--accent-color), transparent 88%);
            color: var(--accent-color); border-radius: 1000px;
            padding: 9px 14px; font-size: 13px; font-weight: 600; line-height: 100%;
            width: 100%; transition: all 0.18s ease; cursor: pointer;
        }
        .btn-fill:hover { background: color-mix(in srgb, var(--accent-color), transparent 78%); }
        .btn-fill.solid { background: var(--grad); color: #fff; }
        .btn-fill.solid:hover { filter: brightness(1.1); }
        .btn-fill.outline { background: transparent; border: 1px solid var(--outline-700); color: var(--text-300); }
        .btn-fill.outline:hover { background: var(--panel); color: var(--ink); }
        .btn-fill:disabled, .btn-fill.disabled { opacity: 0.5; pointer-events: none; }
        .btn-fill i { font-size: 12px; }

        .information-wrapper {
            background: var(--bg-800); border: 1px solid var(--outline-900);
            border-radius: 16px; flex-direction: column; gap: 12px;
            width: 100%; padding: 16px 20px; display: flex; position: relative;
            flex: 1; min-width: 0;
        }
        .wrapper-title { align-items: center; gap: 8px; display: flex; }
        .title-icon, .title-text { color: var(--text-500); font-size: 12px; font-weight: 600; }
        .wrapper-data {
            letter-spacing: -0.3%; color: var(--text-200);
            font-size: 15px; font-weight: 600; line-height: 100%;
            word-break: break-word; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
        }
        .wrapper-data.ok { color: #34d399; }
        .wrapper-data.no { color: var(--text-500); }

        .other-column { flex-direction: column; gap: 16px; display: flex; min-width: 0; flex: 1; }
        .column-launcher { flex: 1.4; }
        .other-column .column-wrapper { width: 100%; padding: 16px; }
        .other-column .column-wrapper:only-child { flex: 1; }

        .wrapper-faq { flex-direction: column; flex: 1; justify-content: space-between; display: flex; gap: 12px; }
        .faq-title { flex-direction: column; gap: 3px; display: flex; }
        .faq-title .title-text { color: var(--text-200); font-size: 13.5px; font-weight: 600; }
        .faq-title .title-description { color: var(--text-600); font-size: 12px; font-weight: 500; }
        .faq-socials { align-items: center; gap: 6px; display: flex; }
        .social {
            background: color-mix(in srgb, var(--accent-color), transparent 88%);
            -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px);
            color: var(--accent-color); cursor: pointer; border-radius: 1000px;
            flex-grow: 1; justify-content: center; align-items: center;
            padding: 8px 10px; font-size: 15px; display: flex; transition: all 0.18s ease;
        }
        .social:hover { background: color-mix(in srgb, var(--accent-color), transparent 78%); }
        .social a { display: flex; align-items: center; justify-content: center; width: 100%; }

        .wrapper-launcher { flex-direction: column; gap: 14px; display: flex; }
        .launcher-lock { font-size: 12px; }
        .launcher-body {
            flex-wrap: wrap; justify-content: space-between;
            align-items: center; gap: 10px; display: flex;
        }
        .body-data {
            border: 1px solid var(--outline-900); border-radius: 14px;
            width: calc(33.333% - 8px); min-width: 110px; padding: 12px 14px;
            display: flex; flex-direction: column; gap: 8px; background: var(--bg-700);
        }
        .data-title { align-items: center; gap: 7px; display: flex; }
        .data-title .title-icon { font-size: 11px; }
        .data-title .title-text { color: var(--text-500); font-size: 11px; font-weight: 600; }
        .data-content { letter-spacing: -0.3%; color: var(--text-200); font-size: 13px; font-weight: 600; line-height: 100%; }
        .launcher-buttons { flex-direction: column; gap: 10px; display: flex; }

        /* форма в карточке */
        .cab-form .form-group { margin-bottom: 10px; }
        .cab-form .form-control {
            width: 100%; background: var(--bg-700); border: 1px solid var(--outline-900);
            border-radius: 12px; padding: 10px 14px; color: var(--ink);
            font-size: 13px; font-weight: 500; outline: none;
        }
        .cab-form .form-control::placeholder { color: var(--text-600); }
        .cab-form .form-control:focus { border-color: rgba(var(--color-accent-rgb),0.6); box-shadow: 0 0 0 3px rgba(var(--color-accent-rgb),0.15); }

        /* чат в колонке */
        .chat-inline { padding: 0; overflow: hidden; }
        .chat-inline .chat-top { padding: 16px 18px; }
        .chat-inline .chat-scroll { max-height: 240px; padding: 12px 18px; }
        .chat-inline .chat-foot { padding: 12px 18px; }
        .chat-inline .chat-empty { padding: 30px 0; }
        .chat-lock { padding: 18px; }

        @media (max-width: 900px) {
            .content-body { flex-direction: column; }
            .column-user, .column-info { max-width: 100%; }
            .row-wrapper { flex-wrap: wrap; }
            .other-column { width: 100%; }
            .content-hello { font-size: 26px; }
        }
        @media (max-width: 640px) {
            .content-hello { font-size: 23px; }
            .body-data { width: 100%; }
            .chat-inline .chat-scroll { max-height: 180px; }
        }
    
        /* Настройки */
        .settings-group { padding: 16px 18px; border-radius: 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--line); margin-bottom: 14px; }
        .settings-group .settings-row { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .settings-group .info .label { font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; color: var(--text-200); }
        .settings-group .info .label i { color: var(--accent-color); }
        .settings-group .info .desc { font-size: 12px; color: var(--text-600); margin-top: 2px; }
        .settings-group .control { display: flex; align-items: center; gap: 12px; }
        .settings-group .ram-slider { -webkit-appearance: none; width: 120px; height: 5px; border-radius: 4px; background: rgba(255,255,255,0.06); outline: none; border: none; }
        .settings-group .ram-slider::-webkit-slider-thumb { -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%; background: var(--grad); cursor: pointer; box-shadow: 0 0 16px rgba(var(--color-accent-rgb),0.25); }
        .settings-group .ram-slider::-moz-range-thumb { width: 18px; height: 18px; border-radius: 50%; background: var(--grad); cursor: pointer; border: none; }
        .settings-group .ram-value { font-size: 15px; font-weight: 700; min-width: 72px; text-align: right; color: var(--text-200); }
        .lang-seg { display: flex; gap: 4px; padding: 4px; border-radius: 10px; background: rgba(255,255,255,0.03); border: 1px solid var(--line); }
        .lang-seg .lang-btn { padding: 6px 16px; border-radius: 7px; border: none; font-size: 12px; font-weight: 600; font-family: 'Inter', sans-serif; cursor: pointer; color: var(--text-600); background: transparent; transition: all .2s; }
        .lang-seg .lang-btn.on { background: var(--grad); color: #fff; box-shadow: 0 2px 8px rgba(var(--color-accent-rgb),0.25); }
        @media (max-width: 640px) {
            .settings-group .settings-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .settings-group .control { width: 100%; justify-content: space-between; }
        }

    
/* ===== FOOTER ===== */
.footer{position:relative;background:transparent;padding:0 20px 32px}
.footer{margin-top:180px;border-top:0}
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
.footer__credit{grid-column:1 / -1;margin-top:10px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);text-align:center;font-family:Inter,sans-serif;font-size:.78rem;line-height:1.3;color:rgba(255,255,255,.4)}
.footer__credit-link{color:rgba(255,255,255,.6);text-decoration:none;transition:color .22s ease}
.footer__credit-link:hover{color:#fff}
.footer__credit-link.shine{position:relative;display:inline-block;isolation:isolate;-webkit-text-fill-color:#fff}
.footer__credit-link.shine:before{content:attr(data-text);position:absolute;top:0;right:0;bottom:0;left:0;pointer-events:none;background-image:linear-gradient(100deg,transparent 0%,transparent 35%,var(--color-accent,#68aeff) 50%,transparent 65%,transparent 100%);background-size:220% 100%;background-position:140% 0;background-repeat:no-repeat;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;animation:brand-shine 4s ease-in-out infinite;}
@media(max-width:1080px){.footer__inner{padding:24px 24px 18px}}
@media(max-width:860px){.footer__inner{padding:22px 22px 16px}}
@media(max-width:760px){.footer__inner{padding:20px 20px 14px;grid-template-columns:1fr 1fr;gap:28px}}
@media(max-width:640px){.footer{padding:0 16px 20px}.footer__inner{padding:20px 16px 14px;grid-template-columns:1fr;gap:24px}}
/* ===== ЦВЕТА САЙТА — управляются файлом site_colors.php ===== */
    :root {
        --bg: <?php echo $C['bg']; ?>;
        --bg2: <?php echo $C['bg2']; ?>;
        --panel: <?php echo $C['panel']; ?>;
        --panel-h: <?php echo $C['panel_h']; ?>;
        --line: <?php echo $C['line']; ?>;
        --line2: <?php echo $C['line2']; ?>;
        --indigo: <?php echo $C['accent']; ?>;
        --purple: <?php echo $C['grad2']; ?>;
        --violet: <?php echo $C['grad3']; ?>;
        --pink: <?php echo $C['pink']; ?>;
        --grad: linear-gradient(135deg, <?php echo $C['grad1']; ?>, <?php echo $C['grad2']; ?> 55%, <?php echo $C['grad3']; ?>);
        --ink: <?php echo $C['ink']; ?>;
        --ink2: <?php echo $C['ink2']; ?>;
        --dim: <?php echo $C['dim']; ?>;
        --faint: <?php echo $C['faint']; ?>;
        --color-accent: <?php echo $C['accent']; ?>;
        --color-accent-rgb: <?php echo $C['accent_rgb']; ?>;
        --color-accent-light: <?php echo $C['accent_light']; ?>;
        --color-accent-dark: <?php echo $C['accent_dark']; ?>;
        --success: <?php echo $C['success']; ?>;
        --danger: <?php echo $C['danger']; ?>;
    }
.shader-orbs{position:absolute;inset:0;overflow:hidden;pointer-events:none}
.shader-orbs .orb{position:absolute;border-radius:50%;filter:blur(90px);mix-blend-mode:screen;will-change:transform}
.orb--1{width:380px;height:380px;background:radial-gradient(circle,rgba(90,99,232,0.45) 0%,transparent 70%);top:-10%;left:-8%;animation:o1 20s ease-in-out infinite alternate}
.orb--2{width:300px;height:300px;background:radial-gradient(circle,rgba(139,92,246,0.38) 0%,transparent 70%);bottom:-12%;right:-6%;animation:o2 24s ease-in-out infinite alternate}
.orb--3{width:240px;height:240px;background:radial-gradient(circle,rgba(168,85,247,0.32) 0%,transparent 70%);top:35%;left:50%;animation:o3 17s ease-in-out infinite alternate}
.orb--4{width:180px;height:180px;background:radial-gradient(circle,rgba(74,82,208,0.28) 0%,transparent 70%);top:12%;right:18%;animation:o4 22s ease-in-out infinite alternate}
@keyframes o1{0%{transform:translate(0,0) scale(1)}33%{transform:translate(70px,50px) scale(1.08)}66%{transform:translate(-40px,90px) scale(0.94)}100%{transform:translate(50px,30px) scale(1.04)}}
@keyframes o2{0%{transform:translate(0,0) scale(1)}33%{transform:translate(-60px,-35px) scale(1.06)}66%{transform:translate(50px,-70px) scale(0.9)}100%{transform:translate(-25px,-45px) scale(1)}}
@keyframes o3{0%{transform:translate(0,0) scale(1)}50%{transform:translate(-80px,35px) scale(1.12)}100%{transform:translate(35px,-55px) scale(0.88)}}
@keyframes o4{0%{transform:translate(0,0) scale(0.9)}40%{transform:translate(45px,55px) scale(1.08)}100%{transform:translate(-55px,-25px) scale(0.94)}}        @media (prefers-reduced-motion: reduce) {
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
            <a href="/main">Home</a>
            <a href="/shop">Shop</a>
            <a href="/rules">Rules</a>
            <a href="/privacy">Terms</a>
            <a href="/profile">Profile</a>
        </nav>

        <div class="header-actions">
            <button class="header-action header-action--profile" type="button" onclick="location.href='/profile'">
                        <img class="header-avatar" src="/assets/ava.png" alt="" onerror="this.style.display='none'">
                        <span>Profile</span>
                    </button>
            <button class="burger" id="burgerBtn" aria-label="Menu"><i class="fas fa-bars"></i></button>
        </div>
    </div>

    <div class="m-menu">
        <a href="/main">Home</a>
        <a href="/shop">Shop</a>
        <a href="/rules">Rules</a>
        <a href="/privacy">Terms</a>
        <a href="/profile">Profile</a>
        <a href="/logout">Log out</a>
    </div>
</header>

<!-- ===== НАСТРОЙКИ ===== -->
<main class="cabinet">
    <div class="cabinet-content">

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?> reveal">
                <i class="fas fa-<?php echo $msg_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <div class="content-page">
                <div class="page-icon"><i class="fas fa-gear"></i></div>
                <div class="page-text">Settings</div>
            </div>
            <div class="content-hello">Launcher settings, <span class="grad"><?php echo htmlspecialchars($user['username']); ?></span></div>
            <div class="content-signature">Configure RAM and language for your launcher — they will be applied automatically</div>
        </div>

        <div class="content-body">

            <div class="content-column column-info" style="width:100%;max-width:100%;">
                <div class="row-wrapper">
                    <div class="column-wrapper reveal" style="max-width:560px;">
                        <div class="wrapper-title">
                            <div class="title-icon"><i class="fas fa-sliders"></i></div>
                            <div class="title-text">Launcher Settings</div>
                        </div>

                        <form method="POST" class="cab-form" style="margin-top:14px;" novalidate>
                            <input autocomplete="off" type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">
                            <input autocomplete="off" type="hidden" name="action" value="save_settings">

                            <div class="settings-group">
                                <div class="settings-row">
                                    <div class="info">
                                        <div class="label"><i class="fas fa-memory"></i> RAM Allocation</div>
                                        <div class="desc">Memory for the game client</div>
                                    </div>
                                    <div class="control">
                                        <input autocomplete="off" type="range" class="ram-slider" id="ramSlider" min="256" max="16384" step="100" value="<?php echo (int)$settings['ram_mb']; ?>" oninput="ramChange(this.value)">
                                        <span class="ram-value" id="ramValue"><?php echo (int)$settings['ram_mb']; ?> MB</span>
                                    </div>
                                </div>
                                <input autocomplete="off" type="hidden" name="ram_mb" id="ramHidden" value="<?php echo (int)$settings['ram_mb']; ?>">
                            </div>

                            <div class="settings-group">
                                <div class="settings-row">
                                    <div class="info">
                                        <div class="label"><i class="fas fa-globe"></i> Language</div>
                                        <div class="desc">Interface language</div>
                                    </div>
                                    <div class="control">
                                        <div class="lang-seg">
                                            <button type="button" class="lang-btn <?php echo $settings['lang'] === 'en' ? 'on' : ''; ?>" data-lang="en">EN</button>
                                            <button type="button" class="lang-btn <?php echo $settings['lang'] === 'ru' ? 'on' : ''; ?>" data-lang="ru">RU</button>
                                        </div>
                                        <input autocomplete="off" type="hidden" name="lang" id="langHidden" value="<?php echo htmlspecialchars($settings['lang']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                                <button type="submit" class="btn-fill solid"><i class="fas fa-save"></i> Save Settings</button>
                            </div>
                        </form>
                    </div>
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

    nav.querySelector('#burgerBtn').addEventListener('click', function() {
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
    document.querySelectorAll('.reveal').forEach(function(el) { observer.observe(el); });
});

</script>

<script>
    // ===== НАСТРОЙКИ: RAM + ЯЗЫК =====
    (function() {
        var slider = document.getElementById('ramSlider');
        var hidden = document.getElementById('ramHidden');
        var value = document.getElementById('ramValue');

        function fmtRam(mb) {
            return mb + ' MB';
        }

        window.ramChange = function(mb) {
            mb = parseInt(mb) || 0;
            if (hidden) hidden.value = mb;
            if (value) value.textContent = fmtRam(mb);
        };

        var langBtns = document.querySelectorAll('.lang-btn');
        var langHidden = document.getElementById('langHidden');
        langBtns.forEach(function(b) {
            b.addEventListener('click', function() {
                langBtns.forEach(function(x) { x.classList.remove('on'); });
                b.classList.add('on');
                if (langHidden) langHidden.value = b.dataset.lang;
            });
        });

        if (slider && value) ramChange(slider.value);
    })();
</script>

<script src="/devtools.js"></script>

<?php include 'loader_js.php'; ?>
</body>
</html>
