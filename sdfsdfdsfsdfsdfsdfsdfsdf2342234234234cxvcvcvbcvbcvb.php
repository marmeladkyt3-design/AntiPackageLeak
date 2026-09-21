<?php
// Запрет прямого доступа к файлу конфигурации/защиты
if (basename($_SERVER['PHP_SELF'] ?? '') === basename(__FILE__)) {
    http_response_code(403);
    exit('Forbidden');
}

if (file_exists(__DIR__ . '/site_config.php')) {
    require_once __DIR__ . '/site_config.php';
}
if (!isset($SITE_NAME) || trim($SITE_NAME) === '') {
    $SITE_NAME = 'Placeholder';
}

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    ]);
    session_start();
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// ========== НАСТРОЙКИ БАЗЫ ДАННЫХ ==========
$db_host = 'sql309.infinityfree.com';
$db_name = 'if0_42944847_AntiPackageLeak';
$db_user = 'if0_42944847';
$db_pass = 'c92WWhdVGc';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Bot protection disabled

// Заголовки безопасности (только для HTML-страниц)
$is_api = strpos($_SERVER['PHP_SELF'], 'api_') !== false
       || strpos($_SERVER['PHP_SELF'], '/api/') !== false
       || strpos($_SERVER['PHP_SELF'], 'hwid_') !== false
       || strpos($_SERVER['PHP_SELF'], 'check_hwid') !== false
       || strpos($_SERVER['PHP_SELF'], 'heartbeat') !== false
       || strpos($_SERVER['PHP_SELF'], 'get_user') !== false
       || strpos($_SERVER['PHP_SELF'], 'log_download') !== false
       || strpos($_SERVER['PHP_SELF'], 'compute_hash') !== false;

if (!$is_api) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// Запрещённые HTTP-методы
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if (in_array($method, ['TRACE', 'TRACK', 'CONNECT'], true)) {
    http_response_code(405);
    if ($is_api) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'method not allowed']);
    } else {
        echo '405 Method Not Allowed';
    }
    exit;
}

// ========== НАСТРОЙКИ reCAPTCHA ==========
define('RECAPTCHA_SITE_KEY', '6LfaSsMtAAAAAIzx9R1jUYDn35w6w9QUyT7xRjAs');
define('RECAPTCHA_SECRET_KEY', '6LfaSsMtAAAAAEhR9WUTQO2zXsGEERrQTphZujjb');

function verifyRecaptcha($token) {
    if (empty($token) || !is_string($token)) return false;
    $secret = RECAPTCHA_SECRET_KEY;
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = http_build_query(['secret' => $secret, 'response' => $token]);
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $data, 'timeout' => 3]]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp === false) return true;
    $j = json_decode($resp, true);
    return isset($j['success']) && $j['success'] === true;
}

function verifyAalCaptcha($token) {
    if (empty($token) || !is_string($token)) return false;

    $CAPTCHA_SECRET = 'aal_cap_' . md5(ANTI_SECRET . '_captcha_v1');

    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) return false;

    $body = @base64_decode(strtr($parts[0], '-_', '+/'), true);
    if ($body === false || strpos($body, '|') === false) return false;

    if (!hash_equals(hash_hmac('sha256', $body, $CAPTCHA_SECRET), $parts[1])) return false;

    $bparts = explode('|', $body);
    $exp = (int)$bparts[0];
    $tok_ip = $bparts[1] ?? '';

    if ($exp < time()) return false;

    $cur_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $cur_ip = trim(explode(',', $cur_ip)[0]);
    if (!filter_var($cur_ip, FILTER_VALIDATE_IP)) $cur_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if ($tok_ip !== $cur_ip) return false;

    return true;
}

// ========== ФУНКЦИИ ПОЛЬЗОВАТЕЛЕЙ ==========
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser($pdo, $id) {
    static $cache = [];
    if (isset($cache[$id])) {
        return $cache[$id];
    }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $cache[$id] = $stmt->fetch();
    return $cache[$id];
}

// ========== РОЛИ И ПРАВА (таблица roles, configure.php) ==========
function getRolesMap($pdo) {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    try {
        $stmt = $pdo->query("SELECT * FROM roles");
        while ($row = $stmt->fetch()) {
            $cache[$row['role_key']] = $row;
        }
    } catch (Exception $e) {
        $cache = [];
    }
    return $cache;
}

function getRoleInfo($pdo, $key) {
    $map = getRolesMap($pdo);
    return $map[$key] ?? null;
}

// Эффективная роль пользователя с учётом авто-ролей (user = есть подписка, guest = нет)
function resolveRole($pdo, $user) {
    if (!$user) {
        return 'guest';
    }
    $key = $user['role'] ?? 'guest';
    $info = getRoleInfo($pdo, $key);
    if (!$info || empty($info['is_auto'])) {
        return $key;
    }
    $has_sub = !empty($user['subscription_end']) && strtotime($user['subscription_end']) > time();
    if ($info['auto_type'] === 'has_sub') {
        return $has_sub ? $key : 'guest';
    }
    if ($info['auto_type'] === 'no_sub') {
        return $has_sub ? 'user' : $key;
    }
    return $key;
}

// Проверка права пользователя: can_admin, can_support, as_admin
function userHasRight($pdo, $user, $right) {
    if (!$user) {
        return false;
    }
    $key = resolveRole($pdo, $user);
    $info = getRoleInfo($pdo, $key);
    if ($info) {
        return !empty($info[$right]);
    }
    return $key === 'admin';
}

function roleHasAccess($pdo, $user, $requiredRole) {
    if (empty($requiredRole)) return true;
    if (!$user) return false;
    $effective = resolveRole($pdo, $user);
    $hierarchy = ['guest' => 0, 'user' => 1, 'support' => 2, 'moderator' => 3, 'admin' => 4];
    $myLevel = $hierarchy[$effective] ?? 0;
    $needLevel = $hierarchy[$requiredRole] ?? 0;
    return $myLevel >= $needLevel;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// ========== ФУНКЦИЯ ДЛЯ СКЛОНЕНИЯ МИНУТ ==========
function getMinuteDeclension($n) {
    $n = abs($n) % 100;
    if ($n >= 11 && $n <= 14) return 'минут';
    $n = $n % 10;
    if ($n == 1) return 'минуту';
    if ($n >= 2 && $n <= 4) return 'минуты';
    return 'минут';
}

// ========== ФУНКЦИИ ДЛЯ ЛИМИТА ПОПЫТОК ВХОДА ==========
function checkLoginAttempts($pdo, $ip, $login = null) {
    // Очищаем старые блокировки
    $pdo->prepare("DELETE FROM login_attempts WHERE blocked_until < NOW()")->execute();
    
    // Проверяем блокировку
    $stmt = $pdo->prepare("SELECT blocked_until FROM login_attempts WHERE ip = ? AND blocked_until > NOW() ORDER BY blocked_until DESC LIMIT 1");
    $stmt->execute([$ip]);
    $blocked = $stmt->fetch();
    
    if ($blocked) {
        $blocked_time = strtotime($blocked['blocked_until']);
        $current_time = time();
        $remaining_seconds = $blocked_time - $current_time;
        
        // Если осталось меньше 0, значит блокировка истекла
        if ($remaining_seconds <= 0) {
            $pdo->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$ip]);
            return ['blocked' => false, 'attempts' => 0];
        }
        
        $remaining_minutes = ceil($remaining_seconds / 60);
        $remaining_minutes = max(1, min(15, $remaining_minutes));
        
        return ['blocked' => true, 'remaining' => $remaining_minutes];
    }
    
    // Считаем попытки за последние 15 минут
    $stmt = $pdo->prepare("SELECT SUM(attempts) as total FROM login_attempts WHERE ip = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$ip]);
    $result = $stmt->fetch();
    $attempts = (int)($result['total'] ?? 0);
    
    return ['blocked' => false, 'attempts' => $attempts];
}

function addLoginAttempt($pdo, $ip, $login = null) {
    // Проверяем есть ли запись за последние 15 минут без блокировки
    $stmt = $pdo->prepare("SELECT id, attempts FROM login_attempts WHERE ip = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND (blocked_until IS NULL OR blocked_until < NOW())");
    $stmt->execute([$ip]);
    $attempt = $stmt->fetch();
    
    if ($attempt) {
        $newAttempts = $attempt['attempts'] + 1;
        if ($newAttempts >= 10) {
            $stmt = $pdo->prepare("UPDATE login_attempts SET attempts = ?, blocked_until = DATE_ADD(NOW(), INTERVAL 10 MINUTE), last_attempt = NOW() WHERE id = ?");
            $stmt->execute([$newAttempts, $attempt['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE login_attempts SET attempts = ?, last_attempt = NOW() WHERE id = ?");
            $stmt->execute([$newAttempts, $attempt['id']]);
        }
    } else {
        $pdo->prepare("DELETE FROM login_attempts WHERE ip = ? AND (blocked_until < NOW() OR (blocked_until IS NULL AND last_attempt < DATE_SUB(NOW(), INTERVAL 15 MINUTE)))")->execute([$ip]);
        
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip, login, attempts) VALUES (?, ?, 1)");
        $stmt->execute([$ip, $login]);
    }
}

function resetLoginAttempts($pdo, $ip) {
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip = ?");
    $stmt->execute([$ip]);
}

// ========== ПРОВЕРКА БАНА ПРИ ЗАГРУЗКЕ СТРАНИЦЫ ==========
function checkAndLogoutIfBanned($pdo, $userId) {
    if (!$userId) return false;
    
    $stmt = $pdo->prepare("SELECT banned, banned_until FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        $is_banned = false;
        if ($user['banned'] == 1) {
            $is_banned = true;
        }
        if (!empty($user['banned_until']) && strtotime($user['banned_until']) > time()) {
            $is_banned = true;
        }
        
        if ($is_banned) {
            session_destroy();
            header('Location: /login');
            exit;
        }
    }
    return false;
}

// ========== БАН ПОЛЬЗОВАТЕЛЯ С ВЫЛЕТОМ ИЗ АККАУНТА ==========
function banUserAndLogout($pdo, $userId, $reason, $adminId = null, $minutes = null) {
    if ($minutes) {
        $stmt = $pdo->prepare("UPDATE users SET banned = 1, ban_reason = ?, banned_at = NOW(), banned_until = DATE_ADD(NOW(), INTERVAL ? MINUTE), subscription_end = NULL, role = 'user' WHERE id = ?");
        $stmt->execute([$reason, $minutes, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET banned = 1, ban_reason = ?, banned_at = NOW(), banned_until = NULL, subscription_end = NULL, role = 'user' WHERE id = ?");
        $stmt->execute([$reason, $userId]);
    }
    
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
        session_destroy();
        return true;
    }
    return false;
}

// ========== ФУНКЦИИ БАНОВ ==========
function isHwidBanned($pdo, $hwid) {
    if (empty($hwid)) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM banned_hwids WHERE hwid = ?");
    $stmt->execute([$hwid]);
    return $stmt->fetch() !== false;
}

function isIpBanned($pdo, $ip) {
    $stmt = $pdo->prepare("SELECT 1 FROM banned_ips WHERE ip_address = ?");
    $stmt->execute([$ip]);
    return $stmt->fetch() !== false;
}

function banUser($pdo, $userId, $reason, $adminId = null) {
    $stmt = $pdo->prepare("UPDATE users SET banned = 1, ban_reason = ?, banned_at = NOW(), banned_by = ?, subscription_end = NULL, role = 'user' WHERE id = ?");
    $stmt->execute([$reason, $adminId, $userId]);
}

function banHwid($pdo, $hwid, $userId, $reason, $adminId = null) {
    if (empty($hwid)) return;
    $stmt = $pdo->prepare("INSERT IGNORE INTO banned_hwids (hwid, user_id, reason, banned_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$hwid, $userId, $reason, $adminId]);
}

function banIp($pdo, $ip, $userId, $reason, $adminId = null) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO banned_ips (ip_address, user_id, reason, banned_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$ip, $userId, $reason, $adminId]);
}

// ========== ФУНКЦИЯ ЗАПИСИ ЗАПУСКОВ ==========
function recordLaunch($pdo, $userId, $version) {
    try {
        $stmt = $pdo->prepare("INSERT INTO launcher_stats (user_id, version) VALUES (?, ?)");
        $stmt->execute([$userId, $version]);
        
        $stmt = $pdo->prepare("UPDATE users SET total_launches = total_launches + 1 WHERE id = ?");
        $stmt->execute([$userId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// ========== ФУНКЦИИ СТАТИСТИКИ ==========
function getLauncherSettings($pdo) {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM launcher_settings");
        while ($row = $stmt->fetch()) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    } catch (PDOException $e) {}
    return $cache;
}

function getTotalLaunches($pdo) {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM launcher_stats");
        $cache = (int)$stmt->fetchColumn();
    } catch (PDOException $e) { $cache = 0; }
    return $cache;
}

function getUserLaunches($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM launcher_stats WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// ========== ФУНКЦИИ ДЛЯ УВЕДОМЛЕНИЙ ==========
function addNotification($pdo, $userId, $title, $message, $type = 'info', $link = null) {
    $stmt = $pdo->prepare("INSERT INTO user_notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $title, $message, $type, $link]);
}

function getUnreadNotificationsCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function getNotifications($pdo, $userId, $limit = 10) {
    $stmt = $pdo->prepare("SELECT * FROM user_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function markNotificationAsRead($pdo, $notificationId, $userId) {
    $stmt = $pdo->prepare("UPDATE user_notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notificationId, $userId]);
}

function markAllNotificationsAsRead($pdo, $userId) {
    $stmt = $pdo->prepare("UPDATE user_notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
}

// ========== ПРОВЕРКА ПОДПИСОК (ДЛЯ КРОНА) ==========
function checkExpiringSubscriptions($pdo) {
    $stmt = $pdo->prepare("SELECT id, username, subscription_end FROM users WHERE subscription_end IS NOT NULL AND subscription_end > NOW() AND subscription_end < DATE_ADD(NOW(), INTERVAL 3 DAY) AND subscription_notified = 0");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $days_left = ceil((strtotime($user['subscription_end']) - time()) / 86400);
        addNotification($pdo, $user['id'], 'Подписка истекает', "Ваша подписка истечёт через $days_left дней. Продлите её, чтобы продолжить пользоваться лаунчером.", 'warning', '/shop.php');
        
        $stmt2 = $pdo->prepare("UPDATE users SET subscription_notified = 1 WHERE id = ?");
        $stmt2->execute([$user['id']]);
    }
}

// ========== ФУНКЦИЯ ДЛЯ ПРОВЕРКИ БАНА ПРИ ВХОДЕ ==========
function isUserBanned($user) {
    if (!$user) return false;
    
    if ($user['banned'] == 1) {
        return true;
    }
    if (!empty($user['banned_until']) && strtotime($user['banned_until']) > time()) {
        return true;
    }
    return false;
}

function getBanRemainingMinutes($user) {
    if (!empty($user['banned_until']) && strtotime($user['banned_until']) > time()) {
        $remaining = ceil((strtotime($user['banned_until']) - time()) / 60);
        return max(1, min(15, $remaining));
    }
    return 0;
}

// ========== АВТОМИГРАЦИЯ (выполняется раз в 24ч через файл-флаг) ==========
$migration_flag = __DIR__ . '/.migration_cache';
$migration_needed = true;
if (file_exists($migration_flag)) {
    $mtime = @filemtime($migration_flag);
    if ($mtime && (time() - $mtime) < 86400) {
        $migration_needed = false;
    }
}
if ($migration_needed) {
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS `banned` TINYINT(1) DEFAULT 0");
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS `ban_reason` TEXT DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS `banned_until` DATETIME DEFAULT NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS `banned_by` INT DEFAULT NULL");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `banned_hwids` (`id` int(11) NOT NULL AUTO_INCREMENT,`hwid` varchar(255) NOT NULL,`user_id` int(11) DEFAULT NULL,`reason` text DEFAULT NULL,`banned_by` int(11) DEFAULT NULL,`created_at` datetime DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),UNIQUE KEY `hwid` (`hwid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `banned_ips` (`id` int(11) NOT NULL AUTO_INCREMENT,`ip_address` varchar(45) NOT NULL,`user_id` int(11) DEFAULT NULL,`reason` text DEFAULT NULL,`banned_by` int(11) DEFAULT NULL,`created_at` datetime DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY (`id`),UNIQUE KEY `ip_address` (`ip_address`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_logs` (`id` int(11) NOT NULL AUTO_INCREMENT,`admin_id` int(11) NOT NULL,`admin_login` varchar(64) NOT NULL,`action` varchar(32) NOT NULL,`target_user` varchar(64) DEFAULT NULL,`target_user_id` int(11) DEFAULT NULL,`details` text DEFAULT NULL,`created_at` datetime DEFAULT NULL,PRIMARY KEY (`id`),KEY `admin_id` (`admin_id`),KEY `created_at` (`created_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e) {}
    @touch($migration_flag);
}

function logAdminAction($pdo, $adminId, $adminLogin, $action, $targetUser = null, $targetUserId = null, $details = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, admin_login, action, target_user, target_user_id, details, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$adminId, $adminLogin, $action, $targetUser, $targetUserId, $details]);
    } catch (PDOException $e) {}
}

function checkMaintenance() {
    global $pdo;
    try {
        $val = $pdo->query("SELECT setting_value FROM launcher_settings WHERE setting_key='maintenance_mode' LIMIT 1")->fetchColumn();
        if ($val !== '1') return;
    } catch (PDOException $e) { return; }
    $self = strtolower(basename($_SERVER['PHP_SELF'] ?? ''));
    $uri = strtolower(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    $exempt_names = ['admin','login','loaderadmin','api','maintenance'];
    foreach ($exempt_names as $e) {
        if ($self === $e || $self === $e.'.php') return;
        if (strpos($uri, '/'.$e) === 0 || strpos($uri, '/'.$e.'.php') === 0) return;
    }
    $msg = 'Сервис временно недоступен. Попробуйте позже.';
    try {
        $m = $pdo->query("SELECT setting_value FROM launcher_settings WHERE setting_key='maintenance_message' LIMIT 1")->fetchColumn();
        if (!empty($m)) $msg = $m;
    } catch (PDOException $e) {}
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#08080f"><title>Тех. работы</title>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">';
    echo '<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:"Inter",sans-serif;background:#08080f;color:#b4bacd;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden}.wrap{text-align:center;padding:40px;max-width:500px}.icon{width:80px;height:80px;border-radius:24px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;margin:0 auto 28px;font-size:32px;color:#fff;box-shadow:0 24px 60px -16px rgba(99,102,241,0.5);animation:pulse 3s ease-in-out infinite}@keyframes pulse{0%,100%{transform:scale(1);box-shadow:0 24px 60px -16px rgba(99,102,241,0.5)}50%{transform:scale(1.05);box-shadow:0 30px 80px -16px rgba(139,92,246,0.6)}}h1{font-family:"Sora",sans-serif;font-size:clamp(24px,4vw,36px);font-weight:700;color:#f1f2fa;margin-bottom:14px;letter-spacing:-0.02em}p{font-size:15px;line-height:1.7;color:#7d86a3;margin-bottom:32px}.dots{display:flex;justify-content:center;gap:8px}.dot{width:10px;height:10px;border-radius:50%;background:#6366f1;animation:bounce 1.4s ease-in-out infinite}.dot:nth-child(2){animation-delay:0.16s;background:#8b5cf6}.dot:nth-child(3){animation-delay:0.32s;background:#a78bfa}@keyframes bounce{0%,80%,100%{transform:scale(0.6);opacity:0.4}40%{transform:scale(1);opacity:1}}</style></head>';
    echo '<body><div class="wrap"><div class="icon"><i class="fas fa-wrench"></i></div><h1>Технические работы</h1><p>' . htmlspecialchars($msg) . '</p><div class="dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></div></body></html>';
    exit;
}
checkMaintenance();
?>