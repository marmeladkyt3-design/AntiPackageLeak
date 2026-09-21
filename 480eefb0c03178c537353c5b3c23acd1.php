<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'site_config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM launcher_settings");
    $ls = [];
    while ($row = $stmt->fetch()) { $ls[$row['setting_key']] = $row['setting_value']; }
    $current_version = $ls['current_version'] ?? '1.0.0';
    $maintenance_mode = $ls['maintenance_mode'] ?? '0';
    $maintenance_message = $ls['maintenance_message'] ?? 'Launcher is under maintenance.';
    $site_url = $ls['site_url'] ?? '';
} catch (PDOException $e) {
    $current_version = '1.0.0';
    $maintenance_mode = '0';
    $maintenance_message = 'Launcher is under maintenance.';
    $site_url = '';
}
if (empty($site_url)) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'AntiPackageLeak.ct.ws';
    $site_url = ($https ? 'https' : 'http') . '://' . $host;
}
$SITE_NAME_esc = htmlspecialchars($SITE_NAME ?? 'AntiPackageLeak');
$js_maintenance = json_encode($maintenance_mode === '1');
$js_maintenance_msg = json_encode($maintenance_message);

$server_crypto_key = '';
try {
    $build = isset($_GET['build']) ? (int)$_GET['build'] : 2;
    $ckStmt = $pdo->prepare("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 AND launcher_build_min <= ? ORDER BY id DESC LIMIT 1");
    $ckStmt->execute([$build]);
    $ckRow = $ckStmt->fetch();
    if ($ckRow && !empty($ckRow['crypto_key'])) { $server_crypto_key = $ckRow['crypto_key']; }
} catch (Exception $e) {}
if (empty($server_crypto_key)) {
    try {
        $ckStmt2 = $pdo->query("SELECT crypto_key FROM launcher_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $ckRow2 = $ckStmt2->fetch();
        if ($ckRow2 && !empty($ckRow2['crypto_key'])) { $server_crypto_key = $ckRow2['crypto_key']; }
    } catch (Exception $e2) {}
}
$js_crypto_key = json_encode($server_crypto_key);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $SITE_NAME_esc; ?> Launcher</title>
<style>
@font-face{font-family:SFRegular;src:url('assets/asset_7.ttf') format('truetype')}
@font-face{font-family:SFBold;src:url('assets/asset_8.ttf') format('truetype')}
@font-face{font-family:Icons;src:url('assets/asset_9.ttf') format('truetype')}
:root{--accent:#5a63e8;--accent2:#7c51dd;--bg:#07070b;--panel:rgba(255,255,255,.04);--panel2:rgba(255,255,255,.07);--border:rgba(255,255,255,.09);--text:#eceefb;--text2:#8d94b0;--danger:#e5484d}
*{margin:0;padding:0;box-sizing:border-box;user-select:none;-webkit-user-select:none}
html,body{width:100%;height:100%;overflow:hidden}
body{font-family:SFRegular;background:var(--bg);color:var(--text);font-size:14px}
#root{position:relative;width:100vw;height:100vh;overflow:hidden}
.app{position:absolute;inset:0;display:flex;flex-direction:column;background:linear-gradient(135deg,rgba(8,8,13,.9),rgba(4,4,8,.95));backdrop-filter:blur(24px)}
.app::before{content:'';position:absolute;inset:0;z-index:-1;background:url('assets/2.png') center/cover no-repeat;filter:blur(14px) saturate(1.15);transform:scale(1.06)}
.sf-hidden{display:none!important}
.auth{flex:1;display:flex;min-height:0}
.auth-left{width:44%;min-width:380px;padding:48px 56px;display:flex;flex-direction:column;justify-content:center;gap:24px;background:rgba(5,5,9,.55);border-right:1px solid var(--border)}
.brand{display:flex;align-items:center;gap:12px}
.brand-logo{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;color:#fff}
.brand-logo svg{width:20px;height:20px}
.brand-name{font-family:SFBold;font-size:21px;letter-spacing:.3px}
.auth-title{font-family:SFBold;font-size:26px}
.auth-sub{color:var(--text2);font-size:13.5px;margin-top:8px}
.f-label{font-size:12px;color:var(--text2);margin-bottom:8px;letter-spacing:.5px;text-transform:uppercase}
.input-wrap{position:relative;display:flex;align-items:center}
.input-wrap .ic{position:absolute;left:14px;display:flex;color:var(--text2);pointer-events:none}
.input-wrap .ic svg{width:16px;height:16px}
.input-wrap .clear-x{position:absolute;right:12px;display:flex;color:var(--text2);cursor:pointer;display:none}
.input-wrap .clear-x svg{width:12px;height:12px}
.input-wrap .eye{position:absolute;right:12px;display:flex;color:var(--text2);cursor:pointer}
.input-wrap .eye svg{width:16px;height:16px}
.input-wrap input{width:100%;height:46px;border-radius:12px;border:1px solid var(--border);background:var(--panel);color:var(--text);font-family:SFRegular;font-size:14px;padding:0 40px 0 42px;outline:none;transition:.2s}
.input-wrap input:focus{border-color:rgba(90,99,232,.7);background:var(--panel2);box-shadow:0 0 0 3px rgba(90,99,232,.16)}
.input-wrap input::placeholder{color:#5c6479}
.btn{height:46px;border:none;border-radius:12px;font-family:SFBold;font-size:14.5px;letter-spacing:.4px;cursor:pointer;transition:.2s}
.btn-primary{width:100%;background:linear-gradient(135deg,#4a52d0,#6b44c8);color:#fff;box-shadow:0 8px 22px rgba(74,82,208,.25)}
.btn-primary:hover{filter:brightness(1.12);transform:translateY(-1px)}
.forgot{margin-top:14px;text-align:center;color:var(--text2);font-size:13px;cursor:pointer}
.forgot:hover{color:var(--text)}
.auth-right{flex:1;position:relative;overflow:hidden}
.auth-right img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity 1s ease}
.auth-right img.active{opacity:1}
.shade{position:absolute;inset:0;background:linear-gradient(90deg,rgba(5,5,9,.6),transparent 40%),linear-gradient(0deg,rgba(5,5,9,.9),transparent 55%)}
.slide-text{position:absolute;left:40px;bottom:78px;max-width:68%}
.st-icon{width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.07);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text);backdrop-filter:blur(8px);margin-bottom:16px}
.st-icon svg{width:18px;height:18px}
.st-title{font-family:SFBold;font-size:24px;margin-bottom:8px}
.st-desc{color:var(--text2);font-size:14px;line-height:1.55}
.dots{position:absolute;left:40px;bottom:38px;display:flex;gap:8px}
.dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.28);cursor:pointer;transition:.25s}
.dot.on{width:26px;border-radius:6px;background:var(--accent)}
.main{flex:1;display:flex;flex-direction:column;min-height:0;position:relative}
.topbar{height:64px;flex:none;display:flex;align-items:center;justify-content:space-between;padding:0 22px;border-bottom:1px solid var(--border);background:rgba(6,6,10,.6)}
.user{display:flex;align-items:center;gap:12px;min-width:0}
.avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--border)}
.u-name{font-family:SFBold;font-size:14px}
.u-sub{font-size:12px;color:var(--text2)}
.top-actions{display:flex;gap:8px}
.act-btn{width:34px;height:34px;border-radius:10px;border:1px solid var(--border);background:var(--panel);color:var(--text2);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s}
.act-btn svg{width:12px;height:12px}
.act-btn:hover{background:var(--panel2);color:var(--text)}
.act-btn.close:hover{background:rgba(229,72,77,.14);color:var(--danger);border-color:rgba(229,72,77,.3)}
.main-body{flex:1;display:flex;min-height:0}
.sidebar{width:62px;flex:none;display:flex;flex-direction:column;align-items:center;gap:8px;padding:14px 0;border-right:1px solid var(--border);background:rgba(6,6,10,.4)}
.sidebar .spacer{flex:1}
.nav-item{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--text2);cursor:pointer;transition:.2s}
.nav-item svg{width:18px;height:18px}
.nav-item:hover{background:var(--panel);color:var(--text)}
.nav-item.on{background:linear-gradient(135deg,#4a52d0,#6b44c8);color:#fff;box-shadow:0 5px 14px rgba(74,82,208,.28)}
.content{flex:1;overflow-y:auto;padding:26px;scrollbar-width:thin}
.versions{display:flex;flex-wrap:wrap;gap:14px}
.v-card{position:relative;height:188px;min-width:240px;flex:1 1 240px;border-radius:12px;overflow:hidden;border:1px solid var(--border);background:#0c0c12;transition:transform .3s ease, box-shadow .3s ease, border-color .3s ease}
.v-card img{width:100%;height:100%;object-fit:cover;display:block;filter:saturate(40%) brightness(.5);transition:filter .25s ease}
.v-card:hover{border-color:rgba(255,255,255,.18);transform:translateY(-6px);box-shadow:0 12px 24px rgba(90,99,232,.2)}
.v-card:hover img{filter:saturate(55%) brightness(.85)}
.v-name-strip{position:absolute;left:10px;bottom:10px;z-index:2;padding:6px 10px;border-radius:8px;background:rgba(8,8,12,.4);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.08);pointer-events:none}
.v-name-strip .nm{font-family:SFBold;font-size:13.5px;line-height:1.2}
.v-name-strip .vm{font-size:11px;color:var(--text2);margin-top:2px}
.start-btn{transition:all .25s ease;display:flex;justify-content:center;align-items:center;position:absolute;right:10px;bottom:10px;z-index:2;width:35px;height:35px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(90,99,232,.18);backdrop-filter:blur(5px);cursor:pointer;color:#fff}
.start-btn svg{width:12px;height:12px}
.v-card:hover .start-btn{background:rgba(255,255,255,.16);border-color:rgba(255,255,255,.24)}
.v-card.no-access img{filter:saturate(20%) brightness(.38) grayscale(.8)}
.v-card.no-access .start-btn{background:rgba(255,255,255,.06);box-shadow:none;color:var(--text2);pointer-events:none}
.lock-tag{position:absolute;top:10px;right:10px;z-index:2;font-size:10.5px;letter-spacing:.4px;background:rgba(8,8,12,.55);border:1px solid var(--border);color:var(--text2);padding:4px 9px;border-radius:7px;backdrop-filter:blur(6px);pointer-events:none}
.launch-screen{position:absolute;inset:0;z-index:10000;background:#07070b;display:flex;align-items:center;justify-content:center;animation:lsIn .3s ease}
@keyframes lsIn{from{opacity:0;transform:scale(1.04)}to{opacity:1;transform:scale(1)}}
.ls-bg{position:absolute;inset:0;background:center/cover no-repeat;filter:blur(10px) brightness(.4) saturate(.7);transform:scale(1.06)}
.ls-dark{position:absolute;inset:0;background:rgba(5,5,9,.55)}
.ls-inner{position:relative;display:flex;flex-direction:column;align-items:center;gap:16px}
.circle-wrap{position:relative;width:80px;height:80px}
.circle-wrap svg{width:80px;height:80px;transform:rotate(-90deg)}
.circle-wrap circle{fill:none;stroke-width:5;stroke-linecap:round}
.circle-wrap circle.bg{stroke:rgba(255,255,255,.12)}
.circle-wrap circle.fill{stroke:rgba(255,255,255,.85);stroke-dasharray:226;stroke-dashoffset:226;transition:stroke-dashoffset .3s}
.pct{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:SFBold;font-size:15px}
.ltxt{font-size:12.5px;color:var(--text2)}
.settings{display:flex;flex-direction:column;gap:22px;max-width:560px}
.set-card{background:var(--panel);border:1px solid var(--border);border-radius:16px;padding:20px;transition:transform .3s ease, box-shadow .3s ease}
.set-card:hover{transform:translateY(-4px);box-shadow:0 8px 16px rgba(90,99,232,.15)}
.set-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.set-title{font-family:SFBold;font-size:15px}
.set-val{font-size:13px;color:var(--text2)}
.set-val b{color:var(--text);font-family:SFBold;font-size:17px}
.set-val span{font-size:11px;margin-left:2px}
input[type=range].custom-range{-webkit-appearance:none;appearance:none;width:100%;height:6px;border-radius:4px;outline:none}
input[type=range].custom-range::-webkit-slider-thumb{-webkit-appearance:none;width:18px;height:18px;border-radius:50%;background:#fff;border:3px solid var(--accent);cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.4)}
#maintenanceOverlay{display:none;position:absolute;inset:0;z-index:25000;background:rgba(0,0,0,.72);backdrop-filter:blur(8px);align-items:center;justify-content:center}
#maintenanceOverlay.active{display:flex}
.maint-box{background:var(--panel);border:1px solid var(--border);border-radius:16px;padding:34px 40px;text-align:center;max-width:380px}
.maint-title{font-family:SFBold;font-size:17px;margin:0 0 8px}
.maint-text{font-size:13px;color:var(--text2);line-height:1.5}
#notifyBox{position:fixed;bottom:14px;right:14px;display:flex;flex-direction:column;align-items:flex-end;gap:8px;z-index:20000}
.notification{display:flex;align-items:center;gap:10px;background:rgba(20,24,40,.92);border:1px solid var(--border);border-left:3px solid var(--accent);border-radius:10px;padding:10px 14px;max-width:340px;backdrop-filter:blur(10px);transform:translateX(20px);opacity:0;transition:.3s}
.notification.show{transform:none;opacity:1}
.notification.closing{opacity:0;transform:translateX(20px)}
.notification.err{border-left-color:var(--danger);background:rgba(40,12,14,.92)}
.notification-message{font-size:13px;color:var(--text);line-height:1.4}
.auth-title .char{display:inline-block;opacity:0;transform:translateY(20px) rotateX(40deg);animation:charReveal 0.4s forwards;filter:blur(4px)}
@keyframes charReveal{to{opacity:1;transform:none;filter:none}}
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:4px 0}
.toggle-row .set-title{margin-bottom:0}
.toggle{width:44px;height:24px;border-radius:12px;background:rgba(255,255,255,.12);border:1px solid var(--border);cursor:pointer;position:relative;transition:all .25s ease}
.toggle.on{background:var(--accent);border-color:var(--accent)}
.toggle::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .25s ease}
.toggle.on::after{transform:translateX(20px)}
</style>
</head>
<body>
<div id="root">
<div class="app">
<div class="auth sf-hidden" id="authScreen">
<div class="auth-left">
<div class="brand"><div class="brand-logo"><img src="/assets/logo.png" alt="" style="width:28px;height:28px;object-fit:contain"></div><div class="brand-name"><?php echo $SITE_NAME_esc; ?></div></div>
<div><div class="auth-title">Welcome back</div><div class="auth-sub">Sign in to continue to your account</div></div>
<form id="loginForm" autocomplete="off" novalidate>
<div class="f-label">Login</div>
<div class="input-wrap">
<div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
<input type="text" name="login" placeholder="Enter your login" id="loginField" autocomplete="off" required>
<div class="clear-x" id="loginClear"><svg viewBox="0 0 12 12"><path d="M3 3l6 6M9 3l-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></div>
</div>
<div style="height:16px"></div>
<div class="f-label">Password</div>
<div class="input-wrap">
<div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
<input type="password" name="password" placeholder="Enter your password" id="passwordField" autocomplete="off" required>
<div class="eye" id="eyeToggle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg></div>
</div>
<input type="hidden" id="launcherHwid" value="">
<div style="height:24px"></div>
<button type="submit" class="btn btn-primary" id="signInBtn">Sign In</button>
<div class="forgot" onclick="notify('Contact support to reset your password')">Forgot Password?</div>
</form>
</div>
<div class="auth-right">
<img src="assets/ingamelaunch3.png" alt="" class="active">
<img src="assets/intogamelaunch2.jpg" alt="">
<div class="shade"></div>
<div class="slide-text">
<div class="st-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
<div class="st-title">Products</div>
<div class="st-desc">Our products are tested and trusted by over 1000+ users</div>
</div>
<div class="dots"><div class="dot on" data-dot="0"></div><div class="dot" data-dot="1"></div></div>
</div>
</div>
<div class="main sf-hidden" id="mainScreen">
<div class="topbar">
<div class="user">
<div class="avatar" id="userAvatarWrap" style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;color:#fff;font-family:SFBold;font-size:15px;border:2px solid var(--border);flex-shrink:0;overflow:hidden"><img id="userAvatar" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover"></div>
<div><div class="u-name" id="userName"></div><div class="u-sub" id="userSub"></div></div>
</div>
<div class="top-actions">
<div class="act-btn" onclick="winMinimize()" title="Minimize"><svg viewBox="0 0 12 12"><path d="M2 6h8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></div>
<div class="act-btn close" onclick="winClose()" title="Close"><svg viewBox="0 0 12 12"><path d="M3 3l6 6M9 3l-6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg></div>
</div>
</div>
<div class="main-body">
<div class="sidebar">
<div class="nav-item on" onclick="goRoute('home',this)" title="Home"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
<div class="nav-item" onclick="goRoute('settings',this)" title="Settings"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.6h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></div>
<div class="spacer"></div>
<div class="nav-item" onclick="doLogout()" title="Logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></div>
</div>
<div class="content">
<div id="view-home"><div class="versions" id="versionsList"></div></div>
<div id="view-settings" style="display:none">
<div class="settings">
<div class="set-card">
<div class="set-head"><div class="set-title">RAM</div><div class="set-val" id="ramValue"><b>2048</b> mb</div></div>
<input type="range" class="custom-range" min="1500" max="16000" value="2048" id="ramSlider">
</div>
<div class="set-card">
<div class="toggle-row"><div class="set-title">GPU Priority</div><div class="toggle" id="gpuToggle" onclick="toggleGpu()"></div></div>
<div style="margin-top:8px;font-size:12px;color:var(--text2)">Enable dedicated GPU acceleration for better performance</div>
</div>
<div class="set-card">
<div class="toggle-row"><div class="set-title">Debug Console</div><div class="toggle" id="debugToggle" onclick="toggleDebug()"></div></div>
<div style="margin-top:8px;font-size:12px;color:var(--text2)">Show Minecraft debug console on launch</div>
</div>
</div>
</div>
</div>
</div>
</div>
<div id="maintenanceOverlay" class="active" style="display:none">
<div class="maint-box"><div class="maint-title">Maintenance</div><div class="maint-text" id="maintMsg"></div></div>
</div>
<div id="notifyBox"></div>
</div></div>

<script>
var SITE='<?php echo $site_url; ?>';
var API=SITE+'/api';
var savedRam=2048,totalRamMb=16000,activeLaunch=null,slideIndex=0,launcherHwid='',startLoading=false;
var _jwtToken=localStorage.getItem('launcher_jwt')||'';

function wvm(m){try{if(window.chrome&&window.chrome.webview)window.chrome.webview.postMessage(m);else if(window.external&&window.external.postMessage)window.external.postMessage(m)}catch(e){}}
function winClose(){try{wvm('close_window')}catch(e){}setTimeout(function(){try{wvm('close_window')}catch(e){}},200)}
function winMinimize(){try{wvm('minimize_window')}catch(e){}}
function notify(t,isErr){var b=document.getElementById('notifyBox');if(!b)return;var e=document.createElement('div');e.className='notification show'+(isErr?' err':'');e.innerHTML='<div class="notification-text"><div class="notification-message">'+String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;')+'</div></div>';b.appendChild(e);setTimeout(function(){e.classList.add('closing');setTimeout(function(){if(e.parentNode)e.parentNode.removeChild(e)},900)},3500)}

function showAuth(){document.getElementById('authScreen').classList.remove('sf-hidden');document.getElementById('mainScreen').classList.add('sf-hidden');animateAuth()}
function showMain(){document.getElementById('authScreen').classList.add('sf-hidden');document.getElementById('mainScreen').classList.remove('sf-hidden');animateMain()}

function apiFetch(base,path,body){
var opts={headers:{'Content-Type':'application/json'}};
if(_jwtToken)opts.headers['Authorization']='Bearer '+_jwtToken;
var url=base+path;
if(_jwtToken)url+=(url.indexOf('?')===-1?'?':'&')+'token='+encodeURIComponent(_jwtToken);
if(body){opts.method='POST';opts.body=JSON.stringify(body)}
return fetch(url,opts).then(function(r){return r.json().then(function(d){d._status=r.status;return d})}).catch(function(e){return{_status:0,error:'network_error'}})
}

function fallbackHwid(){var f=localStorage.getItem('alekhwid');if(!f){f='WEB-'+Math.random().toString(16).slice(2)+Date.now().toString(16);localStorage.setItem('alekhwid',f)}return f}

function onLogin(username,token,user){
_jwtToken=token;localStorage.setItem('launcher_jwt',token);
try{wvm('set_token('+token+')')}catch(e){}
window._launcherUserId=user.id||null;
document.getElementById('userName').textContent=user.username||'';
var subEnd=user.subscription_end;
if(subEnd&&new Date(subEnd)>new Date()){
document.getElementById('userSub').textContent='Active until: '+new Date(subEnd).toLocaleDateString('ru-RU');
}else{document.getElementById('userSub').textContent='Expired'}
var avImg=document.getElementById('userAvatar');
var avWrap=document.getElementById('userAvatarWrap');
var avatarSrc=user.avatar_url&&user.avatar_url!==''?user.avatar_url:'/assets/ava.png';
avImg.src=avatarSrc;avImg.style.display='';
avImg.onerror=function(){this.src='/assets/ava.png';this.onerror=null};
try{wvm('set_username('+user.username+')')}catch(e){}
try{var lp=localStorage.getItem('alek_lastpass');if(lp)wvm('set_password('+lp+')')}catch(e){}
showMain();loadVersions();
}

function doLogout(){
_jwtToken='';localStorage.removeItem('launcher_jwt');
showAuth();
}

function loadVersions(){
apiFetch(API,'/versions').then(function(r){
var el=document.getElementById('versionsList');el.innerHTML='';
console.log('[versions]',r);
if(r._status===0){el.innerHTML='<div style="color:var(--danger);font-size:14px">API unreachable (status 0). Check network.</div>';return}
if(r._status!==200){el.innerHTML='<div style="color:var(--danger);font-size:14px">API error: '+(r.error||r._status)+'</div>';return}
if(!r.versions||!r.versions.length){el.innerHTML='<div style="color:var(--text2);font-size:14px">No versions available yet</div>';return}
var imgs=['assets/ingamelaunch3.png','assets/intogamelaunch2.jpg'];
r.versions.forEach(function(v,i){
var blocked=v.blocked;var src=imgs[i%2];
var card=document.createElement('div');card.className='v-card'+(blocked?' no-access':'');
card.innerHTML='<img src="'+src+'" alt="">' +(blocked?'<div class="lock-tag">NO ACCESS</div>':'')+
'<div class="v-name-strip"><div class="nm">'+v.version+'</div></div>'+
(blocked?'':'<button class="start-btn" onclick="launchGame(\''+v.version+'\',this)"><svg viewBox="0 0 8 8" fill="none"><path d="M0 3.99V2.33C0 .25 1.47-.59 3.26.44L4.71 1.28 6.15 2.12c1.8 1.04 1.8 2.72 0 3.76L4.71 6.72 3.26 7.56C1.47 8.59 0 7.75 0 5.67V3.99Z" fill="white"/></svg></button>');
el.appendChild(card);
});
animateCards();
});
}

/* login form */
var loginForm=document.getElementById('loginForm');
var hwidField=document.getElementById('launcherHwid');
if(hwidField)hwidField.value=fallbackHwid();
if(window.chrome&&window.chrome.webview){
wvm('get_hwid');
window.chrome.webview.addEventListener('message',function(e){
var d=e.data;if(typeof d!=='string')return;
if(d.indexOf('hwid_state|')===0){var hw=d.split('|')[1]||'';if(hw){launcherHwid=hw;if(hwidField)hwidField.value=hw}}
});
}

loginForm.addEventListener('submit',function(e){
e.preventDefault();
var login=document.getElementById('loginField').value.trim();
var pass=document.getElementById('passwordField').value;
var hwid=hwidField.value||fallbackHwid();
if(!login||!pass){showLoginError('Fill all fields');return}
var btn=document.getElementById('signInBtn');btn.textContent='Signing in...';btn.disabled=true;
apiFetch(API,'/login',{login:login,password:pass,hwid:hwid}).then(function(r){
btn.textContent='Sign In';btn.disabled=false;
if(r._status===200&&r.token){
localStorage.setItem('aleklogin',login);
localStorage.setItem('alek_lastpass',pass);
wvm('set_password('+pass+')');
onLogin(r.user.username,r.token,r.user);
}else if(r._status===403&&r.error==='banned'){
notify('Banned: '+(r.reason||'No reason'),true);
}else if(r._status===403&&r.error==='temp_banned'){
notify('Banned: '+(r.reason||'')+' ('+r.minutes_left+' min)',true);
}else if(r._status===403&&r.error==='no_subscription'){
notify('No active subscription',true);
}else{
showLoginError(r.error==='invalid_credentials'?'Invalid login or password':r.error||'Server error');
}
});
});

function showLoginError(msg){notify(msg,true);var el=document.getElementById('loginError');if(el)el.style.display='none'}

/* password eye */
var eyeSvgOpen='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
var eyeSvgClosed='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
var eye=document.getElementById('eyeToggle');
if(eye)eye.addEventListener('click',function(){var p=document.getElementById('passwordField');if(!p)return;var s=p.type==='password';p.type=s?'text':'password';eye.innerHTML=s?eyeSvgClosed:eyeSvgOpen});

/* login clear */
var lf2=document.getElementById('loginField'),lc=document.getElementById('loginClear');
if(lc){lc.style.display='none';function sc(){if(lc)lc.style.display=(lf2&&lf2.value)?'inline-block':'none'}if(lf2){lf2.addEventListener('input',sc)}lc.addEventListener('click',function(){if(lf2){lf2.value='';sc()}})}
if(lf2){var sl=localStorage.getItem('aleklogin');if(sl){lf2.value=sl;if(typeof sc==='function')sc()}}

/* ram */
function ramChange(mb){mb=parseInt(mb)||0;savedRam=mb;var rv=document.getElementById('ramValue');if(rv)rv.innerHTML=mb+'<span>mb</span>';var sl=document.getElementById('ramSlider');if(sl){if(parseInt(sl.value)!==mb)sl.value=mb;var min=parseInt(sl.min)||0,max=parseInt(sl.max)||16000;var pct=(max>min)?(mb-min)/(max-min)*100:0;sl.style.background='linear-gradient(to right,var(--accent) 0%,var(--accent) '+pct+'%,#2b2b2ba6 '+pct+'%,#2b2b2ba6 100%)'}}
var ramSlider=document.getElementById('ramSlider');
if(ramSlider)ramSlider.addEventListener('input',function(){ramChange(this.value)});
var savedRamVal=localStorage.getItem('alek_ram');if(savedRamVal)ramChange(parseInt(savedRamVal)||2048);

/* gpu & debug toggles */
var debugEnabled=localStorage.getItem('alek_debug')==='true';
var gpuEnabled=localStorage.getItem('alek_gpu')==='high'||localStorage.getItem('alek_gpu')==='discrete';
function applyToggles(){var de=document.getElementById('debugToggle');if(de)de.classList.toggle('on',debugEnabled);var ge=document.getElementById('gpuToggle');if(ge)ge.classList.toggle('on',gpuEnabled)}
applyToggles();
try{wvm('set_debug_console('+(debugEnabled?'true':'false')+')');wvm('set_gpu_priority('+(gpuEnabled?'high':'auto')+')');}catch(e){}
function toggleGpu(){gpuEnabled=!gpuEnabled;localStorage.setItem('alek_gpu',gpuEnabled?'high':'auto');document.getElementById('gpuToggle').classList.toggle('on',gpuEnabled);wvm('set_gpu_priority('+(gpuEnabled?'high':'auto')+')')}
function toggleDebug(){debugEnabled=!debugEnabled;localStorage.setItem('alek_debug',debugEnabled);document.getElementById('debugToggle').classList.toggle('on',debugEnabled);wvm('set_debug_console('+(debugEnabled?'true':'false')+')')}

wvm('get_ram_state');wvm('get_hwid');wvm('get_gpu_state');
if(window.chrome&&window.chrome.webview){window.chrome.webview.addEventListener('message',function(e){var d=e.data;if(typeof d!=='string')return;if(d.indexOf('ram_state|')===0){var p=d.split('|');if(p[1])totalRamMb=parseInt(p[1])||16000;var sl=document.getElementById('ramSlider');if(sl){var mx=Math.max(1500,totalRamMb-512);sl.max=mx;var v=parseInt(p[2]||'0')||0;sl.value=v>0?Math.min(v,mx):Math.min(2048,mx);ramChange(sl.value)}}})}

/* routes */
function goRoute(r,btn){document.getElementById('view-home').style.display=r==='home'?'block':'none';document.getElementById('view-settings').style.display=r==='settings'?'block':'none';document.querySelectorAll('.nav-item').forEach(function(p){p.classList.remove('on')});if(btn)btn.classList.add('on')}

/* launch */
function launchGame(ver,btn){
if(startLoading)return;startLoading=true;
wvm('set_ram('+savedRam+')');wvm('set_version('+ver+')');wvm('run_button_clicked');
var card=btn?btn.closest('.v-card'):null;var imgEl=card?card.querySelector('img'):null;
var ov=document.createElement('div');ov.className='launch-screen';
ov.innerHTML='<div class="ls-bg"></div><div class="ls-dark"></div><div class="ls-inner"><div class="circle-wrap"><svg viewBox="0 0 80 80"><circle class="bg" cx="40" cy="40" r="36"/><circle class="fill" cx="40" cy="40" r="36"/></svg><div class="pct">0%</div></div><div class="ltxt">Starting...</div></div>';
if(imgEl){var bg=ov.querySelector('.ls-bg');if(bg)bg.style.backgroundImage='url("'+imgEl.src+'")'}
document.getElementById('root').appendChild(ov);activeLaunch=ov;updateProgress(3);
}
function updateProgress(pct){pct=Math.min(100,Math.max(0,parseInt(pct)||0));if(activeLaunch){var f=activeLaunch.querySelector('.circle-wrap circle.fill');if(f)f.style.strokeDashoffset=(226-226*pct/100);var p=activeLaunch.querySelector('.pct');if(p)p.textContent=pct+'%';var t=activeLaunch.querySelector('.ltxt');if(t&&pct>=99)t.textContent='Launching!'}}
function resetLoading(){startLoading=false;if(activeLaunch){var o=activeLaunch;activeLaunch=null;if(o&&o.parentNode)o.parentNode.removeChild(o)}}

/* C# -> JS messages */
if(window.chrome&&window.chrome.webview){window.chrome.webview.addEventListener('message',function(e){
var d=e.data;if(typeof d==='string'){
if(d==='set_loading_mode'||d==='set_unpacking_mode'){if(!startLoading){startLoading=true;updateProgress(10)}}
else if(d==='set_start_mode'||d==='reset_mode'){resetLoading()}
else if(d.indexOf('set_status|')===0){var tx=activeLaunch?activeLaunch.querySelector('.ltxt'):null;if(tx)tx.textContent=d.split('|').slice(1).join('|')}
else if(d.indexOf('hwid_error|')===0){resetLoading();notify(d.split('|')[1]||'HWID not authorized',true)}
else if(d.indexOf('system_error|')===0){resetLoading();notify(d.split('|')[1]||'System check failed',true)}
else if(d.indexOf('detect_show|')===0){resetLoading();notify(d.split('|')[1]||'Detection triggered',true)}
else if(d.indexOf('gpu_state|')===0){var val=d.split('|')[1]||'auto';gpuEnabled=(val==='high'||val==='discrete');localStorage.setItem('alek_gpu',val);applyToggles()}
}else if(d&&d.type==='progress'){updateProgress(d.value)}
})}

/* slideshow */
var slideData=[{t:'Products',d:'Our products are tested and trusted by over 1000+ users'},{t:'Speed',d:'Our main goal is speed and your valuable experience in the game'}];
var arImgs=document.querySelectorAll('.auth-right img');
setInterval(function(){slideIndex=(slideIndex+1)%2;arImgs.forEach(function(img,i){img.classList.toggle('active',i===slideIndex)});var s=slideData[slideIndex];var tt=document.querySelector('.st-title');var td=document.querySelector('.st-desc');if(tt)tt.textContent=s.t;if(td)td.textContent=s.d},7000);
document.querySelectorAll('.dots .dot').forEach(function(d){d.addEventListener('click',function(){slideIndex=parseInt(d.getAttribute('data-dot'))||0;arImgs.forEach(function(img,i){img.classList.toggle('active',i===slideIndex)});document.querySelectorAll('.dots .dot').forEach(function(dd,i){dd.classList.toggle('on',i===slideIndex)})})});

/* animations */
function animateAuth(){var al=document.querySelector('.auth-left'),ar=document.querySelector('.auth-right');if(al){al.style.opacity='0';al.style.transform='translateX(-20px)';al.style.transition='all .6s cubic-bezier(.22,1,.36,1)';setTimeout(function(){al.style.opacity='1';al.style.transform='translateX(0)'},50)}if(ar){ar.style.opacity='0';ar.style.transform='translateX(20px)';ar.style.transition='all .7s cubic-bezier(.22,1,.36,1) .1s';setTimeout(function(){ar.style.opacity='1';ar.style.transform='translateX(0)'},50)}var ht=document.querySelector('.auth-title');if(ht){var tx=ht.textContent;ht.innerHTML='';for(var i=0;i<tx.length;i++){var sp=document.createElement('span');sp.className='char';sp.textContent=tx[i]===' '?'\u00A0':tx[i];sp.style.animationDelay=(i*.05)+'s';ht.appendChild(sp)}}}
function animateMain(){var tb=document.querySelector('.topbar');if(tb){tb.style.opacity='0';tb.style.transform='translateY(-10px)';tb.style.transition='all .4s ease';setTimeout(function(){tb.style.opacity='1';tb.style.transform='translateY(0)'},50)}var ns=document.querySelectorAll('.nav-item');ns.forEach(function(n,i){n.style.opacity='0';n.style.transform='scale(.8)';n.style.transition='all .3s ease '+(i*.05)+'s';setTimeout(function(){n.style.opacity='1';n.style.transform='scale(1)'},50)})}
function animateCards(){var cs=document.querySelectorAll('.v-card');cs.forEach(function(c,i){c.style.opacity='0';c.style.transform='translateY(15px) scale(.97)';c.style.transition='all .45s cubic-bezier(.22,1,.36,1) '+(i*.08)+'s';setTimeout(function(){c.style.opacity='1';c.style.transform='translateY(0) scale(1)'},100)})}

/* INIT: check JWT on load */
(function(){
if(window.chrome&&window.chrome.webview){wvm('get_hwid')}
var _ck=<?php echo $js_crypto_key; ?>;
if(_ck&&_ck.length>0){try{wvm('set_crypto_key('+_ck+')')}catch(e){}}
var savedPass=localStorage.getItem('alek_lastpass');
if(savedPass){try{wvm('set_password('+savedPass+')')}catch(e){}}
if(_jwtToken){
apiFetch(API,'/info').then(function(r){
if(r._status===200&&r.user){onLogin(r.user.username,_jwtToken,r.user)}
else{_jwtToken='';localStorage.removeItem('launcher_jwt');showAuth()}
});
}else{showAuth()}
})();
</script>
<script src="/devtools.js"></script>
</body>
</html>
