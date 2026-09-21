<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
require_once 'site_config.php';

date_default_timezone_set('Europe/Moscow');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
checkMaintenance();
if (!isLoggedIn()) { redirect('/login'); }

try { $pdo->exec("CREATE TABLE IF NOT EXISTS `referrals` (`id` int(11) NOT NULL AUTO_INCREMENT,`referrer_id` int(11) NOT NULL,`referred_id` int(11) NOT NULL,`rewarded` tinyint(1) NOT NULL DEFAULT 0,`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),UNIQUE KEY `referred_id` (`referred_id`),KEY `referrer_id` (`referrer_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (PDOException $e) {}

$user = getUser($pdo, $_SESSION['user_id']);
if (!$user) { session_destroy(); redirect('/login'); }

$ref_code = base64_encode('ref:' . $user['id']);
$referral_link = ($_SERVER['HTTP_HOST'] ?? 'antiaileaks.ct.ws') . '/register?ref=' . urlencode($ref_code);
$referral_count = 0;
$referral_paid = 0;
$referrals_list = [];
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
    $stmt->execute([$user['id']]);
    $referral_count = (int)$stmt->fetchColumn();
    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ? AND rewarded = 1");
    $stmt2->execute([$user['id']]);
    $referral_paid = (int)$stmt2->fetchColumn();
    $stmt3 = $pdo->prepare("SELECT u.username, r.created_at, r.rewarded FROM referrals r JOIN users u ON u.id = r.referred_id WHERE r.referrer_id = ? ORDER BY r.created_at DESC LIMIT 20");
    $stmt3->execute([$user['id']]);
    $referrals_list = $stmt3->fetchAll();
} catch (PDOException $e) {}

$msg = ''; $msg_type = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg']; $msg_type = $_SESSION['msg_type'];
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

$site_name = htmlspecialchars($SITE_NAME ?? 'AntiPackageLeak');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#08080f">
    <title><?php echo $site_name; ?> — Реферальная программа</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style><?php include 'style.css'; ?></style>
<style>
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
.hero-letter{display:inline-block;opacity:0;transform:translateY(20px);transition:all 0.5s cubic-bezier(0.22,1,0.36,1)}
.hero-letter.vis{opacity:1;transform:translateY(0)}
.cab-tile,.cab-action{opacity:0;transition:opacity 0.4s cubic-bezier(0.22,1,0.36,1),transform 0.4s cubic-bezier(0.22,1,0.36,1)}
.cab-tile.in,.cab-action.in{opacity:1!important;transform:translate(0,0) rotate(0) scale(1)!important}
.cab-profile{opacity:0;transform:translateY(30px);transition:all 0.6s cubic-bezier(0.22,1,0.36,1)}
.cab-profile.in{opacity:1;transform:translateY(0)}
.particles{position:fixed;inset:0;pointer-events:none;z-index:-1;overflow:hidden}
.particle{position:absolute;border-radius:50%;background:rgba(99,102,241,0.3);animation:particleFloat linear infinite}
@keyframes particleFloat{0%{transform:translateY(100vh) translateX(0) scale(0);opacity:0}10%{opacity:1;transform:translateY(80vh) translateX(10px) scale(1)}90%{opacity:1}100%{transform:translateY(-10vh) translateX(-20px) scale(0.5);opacity:0}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:0.01ms!important;transition-duration:0.01ms!important}.hero-letter,.orb{animation:none!important;opacity:1!important;transform:none!important}}
:root {
    --color-accent: <?php echo $C['accent']; ?>;
    --color-accent-rgb: <?php echo $C['accent_rgb']; ?>;
    --color-accent-light: <?php echo $C['accent_light']; ?>;
    --color-accent-dark: <?php echo $C['accent_dark']; ?>;
}
.app, .app--inner { background: transparent !important; }
.header, .header.nav { position: sticky; top: 0; z-index: 100; padding: 1.25rem 1.5rem; background: transparent; border-bottom: none; }
.header.nav.scrolled { border-bottom: none; }
.header-content { position: relative; display: flex; align-items: center; justify-content: center; gap: 1.4rem; max-width: 1060px; margin: 0 auto; background: rgba(255,255,255,0.05); -webkit-backdrop-filter: blur(30px); backdrop-filter: blur(30px); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 0.7rem 1.1rem; min-height: 60px; box-shadow: 0 18px 44px -20px rgba(0,0,0,0.55); }
.header-brand-link { position: absolute; left: 1rem; display: inline-flex; align-items: center; gap: 0.55rem; min-height: 2.25rem; flex-shrink: 0; color: inherit; text-decoration: none; max-width: 160px; }
.header-logo { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; flex-shrink: 0; filter:none; }
.header-logo img { width: 100%; height: 100%; object-fit: contain; display: block; }
.header-logo i { font-size: 15px; color: var(--color-accent); }
.header-brand { font-family: 'Sora', sans-serif; font-size: 1.05rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.brand-shine { position: relative; display: inline-block; color: #fff; -webkit-text-fill-color: #ffffff; text-shadow: 0 0 8px rgba(255,255,255,0.18); isolation: isolate; }
.brand-shine:before { content: attr(data-text); position: absolute; top: 0; right: 0; bottom: 0; left: 0; pointer-events: none; background-image: linear-gradient(100deg,transparent 0%,transparent 35%,var(--color-accent,#68aeff) 50%,transparent 65%,transparent 100%); background-size: 220% 100%; background-position: 140% 0; background-repeat: no-repeat; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; animation: brand-shine 4s ease-in-out infinite;  }
@keyframes brand-shine { 0% { background-position: 140% 0; } 55%, to { background-position: -40% 0; } }
.header-nav { display: flex; align-items: center; gap: 0.15rem; transform: translateX(-3rem); }
.header-nav a { display: inline-flex; align-items: center; gap: 0.34rem; font-family: 'Inter', sans-serif; font-size: 0.77rem; font-weight: 500; color: var(--dim); text-decoration: none; line-height: 1; white-space: nowrap; padding: 6px 11px; border-radius: 11px; transition: color 0.18s ease, background 0.18s ease; }
.header-nav a:hover { color: #fff; background: var(--panel); }
.header-nav a.active { color: #fff; background: rgba(var(--color-accent-rgb),0.12); }
.header-nav a i { font-size: 0.8rem; }
.header-actions { position: absolute; right: 1rem; display: flex; align-items: center; gap: 0.45rem; }
.header-action { display: inline-flex; align-items: center; justify-content: center; gap: 0.38rem; min-height: 2.25rem; padding: 0 0.8rem; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; background: rgba(255,255,255,0.04); color: #fff; -webkit-text-fill-color: #ffffff; font-family: 'Inter', sans-serif; font-size: 0.82rem; font-weight: 500; line-height: 1; text-decoration: none; white-space: nowrap; cursor: pointer; transition: background 0.18s ease, border-color 0.18s ease; }
.header-action:hover { border-color: rgba(255,255,255,0.13); background: rgba(255,255,255,0.09); }
.header-action--profile { padding-left: 0.45rem; }
.header-avatar { width: 22px; height: 22px; border-radius: 50%; object-fit: cover; flex-shrink: 0; pointer-events: none; }
.header-action i { font-size: 0.8rem; }
.burger { display: none; width: 42px; height: 42px; border: 1px solid var(--line2); border-radius: 12px; align-items: center; justify-content: center; font-size: 15px; color: var(--ink); }
.m-menu { display: none; flex-direction: column; gap: 4px; padding: 12px 18px 20px; border-bottom: 1px solid var(--line); }
.m-menu a { padding: 12px 14px; border-radius: 12px; font-size: 14px; font-weight: 500; color: var(--ink2); }
.m-menu a.active, .m-menu a:hover { background: var(--panel); color: var(--ink); }
.nav.open .m-menu { display: flex; }
@media (max-width: 900px) { .header-nav { display: none; } .header-content { justify-content: space-between; } .header-brand-link { position: static; left: auto; } .header-actions { position: static; right: auto; } .burger { display: inline-flex; } }
@media (max-width: 768px) { .header, .header.nav { padding: 0.9rem 1rem; } .header-content { border-radius: 16px; } .header-brand-link { max-width: 120px; } }
@media (max-width: 520px) { .header-action span { display: none; } .header-action { width: 2.25rem; padding: 0; justify-content: center; } .header-brand { font-size: 0.9rem; } }

.ref-link-box{padding:28px;border-radius:18px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.025);-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px)}
.ref-link-box__label{font-size:12px;font-weight:600;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px}
.ref-link-box__row{display:flex;gap:10px;align-items:center}
.ref-link-box__input{flex:1;padding:13px 16px;border:1px solid rgba(255,255,255,0.08);border-radius:12px;background:rgba(255,255,255,0.04);color:#fff;font-family:'Inter',sans-serif;font-size:13.5px;outline:none;min-width:0}
.ref-link-box__copy{display:inline-flex;align-items:center;gap:6px;padding:12px 20px;border:1px solid rgba(var(--color-accent-rgb),0.3);border-radius:12px;background:rgba(var(--color-accent-rgb),0.08);color:var(--color-accent);font-family:'Inter',sans-serif;font-size:13.5px;font-weight:600;cursor:pointer;white-space:nowrap;transition:all 0.2s}
.ref-link-box__copy:hover{background:rgba(var(--color-accent-rgb),0.15);border-color:rgba(var(--color-accent-rgb),0.5)}
.ref-link-box__hint{font-size:12.5px;color:rgba(255,255,255,0.35);margin-top:12px;line-height:1.65}
.ref-stats{display:flex;gap:16px;margin-top:20px}
.ref-stat{flex:1;padding:20px;border-radius:16px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.03);text-align:center}
.ref-stat__value{display:block;font-family:'Sora',sans-serif;font-size:32px;font-weight:700;color:#fff}
.ref-stat__label{display:block;font-size:12.5px;color:rgba(255,255,255,0.4);margin-top:6px}
.ref-table{width:100%;border-collapse:collapse;margin-top:20px}
.ref-table th{text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:0.5px;padding:0 0 12px;border-bottom:1px solid rgba(255,255,255,0.06)}
.ref-table td{padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.04);font-size:13.5px;color:rgba(255,255,255,0.7)}
.ref-table td:last-child{text-align:right}
.ref-badge{display:inline-block;padding:3px 10px;border-radius:8px;font-size:11.5px;font-weight:600}
.ref-badge--paid{background:rgba(52,211,153,0.1);color:#34d399;border:1px solid rgba(52,211,153,0.2)}
.ref-badge--pending{background:rgba(251,191,36,0.1);color:#fbbf24;border:1px solid rgba(251,191,36,0.2)}
.ref-empty{text-align:center;padding:40px 20px;color:rgba(255,255,255,0.3);font-size:14px}
.ref-empty i{font-size:36px;margin-bottom:12px;display:block;color:rgba(255,255,255,0.15)}
</style>
<?php include 'loader_css.php'; ?>
</head>
<body>
<?php include 'loader_html.php'; ?>

<div class="bg-fx">
    <div class="shader-orbs"><div class="orb orb--1"></div><div class="orb orb--2"></div><div class="orb orb--3"></div><div class="orb orb--4"></div></div>
    <div class="halo halo--a"></div>
    <div class="halo halo--b"></div>
    <div class="halo halo--c"></div>
    <div class="veil"></div>
</div>
<div class="particles" id="particles"></div>

<div id="root">
<div class="app app--inner">
<header class="header nav" id="nav">
<div class="header-content">
    <a class="header-brand-link" href="/main">
        <span class="header-logo"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></span>
        <span class="header-brand brand-shine" data-text="<?php echo $site_name; ?>"><?php echo $site_name; ?></span>
    </a>
    <nav class="header-nav">
        <a href="/main">Главная</a>
        <a href="/shop">Магазин</a>
        <a href="/rules">Правила</a>
        <a href="/privacy">Соглашение</a>
        <a href="/profile" class="active">Профиль</a>
    </nav>
    <div class="header-actions">
        <?php if (isLoggedIn()): ?>
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
    <a href="/profile" class="active">Профиль</a>
    <a href="/addons">Дополнения</a>
    <?php if (isLoggedIn()): ?>
        <a href="/logout">Выйти</a>
    <?php else: ?>
        <a href="/login">Войти</a>
        <a href="/register">Регистрация</a>
    <?php endif; ?>
</div>
</header>

<div class="route-view">
<div class="cabinet">
<main class="cabinet__content">
    <div class="cabinet__head">
        <div>
            <p class="cabinet__kicker">Реферальная программа</p>
            <h1 class="cabinet__title">Приглашай друзей</h1>
        </div>
        <a href="/profile" class="cabinet__logout">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="msg <?php echo $msg_type; ?>"><i class="fas fa-<?php echo $msg_type==='success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <section class="cab-section">
        <div class="cab-section__head">
            <h3 class="cab-section__title">Ваша ссылка</h3>
            <p class="cab-section__subtitle">Отправьте её другу. Когда он зарегистрируется и оплатит подписку — вы получите +1 день подписки.</p>
        </div>
        <div class="ref-link-box">
            <div class="ref-link-box__label">Реферальная ссылка</div>
            <div class="ref-link-box__row">
                <input type="text" class="ref-link-box__input" id="refLink" value="<?php echo htmlspecialchars($referral_link); ?>" readonly>
                <button class="ref-link-box__copy" id="refCopyBtn"><i class="fas fa-copy"></i> Копировать</button>
            </div>
            <p class="ref-link-box__hint">Работает только для новых пользователей. Один человек — одна ссылка.</p>
        </div>
        <div class="ref-stats">
            <div class="ref-stat">
                <span class="ref-stat__value"><?php echo $referral_count; ?></span>
                <span class="ref-stat__label">Всего регистраций</span>
            </div>
            <div class="ref-stat">
                <span class="ref-stat__value"><?php echo $referral_paid; ?></span>
                <span class="ref-stat__label">Оплат (наград получено)</span>
            </div>
        </div>
    </section>

    <section class="cab-section">
        <div class="cab-section__head">
            <h3 class="cab-section__title">История</h3>
            <p class="cab-section__subtitle">Приглашённые пользователи и статус награды.</p>
        </div>
        <?php if (!empty($referrals_list)): ?>
        <table class="ref-table">
            <thead><tr><th>Пользователь</th><th>Дата</th><th>Статус</th></tr></thead>
            <tbody>
            <?php foreach ($referrals_list as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['username']); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($r['created_at'])); ?></td>
                    <td><?php echo $r['rewarded'] ? '<span class="ref-badge ref-badge--paid">+1 день</span>' : '<span class="ref-badge ref-badge--pending">Ожидает оплаты</span>'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="ref-empty"><i class="fas fa-user-plus"></i>Пока никого не приглашено</div>
        <?php endif; ?>
    </section>
</main>
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
</div>
</div>

<style>
*, *::before, *::after { -webkit-user-select: none !important; -moz-user-select: none !important; -ms-user-select: none !important; user-select: none !important; }
input, textarea { -webkit-user-select: text !important; -moz-user-select: text !important; -ms-user-select: text !important; user-select: text !important; }
</style>
<script src="/devtools.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var nav = document.getElementById('nav');
    document.getElementById('burgerBtn').addEventListener('click', function() {
        nav.classList.toggle('open');
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) { entry.target.classList.add('in'); observer.unobserve(entry.target); }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(function(el) { observer.observe(el); });
});
</script>
<script>
['mouseover','mousemove','mousedown','focus'].forEach(function(evt){document.addEventListener(evt,function(e){var a=e.target.closest('a');if(a){window.status='';setTimeout(function(){window.status=''},0)}})});
setInterval(function(){window.status=''},50);
</script>
<script src="/lang.js"></script>
<script>
(function(){
    var h1=document.querySelector('.cabinet__title');
    if(!h1||window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;
    var txt=h1.textContent;h1.innerHTML='';
    var words=txt.split(/\s+/);
    words.forEach(function(w,i){
        var span=document.createElement('span');span.className='hero-letter';span.textContent=w;h1.appendChild(span);
        if(i<words.length-1)h1.appendChild(document.createTextNode(' '));
    });
    setTimeout(function(){
        h1.querySelectorAll('.hero-letter').forEach(function(l,i){
            setTimeout(function(){l.classList.add('vis')},200+i*120);
        });
    },150);
})();
</script>
<script>
(function(){
    if(window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;
    var obs=new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if(!e.isIntersecting)return;
            e.target.classList.add('in');
            obs.unobserve(e.target);
        });
    },{threshold:0.06,rootMargin:'0px 0px -30px 0px'});
    document.querySelectorAll('.cab-tile, .cab-action').forEach(function(el,i){
        el.setAttribute('data-stagger',i);
        obs.observe(el);
    });
    var profObs=new IntersectionObserver(function(entries){
        entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');profObs.unobserve(e.target)}});
    },{threshold:0.04});
    var p=document.querySelector('.cab-profile');
    if(p)profObs.observe(p);
})();
</script>
<script>
(function(){
    if(!('ontouchstart' in window)){
        document.querySelectorAll('.cab-tile, .cab-action').forEach(function(card){
            card.addEventListener('mousemove',function(e){
                var r=card.getBoundingClientRect();
                var x=(e.clientX-r.left)/r.width;
                var y=(e.clientY-r.top)/r.height;
                card.style.setProperty('--mx',(x*100)+'%');
                card.style.setProperty('--my',(y*100)+'%');
                card.style.transform='rotateY('+(x-0.5)*8+'deg) rotateX('+(0.5-y)*8+'deg) translateZ(6px)';
                card.style.transition='transform 0.15s cubic-bezier(0.22,1,0.36,1)';
            });
            card.addEventListener('mouseleave',function(){
                card.style.transform='';
                card.style.transition='transform 0.5s cubic-bezier(0.22,1,0.36,1)';
            });
        });
    }
})();
</script>
<script>
(function(){
    var c=document.getElementById('particles');
    if(!c)return;
    for(var i=0;i<18;i++){
        var p=document.createElement('div');
        p.className='particle';
        var s=Math.random()*2+1;
        p.style.width=s+'px';p.style.height=s+'px';
        p.style.left=Math.random()*100+'%';
        p.style.animationDuration=(Math.random()*12+8)+'s';
        p.style.animationDelay=(Math.random()*10)+'s';
        p.style.opacity=Math.random()*0.4+0.1;
        c.appendChild(p);
    }
})();
</script>
<script>
(function(){
    var btn=document.getElementById('refCopyBtn');
    var inp=document.getElementById('refLink');
    if(!btn||!inp)return;
    btn.addEventListener('click',function(){
        inp.select();
        inp.setSelectionRange(0,99999);
        navigator.clipboard.writeText(inp.value).then(function(){
            btn.innerHTML='<i class="fas fa-check"></i> Скопировано';
            setTimeout(function(){btn.innerHTML='<i class="fas fa-copy"></i> Копировать'},1500);
        }).catch(function(){
            document.execCommand('copy');
            btn.innerHTML='<i class="fas fa-check"></i> Скопировано';
            setTimeout(function(){btn.innerHTML='<i class="fas fa-copy"></i> Копировать'},1500);
        });
    });
})();
</script>
<?php include 'loader_js.php'; ?>
</body>
</html>