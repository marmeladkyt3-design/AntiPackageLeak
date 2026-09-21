<?php
require_once 'sdfsdfdsfsdfsdfsdfsdfsdf2342234234234cxvcvcvbcvbcvb.php';
require_once 'colors_loader.php';
require_once 'site_config.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isLoggedIn()) redirect('/login.php');
$current_user = getUser($pdo, $_SESSION['user_id']);
if (!userHasRight($pdo, $current_user, 'can_admin')) redirect('/profile.php');

/* ═══ CSRF ═══ */
function csT($a){if(!isset($_SESSION['adm']))$_SESSION['adm']=[];if(!isset($_SESSION['adm'][$a]))$_SESSION['adm'][$a]=bin2hex(random_bytes(16));return $_SESSION['adm'][$a];}
function csV($a){$t=$_POST['csrf']??$_GET['csrf']??'';return isset($_SESSION['adm'][$a])&&hash_equals($_SESSION['adm'][$a],$t);}
function csF($a){return '<input type="hidden" name="csrf" value="'.htmlspecialchars(csT($a)).'">';}
function csL($u,$a){return $u.(strpos($u,'?')!==false?'&':'?').'csrf='.urlencode(csT($a));}

$msg='';$msg_type='';

/* ═══ HANDLERS ═══ */
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(($_POST['action']??'')==='generate_key'&&csV('generate_key')){
        $d=max(1,(int)$_POST['days']);$n=max(1,min(100,(int)$_POST['amount']));
        for($i=0;$i<$n;$i++){$k='ATTACK-'.strtoupper(bin2hex(random_bytes(2))).'-'.strtoupper(bin2hex(random_bytes(2))).'-'.strtoupper(bin2hex(random_bytes(2)));$pdo->prepare("INSERT INTO license_keys(`key`,days)VALUES(?,?)")->execute([$k,$d]);}
        $msg="Создано $n ключей на $d дней";$msg_type='success';logAdminAction($pdo,$current_user['id'],$current_user['username'],'create_keys',null,null,"$n ключей");
    }
    if(($_POST['action']??'')==='change_role'&&csV('change_role')){
        $uid=(int)$_POST['user_id'];$role=$_POST['role']??'user';$roles=getRolesMap($pdo);
        if(isset($roles[$role])){$pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role,$uid]);$msg="Роль → ".$roles[$role]['name'];$msg_type='success';$t=$pdo->prepare("SELECT username FROM users WHERE id=?");$t->execute([$uid]);logAdminAction($pdo,$current_user['id'],$current_user['username'],'change_role',$t->fetchColumn(),$uid);}
    }
    if(($_POST['action']??'')==='change_pw'&&csV('change_pw')){
        $uid=(int)$_POST['user_id'];$pw=$_POST['new_password']??'';
        if(strlen($pw)<6){$msg='Минимум 6 символов';$msg_type='error';}
        else{$pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($pw,PASSWORD_DEFAULT),$uid]);$msg="Пароль #$uid изменён";$msg_type='success';$t=$pdo->prepare("SELECT username FROM users WHERE id=?");$t->execute([$uid]);logAdminAction($pdo,$current_user['id'],$current_user['username'],'change_password',$t->fetchColumn(),$uid);}
    }
    if(($_POST['action']??'')==='create_promo'&&csV('create_promo')){
        $code=trim($_POST['promo_code']??'');$dt=$_POST['discount_type']??'percent';$dv=max(0,(float)($_POST['discount_value']??0));$mp=max(0,(float)($_POST['min_purchase']??0));$mu=max(0,(int)($_POST['max_uses']??0));$exp=!empty($_POST['expires_at'])?$_POST['expires_at']:null;
        if($code==='')$code='PROMO-'.strtoupper(bin2hex(random_bytes(3)));
        $ch=$pdo->prepare("SELECT id FROM promo_codes WHERE code=?");$ch->execute([$code]);
        if($ch->fetch()){$msg="«$code» уже есть";$msg_type='error';}
        else{$pdo->prepare("INSERT INTO promo_codes(code,discount_type,discount_value,min_purchase,max_uses,expires_at)VALUES(?,?,?,?,?,?)")->execute([$code,$dt,$dv,$mp,$mu,$exp]);$msg="Промо «$code» создан";$msg_type='success';logAdminAction($pdo,$current_user['id'],$current_user['username'],'create_promo',null,null,$code);}
    }
    if(isset($_POST['save_settings'])&&csV('save_settings')){
        foreach(['maintenance_mode','maintenance_message','site_name','current_version','launcher_download_url','discord_link','telegram_link','youtube_link','site_url'] as $f){
            $v=($f==='maintenance_mode')?(isset($_POST[$f])?'1':'0'):($_POST[$f]??'');
            try{$pdo->prepare("UPDATE launcher_settings SET setting_value=? WHERE setting_key=?")->execute([$v,$f]);}catch(PDOException $e){}
        }
        $msg='Настройки сохранены';$msg_type='success';logAdminAction($pdo,$current_user['id'],$current_user['username'],'save_settings',null,null);
    }
    if(isset($_POST['save_shop'])&&csV('save_shop')){
        $pid=(int)($_POST['plan_id']??0);$name=trim($_POST['name']??'');$price=(float)($_POST['price']??0);$badge=trim($_POST['badge']??'');$feat=trim($_POST['features']??'');$act=isset($_POST['is_active'])?1:0;$sort=(int)($_POST['sort_order']??0);
        if($pid>0){$pdo->prepare("UPDATE shop_plans SET name=?,price=?,badge=?,features=?,is_active=?,sort_order=? WHERE id=?")->execute([$name,$price,$badge,$feat,$act,$sort,$pid]);$msg='План обновлён';}
        else{$pdo->prepare("INSERT INTO shop_plans(name,price,badge,features,is_active,sort_order)VALUES(?,?,?,?,?,?)")->execute([$name,$price,$badge,$feat,$act,$sort]);$msg='План создан';}
        $msg_type='success';logAdminAction($pdo,$current_user['id'],$current_user['username'],$pid>0?'update_shop':'create_shop',null,null,$name);
    }
    if(isset($_POST['delete_shop_plan'])&&csV('delete_shop_plan')){$pdo->prepare("DELETE FROM shop_plans WHERE id=?")->execute([(int)$_POST['delete_shop_plan']]);$msg='План удалён';$msg_type='success';}
}
if(isset($_GET['give_sub'])&&csV('give_sub')){
    $uid=(int)$_GET['give_sub'];$days=isset($_GET['days'])?(int)$_GET['days']:30;
    if($days===99999){$ne='9999-12-31 23:59:59';}
    else{$r=$pdo->prepare("SELECT subscription_end FROM users WHERE id=?");$r->execute([$uid]);$r=$r->fetch();$ce=(!empty($r['subscription_end'])&&strtotime($r['subscription_end'])>time()&&strtotime($r['subscription_end'])<strtotime('9999-01-01'))?strtotime($r['subscription_end']):time();$ne=date('Y-m-d H:i:s',$ce+($days*86400));}
    $pdo->prepare("UPDATE users SET subscription_end=?,role='user' WHERE id=?")->execute([$ne,$uid]);$msg="Подписка +".($days===99999?'НАВСЕГДА':"$daysд");$msg_type='success';
    $t=$pdo->prepare("SELECT username FROM users WHERE id=?");$t->execute([$uid]);logAdminAction($pdo,$current_user['id'],$current_user['username'],'give_sub',$t->fetchColumn(),$uid,"$days дней");
}
if(isset($_GET['remove_sub'])&&csV('remove_sub')){$uid=(int)$_GET['remove_sub'];$pdo->prepare("UPDATE users SET subscription_end=NULL WHERE id=?")->execute([$uid]);$msg="Подписка #$uid снята";$msg_type='success';}
if(isset($_GET['ban_user'])&&csV('ban_user')){
    $uid=(int)$_GET['ban_user'];$reason=$_GET['reason']??'Нарушение';$bh=($_GET['ban_hwid']??'')==='1';
    if($uid==$_SESSION['user_id']){$msg='Нельзя забанить себя';$msg_type='error';}
    else{$t=$pdo->prepare("SELECT username FROM users WHERE id=?");$t->execute([$uid]);$u=$t->fetch();
    if($u){banUserAndLogout($pdo,$uid,$reason,$_SESSION['user_id']);if($bh){$hw=$pdo->prepare("SELECT hwid FROM users WHERE id=?");$hw->execute([$uid]);$hw=$hw->fetchColumn();if(!empty($hw))banHwid($pdo,$hw,$uid,$reason,$_SESSION['user_id']);}$msg=$u['username']." забанен".($bh?' [HWID]':'');$msg_type='success';logAdminAction($pdo,$current_user['id'],$current_user['username'],'ban_user',$u['username'],$uid,$reason);}
    else{$msg='Не найден';$msg_type='error';}}
}
if(isset($_GET['unban_user'])&&csV('unban_user')){$uid=(int)$_GET['unban_user'];$pdo->prepare("UPDATE users SET banned=0,ban_reason=NULL,banned_at=NULL,banned_by=NULL WHERE id=?")->execute([$uid]);$msg="#$uid разбанен";$msg_type='success';}
if(isset($_GET['reset_hwid'])&&csV('reset_hwid')){$uid=(int)$_GET['reset_hwid'];$pdo->prepare("UPDATE users SET hwid=NULL WHERE id=?")->execute([$uid]);$msg="HWID #$uid сброшен";$msg_type='success';}
if(isset($_GET['delete_key'])&&csV('delete_key')){$pdo->prepare("DELETE FROM license_keys WHERE id=?")->execute([(int)$_GET['delete_key']]);$msg='Ключ удалён';$msg_type='success';}
if(isset($_GET['toggle_promo'])&&csV('toggle_promo')){$pdo->prepare("UPDATE promo_codes SET active=NOT active WHERE id=?")->execute([(int)$_GET['toggle_promo']]);$msg='Статус изменён';$msg_type='success';}
if(isset($_GET['delete_promo'])&&csV('delete_promo')){$pdo->prepare("DELETE FROM promo_codes WHERE id=?")->execute([(int)$_GET['delete_promo']]);$msg='Промо удалён';$msg_type='success';}

/* ═══ DATA ═══ */
$tab=$_GET['tab']??'users';$page=max(1,(int)($_GET['page']??1));$limit=15;$offset=($page-1)*$limit;$search=trim($_GET['search']??'');
$list=[];$total_items=0;$total_pages=1;
try{
if($tab==='users'){
    if(!empty($search)){
        if(is_numeric($search)){$s=$pdo->prepare("SELECT COUNT(*) FROM users WHERE id=?");$s->execute([$search]);$total_items=$s->fetchColumn();$s=$pdo->prepare("SELECT * FROM users WHERE id=? ORDER BY id DESC LIMIT ? OFFSET ?");$s->bindValue(1,$search,PDO::PARAM_INT);$s->bindValue(2,$limit,PDO::PARAM_INT);$s->bindValue(3,$offset,PDO::PARAM_INT);$s->execute();$list=$s->fetchAll();}
        else{$s=$pdo->prepare("SELECT COUNT(*) FROM users WHERE username LIKE ?");$s->execute(["%$search%"]);$total_items=$s->fetchColumn();$s=$pdo->prepare("SELECT * FROM users WHERE username LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");$s->bindValue(1,"%$search%",PDO::PARAM_STR);$s->bindValue(2,$limit,PDO::PARAM_INT);$s->bindValue(3,$offset,PDO::PARAM_INT);$s->execute();$list=$s->fetchAll();}
    }else{$total_items=$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();$s=$pdo->prepare("SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?");$s->bindValue(1,$limit,PDO::PARAM_INT);$s->bindValue(2,$offset,PDO::PARAM_INT);$s->execute();$list=$s->fetchAll();}
    $total_pages=max(1,ceil($total_items/$limit));
}elseif($tab==='keys'){$total_items=$pdo->query("SELECT COUNT(*) FROM license_keys")->fetchColumn();$total_pages=max(1,ceil($total_items/$limit));$s=$pdo->prepare("SELECT * FROM license_keys ORDER BY id DESC LIMIT ? OFFSET ?");$s->bindValue(1,$limit,PDO::PARAM_INT);$s->bindValue(2,$offset,PDO::PARAM_INT);$s->execute();$list=$s->fetchAll();}
elseif($tab==='promos'){$total_items=$pdo->query("SELECT COUNT(*) FROM promo_codes")->fetchColumn();$total_pages=max(1,ceil($total_items/$limit));$s=$pdo->prepare("SELECT * FROM promo_codes ORDER BY id DESC LIMIT ? OFFSET ?");$s->bindValue(1,$limit,PDO::PARAM_INT);$s->bindValue(2,$offset,PDO::PARAM_INT);$s->execute();$list=$s->fetchAll();}
}catch(PDOException $e){$list=[];$total_items=0;$total_pages=1;}

$settings=getLauncherSettings($pdo);
if(!empty($settings['site_name']))$SITE_NAME=$settings['site_name'];
$site_name=htmlspecialchars($SITE_NAME??'');
$all_roles=getRolesMap($pdo);
$shop_plans=[];try{$shop_plans=$pdo->query("SELECT * FROM shop_plans ORDER BY sort_order,id")->fetchAll();}catch(PDOException $e){}
$payments_list=[];try{$payments_list=$pdo->query("SELECT p.*,u.username FROM payments p LEFT JOIN users u ON p.user_id=u.id ORDER BY p.id DESC LIMIT 50")->fetchAll();}catch(PDOException $e){}
$stats=['users'=>0,'keys'=>0,'reviews'=>0,'payments'=>0];
try{$stats['users']=$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();}catch(PDOException $e){}
try{$stats['keys']=$pdo->query("SELECT COUNT(*) FROM license_keys WHERE used=0")->fetchColumn();}catch(PDOException $e){}
try{$stats['payments']=$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed'")->fetchColumn();}catch(PDOException $e){}

$CT=[];
foreach(['generate_key','give_sub','remove_sub','ban_user','unban_user','reset_hwid','change_pw','change_role','delete_key','create_promo','delete_promo','toggle_promo','save_settings','save_shop','delete_shop_plan'] as $a){$CT[$a]=csT($a);}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#08080f">
    <title>Админ — <?php echo $site_name; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style><?php include 'style.css'; ?></style>
<style>
.shader-orbs{display:none}
.orb--1,.orb--2,.orb--3,.orb--4{display:none}
.halo--a,.halo--b,.halo--c{display:none}
.veil{display:none}
.hero-letter{display:inline-block;opacity:1;transition:all 0.5s cubic-bezier(0.22,1,0.36,1)}
.hero-letter.vis{opacity:1;transform:translateY(0)}
.cab-tile,.cab-action{opacity:1;transition:opacity 0.4s cubic-bezier(0.22,1,0.36,1),transform 0.4s cubic-bezier(0.22,1,0.36,1)}
.cab-tile.in,.cab-action.in{opacity:1!important;transform:translate(0,0) rotate(0) scale(1)!important}
.cab-profile{opacity:1;transition:all 0.6s cubic-bezier(0.22,1,0.36,1)}
.cab-profile.in{opacity:1;transform:translateY(0)}
.scroll-top{position:fixed;bottom:32px;right:32px;width:44px;height:44px;border-radius:14px;background:rgba(255,255,255,0.06);-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.08);color:#fff;font-size:16px;display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:0;transform:translateY(20px);transition:all 0.3s cubic-bezier(0.22,1,0.36,1);z-index:90;pointer-events:none}
.scroll-top.show{opacity:1;transform:translateY(0);pointer-events:auto}
.scroll-top:hover{background:rgba(var(--color-accent-rgb),0.15);border-color:rgba(var(--color-accent-rgb),0.3)}
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
.bg-fx{position:fixed;inset:0;z-index:-1;pointer-events:none;overflow:hidden;background-image:url('assets/img/background.jpg');background-size:cover;background-position:center}
.bg-fx::before{content:"";position:absolute;inset:0;background:rgba(8,8,15,0.5);-webkit-backdrop-filter:blur(16px) saturate(1.2);backdrop-filter:blur(16px) saturate(1.2)}
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
</style>
<?php include 'loader_css.php'; ?>
</head>
<body>
<?php include 'loader_html.php'; ?>

<script>setTimeout(function(){var o=document.getElementById('loaderOverlay');if(o){o.classList.add('hide');setTimeout(function(){if(o.parentNode)o.parentNode.removeChild(o)},600)}},3000);</script>
<div class="bg-fx"></div>
<div class="particles" id="particles"></div>

<div id="root">
<div class="app app--inner">
<header class="header nav" id="nav">
<div class="header-content">
    <a class="header-brand-link" href="/main.php">
        <span class="header-logo"><img src="/assets/logo.png" alt="" onerror="this.style.display='none'"></span>
        <span class="header-brand brand-shine" data-text="<?php echo $site_name; ?>"><?php echo $site_name; ?></span>
    </a>
    <nav class="header-nav">
        <a href="/main.php">Главная</a>
        <a href="/shop.php">Магазин</a>
        <a href="/rules.php">Правила</a>
        <a href="/privacy.php">Соглашение</a>
        <a href="/profile.php">Профиль</a>
    </nav>
    <div class="header-actions">
        <?php if (isLoggedIn() && $current_user): ?>
            <a href="/loaderadmin.php" class="header-action"><i class="fas fa-rocket"></i><span>Лаунчер</span></a>
            <a href="/configure.php" class="header-action"><i class="fas fa-user-tag"></i><span>Роли</span></a>
            <button class="header-action header-action--profile" type="button" onclick="location.href='/profile.php'">
                <img class="header-avatar" src="/assets/ava.png" alt="" onerror="this.style.display='none'">
                <span>Profile</span>
            </button>
        <?php else: ?>
            <a href="/login.php" class="header-action"><i class="fas fa-sign-in-alt"></i><span>Войти</span></a>
            <a href="/register.php" class="header-action"><i class="fas fa-user-plus"></i><span>Регистрация</span></a>
        <?php endif; ?>
        <button class="burger" id="burgerBtn" aria-label="Меню"><i class="fas fa-bars"></i></button>
    </div>
</div>
<div class="m-menu">
    <a href="/main.php">Главная</a>
    <a href="/shop.php">Магазин</a>
    <a href="/rules.php">Правила</a>
    <a href="/privacy.php">Соглашение</a>
    <a href="/profile.php">Профиль</a>
    <a href="/loaderadmin.php">Лаунчер</a>
    <?php if (isLoggedIn()): ?>
        <a href="/logout.php">Выйти</a>
    <?php else: ?>
        <a href="/login.php">Войти</a>
        <a href="/register.php">Регистрация</a>
    <?php endif; ?>
</div>
</header>

<div class="route-view">
<div class="cabinet">
<main class="cabinet__content">
    <div class="cabinet__head">
        <div>
            <p class="cabinet__kicker">Панель управления</p>
            <h1 class="cabinet__title">Админ</h1>
        </div>
        <a href="/logout.php" class="cabinet__logout">
            <i class="fas fa-right-from-bracket"></i> Выйти
        </a>
    </div>

    <?php if(!empty($msg)):?>
        <div class="msg <?php echo $msg_type==='success'?'success':'error'; ?>"><i class="fas fa-<?php echo $msg_type==='success'?'check-circle':'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <section class="cab-profile">
        <div class="cab-profile__avatar">
            <img src="/assets/ava.png" alt="avatar" onerror="this.style.display='none'">
        </div>
        <div class="cab-profile__info">
            <h2 class="cab-profile__name"><?php echo htmlspecialchars($current_user['username']); ?></h2>
            <p class="cab-profile__email"><?php echo htmlspecialchars($current_user['email']); ?></p>
            <div class="cab-badges">
                <span class="cab-badge"><i class="fas fa-shield-alt"></i> Admin</span>
                <span class="cab-badge"><i class="fas fa-hashtag"></i> UID <?php echo (int)$current_user['id']; ?></span>
            </div>
        </div>
    </section>

    <section class="cab-section">
        <div class="cab-section__head">
            <h3 class="cab-section__title">Статистика</h3>
        </div>
        <div class="cab-tiles">
            <div class="cab-tile">
                <span class="cab-tile__icon"><i class="fas fa-users"></i></span>
                <span class="cab-tile__label">Пользователей</span>
                <span class="cab-tile__value"><?php echo number_format($stats['users']); ?></span>
            </div>
            <div class="cab-tile">
                <span class="cab-tile__icon"><i class="fas fa-key"></i></span>
                <span class="cab-tile__label">Ключей доступно</span>
                <span class="cab-tile__value"><?php echo number_format($stats['keys']); ?></span>
            </div>
            <div class="cab-tile">
                <span class="cab-tile__icon"><i class="fas fa-ruble-sign"></i></span>
                <span class="cab-tile__label">Доход</span>
                <span class="cab-tile__value"><?php echo number_format($stats['payments'],0,'',' '); ?> ₽</span>
            </div>
        </div>
    </section>

    <div class="tabs">
        <a href="?tab=users" class="tab <?php echo $tab==='users'?'active':''; ?>"><i class="fas fa-users"></i> Пользователи</a>
        <a href="?tab=keys" class="tab <?php echo $tab==='keys'?'active':''; ?>"><i class="fas fa-key"></i> Ключи</a>
        <a href="?tab=promos" class="tab <?php echo $tab==='promos'?'active':''; ?>"><i class="fas fa-ticket-alt"></i> Промокоды</a>
        <a href="?tab=payments" class="tab <?php echo $tab==='payments'?'active':''; ?>"><i class="fas fa-credit-card"></i> Платежи</a>
        <a href="?tab=shop" class="tab <?php echo $tab==='shop'?'active':''; ?>"><i class="fas fa-shopping-cart"></i> Магазин</a>
        <a href="?tab=logs" class="tab <?php echo $tab==='logs'?'active':''; ?>"><i class="fas fa-history"></i> Логи</a>
        <a href="?tab=maintenance" class="tab <?php echo $tab==='maintenance'?'active':''; ?>"><i class="fas fa-wrench"></i> Тех. работы</a>
    </div>

<?php /* ══ USERS ══ */ if($tab==='users'): ?>
<div class="card"><div class="card-title"><i class="fas fa-users"></i> Пользователи</div>
<form method="GET" class="search-box"><input type="hidden" name="tab" value="users"><i class="fas fa-search"></i><input type="text" name="search" class="form-control" placeholder="Поиск по ID или username..." value="<?php echo htmlspecialchars($search); ?>"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button><?php if($search):?><a href="?tab=users" class="btn btn-warning btn-sm"><i class="fas fa-times"></i></a><?php endif;?></form>
<div class="table-wrapper"><table>
<thead><tr><th>ID</th><th>Логин</th><th>Email</th><th>Роль</th><th>Подписка</th><th>Действия</th></tr></thead>
<tbody><?php if(empty($list)):?><tr><td colspan="6" style="text-align:center;padding:36px;color:var(--faint)">Пусто</td></tr>
<?php else:foreach($list as $u):$hs=!empty($u['subscription_end'])&&strtotime($u['subscription_end'])>time();$fv=!empty($u['subscription_end'])&&strtotime($u['subscription_end'])>strtotime('9998-01-01');$bn=$u['banned']==1;?>
<tr>
<td><span class="mono">#<?php echo $u['id']; ?></span></td>
<td><a href="/admin_user.php?id=<?php echo $u['id']; ?>" style="color:var(--ink);text-decoration:none;font-weight:600;border-bottom:1px solid rgba(255,255,255,0.1);transition:color .15s" onmouseover="this.style.color='var(--color-accent)'" onmouseout="this.style.color='var(--ink)'"><?php echo htmlspecialchars($u['username']); ?></a></td>
<td><?php echo htmlspecialchars($u['email']); ?></td>
<td><form method="POST" onsubmit="return confirm('Сменить роль?')" style="display:inline-flex;gap:3px;align-items:center"><?php echo csF('change_role');?><input type="hidden" name="action" value="change_role"><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><select name="role" class="role-select"><?php foreach($all_roles as $rk=>$ri):?><option value="<?php echo htmlspecialchars($rk); ?>"<?php echo $u['role']===$rk?' selected':''; ?>><?php echo htmlspecialchars($ri['name']); ?></option><?php endforeach;?></select><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button></form></td>
<td><?php if($fv):?><span class="badge badge-premium">НАВСЕГДА</span><?php elseif($hs):?><span class="badge badge-success">до <?php echo date('d.m.Y',strtotime($u['subscription_end'])); ?></span><?php else:?><span class="badge badge-user">Нет</span><?php endif;?></td>
<td><div class="actions">
<a href="<?php echo csL("?give_sub={$u['id']}&days=30",'give_sub'); ?>" class="btn btn-success btn-sm">+30д</a>
<a href="<?php echo csL("?give_sub={$u['id']}&days=90",'give_sub'); ?>" class="btn btn-success btn-sm">+90д</a>
<a href="<?php echo csL("?give_sub={$u['id']}&days=99999",'give_sub'); ?>" class="btn btn-primary btn-sm"><i class="fas fa-infinity"></i></a>
<?php if($hs&&!$fv):?><a href="<?php echo csL("?remove_sub={$u['id']},'remove_sub'"); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Снять?')"><i class="fas fa-minus"></i></a><?php endif;?>
<a href="<?php echo csL("?reset_hwid={$u['id']},'reset_hwid'"); ?>" class="btn btn-warning btn-sm" onclick="return confirm('Сбросить HWID?')"><i class="fas fa-microchip"></i></a>
<?php if($bn):?><a href="<?php echo csL("?unban_user={$u['id']},'unban_user'"); ?>" class="btn btn-success btn-sm" onclick="return confirm('Разбан?')"><i class="fas fa-undo"></i></a>
<?php else:?><a href="#" onclick="showBan(<?php echo $u['id']; ?>,'<?php echo htmlspecialchars($u['username']); ?>')" class="btn btn-danger btn-sm"><i class="fas fa-gavel"></i></a><?php endif;?>
<form method="POST" style="display:inline-flex;gap:3px;align-items:center" onsubmit="return confirm('Сменить пароль?')"><?php echo csF('change_pw');?><input type="hidden" name="action" value="change_pw"><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="text" name="new_password" placeholder="*" class="form-control" style="width:88px;padding:5px 9px;font-size:11.5px"><button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-key"></i></button></form>
</div></td></tr>
<?php endforeach;endif;?></tbody></table></div>
<?php if($total_pages>1):?><div class="pagination"><?php for($i=1;$i<=$total_pages;$i++):?><a href="?tab=users&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="page <?php echo $i===$page?'active':''; ?>"><?php echo $i; ?></a><?php endfor;?></div><?php endif;?>
</div><?php endif; ?>

<?php /* ══ KEYS ══ */ if($tab==='keys'): ?>
<div class="card"><div class="card-title"><i class="fas fa-plus"></i> Генерация</div>
<form method="POST" class="form-row"><?php echo csF('generate_key');?><input type="hidden" name="action" value="generate_key">
<div class="form-group"><label>Дней</label><input type="number" name="days" value="30" min="1" class="form-control"></div>
<div class="form-group"><label>Количество</label><input type="number" name="amount" value="1" min="1" max="100" class="form-control"></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-sync"></i> Создать</button></form></div>
<div class="card"><div class="card-title"><i class="fas fa-key"></i> Список</div>
<div class="table-wrapper"><table><thead><tr><th>ID</th><th>Ключ</th><th>Дней</th><th>Статус</th><th>Кем</th><th></th></tr></thead>
<tbody><?php if(empty($list)):?><tr><td colspan="6" style="text-align:center;padding:36px;color:var(--faint)">Пусто</td></tr>
<?php else:foreach($list as $k):?><tr><td><span class="mono">#<?php echo $k['id']; ?></span></td><td class="mono"><?php echo htmlspecialchars($k['key']); ?></td><td><?php echo $k['days']; ?></td><td><span class="badge <?php echo $k['used']?'badge-user':'badge-success'; ?>"><?php echo $k['used']?'Использован':'Активен'; ?></span></td><td><?php echo $k['used_by']?'#'.$k['used_by']:'—'; ?></td><td><a href="<?php echo csL("?delete_key={$k['id']},'delete_key'"); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i></a></td></tr><?php endforeach;endif;?></tbody></table></div>
<?php if($total_pages>1):?><div class="pagination"><?php for($i=1;$i<=$total_pages;$i++):?><a href="?tab=keys&page=<?php echo $i; ?>" class="page <?php echo $i===$page?'active':''; ?>"><?php echo $i; ?></a><?php endfor;?></div><?php endif;?>
</div><?php endif; ?>

<?php /* ══ PROMOS ══ */ if($tab==='promos'): ?>
<div class="card"><div class="card-title"><i class="fas fa-plus"></i> Создать промокод</div>
<form method="POST" class="form-row"><?php echo csF('create_promo');?><input type="hidden" name="action" value="create_promo">
<div class="form-group"><label>Код</label><input type="text" name="promo_code" placeholder="Авто если пусто" class="form-control"></div>
<div class="form-group"><label>Тип</label><select name="discount_type" class="form-control"><option value="percent">%</option><option value="fixed">₽</option></select></div>
<div class="form-group"><label>Скидка</label><input type="number" name="discount_value" value="10" class="form-control"></div>
<div class="form-group"><label>Мин. покупка</label><input type="number" name="min_purchase" value="0" class="form-control"></div>
<div class="form-group"><label>Лимит (0=∞)</label><input type="number" name="max_uses" value="0" class="form-control"></div>
<div class="form-group"><label>До</label><input type="datetime-local" name="expires_at" class="form-control"></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Создать</button></form></div>
<div class="card"><div class="card-title"><i class="fas fa-ticket-alt"></i> Список</div>
<div class="table-wrapper"><table><thead><tr><th>ID</th><th>Код</th><th>Скидка</th><th>Uses</th><th>До</th><th>Статус</th><th></th></tr></thead>
<tbody><?php if(empty($list)):?><tr><td colspan="7" style="text-align:center;padding:36px;color:var(--faint)">Пусто</td></tr>
<?php else:foreach($list as $p):$ex=!empty($p['expires_at'])&&strtotime($p['expires_at'])<time();$mx=$p['max_uses']>0&&($p['used_count']??0)>=$p['max_uses'];?>
<tr><td><span class="mono">#<?php echo $p['id']; ?></span></td><td class="mono"><?php echo htmlspecialchars($p['code']); ?></td><td><span class="badge badge-premium"><?php echo $p['discount_value'].($p['discount_type']==='percent'?'%':'₽'); ?></span></td><td><?php echo ($p['used_count']??0).'/'.($p['max_uses']>0?$p['max_uses']:'∞'); ?></td><td><?php echo !empty($p['expires_at'])?date('d.m.Y',strtotime($p['expires_at'])):'—'; ?></td>
<td><?php if(!$p['active']):?><span class="badge badge-user">Выкл</span><?php elseif($ex):?><span class="badge badge-danger">Истёк</span><?php elseif($mx):?><span class="badge badge-warning">Лимит</span><?php else:?><span class="badge badge-success">Ок</span><?php endif;?></td>
<td class="actions"><a href="<?php echo csL("?toggle_promo={$p['id']},'toggle_promo'"); ?>" class="btn btn-sm <?php echo $p['active']?'btn-warning':'btn-success'; ?>"><?php echo $p['active']?'⏸':'▶'; ?></a><a href="<?php echo csL("?delete_promo={$p['id']},'delete_promo'"); ?>" class="btn btn-danger btn-sm" onclick="return confirm('?')"><i class="fas fa-trash"></i></a></td></tr>
<?php endforeach;endif;?></tbody></table></div>
<?php if($total_pages>1):?><div class="pagination"><?php for($i=1;$i<=$total_pages;$i++):?><a href="?tab=promos&page=<?php echo $i; ?>" class="page <?php echo $i===$page?'active':''; ?>"><?php echo $i; ?></a><?php endfor;?></div><?php endif;?>
</div><?php endif; ?>

<?php /* ══ PAYMENTS ══ */ if($tab==='payments'): ?>
<div class="card"><div class="card-title"><i class="fas fa-credit-card"></i> Платежи</div>
<div class="table-wrapper"><table><thead><tr><th>ID</th><th>Юзер</th><th>Сумма</th><th>План</th><th>Статус</th><th>Дата</th></tr></thead>
<tbody><?php if(empty($payments_list)):?><tr><td colspan="6" style="text-align:center;padding:36px;color:var(--faint)">Пусто</td></tr>
<?php else:foreach($payments_list as $p):$stc=['completed'=>'badge-success','pending'=>'badge-warning','failed'=>'badge-danger','refunded'=>'badge-premium'];?>
<tr><td><span class="mono">#<?php echo $p['id']; ?></span></td><td><strong><?php echo htmlspecialchars($p['username']??'ID:'.$p['user_id']); ?></strong></td><td><?php echo number_format($p['amount'],2); ?> ₽</td><td><?php echo htmlspecialchars($p['plan']??'—'); ?></td><td><span class="badge <?php echo $stc[$p['status']]??'badge-user'; ?>"><?php echo htmlspecialchars($p['status']); ?></span></td><td style="white-space:nowrap;font-size:12px"><?php echo date('d.m.y H:i',strtotime($p['created_at']??'now')); ?></td></tr>
<?php endforeach;endif;?></tbody></table></div></div><?php endif; ?>

<?php /* ══ SHOP ══ */ if($tab==='shop'): ?>
<div class="card"><div class="card-title"><i class="fas fa-shopping-cart"></i> Тарифы</div>
<div class="table-wrapper"><table><thead><tr><th>ID</th><th>Название</th><th>Цена</th><th>Бейдж</th><th>Фичи</th><th>Акт.</th><th></th></tr></thead>
<tbody><?php if(empty($shop_plans)):?><tr><td colspan="7" style="text-align:center;padding:36px;color:var(--faint)">Пусто</td></tr>
<?php else:foreach($shop_plans as $sp):?><tr>
<td><span class="mono">#<?php echo $sp['id']; ?></span></td><td><strong><?php echo htmlspecialchars($sp['name']); ?></strong></td><td><?php echo number_format($sp['price'],2); ?> ₽</td><td><?php echo htmlspecialchars($sp['badge']??'—'); ?></td><td style="font-size:11.5px;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($sp['features']??'—'); ?></td>
<td><?php echo $sp['is_active']?'<span class="badge badge-success">✓</span>':'<span class="badge badge-danger">✕</span>'; ?></td>
<td><form method="POST" style="display:inline" onsubmit="return confirm('?')"><?php echo csF('delete_shop_plan');?><input type="hidden" name="delete_shop_plan" value="<?php echo $sp['id']; ?>"><button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form></td></tr>
<?php endforeach;endif;?></tbody></table></div></div>
<div class="card"><div class="card-title"><i class="fas fa-plus"></i> Новый план</div>
<form method="POST"><?php echo csF('save_shop');?><input type="hidden" name="save_shop" value="1"><input type="hidden" name="plan_id" value="0">
<div class="form-row"><div class="form-group"><label>Название</label><input type="text" name="name" class="form-control" required></div><div class="form-group"><label>Цена ₽</label><input type="number" step="0.01" name="price" class="form-control" required></div></div>
<div class="form-row"><div class="form-group"><label>Бейдж</label><input type="text" name="badge" class="form-control"></div><div class="form-group"><label>Порядок</label><input type="number" name="sort_order" class="form-control" value="0"></div></div>
<div class="form-group"><label>Фичи</label><textarea name="features" class="form-control" rows="2"></textarea></div>
<div class="form-group"><label class="checkbox-group"><input type="checkbox" name="is_active" checked> Активен</label></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button></form></div><?php endif; ?>

<?php /* ══ LOGS ══ */ if($tab==='logs'):
try{$pdo->exec("CREATE TABLE IF NOT EXISTS `admin_logs`(`id` int NOT NULL AUTO_INCREMENT,`admin_id` int NOT NULL,`admin_login` varchar(64) NOT NULL,`action` varchar(32) NOT NULL,`target_user` varchar(64) DEFAULT NULL,`target_user_id` int DEFAULT NULL,`details` text,`created_at` datetime,PRIMARY KEY(`id`),KEY `admin_id`(`admin_id`),KEY `created_at`(`created_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}catch(PDOException $e){}
$lp=max(1,(int)($_GET['page']??1));$ll=30;$lo=($lp-1)*$ll;
try{$lt=$pdo->query("SELECT COUNT(*) FROM admin_logs")->fetchColumn();}catch(PDOException $e){$lt=0;}
$ltp=max(1,ceil($lt/$ll));$logs=[];
try{$lq=$pdo->prepare("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT ? OFFSET ?");$lq->bindValue(1,$ll,PDO::PARAM_INT);$lq->bindValue(2,$lo,PDO::PARAM_INT);$lq->execute();$logs=$lq->fetchAll();}catch(PDOException $e){}
$al=['create_keys'=>['Ключи','#8b5cf6'],'give_sub'=>['+Подписка','#10b981'],'remove_sub'=>['-Подписка','#f59e0b'],'ban_user'=>['Бан','#ef4444'],'unban_user'=>['Разбан','#10b981'],'reset_hwid'=>['HWID','#6366f1'],'change_password'=>['Пароль','#f59e0b'],'change_role'=>['Роль','#8b5cf6'],'delete_key'=>['-Ключ','#ef4444'],'create_promo'=>['+Промо','#10b981'],'delete_promo'=>['-Промо','#ef4444'],'toggle_promo'=>['~Промо','#f59e0b'],'approve_review'=>['+Отзыв','#10b981'],'reject_review'=>['-Отзыв','#ef4444'],'delete_review'=>['-Отзыв','#ef4444'],'edit_review'=>['~Отзыв','#6366f1'],'save_settings'=>['Настройки','#8b5cf6']];
?>
<div class="card"><div class="card-title"><i class="fas fa-history"></i> Журнал</div>
<div class="table-wrapper"><table><thead><tr><th>Дата</th><th>Админ</th><th>Действие</th><th>Цель</th><th>Детали</th></tr></thead>
<tbody><?php if(empty($logs)):?><tr><td colspan="5" style="text-align:center;padding:36px;color:var(--faint)">Пусто</td></tr>
<?php else:foreach($logs as $l):$lb=$al[$l['action']]??[$l['action'],'#aaa'];?>
<tr><td style="white-space:nowrap;font-size:11.5px"><?php echo date('d.m.y H:i',strtotime($l['created_at'])); ?></td><td><strong><?php echo htmlspecialchars($l['admin_login']); ?></strong></td><td><span style="color:<?php echo $lb[1]; ?>;font-weight:600;font-size:12.5px"><?php echo $lb[0]; ?></span></td><td><?php echo $l['target_user']?'<strong>'.htmlspecialchars($l['target_user']).'</strong>':'—'; ?></td><td style="color:var(--faint);font-size:11.5px"><?php echo htmlspecialchars($l['details']??'—'); ?></td></tr>
<?php endforeach;endif;?></tbody></table></div>
<?php if($ltp>1):?><div class="pagination"><?php for($i=1;$i<=$ltp;$i++):?><a href="?tab=logs&page=<?php echo $i; ?>" class="page <?php echo $i===$lp?'active':''; ?>"><?php echo $i; ?></a><?php endfor;?></div><?php endif;?>
</div><?php endif; ?>

<?php /* ══ MAINTENANCE ══ */ if($tab==='maintenance'):
$mm=($settings['maintenance_mode']??'0')==='1';?>
<div class="card"><div class="card-title"><i class="fas fa-wrench"></i> Технические работы</div>
<form method="POST"><?php echo csF('save_settings');?><input type="hidden" name="save_settings" value="1">
<div style="padding:20px;border-radius:12px;background:<?php echo $mm?'rgba(245,158,11,.08)':'rgba(16,185,129,.06)'; ?>;border:1px solid <?php echo $mm?'rgba(245,158,11,.25)':'rgba(16,185,129,.2)'; ?>;margin-bottom:18px">
<label class="checkbox-group" style="border:none;padding:0;background:none;font-size:14px;font-weight:600;color:<?php echo $mm?'#fcd34d':'#6ee7b7'; ?>">
<input type="checkbox" name="maintenance_mode" <?php echo $mm?'checked':''; ?> onchange="this.closest('div').style.background=this.checked?'rgba(245,158,11,.08)':'rgba(16,185,129,.06)';this.closest('div').style.borderColor=this.checked?'rgba(245,158,11,.25)':'rgba(16,185,129,.2)';this.closest('label').style.color=this.checked?'#fcd34d':'#6ee7b7'">
<?php echo $mm?'⚠ Тех. работы ВКЛЮЧЕНЫ — сайт недоступен для всех кроме admin/login':'✓ Сайт работает нормально'; ?>
</label></div>
<div class="form-group"><label>Сообщение</label><textarea name="maintenance_message" class="form-control" rows="3"><?php echo htmlspecialchars($settings['maintenance_message']??'Сервис временно недоступен.'); ?></textarea></div>
<div class="form-group"><label>Название сайта</label><input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name']??''); ?>"></div>
<div class="form-row"><div class="form-group"><label>Discord</label><input type="text" name="discord_link" class="form-control" value="<?php echo htmlspecialchars($settings['discord_link']??''); ?>"></div><div class="form-group"><label>Telegram</label><input type="text" name="telegram_link" class="form-control" value="<?php echo htmlspecialchars($settings['telegram_link']??''); ?>"></div></div>
<div class="form-row"><div class="form-group"><label>YouTube</label><input type="text" name="youtube_link" class="form-control" value="<?php echo htmlspecialchars($settings['youtube_link']??''); ?>"></div><div class="form-group"><label>URL сайта</label><input type="text" name="site_url" class="form-control" value="<?php echo htmlspecialchars($settings['site_url']??''); ?>"></div></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button></form>
<p style="margin-top:14px;font-size:12.5px;color:var(--faint)"><i class="fas fa-info-circle"></i> При включении сайт покажет страницу «Тех. работы» всем пользователям <strong>кроме</strong> admin.php и login.php</p>
</div><?php endif; ?>

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
            <a class="footer__link" href="/main.php">Главная</a>
            <a class="footer__link" href="/shop.php">Магазин</a>
            <a class="footer__link" href="/rules.php">Правила</a>
            <a class="footer__link" href="/privacy.php">Соглашение</a>
        </nav>
    </div>
    <div class="footer__nav-group">
        <h3 class="footer__title">Документы</h3>
        <nav class="footer__links">
            <a class="footer__link" href="/privacy.php">Политика конфиденциальности</a>
            <a class="footer__link" href="/rules.php">Пользовательское соглашение</a>
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

<button class="scroll-top" id="scrollTop" aria-label="Наверх"><i class="fas fa-arrow-up"></i></button>

<div class="modal-overlay" id="banMo"><div class="modal-box">
<h3><i class="fas fa-gavel"></i> Бан</h3>
<div class="sub" id="banWho">Юзер: <strong></strong></div>
<div class="form-group"><label>Причина</label><input type="text" id="banReason" class="form-control" value="Нарушение правил"></div>
<label class="checkbox-group" style="margin-bottom:16px"><input type="checkbox" id="banHwid"> <i class="fas fa-microchip"></i> +HWID бан</label>
<div class="btn-group"><button onclick="doBan()" class="btn btn-danger"><i class="fas fa-gavel"></i> Забанить</button><button onclick="closeBan()" class="btn btn-ghost">Отмена</button></div>
</div></div>

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
            var d=e.target.getAttribute('data-stagger');
            if(d)e.target.style.transitionDelay=(parseInt(d)*50)+'ms';
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
    var btn=document.getElementById('scrollTop');
    if(!btn)return;
    window.addEventListener('scroll',function(){
        btn.classList.toggle('show',window.scrollY>300);
    },{passive:true});
    btn.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'})});
})();
</script>
<script>
var bu=null;
function showBan(id,name){bu=id;document.querySelector('#banWho strong').textContent=name;document.getElementById('banReason').value='Нарушение правил';document.getElementById('banHwid').checked=false;document.getElementById('banMo').classList.add('active')}
function closeBan(){document.getElementById('banMo').classList.remove('active');bu=null}
function doBan(){if(bu){var r=encodeURIComponent(document.getElementById('banReason').value);var h=document.getElementById('banHwid').checked?'1':'0';location.href='?ban_user='+bu+'&reason='+r+'&ban_hwid='+h+'&csrf=<?php echo urlencode($CT['ban_user']); ?>'}}
document.getElementById('banMo').addEventListener('click',function(e){if(e.target===this)closeBan()});
</script>
<script src="/lang.js"></script>
<?php include 'loader_js.php'; ?>
</body>
</html>
