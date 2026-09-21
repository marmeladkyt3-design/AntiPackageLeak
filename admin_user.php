<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
require_once 'site_config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isLoggedIn()) redirect('/login.php');
$current_user = getUser($pdo, $_SESSION['user_id']);
if (!userHasRight($pdo, $current_user, 'can_admin')) redirect('/profile.php');

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) redirect('/admin.php');

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) { $user = null; }

if (!$user) redirect('/admin.php');

$settings = getLauncherSettings($pdo);
if (!empty($settings['site_name'])) $SITE_NAME = $settings['site_name'];
$site_name = htmlspecialchars($SITE_NAME ?? '');

$role_name = htmlspecialchars($user['role']);
try {
    $all_roles = getRolesMap($pdo);
    if (isset($all_roles[$user['role']])) $role_name = htmlspecialchars($all_roles[$user['role']]['name']);
} catch (PDOException $e) {}

$hs = !empty($user['subscription_end']) && strtotime($user['subscription_end']) > time();
$fv = !empty($user['subscription_end']) && strtotime($user['subscription_end']) > strtotime('9998-01-01');
$bn = ($user['banned'] ?? 0) == 1;

$hwid_masked = !empty($user['hwid']) ? substr($user['hwid'],0,8).'...'.substr($user['hwid'],-4) : '';

$last_launch = null;
try {
    $ls = $pdo->prepare("SELECT MAX(created_at) FROM launcher_stats WHERE user_id=?");
    $ls->execute([$user_id]);
    $last_launch = $ls->fetchColumn();
} catch (PDOException $e) {}

$reg_date = $user['created_at'] ?? $user['last_login'] ?? null;
if (!empty($reg_date) && preg_match('/^\d{4}-\d{2}-\d{2}/', $reg_date)) {
    $reg_date = date('d.m.Y H:i', strtotime($reg_date));
} else {
    $reg_date = null;
}
$C_defaults = ['accent'=>'#6366f1','accent_rgb'=>'99,102,241','accent_light'=>'#818cf8','accent_dark'=>'#4f46e5','bg'=>'#0a0a14','bg2'=>'#111127','panel'=>'rgba(255,255,255,0.04)','panel_h'=>'rgba(255,255,255,0.07)','line'=>'rgba(255,255,255,0.06)','line2'=>'rgba(255,255,255,0.08)','ink'=>'#fff','ink2'=>'#c8cdd8','dim'=>'#8892a8','faint'=>'#4a5568','success'=>'#10b981','danger'=>'#ef4444'];
$C = [];
foreach($C_defaults as $ck => $cv){$C[$ck] = isset($GLOBALS['C'][$ck]) ? $GLOBALS['C'][$ck] : $cv;}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#08080f">
    <title><?php echo htmlspecialchars($user['username']); ?> — Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:<?php echo $C['bg']; ?> url('assets/img/background.jpg') center/cover no-repeat fixed;color:<?php echo $C['ink2']; ?>;line-height:1.65;overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:9px}::-webkit-scrollbar-track{background:<?php echo $C['bg']; ?>}::-webkit-scrollbar-thumb{background:#232a44;border-radius:9px;border:2px solid <?php echo $C['bg']; ?>}
a{color:inherit;text-decoration:none}
.bg-fx{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;background-image:url('assets/img/background.jpg');background-size:cover;background-position:center}
.bg-fx::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,0.5);-webkit-backdrop-filter:blur(16px) saturate(1.2);backdrop-filter:blur(16px) saturate(1.2)}

.header{position:sticky;top:0;z-index:100;padding:1.25rem 1.5rem;background:transparent}
.header-content{position:relative;display:flex;align-items:center;justify-content:center;gap:1.4rem;max-width:1060px;margin:0 auto;background:rgba(255,255,255,0.05);-webkit-backdrop-filter:blur(30px);backdrop-filter:blur(30px);border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:0.7rem 1.1rem;min-height:60px;box-shadow:0 18px 44px -20px rgba(0,0,0,0.55)}
.header-brand-link{position:absolute;left:1rem;display:inline-flex;align-items:center;gap:0.55rem;color:inherit;text-decoration:none}
.header-logo{display:flex;align-items:center;justify-content:center;width:30px;height:30px}
.header-logo img{width:100%;height:100%;object-fit:contain}
.header-brand{font-family:'Sora',sans-serif;font-size:1.05rem;font-weight:700;color:#fff}
.header-actions{position:absolute;right:1rem;display:flex;align-items:center;gap:0.45rem}
.header-action{display:inline-flex;align-items:center;gap:0.38rem;min-height:2.25rem;padding:0 0.8rem;border:1px solid rgba(255,255,255,0.08);border-radius:12px;background:rgba(255,255,255,0.04);color:#fff;font-family:'Inter',sans-serif;font-size:0.82rem;font-weight:500;cursor:pointer;transition:background 0.18s ease}
.header-action:hover{border-color:rgba(255,255,255,0.13);background:rgba(255,255,255,0.09)}
.header-action i{font-size:0.8rem}

.cabinet{max-width:600px;margin:0 auto;padding:0 20px 60px}
.cabinet__head{text-align:center;margin-bottom:28px;opacity:1}
.cabinet__kicker{font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:<?php echo $C['dim']; ?>;margin-bottom:6px;font-weight:600}
.cabinet__title{font-family:'Sora',sans-serif;font-size:clamp(28px,5vw,36px);font-weight:800;color:#fff}

.user-card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px 32px 36px;text-align:center;position:relative;overflow:hidden;-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px)}
.user-card::before{content:'';position:absolute;top:0;left:0;right:0;height:120px;background:linear-gradient(135deg,rgba(<?php echo $C['accent_rgb']; ?>,0.12),rgba(<?php echo $C['accent_rgb']; ?>,0.04));pointer-events:none}
.user-avatar{width:88px;height:88px;border-radius:50%;border:3px solid rgba(255,255,255,0.12);margin:0 auto 16px;position:relative;z-index:1;background:<?php echo $C['panel']; ?>;display:flex;align-items:center;justify-content:center;font-size:32px;color:<?php echo $C['faint']; ?>;overflow:hidden}
.user-avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover}
.user-name{font-family:'Sora',sans-serif;font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:4px;position:relative;z-index:1}
.user-email{font-size:13px;color:<?php echo $C['faint']; ?>;margin-bottom:16px;position:relative;z-index:1}
.user-badges{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;position:relative;z-index:1;margin-bottom:24px}
.user-badge{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:99px;font-size:11.5px;font-weight:600;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04)}
.user-badge.role-admin{color:#a78bfa;border-color:rgba(139,92,246,0.3);background:rgba(139,92,246,0.1)}
.user-badge.banned{color:#f87171;border-color:rgba(248,113,113,0.3);background:rgba(248,113,113,0.1)}
.user-badge.sub-active{color:#34d399;border-color:rgba(52,211,153,0.3);background:rgba(52,211,153,0.1)}
.user-badge.sub-none{color:<?php echo $C['faint']; ?>}

.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;text-align:left}
.info-item{background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:14px;padding:14px 16px}
.info-item.full{grid-column:1/-1}
.info-label{font-size:11px;color:<?php echo $C['faint']; ?>;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;font-weight:500}
.info-value{font-size:13.5px;color:#fff;font-weight:500;word-break:break-all}
.info-value.mono{font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:-0.02em}
.info-value.faint{color:<?php echo $C['faint']; ?>}

.back-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:14px;font-size:13px;font-weight:600;background:linear-gradient(135deg,<?php echo $C['accent']; ?>,#8b5cf6);color:#fff;text-decoration:none;transition:all 0.25s;box-shadow:0 8px 24px -6px rgba(<?php echo $C['accent_rgb']; ?>,0.5);margin-top:28px}
.back-btn:hover{transform:translateY(-2px);box-shadow:0 12px 32px -6px rgba(<?php echo $C['accent_rgb']; ?>,0.65)}

.footer{position:relative;background:transparent;padding:0 20px 32px;margin-top:80px;border-top:0}
.footer__inner{width:min(1100px,calc(100vw - 40px));margin:0 auto;padding:28px 28px 20px;display:grid;grid-template-columns:minmax(0,1.3fr) auto auto auto;gap:40px;justify-items:start;text-align:left;border-radius:24px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.02);-webkit-backdrop-filter:blur(18px);backdrop-filter:blur(18px)}
.footer__brand-block{display:flex;flex-direction:column;align-items:flex-start}
.footer__brand{display:inline-flex;align-items:center;gap:8px}
.footer__mark{display:flex;align-items:center;justify-content:center}
.footer__mark img{width:24px;height:24px;object-fit:contain}
.footer__brand-name{font-family:Inter,sans-serif;font-size:1.1rem;font-weight:500;color:#fff}
.footer__copyright{margin-top:12px;font-size:.74rem;color:rgba(255,255,255,.4)}
.footer__socials{display:flex;gap:10px;margin-top:16px}
.footer__social{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.8);font-size:16px;transition:border-color .2s,color .2s,background .2s}
.footer__social:hover{color:#fff;background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15)}
.footer__nav-group{min-width:120px}
.footer__title{font-size:.95rem;font-weight:500;color:#fff}
.footer__links{display:flex;flex-direction:column;gap:8px;margin-top:12px}
.footer__link{font-size:.8rem;color:rgba(255,255,255,.5);text-decoration:none;transition:color .22s}
.footer__link:hover{color:#fff}
.footer__credit{grid-column:1/-1;margin-top:10px;padding-top:18px;border-top:1px solid rgba(255,255,255,.07);text-align:center;font-size:.78rem;color:rgba(255,255,255,.4)}
.footer__credit-link{color:rgba(255,255,255,.6);text-decoration:none;transition:color .22s}
.footer__credit-link:hover{color:#fff}
@media(max-width:760px){.footer__inner{padding:20px 20px 14px;grid-template-columns:1fr 1fr;gap:28px}}
@media(max-width:640px){.footer{padding:0 16px 20px}.footer__inner{padding:20px 16px 14px;grid-template-columns:1fr;gap:24px}}
@media(max-width:520px){.info-grid{grid-template-columns:1fr}.user-card{padding:32px 20px 28px}}
@media(prefers-reduced-motion:reduce){*{animation-duration:0.01ms!important;transition-duration:0.01ms!important}}
</style>
<?php include 'loader_css.php'; ?>
</head>
<body>
<?php include 'loader_html.php'; ?>
<div class="bg-fx"></div>

<div class="header">
<div class="header-content">
    <a class="header-brand-link" href="/admin.php">
        <span class="header-logo"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></span>
        <span class="header-brand"><?php echo $site_name; ?></span>
    </a>
    <div class="header-actions">
        <a href="/admin.php?tab=users" class="header-action"><i class="fas fa-arrow-left"></i><span>Админ</span></a>
        <a href="/profile.php" class="header-action"><i class="fas fa-user"></i><span>Profile</span></a>
    </div>
</div>
</div>

<main class="cabinet">
    <div class="cabinet__head">
        <p class="cabinet__kicker">Profile of user</p>
        <h1 class="cabinet__title"><?php echo htmlspecialchars($user['username']); ?></h1>
    </div>

    <div class="user-card">
        <div class="user-avatar">
            <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="" onerror="this.style.display='none';this.parentNode.innerHTML='<i class=\'fas fa-user\'></i>'">
            <?php else: ?>
                <img src="/assets/ava.png" alt="" onerror="this.style.display='none';this.parentNode.innerHTML='<i class=\'fas fa-user\'></i>'">
            <?php endif; ?>
        </div>
        <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
        <div class="user-badges">
            <span class="user-badge role-admin"><i class="fas fa-shield-alt"></i> <?php echo $role_name; ?></span>
            <span class="user-badge">UID #<?php echo (int)$user['id']; ?></span>
            <?php if ($bn): ?>
                <span class="user-badge banned"><i class="fas fa-ban"></i> Забанен</span>
            <?php endif; ?>
            <?php if ($fv): ?>
                <span class="user-badge sub-active"><i class="fas fa-infinity"></i> Навсегда</span>
            <?php elseif ($hs): ?>
                <span class="user-badge sub-active"><i class="fas fa-crown"></i> до <?php echo date('d.m.Y', strtotime($user['subscription_end'])); ?></span>
            <?php else: ?>
                <span class="user-badge sub-none"><i class="fas fa-times"></i> Без подписки</span>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Registration</div>
                <div class="info-value"><?php echo $reg_date ? $reg_date : '<span class="faint">—</span>'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Last login</div>
                <div class="info-value"><?php echo !empty($user['last_login']) ? date('d.m.Y H:i', strtotime($user['last_login'])) : '<span class="faint">—</span>'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Last IP</div>
                <div class="info-value mono"><?php echo !empty($user['last_ip']) ? htmlspecialchars($user['last_ip']) : '<span class="faint">—</span>'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Last activity</div>
                <div class="info-value"><?php echo !empty($user['last_activity']) ? date('d.m.Y H:i', strtotime($user['last_activity'])) : '<span class="faint">—</span>'; ?></div>
            </div>
            <div class="info-item full">
                <div class="info-label">HWID</div>
                <div class="info-value mono"><?php echo !empty($hwid_masked) ? htmlspecialchars($hwid_masked) : '<span class="faint">—</span>'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">UUID</div>
                <div class="info-value mono"><?php echo !empty($user['uuid']) ? htmlspecialchars(substr($user['uuid'],0,16).'...') : '<span class="faint">—</span>'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Launches</div>
                <div class="info-value"><?php echo number_format($user['total_launches'] ?? 0); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Last launcher launch</div>
                <div class="info-value"><?php echo $last_launch ? date('d.m.Y H:i', strtotime($last_launch)) : '<span class="faint">—</span>'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Subscription until</div>
                <div class="info-value"><?php echo $fv ? 'NAVS' : ($hs ? date('d.m.Y H:i', strtotime($user['subscription_end'])) : '<span class="faint">None</span>'); ?></div>
            </div>
            <?php if ($bn): ?>
            <div class="info-item full">
                <div class="info-label">Ban reason</div>
                <div class="info-value" style="color:#f87171"><?php echo htmlspecialchars($user['ban_reason'] ?? 'Not specified'); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Banned at</div>
                <div class="info-value"><?php echo !empty($user['banned_at']) ? date('d.m.Y H:i', strtotime($user['banned_at'])) : '—'; ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="text-align:center">
        <a href="/admin.php?tab=users" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Admin</a>
    </div>
</main>

<footer class="footer">
<div class="footer__inner">
    <div class="footer__brand-block">
        <div class="footer__brand">
            <div class="footer__mark"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></div>
            <span class="footer__brand-name"><?php echo $site_name; ?></span>
        </div>
        <p class="footer__copyright">&copy; <?php echo $site_name; ?> <?php echo date('Y'); ?>. All rights reserved.</p>
        <div class="footer__socials">
            <a class="footer__social" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-telegram"></i></a>
        </div>
    </div>
    <div class="footer__nav-group">
        <h3 class="footer__title">Navigation</h3>
        <nav class="footer__links">
            <a class="footer__link" href="/main.php">Home</a>
            <a class="footer__link" href="/shop.php">Shop</a>
            <a class="footer__link" href="/rules.php">Rules</a>
            <a class="footer__link" href="/privacy.php">Privacy</a>
        </nav>
    </div>
    <div class="footer__nav-group">
        <h3 class="footer__title">Documents</h3>
        <nav class="footer__links">
            <a class="footer__link" href="/privacy.php">Privacy Policy</a>
            <a class="footer__link" href="/rules.php">Terms of Service</a>
        </nav>
    </div>
    <div class="footer__nav-group">
        <h3 class="footer__title">Support</h3>
        <nav class="footer__links">
            <a class="footer__link" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank">Telegram</a>
        </nav>
    </div>
    <p class="footer__credit">made by <a class="footer__credit-link" href="https://t.me/kodexnull" target="_blank">kodexnull</a> special for <a class="footer__credit-link" href="<?php echo htmlspecialchars($TELEGRAM_LINK ?? '#'); ?>" target="_blank"><?php echo $site_name; ?></a></p>
</div>
</footer>
</div>

<script src="/devtools.js"></script>
<script src="/lang.js"></script>
<?php include 'loader_js.php'; ?>
</body>
</html>
